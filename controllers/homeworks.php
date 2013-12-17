<?php
class NamasteLMSHomeworkController {
	static function submit_solution() {
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
				$file = $file_blob = '';
				if($homework->accept_files and !empty($_FILES['file']['tmp_name'])) {
					$file_blob = file_get_contents($_FILES['file']['tmp_name']);
					$file = $_FILES['file']['name'];
				}				
				
				$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_STUDENT_HOMEWORKS." SET
					homework_id=%d, student_id=%d, status='pending', date_submitted=CURDATE(), 
					content=%s, file=%s, fileblob=%s",
					$homework->id, $user_ID, $_POST['content'], $file, $file_blob));
			}	 			
			
			do_action('namaste_submitted_solution', $user_ID, $homework->id);
			
			// insert in history
			$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_HISTORY." SET
				user_id=%d, date=CURDATE(), datetime=NOW(), action='submitted_solution', value=%s, num_value=%d",
				$user_ID, sprintf(__('Submitted solution to assignment "%s"', 'namaste'), $homework->title), $homework->id));
			
			require(NAMASTE_PATH."/views/solution-submitted.php");
		}
		else require(NAMASTE_PATH."/views/submit-solution.php");		
	}
	
	// teacher views, approves, rejects submitted solutions
	static function view() {
		global $wpdb, $user_ID;
		
		$student_id = empty($_GET['student_id'])?$user_ID : $_GET['student_id'];
		if(!current_user_can('namaste_manage') and $student_id!=$user_ID) wp_die(__('You are not allowed to see these solutions', 'namaste'));
		$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID=%d", $student_id));
		
		list($homework, $course, $lesson) = NamasteLMSHomeworkModel::full_select($_GET['id']);
		
		// approve or reject solution
		if(!empty($_POST['change_status'])) self::change_solution_status($lesson, $student_id);
		
		$use_grading_system = get_option('namaste_use_grading_system');
		$grades = explode(",", stripslashes(get_option('namaste_grading_system')));
		// give grade on a solution
		if($use_grading_system and !empty($_POST['grade_solution']) and current_user_can('namaste_manage')) {
			$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_HOMEWORKS." SET grade=%s WHERE id=%d", $_POST['grade'], $_POST['id']));
			do_action('namaste_graded_homework', $_POST['id'], $_POST['grade']);
		}
		
		// select submitted solutions
		$solutions = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_HOMEWORKS."
			WHERE student_id=%d AND homework_id=%d ORDER BY id DESC", $student_id, $homework->id));
		
		require(NAMASTE_PATH."/views/view-solutions.php");
	}
	
	// view everyone's solutions ion a homework
	static function view_all() {
		global $wpdb;
		
		list($homework, $course, $lesson) = NamasteLMSHomeworkModel::full_select($_GET['id']);
		
		$use_grading_system = get_option('namaste_use_grading_system');
		
		// approve or reject solution
		if(!empty($_POST['change_status'])) self::change_solution_status($lesson);
		
		// select submitted solutions
		$solutions = $wpdb -> get_results($wpdb->prepare("SELECT tH.*, tU.user_login as user_login 
			FROM ".NAMASTE_STUDENT_HOMEWORKS." tH JOIN {$wpdb->users} tU ON tH.student_id = tU.ID
			WHERE homework_id=%d ORDER BY id DESC", $homework->id));
			
		$show_everyone = true;
		require(NAMASTE_PATH."/views/view-solutions.php");
	}
	
	// approve or reject a homework solution
	static function change_solution_status($lesson, $student_id = NULL) {
		global $wpdb;
		
		if(!current_user_can('namaste_manage')) wp_die(__('You are not allowed to do this', 'namaste'));
		
		$solution = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_HOMEWORKS." WHERE id=%d", $_POST['solution_id']));
		if(!$student_id)  $student_id = $solution->student_id;
		$homework = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_HOMEWORKS." WHERE id=%d", $solution->homework_id));
			
		$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_STUDENT_HOMEWORKS." SET
			status=%s WHERE id=%d", $_POST['status'], $_POST['solution_id']));
			
		do_action('namaste_change_solution_status', $student_id, $_POST['solution_id'], $_POST['status']);	
		
		// insert in history
		$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_HISTORY." SET
			user_id=%d, date=CURDATE(), datetime=NOW(), action='solution_processed', value=%s, num_value=%d",
			$student_id, sprintf(__('Solution to assignment %s was %s', 'namaste'), $homework->title, $_POST['status']), $_POST['solution_id']));
		
		// award points?
		if($_POST['status']=='approved' and get_option('namaste_use_points_system')) {			
			if($homework->award_points) {
				NamastePoint :: award($student_id, $homework->award_points, sprintf(__('Received %d points for completing assignment "%s".', 'namaste'), 
					$homework->award_points, $homework->title));
			}
		}
		
		// maybe complete the lesson if the status is approved 				
		if($_POST['status']=='approved' and NamasteLMSLessonModel::is_ready($lesson->ID, $student_id)) {
			NamasteLMSLessonModel::complete($lesson->ID, $student_id);
		}		
	} // end change_solution_status
	
	// download solution file
	static function download_solution() {
		global $wpdb, $user_ID;
		
		$solution = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_HOMEWORKS." WHERE id=%d", $_GET['id']));
		
		if(empty($solution->fileblob)) wp_die(__("There is nothing to download.", 'namaste'));
		
		if(!current_user_can('namaste_manage') and $user_ID != $solution->student_id) wp_die(__('You can download only your own solutions.', 'namaste'));
		
		// select fileblob
		// $fileblob = $wpdb->get_var($wpdb->prepare("SELECT BINARY fileblob FROM ".NAMASTE_STUDENT_HOMEWORKS." WHERE id=%d", $solution->id)); 
				
		// send download headers
		header('Content-Disposition: attachment; filename="'.$solution->file.'"');				
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Description: File Transfer");
		header("Content-Length: " . strlen($solution->fileblob)); 
		
		echo $solution->fileblob;
	}
}