<?php
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
require_once( plugin_dir_path( __DIR__ ) . '/woocommerce/woocommerce.php');	

/*
Plugin Name: Surfaces Order Portal Setup
Description: Make Changes on WooCommerce to Setup Surfaces Order Portal
Version: 1.0
Author: Nauro Rezende Jr
*/
defined( 'ABSPATH' ) or exit;


// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

//HIDE STOCK QUANITY FROM PRODUCT PAGE
add_filter( 'woocommerce_get_stock_html', '__return_empty_string' );

//Add SVG capabilities
function wpcontent_svg_mime_type( $mimes = array() ) {
  $mimes['svg']  = 'image/svg+xml';
  $mimes['svgz'] = 'image/svg+xml';
  return $mimes;
}
add_filter( 'upload_mimes', 'wpcontent_svg_mime_type' );


//CREATE A NEW FIELD ON SHIPPING ADDRESS
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );

// Our hooked in function - $fields is passed via the filter!
function custom_override_checkout_fields( $fields ) {
     $fields['shipping']['shipping_type'] = array(
        'label'     => __('Type of Address', 'woocommerce'),
   		'required'  => true,
		'class'     => array('form-row-wide'),
		'type'		=> 'select',
		'options'	=> array('Commercial'=>'Commercial', 'Residential'=>'Residential'), 
	    'clear'     => true
     );

	 $fields['order']['customer_po'] = array(
        'label'     => __('Customer PO', 'woocommerce'),
   		'required'  => true,
		'class'     => array('form-row-first'),
		'type'		=> 'text',
	    'clear'     => false
     );         

	 $fields['order']['required_shipping_date'] = array(
        'label'     => __('Required Shipping Date', 'woocommerce'),
   		'required'  => false,
		'class'     => array('form-row-last'),
		'type'		=> 'text',
		'placeholder' => date('m/d/Y'),
	    'clear'     => true
     );       


     return $fields;
}

// Display field value on the order edit page 
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );

function my_custom_checkout_field_display_admin_order_meta($order){
	$shipping_type = get_post_meta( $order->id, '_shipping_type', true );
	$customer_po = get_post_meta( $order->id, 'customer_po', true );
	$required_shipping_date = get_post_meta( $order->id, '_order_required_shipping_date', true );
	$invoice_num = get_post_meta( $order->id, '_invoice_number', true );
	$transaction_id =  get_post_meta( $order->id, '_order_transaction_id', true );
	$syspro_balance =  get_post_meta( $order->id, '_current_syspro_balance', true );
	
    if(!empty($shipping_type)) 	{ echo '<p><strong>'.__('Type of Address').':</strong> ' . $shipping_type . '</p>'; } 
    if(!empty($customer_po)) 	{ echo '<p><strong>'.__('Customer PO').':</strong> ' . $customer_po . '</p>'; }
    if(!empty($required_shipping_date)) { echo '<p><strong>'.__('Required Shipping Date').':</strong> ' . $required_shipping_date . '</p>'; }
    if(!empty($invoice_num)) 	{ echo '<p><strong>'.__('Invoice #').':</strong> ' . $invoice_num . '</p>'; }
    if(!empty($transaction_id)) { echo '<p><strong>'.__('Transaction ID').':</strong> <br>' . $transaction_id . '</p>'; }
    if(!empty($syspro_balance)) { echo '<hr><p><strong>'.__('Balance in Syspro').':</strong> <br>' . wc_price($syspro_balance) . '</p>'; }
}


// Update the order meta with field value
add_action( 'woocommerce_checkout_update_order_meta', 'my_custom_checkout_field_update_order_meta' );

function my_custom_checkout_field_update_order_meta( $order_id ) {

    if ( ! empty( $_POST['customer_po'] ) ) {
        update_post_meta( $order_id, 'customer_po', sanitize_text_field( $_POST['customer_po'] ) );
    }

    if ( ! empty( $_POST['required_shipping_date'] ) ) {
        update_post_meta( $order_id, 'required_shipping_date', sanitize_text_field( $_POST['required_shipping_date'] ) );
    }

}

//BILLING ADDRESS READONLY
add_action('woocommerce_checkout_fields','customization_readonly_billing_fields',10,1);
function customization_readonly_billing_fields($checkout_fields){
    $current_user = wp_get_current_user();;
    $user_id = $current_user->ID;
    foreach ( $checkout_fields['billing'] as $key => $field ){
        if($key != 'billing_first_name' && $key != 'billing_last_name' && $key != 'billing_email' && $key != 'billing_phone' ){
            $key_value = get_user_meta($user_id, $key, true);
            // ALLOW CHANGE FOR SUPER USER //COMMENTED
            //if( strlen($key_value)>0){
                $checkout_fields['billing'][$key]['custom_attributes'] = array('readonly'=>'readonly');
            //}
        }
    }
    return $checkout_fields;
}

function sv_change_product_price_display( $price, $cart_item ) {

	if(!empty($GLOBALS['USER_PRICE_LIST'])) {
		$price_list_name = '_wc_price_list_'.$GLOBALS['USER_PRICE_LIST'];
	}

	
	if(is_array($cart_item)) { 
		$uom = get_post_meta($cart_item["product_id"], '_woo_uom_input', true);	
		$new_price = get_post_meta($cart_item["product_id"], $price_list_name, true);		

	} elseif(is_object($cart_item)) { 
		$uom = get_post_meta($cart_item->id, '_woo_uom_input', true);	
		$new_price = get_post_meta($cart_item->id, $price_list_name, true);		
	}
	
	if(!empty($new_price) && $new_price > 0) { 
		$price = wc_price($new_price);	
	}

	
	switch($uom) { 
		case 'BOX': 
			$UOM = 'carton';
			break;
		case 'PLT': 
			$UOM = 'pallet';
			break;				
		case 'EA': 
			$UOM = 'each';
			break;	
		default: 
			$UOM = 'piece';				
		}
		$price .= " / $UOM";		
		
return $price;
}

add_filter( 'woocommerce_get_price_html', 'sv_change_product_price_display', 5, 2 ); // PRODUCT CATALOG
add_filter( 'woocommerce_cart_item_price', 'sv_change_product_price_display', 5, 2 ); // CART AND LISTS


// ADD UOM TO PRODUCT GENERAL TAB 
add_action( 'woocommerce_product_options_general_product_data', 'woo_add_sqft_fields' );


function woo_add_sqft_fields() {

  global $woocommerce, $post;
  
  echo '<div class="options_group">';
  
	woocommerce_wp_select( 
	array( 
		'id'      => '_woo_uom_input', 
		'label'   => __( 'Unit of Measurement', 'woocommerce' ), 
		'options' => array(
			'PCS'   => __( 'Piece', 'woocommerce' ),
			'BOX'   => __( 'Carton', 'woocommerce' ),
			'EA'   => __( 'Each', 'woocommerce' ),			
			'PLT' => __( 'Pallet', 'woocommerce' )
			)
		)
	);

	woocommerce_wp_text_input( 
				array( 
					'id'          => '_woo_coverage_per_uom', 
					'label'       => __( 'Coverage per UOM (sqft)', 'woocommerce' ), 
					'placeholder' => '1',
					'desc_tip'    => 'true',
					'description' => __( 'Inform the coverage in sqft for the UOM.', 'woocommerce' )
				)
			);

	woocommerce_wp_text_input( 
				array( 
					'id'          => '_woo_piece_per_uom', 
					'label'       => __( 'Pieces per UOM', 'woocommerce' ), 
					'placeholder' => '1',
					'desc_tip'    => 'true',
					'description' => __( 'Inform number of pieces for the UOM.', 'woocommerce' )
				)
			);
	
	woocommerce_wp_text_input( 
				array( 
					'id'          => '_woo_price_per_sqft', 
					'label'       => __( 'Price per SQFT', 'woocommerce' ), 
					'placeholder' => '1',
					'desc_tip'    => 'true',
					'description' => __( 'Inform the price per sqft for the item.', 'woocommerce' )
				)
			);
	
	woocommerce_wp_text_input( 
				array( 
					'id'          => '_woo_price_per_piece', 
					'label'       => __( 'Price per Piece', 'woocommerce' ), 
					'placeholder' => '1',
					'desc_tip'    => 'true',
					'description' => __( 'If product is sold by box inform the price per piece for the item.', 'woocommerce' )
				)
			);


	
  
  echo '</div>';
	
}

// Save Fields
add_action( 'woocommerce_process_product_meta', 'woo_add_custom_sqft_save' );
function woo_add_custom_sqft_save( $post_id ){
	// Select
	$woocommerce_select = $_POST['_woo_uom_input'];
	if( !empty( $woocommerce_select ) ) update_post_meta( $post_id, '_woo_uom_input', esc_attr( $woocommerce_select ) );

	$woocommerce_coverage = $_POST['_woo_coverage_per_uom'];
	if( !empty( $woocommerce_coverage ) ) update_post_meta( $post_id, '_woo_coverage_per_uom', esc_attr( $woocommerce_coverage ) );

	$woocommerce_pieces_per_uom = $_POST['_woo_piece_per_uom'];
	if( !empty( $woocommerce_pieces_per_uom ) ) update_post_meta( $post_id, '_woo_piece_per_uom', esc_attr( $woocommerce_pieces_per_uom ) );

	$woocommerce_price_per_sqft = $_POST['_woo_price_per_sqft'];
	if( !empty( $woocommerce_price_per_sqft ) ) update_post_meta( $post_id, '_woo_price_per_sqft', esc_attr( $woocommerce_price_per_sqft ) );

	$woocommerce_price_per_piece = $_POST['_woo_price_per_piece'];
	if( !empty( $woocommerce_price_per_piece ) ) update_post_meta( $post_id, '_woo_price_per_sqft', esc_attr( $woocommerce_price_per_piece ) );


}


add_action( 'woocommerce_single_product_summary', 'woocommerce_show_pieces_per_moq_on_product', 11 );

function woocommerce_show_pieces_per_moq_on_product() {
	global $post;
	$uom = get_post_meta($post->ID, '_woo_uom_input', true);
	$pieces_per_box = get_post_meta($post->ID, '_woo_piece_per_uom', true);
	$coverage_per_box = get_post_meta($post->ID, '_woo_coverage_per_uom', true); 
		
	if($uom == 'BOX' && !empty($pieces_per_box) && $pieces_per_box > 1){
	
		echo "<small>$pieces_per_box pieces per carton / $coverage_per_box SQFT per carton</small>";
		
	}
			

}



// CHANGE PRICE ON CART BASED ON MULTI_PRICE_LIST
add_action( 'woocommerce_before_calculate_totals', 'add_custom_price' );

function add_custom_price( $cart_object ) {
  
  	if(!empty($GLOBALS['USER_PRICE_LIST'])) {
		$price_list_name = '_wc_price_list_'.$GLOBALS['USER_PRICE_LIST'];
	} 
	
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
	    $new_price = get_post_meta($cart_item['data']->get_id(), $price_list_name, true); 
	    if(!empty($new_price) && $new_price > 0) { 
		  $cart_item['data']->set_price($new_price);  
		    
	    }
    }
}

add_filter( 'woocommerce_product_tabs', 'wcs_woo_remove_reviews_tab', 98 );
    function wcs_woo_remove_reviews_tab($tabs) {
    unset($tabs['reviews']);
    unset($tabs['description']);
    return $tabs;
}

add_filter( 'woocommerce_product_tabs', 'woo_rename_tabs', 98 );
function woo_rename_tabs( $tabs ) {
	if(sizeof($tabs) > 0 && isset($tabs['additional_information']['title'])) {
		$tabs['additional_information']['title'] = __( 'Product Specification' );	// Rename the additional information tab
	} 
	return $tabs;
}


// Change the heading on the Additional Information tab section title for single products.
add_filter( 'woocommerce_product_additional_information_heading', 'isa_additional_info_heading' );
 
function isa_additional_info_heading() {
    return 'Product Specification';
}

function tutsplus_list_attributes( $product ) {
    global $product;
    $product->get_attributes();
    echo  $product;
}
add_action( 'woocommerce_template_single_title', 'tutsplus_list_attributes' );

// Remove "Edit" links from My Account > Addresses
function sv_remove_edit_account_links() {
    wc_enqueue_js( "
        jQuery(document).ready(function($) {
			$('.woocommerce-Address:first a.edit').remove();
        });
    " );
}
add_action( 'woocommerce_before_my_account', 'sv_remove_edit_account_links' );


//Add Continue Shopping Button on Cart Page
//Add to theme functions.php file or Code Snippets plugin
add_action( 'woocommerce_before_cart_table', 'woo_add_continue_shopping_button_to_cart' );
function woo_add_continue_shopping_button_to_cart() {
 $shop_page_url = get_permalink( woocommerce_get_page_id( 'shop' ) );
 
 echo '<div class="woocommerce-message">';
 echo ' <a href="'.$shop_page_url.'" class="button">Continue Shopping ?</a> Select more products.';
 echo '</div>';
}


//Add quantity field on the archive page.
function custom_quantity_field_archive() {
	if(is_user_logged_in()) {
		$product = wc_get_product( get_the_ID() );
		if ( ! $product->is_sold_individually() && 'variable' != $product->product_type && $product->is_purchasable() ) {
			woocommerce_quantity_input( array( 'min_value' => 1, 'max_value' => $product->backorders_allowed() ? '' : $product->get_stock_quantity() ) );
			}
	} 
}
add_action( 'woocommerce_after_shop_loop_item', 'custom_quantity_field_archive', 0, 9 );


//Add requires JavaScript.
function custom_add_to_cart_quantity_handler() {
	wc_enqueue_js( '
		jQuery( ".post-type-archive-product" ).on( "click", ".quantity input", function() {
			return false;
		});

		jQuery( ".archive.tax-product_cat" ).on( "click", ".quantity input", function() {
			return false;
		});


		jQuery( ".archive.tax-product_cat" ).on( "change input", ".quantity .qty", function() {
			var add_to_cart_button = jQuery( this ).parents( ".product" ).find( ".add_to_cart_button" );
			// For AJAX add-to-cart actions
			add_to_cart_button.data( "quantity", jQuery( this ).val() );
			// For non-AJAX add-to-cart actions
			add_to_cart_button.attr( "href", "?add-to-cart=" + add_to_cart_button.attr( "data-product_id" ) + "&quantity=" + jQuery( this ).val() );
		});


		jQuery( ".post-type-archive-product" ).on( "change input", ".quantity .qty", function() {
			var add_to_cart_button = jQuery( this ).parents( ".product" ).find( ".add_to_cart_button" );
			// For AJAX add-to-cart actions
			add_to_cart_button.data( "quantity", jQuery( this ).val() );
			// For non-AJAX add-to-cart actions
			add_to_cart_button.attr( "href", "?add-to-cart=" + add_to_cart_button.attr( "data-product_id" ) + "&quantity=" + jQuery( this ).val() );
		});
	' );
}
add_action( 'init', 'custom_add_to_cart_quantity_handler' );


//Add Alphabetical sorting option to shop page / WC Product Settings
function sv_alphabetical_woocommerce_shop_ordering( $sort_args ) {
  $orderby_value = isset( $_GET['orderby'] ) ? woocommerce_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
  
    if ( 'sku' == $orderby_value ) {
        $sort_args['orderby'] = 'meta_value';
        $sort_args['order'] = 'asc';
        $sort_args['meta_key'] = '_sku';
    }
  
    return $sort_args;
}
add_filter( 'woocommerce_get_catalog_ordering_args', 'sv_alphabetical_woocommerce_shop_ordering' );


function sv_custom_woocommerce_catalog_orderby( $sortby ) {
    $sortby['sku'] = 'Sort by SKU number';
    return $sortby;
}
add_filter( 'woocommerce_default_catalog_orderby_options', 'sv_custom_woocommerce_catalog_orderby' );
add_filter( 'woocommerce_catalog_orderby', 'sv_custom_woocommerce_catalog_orderby' );


//SHOW SKU ON PRODUCT CATALOG PAGE
//add_action("woocommerce_after_shop_loop_item_title", "shop_sku");
function shop_sku(){
	global $product;
	echo '<span itemprop=?productID? class=?sku?>SKU: ' . $product->sku . '</span>';
}

// ADD UOM TO PRODUCT GENERAL TAB 
//add_action( 'woocommerce_product_options_general_product_data', 'woo_add_custom_general_fields' );
function woo_add_custom_general_fields() {

  global $woocommerce, $post;
  
  echo '<div class="options_group">';
  
	woocommerce_wp_select( 
	array( 
		'id'      => '_woo_uom_input', 
		'label'   => __( 'Unit of Measurement', 'woocommerce' ), 
		'options' => array(
			'PCS'   => __( 'Piece', 'woocommerce' ),
			'BOX'   => __( 'Carton', 'woocommerce' ),
			'EA'   => __( 'Each', 'woocommerce' ),			
			'PLT' => __( 'Pallet', 'woocommerce' )
			)
		)
	);
  
  echo '</div>';
	
}

// Save Fields
//add_action( 'woocommerce_process_product_meta', 'woo_add_custom_general_fields_save' );
function woo_add_custom_general_fields_save( $post_id ){
	// Select
	$woocommerce_select = $_POST['_woo_uom_input'];
	if( !empty( $woocommerce_select ) ) update_post_meta( $post_id, '_woo_uom_input', esc_attr( $woocommerce_select ) );
}


// First Register the Tab by hooking into the 'woocommerce_product_data_tabs' filter
add_filter( 'woocommerce_product_data_tabs', 'add_my_custom_application_tab' );
function add_my_custom_application_tab( $product_data_tabs ) {
	$new_tab_order = array(); 
	
	foreach($product_data_tabs as $key => $value) { 
		$new_tab_order[$key] = $value;
		if($key == 'shipping') {
			$new_tab_order['application-tab'] = array('label' => __( 'Application', 'my_text_domain' ), 'target' => 'my_custom_application_data');					
		}
	}
	return $new_tab_order;
}

//Add Applications Fields to Applications Tab
add_action( 'woocommerce_product_data_panels', 'add_my_custom_application_fields');
function add_my_custom_application_fields() {
	global $woocommerce, $post;
	?>
	<!-- id below must match target registered in above add_my_custom_product_data_tab function -->
	<div id="my_custom_application_data" class="panel woocommerce_options_panel">
		<?php

		
		// Select
		woocommerce_wp_select( 
		array( 
			'id'      => '_application_wall', 
			'label'   => __( 'Wall', 'woocommerce' ), 
			'options' => array(
			'true'   => __( 'Yes', 'woocommerce' ),
			'false'   => __( 'No', 'woocommerce' ))
		));

		// Select
		woocommerce_wp_select( 
		array( 
			'id'      => '_application_floor', 
			'label'   => __( 'Floor', 'woocommerce' ), 
			'options' => array(
			'true'   => __( 'Yes', 'woocommerce' ),
			'false'   => __( 'No', 'woocommerce' ))
		));
		
		woocommerce_wp_select( 
		array( 
			'id'      => '_application_backsplash', 
			'label'   => __( 'Backsplash', 'woocommerce' ), 
			'options' => array(
			'true'   => __( 'Yes', 'woocommerce' ),
			'false'   => __( 'No', 'woocommerce' ))
		));		

		woocommerce_wp_select( 
		array( 
			'id'      => '_application_shower', 
			'label'   => __( 'Shower/Wet Areas', 'woocommerce' ), 
			'options' => array(
			'true'   => __( 'Yes', 'woocommerce' ),
			'false'   => __( 'No', 'woocommerce' ))
		));		


		woocommerce_wp_select( 
		array( 
			'id'      => '_application_fireplace', 
			'label'   => __( 'Fireplaces Surround', 'woocommerce' ), 
			'options' => array(
			'true'   => __( 'Yes', 'woocommerce' ),
			'false'   => __( 'No', 'woocommerce' ))
		));	


		woocommerce_wp_select( 
		array( 
			'id'      => '_application_countertop', 
			'label'   => __( 'Countertops', 'woocommerce' ), 
			'options' => array(
			'true'   => __( 'Yes', 'woocommerce' ),
			'false'   => __( 'No', 'woocommerce' ))
		));	

		woocommerce_wp_select( 
		array( 
			'id'      => '_application_pool', 
			'label'   => __( 'Pool/Fountain', 'woocommerce' ), 
			'options' => array(
			'true'   => __( 'Yes', 'woocommerce' ),
			'false'   => __( 'No', 'woocommerce' ))
		));	

		
		?>
	</div>
	<?php
}

// Save Fields
add_action( 'woocommerce_process_product_meta', 'woo_add_my_custom_application_save' );

function woo_add_my_custom_application_save( $post_id ) {

	$applications = array('_application_wall','_application_floor', '_application_backsplash',  '_application_shower', '_application_shower', '_application_fireplace', '_application_countertop', '_application_pool');
		
	for($i=0;$i<sizeof($applications);$i++) { 
		if( !empty($_POST[$applications[$i]]) ) update_post_meta( $post_id, $applications[$i], esc_attr( $_POST[$applications[$i]] ) );
	}

} 

function get_uom($product_id, $qnty=0){
	$uom = get_post_meta($product_id, '_woo_uom_input', true);

    	switch($uom) { 
		case 'BOX': 
			$UOM = 'carton';
			break;
		case 'PLT': 
			$UOM = 'pallet';
			break;				
		case 'EA': 
			$UOM = 'each';
			break;				
		default: 
			$UOM = 'piece';				
		}
		
		if($qnty > 1) { 
			$UOM .= 's';	
		}
		
		$price .= " / $UOM";	

return $price;
}


// define the woocommerce_email_order_item_quantity callback 
function filter_woocommerce_email_order_item_quantity( $item_qty, $item ) { 
	$uom = get_uom($item["product_id"], $item_qty);
    return $item_qty. $uom; 
}; 
         
// add the filter 
add_filter( 'woocommerce_email_order_item_quantity', 'filter_woocommerce_email_order_item_quantity', 10, 2 ); 

//ADD SYSPRO CLIENT ID TO ORDER
function sv_wc_xml_export_add_vat_number_item( $format, $order ) {
	$new_format = array();
	$user_id = get_current_user_id();
	$syspro_id = get_user_meta($user_id,'_syspro_customer_id', true);
	
	$order_id   = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id;

	if(!empty($order_id)) {
		$order_syspro_id = get_post_meta($order_id , '_order_syspro_customer_id', true);
	} 

	foreach ( $format as $key => $data ) {
		$new_format[ $key ] = $data;
	
		if ( 'CustomerId' === $key ) {
			$new_format['CustomerId'] = $syspro_id;
			$new_format['SysproCustomerId'] = $syspro_id;
		}
		
		if ( 'CustomerNote' === $key ) {
			$post = get_post($order->ID);
			$new_format['CustomerNote'] = clean(str_replace(array("\n", "\r"), ' ',html_entity_decode($post->post_excerpt)));
		}		 
	}
	
	if(empty($new_format['CustomerId'])) { 
		$new_format['CustomerId'] = $GLOBALS["SYSPRO_ID"];
		$new_format['SysproCustomerId'] = $GLOBALS["SYSPRO_ID"];
	}
	
	if(empty($new_format['CustomerId'])) { 
		$new_format['CustomerId'] = $order_syspro_id;
		$new_format['SysproCustomerId'] = $order_syspro_id;
	} 	
	
	if(!empty($syspro_id)) { 
		$new_format['SysproCustomerId'] = $syspro_id;
	}
	
	if(!empty($order_syspro_id) && !empty($GLOBALS["SYSPRO_ID"]) && $order_syspro_id != $GLOBALS["SYSPRO_ID"]) { 
		$new_format['CustomerId'] = $order_syspro_id;
		$new_format['SysproCustomerId'] = $order_syspro_id;
	}	

	
	return $new_format;
}
add_filter( 'wc_customer_order_xml_export_suite_order_data', 'sv_wc_xml_export_add_vat_number_item', 20, 2 );


//NEW ORDER STATUS 
function register_new_order_status() {
    
    register_post_status( 'wc-posted', array(
        'label'                     => 'Posted to ERP',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Posted <span class="count">(%s)</span>', 'Posted <span class="count">(%s)</span>' )
    ) );
    
    register_post_status( 'wc-paid', array(
        'label'                     => 'Order Paid',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Paid <span class="count">(%s)</span>', 'Paid <span class="count">(%s)</span>' )
    ) );
    
}
add_action( 'init', 'register_new_order_status' );

// ADD CUSTOM ORDER STATUS TO REPORT
function woocommerce_reports_order_statuses_filter( $order_status ){
    $order_status[] = 'paid';
    $order_status[] = 'posted';
    return $order_status;
}
add_filter( 'woocommerce_reports_order_statuses', 'woocommerce_reports_order_statuses_filter' );


// Add to list of WC Order statuses
function add_awaiting_shipment_to_order_statuses( $order_statuses ) {

    $new_order_statuses = array();

    // add new order status after processing
    foreach ( $order_statuses as $key => $status ) {

        $new_order_statuses[ $key ] = $status;

        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-posted'] = 'Posted to ERP';
        }

        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-paid'] = 'Order Paid';
        }

    }

    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_awaiting_shipment_to_order_statuses' );


//Add order status icon CSS
add_action('admin_head', 'backorder_font_icon');

function backorder_font_icon() {
  echo '<style>
			.widefat .column-order_status mark.posted:after{
				font-family:WooCommerce;
				speak:none;
				font-weight:400;
				font-variant:normal;
				text-transform:none;
				line-height:1;
				-webkit-font-smoothing:antialiased;
				margin:0;
				text-indent:0;
				position:absolute;
				top:0;
				left:0;
				width:100%;
				height:100%;
				text-align:center;
				color: blue !important;
			}
			
			.widefat .column-order_status mark.paid:after{
				font-family:WooCommerce;
				speak:none;
				font-weight:400;
				font-variant:normal;
				text-transform:none;
				line-height:1;
				-webkit-font-smoothing:antialiased;
				margin:0;
				text-indent:0;
				position:absolute;
				top:0;
				left:0;
				width:100%;
				height:100%;
				text-align:center;
				color: green !important;
			}			
			.widefat .column-order_status mark.posted:after{
				content:"\e029";
				color:#ff0000;
			}
			.widefat .column-order_status mark.paid:after{
				content:"\e017";
				color:#ff0000;
			}			
  </style>';
}


//Change Place Order button text on checkout page in woocommerce
add_filter('woocommerce_pay_order_button_html','custom_order_button_text',1);
function custom_order_button_text($order_button_text) {
	global $order;
	
	$order_id = wc_get_order_id_by_order_key($_GET['key']);
	$invoice_num = get_post_meta( $order_id, '_invoice_number', true );
	
	if (is_wc_endpoint_url( 'order-pay' ) && !empty($invoice_num)) {
		$order_button_text = str_ireplace('Pay for order', "Pay Invoice", $order_button_text);
	} 
	
	return $order_button_text;
}

//Change the title of the endpoint to show Invoice #
function filter_woocommerce_endpoint_endpoint_title( $title, $endpoint ) { 
	
	$order_id = wc_get_order_id_by_order_key($_GET['key']);
	$invoice_num = get_post_meta( $order_id, '_invoice_number', true );
	
	if(!empty($order_id) && !empty($invoice_num)) { 
		$title = "INVOICE # ".$invoice_num;
	}
	
    return $title;
}; 
         
// add the filter 
add_filter( "woocommerce_endpoint_order-pay_title", 'filter_woocommerce_endpoint_endpoint_title', 10, 2 ); 


//REMOVE ALL OTHER PAYMENT METHODS FOR INVOICE PAYMENT	
//@snippet Enable Payment Gateway for "Order Pay" page | WooCommerce
//@how-to Watch tutorial @ https://businessbloomer.com/?p=19055
 
function bbloomer_enable_gateway_order_pay( $available_gateways ) {
	global $woocommerce;

	$order_id = wc_get_order_id_by_order_key($_GET['key']);
	$invoice_num = get_post_meta( $order_id, '_invoice_number', true );



	$syspro_credit_balance = esc_attr( get_the_author_meta( '_user_syspro_credit_balance', THE_USER_ID) );
	$total = $woocommerce->cart->total;	
	

	if(!empty($order_id) && !empty($invoice_num)) { 
		unset( $available_gateways['syspro_virtual_wallet']);
	}

	if($total > $syspro_credit_balance ) { 		
		unset( $available_gateways['syspro_virtual_wallet']);
	}

	unset( $available_gateways['offline_gateway']);
	unset( $available_gateways['cod']);

	
	return $available_gateways;
}
add_filter( 'woocommerce_available_payment_gateways', 'bbloomer_enable_gateway_order_pay' );



// DISABLE EMAIL NOTIFICATION FROM INVOICES PAID;
add_action( 'woocommerce_email', 'unhook_those_pesky_emails' );

function unhook_those_pesky_emails( $email_class ) {
	
	$order_id = wc_get_order_id_by_order_key($_GET['key']);
	$invoice_num = get_post_meta( $order_id, '_invoice_number', true );
	
	if(!empty($order_id) && !empty($invoice_num)) { 
		remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_pending_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );

		remove_action( 'woocommerce_order_status_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_processing_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
		
		// Completed order emails
		remove_action( 'woocommerce_order_status_completed_notification', array( $email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger' ) );
	} 
}


// REDIRECT USER AFTER PAYING INVOICE;
add_action( 'woocommerce_thankyou', 'bbloomer_redirectcustom');
 
function bbloomer_redirectcustom( $order_id ){

 	$invoice_num = get_post_meta( $order_id, '_invoice_number', true );

	if(!empty($order_id) && !empty($invoice_num)) { 
		$order = new WC_Order($order_id);
		$order->update_status('wc-paid', 'Invoice Paid'); 		
		
	    $url = get_permalink( get_option('woocommerce_myaccount_page_id') );
		wp_redirect($url);
		exit;
	} 
 
}

//REMOVE CANCEL BUTTON FROM WOOCOMMERCE ORDERS TABLE: 
function sv_add_my_account_order_actions( $actions, $order ) {
	unset($actions['cancel']);
    return $actions;
}
add_filter( 'woocommerce_my_account_my_orders_actions', 'sv_add_my_account_order_actions', 10, 2 );

// UPDATED ON 12/11/18 to $int2 from $int to comply with PHP 7
// SHOW PARTIAL PAYMENT ON INVOICES 
function filter_woocommerce_get_order_item_totals( $total_rows, $int, $int2 ) { 
	$order_id = wc_get_order_id_by_order_key($_GET['key']);
	$invoice_num = get_post_meta( $order_id, '_invoice_number', true );
	$new_rows = array();
	
	if(!empty($order_id) && !empty($invoice_num)) { 
	foreach($total_rows as $key => $val) { 
		
		$order_total = get_post_meta( $order_id, '_order_total', true );
		$balance_in_syspro = get_post_meta( $order_id, '_current_syspro_balance', true );
		if(!empty($balance_in_syspro) && $balance_in_syspro > 0) {
			$pre_paid = $order_total - $balance_in_syspro;
		} else { 
			$pre_paid = 0;
		}
		
		if($key != 'payment_method') {
			if($key == 'order_total' && !empty($pre_paid) && $pre_paid > 0) { 
				$new_rows['pre_payments'] = array('label' => 'Pre-Paid', 'value' => "-".wc_price($pre_paid));
				$val['value'] = wc_price($order_total - $pre_paid);
			}
			$new_rows[$key] = $val;
		} 
	}
	} else { 
		$new_rows = $total_rows;
	}

    return $new_rows; 
}; 
add_filter( 'woocommerce_get_order_item_totals', 'filter_woocommerce_get_order_item_totals', 10, 3 ); 


function mysite_woocommerce_order_status_completed( $order_id ) {
	$order = new WC_Order( $order_id );
	$invoice_num = get_post_meta( $order_id, '_invoice_number', true );
	$key = substr($_SERVER["REDIRECT_QUERY_STRING"],0, 3);
		
	if(!empty($key) && $key == "key" && !empty($invoice_num)) {
		$order->update_status('wc-paid', 'Invoice Paid Using Credit Card', true);
	} 
}
add_action( 'woocommerce_order_status_completed', 'mysite_woocommerce_order_status_completed', 10, 1 );

// Remove download Tab from MyAccount Page
function custom_my_account_menu_items( $items ) {
    unset($items['downloads']);
    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'custom_my_account_menu_items' );

class SurfacesOrderPortal {
	
	public function __construct() { 
		return true;
	}

}
?>