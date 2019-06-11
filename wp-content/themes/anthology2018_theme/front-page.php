<?php get_header(); ?>
  	<div class='container-fuild' id='home-slider'>
    <?php 
	echo do_shortcode('[twabc-carousel] '); 
	?>
  	</div>
  	<div id='visual-menu' class='container-fluid'>
	  		<div class='row'>
		  		<div class='col-sm-3'>
				  	<img src='<?php bloginfo('template_url'); ?>/img/shop-by-color.jpg' style='display:none;'>	  		
			  		<a href='<?php the_permalink(249); ?>' class='link'></a>
			  		<div class='shop-by-label left'>
				  		<h6>shop by<h6>
				  		<h5>color</h5>
			  		</div>
		  		</div>
		  		<div class='col-sm-3'>
				  	<img src='<?php bloginfo('template_url'); ?>/img/shop-by-materials.jpg' style='display:none;'>
			  		<a href='<?php the_permalink(250); ?>' class='link'></a>
			  		<div class='shop-by-label right'>
			  		<h6>shop by<h6>
			  		<h5>materials</h5>
			  		</div>

		  		</div>
		  		<div class='col-sm-3'>
				  	<img src='<?php bloginfo('template_url'); ?>/img/shop-by-collections.jpg' style='display:none;'>
			  		<a href='<?php the_permalink(4); ?>' class='link'></a>				  	
			  		<div class='shop-by-label center'>
				  		<h6>shop<h6>
				  		<h5>collection</h5>
			  		</div>
		  		</div>
		  		<div class='col-sm-3'>
				  	<img src='<?php bloginfo('template_url'); ?>/img/become-an-anthology-dealer.jpg' style='display:none;'>
			  		<a href='<?php the_permalink(223); ?>' class='link'></a>				  				  		
			  		<div class='shop-by-label right'>
				  		<h6>become a<h6>
				  		<h5>dealer</h5>
			  		</div>

		  		</div>
	  		</div>
  	</div>  
  	<div class='container-fluid' id='home-gallery-header'>
	  		<h5>Inspiration</h5>
	  		<h6>Gallery</h6>
  	</div>
  	<div id='home-gallery' class='container-fluid' >
		<a href='<?php the_permalink(226); ?>' class='link'></a>	  	
	  	<div class='shop-by-label view_more right'>
			<h5>view more</h5>
		</div>
	  	<div id='inspiration-gallery-home' class='row'>
		  	<div class='col-sm-3'><?php echo wp_get_attachment_image('1398', 'woocommerce_thumbnail', false, array('class'=>'img-fluid', 'style'=>'display:none;')); ?></div>
		  	<div class='col-sm-3'><?php echo wp_get_attachment_image('1368', 'woocommerce_thumbnail', false, array('class'=>'img-fluid', 'style'=>'display:none;')); ?></div>
		  	<div class='col-sm-3'><?php echo wp_get_attachment_image('241', 'woocommerce_thumbnail', false, array('class'=>'img-fluid', 'style'=>'display:none;')); ?></div>
		  	<div class='col-sm-3'><?php echo wp_get_attachment_image('238', 'woocommerce_thumbnail', false, array('class'=>'img-fluid', 'style'=>'display:none;')); ?></div>		  			  			  			  		  	
	  	</div>
  	</div>
<?php get_footer(); ?>