<?php
/**
 * Visitor statistics without plugins: Cloudflare Web Analytics (cookie-free) and/or Google Analytics 4.
 * Set the token / ID in Customizer → Aria Foods settings → Company & documents.
 * Logged-in editors are not counted.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_footer', function () {
	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		return;
	}
	$cf = preg_replace( '/[^a-f0-9]/i', '', (string) aria_opt( 'cf_beacon' ) );
	if ( $cf ) {
		echo '<script defer src="https://static.cloudflareinsights.com/beacon.min.js" data-cf-beacon=\'{"token":"' . esc_attr( $cf ) . '"}\'></script>' . "\n";
	}
	$ga = preg_replace( '/[^A-Z0-9\-]/', '', strtoupper( (string) aria_opt( 'ga4_id' ) ) );
	if ( $ga ) {
		// Loaded after the page is interactive so it never slows down the first view.
		echo "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','" . esc_js( $ga ) . "');addEventListener('load',function(){setTimeout(function(){var s=document.createElement('script');s.async=1;s.src='https://www.googletagmanager.com/gtag/js?id=" . esc_js( $ga ) . "';document.head.appendChild(s)},1500)});</script>\n";
	}
}, 99 );
