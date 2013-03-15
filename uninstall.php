<?php
global $wpdb;

if(!defined('WP_UNINSTALL_PLUGIN') or !WP_UNINSTALL_PLUGIN) exit;
    
// clenaup all data
if(get_option('namaste_cleanup_db')==1)
{
	// now drop tables	
	$wpdb->query("DROP TABLE `".NAMASTE_CLASSES."`");
	// NYI
	    
	// clean options
	// NYI
}