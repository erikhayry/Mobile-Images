<?php
/*
Plugin Name: Mobile First Content Images
Plugin URI: http://erikportin.com/mi
Description: Swap content images with thumbnail image sizes
Version: 0.1
Author: Erik Portin, erikportin@gmail.com
Author URI: http://erikportin.com
License: GPL2
*/

/*  Copyright 2011  Erik Portin  (email : erikportin@gmail.com)

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
define( 'MFCI_PUGIN_NAME', 'Mobile First Content Images');
define( 'MFCI_PLUGIN_DIRECTORY', 'mobile-first-content-images');
define( 'MFCI_CURRENT_VERSION', '0.1' );
define( 'MFCI_CURRENT_BUILD', '1' );

// create custom plugin settings menu
add_action( 'admin_menu', 'MFCI_create_menu' );

//call register settings function
add_action( 'admin_init', 'MFCI_register_settings' );

register_activation_hook(__FILE__, 'MFCI_activate');
register_deactivation_hook(__FILE__, 'MFCI_deactivate');
register_uninstall_hook(__FILE__, 'MFCI_uninstall');

/* PLUGIN FUNCTIONALITY */
global $isMobile; 

/*include for phpQuery */
require_once('includes/phpQuery-onefile.php');
require_once('includes/deviceDetection.php');

$mobile = $isMobile;

if($mobile){
	add_filter('the_content', 'mobile_images',2);
}

if(get_option('option_add_css') == '1'){    
	add_action('wp_head', 'add_responsive_css');
}

function add_responsive_css(){
	if(get_option('options_add_css_class') == ''){
		$content_class = 'entry-content';
	}
	else {
		$content_class = get_option('options_add_css_class');
	}	
    echo '<style type="text/css">.' .$content_class. ' img{max-width:100%;}</style>';
}

function mobile_images($content){
	$doc = phpQuery::newDocumentHTML($content);
	
	$args = array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_parent' => get_the_ID()); 
	$attachments = get_posts($args);
		if ($attachments && !( get_option('option_image_size') == '' )) {
			foreach ( $attachments as $attachment ) {
				$image_size = get_option('option_image_size');
				$image_class = 'img.'.get_option('option_image_class');
				$image = wp_get_attachment_link( $attachment->ID, $image_size );
				$element = $image_class.$attachment->ID;
				if(!empty($doc[$element])){
					$doc[$element]->switchWith($image);
				}
				else {
					$element = 'img.wp-image-'.$attachment->ID;
					$doc[$element]->switchWith($image);
				}
			}
		}
	return $doc;
}

// activating the default values
function MFCI_activate() {
	add_option('MFCI_option', 'any_value');
}

// deactivating
function MFCI_deactivate() {
	// needed for proper deletion of every option
	delete_option('MFCI_option');
}

// uninstalling
function MFCI_uninstall() {
	# delete all data stored
	delete_option('MFCI_option');
	// delete log files and folder only if needed
	if (function_exists('MFCI_deleteLogFolder')) MFCI_deleteLogFolder();
}

function MFCI_create_menu() {
	add_options_page('Mobile First Content Images', "Mobile First Content Images", 9,  MFCI_PLUGIN_DIRECTORY.'/mfci_settings_page.php');
}


function MFCI_register_settings() {
	//register settings
	register_setting( 'mfci-settings-group', 'option_image_size' );
	register_setting( 'mfci-settings-group', 'option_add_css' );
	register_setting( 'mi-settings-group', 'options_add_css_class');
}
?>
