<h1><?php _e("Manage Certificates", 'namaste')?></h1>

<div class="wrap">
	<div class="postbox-container" style="width:73%;margin-right:2%;"> 
		<?php if(!empty($msg)):?>
			<div class="namaste-note"><?php echo $msg?></div>
		<?php endif;?>	
	
		<p><?php _e('Certificates can optionally be assigned to users upon completion of courses.', 'namaste')?></p>
		
		<p><a href="admin.php?page=namaste_certificates&action=add"><?php _e('Create new certificate', 'namaste')?></a></p>
		
		<?php if(sizeof($certificates)):?>
			<table class="widefat">
				<tr><th><?php _e('Certificate title', 'namaste')?></th><th><?php _e('Edit', 'namaste')?></th></tr>
				<?php foreach($certificates as $certificate):
					$class = ('alternate' == @$class) ? '' : 'alternate';?>
					<tr class="<?php echo $class?>"><td><?php echo stripslashes($certificate->title)?></td><td><a href="admin.php?page=namaste_certificates&action=edit&id=<?php echo $certificate->id?>"><?php _e('Edit', 'namaste')?></a></td></tr>
				<?php endforeach;?>	
			</table>
			
			<form method="post">
				<p><input type="checkbox" name="generate_pdf_certificates" value="1" <?php if(get_option('namaste_generate_pdf_certificates') == '1') echo 'checked'?>> <?php printf(__('I have installed the free <a href="%s" target="_blank">PDF Bridge</a> plugin and I want the certificates to be generated as PDF', 'namaste'), 'http://blog.calendarscripts.info/using-the-free-pdf-bridge-plugin-in-watupro/')?> <input type="submit" value="<?php _e('Save', 'namaste')?>" name="save_pdf_settings"></p>
			</form>
		<?php else:?>
			<p><?php _e('You have not added any certificates yet.', 'namaste')?></p>
		<?php endif;?>
	</div>
	<div id="namaste-sidebar">
			<?php include(NAMASTE_PATH."/views/sidebar.html.php");?>
	</div>
</div>