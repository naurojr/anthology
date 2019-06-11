<?php
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );	
/*
Plugin Name: Surfaces Multi Price Lists
Description: Enable Multi Pricelists on WooCommerce Products and Users
Version: 1.1
Author: Nauro Rezende Jr
*/


class MultiPriceList {

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
		$multi_price_list = get_option('multi_price_list');
	?>
	<div class='wrap'>
		<h1 class="wp-heading-inline" >Surfaces Multi Price List Management</h1>
		<div class="postbox">
			<h2 class="hndle ui-sortable-handle" style="padding: 8px; padding-top: 0;"><span>Price List Codes</span></h2>
			<div class="inside">
			<form method="post">
				<label for="csv_import">PRICE LISTS: <input name="price_lists" id="price_lists" type="text" aria-required="true" value="<?php echo $multi_price_list; ?>"/></label>
				<p class='howto'><small>PRICE LIST SHOULD BE COMMA SEPARATED A,B,C,D,...,Z</small></p>
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
        
        if(!empty(strip_tags($_POST['price_lists']))) { 
	        wp_cache_delete ( 'alloptions', 'options' );
	        $this->set_multi_price_list($_POST['price_lists']);
        }
        
        $this->log['notice'][] = "<b>Multi Price List ". $_POST['price_lists'] ." Has Been Saved.</b>";
        $this->print_messages();
        
    }
    
    function set_multi_price_list($lists) { 
			if (!empty($lists)) {
				update_option('multi_price_list', strip_tags($lists));
			}	       
    }
}





function add_multi_pricelist() {
    require_once ABSPATH . '/wp-admin/admin.php';
    $plugin = new MultiPriceList;
    add_management_page('Multi Price List', 'Multi Price List', 'manage_options', __FILE__, array($plugin, 'form'));
}

add_action('admin_menu', 'add_multi_pricelist');


function multi_price_list_activate() {

	// CREATE MULTI PRICE LIST ARRAY
	$multi_price_list = get_option('multi_price_list');
	if(!get_option('multi_price_list')) { 
		add_option('multi_price_list', 'A,B,C');
	} 

}

register_activation_hook( __FILE__, 'multi_price_list_activate' );


// ADD DROP DOWN FOR MULTI PRICE LIST ON USER
add_action( 'show_user_profile', 'multi_price_lists_extra_profile_fields' );
add_action( 'edit_user_profile', 'multi_price_lists_extra_profile_fields' );

function multi_price_lists_extra_profile_fields( $user ) { ?>

	<h3>Multi Price List</h3>

	<table class="form-table">

		<tr>
			<th><label for="twitter">Set User Prices List</label></th>

			<td>
				<?php 
					$user_price_list = esc_attr( get_the_author_meta( '_customer_price_list_id', $user->ID ) ); 
						if(empty($user_price_list)){ $user_price_list = 'B'; } 
						$price_list_array = explode(",",get_option('multi_price_list'));
				?>
				<select name="_customer_price_list_id" id="_customer_price_list_id" style='width:15em;'>
					<?php for($i=0;$i<sizeof($price_list_array);$i++): 
							if(!empty(trim($price_list_array[$i]))):
							?>
							<option value='<?php echo $price_list_array[$i]?>' <?php if($price_list_array[$i] == $user_price_list) echo "selected"; ?>>Price List <?php echo $price_list_array[$i]; ?></option>
							<?php endif; ?>
					<?php endfor; ?>
				</select>
			</td>
		</tr>

	</table>
<?php }


// SAVE SYSPRO USER ID
add_action( 'personal_options_update', 'multi_price_lists_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'multi_price_lists_save_extra_profile_fields' );

function multi_price_lists_save_extra_profile_fields( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
	update_user_meta( $user_id, '_customer_price_list_id', $_POST['_customer_price_list_id'] );
}


// First Register the Tab by hooking into the 'woocommerce_product_data_tabs' filter
if(is_plugin_active( 'woocommerce/woocommerce.php')) { 
	add_filter( 'woocommerce_product_data_tabs', 'add_multi_price_list_tab');	
	add_action( 'admin_head', 'wcpp_custom_style' );
	add_action( 'woocommerce_product_data_panels', 'add_multi_price_list_fields');
	add_action( 'woocommerce_process_product_meta', 'woo_multi_price_list_save',1);	
}
function add_multi_price_list_tab( $product_data_tabs ) {
	$product_data_tabs['multi-prices-tab'] = array(
		'label' => __( 'Multi Prices', 'my_text_domain' ),
		'target' => 'add_multi_price_list_data',
	);
	
	$product_data_tabs['multi-prices-tab']['priority'] = 12;
	return $product_data_tabs;
}

/** CSS To Add Custom tab Icon */
function wcpp_custom_style() {?>
<style>
#woocommerce-product-data ul.wc-tabs li.multi-prices-tab_options a:before { content: "\f145";; }
</style>
<?php 
}


function add_multi_price_list_fields() { 
	global $woocommerce, $post;
	
	$price_list_array = explode(",",get_option('multi_price_list'));
	
	?>
	<!-- id below must match target registered in above add_my_custom_product_data_tab function -->
	<div id="add_multi_price_list_data" class="panel woocommerce_options_panel">
	<?php
		for($i=0;$i<sizeof($price_list_array);$i++) { 
			
			if(!empty(trim($price_list_array[$i]))) {
			
			$field_name = '_wc_price_list_'.$price_list_array[$i];
			$field_label = 'Price List '.$price_list_array[$i];
			
			woocommerce_wp_text_input(array(
			'id'=>$field_name, 
			'label'=>$field_label,
			'data_type'=>'price'
			));
			
			} 
		}
		
	?>
	</div>
	<?php		
}


function woo_multi_price_list_save($post_id) {
	
	$price_list_array = explode(",",get_option('multi_price_list'));
	$field_name = array();
	
	for($i=0;$i<sizeof($price_list_array);$i++) { 
		
		if(!empty(trim($price_list_array[$i]))) { 
			$field_name[] = '_wc_price_list_'.$price_list_array[$i];
		}
	}
	
	for($i=0;$i<sizeof($field_name);$i++) { 
		if( !empty($_POST[$field_name[$i]]) ) {
			update_post_meta( $post_id, $field_name[$i], esc_attr($_POST[$field_name[$i]]));
		} else { 
			delete_post_meta( $post_id, $field_name[$i]);	
		}
	}
	
} 


add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_startsinfo', 12 );
function woocommerce_template_single_startsinfo() {
	global $post;
  	

	$price = get_post_meta($post->ID, '_regular_price');
	$discounted_price = 0;
	  	
	$uom = get_post_meta($post->ID, '_woo_uom_input', true);


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
		
	if(!has_term('Marketing Material', 'product_cat', $post->ID) && is_user_logged_in()) {		
		echo "<div id='show_discount' class='alert alert-secondary' role='alert'><strong>MSRP ".wc_price($price[0]) ." / $UOM</strong></div>";
	}  
}

?>
