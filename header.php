<?php
/**
 * Header.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>document.documentElement.classList.add('js')</script>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#main"><?php esc_html_e( 'Skip to content', 'aria' ); ?></a>
<header class="site-head" id="top">
	<div class="wrap head-row">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( aria_opt( 'company' ) ); ?> – <?php esc_attr_e( 'home', 'aria' ); ?>">
			<img src="<?php echo esc_url( aria_logo_url() ); ?>" alt="" width="46" height="40" fetchpriority="high">
			<span class="brand-name"><?php echo esc_html( aria_opt( 'company' ) ); ?><small><?php esc_html_e( 'Iranian dates exporter', 'aria' ); ?></small></span>
		</a>
		<nav class="nav" id="site-nav" aria-label="<?php esc_attr_e( 'Main', 'aria' ); ?>">
			<?php aria_primary_nav(); ?>
			<div class="nav-extra">
				<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>"><?php esc_html_e( 'Request a quote', 'aria' ); ?></a>
				<?php aria_contact_list( 'contact-list contact-list--nav' ); ?>
			</div>
		</nav>
		<div class="head-actions">
			<a class="icon-btn icon-btn--wa" href="<?php echo esc_url( aria_wa_url( 'Hello Aria Foods, I would like a wholesale quote.' ) ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'WhatsApp the trade desk', 'aria' ); ?>"><?php echo aria_icon( 'whatsapp' ); // phpcs:ignore ?></a>
			<a class="icon-btn" href="<?php echo esc_url( aria_account_url() ); ?>" aria-label="<?php echo is_user_logged_in() ? esc_attr__( 'My account', 'aria' ) : esc_attr__( 'Sign in or create an account', 'aria' ); ?>"><?php echo aria_icon( 'user' ); // phpcs:ignore ?></a>
			<a class="btn btn--primary head-cta" href="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>"><?php esc_html_e( 'Request a quote', 'aria' ); ?></a>
			<button class="icon-btn menu-btn" type="button" aria-expanded="false" aria-controls="site-nav"><span class="screen-reader-text"><?php esc_html_e( 'Menu', 'aria' ); ?></span><?php echo aria_icon( 'menu', 'icon i-open' ) . aria_icon( 'close', 'icon i-close' ); // phpcs:ignore ?></button>
		</div>
	</div>
</header>
<div class="progress" aria-hidden="true"></div>
<main id="main" class="site-main" tabindex="-1">
