<?php get_header(); ?>
	<div class='container-fluid medium' id='img-header' style="background-image:url('<?php bloginfo('template_url'); ?>/img/main_scene.jpg')";>&nbsp;</div>
  	<div class='content'>
  	<div class='container'>
	  	<h2>DEALER PAGE</h2>
	  	 <div class='row'>
		  	 <div class='col-sm-6'>
	  	 <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>	
	  		<?php the_content(); ?>	
	  	 <?php endwhile; else : ?>
	  	 	<!-- The very first "if" tested to see if there were any Posts to -->
	  	 	<!-- display.  This "else" part tells what do if there weren't any. -->
	  	 	<p><?php esc_html_e( 'Sorry, no posts matched your criteria.' ); ?></p>
	  	 <!-- REALLY stop The Loop. -->
	  	 <?php endif; ?>
		  	 </div>
		  	 <div class='col-sm-6'>
			  	 <?php echo do_shortcode('[wpforms id="255"]'); ?>
		  	 </div>
	  	 </div>
	  	</div>
  	</div>
<?php get_footer(); ?>