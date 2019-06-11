<?php get_header(); ?>

  	<div class='content'>
	  	<h2><?php the_title(); ?></h2>
	  	 <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>	
	  		<?php the_content(); ?>	
	  	 <?php endwhile; else : ?>
	  	 	<!-- The very first "if" tested to see if there were any Posts to -->
	  	 	<!-- display.  This "else" part tells what do if there weren't any. -->
	  	 	<p><?php esc_html_e( 'Sorry, no posts matched your criteria.' ); ?></p>
	  	 <!-- REALLY stop The Loop. -->
	  	 <?php endif; ?>
  	</div>
<?php get_footer(); 