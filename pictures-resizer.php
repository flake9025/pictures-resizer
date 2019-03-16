<?php
/*
Plugin Name: Pictures Resizer
Plugin URI: http://bysus.fr
Description: Resize and compress pictures uploaded to WordPress
Version: 1.0.4
Author: Vincent Villain
Author URI: https://twitter.com/flake9025
License: GPLv2 or later
*/

//--- INCLUDES
include_once(dirname(__FILE__).'/lib_files.php');
include_once(dirname(__FILE__).'/lib_resize.php');
include_once(dirname(__FILE__).'/pictures-resizer-functions.php');

//--- WORDPRESS ACTIONS HANDLERS
register_activation_hook(__FILE__, 'vv_install');
register_deactivation_hook(__FILE__, 'vv_uninstall');
add_action('admin_menu', 'vv_adminMenu' );
add_action('add_attachment', 'vv_resizeAttachment');
?>