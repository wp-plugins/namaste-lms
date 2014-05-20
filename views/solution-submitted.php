<h1><?php _e('Solution submitted', 'namaste');?></h1>

<div class="wrap">
	<p><?php _e('A manager will review your solution and will approve or reject it. If the manager add any notes, they will appear in the assignments page for this course', 'namaste')?></p>
	
	<?php if($in_shortcode):
		$permalink = get_permalink($post->ID);
		$params = array('lesson_id' => $_GET['lesson_id']);
		$target_url = add_query_arg( $params, $permalink );?>
		<p><a href="<?php echo $target_url;?>"><?php _e('Back to the assignments', 'namaste')?></a></p>
	<?php else:?>	
		<p><a href="admin.php?page=namaste_lesson_homeworks&lesson_id=<?php echo $lesson->ID?>&student_id=<?php echo $user_ID?>"><?php _e('Back to assignments in', 'namaste')?> "<?php echo $lesson->post_title?>"</a> 
		<?php _e('from course','namaste')?> "<a href="admin.php?page=namaste_student_lessons&course_id=<?php echo $course->ID?>&student_id=<?php echo $user_ID?>"><?php echo $course->post_title?></a>"</p>
	<?php endif;?>	
</div>