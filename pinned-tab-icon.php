<?php
/*
Plugin Name: Pinned Tab Icon
Plugin URI: http://jessedyck.me
Description: Adds a section to the customizer section that allows for selection of an SVG Image file and colour for Safari 9's Pinned Tab feature. See https://support.apple.com/kb/PH21462?locale=en_US for details
Author: Jesse Dyck
Version: 1
Author URI: http://jessedyck.me
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


// Register customizer controls
function jd_mask_icon_customizer ($wp_customize)
{
	// Section
	$wp_customize->add_section( 'mask_icon_section', array(
		    'title' => 'Pinned Tab Icon',
		    'description' => 'Add an image to represent your site in Safari 9\'s Pinned Tabs feature. For details, see <a href="https://support.apple.com/kb/PH21462?locale=en_US" title="Safari\'s Pinned Tabs">support.apple.com</a>',
		) );
		
	// Image field
	$wp_customize->add_setting( 'mask_icon_image', array(
	    'default'        => '',
	    'type'           => 'option',
	    'sanitize_callback' => 'jd_sanitize_image' // required by Theme Checker - also just a good idea
	) );
	
	$wp_customize->add_control( 
		new WP_Customize_Image_Control( $wp_customize, 'mask_icon_control', array(
		    'label'   => 'Icon Image (SVG)',
		    'description' => 'SVG file with black shapes with transparent backgrounds only.',
		    'section' => 'mask_icon_section',
		    'settings'   => 'mask_icon_image'
		) 
	) );
	
	
	// Colour field
	$wp_customize->add_setting( 'mask_icon_colour', array(
	    'default'        => '#000000',
	    'type'           => 'option',
	    'sanitize_callback' => 'sanitize_hex_color' // required by Theme Checker - also just a good idea
	) );
	
	$wp_customize->add_control(
		new WP_Customize_Color_Control( $wp_customize, 'mask_icon_color_control', array(
		  'label' => 'Icon Color',
		  'description' => 'Define the colour for the image',
		  'section' => 'mask_icon_section',
		  'settings' => 'mask_icon_colour'
		) 
	) );
}
add_action( 'customize_register', 'jd_mask_icon_customizer' );


// Add SVG support if not already existing
function jd_custom_upload_mimes ( $existing_mimes = array() )
{
	if ($existing_mimes['svg'] != 'image/svg+xml')
		$existing_mimes['svg'] = 'image/svg+xml';
		
	return $existing_mimes;
}
add_filter('upload_mimes', 'jd_custom_upload_mimes');


// Action to output the mask icon HTML tag
function jd_mask_icon ()
{
	$img_id = get_option( 'mask_icon_image' );
	
	// If the ID is invalid, don't output a link; bail
	if (!($img_id > -1))
		return;
		
	$img_url = wp_get_attachment_url ( $img_id );
	
	$colour = get_option ( 'mask_icon_colour' );
	
	// Check again that we have a valid hex code
	$colour = ( preg_match ("|^#([A-Fa-f0-9]{3}){1,2}$|", $colour ) ? $colour : '#000000' );
	
	if ( isset ( $img_url ) && jd_is_svg ( $img_id ) )
		echo '<link rel="mask-icon" href="'. $img_url . '" color="' . $colour . '">';

}
add_action ( 'wp_head', 'jd_mask_icon' );


// Ensure image is SVG and convert attachment URL to ID
function jd_sanitize_image ($url)
{
	$url = esc_url_raw($url);
	
	// When the image is unset, $url is null; bail early
	if ( $url == NULL || !isset ($url) )
	{
		return $url;
	}
	
	// Get attachment ID
	$id = jd_url_to_id ( $url );
	
	if (!$id)
		return NULL;
		
	// Don't persist to DB unless it's an SVG
	if ( jd_is_svg ( $id ) )
		return $id;
	else
		return NULL;	
}

// Store the image attachment ID in the database rather than the full URL
function jd_url_to_id ($url)
{
	$id = (int) jd_get_attachment_id_from_url( $url );

	if ( is_int ( $id ) && $id > -1 )
		return $id;
	else
		return false;
}

function jd_is_svg ($id)
{
	if ( !$id || !is_int( $id ) )
		return false; 
		
	return ( get_post_mime_type ( $id ) == 'image/svg+xml' ? true : false );
}


// retrieves the attachment ID from the file URL
// https://pippinsplugins.com/retrieve-attachment-id-from-image-url/
// http://philipnewcomer.net/2012/11/get-the-attachment-id-from-an-image-url-in-wordpress/
function jd_get_attachment_id_from_url( $attachment_url = '' ) {
 
	global $wpdb;
	$attachment_id = false;
 
	// If there is no url, return.
	if ( '' == $attachment_url )
		return;
 
	// Get the upload directory paths
	$upload_dir_paths = wp_upload_dir();
 
	// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
	if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {
 
		// If this is the URL of an auto-generated thumbnail, get the URL of the original image
		$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );
 
		// Remove the upload path base directory from the attachment URL
		$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );
 
		// Finally, run a custom database query to get the attachment ID from the modified attachment URL
		$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
 
	}
 
	return $attachment_id;
}





?>