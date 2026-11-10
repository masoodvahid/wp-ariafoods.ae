<?php
/**
 * Aria Foods Modern — plugin-free theme.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

define( 'ARIA_VER', wp_get_theme( get_template() )->get( 'Version' ) );
define( 'ARIA_DIR', get_template_directory() );
define( 'ARIA_URI', get_template_directory_uri() );

require ARIA_DIR . '/inc/helpers.php';
require ARIA_DIR . '/inc/template-tags.php';
require ARIA_DIR . '/inc/cleanup.php';
require ARIA_DIR . '/inc/content-types.php';
require ARIA_DIR . '/inc/customizer.php';
require ARIA_DIR . '/inc/nav.php';
require ARIA_DIR . '/inc/forms.php';
require ARIA_DIR . '/inc/account.php';
require ARIA_DIR . '/inc/seo.php';
require ARIA_DIR . '/inc/security.php';
require ARIA_DIR . '/inc/fonts.php';
require ARIA_DIR . '/inc/media.php';
require ARIA_DIR . '/inc/analytics.php';
require ARIA_DIR . '/inc/export-markets.php';

if ( is_admin() || wp_doing_cron() ) {
	require ARIA_DIR . '/inc/updater.php';
}
if ( is_admin() ) {
	require ARIA_DIR . '/inc/admin.php';
	require ARIA_DIR . '/inc/importer.php';
	require ARIA_DIR . '/inc/migrate.php';
	require ARIA_DIR . '/inc/domain.php';
	require ARIA_DIR . '/inc/seo-fixes.php';
}

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'aria', ARIA_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 120, 'width' => 140, 'flex-width' => true, 'flex-height' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );

	register_nav_menus( array(
		'primary' => __( 'Main menu', 'aria' ),
		'footer'  => __( 'Footer menu', 'aria' ),
		'legal'   => __( 'Footer bottom links', 'aria' ),
	) );

	add_image_size( 'aria-card', 640, 640, true );
	add_image_size( 'aria-wide', 1200, 675, true );
	add_image_size( 'aria-hero', 1600, 985, false );
} );

add_action( 'wp_enqueue_scripts', function () {
	$css = ARIA_DIR . '/assets/main.css';
	wp_enqueue_style( 'aria', ARIA_URI . '/assets/main.css', array(), ARIA_VER . '.' . ( file_exists( $css ) ? filemtime( $css ) : 0 ) );
	wp_enqueue_script( 'aria', ARIA_URI . '/assets/js/main.js', array(), ARIA_VER, array( 'strategy' => 'defer', 'in_footer' => true ) );
} );

/* Wider content width for embeds/images. */
$GLOBALS['content_width'] = 760;
