<?php
// procedural function to dispatch ajax requests
function namaste_ajax() {
	global $wpdb, $user_ID;	
	
	$type = empty($_POST['type']) ? $_GET['type'] : $_POST['type'];	
	
	switch($type) {
		case 'lessons_for_course':
			$_lesson = new NamasteLMSLessonModel();
			echo $_lesson->select($_POST['course_id'], 'json');
		break;
		
		// load notes for student homework
		case 'load_notes':
			// unless I am manager I can see other user's notes
			if($user_ID != $_GET['student_id'] and !current_user_can('namaste_manage')) wp_die('You are not allowed to see these notes.', 'namaste');	
		
			// select notes
			$notes = $wpdb->get_results($wpdb->prepare("SELECT tN.*, tU.user_login as username
			  FROM ".NAMASTE_HOMEWORK_NOTES." tN JOIN {$wpdb->users} tU ON tU.ID = tN.teacher_id
				WHERE homework_id=%d AND student_id=%d ORDER BY tN.id DESC", $_GET['homework_id'], $_GET['student_id']));
				
			// select homework
			$homework = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_HOMEWORKS." WHERE id=%d", $_GET['homework_id']));	
				
			require(NAMASTE_PATH."/views/homework-notes.php");	
		break;
		
		// show lesson progress
		case 'lesson_progress':
			// if i am not manager I can see only my own todo
			if(!current_user_can('namaste_manage') and $user_ID != $_GET['student_id']) die(__("You are not allowed to view this", 'namaste'));
			
			// select lesson and student
			$lesson = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID=%d", $_GET['lesson_id']));
			$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID=%d", $_GET['student_id']));		
			
			$todo = NamasteLMSLessonModel :: todo($_GET['lesson_id'], $_GET['student_id']);
			
			require(NAMASTE_PATH."/views/lesson-todo.php");		
		break;
		
		// display payment screen for a course
		case 'course_payment':
			// select course
			$course = $wpdb -> get_row( $wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID=%d", $_GET['course_id']));
			$fee = get_post_meta($course->ID, 'namaste_fee', true);
			$currency = get_option('namaste_currency');
			$accept_other_payment_methods = get_option('namaste_accept_other_payment_methods');
			
			if($accept_other_payment_methods) {
				$other_payment_methods = stripslashes(get_option('namaste_other_payment_methods'));
				$other_payment_methods = str_replace('{{course-id}}', $course->ID, $other_payment_methods);
				$other_payment_methods = str_replace('{{course-name}}', $course->post_title, $other_payment_methods);
				$other_payment_methods = str_replace('{{user-id}}', $_GET['student_id'], $other_payment_methods);
				$other_payment_methods = str_replace('{{amount}}', $_GET['fee'], $other_payment_methods);
			}
			
			require(NAMASTE_PATH."/views/course-pay.php");	
		break;
	}
	exit;
}