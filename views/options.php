<h1><?php _e("Namaste! LMS Options", 'namaste')?></h1>

<form method="post" class="namaste-form">
	<div class="postbox wp-admin namaste-box">
		<h2><?php _e('Wordpress roles with access to the learning material', 'namaste')?></h2>
		
		<p><?php _e('By default Namaste! LMS creates a role "student" which is the only role allowed to work with the learning material. The idea behind this is to allow the admin have better control over which users can access it. However, you can enable the other existing user roles here. Note that this setting is regarding consuming content, and not creating it.', 'namaste')?></p>
		
		<p><?php foreach($roles as $key=>$r):
			if($key=='administrator') continue;
			$role = get_role($key);?>
			<input type="checkbox" name="use_roles[]" value="<?php echo $key?>" <?php if($role->has_cap('namaste')) echo 'checked';?>> <?php _e($role->name, 'namaste')?> &nbsp;
		<?php endforeach;?></p>
		
		<h2><?php _e('Wordpress roles that can administrate the LMS', 'namaste')?></h2>
		
		<p><?php _e('By default this is only the blog administrator. Here you can enable any of the other roles as well', 'namaste')?></p>
		
		<p><?php foreach($roles as $key=>$r):
			if($key=='administrator') continue;
			$role = get_role($key);?>
			<input type="checkbox" name="manage_roles[]" value="<?php echo $key?>" <?php if($role->has_cap('namaste_manage')) echo 'checked';?>> <?php _e($role->name, 'namaste')?> &nbsp;
		<?php endforeach;?></p>
		
		<p></p>
		<p><input type="submit" value="<?php _e('Save Options', 'namaste')?>" name="namaste_options"></p>
	</div>
	<?php echo wp_nonce_field('save_options', 'nonce_options');?>
</form>

<form method="post" class="namaste-form">
	<div class="postbox wp-admin namaste-box">
		<h2><?php _e('Exam/Test Related Settings')?></h2>
		
		<p><?php _e('Namaste LMS utilizes the power of existing Wordpress plugins to handle exams, tests and quizzes. At this moment it can connect with two plugins:', 'namaste')?> <a href="http://wordpress.org/extend/plugins/watu/">Watu</a> <?php _e('(Free) and ', 'namaste')?> <a href="http://calendarscripts.info/watupro/">WatuPRO</a> <?php _e('(Premium)', 'namaste')?></p>
		
		<p><?php _e('If you have any of these plugins installed and activated, please choose which one to use for handling tests below:', 'namaste')?></p>
		
		<p><input type="radio" name='use_exams' <?php if(empty($use_exams)) echo 'checked'?> value="0"> <?php _e('I don not need to create any exams or tests.', 'namaste')?></p>
		
		<?php if($watu_active):?>
			<p><input type="radio" name='use_exams' <?php if(!empty($use_exams) and ($use_exams == 'watu')) echo 'checked'?> value="watu"> <?php _e('I will create exams with Watu.', 'namaste')?></p>
		<?php endif;?>
		
		<?php if($watupro_active):?>
			<p><input type="radio" name='use_exams' <?php if(!empty($use_exams) and ($use_exams == 'watupro')) echo 'checked'?> value="watupro"> <?php _e('I will create exams with WatuPRO.', 'namaste')?></p>
		<?php endif;?>
		
		<p><input type="submit" value="<?php _e('Save Exam Options', 'namaste')?>" name="namaste_exam_options"></p>
	</div>
	<?php echo wp_nonce_field('save_exam_options', 'nonce_exam_options');?>
</form>	
	