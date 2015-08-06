<h4><?php _e('Enrollment mode:', 'namaste')?></h4>

<p><b><?php _e('You can use the shortcode', 'namaste')?></b> <input type="text" value="[namaste-enroll]" onclick="this.select();" readonly size="14"> <b><?php _e('to display enrollment button (or enrolled/pending message) in the course content', 'namaste')?></b></p>

<p><b><?php _e('The shortcode', 'namaste')?></b> <input type="text" value="[namaste-enroll course_id=<?php echo $post->ID?>]" onclick="this.select();" readonly size="24"> <b><?php _e('needs to be used if you want to display enroll button for this course on a different page.', 'namaste')?></b></p>
 
<p><input type="radio" name="namaste_enroll_mode" value="free" <?php if(empty($enroll_mode) or $enroll_mode == 'free') echo 'checked'?>> <?php _e('Logged in users can enroll this course themselves.', 'namaste')?></p>

<p><input type="radio" name="namaste_enroll_mode" value="manual" <?php if(!empty($enroll_mode) and $enroll_mode == 'manual') echo 'checked'?>> <?php _e('Admin manually approves/enrolls students in courses', 'namaste')?></p>

<p><input type="checkbox" id="namasteUnEnrollChk" name="namaste_unenroll" value="1" <?php if($unenroll_allowed) echo 'checked'?>> <?php _e('Allow students to unenroll from this course (this will cleanup any stats)', 'namaste');?></p>

<?php if(!empty($currency)):?>
	<p><?php _e('Students need to pay a fee of', 'namaste')?> <?php echo $currency?> <input type="text" size="6" name="namaste_fee" value="<?php echo $fee?>"> <?php _e('to enroll this course. (Leave it 0 for no fee.)', 'namaste')?></p>
<?php endif;?>

<h4><?php _e('Course Access / Pre-requisites', 'namaste')?></h4>

<?php if(!sizeof($other_courses)):?>
	<p><?php _e('There are no other courses so every student can enroll in this course.', 'namaste')?></p>
<?php else: 
echo '<p>'.__('This course will be accessible only after the following courses are completed:','namaste').'</p>'; 
foreach($other_courses as $course):?>
	<p><input type="checkbox" name="namaste_access[]" value="<?php echo $course->ID?>" <?php if(in_array($course->ID, $course_access)) echo "checked"?>> <?php echo $course->post_title?></p>
<?php endforeach;
endif;?>

<h4><?php _e('Course completeness', 'namaste')?></h4>

<?php if(!sizeof($lessons)):?>
	<p><?php _e('This course has no lessons assigned so it can never be completed. Please create and assign some lessons to this course.', 'namaste')?></p>
<?php else:?>
	<p><?php _e('The following lessons must be completed in order to complete this course. Please select at least one.', 'namaste')?></p>
	<ul>
		<?php foreach($lessons as $lesson):?>
			<li><input type="checkbox" name="namaste_required_lessons[]" value="<?php echo $lesson->ID?>" <?php if(in_array($lesson->ID, $required_lessons)) echo 'checked'?>> <?php echo $lesson->post_title?></li>
		<?php endforeach;?>
	</ul>
<?php endif;?>

<?php if(!empty($use_points_system)):?>
	<p><?php _e('Reward', 'namaste')?> <input type="text" size="4" name="namaste_award_points" value="<?php echo $award_points?>"> <?php _e('points for completing this course.', 'namaste')?></p>
<?php endif;?>

<?php do_action('namaste-course-meta-box', $post);?>

<h4><?php _e('Shortcodes', 'namaste')?></h4>

<p><?php _e('You can use the shortcode', 'namaste')?> <b>[namaste-todo]</b> <?php _e('inside the course content to display what the student needs to do to complete the course.', 'namaste')?></p>
<p><?php _e('The shortcode', 'namaste')?> <b>[namaste-course-lessons]</b> <?php _e('will display the lessons in the course.','namaste');?> <?php _e('It allows more advanced configurations explained on the ', 'namaste');?> <a href="admin.php?page=namaste_help"><?php _e('help page.', 'namaste')?></a></p>

<h4><?php _e('Did you know?', 'namaste')?></h4>

<?php if(is_plugin_active('namaste-pro/namaste-pro.php')):?>
	<p><?php printf(__('You can limit the access to this course also by <a href="%s" target="_blank">class / group</a>.', 'namaste'), 'admin.php?page=namastepro_classes')?></p>
<?php else:?>
	<p><?php printf(__('If you <a href="%s" target="_blank">upgrade to PRO</a> you will be able to assign courses to classes and restrict access based on class, have different managers for different classes, and a lot more.', 'namaste'),'http://namaste-lms.org/pro.php')?></p>
<?php endif;?>