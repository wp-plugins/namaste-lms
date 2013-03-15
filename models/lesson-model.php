<?php
class NamasteLMSLessonModel {
	// custom post type Lesson	
	static function register_lesson_type() {
		$args=array(
			"label" => __("Namaste! Lessons", 'namaste'),
			"labels" => array
				(
					"name"=>__("Lessons", 'namaste'), 
					"singular_name"=>__("Lesson", 'namaste'),
					"add_new_item"=>__("Add New Lesson", 'namaste')
				),
			"public"=> true,
			"show_ui"=>true,
			"has_archive"=>true,
			"rewrite"=> array("slug"=>"namaste-lesson", "with_front"=>false),
			"description"=>__("This will create a new lesson in your Namaste! LMS.",'namaste'),
			"supports"=>array("title", 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'post-formats'),
			"taxonomies"=>array("category"),
			"show_in_nav_menus"=>'true',
			'show_in_menu' => 'namaste_options',
			"register_meta_box_cb"=>array(__CLASS__,"meta_boxes")
		);
		register_post_type( 'namaste_lesson', $args );
	}
	
	static function meta_boxes() {
		add_meta_box("namaste_meta", __("Namaste! Settings", 'namaste'), 
							array(__CLASS__, "print_meta_box"), "namaste_lesson", 'normal', 'high');
	}
	
	static function print_meta_box($post) {
		global $wpdb;
			
		$_course = new NamasteLMSCourseModel();
		
		// select all existing courses
		$courses = $_course -> select();
		
		// which courses do this lesson belong to?
		$course_id = get_post_meta($post->ID, 'namaste_course', true);
		
		// other lessons in this course
		$other_lessons = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->posts} tP
			JOIN {$wpdb->postmeta} tM ON tM.post_id = tP.ID AND tM.meta_key = 'namaste_course'
			AND tM.meta_value = %d
			WHERE post_type = 'namaste_lesson'  AND (post_status='publish' OR post_status='draft') 
			AND ID!=%d ORDER BY post_title",  $course_id, $post->ID));
			
		$lesson_access = get_post_meta($post->ID, 'namaste_access', true);	
		if(!is_array($lesson_access)) $lesson_access = array();
		$lesson_completion = get_post_meta($post->ID, 'namaste_completion', true);	
		if(!is_array($lesson_completion)) $lesson_completion = array();
		$required_homeworks = get_post_meta($post->ID, 'namaste_required_homeworks', true);	
		if(!is_array($required_homeworks)) $required_homeworks = array();
		$required_exam = get_post_meta($post->ID, 'namaste_required_exam', true);
		$required_grade = get_post_meta($post->ID, 'namaste_required_grade', true);
		
		// select assignments
		$homeworks = NamasteLMSHomeworkModel::select($wpdb->prepare(' WHERE lesson_id = %d', $post->ID));
				
		// select quizzes from Watu/WatuPRO
		$use_exams = get_option('namaste_use_exams');
		
		if(!empty($use_exams)) {
			if($use_exams = 'watu') {
					$exams_table = $wpdb->prefix.'watu_master';
					$grades_table = $wpdb->prefix.'watu_grading';
			}
			if($use_exams = 'watupro') {
					$exams_table = $wpdb->prefix.'watupro_master';
					$grades_table = $wpdb->prefix.'watupro_grading';
			}
			
			$exams = $wpdb->get_results("SELECT * FROM $exams_table ORDER BY name");
			
			// fill grades
			$grades = $wpdb->get_results("SELECT * FROM $grades_table ORDER BY id");
			
			// grades of the currently selected exam. Will be filled only if such is selected
			$required_grades = array(); 
			
			foreach($exams as $cnt=>$exam) {
					$exam_grades = array();
					foreach($grades as $grade) {
							if($grade->exam_id == $exam->ID) $exam_grades[] = $grade;
					}
					
					$exams[$cnt]->grades = $exam_grades;
					
					if($required_exam and $required_exam == $exam->ID) $required_grades = $exam_grades;
			}
		}
		
		wp_nonce_field( plugin_basename( __FILE__ ), 'namaste_noncemeta' );
		require(NAMASTE_PATH."/views/lesson-meta-box.php");
	}
	
	static function save_lesson_meta($post_id) {	
		global $wpdb;
			
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  return;		
	  	if ( !wp_verify_nonce( $_POST['namaste_noncemeta'], plugin_basename( __FILE__ ) ) ) return;  	  		
	  	if ( !current_user_can( 'edit_post', $post_id ) ) return;
	  	if ('namaste_lesson' != $_POST['post_type']) return;
	  	  		  
	  	update_post_meta($post_id, "namaste_course", $_POST['namaste_course']);	
	  	update_post_meta($post_id, "namaste_access", $_POST['namaste_access']);
	  	update_post_meta($post_id, "namaste_completion", $_POST['namaste_completion']);
	  	update_post_meta($post_id, "namaste_required_homeworks", $_POST['namaste_required_homeworks']);  	
	  	update_post_meta($post_id, "namaste_required_exam", $_POST['namaste_required_exam']);
	  	update_post_meta($post_id, "namaste_required_grade", $_POST['namaste_required_grade']);
	}
	
	// select lessons in course ID
	function select($course_id, $format = 'array', $id = null) {
		global $wpdb;
		
		$id_sql = '';
		if(!empty($id)) $id_sql = $wpdb->prepare(' AND tP.ID = %d ', $id);
		
		$lessons = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->posts} tP
			JOIN {$wpdb->postmeta} tM ON tM.post_id = tP.ID AND tM.meta_key = 'namaste_course'
			AND tM.meta_value = %d
			WHERE post_type = 'namaste_lesson'  AND (post_status='publish' OR post_status='draft') $id_sql
			ORDER BY post_title",  $course_id));
			
		if($format == 'array') return $lessons;
		
		if($format == 'single') return $lessons[0];
		
		if($format == 'json') echo json_encode($lessons);		
	}
	
	// students lessons in a selected course
	function student_lessons() {
		global $wpdb, $user_ID; 
		
		// student_id
		$student_id = (empty($_GET['student_id']) or !current_user_can('namaste_manage')) ? $user_ID : $_GET['student_id'];
				
		// select this student
		$student = $wpdb -> get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID=%d", $student_id));
		
		// select this course
		$course = $wpdb -> get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE id=%d", $_GET['course_id']));
		
		// am I enrolled?
		if(!current_user_can('namaste_manage')) {
			$enrolled = $wpdb -> get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_COURSES.
				" WHERE user_id = %d AND course_id = %d AND (status = 'enrolled' OR status = 'completed')", $student_id, $course->ID));
			if(!$enrolled) {
				_e("You must enroll in the course first before you can see the lessons", 'namaste');
				return false;
			}	
		} // end enrolled check	
		
		// change student-lesson status?
		if(!empty($_POST['change_status'])) {
				$result = NamasteLMSStudentModel :: lesson_status($student->ID, $_POST['lesson_id'], $_POST['status']);
				if(!$result) $error = __('The lesson cannot be completed because there are unsatisfied requirements', 'namaste');
		}
		
		// select lessons
		$lessons = self::select($course->ID);
		$ids = array(0);
		foreach($lessons as $lesson) $ids[] = $lesson->ID;
		$id_sql = implode(",", $ids);
		
		// select homeworks and match to lessons
		$homeworks = NamasteLMSHomeworkModel::select("WHERE lesson_id IN ($id_sql)");
		
		// using exams? select them too
		$use_exams = get_option('namaste_use_exams');
		$exams_table = ($use_exams == 'watu') ? $wpdb->prefix.'watu_master' : $wpdb->prefix.'watupro_master';
		$shortcode = ($use_exams == 'watu') ? 'WATU' : 'WATUPRO';
		
		// select student-lesson relation so we can match status
		$student_lessons = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_LESSONS."
			WHERE student_id = %d", $student_id));
		
		foreach($lessons as $cnt=>$lesson) {
			$lesson_homeworks = array();
			foreach($homeworks as $homework) {
				if($homework->lesson_id == $lesson->ID) $lesson_homeworks[] = $homework;
			}
			$lessons[$cnt]->homeworks = $lesson_homeworks;
			
			if($use_exams) {
				$required_exam = get_post_meta($lesson->ID, 'namaste_required_exam', true);
				if($required_exam) {
					$exam = $wpdb->get_row("SELECT tE.*, tP.id as post_id FROM $exams_table tE, {$wpdb->posts} tP
						WHERE tE.ID = $required_exam AND tP.post_content LIKE CONCAT('%[$shortcode ', tE.ID, ']%')
						AND (tP.post_type='post' OR tP.post_type='page') AND tP.post_status='publish' 
						AND post_title!=''");
						
					$lessons[$cnt]->exam = $exam;
				}					
			}
			
			// status
			$status = null;
			foreach($student_lessons as $l) {
				 if($l->lesson_id == $lesson->ID) $status = $l;
			}			
			
			if(empty($status->id)) {
				$lessons[$cnt]->status = __('Not started', 'namaste');
				$lessons[$cnt]->statuscode = -1;
			}
			else {
				if($status->status == 1) { 
					$lessons[$cnt]->status = __('Completed on', 'namaste') . 
					' ' . date(get_option('date_format'), strtotime($status->completion_date));
					$lessons[$cnt]->statuscode = 1;
				}
				else {
					$lessons[$cnt]->status = __('In progress', 'namaste');
					$lessons[$cnt]->statuscode = 0;
				}					
			} // end defining status
		}
		
		require(NAMASTE_PATH."/views/student-lessons.php");
	}
	
	// check if user can access the lesson, mark lesson as started
	static function access_lesson($content) {
		global $wpdb, $post, $user_ID;
		if($post->post_type != 'namaste_lesson') return $content;		
		$_course = new NamasteLMSCourseModel();
		
		if(!is_user_logged_in()) return __('You need to be logged in to access this lesson.', 'namaste');
		
		// enrolled in the course?
		$course_id = get_post_meta($post->ID, 'namaste_course', true);
		$course = $_course -> select($course_id);
		$enrolled = $wpdb -> get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_COURSES.
			" WHERE user_id = %d AND course_id = %d AND status = 'enrolled'", $user_ID, $course_id));
		if(!$enrolled) {
			$content = __('In order to see this lesson you first have to be enrolled in the course', 'namaste').' <b>"'.$course->post_title.'"</b>';
			return $content; // no need to run further queries
		}		
		
		// can access based on other lesson restriction?
		$lesson_access = get_post_meta($post->ID, 'namaste_access', true);	
		if(!is_array($lesson_access)) $lesson_access = array();
		$completed_lessons = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_LESSONS.
			" WHERE student_id = %d AND status = 1 ", $user_ID));
		$completed_ids = array(0);
		foreach($completed_lessons as $l) $completed_ids[] = $l->lesson_id;
		if(sizeof($lesson_access)) {
			$not_completed_ids = array();
			foreach($lesson_access as $access) {
				if(!in_array($access, $completed_ids)) $not_completed_ids[] = $access;
			}
		}			
		if(!empty($not_completed_ids)) {
			 $content = '<p>'.__('Before accessing this lesson you must complete the following lessons:','namaste').'</p>';			 
			 $content	.= '<ul>';
			
			 foreach($not_completed_ids as $id) {
			 		$not_completed = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE id=%d", $id));
			 		
			 		$content .= '<li><a href="'.get_permalink($id).'">'.$not_completed->post_title.'</a></li>';
			 }					 
			 
			 $content .= '</ul>';
			 return $content;
		}
		
		// mark as accessed now (if record does not exist)
		$lesson_completion = get_post_meta($post->ID, 'namaste_completion', true);		
		$status = empty($lesson_completion) ? 1 : 0;	
		$exists = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_LESSONS." 
			WHERE student_id=%d AND lesson_id=%d", $user_ID, $post->ID));
			
		if(empty($exists->id)) {
			  $wpdb -> query($wpdb->prepare("INSERT INTO ".NAMASTE_STUDENT_LESSONS." SET
			  	lesson_id=%d, student_id=%d, status=%d, completion_date = CURDATE()", 
			  	$post->ID, $user_ID, $status));
		} 
		
		// if ready, complete lesson
		// think about how to reduce these queries a little bit in the future
		if(self::is_ready($post->ID, $user_ID)) self::complete($post->ID, $user_ID);		
				
		return $content;
	} // end access_lesson
	
	// checks if the lesson is ready to be considered "completed" for a given student. 
	// I.e. checks if all the requirements are completed
	// $admin_check - when admin checks completeness, we'll ignore the requirement for 
	// completed status - because we want to check only the other reqs
	static function is_ready($lesson_id, $student_id, $admin_check = false) {
		global $wpdb;

		// first let's check for already completed status. If such is there, obviously the lesson is ready for completing
		$student_lesson = $wpdb -> get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_LESSONS."
			WHERE lesson_id=%d AND student_id=%d", $lesson_id, $student_id));				
		if(!empty($student_lesson->id) and $student_lesson->status == 1) return true;
				
		if(empty($student_lesson->id)) return false; // It can never be ready if it's not visited at all	
		
		if(!$admin_check) {
			// if admin has to manually approve the lesson and has not done this yet (if he done it, we'd have "completed"
			// status already and not reach this point at all), then the lesson is not ready
			$lesson_completion = get_post_meta($lesson_id, 'namaste_completion', true);	
			if(!is_array($lesson_completion)) $lesson_completion = array();
			
			if(in_array('admin_approval', $lesson_completion)) return false;
		}
		
		// Homeworks check
		$required_homeworks = get_post_meta($lesson_id, 'namaste_required_homeworks', true);	
		if(!is_array($required_homeworks)) $required_homeworks = array();
		if(!empty($required_homeworks)) {
			// select all completed homeworks of this student and see if all required are satisfied
			$completed_homeworks = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT(homework_id) FROM ".
				NAMASTE_STUDENT_HOMEWORKS." WHERE student_id=%d AND status='completed'", $student_id));
			$ids = array(0);
			foreach($completed_homeworks as $hw) $ids[] = $hw->homework_id;
			
			// if just one is not completed, return false
			foreach($required_homeworks as $homework) {
				if(!in_array($homework->id, $ids)) return false;
			}	
		}
		
		// Exam check
		$use_exams = get_option('namaste_use_exams');
		if(!empty($use_exams)) {
			$required_exam = get_post_meta($lesson_id, 'namaste_required_exam', true);
			$required_grade = get_post_meta($lesson_id, 'namaste_required_grade', true);
			
			if(!empty($required_exam)) {
				// see if there is taking record at all
				if($use_exams == 'watu') {
					$takings = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watu_takings 
						WHERE user_id=%d AND exam_id=%d",$student_id, $required_exam));
				}
				if($use_exams == 'watupro') {
					$takings = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}watupro_taken_exams 
						WHERE user_id=%d AND exam_id=%d",$student_id, $required_exam));						
				}
				
				if(empty($takings)) return false; // no takings at all, exam is not taken
			}
			
			if(!empty($required_grade)) {
				// let's make sure they have achieved the grade
				$achieved_grade = false;
				foreach($takings as $taking) {
					if(preg_match("/^".$required_grade."<p/", $taking->result) or (trim($required_grade) == trim($taking->result))) {
						$achieved_grade = true;
						break;
					}
				}
				
				if(!$achieved_grade) return false;
			}
		}
		
		return true;
	}
	
	// marks lesson as completed. If required, marks the corresponding course as completed as well
	static function complete($lesson_id, $student_id) {
		global $wpdb;
		$_course = new NamasteLMSCourseModel();
	
		// find the lesson
		$lesson = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID=%d", $lesson_id));
		$course_id = get_post_meta($lesson->ID, 'namaste_course', true);
		
		// get course
		$course = $_course->select($course_id);
		
		// mark lesson as completed - at this point we must have student-lesson record
		$student_lesson = $wpdb -> get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_LESSONS."
			WHERE lesson_id=%d AND student_id=%d", $lesson->ID, $student_id));
		if(empty($student_lesson->id)) return false;
		
		$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_LESSONS." 
		SET status = '1', completion_date = CURDATE() 
		WHERE id=%d", $student_lesson->id));
		
		// now see if course should be completed
		if($_course->is_ready($course_id, $student_id)) $_course->complete($course_id, $student_id);
		
		return true;
	}
	
	// checks if lesson is completed
	static function is_completed($lesson_id, $student_id) {
		global $wpdb;
		
		$id = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_LESSONS." 
			WHERE lesson_id=%d AND student_id=%d AND status='1'", $lesson_id, $student_id));
			
		return $id;		
	}
}