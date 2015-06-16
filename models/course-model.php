<?php
// possible course statuses: pending, rejected, enrolled, completed
class NamasteLMSCourseModel {
	
	// custom post type Course	
	static function register_course_type() {
		
		$course_slug = get_option('namaste_course_slug');
	   if(empty($course_slug)) $course_slug = 'namaste-course';
	  	   
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
			"rewrite"=> array("slug"=>$course_slug, "with_front"=>false),
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
	
	// add courses to the homepage and archive listings
	static function query_post_type($query) {
		if(!get_option('namaste_show_courses_in_blog')) return $query;
		
		if ( (is_home() or is_archive()) and $query->is_main_query() ) {
			$post_types = @$query->query_vars['post_type'];
			
			// empty, so we'll have to create post_type setting			
			if(empty($post_types)) {
				if(is_home()) $post_types = array('post', 'namaste_course');
				else $post_types = array('post', 'namaste_course');
			}
			
			// not empty, so let's just add
			if(!empty($post_types) and is_array($post_types)) {
				$post_types[] = 'namaste_course';				
				$query->set( 'post_type', $post_types );
			}
		}		
		return $query;
	}
	
	static function meta_boxes() {
		add_meta_box("namaste_meta", __("Namaste! Settings", 'namaste'), 
							array(__CLASS__, "print_meta_box"), "namaste_course", 'normal', 'high');
		add_meta_box("namaste_advanced_reports_hint", __("Advanced Reports", 'namaste'), 
							array(__CLASS__, "print_reports_box"), "namaste_course", 'side', 'default');					
	}
	
	static function print_meta_box($post) {
			global $wpdb;
			
			// select lessons in this course
			$_lesson = new NamasteLMSLessonModel();
			$lessons = $_lesson -> select($post->ID);
						
			// required lessons
			$required_lessons = get_post_meta($post->ID, 'namaste_required_lessons', true);	
			if(!is_array($required_lessons)) $required_lessons = array();
			
			// enrollment - for now free or admin approved, in the future also paid
			$enroll_mode = get_post_meta($post->ID, 'namaste_enroll_mode', true);
			
			$fee = get_post_meta($post->ID, 'namaste_fee', true);
			$currency = get_option('namaste_currency');
			
			$use_points_system = get_option('namaste_use_points_system');
			$award_points = get_post_meta($post->ID, 'namaste_award_points', true);
			if($award_points === '') $award_points = get_option('namaste_points_course');
			
			// other courses
			$other_courses = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->posts} tP			
			WHERE post_type = 'namaste_course'  AND (post_status='publish' OR post_status='draft') 
			AND ID!=%d ORDER BY post_title", $post->ID));

			// course will be accessible after these course(s) are completed			
			$course_access = get_post_meta($post->ID, 'namaste_access', true);	
			if(!is_array($course_access)) $course_access = array();
			
			wp_nonce_field( plugin_basename( __FILE__ ), 'namaste_noncemeta' );			  
			if(@file_exists(get_stylesheet_directory().'/namaste/course-meta-box.php')) require get_stylesheet_directory().'/namaste/course-meta-box.php';
			else require(NAMASTE_PATH."/views/course-meta-box.php");
	}
	
	static function print_reports_box($post) {
			global $wpdb;
			
			// for now simply remind there are reports
			// or hint to the plugin. In the future we'll allow some basic report to be shown right in the box
			if(is_plugin_active('namaste-reports/namaste-reports.php')) {
				echo "<p>".sprintf(__('For advanced reports on this course, <a href="%s">click here</a>.', 'namaste'), 'admin.php?page=namasterep&action=courses&course_id='.$post->ID)."</p>";
			} else {
				echo "<p>".sprintf(__('You can get <b>advanced reports</b> on this course if you install the <a href="%s" target="_blank">Namaste! Reports</a> plugin.', 'namaste'), 'http://namaste-lms.org/reports.php"')."</p>";
			}
	}
	
	static function save_course_meta($post_id) {
			global $wpdb;
			
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  return;		
	  		if ( empty($_POST['namaste_noncemeta']) or !wp_verify_nonce( $_POST['namaste_noncemeta'], plugin_basename( __FILE__ ) ) ) return;  	  		
	  		if ( !current_user_can( 'edit_post', $post_id ) ) return;
	 	 	if ('namaste_course' != $_POST['post_type']) return;
			
			update_post_meta($post_id, "namaste_enroll_mode", $_POST['namaste_enroll_mode']);
			update_post_meta($post_id, "namaste_required_lessons", $_POST['namaste_required_lessons']);			
			update_post_meta($post_id, "namaste_fee", $_POST['namaste_fee']);
			update_post_meta($post_id, "namaste_access", $_POST['namaste_access']);
			if(isset($_POST['namaste_award_points'])) update_post_meta($post_id, "namaste_award_points", $_POST['namaste_award_points']);
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
	static function enroll_text($content) {
		global $wpdb, $user_ID, $post;
				
		if(@$post->post_type != 'namaste_course') return $content;
		
		// track the visit
		if(is_user_logged_in()) NamasteTrack::visit('course', $post->ID, $user_ID);
		
		// if the shortcode is there don't show this
		if(strstr($content, '[namaste-enroll]')) return $content;
		
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
		
		// if the course is already completed, don't mark it again
		if($student_course->status == 'completed') return false;
		
		$course = get_post($course_id);
		
		$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_COURSES." SET status = 'completed',
			completion_date = %s, completion_time=%s WHERE id=%d", 
			date("Y-m-d", current_time('timestamp')), current_time('mysql'), $student_course->id));
			
		// should we assign certificates?
		$_cert = new NamasteLMSCertificateModel();
		$_cert -> complete_course($course_id, $student_id);
		
		// award points?
		$use_points_system = get_option('namaste_use_points_system');
		if($use_points_system) {
			$award_points = get_post_meta($course_id, 'namaste_award_points', true);
			if($award_points === '') $award_points = get_option('namaste_points_course');
			if($award_points) {				
				NamastePoint :: award($student_id, $award_points, sprintf(__('Received %d points for completing course "%s".', 'namaste'), $award_points, $course->post_title));
			}
		}
		
		// add custom action
		do_action('namaste_completed_course', $student_id, $course_id);	
		
		// insert in history
	  $wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_HISTORY." SET
			user_id=%d, date=CURDATE(), datetime=NOW(), action='completed_course', value=%s, num_value=%d",
			$student_id, sprintf(__('Completed course "%s"', 'namaste'), $course->post_title), $course_id));
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
		
		// checks from other plugins, for example Namaste PRO
		$no_access = $message = null;
		list($no_access, $message) = apply_filters('namaste-course-access', array(false, ''), $student_id, $course_id);
		// echo $no_access.'a';
		if($no_access) wp_die($message);
		
		// check for course access requirements
		$course_access = get_post_meta($course_id, 'namaste_access', true);

		if(!empty($course_access) and is_array($course_access)) {
			// check if there is any unsatisfied requirement
			foreach($course_access as $required_course) {
				$is_completed = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_COURSES."
					WHERE user_id=%d AND course_id=%d AND status='completed'", $student_id, $required_course));
				if(!$is_completed) wp_die(__('You cannot enroll this course - other courses have to be completed first.', 'namaste'));	
			}
		}
		
		$result = $wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_STUDENT_COURSES." SET
					course_id = %d, user_id = %d, status = %s, enrollment_date = %s, enrollment_time=%s,
					completion_date='1900-01-01', comments=''",
					$course_id, $student_id, $status, date("Y-m-d", current_time('timestamp')), current_time('mysql') ) );
		if($result !== false) {                        					
			do_action('namaste_enrolled_course', $student_id, $course_id, $status);
			
			// insert in history
			$course = get_post($course_id);
			$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_HISTORY." SET
				user_id=%d, date=CURDATE(), datetime=NOW(), action='enrolled_course', value=%s, num_value=%d",
				$student_id, sprintf(__('Enrolled in course %s. Status: %s', 'namaste'), $course->post_title, $status), $course_id));
		}			
	}
	
	// displays enroll buttons
	function enroll_buttons($course, $is_manager) {
		global $user_ID;
		
		$currency = $this->currency;
		$accept_other_payment_methods = $this->accept_other_payment_methods;
		$accept_paypal = $this->accept_paypal;
		$accept_stripe = $this->accept_stripe;		
		$stripe = $this->stripe;
		
		// checked for prerequisites
		list($can_enroll, $enroll_prerequisites) = $this->enroll_prerequisites($course);
		
		// can't enroll?
		if(empty($can_enroll)) {
			return $enroll_prerequisites;
		}			
		
		$output = '';	
		if(!empty($course->fee) and !$is_manager) {	
			// coupon codes and discount filters from other plugins
			$output = apply_filters('namaste-coupon-form', $output);
			$course->fee = apply_filters('namaste-coupon-applied', $course->fee); // coupon code from other plugin?		
				
			if($accept_paypal or $accept_other_payment_methods) { 
				$url = admin_url("admin-ajax.php?action=namaste_ajax&type=course_payment");
				$box_title = __('Payment for course', 'namaste');
				$output .= "<strong><a href='#' onclick=\"namasteEnrollCourse('".$box_title."', ".$course->post_id.", ".$user_ID.", '".$url."');return false;\">".__('Enroll for', 'namaste').' '.$currency." ".$course->fee."</a></strong>"; 
			}
			if($accept_stripe) {
				$output .= '<form method="post">
				  <script src="https://checkout.stripe.com/v2/checkout.js" class="stripe-button"
				          data-key="'.$stripe['publishable_key'].'"
				          data-amount="'.($course->fee*100).'" data-description="'.$course->post_title.'" data-currency="'.$currency.'"></script>
				<input type="hidden" name="stripe_pay" value="1">
				<input type="hidden" name="course_id" value="'.$course->post_id.'">
				</form>';
			} // end if accept stripe
		}	
		else {
			$output .= '<form method="post">
				<input type="submit" value="'.__('Click to Enroll', 'namaste').'">
				<input type="hidden" name="enroll" value="1">
				<input type="hidden" name="course_id" value="'.$course->post_id.'">
			</form>';				
		}  
		
		return $output;
	} // end enroll buttons
	
	// adds visits column in manage courses page
	static function manage_post_columns($columns) {
		// add this after title column 
		$final_columns = array();
		foreach($columns as $key=>$column) {			
			$final_columns[$key] = $column;
			if($key == 'title') {				
				$final_columns['namaste_course_visits'] = __( 'Visits (unique/total)', 'namaste' );
			}
		}
		return $final_columns;
	}
	
	// actually displaying the course column value
	static function custom_columns($column, $post_id) {
		switch($column) {			
			case 'namaste_course_visits':
				// get unique and total visits
				list($total, $unique) = NamasteTrack::get_visits('course', $post_id);
				echo $unique.' / '.$total;
			break;
		}
	}
	
	// check course pre-requisites
	// returns array($can_enroll, $enroll_prerequisites)
	function enroll_prerequisites($course) {
		global $wpdb, $user_ID;
		// can enroll? or are there unsatisfied pre-requisites
		$can_enroll = true;		
		$enroll_prerequisites = '';
		// check for course access requirements
		$course_access = get_post_meta($course->post_id, 'namaste_access', true);
		if(!empty($course_access) and is_array($course_access)) {
			$enroll_prerequisites = __('These courses should be completed before you can enroll:', 'namaste');
			
			// check if there is any unsatisfied requirement
			foreach($course_access as $required_course) {
				$is_completed = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_COURSES."
					WHERE user_id=%d AND course_id=%d AND status='completed'", $user_ID, $required_course));
				if(!$is_completed) {
					$can_enroll = false; // even one failed is enough;
					$required_course_post = get_post($required_course);
					$enroll_prerequisites .= ' <b>' . $required_course_post->post_title. '</b>;';
				}
			} // end foreach course access
		}
		
		return array($can_enroll, $enroll_prerequisites);
	} // end enroll_prerequisites()
}