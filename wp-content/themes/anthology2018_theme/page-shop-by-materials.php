<?php get_header(); ?>
	<div class='container-fluid medium' id='img-header' style="background-image:url('<?php bloginfo('template_url'); ?>/img/main_scene.jpg')";>&nbsp;</div>
  	<div class='content'>
  	<div class='container'>
	  	<h1><?php the_title(); ?></h1>
	  		<div class='row balance-vertical-space' id='materials'>
	  		<?php
		  		$args = array( 'parent' => '16', 'hide_empty' => true);
		  		$terms = get_terms( 'pa_material', array('hide_empty'=>false, 'orderby'=>'name', 'order'=>'ASC'));
		  		if($terms) { 
			  		foreach($terms as $term): ?>
				  	<div class='col-sm-3'>
					  	<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="<?php echo $term->slug ?>">
					  	<?php woocommerce_subcategory_thumbnail( $term , array('class'=>'img-fluid')); ?>
					  	<?php echo $term->name; ?>
					  	</a>
				  	</div>
				  	<?	
			  		endforeach;
		  		}
			?>
	  		</div>
	  	</div>
  	</div>
<?php get_footer(); ?>