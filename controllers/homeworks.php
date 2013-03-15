<?php
class NamasteLMSHomeworkController {
	function submit_solution() {
		global $wpdb, $user_ID;
		$_course = new NamasteLMSCourseModel();
		
		list($homework, $course, $lesson) = NamasteLMSHomeworkModel::full_select($_GET['id']);
		
		// am I enrolled?
		if(!NamasteLMSStudentModel::is_enrolled($user_ID, $course->ID)) wp_die(__('You are not enrolled in this course!',
			'namaste'));
			
		// now submit
		if(!empty($_POST['ok'])) {
			if(empty($_POST['content'])) wp_die(__('You cannot submit an empty solution', 'namaste'));			
			
			// avoid duplicates
			$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".NAMASTE_STUDENT_HOMEWORKS."
				WHERE student_id=%d AND homework_id=%d AND content=%s", $user_ID, $homework->id,
				$_POST['content']));
			if(!$exists) {
				$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_STUDENT_HOMEWORKS." SET
					homework_id=%d, student_id=%d, status='pending', date_submitted=CURDATE(), 
					content=%s, file=''",
					$homework->id, $user_ID, $_POST['content']));
			}	 			
			
			require(NAMASTE_PATH."/views/solution-submitted.php");
		}
		else require(NAMASTE_PATH."/views/submit-solution.php");		
	}
	
	// teacher views, approves, rejects submitted solutions
	function view() {
		global $wpdb, $user_ID;
		
		$student_id = empty($_GET['student_id'])?$user_ID : $_GET['student_id'];
		if(!current_user_can('namaste_manage') and $student_id!=$user_ID) wp_die(__('You are not allowed to see these solutions', 'namaste'));
		$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID=%d", $student_id));
		
		list($homework, $course, $lesson) = NamasteLMSHomeworkModel::full_select($_GET['id']);
		
		// approve or reject solution
		if(!empty($_POST['change_status'])) {
			if(!current_user_can('namaste_manage')) wp_die(__('You are not allowed to do this', 'namaste'));
			
			$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_HOMEWORKS." SET
				status=%s WHERE id=%d", $_POST['status'], $_POST['solution_id']));
			
			// maybe complete the lesson if the status is approved 				
			if($_POST['status']=='approved' and NamasteLMSLessonModel::is_ready($lesson->ID, $student_id)) {
				NamasteLMSLessonModel::complete($lesson->ID, $student_id);
			}							
		}
		
		// select submitted solutions
		$solutions = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_HOMEWORKS."
			WHERE student_id=%d AND homework_id=%d", $student_id, $homework->id));
		
		require(NAMASTE_PATH."/views/view-solutions.php");
	}
}