<?php
/*
Plugin Name: 	WooHQ for BilahPro
Plugin URI:		https://grouprint.my/woohq
Description: 	Special plugin for BilahPro Imposition System users.
Version: 		1.0.4
Author: 		Grouprint Solutions
Author URI: 	https://grouprint.my
Text Domain: 	woohq
License: 		GPL-2.0+
License URI:	http://www.gnu.org/licenses/gpl-2.0.txt

Copyright 2021 and beyond | Azudin (email : azudin.daem@gmail.com)
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Global variables
 */
$woohq_plugin_version = '1.0.4';
$plugin_file = plugin_basename(__FILE__);	
define( 'WOOHQ_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOOHQ_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WOOHQ_LICENSE_URL', 'https://manage.bilahpro.com/woohq_license' );

/**
 * Includes - keeping it modular
 */
include( WOOHQ_PLUGIN_PATH . 'admin/admin-init.php' ); 
include( WOOHQ_PLUGIN_PATH . 'includes/price_check.php' ); 