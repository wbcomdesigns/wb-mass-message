<?php 
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

delete_option( 'MassMessageGroups' );
delete_option( 'MassMessageAllGroups' );
delete_option( 'MassMessageAllGroupsOverride' );
delete_option( 'MassMessageMembers' );
delete_option( 'MassMessageAllMembers' );
delete_option( 'MassMessageAllMembersOverride' );
delete_option( 'MassMessageBlogs' );
delete_option( 'MassMessageAllBlogs' );
delete_option( 'MassMessageAllBlogsOverride' );
delete_option( 'MassMessageMinimumType' );

// For site options in multisite
delete_site_option( 'MassMessageGroups' );  
delete_site_option( 'MassMessageGroups' );
delete_site_option( 'MassMessageAllGroups' );
delete_site_option( 'MassMessageAllGroupsOverride' );
delete_site_option( 'MassMessageMembers' );
delete_site_option( 'MassMessageAllMembers' );
delete_site_option( 'MassMessageAllMembersOverride' );
delete_site_option( 'MassMessageBlogs' );
delete_site_option( 'MassMessageAllBlogs' );
delete_site_option( 'MassMessageAllBlogsOverride' );
delete_site_option( 'MassMessageMinimumType' );

//drop a custom db table
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mass_message_log" );