<?php
/*
Plugin Name: Mobile First Content Images
Plugin URI: http://ww.erikportin.com/mobile-first-content-images
Description: Swap content images with thumbnail image sizes on mobile devices
Version: 0.1
Author: Erik Portin, erikportin@gmail.com
Author URI: http://www.erikportin.com
License: GPL2
*/

/*  Copyright 2012  Erik Portin  (email : erikportin@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?><?php

// some definition we will use
define( 'EP_MFCI_PUGIN_NAME', 'Mobile First Content Images');
define( 'EP_MFCI_PLUGIN_DIRECTORY', 'mobile-first-content-images');
define( 'EP_MFCI_CURRENT_VERSION', '0.1' );
define( 'EP_MFCI_CURRENT_BUILD', '1' );

// create custom plugin settings menu
add_action( 'admin_menu', 'ep_mfci_create_menu' );

//call register settings function
add_action( 'admin_init', 'ep_mfci_register_settings' );


function ep_mfci_enque_admin_scripts($hook) {
    if( 'mobile-first-content-images/ep_mfci_settings_page.php' != $hook )
        return;
    echo "<link type='text/css' rel='stylesheet' href='" . plugins_url('/css/admin.css', __FILE__) . "' />";    
    wp_enqueue_script( 'ep_mfci_admin_script_script', plugins_url('js/admin.js', __FILE__), array('jquery'));
}
add_action( 'admin_enqueue_scripts', 'ep_mfci_enque_admin_scripts' );

register_activation_hook(__FILE__, 'EP_MFCI_activate');
register_deactivation_hook(__FILE__, 'EP_MFCI_deactivate');
register_uninstall_hook(__FILE__, 'EP_MFCI_uninstall');

/* PLUGIN FUNCTIONALITY */
global $ep_mfci_is_mobile; 

/*include for phpQuery */
require_once('includes/phpQuery-onefile.php');
require_once('includes/categorizr.php');

if(!tablet() && !desktop() && !tv()){
	$ep_mfci_mobile =  true;
}

if($ep_mfci_mobile){
	add_filter('the_content', 'ep_mfci_mobile_images',2);
}

if(get_option('option_add_css') == '1'){    
	add_action('wp_head', 'ep_mfci_add_responsive_css');
}

function ep_mfci_add_responsive_css(){
	if(get_option('options_add_css_class') == ''){
		$ep_mfci_content_class = 'entry-content';
	}
	else {
		$ep_mfci_content_class = get_option('options_add_css_class');
	}	
    echo '<style type="text/css">.' .$ep_mfci_content_class. ' img{max-width:100%;}</style>';
}

function ep_mfci_mobile_images($content){
	$ep_mfci_doc = phpQuery::newDocumentHTML($content);
	
	$ep_mfci_args = array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => get_the_ID()); 
	$ep_mfci_attachments = get_posts($ep_mfci_args);
		if ($ep_mfci_attachments && !( get_option('option_image_size') == '' )) {
			foreach ( $ep_mfci_attachments as $ep_mfci_attachment ) {
				$ep_mfci_image_size = get_option('option_image_size');
				$ep_mfci_image_class = 'img.'.get_option('option_image_class');
				$ep_mfci_image = wp_get_attachment_link( $ep_mfci_attachment->ID, $ep_mfci_image_size );
				$ep_mfci_element = $ep_mfci_image_class.$ep_mfci_attachment->ID;
				if(!empty($ep_mfci_doc[$ep_mfci_element])){
					$ep_mfci_doc[$ep_mfci_element]->switchWith($ep_mfci_image);
				}
				else {
					$ep_mfci_element = 'img.wp-image-'.$ep_mfci_attachment->ID;
					$ep_mfci_doc[$ep_mfci_element]->switchWith($ep_mfci_image);
				}
			}
		}
	return $ep_mfci_doc;
}

// activating the default values
function EP_MFCI_activate() {
	if( version_compare( get_bloginfo( 'version' ), '3.0', '<' ) ){
		deactivate_plugins( basename( __FILE__ ) );
	}
}

// deactivating
function EP_MFCI_deactivate() {
	// needed for proper deletion of every option
	delete_option('EP_MFCI_option');
}

// uninstalling
function EP_MFCI_uninstall() {
	# delete all data stored
	delete_option('EP_MFCI_option');
	// delete log files and folder only if needed
	if (function_exists('EP_MFCI_deleteLogFolder')) EP_MFCI_deleteLogFolder();
}

function ep_mfci_create_menu() {
	add_options_page('Mobile First Content Images', "Mobile First Content Images", 9,  EP_MFCI_PLUGIN_DIRECTORY.'/ep_mfci_settings_page.php');
}

function ep_mfci_register_settings() {
	//register settings
	register_setting( 'ep-mfci-settings-group', 'option_image_size' );
	register_setting( 'ep-mfci-settings-group', 'option_add_css' );
	register_setting( 'ep-mfci-settings-group', 'options_add_css_class');
}
?>
