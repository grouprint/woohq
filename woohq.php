<?php
/**
 * WooHQ Plugin is the simplest WordPress plugin for beginner.
 * Take this as a base plugin and modify as per your need.
 *
 * @package WooHQ for BilahPro
 * @author Grouprint Solutions
 * @license GPL-2.0+
 * @link https://grouprint.my/woohq
 * @copyright 2021 Grouprint Solutions. All rights reserved.
 *
 *            @wordpress-plugin
 *            Plugin Name: WooHQ for BilahPro
 *            Plugin URI: https://grouprint.my/woohq
 *            Description: Special plugin for BilahPro Imposition System users.
 *            Version: 1.0.2
 *            Author: Grouprint Solutions
 *            Author URI: https://grouprint.my
 *            Text Domain: woohq
 *            Contributors: Grouprint Solutions
 *            License: GPL-2.0+
 *            License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'URL_BILAHPRO' ) ) {
	define (URL_BILAHPRO, 'https://manage.bilahpro.com/woohq_license');
}

include_once('includes/license.php');
include_once('includes/price_check.php');

add_filter( 'plugin_action_links_woohq/woohq.php', 'woohq_settings_link' );
function woohq_settings_link( $links ) {
	// Build and escape the URL.
	$url = esc_url( add_query_arg(
		'page',
		'woohq',
		get_admin_url() . 'admin.php'
	) );
	// Create the link.
	$settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
	// Adds the link to the end of the array.
	array_unshift(
		$links,
		$settings_link
	);
	return $links;
}//end woohq_settings_link()

