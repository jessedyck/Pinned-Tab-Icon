<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! current_user_can( 'activate_plugins' ) )
    return;
check_admin_referer( 'bulk-plugins' );	

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
    die( 'Uninstalling error. File: ' . __FILE__  . ' ' . WP_UNINSTALL_PLUGIN);
    
delete_option ( 'jd_pinned_icon_image' );
delete_option (' jd_pinned_icon_colour' );

?>