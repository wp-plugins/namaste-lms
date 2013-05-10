<h1><?php _e('My Courses', 'namaste')?></h1>

<?php if(!sizeof($courses)) :?>
	<p><?php _e('No courses are available at this time.', 'namaste')?></p>
<?php return false;
endif;?>

<div class="wrap">
	<?php if(!empty($message)):?>
		<p class="namaste-note"><?php echo $message?></p>
	<?php endif;?>	

	<table class="widefat">
		<tr><th><?php _e('Course title &amp; description', 'namaste')?></th>
		<th><?php _e('Lessons', 'namaste')?></th>		
		<th><?php _e('Status', 'namaste')?></th></tr>
		<?php foreach($courses as $course):?>
			<tr><td><a href="<?php echo get_permalink($course->post_id)?>" target="_blank"><?php echo $course->post_title?></a>
			<?php if(!empty($course->post_excerpt)): echo apply_filters('the_content', stripslashes($course->post_excerpt)); endif;?></td>
			<td><?php if(empty($course->status) or $course->status == 'pending'): 
				_e('Enroll to get access to the lessons', 'namaste');
				else: ?>
					<a href="admin.php?page=namaste_student_lessons&course_id=<?php echo $course->post_id?>&student_id=<?php echo $user_ID?>"><?php _e('View lessons', 'namaste')?></a>
				<?php endif;?></td>
			<td>
			<?php if(empty($course->status)): // not enrolled
				if($course->fee and !$is_manager):
					echo "<strong><a href='#' onclick='enrollCourse(".$course->post_id.", ".$user_ID.");return false;'>".__('Enroll for', 'namaste').' '.$currency." ".$course->fee."</a></strong>";
				else:?>
				<form method="post">
					<input type="submit" value="<?php _e('Click to Enroll', 'namaste')?>">
					<input type="hidden" name="enroll" value="1">
					<input type="hidden" name="course_id" value="<?php echo $course->post_id?>">
				</form>				
			<?php	endif;  
			else: // enrolled
				if($course->status == 'pending'): _e('Pending enrollment', 'namaste'); endif;
				if($course->status == 'rejected'): _e('Application rejected', 'namaste'); endif;
				if($course->status == 'enrolled'): printf(__('Enrolled on %s', 'namaste'), 
					date(get_option('date_format'), strtotime($course->enrollment_date))); endif;
				if($course->status == 'completed'): printf(__('Completed on %s', 'namaste'), 
					date(get_option('date_format'), strtotime($course->completion_date))); endif;
			endif;?>			
			</td></tr>
		<?php endforeach;?>
	</table>
</div>

<script type="text/javascript" >
function enrollCourse(courseID, studentID) {
	tb_show("<?php _e('Payment for course', 'namaste')?>", 
		'<?php echo admin_url("admin-ajax.php?action=namaste_ajax&type=course_payment")?>&course_id=' + courseID + 
		'&student_id=' + studentID);
}
</script>