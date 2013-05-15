<?php
class NamasteLMSCourseModel {
	
	// custom post type Course	
	static function register_course_type() {
		$args=array(
			"label" => __("Namaste! Courses", 'namaste'),
			"labels" => array
				(
					"name"=>__("Courses", 'namaste'), 
					"singular_name"=>__("Course", 'namaste'),
					"add_new_item"=>__("Add New Course", 'namaste')
				),
			"public"=> true,
			"show_ui"=>true,
			"has_archive"=>true,
			"rewrite"=> array("slug"=>"namaste-course", "with_front"=>false),
			"description"=>__("This will create a new course in your Namaste! LMS.",'namaste'),
			"supports"=>array("title", 'editor', 'thumbnail', 'excerpt'),
			"taxonomies"=>array("category"),
			"show_in_nav_menus" => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'show_in_menu' => 'namaste_options',
			"register_meta_box_cb" => array(__CLASS__,"meta_boxes")
		);
		register_post_type( 'namaste_course', $args );
		register_taxonomy_for_object_type('category', 'namaste_course');
	}
	
	// thanks to paranoid at http://wordpress.org/support/topic/custom-post-type-tagscategories-archive-page?replies=40
	static function query_post_type($query) {
		
		if(is_category() || is_tag()) {
			$post_type = get_query_var('post_type');
			if($post_type) $post_type = $post_type;
			else $post_type = array('post','namaste_course'); 
			$query->set('post_type',$post_type);
		}
		
		if ( is_home() && $query->is_main_query() ) $query->set( 'post_type', array( 'post', 'page', 'namaste_course' ) );
		
		return $query;
	}
	
	static function meta_boxes() {
		add_meta_box("namaste_meta", __("Namaste! Settings", 'namaste'), 
							array(__CLASS__, "print_meta_box"), "namaste_course", 'normal', 'high');
	}
	
	static function print_meta_box($post) {
			global $wpdb;
			
			// select lessons in this course
			$lessons = NamasteLMSLessonModel::select($post->ID);
						
			// required lessons
			$required_lessons = get_post_meta($post->ID, 'namaste_required_lessons', true);	
			if(!is_array($required_lessons)) $required_lessons = array();
			
			// enrollment - for now free or admin approved, in the future also paid
			$enroll_mode = get_post_meta($post->ID, 'namaste_enroll_mode', true);
			
			$fee = get_post_meta($post->ID, 'namaste_fee', true);
			$currency = get_option('namaste_currency');
			
			wp_nonce_field( plugin_basename( __FILE__ ), 'namaste_noncemeta' );
			require(NAMASTE_PATH.'/views/course-meta-box.php');  
	}
	
	static function save_course_meta($post_id) {
			global $wpdb;
			
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  return;		
	  	if ( !wp_verify_nonce( $_POST['namaste_noncemeta'], plugin_basename( __FILE__ ) ) ) return;  	  		
	  	if ( !current_user_can( 'edit_post', $post_id ) ) return;
	  	if ('namaste_course' != $_POST['post_type']) return;
			
			update_post_meta($post_id, "namaste_enroll_mode", $_POST['namaste_enroll_mode']);
			update_post_meta($post_id, "namaste_required_lessons", $_POST['namaste_required_lessons']);			
			update_post_meta($post_id, "namaste_fee", $_POST['namaste_fee']);
	}	
	
	// select existing courses
	function select($id = null) {
		global $wpdb;
		
		$id_sql = $id ? $wpdb->prepare(' AND ID = %d ', $id) : '';
		
		$courses = $wpdb->get_results("SELECT * FROM {$wpdb->posts}
		WHERE post_type = 'namaste_course'  AND (post_status='publish' OR post_status='draft')
		$id_sql ORDER BY post_title");
		
		if($id) return $courses[0];
		
		return $courses;	
	}
	
	// let's keep it simple for the moment - display text showing whether the user is enrolled or not
	function enroll_text($content) {
		global $wpdb, $user_ID, $post;
		
		if($post->post_type != 'namaste_course') return $content;
		
		// enrolled? 
		$enrolled = false;
		if(is_user_logged_in()) {
			$enrolled = $wpdb -> get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_COURSES.
			" WHERE user_id = %d AND course_id = %d AND (status = 'enrolled' OR status='completed')", $user_ID, $post->ID));
		}	
		
		if($enrolled) $text = __('You are enrolled in this course. Check "My courses" link in your dashboard to see the lessons and to-do list', 'namaste');
		else $text = __('You can enroll in this course from your student dashboard. You need to be logged in.', 'namaste');
		
		return $content."<p>".$text."</p>";		
	}
	
	// checks if all requirements for completion are satisfied
	function is_ready($course_id, $student_id) {
		$required_lessons = get_post_meta($course_id, 'namaste_required_lessons', true);	
		if(!is_array($required_lessons)) $required_lessons = array();
		
		foreach($required_lessons as $lesson) {
			if(!NamasteLMSLessonModel::is_completed($lesson, $student_id)) return false;
		}	
		
		// all completed, so it's ready
		return true;
	}
	
	// actually marks course as completed
	function complete($course_id, $student_id) {
		global $wpdb;
		
		$student_course = $wpdb -> get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_COURSES."
			WHERE course_id=%d AND user_id=%d", $course_id, $student_id));
		
		if(empty($student_course->id)) return false;
		
		$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_COURSES." SET status = 'completed',
			completion_date = CURDATE() WHERE id=%d", $student_course->id));
			
		// should we assign certificates?
		$_cert = new NamasteLMSCertificateModel();
		$_cert -> complete_course($course_id, $student_id);
		
		// add custom action
		do_action('namaste_completed_course', $student_id, $course_id);	
	}
	
	// returns all the required lessons along with mark whether they are completed or not
	function required_lessons($course_id, $student_id) {
		global $wpdb;
		
		$required_lessons_ids = get_post_meta($course_id, 'namaste_required_lessons', true);	
		if(!is_array($required_lessons_ids)) return array();
		
		$required_lessons = $wpdb->get_results("SELECT * FROM {$wpdb->posts} 
			WHERE ID IN (".implode(",", $required_lessons_ids).") ORDER BY ID");
		
		foreach($required_lessons as $cnt => $lesson) {
			$required_lessons[$cnt]->namaste_completed = 0;
			if(NamasteLMSLessonModel::is_completed($lesson->ID, $student_id)) $required_lessons[$cnt]->namaste_completed = 1;
		}	
		return $required_lessons;
	}
	
	// enrolls or applies to enroll a course
	function enroll($student_id, $course_id, $status) {
		global $wpdb;
		
		$result = $wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_STUDENT_COURSES." SET
					course_id = %d, user_id = %d, status = %s, enrollment_date = CURDATE(),
					completion_date='1900-01-01', comments=''",
					$course_id, $student_id, $status));
		if($result !== false) {
			do_action('namaste_enrolled_course', $student_id, $course_id, $status);
		}			
	}
}