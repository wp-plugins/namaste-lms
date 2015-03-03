<?php
class NamasteLMSHomeworkModel {
	static function manage() {
		global $wpdb, $user_ID;
		$_course = new NamasteLMSCourseModel();
		$_lesson = new NamasteLMSLessonModel();
		
		$multiuser_access = 'all';
		$multiuser_access = NamasteLMSMultiUser :: check_access('homework_access');
				
		// select courses
		$courses = $_course -> select();
		$courses = apply_filters('namaste-homeworks-select-courses', $courses);
		
		// if course and lesson are selected, populate two variables for displaying titles etc
		if(!empty($_GET['course_id'])) $this_course = $_course -> select($_GET['course_id']);
		if(!empty($_GET['lesson_id'])) $this_lesson = $_lesson -> select($_GET['course_id'], 'single', $_GET['lesson_id']);
		
		switch(@$_GET['do']) {
			case 'add':
				// apply permissions from other plugins 
				do_action('namaste-check-permissions', 'course', $_GET['course_id']);
				if(!empty($_POST['ok'])) {
						$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_HOMEWORKS." SET
						course_id=%d, lesson_id=%d, title=%s, description=%s, accept_files=%d, 
						award_points=%d, editor_id=%d",
						$_GET['course_id'], $_GET['lesson_id'], $_POST['title'], 
						$_POST['description'], @$_POST['accept_files'], @$_POST['award_points'], $user_ID));	
						
						$id = $wpdb->insert_id;		
						
						do_action('namaste_add_homework', $id);		
					
						//$_SESSION['namaste_flash'] = __('Homework added', 'namaste');
						namaste_redirect("admin.php?page=namaste_homeworks&course_id=$_GET[course_id]&lesson_id=$_GET[lesson_id]");
				}			
			
				if(@file_exists(get_stylesheet_directory().'/namaste/homework.php')) require get_stylesheet_directory().'/namaste/homework.php';
				else require(NAMASTE_PATH."/views/homework.php");
			break;		
			
			case 'edit':
				// apply permissions from other plugins 
				do_action('namaste-check-permissions', 'homework', $_GET['id']);
				
				if($multiuser_access == 'own') {
					$homework = self::select($wpdb->prepare(' WHERE id=%d ', $_GET['id']));
					$homework = $homework[0];
					if($homework->editor_id != $user_ID) wp_die(__('You are not allowed to edit or delete this assignment', 'namaste'));
				}				
				
				if(!empty($_POST['del'])) {
					 self::delete($_GET['id']);
					 
					 //$_SESSION['namaste_flash'] = __('Homework deleted', 'namaste');
					 namaste_redirect("admin.php?page=namaste_homeworks&course_id=$_GET[course_id]&lesson_id=$_GET[lesson_id]");
				}			
			
				if(!empty($_POST['ok'])) {
						$wpdb->query($wpdb->prepare("UPDATE ".NAMASTE_HOMEWORKS." SET
						course_id=%d, lesson_id=%d, title=%s, description=%s, accept_files=%d, award_points=%d
						WHERE id=%d",
						$_GET['course_id'], $_GET['lesson_id'], $_POST['title'], 
						$_POST['description'], @$_POST['accept_files'], @$_POST['award_points'], $_GET['id']));		
						
						do_action('namaste_save_homework', $_GET['id']);					
					
						//$_SESSION['namaste_flash'] = __('Homework saved', 'namaste');
						namaste_redirect("admin.php?page=namaste_homeworks&course_id=$_GET[course_id]&lesson_id=$_GET[lesson_id]");
				}			
				
				// select homework
				$homework = self::select($wpdb->prepare(' WHERE id=%d ', $_GET['id']));
				$homework = $homework[0];
			
				if(@file_exists(get_stylesheet_directory().'/namaste/homework.php')) require get_stylesheet_directory().'/namaste/homework.php';
				else require(NAMASTE_PATH."/views/homework.php");
			break;			
			
			default:
				// if course is selected, find lessons
				if(!empty($_GET['course_id'])) {
					$lessons = $_lesson->select($_GET['course_id']);
				}			
			
				// list existing homeworks if course and lesson are selected
				if(!empty($_GET['course_id']) and !empty($_GET['lesson_id'])) {
					// apply permissions from other plugins - this allows other plugins to die here if user can't access the course
					do_action('namaste-check-permissions', 'course', $_GET['course_id']);
					
					$own_sql = '';
					if($multiuser_access == 'own') $own_sql = $wpdb->prepare(" AND tH.editor_id=%d ", $user_ID);
					
					$homeworks = $wpdb->get_results($wpdb->prepare("SELECT tH.*, COUNT(tS.id) as solutions 
						FROM ".NAMASTE_HOMEWORKS." tH LEFT JOIN ".NAMASTE_STUDENT_HOMEWORKS." tS ON tS.homework_id = tH.id
						WHERE tH.course_id=%d AND tH.lesson_id=%d	$own_sql 
						GROUP BY tH.id ORDER BY tH.title", 
						$_GET['course_id'], $_GET['lesson_id']));
				} 
				
				if(@file_exists(get_stylesheet_directory().'/namaste/homeworks.php')) require get_stylesheet_directory().'/namaste/homeworks.php';
				else require(NAMASTE_PATH."/views/homeworks.php");
			break;
		}
	}
	
	// shows homeworks assigned to a lesson
	static function lesson_homeworks($in_shortcode = false) {
		 global $wpdb, $user_ID, $post;
		 
		 // not my own homeworks? I need to have manage caps then
		 $manager_mode = false;
		 if($user_ID != $_GET['student_id']) {
		 		if(!current_user_can('namaste_manage')) wp_die(__('You are not allowed to see this page', 'namaste'));
		 		$manager_mode = true;		 		
		 }
		 $student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID=%d", $_GET['student_id']));
		 
		 // select lesson
		 $lesson = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID=%d", $_GET['lesson_id']));
		 // course ID
		 $course_id = get_post_meta($lesson->ID, 'namaste_course', true);
		 
		 // select the homeworks assigned to this lesson
		 $homeworks = self :: select($wpdb->prepare("WHERE lesson_id = %d", $lesson->ID)); 
		 $ids = array(0);
		 foreach($homeworks as $homework) $ids[] = $homework->id;
		 $id_sql = implode(", ", $ids);
		 
		 // select & match student solutions for each homework
		 $solutions = $wpdb -> get_results( $wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_HOMEWORKS."
		 	WHERE student_id = %d AND homework_id IN ($id_sql) ORDER BY id", $_GET['student_id']) );	
		 	
		 // select & match notes for each homework
		 $notes = $wpdb -> get_results($wpdb->prepare("SELECT * FROM ".NAMASTE_HOMEWORK_NOTES." 
		 	WHERE homework_id IN ($id_sql) AND student_id = %d", $_GET['student_id']));	
		 	
		 	
		 foreach($homeworks as $cnt=>$homework) {
		 		$homework_solutions = array();
		 		$homework_notes = array();
		 		
		 		foreach($solutions as $solution) {
		 			if($solution -> homework_id == $homework->id) $homework_solutions[] = $solution; 
		 		}
		 		
		 		foreach($notes as $note) {
		 			if($note->homework_id == $homework->id) $homework_notes[] = $note;
		 		}
		 		
		 		// define homework status - if even 1 solution is approved, the homework status is true
		 		$homeworks[$cnt]->status = false;
		 		foreach($homework_solutions as $solution) {
		 			if($solution->status == 'approved') $homeworks[$cnt]->status = true;
		 		}
		 		
		 		$homeworks[$cnt]->solutions = $homework_solutions;
		 		$homeworks[$cnt]->notes = $homework_notes;
		 }
		 
		 wp_enqueue_script('thickbox',null,array('jquery'));
		 wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
		 if(@file_exists(get_stylesheet_directory().'/namaste/lesson-homeworks.php')) require get_stylesheet_directory().'/namaste/lesson-homeworks.php';
		  else require(NAMASTE_PATH."/views/lesson-homeworks.php");
	}
	
	// select homeworks
	static function select($where) {
		global $wpdb;
		
		$homeworks = $wpdb -> get_results("SELECT * FROM ".NAMASTE_HOMEWORKS." $where ORDER BY id");
		
		return $homeworks;
	}
	
	// delete homework
	// for the moment delete only the DB record, but for the future 
	// consider deleting the solutions along with their files
	static function delete($id) {
			global $wpdb;
			
			$wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_HOMEWORKS." WHERE id=%d", $id));
	}
	
	// full select homework - with lesson and course (used in few places)
	static function full_select($id) {
		global $wpdb;
		$_course = new NamasteLMSCourseModel();		
		$_lesson = new NamasteLMSLessonModel();
		
		// select this homework and lesson
		$homework = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_HOMEWORKS."
			WHERE id=%d", $id));			
		// select course
		$course = $_course->select($homework->course_id);		
		// select lesson
		$lesson = $_lesson->select($course->ID, 'single', $homework->lesson_id);	
		
		return array($homework, $course, $lesson);
	}
}