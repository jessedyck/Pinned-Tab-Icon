<?php
/*
Plugin Name: Pinned Tab Icon
Plugin URI: https://github.com/jessedyck/Pinned-Tab-Icon
Description: A WordPress Plugin that adds Customizer controls to select an SVG image file and set the colour, for use in Safari 9's Pinned Tab feature. See https://support.apple.com/kb/PH21462?locale=en_US for details from Apple.
Author: Jesse Dyck
Version: 1.02
Author URI: http://jessedyck.me
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


// Register customizer controls
function jd_mask_icon_customizer ($wp_customize)
{
	// Image field
	$wp_customize->add_setting( 'mask_icon_image', array(
	    'default'        => '',
	    'type'           => 'option',
	    'sanitize_callback' => 'absint' // required by Theme Checker - also just a good idea
	) );
	
	$wp_customize->add_control( 
		new WP_Customize_Media_Control( $wp_customize, 'mask_icon_control', array(
		    'label'   => 'Pinned Tab Image (SVG)',
		    'description' => 'SVG file with black shapes with transparent backgrounds only. This is used  to represent your site in Safari 9\'s Pinned Tabs feature. For details, see <a href="https://support.apple.com/kb/PH21462?locale=en_US" title="Safari\'s Pinned Tabs">support.apple.com</a>',
		    'section' => 'title_tagline',
		    'settings'   => 'mask_icon_image',
		    'mime_type' => 'image/svg+xml',
		    'priority' => 80
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
		  'label' => 'Pinned Tab Image Color',
		  'description' => 'Define the colour for the image',
		  'section' => 'title_tagline',
		  'settings' => 'mask_icon_colour',
		  'priority' => 100
		) 
	) );
}
add_action( 'customize_register', 'jd_mask_icon_customizer' );


// Add SVG support if not already existing
function jd_custom_upload_mimes ( $existing_mimes = array() )
{
	if (empty ($existing_mimes['svg']) || $existing_mimes['svg'] != 'image/svg+xml')
		$existing_mimes['svg'] = 'image/svg+xml';
		
	if (empty ($existing_mimes['svgz']) || $existing_mimes['svgz'] != 'image/svg+xml')
		$existing_mimes['svgz'] = 'image/svg+xml';
	
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


function jd_is_svg ($id)
{
	if ( !is_numeric($id) )
		return false; 
	
	$id = (int)$id;	
	
	return ( get_post_mime_type ( $id ) == 'image/svg+xml' ? true : false );
}

?>