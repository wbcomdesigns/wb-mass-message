<?php
/*
Plugin Name: Wb Mass Message
Plugin URI: http://www.wbcomdesigns.com
Version: 1.0.0
Author: Wbcom Designs
Author URI: http://www.wbcomdesigns.com
Description: Ever wanted to send a message to many people at once? Now you can, introducing - Mass Messaging.
Tested up to: 4.2.4
*/
/* Only load code that needs BuddyPress to run once BP is loaded and initialized. */

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );
	
	if ( !defined( '' ) ) {
	
		define( 'WBCOM_MASS_MESSAGE' , '1.0' );
	}
	
	if ( !defined( 'WBCOM_MASS_MESSAGE_PATH' ) ) {
	
		define( 'WBCOM_MASS_MESSAGE_PATH' , plugin_dir_path( __FILE__ ) );
	}
	
	if ( !defined( 'WBCOM_MASS_MESSAGE_URL' ) ) {
	
		define( 'WBCOM_MASS_MESSAGE_URL' , plugin_dir_url( __FILE__ ));
	}
	
	if ( !defined( 'WBCOM_MASS_MESSAGE_DB_VERSION' ) ) {
	
		define( 'WBCOM_MASS_MESSAGE_DB_VERSION' , '1' );
	}
	
	if ( !defined( 'WBCOM_MASS_MESSAGE_TEXT_DOMIAN' ) ) {
	
		define( 'WBCOM_MASS_MESSAGE_TEXT_DOMIAN' , 'wb-mass-messages' );
	}
	
function wb_mass_messaging_init_loader() {
    require( WBCOM_MASS_MESSAGE_PATH . '/mass-messaging-admin.php' );
    require( WBCOM_MASS_MESSAGE_PATH . '/mass-messaging-member.php' );
    require( WBCOM_MASS_MESSAGE_PATH . '/mass-messaging-groups.php' );
}
add_action( 'bp_include', 'wb_mass_messaging_init_loader' );

if( !function_exists( 'wbcom_mass_message_install' ) )
	{
		function wbcom_mass_message_install() {
			global $wpdb;
			$installed_ver = get_option( "mass_message_db_version" );
			if ( $installed_ver != WBCOM_MASS_MESSAGE_DB_VERSION ) 
			{
				$table_name = $wpdb->prefix . 'mass_message_log';
				$charset_collate = $wpdb->get_charset_collate();
			
				$sql = "CREATE TABLE $table_name (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					user_id mediumint(9) NOT NULL,
					message_receiver text NOT NULL,
					msg_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					UNIQUE KEY id (id)
				) $charset_collate;";
			
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				
				dbDelta( $sql );
				
				update_option( 'mass_message_db_version', WBCOM_MASS_MESSAGE_DB_VERSION );
			}
		}
	}
	register_activation_hook( __FILE__, 'wbcom_mass_message_install' );

/* If you have code that does not need BuddyPress to run, then add it here. */
?>