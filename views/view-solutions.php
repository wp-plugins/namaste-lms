<h1><?php _e('Viewing Solutions for ', 'namaste')?> "<?php echo $homework->title?>"</h1>

<div class="wrap">
	<p><?php _e('Lesson:', 'namaste')?> <strong><?php echo $lesson->post_title?></strong></p>	
	<p><?php _e('Course:', 'namaste')?> <strong><?php echo $course->post_title?></strong></p>

	<p><a href="admin.php?page=namaste_lesson_homeworks&lesson_id=<?php echo $lesson->ID?>&student_id=<?php echo $student->ID?>"><?php printf(__('Back to assignments for "%s"', 'namaste'), $lesson->post_title);?></a></p>
	
	<?php if($user_ID != $student->ID):?>
		<p><?php _e('Showing solutions submitted by', 'namaste')?> <strong><?php echo $student->user_login?></strong></p>
		<p><?php _e('Note: when one solution is approved, the assignment will be considered completed by this student and they will not be asked to submit more solutions for it.', 'namaste')?></p>
	<?php endif;?>
	
	<?php if(!sizeof($solutions)):
		echo "<p>".__("The student has not submitted any solutions for this assignment yet.", 'namaste')."</p>";
		echo "</div>";
		return true;
	endif;?>
	
	<table class="widefat">
	<?php foreach($solutions as $solution):?>
		<tr><th><?php printf(__('Solution submitted at %s', 'namaste'), date(get_option('date_format'), strtotime($solution->date_submitted)));?></th>
		<th><?php _e('Status');?></th></tr>
		<tr><td><?php echo apply_filters('the_content', $solution->content);?></td>
		<td><?php if(current_user_can('namaste_manage')):?>
		<form method="post">
			<select name="status" onchange="this.form.submit();">
				<option value="pending" <?php if($solution->status=='pending') echo 'selected'?>><?php _e('Pending', 'namaste')?></option>
				<option value="approved" <?php if($solution->status=='approved') echo 'selected'?>><?php _e('Approved', 'namaste')?></option>
				<option value="rejected" <?php if($solution->status=='rejected') echo 'selected'?>><?php _e('Rejected', 'namaste')?></option>
			</select>
			<input type="hidden" name="change_status" value="1">
			<input type="hidden" name="solution_id" value="<?php echo $solution->id?>">					
		</form>
		<?php else: echo $solution->status;
		endif;?></td></tr>
	<?php endforeach;?>
	</table>
</div>