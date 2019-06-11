<?php
/**
 * Customer new account email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-new-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php /* translators: %s Customer first name */ ?>
<p><?php printf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html( $user_login ) ); ?></p>
<?php /* translators: %1$s: Site title, %2$s: Username, %3$s: My account link */ ?>

<p>Thank you for choosing Anthology Tile for all of your tile needs.</p>

<p><?php printf( __( 'As a reminder, your username is %1$s. You can access your account area to view orders, change your password, and more at:<br> %2$s', 'woocommerce' ), '<strong>' . esc_html( $user_login ) . '</strong>', make_clickable( esc_url( wc_get_page_permalink( 'myaccount' ) ) ) ); ?></p><?php // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>

<?php if ( 'yes' === get_option( 'woocommerce_registration_generate_password' ) && $password_generated ) : ?>
	<?php /* translators: %s Auto generated password */ ?>
	<p><?php printf( esc_html__( 'Your password has been automatically generated: %s', 'woocommerce' ), '<strong>' . esc_html( $user_pass ) . '</strong>' ); ?></p>
<?php endif; ?>


<p>At Anthology Tile we strive to provide responsive, competent, and excellent service. We are committed to the best possible results and making our working relationship a success. Our clients are the most important part of our business and we work tirelessly to ensure your complete satisfaction. </p>

<p>Please use this dealer portal for the most advanced customer service/ordering system in the industry. The dealer portal can be used for the following and more:</p>

<ul>
	<li>Ordering products and marketing tools.</li>
	<li>Checking your credit balance and limit.</li> 
	<li>Reviewing your order history.</li>
	<li>Updating your account information.</li>
</ul>

<p>For your convenience Anthology Tile can also be contacted at 1.888.461.3520 or at <a href="mailto:cs@anthologytile.com">cs@anthologytile.com</a> for additional information.</p>

<p>Thank you again for entrusting Anthology Tile with all of your surface needs. We are very pleased to have you part of the Anthology family.</p>

<?php
do_action( 'woocommerce_email_footer', $email );
