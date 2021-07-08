<?php
/*
Plugin Name: 	WooHQ for BilahPro
Plugin URI:		https://grouprint.my/woohq
Description: 	Special plugin for BilahPro Imposition System users.
Version: 		1.0.12
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

$plugin_file = plugin_basename(__FILE__);	
define( 'WOOHQ_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOOHQ_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WOOHQ_LICENSE_URL', 'https://manage.bilahpro.com/woohq_license' );

include( WOOHQ_PLUGIN_PATH . 'admin/admin-init.php' ); 
include( WOOHQ_PLUGIN_PATH . 'includes/price_check.php' ); 
include( WOOHQ_PLUGIN_PATH . 'plugin-update-checker/plugin-update-checker.php' ); 

$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/grouprint/woohq/',
	__FILE__,
	'woohq'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');
//Optional: If you're using a private repository, specify the access token like this:
$myUpdateChecker->setAuthentication('your-token-here');

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

    register_rest_route( 'woohq', 'getListStatus', array(
            'methods' => 'GET',
            'callback' => 'get_list_status',
        )
    );

    register_rest_route( 'woohq', 'getItemProduct', array(
            'methods' => 'GET, POST',
            'callback' => 'get_item_product',
        )
    );

    register_rest_route( 'woohq', 'getUnicpo', array(
            'methods' => 'GET, POST',
            'callback' => 'get_unicpo',
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

function get_list_status(){
	return wc_get_order_statuses();
}


function get_item_product(){
	$order_id = $_REQUEST['order_id'] ?? ''; 
	// Get an instance of the WC_Order object
	$order = wc_get_order($order_id);

	// Iterating through each WC_Order_Item_Product objects
	foreach ($order->get_items() as $item_key => $item ):

	    ## Using WC_Order_Item methods ##

	    // Item ID is directly accessible from the $item_key in the foreach loop or
	    $item_id = $item->get_id();

	    ## Using WC_Order_Item_Product methods ##

	    $product      = $item->get_product(); // Get the WC_Product object

	    $product_id   = $item->get_product_id(); // the Product id
	    $variation_id = $item->get_variation_id(); // the Variation id

	    $item_type    = $item->get_type(); // Type of the order item ("line_item")

	    $item_name    = $item->get_name(); // Name of the product
	    $quantity     = $item->get_quantity();  
	    $tax_class    = $item->get_tax_class();
	    $line_subtotal     = $item->get_subtotal(); // Line subtotal (non discounted)
	    $line_subtotal_tax = $item->get_subtotal_tax(); // Line subtotal tax (non discounted)
	    $line_total        = $item->get_total(); // Line total (discounted)
	    $line_total_tax    = $item->get_total_tax(); // Line total tax (discounted)

	    ## Access Order Items data properties (in an array of values) ##
	    $item_data    = $item->get_data();

	    $product_name = $item_data['name'];
	    $product_id   = $item_data['product_id'];
	    $variation_id = $item_data['variation_id'];
	    $quantity     = $item_data['quantity'];
	    $tax_class    = $item_data['tax_class'];
	    $line_subtotal     = $item_data['subtotal'];
	    $line_subtotal_tax = $item_data['subtotal_tax'];
	    $line_total        = $item_data['total'];
	    $line_total_tax    = $item_data['total_tax'];

	    // Get data from The WC_product object using methods (examples)
	    $product        = $item->get_product(); // Get the WC_Product object

	    $product_type   = $product->get_type();
	    $product_sku    = $product->get_sku();
	    $product_price  = $product->get_price();
	    $stock_quantity = $product->get_stock_quantity();

	endforeach;

	return $item_data;

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

function get_meta_data( $item_id ) {
    global $wpdb, $table_prefix;
    $items     = $wpdb->get_results( "SELECT * FROM `{$table_prefix}woocommerce_order_itemmeta` WHERE `order_item_id` = {$item_id}" );
    /*
    $item_name = array();

    foreach ( $items as $item ) {
        $item_id[] = $item->order_item_id;
    }
	*/
    return $items;
}

function get_unicpo( ) {
    global $wpdb, $table_prefix;

    $order_id = $_REQUEST['order_id'] ?? ''; 

    $item_id = get_order_items( $order_id );
        
    foreach ( $item_id as $item ) {
       $order_item_meta[] = get_meta_data( $item );
    }

	
	//$order_item_id =  $my_order->order_item_id; 

    //$items     = $wpdb->get_results( "SELECT * FROM `{$table_prefix}postmeta` WHERE `meta_key` = '_cpo_general' " );
    

    return $order_item_meta;
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