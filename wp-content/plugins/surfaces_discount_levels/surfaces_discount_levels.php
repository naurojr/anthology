<?php
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );	
/*
Plugin Name: Surfaces Discount Levels
Description: Manage Discount Levels on users
Version: 1.1
Author: Nauro Rezende Jr
*/


class DiscountLevels {

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
        $discount_distributor = get_option('discount_distributor_level');
        $discount_dealer = get_option('discount_dealer_level');
	?>
	<div class='wrap'>
		<h1 class="wp-heading-inline" >Surfaces Discount Level Management</h1>
		<div class="postbox">
			<h2 class="hndle ui-sortable-handle" style="padding: 8px; padding-top: 0;"><span>Set Discounts Levels</span></h2>
			<div class="inside">
			<form method="post">
				<table>
					<thead>
						<td colspan="2"><p class='howto'><small>To set discount please use formula (P*0.50) - Where P is price</small></p></th>
					</thead>
					<tbody>
						<tr>
							<td width="20%"><label for="csv_import">Distributor:</label></td><td><input name="discount_distributor_level" id="discount_distributor_level" type="text" aria-required="true" value="<?php echo $discount_distributor; ?>"/></td>
						</tr>
						<tr>
							<td width="20%">Dealer:</td><td><input name="discount_dealer_level" id="discount_dealer_level" type="text" aria-required="true" value="<?php echo $discount_dealer; ?>"/></td>
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
        
        if(!empty(strip_tags($_POST['discount_distributor_level'])) || !empty(strip_tags($_POST['discount_dealer_level']))) { 
	        wp_cache_delete ( 'alloptions', 'options' );
	        $this->set_discount_levels($_POST['discount_distributor_level'], $_POST['discount_dealer_level'] );
        }
        
        $this->log['notice'][] = "<b>Discount Levels have been Saved.</b>";
        $this->print_messages();
        
    }
    
    function set_discount_levels($distributor_discount, $dealer_discount) { 
			if (!empty($distributor_discount)) {
				update_option('discount_distributor_level', strtoupper(strip_tags($distributor_discount)));
			}

			if (!empty($dealer_discount)) {
				update_option('discount_dealer_level', strtoupper(strip_tags($dealer_discount)));
			}				       
    }
}





function add_discount_levels() {
    require_once ABSPATH . '/wp-admin/admin.php';
    $plugin = new DiscountLevels;
    add_management_page('Discount Levels', 'Discount Levels', 'manage_options', __FILE__, array($plugin, 'form'));
}

add_action('admin_menu', 'add_discount_levels');


function discount_levels_activate() {

	// CREATE MULTI PRICE LIST ARRAY
		add_option('discount_distributor_level', '(P*0.50)');
		add_option('discount_dealer_level', '((P*0.50)*1.15)');
		add_option('discount_levels', 'distributor,dealer');

}

register_activation_hook( __FILE__, 'discount_levels_activate' );


// ADD DROP DOWN FOR MULTI PRICE LIST ON USER
add_action( 'show_user_profile', 'multi_price_lists_extra_profile_fields' );
add_action( 'edit_user_profile', 'multi_price_lists_extra_profile_fields' );

function multi_price_lists_extra_profile_fields( $user ) { ?>

	<h3>Discount Level</h3>

	<table class="form-table">

		<tr>
			<th><label for="twitter">Set User Discount Level</label></th>

			<td>
				<?php 
					$user_discount_level = esc_attr( get_the_author_meta( '_customer_discount_level', $user->ID ) ); 
						if(empty($user_discount_level)){ $user_discount_level = 'none'; } 
						$no_discount_array[] = 'none';
						$more_levels = explode(",",get_option('discount_levels'));
						$discount_level_array = array_merge($no_discount_array, $more_levels);
				?>
				<select name="_customer_discount_level" id="_customer_discount_level" style='width:15em;'>
					<?php for($i=0;$i<sizeof($discount_level_array);$i++): 
							if(!empty(trim($discount_level_array[$i]))):
							?>
							<option value='<?php echo $discount_level_array[$i]?>' <?php if($discount_level_array[$i] == $user_discount_level) echo "selected"; ?>><?php echo ucfirst(trim($discount_level_array[$i])); ?></option>
							<?php endif; ?>
					<?php endfor; ?>
				</select>
			</td>
		</tr>

	</table>
<?php }
	
// SAVE USER DISCOUNT LEVEL
add_action( 'personal_options_update', 'discount_levels_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'discount_levels_save_extra_profile_fields' );

function discount_levels_save_extra_profile_fields( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
	update_user_meta( $user_id, '_customer_discount_level', $_POST['_customer_discount_level'] );
}	


function calculate_discount($price, $level) { 
	if(!empty($level) && $level != 'none') { 
		$discount = get_option("discount_". $level ."_level");
		$Cal = new Field_calculate();
		$discount = $Cal->calculate(str_replace('P', $price, $discount));
		$price = cutNum($price - $discount);
	}
	
	return $price; 
}


function cutNum($num, $precision = 2){
    return floor($num).substr($num-floor($num),1,$precision+1);
}



// CHANGE PRICE ON CART BASED DISCOUNT LEVEL
add_action( 'woocommerce_before_calculate_totals', 'add_custom_price_with_discount', 99);
function add_custom_price_with_discount( $cart_object ) {
  
  	$user_discount_level = esc_attr( get_the_author_meta( '_customer_discount_level', get_current_user_id() ) ); 
  	
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
	    $product_id = $cart_item['data']->get_id();
	    $relugar_price = get_post_meta($product_id, '_regular_price');
	    
	    if(!has_term('Marketing Material', 'product_cat', $product_id)) {
		    if($cart_item['data']->get_price() ==  $relugar_price[0]) {
		    $new_price = calculate_discount($cart_item['data']->get_price(), trim($user_discount_level)); 
		    	if(!empty($new_price) && $new_price > 0) {  $cart_item['data']->set_price($new_price); }
	    	} 
	    } 
    }
}


add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_startsinfo', 11 );
function woocommerce_template_single_startsinfo() {
	global $post;
  	$user_discount_level = esc_attr( get_the_author_meta( '_customer_discount_level', get_current_user_id() ) ); 
  	
  	if($user_discount_level != 'none') { 
	$price = get_post_meta($post->ID, '_regular_price');
  	$discounted_price = wc_price(calculate_discount($price[0], trim($user_discount_level)));   	
  	
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
		if(!has_term('Marketing Material', 'product_cat', $post->ID)) {		
			echo "<div id='show_discount' class='alert alert-primary' role='alert'>As a $user_discount_level you pay only ".$discounted_price ." / $UOM</div>";
		} 
	} 
}



function calculate_string( $mathString )    {
    $mathString = trim($mathString);     // trim white spaces
    $mathString = ereg_replace ('[^0-9\+-\*\/\(\) ]', '', $mathString);    // remove any non-numbers chars; exception for math operators
 
    $compute = create_function("", "return (" . $mathString . ");" );
    return 0 + $compute();
}


class Field_calculate {
    const PATTERN = '/(?:\-?\d+(?:\.?\d+)?[\+\-\*\/])+\-?\d+(?:\.?\d+)?/';

    const PARENTHESIS_DEPTH = 10;

    public function calculate($input){
        if(strpos($input, '+') != null || strpos($input, '-') != null || strpos($input, '/') != null || strpos($input, '*') != null){
            //  Remove white spaces and invalid math chars
            $input = str_replace(',', '.', $input);
            $input = preg_replace('[^0-9\.\+\-\*\/\(\)]', '', $input);

            //  Calculate each of the parenthesis from the top
            $i = 0;
            while(strpos($input, '(') || strpos($input, ')')){
                $input = preg_replace_callback('/\(([^\(\)]+)\)/', 'self::callback', $input);

                $i++;
                if($i > self::PARENTHESIS_DEPTH){
                    break;
                }
            }

            //  Calculate the result
            if(preg_match(self::PATTERN, $input, $match)){
                return $this->compute($match[0]);
            }
            // To handle the special case of expressions surrounded by global parenthesis like "(1+1)"
            if(is_numeric($input)){
                return $input;
            }

            return 0;
        }

        return $input;
    }

    private function compute($input){
        $compute = create_function('', 'return '.$input.';');

        return 0 + $compute();
    }

    private function callback($input){
        if(is_numeric($input[1])){
            return $input[1];
        }
        elseif(preg_match(self::PATTERN, $input[1], $match)){
            return $this->compute($match[0]);
        }

        return 0;
    }
}


?>