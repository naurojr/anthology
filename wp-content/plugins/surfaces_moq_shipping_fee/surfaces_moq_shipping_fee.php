<?php
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
require_once( plugin_dir_path( __DIR__ ) . '/woocommerce/woocommerce.php');
/*
Plugin Name: TRI - MOQ Shipping Fee
Description: Add a fee to the order if order is smaller than MOQ.
Version: 1.0
Author: Nauro Rezende Jr
*/

defined( 'ABSPATH' ) or exit;

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}




class MOQFees {

    var $log = array();

    /**
     * Plugin's interface
     *
     * @return void
     */
    function form() {

        if ('POST' == $_SERVER['REQUEST_METHOD']) {
            $this->post();
        }
        $order_moq = get_option('order_moq');
        $moq_fee = get_option('moq_fee');
	?>
	<div class='wrap'>
		<h1 class="wp-heading-inline" >Surfaces Minimum Order Quantity Management</h1>
		<div class="postbox">
			<h2 class="hndle ui-sortable-handle" style="padding: 8px; padding-top: 0;"><span>Set MOQ and Fee in $</span></h2>
			<div class="inside">
			<form method="post">
				<table>
					<tbody>
						<tr>
							<td width="20%"><label for="csv_import">MOQ:</label></td><td><input name="order_moq" id="order_moq" type="number" aria-required="true" value="<?php echo $order_moq; ?>"/></td>
						</tr>
						<tr>
							<td width="20%">Fee $:</td><td><input name="moq_fee" id="moq_fee" step="0.01" type="number" aria-required="true" value="<?php echo $moq_fee; ?>"/></td>
						</tr>
					</tbody>
				</table>
				<input type="submit" class="button" name="submit" value="Save" />
			</form>
			</div>
		</div>
	</div>

<?php
    }

    function print_messages() {
        if (!empty($this->log)) {
?>

<div class="wrap">
    <?php if (!empty($this->log['error'])): ?>

    <div class="error">

        <?php foreach ($this->log['error'] as $error): ?>
            <p><?php echo $error; ?></p>
        <?php endforeach; ?>

    </div>

    <?php endif; ?>

    <?php if (!empty($this->log['notice'])): ?>

    <div class="updated fade">

        <?php foreach ($this->log['notice'] as $notice): ?>
            <p><?php echo $notice; ?></p>
        <?php endforeach; ?>

    </div>

    <?php endif; ?>
</div><!-- end wrap -->

<?php
        // end messages HTML }}}

            $this->log = array();
        }
    }
    /**
     * Handle POST submission
     *
     * @param array $options
     * @return void
     */
    function post($options = "") {


        if (!current_user_can('publish_pages') || !current_user_can('publish_posts')) {
            $this->log['error'][] = 'You don\'t have the permissions to publish posts and pages. Please contact the blog\'s administrator.';
            $this->print_messages();
            return;
        }

        if(!empty(strip_tags($_POST['order_moq'])) || !empty(strip_tags($_POST['moq_fee']))) {
	        wp_cache_delete ( 'alloptions', 'options' );
	        $this->set_moq_and_fee($_POST['order_moq'], $_POST['moq_fee'] );
        }

        $this->log['notice'][] = "<b>MOQ and Fee have been saved.</b>";
        $this->print_messages();

    }

    function set_moq_and_fee($order_moq, $moq_fee) {
			if (!empty($order_moq)) {
				update_option('order_moq', strtoupper(strip_tags($order_moq)));
			}

			if (!empty($moq_fee)) {
				update_option('moq_fee', strtoupper(strip_tags($moq_fee)));
			}
    }
}


function add_moq_management() {
    require_once ABSPATH . '/wp-admin/admin.php';
    $plugin = new MOQFees;
    add_management_page('MOQ Management', 'MOQ Management', 'manage_options', __FILE__, array($plugin, 'form'));
}

add_action('admin_menu', 'add_moq_management');


function moq_management_activate() {

		add_option('order_moq', '5');
		add_option('moq_fee', '15');

}

register_activation_hook( __FILE__, 'moq_management_activate' );




function add_shipping_fee_message() {
	$moq = get_option('order_moq');
	$moq_fee = get_option('moq_fee');
	?>
    <div class="alert alert-primary" role="alert" id="shipping_fee" style='display:none;'>A shipping fee of <?php echo wc_price($moq_fee); ?> will be added to your order because as you're ordering less than <?php echo $moq; ?> valid item.</div>
    <script type='text/javascript'>
		jQuery('document').ready(function($){
			jQuery( document.body ).on( 'updated_cart_totals', function(){
				var fees = $('.fee').find('[data-title="Shipping Fee"]');

				if(fees.length > 0) {
					$('#shipping_fee').fadeIn();
				} else {
					$('#shipping_fee').fadeOut();
				}
			});
			jQuery( document ).ready(function($) {
				var fees = $('.fee').find('[data-title="Shipping Fee"]');

				if(fees.length > 0) {
					$('#shipping_fee').fadeIn();
				} else {
					$('#shipping_fee').fadeOut();
				}
			});

		});
	</script>
    <?php
}


add_action('woocommerce_before_cart', 'add_shipping_fee_message', 50);



//ADD SHIPPING FEE FOR ORDER SMALLER THAN MOQ
function woo_add_moq_cart_fee($cart_objects) {
  global $woocommerce;
  //$quantity = WC()->cart->get_cart_contents_count();

  $add_moq_fee = false;
  $has_box = false;
	$has_mkt = false;
  $moq = get_option('order_moq');
  $moq_fee = get_option('moq_fee');


  $quantity = 0;

  foreach( WC()->cart->get_cart() as $cart_item ) {
  	if($cart_item['product_id'] != 261) {
	  $uom = get_post_meta($cart_item['product_id'], '_woo_uom_input', true);
	  $quantity += $cart_item['quantity'];

	  	if($uom == 'BOX') {
		  	$has_box = true;
	  	}

			//REMOVE SHIPPING CHARGE IF MARKETING MATERIAL IS PRESENT
			if ( has_term( 'marketing-material', 'product_cat', $cart_item['product_id'] ) ) {
        	$has_mkt = true;
    	}

		} else {
    	$has_mkt = true;
		}
  }

  if($quantity < $moq && $has_box == false && $has_mkt == false) {
	 $add_moq_fee = true;
  }

  if($add_moq_fee == true) {
	  $woocommerce->cart->add_fee( __('Shipping Fee', 'woocommerce'),  $moq_fee);
  }
}
add_action( 'woocommerce_before_calculate_totals', 'woo_add_moq_cart_fee');
