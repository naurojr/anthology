<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php
	/**
	 * woocommerce_before_single_product hook.
	 *
	 * @hooked wc_print_notices - 10
	 */
	 do_action( 'woocommerce_before_single_product' );

	 if ( post_password_required() ) {
	 	echo get_the_password_form();
	 	return;
	 }
?>

<div itemscope itemtype="<?php echo woocommerce_get_product_schema(); ?>" id="product-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class='row'>
		<div class='col-sm-12 backtocollection'><a href="<?php bloginfo('siteurl'); ?>">Back to the Collection</a></div>
	</div>

	<?php
		/**
		 * woocommerce_before_single_product_summary hook.
		 *
		 * @hooked woocommerce_show_product_sale_flash - 10
		 * @hooked woocommerce_show_product_images - 20
		 */
		do_action( 'woocommerce_before_single_product_summary' );
	?>

	<div class="summary entry-summary">

		<?php
			/**
			 * woocommerce_single_product_summary hook.
			 *
			 * @hooked woocommerce_template_single_title - 5
			 * @hooked woocommerce_template_single_rating - 10
			 * @hooked woocommerce_template_single_price - 10
			 * @hooked woocommerce_template_single_excerpt - 20
			 * @hooked woocommerce_template_single_add_to_cart - 30
			 * @hooked woocommerce_template_single_meta - 40
			 * @hooked woocommerce_template_single_sharing - 50
			 */

		    //remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
		    //add_action( 'woocommerce_single_product_summary', 'woocommerce_output_product_data_tabs', 20);
			do_action( 'woocommerce_single_product_summary' );			
		    
		    $applications = array('wall','floor', 'backsplash',  'shower', 'shower', 'fireplace', 'countertop', 'pool');
		    $application = array();
		    
		    for($i=0;$i<sizeof($applications);$i++) { 
		    
		    	$app = get_post_meta( $post->ID, '_application_'.$applications[$i], true );
		    	if(!empty($app)) { $application[$applications[$i]] = $app; 
		    		if($app == 'true') $has_app++; 
		    	} 
		    	unset($app);
		    }	
			
		    ?>
		    
		    <?php if(sizeof($application) > 0 && $has_app > 0): ?>
		  	<h3>Application</h3>
			<table class="shop_attributes">
			<tbody>
			
			<tr class="">
			<th>Walls</th><td><p><span class="<?php if($application['wall'] == 'true') { echo 'fas fa-check-circle'; } else { echo 'fas fa-ban'; } ?>" aria-hidden="true"></span></p></td>
			<th>Floors</th><td><p><span class="<?php if($application['floor'] == 'true') { echo 'fas fa-check-circle'; } else { echo 'fas fa-ban'; } ?>" aria-hidden="true"></span></p></td>
			</tr>

			<tr class="alt">
			<th>Backsplash</th><td><p><span class="<?php if($application['backsplash'] == 'true') { echo 'fas fa-check-circle'; } else { echo 'fas fa-ban'; } ?>" aria-hidden="true"></span></p></td>
			<th>Shower/Wet Areas</th><td><p><span class="<?php if($application['shower'] == 'true') { echo 'fas fa-check-circle'; } else { echo 'fas fa-ban'; } ?>" aria-hidden="true"></span></p></td>
			</tr>
			
			<tr class="">
			<th>Fireplace Surrounds</th><td><p><span class="<?php if($application['fireplace'] == 'true') { echo 'fas fa-check-circle'; } else { echo 'fas fa-ban'; } ?>" aria-hidden="true"></span></p></td>
			<th>Countertops</th><td><p><span class="<?php if($application['countertop'] == 'true') { echo 'fas fa-check-circle'; } else { echo 'fas fa-ban'; } ?>" aria-hidden="true"></span></p></td>
			</tr>

			<tr class="alt">
			<th>Pool/Fountain</th><td><p><span class="<?php if($application['pool'] == 'true') { echo 'fas fa-check-circle'; } else { echo 'fas fa-ban'; } ?>" aria-hidden="true"></span></p></td>
			</tr>
	
			</tbody>
			</table>		  
			<?php endif; ?>
			
			<div class='row'>
				<div class='col-sm-12 backtocollection'><a href="<?php bloginfo('siteurl'); ?>/products/">Back to the Collection</a></div>
			</div>

	</div><!-- .summary -->

	<?php
		/**
		 * woocommerce_after_single_product_summary hook.
		 *
		 * @hooked woocommerce_output_product_data_tabs - 10
		 * @hooked woocommerce_upsell_display - 15
		 * @hooked woocommerce_output_related_products - 20
		 */
		do_action( 'woocommerce_after_single_product_summary' );
	?>

	<meta itemprop="url" content="<?php the_permalink(); ?>" />

</div><!-- #product-<?php the_ID(); ?> -->

<?php do_action( 'woocommerce_after_single_product' ); ?>
