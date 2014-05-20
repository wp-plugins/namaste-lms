<h1><?php _e('Submitting a solution to assignment', 'namaste');?></h1>

<div class="wrap">
		<?php if($in_shortcode):$permalink = get_permalink($post->ID);
		$params = array('lesson_id' => $_GET['lesson_id']);
		$target_url = add_query_arg( $params, $permalink );?>
		<p><a href="<?php echo $target_url;?>"><?php _e('Back to the assignments', 'namaste')?></a></p>
		<?php else:?>
		<p><a href="admin.php?page=namaste_lesson_homeworks&lesson_id=<?php echo $lesson->ID?>&student_id=<?php echo $user_ID?>"><?php _e('Back to assignments in', 'namaste')?> "<?php echo $lesson->post_title?>"</a> 
	<?php _e('from course','namaste')?> "<a href="admin.php?page=namaste_student_lessons&course_id=<?php echo $course->ID?>&student_id=<?php echo $user_ID?>"><?php echo $course->post_title?></a>"</p>
	<?php endif;?>

	<h2><?php echo $homework->title?></h2>
	
	<div><?php echo apply_filters('the_content', stripslashes($homework->description))?></div>

	<p><b><?php _e('Submit your solution below:','namaste')?></b></p>
	
	<form method="post" enctype="multipart/form-data">
	<?php if($homework->accept_files):?>
		<div><label><?php _e('Upload file:', 'namaste')?></label> <input type="file" name="file"></div>
	<?php endif;?>
	<div><?php if($in_shortcode):?>
	<textarea name="content" rows="10" cols="50" class="namaste-submit-solution"></textarea>
	<?php else: wp_editor('', 'content');
	endif;?></div>
	<p align="center">
		<input type="submit" value="<?php _e('Submit your solution', 'namaste')?>">
		<input type="hidden" name="ok" value="1">
	</p>
	</form>
</div>