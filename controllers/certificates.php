<?php
class NamasteLMSCertificatesController {
	// manage certificates
	static function manage() {
		global $wpdb;
		
		$_cert = new NamasteLMSCertificateModel();
		
		// select courses
		$_course = new NamasteLMSCourseModel();
		$courses = $_course -> select();
		
		switch(@$_GET['action']) {
			case 'add':
				if(!empty($_POST['ok'])) {
					$_cert->add($_POST);
					namaste_redirect("admin.php?page=namaste_certificates&msg=added");
				}
				
				if(@file_exists(get_stylesheet_directory().'/namaste/certificate-form.php')) require get_stylesheet_directory().'/namaste/certificate-form.php';
				else require(NAMASTE_PATH."/views/certificate-form.php");		
			break;	
			
			case 'edit':
				if(!empty($_POST['del'])) {
					$wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_CERTIFICATES." WHERE id=%d", $_GET['id']));						
					namaste_redirect("admin.php?page=namaste_certificates&msg=deleted");
				}			
			
				if(!empty($_POST['ok'])) {
					$_cert->edit($_POST, $_GET['id']);
					namaste_redirect("admin.php?page=namaste_certificates&msg=edited");
				}
				
				$certificate = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_CERTIFICATES." WHERE id=%d", $_GET['id']));	
					
				if(@file_exists(get_stylesheet_directory().'/namaste/certificate-form.php')) require get_stylesheet_directory().'/namaste/certificate-form.php';
				else require(NAMASTE_PATH."/views/certificate-form.php");		
			break;	
			
			default:
				$certificates = $wpdb->get_results("SELECT * FROM ".NAMASTE_CERTIFICATES." ORDER BY title");			
				
				if(!empty($_GET['msg'])) {
					switch($_GET['msg']) {
					   case 'added': $msg = __('Certificate added', 'namaste'); break;
						case 'edited': $msg = __('Certificate saved', 'namaste'); break;
						case 'deleted': $msg = __('Certificate deleted', 'namaste'); break;
					}
				}	
				
				// using PDF bridge?
				if(!empty($_POST['save_pdf_settings'])) {
					update_option('namaste_generate_pdf_certificates', @$_POST['generate_pdf_certificates']);
				}		
				
				if(@file_exists(get_stylesheet_directory().'/namaste/certificates.php')) require get_stylesheet_directory().'/namaste/certificates.php';
				else require(NAMASTE_PATH."/views/certificates.php");		
			break;
		}
	}
	
	// display my achieved certificates
	static function my_certificates() {
		global $wpdb, $user_ID;
		$_cert = new NamasteLMSCertificateModel();
		
		$certificates = $_cert -> student_certificates($user_ID);
		
		$student_id = $user_ID;
		
		if(@file_exists(get_stylesheet_directory().'/namaste/my-certificates.php')) require get_stylesheet_directory().'/namaste/my-certificates.php';
		else require(NAMASTE_PATH."/views/my-certificates.php");
	}
	
	// viewing a specific certificate
	static function view_certificate() {
		global $wpdb, $user_ID;
		
		if(!current_user_can('namaste_manage') and $_GET['student_id']!=$user_ID) wp_die(__('You are not allowed to access this certificate', 'namaste'));
		
		// select certificate
		$certificate = $wpdb -> get_row($wpdb->prepare("SELECT tC.*, tS.date as date, tS.id as stid 
			FROM ".NAMASTE_CERTIFICATES." tC JOIN ".NAMASTE_STUDENT_CERTIFICATES." tS On tC.id = tS.certificate_id 
			WHERE tS.student_id = %d AND tC.id=%d", $_GET['student_id'], $_GET['id']));
			
		$output = wpautop(stripslashes($certificate -> content));	
			
		$user_info=get_userdata($_GET['student_id']);
		$name=(empty($user_info->first_name) or empty($user_info->last_name)) ? 
			$user_info->display_name : $user_info->first_name." ".$user_info->last_name;
		// if $name is still empty, output username
		if(empty($name)) $name = $user_info->user_login;	
			
		$output = str_replace("{{name}}", $name, $output);			
		$output = str_replace("{{id}}", sprintf('%08d', $certificate->stid), $output);
		
		if(strstr($output, "{{courses}}") or strstr($output, "{{courses-extended}}")) {
			$_course = new NamasteLMSCourseModel();
			$courses = $_course->select();
			$c_courses = array();
			
			foreach($courses as $course) {
				if(strstr($certificate -> course_ids, "|".$course->ID."|")) {
					if(strstr($output, "{{courses-extended}}")) {
						$c_courses[] = "<h2>".stripslashes($course->post_title)."</h2>".wpautop(stripslashes($course->post_excerpt));
					}
					else $c_courses[] = stripslashes($course->post_title);
				}
			}	
			
			$courses_str = implode(", ", $c_courses);
			$output = str_replace("{{courses}}", $courses_str, $output);
			$output = str_replace("{{courses-extended}}", $courses_str, $output);
		}					
	
		$date = date(get_option('date_format'), strtotime($certificate->date));
	 	$output=str_replace("{{date}}", $date, $output);
	 	
	 	if(get_option('namaste_generate_pdf_certificates') == "1") {
	 		$output = '<html>
			<head><title>'.$certificate->title.'</title>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head>
			<body>'.$output.'</body>
			</html>';
			//	die($output);
			$content = apply_filters('pdf-bridge-convert', $output);		
			echo $content;
			exit;	
		}	 	
	 	// else output HTML
		?>
		<html>
		<head><title><?php echo $certificate->title;?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head>
		<body><?php echo apply_filters('the_content', $output);?></body>
		</html>
		<?php exit;
	}
}