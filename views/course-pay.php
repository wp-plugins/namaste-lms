<div class="wrap">
	<h2><?php _e('Enrolling in course', 'namaste')?> "<?php echo $course->post_title?>"</h2>

	<p><?php printf(__('This is a premium course. There is a fee of <strong>%s %d</strong> to enroll it.', 'namaste'), $currency, $fee)?></p>
	
	<?php if($accept_other_payment_methods):?>
		<div><?php echo $other_payment_methods?></div>
	<?php endif;?>
</div>