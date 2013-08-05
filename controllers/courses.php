<?php
class NamasteLMSCoursesController {
	// displays courses of a student, lets them enroll in a course
	static function my_courses() {
		global $wpdb, $user_ID, $user_email;
		
		$currency = get_option('namaste_currency');
		$is_manager = current_user_can('namaste_manage');
		$_course = new NamasteLMSCourseModel();
		
		// stripe integration goes right on this page
		$accept_stripe = get_option('namaste_accept_stripe');
		$accept_paypal = get_option('namaste_accept_paypal');
		$accept_other_payment_methods = get_option('namaste_accept_other_payment_methods');
		if($accept_stripe) {
				require_once(NAMASTE_PATH.'/lib/Stripe.php');
 
				$stripe = array(
				  'secret_key'      => get_option('namaste_stripe_secret'),
				  'publishable_key' => get_option('namaste_stripe_public')
				);
				 
				Stripe::setApiKey($stripe['secret_key']);
		}		
		
		if(!empty($_POST['stripe_pay'])) {
			 $token  = $_POST['stripeToken'];
			 $course = get_post($_POST['course_id']);
			 $fee = get_post_meta($course->ID, 'namaste_fee', true);
			 
			try {
				 $customer = Stripe_Customer::create(array(
			      'email' => $user_email,
			      'card'  => $token
			  ));				
				
				  $charge = Stripe_Charge::create(array(
				      'customer' => $customer->id,
				      'amount'   => $fee*100,
				      'currency' => $currency
				  ));
			} catch (Exception $e) {
				wp_die($e->getMessage());
			}	  
			 
			// !!!!in the next version avoid this copy-paste
			// almost the same code is in models/payment.php for the paypal payments
			$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_PAYMENTS." SET 
							course_id=%d, user_id=%s, date=CURDATE(), amount=%s, status='completed', paycode=%s, paytype='paypal'", 
							$_POST['course_id'], $user_ID, $fee, $token));
							
			// enroll accordingly to course settings - this will be placed in a method once we 
			// have more payment options
			$enroll_mode = get_post_meta($course->ID, 'namaste_enroll_mode', true);	
			if(!NamasteLMSStudentModel :: is_enrolled($user_ID, $course->ID))  {
				$status = ($enroll_mode == 'free') ? 'enrolled' : 'pending';				
				$_course->enroll($user_ID, $course->ID, $status);
			}	
			
			namaste_redirect('admin.php?page=namaste_my_courses');
		}	
		
		$message = '';
		if(!empty($_POST['enroll'])) {
			// enroll in course
			$course = NamasteLMSCourseModel :: select($_POST['course_id']);
			
			// course fee? For the moment all payments are manual so if there's a course fee, enrollment can't happen			
			$fee = get_post_meta($course->ID, 'namaste_fee', true);
			
			// When fee is paid, enrollment is automatic so this is just fine here
			if($fee > 0 and !$is_manager) wp_die("You can't enroll yourself in a course when there is a fee"); 			
			
			$enroll_mode = get_post_meta($course->ID, 'namaste_enroll_mode', true);
			
			// if already enrolled, just skip this altogether
			if(!NamasteLMSStudentModel :: is_enrolled($user_ID, $course->ID)) {
				// depending on mode, status will be either 'pending' or 'enrolled'
				$status = ($enroll_mode == 'free') ? 'enrolled' : 'pending';
				
				$_course->enroll($user_ID, $course->ID, $status);	
					
				if($enroll_mode == 'free') $message = sprintf(__('You enrolled in "%s"', 'namaste'), $course->post_title);
				else $message = __('Thank you for your interest in enrolling this course. A manager will review your application.', 'namaste');	
			}
			else $message = __('You have already enrolled in this course','namaste');
		}
		
		// select all courses join to student courses so we can have status.
		$courses = $wpdb -> get_results($wpdb->prepare("SELECT tSC.*, 
			tC.post_title as post_title, tC.ID as post_id, tC.post_excerpt as post_excerpt
			 FROM {$wpdb->posts} tC LEFT JOIN ".NAMASTE_STUDENT_COURSES." tSC ON tC.ID = tSC.course_id
			 AND tSC.user_id = %d WHERE tC.post_status = 'publish'
			 AND tC.post_type='namaste_course' ORDER BY tC.post_title", $user_ID));
			 
		if(!empty($currency) and !$is_manager) {
			foreach($courses as $cnt=>$course) {
				$courses[$cnt]->fee = get_post_meta($course->post_id, 'namaste_fee', true); 
			}
		}	 
				
		wp_enqueue_script('thickbox',null,array('jquery'));
		wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');	 
		require(NAMASTE_PATH."/views/my_courses.php");	 
	}
}