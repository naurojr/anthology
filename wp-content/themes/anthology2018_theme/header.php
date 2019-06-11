<!doctype html>
<html lang="en">
  <head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-133581655-1"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'UA-133581655-1');
    </script>
    
    <!-- Required meta tags -->
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<link href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700" rel="stylesheet">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">


    <title><?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
  </head>
  <body <?php body_class(get_option('woo_site_layout')); ?>>
	<header>
	<div id='sidebar'>
	<div class='' id='menu'>
		<a href='<?php bloginfo('url'); ?>'><img src='<?php bloginfo('template_url'); ?>/img/anthology_logo.svg' class='img-fluid'></a>
		<!-- BOOTSTRAP NAVBAR -->
		<nav class="navbar navbar-light">
			<a class="navbar-brand" href="#">MENU</a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
			<?php
			wp_nav_menu([
			'menu'            => 'top',
			'theme_location'  => 'top',
			'container'       => 'div',
			'container_id'    => 'bs4navbar',
			'container_class' => 'collapse navbar-collapse',
			'menu_id'         => false,
			'menu_class'      => 'navbar-nav mr-auto',
			'depth'           => 2,
			'fallback_cb'     => 'bs4navwalker::fallback',
			'walker'          => new bs4navwalker()
			]);
			?>
			<form class="form-inline search woocommerce-product-search" role="search" method="get" action="<?php bloginfo('url'); ?>">
			<input class="form-control" type="search" placeholder="SEARCH" aria-label="Search" name='s'>
			<input type="hidden" name="post_type" value="product">
				<button class="btn bt-sm" type="submit">SEARCH</button>
			</form>

			</div>
		</nav>
		<!-- END OF BOOTSTRAP NAVBAR -->
		<!-- DESKTOP MENU -->
		<nav class='navbox'>
		<ul>
			<?php if (has_nav_menu('top')) wp_nav_menu(array('container' => '', 'items_wrap' => '%3$s', 'depth' => 2, 'theme_location'  => 'top')); ?>
		</ul>
		</nav>
		<div class='search'>
			<form role="search" method="get" class="woocommerce-product-search" action="<?php bloginfo('url'); ?>">
			<input type="text" placeholder="SEARCH" name='s'>
			<input type="hidden" name="post_type" value="product">
			</form>
		</div>
		<?php if(is_user_logged_in() && is_product_category()): ?>
		<div class='row' id='filters'>
			<div class='col-sm-11'><h4>Filter by:</h4></div>
			<?php if (!dynamic_sidebar()) { dynamic_sidebar('woo_sidebar'); } ?>
		</div>
		<?php endif; ?>
	</div>
	</div>

	<div id='dealer-login'>
		<div id='dealer-login-header'><a href='#' class='dealer-header'>Dealer Login</a></div>
		<div id='dealer-login-panel' <?php if(is_user_logged_in()) echo "class='logged-only';"; ?>>

			<?php
				if(!is_user_logged_in()):
				$args = array(
				'echo'           => true,
				'remember'       => false,
				'redirect'       => get_the_permalink(234),
				'form_id'        => 'loginform',
				'id_username'    => 'user_login',
				'id_password'    => 'user_pass',
				'id_remember'    => 'rememberme',
				'id_submit'      => 'wp-submit',
				'label_username' => __( 'Username' ),
				'label_password' => __( 'Password' ),
				'label_remember' => __( 'Remember Me' ),
				'label_log_in'   => __( 'Log In' ),
				'value_username' => '',
				'value_remember' => false
				);
				wp_login_form( $args );

				?>
				<a href='https://anthologytile.com/my-account/lost-password/' style='color:#fff; margin-bottom:5px;'><small>Change password</small></a>
				<?php
				else:
				global $current_user; wp_get_current_user();
			?>
			<div class='welcome-message'>
			<p>Welcome Back <?php echo $current_user->display_name; ?></p>
			<a class="btn btn-sm btn-light" href="<?php echo wp_logout_url() ?>" role="button">Log Out</a>
			</div>
			<?php endif; ?>
		</div>

	</div>
	</header>
