<?php
/*
Plugin Name: Admin IP Watcher
Description: Monitors when someone logs in with a new IP and emails you.
Author: Matt Mullenweg
Version: 1.1
Author URI: http://photomatt.net/
*/


$aiw_notify_both = 0; // 0 = email user, 1 = email user and blog admin

function aiw_new_ip_check() {
	global $current_user;
	$known = get_option('admin_ip_watcher');
	$user = $current_user->user_login;
	$ip = $_SERVER['REMOTE_ADDR'];
	if ( isset( $known[ $user ][ $ip ] ) )
		return false; // This is a known IP

	aiw_notify_admins();
	$known[ $user ][ $ip ] = array( 'first_seen' => time() );
	update_option( 'admin_ip_watcher', $known );
}
add_action( 'admin_head', 'aiw_new_ip_check' );

function aiw_notify_admins() {
	global $current_user, $aiw_notify_both;
	$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$message = "Someone has logged in with the below information from an IP we haven't seen before.\n\nUser: $current_user->user_login\nIP: {$_SERVER['REMOTE_ADDR']}\nURL: $url";
	$subject = sprintf( __('[%s] Admin IP Watcher Alert'), get_option('blogname') );
	wp_mail( $current_user->user_email, $subject, $message );
	if ( $aiw_notify_both )
		wp_mail( get_option('admin_email'), $subject, $message );
}
