<?php 
//ANTHOLOGY
require_once('bs4navwalker.php');	
	
add_theme_support( 'menus' );
add_theme_support( 'html5' );
add_theme_support( 'widgets' );
add_theme_support( 'post-thumbnails', array( 'post', 'product', 'page' ));

define('THE_USER_ID', get_current_user_id());


if(!isset($GLOBALS['USER_PRICE_LIST'])) { 
	$GLOBALS['USER_PRICE_LIST'] = get_user_meta(THE_USER_ID, '_customer_price_list_id', true);
	if(empty($GLOBALS['USER_PRICE_LIST'])) $GLOBALS['USER_PRICE_LIST'] = 'DED';
}

if(empty($GLOBALS["SYSPRO_ID"])) {
	$user_id = get_current_user_id();
	$syspro_id = get_user_meta($user_id,'_syspro_customer_id', true);
	
	if(!empty($syspro_id)) {
		$GLOBALS["SYSPRO_ID"] = $syspro_id;
	} 

}

add_action( 'after_setup_theme', 'woocommerce_support');

function woocommerce_support() {
    add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );    
}


//Remove Admin Bar
add_filter('show_admin_bar', '__return_false');

/**
 * Proper way to enqueue scripts and styles
 */
function theme_name_scripts() {
	//wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css');
	//wp_enqueue_style( 'fontawesome', get_template_directory_uri() . '/css/font-awesome.min.css');
	wp_enqueue_style( 'style', get_stylesheet_uri());
	
	//wp_enqueue_script('jquery');
	//wp_enqueue_script('bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js',  array('jquery'));
	wp_enqueue_script('website_script', get_template_directory_uri() . '/script.js',  array('jquery'));
}

add_action( 'wp_enqueue_scripts', 'theme_name_scripts' );


if ( function_exists('register_sidebar') ) {
	register_sidebar(array( 'name' => 'Woocommerce', 'id' => 'woo_sidebar', 'before_widget' => '<div id="%1$s" class="col-sm-11 widgetSidebar %2$s">', 'after_widget' => '</div>', 'before_title' => '<p>','after_title' => '</p>'));
}


//Remove <ul> from Menu </ul>
function my_wp_nav_menu_args( $args = '' ) {
	$args['container'] = false;
	return $args;
}
add_filter( 'wp_nav_menu_args', 'my_wp_nav_menu_args' );

add_action( 'after_setup_theme', 'register_my_menu' );
function register_my_menu() {
  register_nav_menu( 'top', 'Top Menu' );
}

add_filter('woocommerce_form_field_args',  'wc_form_field_args',10,3);
  function wc_form_field_args($args, $key, $value) {
  $args['input_class'] = array( 'form-control' );
  return $args;
}


//Remove Menus with Logge-only class when user is not logged in.
function tgm_filter_menu_class( $objects ) {
 	foreach($objects as $k=>$object) { 
		if(in_array('logged-only', $object->classes)) { 
			if(!is_user_logged_in()) {
				unset($objects[$k]);
			} 
		}	 	
 	}
    // Return the menu objects
    return $objects;
}
add_filter( 'wp_nav_menu_objects', 'tgm_filter_menu_class', 10, 1);



/**
 * Add a login link to the members navigation
 */
function add_logout_link( $items, $args )
{
    if($args->theme_location == 'top')
    {
        if(is_user_logged_in()) {
	      	$items .= '<li><a href="'. get_the_permalink(234) .'">DEALER PAGE</a></li>';
	        $items .= '<li><a href="'. wp_logout_url() .'">LOGOUT</a></li>';
	    } 
    }

    return $items;
}
add_filter( 'wp_nav_menu_items', 'add_logout_link', 10, 2);


//REDIRECT LOGIN
function admin_default_page() {
	if( current_user_can('administrator')) {
		return site_url();
	} else { 
		return get_permalink(234);
	}
		
}
add_filter('login_redirect', 'admin_default_page');


//ESCAPE CustomerNotes TO MAKE IT EASIER TO IMPORT
function escape_customer_comment_on_xml( $format, $order ) {
	$new_format = array();
	foreach ( $format as $key => $data ) {
		$new_format[ $key ] = $data;
		if ( 'CustomerNote' === $key ) {
			$post = get_post($order->ID);
			$new_format['CustomerNote'] = clean(str_replace(array("\n", "\r"), ' ',html_entity_decode($post->post_excerpt)));
		}
	}
	return $new_format;
}


//add_filter( 'wc_customer_order_xml_export_suite_order_data', 'escape_customer_comment_on_xml', 10, 2 );

//REMOVE ALL SPECIAL CHARS FROM STRING
function clean($string) {
   return preg_replace('/[^A-Za-z0-9\-]/', ' ', $string); // Removes special chars.
}


// SHOW SYSPRO USER ID
add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'my_show_extra_profile_fields' );

function my_show_extra_profile_fields( $user ) { ?>

	<h3>Extra profile information</h3>

	<table class="form-table">

		<tr>
			<th><label for="twitter">Syspro ID</label></th>

			<td>
				<input type="text" name="_syspro_customer_id" id="_syspro_customer_id" value="<?php echo esc_attr( get_the_author_meta( '_syspro_customer_id', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Please enter Syspro User Id.</span>
			</td>
		</tr>

	</table>
<?php }


// SAVE SYSPRO USER ID
add_action( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'my_save_extra_profile_fields' );

function my_save_extra_profile_fields( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
	update_user_meta( $user_id, '_syspro_customer_id', $_POST['_syspro_customer_id'] );
}

//UPDATE SYSPRO ID ORDER META WHEN ORDER IS SAVED
add_action('woocommerce_checkout_update_order_meta',function( $order_id, $posted ){
    update_post_meta( $order_id, '_order_syspro_customer_id', $GLOBALS["SYSPRO_ID"]);
} , 10, 2);


if ( function_exists('register_sidebar') ) {
	register_sidebar(array( 'name' => 'Woocommerce', 'id' => 'wc_sidebar', 'before_widget' => '<div id="%1$s" class="widgetSidebar %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4>','after_title' => '</h4>'));
	register_sidebar(array( 'name' => 'Main Sidebar', 'id' => 'main_sidebar', 'before_widget' => '<div id="%1$s" class="widgetSidebar %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4>','after_title' => '</h4>'));
}


//REDIRECT LOGOUT
function auto_redirect_after_logout(){
wp_redirect( home_url() );
exit();
}
add_action('wp_logout','auto_redirect_after_logout');


add_action( 'init', 'bbloomer_hide_price_add_cart_not_logged_in' );
 
function bbloomer_hide_price_add_cart_not_logged_in() { 
if ( !is_user_logged_in() ) {       
 remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
 remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
 remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
 remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );  
 add_action( 'woocommerce_single_product_summary', 'bbloomer_print_login_to_see', 31 );
 add_action( 'woocommerce_after_shop_loop_item', 'bbloomer_print_login_to_see', 11 );
}
}
 
function bbloomer_print_login_to_see() {
echo '<a href="' . get_permalink(wc_get_page_id('myaccount')) . '">' . __('Login to see prices', 'theme_name') . '</a>';
}

$subRole = get_role( 'customer' );
$subRole->add_cap( 'read_private_posts' );
$subRole->add_cap( 'read_private_pages' );


function ssv_remove_edit_account_links() {
    wc_enqueue_js("jQuery(document).ready(function() { jQuery( 'a.edit' ).remove(); });");
}
add_action( 'woocommerce_after_my_account', 'ssv_remove_edit_account_links' );



add_filter( 'get_terms', 'get_subcategory_terms', 10, 3 );

function get_subcategory_terms( $terms, $taxonomies, $args ) {
  $new_terms = array();
  // if a product category and on the shop page
 // to hide from shop page, replace is_page('YOUR_PAGE_SLUG') with is_shop()
  if ( in_array( 'product_cat', $taxonomies ) && ! is_admin() && is_shop() ) {
    foreach ( $terms as $key => $term ) {
      if ( ! in_array( $term->slug, array( 'tile', 'marketing-material' ) ) ) {
        $new_terms[] = $term;
      }
    }
    $terms = $new_terms;
  }
  return $terms;
}

//REMOVE COUNT ON CATEGORY PAGES
add_filter( 'woocommerce_subcategory_count_html', '__return_empty_string' );


//REMOVE STORE MANAGEMENT EMAILS FOR LOW STOCK, NO STOCK AND BACKORDER
remove_action( 'woocommerce_low_stock_notification', array( $email_class, 'low_stock' ) );
remove_action( 'woocommerce_no_stock_notification', array( $email_class, 'no_stock' ) );
remove_action( 'woocommerce_product_on_backorder_notification', array( $email_class, 'backorder' ) );



add_action( 'woocommerce_view_order', 'view_order_show_tracking', 5 );

function view_order_show_tracking($order_id) { 
	global $wpdb; 

	$order_id=1597;		
	$tracking  = $wpdb->get_var("SELECT comment_content FROM $wpdb->comments WHERE comment_post_ID = $order_id AND comment_content LIKE '%Tracking%'");
		
	if(!is_null($tracking)): 
	
	?>	
	<h2>TRACKING</h2>
	<div class="alert alert-primary" role="alert">
		<?php echo $tracking; ?>
	</div>
	
    <?php endif;	
}

// Remove Auto Cancelation of Orders 
remove_action( 'woocommerce_cancel_unpaid_orders', 'wc_cancel_unpaid_orders', 10); 


// New order notification only for "Pending" Order status
add_action( 'woocommerce_checkout_order_processed', 'pending_new_order_notification', 20, 1 );
function pending_new_order_notification( $order_id ) {

    // Get an instance of the WC_Order object
    $order = wc_get_order( $order_id );

    // Only for "pending" order status
    if( ! $order->has_status( 'pending' ) ) return;

    // Send "New Email" notification (to admin)
    WC()->mailer()->get_emails()['WC_Email_New_Order']->trigger( $order_id );
}