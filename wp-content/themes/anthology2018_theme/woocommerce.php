<?php get_header(); ?>

  <?php 
	  	if(is_single()) { 
		  	$product_header = get_post_meta($post->ID, '_image_id', true);	
		  	$image_header = get_the_guid($product_header);
	  	} 

		if ( is_product_category() ) { 
			global $wp_query;
			$cat = $wp_query->get_queried_object();
			$thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true ); 
			$image_header = wp_get_attachment_url( $thumbnail_id ); 
		} 
	  	
	    if(!empty($image_header)): 
	  ?>
		<div class='container-fluid large' id='img-header' style="background-image:url('<?php echo $image_header; ?>'); ?>')";>&nbsp;</div>
	  <?php else: ?>
	  	<div class='container-fluid large' id='img-header' style="background-image:url('<?php bloginfo('template_url'); ?>/img/main_scene.jpg')";>&nbsp;</div>
	  <?php endif; ?>
  	<div class='content'>
	  	<div class='container'>
	  	<?php woocommerce_content(); ?> 
	  	</div>
	  	<?php get_sidebar(); ?>
  	</div>
<?php get_footer(); ?>