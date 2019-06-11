<?php
require_once( plugin_dir_path( __DIR__ ) . '/woocommerce/woocommerce.php');	
/*
Plugin Name: TRI - Expand WooCommerce API
Description: Add Custom Meta Information to WooCommerce Order 
Version: 1.1
Author: Nauro Rezende Jr
*/

defined( 'ABSPATH' ) or exit;

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}


add_filter( 'woocommerce_rest_prepare_shop_order_object', 'my_wc_prepare_shop_order', 10, 3 );

function my_wc_prepare_shop_order( $response, $object, $request ) {
$order_data = $response->get_data();

foreach ( $order_data['line_items'] as $key => $item ) {
	$order_data['line_items'][ $key ]['uom'] = get_post_meta( $item['product_id'], '_woo_uom_input', true );
	
	$uom = array('id'=>'0000', 'key'=>'UOM', 'value'=>get_post_meta( $item['product_id'], '_woo_uom_input', true ));
	$order_data['line_items'][ $key ][ 'meta_data' ] = array($uom, $order_data['line_items'][ $key ][ 'meta_data' ][0]);
}

$order_data['syspro_id'] = get_the_author_meta('_syspro_customer_id',$order_data['customer_id']);

$response->data = $order_data;
return $response;
}

?>