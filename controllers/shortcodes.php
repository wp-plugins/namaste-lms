<?php
// various Namaste shortcodes
class NamasteLMSShortcodesController {
	// what's todo in a lesson or course
   static function todo() {
   	global $post, $user_ID;
   	
   	if(!is_user_logged_in()) return "";
   	
   	if($post->post_type == 'namaste_lesson') {   		
   		$todo = NamasteLMSLessonModel :: todo($post->ID, $user_ID);   		
   		ob_start();
   		require(NAMASTE_PATH."/views/lesson-todo.php");
   		if(!empty($todo['todo_nothing'])) _e('This lesson has been completed.', 'namaste');
   		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;		
   	}
   	
   	if($post->post_type == 'namaste_course') {
   		$_course = new NamasteLMSCourseModel();
   		
   		$required_lessons = $_course->required_lessons($post->ID, $user_ID);
   		
   		$content = "";
   		
   		if(!empty($required_lessons)) {
   			$content .= "<ul>\n";
   			foreach($required_lessons as $lesson) {
   				$content .= "<li".($lesson->namaste_completed?' class="namaste-completed" ':' class="namaste-incomplete" ')."><a href='".get_permalink($lesson->ID)."'>".$lesson->post_title."</a> - ";
					if($lesson->namaste_completed) $content .= __('Completed', 'namaste');
					else $content .= __('Not completed', 'namaste');			
   				
   				$content .= "</li>\n";
   			}   			
   			$content .= "</ul>";
   		}
   		
   		return $content;
   	}
   } // end todo
   
   // display enroll button
   static function enroll() {
   	global $wpdb, $user_ID, $user_email, $post;
   	
   	if(!is_user_logged_in()) {
   		return __('You need to be logged in to enroll in this course', 'namaste');
   	}
   	
   	$enrolled = $wpdb -> get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_STUDENT_COURSES.
			" WHERE user_id = %d AND course_id = %d", $user_ID, $post->ID));
			
		if(empty($enrolled->id)) {
			$currency = get_option('namaste_currency');
			$is_manager = current_user_can('namaste_manage');
			$_course = new NamasteLMSCourseModel();
						
			// stripe integration goes right on this page
			$accept_stripe = get_option('namaste_accept_stripe');
			$accept_paypal = get_option('namaste_accept_paypal');
			$accept_other_payment_methods = get_option('namaste_accept_other_payment_methods');
			if($accept_stripe) $stripe = NamasteStripe::load();
			
			if(!empty($_POST['stripe_pay'])) {
				 NamasteStripe::pay($currency);			
				 namaste_redirect($_SERVER['REQUEST_URI']);
			}	
		
			if(!empty($_POST['enroll'])) {
				$mesage = NamasteLMSCoursesController::enroll($is_manager);
				namaste_redirect($_SERVER['REQUEST_URI']);
			}	
			
			$_course->currency = $currency;
			$_course->accept_other_payment_methods = $accept_other_payment_methods;
			$_course->accept_paypal = $accept_paypal;
			$_course->accept_stripe = $accept_stripe;		
			$_course->stripe = $stripe;		
			wp_enqueue_script('thickbox',null,array('jquery'));
			wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');	 
			$post->post_id = $post->ID;
			$post->fee = get_post_meta($post->ID, 'namaste_fee', true); 
			return $_course->enroll_buttons($post, $is_manager);
		}	
		else {
			switch($enrolled->status) {
				case 'enrolled': return __('You are enrolled in this course.', 'namaste'); break;
				case 'pending': return __('Your enroll request is received. Waiting for manager approval.', 'namaste'); break;
				case 'completed': return __('You have completed this course.', 'namaste'); break;
				case 'rejected': return __('Your enrollment request is rejected.', 'namaste'); break;
			}
		}
	}
	
	// display user points
	static function points($atts) {
		global $user_ID;
		$user_id = $user_ID;
		if(!empty($atts[0]) and is_numeric($atts[0])) $user_id = $atts[0];		
		
		$points = get_user_meta($user_id, 'namaste_points', true);
		return $points;
	}
	
	// displays leaderboard by points
	static function leaderboard($atts) {
		global $wpdb;
		
		$num_users = @$atts[0];
		if(!is_numeric($num_users)) $num_users = 10;
		
		$display = empty($atts[1]) ? 'usernames' : 'table';		

		// select top users
		$users = $wpdb->get_results($wpdb->prepare("SELECT tU.*, tM.meta_value as namaste_points FROM {$wpdb->users} tU JOIN {$wpdb->usermeta} tM
			ON tU.ID = tM.user_id AND tM.meta_key = 'namaste_points'
			ORDER BY namaste_points DESC LIMIT %d", $num_users));
		
		$html = "";
		if($display == 'usernames') {
			$html .= "<ol class='namaste-leaderboard'>";
			foreach($users as $user) $html.="<li>".$user->user_nicename."</li>";
			$html .= "</ol>";
		}
		else {
			$html .= "<table class='namaste-leaderboard'><tr><th>".__('User', 'namaste')."</th><th>".__('Points')."</th></tr>";
			foreach($users as $user) $html.="<tr><td>".$user->user_nicename."</td><td>".$user->namaste_points."</td></tr>";
			$html .="</table>";
		}
		
		return $html;
	}
	
	// display lessons in this course 
	// in table, just <ul>, or in user-defined HTML
	static function lessons() {
		// NYI
	}	
}