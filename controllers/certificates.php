<?php
class NamasteLMSCertificatesController {
	// manage certificates
	static function manage() {
		global $wpdb, $user_ID;
		$multiuser_access = 'all';
		$multiuser_access = NamasteLMSMultiUser :: check_access('certificates_access');
		
		$_cert = new NamasteLMSCertificateModel();
		
		// select courses
		$_course = new NamasteLMSCourseModel();
		$courses = $_course -> select();
		
		switch(@$_GET['action']) {
			case 'add':
				if(!empty($_POST['ok'])) {
					$cid = $_cert->add($_POST);
					do_action('namaste-certificate-saved', $cid);	
					namaste_redirect("admin.php?page=namaste_certificates&msg=added");
				}
				
				if(@file_exists(get_stylesheet_directory().'/namaste/certificate-form.php')) require get_stylesheet_directory().'/namaste/certificate-form.php';
				else require(NAMASTE_PATH."/views/certificate-form.php");		
			break;	
			
			case 'edit':			
				if($multiuser_access == 'own') {
					$certificate = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_CERTIFICATES." WHERE id=%d", $_GET['id']));	
					if($certificate->editor_id != $user_ID) wp_die(__('You are not allowed to do this.', 'namaste'));
				}

				if(!empty($_POST['del'])) {
					$wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_CERTIFICATES." WHERE id=%d", $_GET['id']));						
					namaste_redirect("admin.php?page=namaste_certificates&msg=deleted");
				}			
			
				if(!empty($_POST['ok'])) {
					$_cert->edit($_POST, $_GET['id']);
					do_action('namaste-certificate-saved', $_GET['id']);	
					namaste_redirect("admin.php?page=namaste_certificates&msg=edited");
				}
				
				$certificate = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_CERTIFICATES." WHERE id=%d", $_GET['id']));	
					
				if(@file_exists(get_stylesheet_directory().'/namaste/certificate-form.php')) require get_stylesheet_directory().'/namaste/certificate-form.php';
				else require(NAMASTE_PATH."/views/certificate-form.php");		
			break;	
			
			default:
				$own_sql = '';
				if($multiuser_access == 'own') $own_sql = $wpdb->prepare("WHERE editor_id=%d", $user_ID); 
				$certificates = $wpdb->get_results("SELECT * FROM ".NAMASTE_CERTIFICATES." $own_sql ORDER BY title");			
				
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
	static function my_certificates($in_shortcode = false) {
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
			WHERE tS.student_id = %d AND tC.id=%d
			AND tS.id=%d", $_GET['student_id'], $_GET['id'], $_GET['my_id']));
			
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
			<body><!--namaste-certificate-id-'.$certificate->id.'-namaste-certificate-id-->'.$output.'</body>
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
	
	static function certificate_redirect() {
		if(empty($_GET['namaste_view_certificate'])) return true;
		self :: view_certificate();
	}
	
	// displays links to certificates earned in a course
	static function my_course_certificates($course_id, $student_id, $text) {
		global $wpdb;
		
		$student_id_sql = $wpdb->prepare("tSC.student_id=%d", $student_id);
		$my_certificates = $wpdb->get_results("SELECT tC.id as id, tC.title as title, 
			tSC.date as date, tSC.id as my_id 
			FROM ".NAMASTE_CERTIFICATES." tC JOIN ".NAMASTE_STUDENT_CERTIFICATES." tSC
			ON tSC.certificate_id = tC.id 
			WHERE $student_id_sql AND tC.course_ids LIKE '%|".$course_id."|%'
			ORDER BY tSC.date");
			
		if(sizeof($my_certificates)) {
			$output = '';
			if(!empty($text)) $output .= "<p class='namaste-earned-certificates-text'>".$text."</p>";
			$output .= "<p class='namaste-earned-certificates-links'>";
			foreach($my_certificates as $certificate) $output .= '<a href="'.site_url("?namaste_view_certificate=1&id=".$certificate->id."&student_id=".$student_id."&noheader=1&my_id=".$certificate->my_id).'" target="_blank">'.stripslashes($certificate->title).'</a><br>';
			$output .= "</p>";
			return $output;
		}
		
		return '';	
	}
	
	// view and manage users who earned certificates
	static function student_certificates() {
		global $wpdb, $user_ID;
		
		$certificate = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".NAMASTE_CERTIFICATES." WHERE id=%d", $_GET['id']));
		
		$multiuser_access = 'all';
		$multiuser_access = NamasteLMSMultiUser :: check_access('certificates_access');
		
		$_cert = new NamasteLMSCertificateModel();
		
		if(!empty($_GET['approve'])) {
			// NYI, no such feature yet
		}
		
		if(!empty($_GET['delete'])) {
			$wpdb->query($wpdb->prepare("DELETE FROM ".NAMASTE_STUDENT_CERTIFICATES." WHERE id=%d", $_GET['student_certificate_id']));
		}
		
		// select users
		$users = $wpdb->get_results($wpdb->prepare("SELECT tSC.id as student_certificate_id, tU.user_nicename as user_nicename, 
		tU.user_email as user_email, tSC.date as date, tU.id as student_id
		FROM ".NAMASTE_STUDENT_CERTIFICATES." tSC JOIN {$wpdb->users} tU ON tSC.student_id = tU.ID 		
		WHERE tSC.certificate_id=%d
		ORDER BY tSC.id DESC", $certificate->id));
		
		$dateformat = get_option('date_format');
		
		$is_admin = true;
		
		if(@file_exists(get_stylesheet_directory().'/namaste/students-earned-certificate.html.php')) require get_stylesheet_directory().'/namaste/students-earned-certificate.html.php';
		else require NAMASTE_PATH."/views/students-earned-certificate.html.php";
	}
}