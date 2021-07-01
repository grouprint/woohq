
<?php 
function woohq_adding_scripts() {
	wp_register_script('woohq_script', plugins_url('woohq.js', __FILE__), array('jquery'), date("his") , true);
	wp_enqueue_script('woohq_script');
}
  
add_action( 'wp_enqueue_scripts', 'woohq_adding_scripts' );

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );

add_action( 'woocommerce_single_product_summary', 'woocommerce_total_product_price', 31 );
function woocommerce_total_product_price() {
    global $woocommerce, $product;
    // let's setup our divs
    echo sprintf('<div id="product_total_price" style="margin-bottom:20px;">%s %s</div>',__('Product Total: ','woocommerce'),'<span class="price"> RM '.$product->get_price().'</span>');
}

add_filter( 'woocommerce_add_cart_item_data', 'add_cart_item_data', 10, 3 );
 
function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
     // get product id & price
    $product = wc_get_product( $product_id );
    $price = $product->get_price();
    // extra pack checkbox
    if( ! empty( $_POST['total_price'] ) ) {
       
        $cart_item_data['new_price'] = $_POST['total_price'];
    }
return $cart_item_data;
}

add_action( 'woocommerce_before_calculate_totals', 'before_calculate_totals', 10, 1 );
 
function before_calculate_totals( $cart_obj ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}

	// Iterate through each cart item
	foreach( $cart_obj->get_cart() as $key=>$value ) 
	{
		if( isset( $value['new_price'] ) ) 
		{
			$price = $value['new_price'];
			$value['data']->set_price( ( $price ) );
		}
	}
}