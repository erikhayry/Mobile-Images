<?php
/*
Plugin Name: MI - Mobile Images
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
define( 'MI_PUGIN_NAME', 'MI Mobile Images');
define( 'MI_PLUGIN_DIRECTORY', 'mobile-images');
define( 'MI_CURRENT_VERSION', '0.1' );
define( 'MI_CURRENT_BUILD', '1' );
define( 'MI_LOGPATH', str_replace('\\', '/', WP_CONTENT_DIR).'/MI-logs/');
define( 'MI_DEBUG', false);		# never use debug mode on productive systems
// i18n plugin domain for language files
define( 'EMU2_I18N_DOMAIN', 'MI' );

// how to handle log files, don't load them if you don't log
require_once('MI_logfilehandling.php');

// load language files
function MI_set_lang_file() {
	# set the language file
	$currentLocale = get_locale();
	if(!empty($currentLocale)) {
		$moFile = dirname(__FILE__) . "/lang/" . $currentLocale . ".mo";
		if (@file_exists($moFile) && is_readable($moFile)) {
			load_textdomain(EMU2_I18N_DOMAIN, $moFile);
		}

	}
}
MI_set_lang_file();

// create custom plugin settings menu
add_action( 'admin_menu', 'MI_create_menu' );

//call register settings function
add_action( 'admin_init', 'MI_register_settings' );

register_activation_hook(__FILE__, 'MI_activate');
register_deactivation_hook(__FILE__, 'MI_deactivate');
register_uninstall_hook(__FILE__, 'MI_uninstall');

/* FUNCTIONALITY */

/*include for phpQuery */
include 'includes/phpQuery-onefile.php';
include 'includes/deviceDetection.php';

$mobile = $isMobile;
$mobile = true;
if($mobile){
	add_filter('the_content', 'mobile_images',2);
}

function add_id_class($html, $id , $alt, $title, $align){
	$doc = phpQuery::newDocumentHTML($html);
	$id_class = 'mi-'.$id;
	$doc['img']->addClass($id_class) ;
	return $doc;
}

add_filter('get_image_tag','add_id_class',10,4);

function add_responsive_css(){
	if(get_option('options_add_css_class') == ''){
		$content_class = 'entry-content';
	}
	else {
		$content_class = get_option('options_add_css_class');
	}
		
    echo '<style type="text/css">.' .$content_class. ' img{max-width:100%;}</style>';
}

if(get_option('option_add_css') == '1'){    
	add_action('wp_head', 'add_responsive_css');
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
				$doc[$element]->switchWith($image) ;
			}
		}
	return $doc;
}





// activating the default values
function MI_activate() {
	add_option('MI_option_3', 'any_value');
}

// deactivating
function MI_deactivate() {
	// needed for proper deletion of every option
	delete_option('MI_option_3');
}

// uninstalling
function MI_uninstall() {
	# delete all data stored
	delete_option('MI_option_3');
	// delete log files and folder only if needed
	if (function_exists('MI_deleteLogFolder')) MI_deleteLogFolder();
}

function MI_create_menu() {

	// create new top-level menu
	/*add_menu_page( 
	__('HTML Title', EMU2_I18N_DOMAIN),
	__('HTML Title', EMU2_I18N_DOMAIN),
	0,
	MI_PLUGIN_DIRECTORY.'/mi_settings_page.php',
	'',
	plugins_url('/images/icon.png', __FILE__));
	
	
	add_submenu_page( 
	MI_PLUGIN_DIRECTORY.'/mi_settings_page.php',
	__("HTML Title", EMU2_I18N_DOMAIN),
	__("Menu title", EMU2_I18N_DOMAIN),
	0,
	MI_PLUGIN_DIRECTORY.'/mi_settings_page.php'
	);
	
	add_submenu_page( 
	MI_PLUGIN_DIRECTORY.'/mi_settings_page.php',
	__("HTML Title2", EMU2_I18N_DOMAIN),
	__("Menu title 2", EMU2_I18N_DOMAIN),
	9,
	MI_PLUGIN_DIRECTORY.'/mi_settings_page2.php'
	);
	*/
	// or create options menu page
	add_options_page(__('Mobile Images', EMU2_I18N_DOMAIN), __("Mobile Images", EMU2_I18N_DOMAIN), 9,  MI_PLUGIN_DIRECTORY.'/mi_settings_page.php');

	// or create sub menu page
	///$parent_slug="index.php";	# For Dashboard
	#$parent_slug="edit.php";		# For Posts
	// more examples at http://codex.wordpress.org/Administration_Menus
	//add_submenu_page( $parent_slug, __("HTML Title 4", EMU2_I18N_DOMAIN), __("Menu title 4", EMU2_I18N_DOMAIN), 9, MI_PLUGIN_DIRECTORY.'/mi_settings_page.php');
}


function MI_register_settings() {
	//register settings
	register_setting( 'mi-settings-group', 'option_image_size' );
	register_setting( 'mi-settings-group', 'option_image_class' );
	register_setting( 'mi-settings-group', 'option_add_css' );
	register_setting( 'mi-settings-group', 'options_add_css_class');
}

// check if debug is activated
function MI_debug() {
	# only run debug on localhost
	if ($_SERVER["HTTP_HOST"]=="localhost" && defined('EPS_DEBUG') && EPS_DEBUG==true) return true;
}
?>
