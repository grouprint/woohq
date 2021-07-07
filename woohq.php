<?php
/*
Plugin Name: 	WooHQ for BilahPro
Plugin URI:		https://grouprint.my/woohq
Description: 	Special plugin for BilahPro Imposition System users.
Version: 		1.0.5
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

$woohq_plugin_version = '1.0.5';
$plugin_file = plugin_basename(__FILE__);	
define( 'WOOHQ_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOOHQ_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WOOHQ_LICENSE_URL', 'https://manage.bilahpro.com/woohq_license' );

include( WOOHQ_PLUGIN_PATH . 'admin/admin-init.php' ); 
include( WOOHQ_PLUGIN_PATH . 'includes/price_check.php' ); 

add_filter( 'woocommerce_webhook_deliver_async', '__return_false' );

// register jquery and style on initialization
add_action('init', 'register_script');
function register_script() {
    wp_register_style( 'woohq', plugins_url('/assets/woohq.css', __FILE__), false, '1.0.0', 'all');
}

// use the registered jquery and style above
add_action('wp_enqueue_scripts', 'enqueue_style');

function enqueue_style(){
   wp_enqueue_style( 'woohq' );
}


add_action( 'rest_api_init', 'woohq_api' );

function woohq_api() {
    register_rest_route( 'woohq', 'getprice', array(
            'methods' => 'GET, POST',
            'callback' => 'get_price',
        )
    );

    register_rest_route( 'woohq', 'getItemMeta', array(
            'methods' => 'GET, POST',
            'callback' => 'get_item_meta',
        )
    );
}

function get_price() {
	
	$token = get_option( 'woohq_license_key' );
	$api_url = 'https://manage.bilahpro.com/api/getPrice';
	$curl = curl_init();
	$headers = array(
	   "Accept: application/json",
	   "Authorization: Bearer " . $token,
	);
	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'https://manage.bilahpro.com/api/getPrice',
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'POST',
	  CURLOPT_POSTFIELDS => http_build_query($_REQUEST),
	  CURLOPT_HTTPHEADER => $headers,
	));

	$response = curl_exec($curl);
	curl_close($curl);
	$res = json_decode($response);
    return rest_ensure_response( $res);
}

//get order meta
if ( !function_exists( 'wc_get_order_item_meta' ) ) { 
    require_once '/includes/wc-order-item-functions.php'; 
}
function get_item_meta(){

	$order_id = $_REQUEST['order_id'] ?? ''; 
	$items = get_order_items( $order_id );

	$key = $_REQUEST['key'] ?? ''; 
	$single = $_REQUEST['single'] ?? true; 
	
	foreach ($items as $item_id) {
		$result[] = wc_get_order_item_meta($item_id, $key, $single);
	}
	 
	return $result;
}

function get_order_items( $order_id ) {
    global $wpdb, $table_prefix;
    $items     = $wpdb->get_results( "SELECT * FROM `{$table_prefix}woocommerce_order_items` WHERE `order_id` = {$order_id}" );
    $item_name = array();

    foreach ( $items as $item ) {
        $item_id[] = $item->order_item_id;
    }

    return $item_id;
}

function convert($size, $unit){
	switch ($unit){
		case 'cm':
			$saiz = $size * 10;
			break;
		case 'in':
			$saiz = $size * 25.4;
			break;
		case 'mm':
			$saiz = $size;
			break;
	}
	return number_format($saiz);
}