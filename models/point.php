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
			
		do_action('namaste_earned_points', $user_id, $award_points);	
			
		return true;	
	}
	
	// add custom column to the users table
	static function add_custom_column($columns) {		
		$columns['namaste_points'] = sprintf(__('LMS Points', 'namaste'));
	 	return $columns;		
	}
	
	static function manage_custom_column($empty='', $column_name, $id) {		
	  if( $column_name == 'namaste_points' ) {
			if(!empty($_GET['namaste_cleanup_points']) and $id == $_GET['namaste_cleanup_points']) {
				update_user_meta($_GET['namaste_cleanup_points'], 'namaste_points', 0);
			}	
	  	
			// get the number of points
	  		$points = get_user_meta($id, 'namaste_points', true);
	  		if($points) return $points . ' <a href="#" onclick="namasteResetPoints(' .$id.');return false;">' . __('(Cleanup)', 'namaste'). '</a>'; 
	  		else return "0";
	  }
		return $empty;
	}
}