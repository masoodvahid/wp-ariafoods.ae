<?php
/**
 * Front-end weight loss: remove what core adds that this theme does not use.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );

	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	remove_action( 'wp_head', 'rel_canonical' ); // Theme prints its own canonical.
	remove_action( 'wp_head', 'noindex', 1 );
} );

add_action( 'wp_enqueue_scripts', function () {
	// Theme CSS covers content blocks; drop core block/global CSS (~100 KB).
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'core-block-supports' );
	if ( ! is_admin_bar_showing() ) {
		wp_deregister_script( 'wp-embed' );
	}
}, 100 );

remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
add_filter( 'should_load_separate_core_block_assets', '__return_true' );
add_filter( 'wp_img_tag_add_auto_sizes', '__return_true' );

/* No comments on a B2B catalogue. */
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );
add_action( 'admin_menu', function () {
	remove_menu_page( 'edit-comments.php' );
} );

/* XML-RPC is a common brute-force target and unused here. */
add_filter( 'xmlrpc_enabled', '__return_false' );

/* Author archives expose usernames and add thin pages. */
add_action( 'template_redirect', function () {
	if ( is_author() || is_attachment() || is_date() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
} );

/* Hide user list from the public REST API. */
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( ! is_user_logged_in() ) {
		unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
} );

/* Preload the one font file and preconnect nothing else (everything is local). */
add_action( 'wp_head', function () {
	foreach ( array( 'manrope' => 'head', 'inter' => 'body' ) as $font => $role ) {
		if ( function_exists( 'aria_font_choice' ) && aria_font_choice( $role ) ) {
			continue; // Replaced by a font chosen in the Customizer.
		}
		if ( file_exists( ARIA_DIR . '/assets/' . $font . '.woff2' ) ) {
			echo '<link rel="preload" href="' . esc_url( ARIA_URI . '/assets/' . $font . '.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
		}
	}
}, 1 );

/* Strip core's speculative-loading rules: the theme's own instant navigation prefetches. */
add_filter( 'wp_speculation_rules_configuration', '__return_null' );

/* Plain excerpt length. */
add_filter( 'excerpt_length', function () {
	return 26;
} );
add_filter( 'excerpt_more', function () {
	return '…';
} );
