<?php if(!empty($student) and !empty($lesson)):?>
	<h2><?php echo $student->user_login?><?php _e("'s todo items in lesson", 'namaste')?> "<?php echo $lesson->post_title?>"</h2>
<?php endif;?>	

<div class="wrap">
	<ol>
	<?php if(!empty($todo['todo_homeworks'])):?>
		<li><?php _e('To-do Assignments:', 'namaste')?>
		
			<ol>
				<?php foreach($todo['todo_homeworks'] as $homework):?>
					<li><strong><a href="<?php echo $homework->submit_link?>"><?php echo $homework->title?></a></strong></li>
				<?php endforeach;?>
			</ol>
		</li>
	<?php endif;?>
	
	<?php if(!empty($todo['todo_exam'])):?>
		<li><?php _e('To-do Test/Exam:', 'namaste')?> <a href="<?php echo $todo['todo_exam']->post_link?>"><?php echo $todo['todo_exam']->name?></a></li>
	<?php endif;?>
	
	<?php if($todo['todo_admin_approval']) echo "<li>".__('Manual manager approval is also required to complete this lesson.', 'namaste')."</li>";?>
	
	<?php if($todo['todo_mark']) echo "<li>".__('Student needs to manually mark this lesson as completed.', 'namaste')."</li>";?>
	
	<?php if(!empty($todo['nothing'])) echo "<li>".__('All the requirements are completed. Maybe a manager has set this "In progress" status manually.', 'namaste')."</li>"; ?>
	</ol>
</div>