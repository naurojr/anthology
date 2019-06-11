<?php
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
require_once( plugin_dir_path( __DIR__ ) . '/woocommerce/woocommerce.php');	
/*
Plugin Name: TRI - Marketing Material Rebate
Description: Create a Virtual Wallet System to manage Marketing Rebates
Version: 1.0
Author: Nauro Rezende Jr
*/

defined( 'ABSPATH' ) or exit;

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

// SHOW USER CREDIT DATA
add_action( 'show_user_profile', 'user_marketing_rebate_on_profile' );
add_action( 'edit_user_profile', 'user_marketing_rebate_on_profile' );

function user_marketing_rebate_on_profile( $user ) { 
	$user_marketing_rebate = esc_attr( get_the_author_meta( '_user_marketing_rebate', $user->ID ) );
	if(empty($user_marketing_rebate) || $user_marketing_rebate < 0) $user_marketing_rebate = 0;


	if(empty($syspro_credit_balance) || $syspro_credit_balance < 0) $syspro_credit_balance = 0;
	?>
		<hr>
		<h3><span class="dashicons dashicons-cart"></span> User Marketing Rebate Wallet</h3>

		<table class="form-table">

		<tr>
			<th><label for="twitter">User Rebate</label></th>

			<td>
				<input type="number" step="any" min="0" name="_user_marketing_rebate" id="_user_marketing_rebate" value="<?php echo $user_marketing_rebate; ?>" class="regular-text" /><br />
				<span class="description">Inform the current amount of rebates originated from the purchase of marketing material the user have.</span>
			</td>
		</tr>
		</table>	
<?php }	

// SAVE CREDIT DATA FOR USER
add_action( 'personal_options_update', 'save_user_marketing_rebate_on_profile' );
add_action( 'edit_user_profile_update', 'save_user_marketing_rebate_on_profile' );

function save_user_marketing_rebate_on_profile( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;
		
	/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
	update_user_meta( $user_id, '_user_marketing_rebate', $_POST['_user_marketing_rebate'] );
}

// SET INITIAL CREDIT VALUES FOR USERS
add_action( 'user_register', 'create_inital__marketing_rebate_for_user', 10, 1 );
function create_inital__marketing_rebate_for_user( $user_id ) {

    if ( isset( $user_id ) ) {
        update_user_meta($user_id, '_user_marketing_rebate', 0);
	} 
}

//ADD MARKETING REBATE TO CART
function woo_add_cart_fee($cart_objects) {
  global $woocommerce;
  $user_marketing_rebate = esc_attr( get_the_author_meta( '_user_marketing_rebate', get_current_user_id() ) );
  $rebate = 0-($user_marketing_rebate*0.1);	  
  $sub_total = $woocommerce->cart->subtotal;
  
  //CHECK IF DISPLAY IS PART OF THE ORDER AND REDUCE DISPLAY COST FROM SUBTOTAL
  foreach( WC()->cart->get_cart() as $cart_item ) {

	  if(has_term('Marketing Material', 'product_cat', $cart_item['product_id'])) {	
		  $sub_total -= $cart_item['line_subtotal'];	  
	  } 
  
	  //if($cart_item['product_id'] == 261) {  $sub_total -= $cart_item['line_subtotal']; }
  }

  $rebate = $sub_total*0.1;
  
  if($user_marketing_rebate < $rebate) {
	  $rebate = 0 - $user_marketing_rebate;
  } else { 
	  $rebate = 0 - $rebate;
  }

  //IF REBATE IS DIFFERENT THAN ZERO AND SMALLER THAN SUB_TOTAL APPLY REBATE TO ORDER 
  if($rebate != 0 && (0-$rebate) < $sub_total) { 	 
  	$woocommerce->cart->add_fee( __('Rebate', 'woocommerce'),  $rebate);
  }
}
add_action( 'woocommerce_cart_calculate_fees', 'woo_add_cart_fee');



//Information content to My Credit Tab
function tri_information_endpoint_content() {
	  $user_marketing_rebate = esc_attr( get_the_author_meta( '_user_marketing_rebate', get_current_user_id() ) );
	?>

        <table class="shop_table" id="credit-availability">
			<tbody>
				<tr class="cart-subtotal">
					<th>Your Marketing Rebate Availabiltiy is: </th>
					<td class="product-total"><?php echo wc_price($user_marketing_rebate); ?></td>
				</tr>
			</tbody>
		</table>
		<p>Marketing Rebate is automatically applied to your order. Rebates are limited to 10% of the total of the order. For more information ask you sales rep. about marketing rebates. <p>

    <?php 
}

add_action( 'woocommerce_account_my-credit_endpoint', 'tri_information_endpoint_content', 90 );


//GIVE MARKETING REBATE CREDIT FOR USER
add_action( 'woocommerce_thankyou', 'give_marketing_rebate_credit',  10, 1  );

function give_marketing_rebate_credit( $order_id ) {
	global $woocommerce;
	$user_id = get_current_user_id();
	$user_marketing_rebate = esc_attr( get_the_author_meta( '_user_marketing_rebate', $user_id  ) );	
	
	$order_detail = new WC_Order( $order_id );
	$rebate_applied = get_post_meta($order_id, '_rebate_applied', true);
	
	if($rebate_applied != 'true') { 
	//add rebate credit to virtual wallet when display is ordered
	$add_new_rebate = 0;
	foreach ( $order_detail->get_items() as $item_id => $item ) {
	    $product_id = $item->get_product_id();
	    
		if(has_term('Marketing Material', 'product_cat', $product_id)) {		    
			$add_new_rebate += $item->get_subtotal();   
		} 
	}

	$add_new_rebate = $add_new_rebate + $user_marketing_rebate;

	// subtract rebate used from virtual wallet
	$sub_new_rebate = 0;
	foreach ( $order_detail->get_items('fee') as $item_id => $item_fee) {
		  
		  // The fee name
		  $fee_name = $item_fee->get_name();
		  // The fee total amount
		  $fee_total = $item_fee->get_total();
		  
		  if($item_fee->get_name() === 'Rebate') { 
			  $sub_new_rebate = $item_fee->get_total();
		  }
	}	
	
	$new_rebate = $add_new_rebate - (0-$sub_new_rebate);	
	if($new_rebate != $user_marketing_rebate && $new_rebate > 0) {
		update_user_meta($user_id, '_user_marketing_rebate', $new_rebate, $user_marketing_rebate);	 
	} 

	add_post_meta($order_id, '_rebate_applied', 'true', true);
	} 

}
