<h1><?php _e('Student Enrollments', 'namaste')?></h1>

<?php if(!sizeof($courses)):?>
<p><?php _e('Nothing to do here as you have not created any courses yet!')?></p>
<?php return true;
endif;?>

<?php if(!empty($error)):?>
	<div class="namaste-error"><?php echo $error?></div>
<?php endif;?>

<form method="get">
	<input type="hidden" name="page" value="namaste_students">
	<div class="wp-admin namaste-form">
		<p><label><?php _e('Select course:', 'namaste')?></label>
		<select name='course_id' onchange="this.form.submit();">
		<option value=""></option>
		<?php foreach($courses as $course):?>
			<option value="<?php echo $course->ID?>" <?php if($course->ID == $_GET['course_id']) echo 'selected'?>><?php echo $course->post_title?></option>
		<?php endforeach;?>
		</select></p>
		<?php if(!empty($_GET['course_id'])):?>
			<p><label><?php _e('Enroll student in the course:', 'namaste')?></label>
			 <input type="text" name="email" size="30" placeholder="<?php _e('Enter email', 'namaste')?>"> 
			<input type="submit" name="enroll" value="<?php _e('Enroll', 'namaste')?>"></p>
		<?php endif;?>
	</div>
</form>

<?php if(!empty($_GET['course_id'])):?>
	<?php if(!sizeof($students)):?>
	<p><?php _e('There are no students enrolled in this course yet.', 'namaste')?></p>
	<?php return false;
	endif;?>
	
	<p><?php _e('The below table shows all students enrolled in this course allow with the status for every lesson in it', 'namaste')?></p>
	<table class="widefat">
		<tr><th><?php _e('Student', 'namaste')?></th>
			<?php foreach($lessons as $lesson):?>
				<th><?php echo $lesson->post_title?></th>
			<?php endforeach;?>		
			<th><?php _e('Status', 'namaste')?></th>
		</tr>	
		<?php foreach($students as $student):
			// this page linked in the first cell will be the same for student - when student clicks on enrolled course, 
			// they'll see the same table as the admin will see here?>
			<tr><td><a href="admin.php?page=namaste_student_lessons&course_id=<?php echo $_GET['course_id']?>&student_id=<?php echo $student->ID?>"><?php echo $student->user_login?></td>
			<?php foreach($lessons as $lesson):?>
				<td><?php if(in_array($lesson->ID, $student->completed_lessons)): _e('Completed', 'namaste');
				elseif(in_array($lesson->ID, $student->incomplete_lessons)): echo "<a href='#' onclick='namasteInProgress(".$lesson->ID.", ".$student->ID."); return false;'>".__('In progress', 'namaste')."</a>";
				else: _e('Not started', 'namaste'); endif;?></td>
			<?php endforeach;?>		
			<td><?php echo $student->namaste_status;
			if($student->namaste_status=='pending'):?>
				(<a href="#" onclick="namasteConfirmStatus('enrolled',<?php echo $student->ID?>);return false;"><?php _e('approve', 'namaste')?></a> | <a href="#" onclick="namasteConfirmStatus('rejected',<?php echo $student->ID?>);return false;"><?php _e('reject')?></a>)
			<?php endif;?></td></tr>
		<?php endforeach;?>
	</table>
<?php endif;?>

<script type="text/javascript" >
function namasteConfirmStatus(status, id) {	
	if(!confirm("<?php _e('Are you sure?','namaste')?>")) return false;
	
	window.location="admin.php?page=namaste_students&course_id=<?php echo $_GET['course_id']?>&change_status=1&status="+status	
		+ "&student_id="+id;	
}

function namasteInProgress(lessonID, studentID) {
	tb_show("<?php _e('Lesson progress', 'namaste')?>", 
		'<?php echo admin_url("admin-ajax.php?action=namaste_ajax&type=lesson_progress")?>&lesson_id=' + lessonID + 
		'&student_id=' + studentID);
}
</script>