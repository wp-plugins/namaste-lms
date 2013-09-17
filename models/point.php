<?php
// handles point awarding, spending, and so on
class NamastePoint {
	static function award($user_id, $award_points, $explanation) {
		global $wpdb;
		
		$points = get_user_meta($user_id, 'namaste_points', true);
		$points = $points + $award_points;
		update_user_meta($user_id, 'namaste_points', $points);
		
		// insert in history
		$wpdb->query($wpdb->prepare("INSERT INTO ".NAMASTE_HISTORY." SET
			user_id=%d, date=CURDATE(), datetime=NOW(), action='awarded_points', value=%s, num_value=%d",
			$user_id, $explanation, $award_points));
			
		return true;	
	}
}