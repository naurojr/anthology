<?php get_header(); ?>
	<div class='container-fluid medium' id='img-header' style="background-image:url('<?php bloginfo('template_url'); ?>/img/main_scene.jpg')";>&nbsp;</div>
  	<div class='content'>
  	<div class='container'>
	  	<h1><?php the_title(); ?></h1>
	  		<div class='row balance-vertical-space'>
	  		<?php
		  		$args = array( 'parent' => '16', 'hide_empty' => true);
		  		//$terms = get_terms( 'product_cat', $args );
		  		$terms = get_terms( 'pa_color', array('hide_empty'=>false, 'orderby'=>'name', 'order'=>'ASC'));
		  		if($terms) { 
			  		foreach($terms as $term): ?>
				  	<div class='col-sm-3'>
					  	<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="<?php echo $term->slug ?>">
				  		<canvas id="myCanvas" width="360" height="360" class='img-fluid' style='width:100%; height:auto; border:1px solid #999; background-color:<?php echo $term->description; ?>'></canvas>
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