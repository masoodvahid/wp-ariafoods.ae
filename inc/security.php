<?php
/**
 * Hardening without plugins:
 * - Login screen lives at /gate (wp-login.php and wp-admin answer 404 to visitors who are not signed in).
 * - Login attempts are limited per IP.
 * - Version numbers, generator tags and REST API are hidden from the public.
 * - readme.html, license.txt, xmlrpc.php… answer 404 (rules added to .htaccess).
 *
 * Emergency: if you are ever locked out, rename the theme folder over FTP/File Manager;
 * WordPress falls back to the normal wp-login.php.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

/** Login address slug. Change it by adding define( 'ARIA_LOGIN_SLUG', 'something' ); to wp-config.php. */
function aria_login_slug() {
	return defined( 'ARIA_LOGIN_SLUG' ) ? trim( ARIA_LOGIN_SLUG, '/' ) : 'gate';
}

function aria_login_url_base() {
	return home_url( '/' . aria_login_slug() );
}

function aria_request_path() {
	$path = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ); // phpcs:ignore
	$home = (string) wp_parse_url( home_url(), PHP_URL_PATH );
	if ( $home && 0 === strpos( $path, $home ) ) {
		$path = substr( $path, strlen( $home ) );
	}
	return trim( $path, '/' );
}

/** Send a normal-looking 404 page and stop. */
function aria_send_404() {
	global $wp_query;
	status_header( 404 );
	nocache_headers();
	if ( $wp_query ) {
		$wp_query->set_404();
	}
	$tpl = get_404_template();
	if ( $tpl ) {
		include $tpl;
	}
	exit;
}

/* Rewrite every core link to wp-login.php into /gate. */
function aria_swap_login_url( $url ) {
	if ( is_string( $url ) && false !== strpos( $url, 'wp-login.php' ) ) {
		$parts = explode( '?', $url, 2 );
		$url   = aria_login_url_base() . ( isset( $parts[1] ) ? '?' . $parts[1] : '' );
	}
	return $url;
}
foreach ( array( 'site_url', 'network_site_url', 'wp_redirect', 'login_url', 'logout_url', 'lostpassword_url', 'register_url' ) as $aria_hook ) {
	add_filter( $aria_hook, 'aria_swap_login_url', 99 );
}
unset( $aria_hook );

add_action( 'wp_loaded', function () {
	global $pagenow;
	$path = aria_request_path();

	// 1. /gate → the real login screen.
	if ( aria_login_slug() === $path ) {
		global $error, $interim_login, $action, $user_login, $user, $redirect_to;
		$pagenow = 'wp-login.php'; // phpcs:ignore
		require_once ABSPATH . 'wp-login.php';
		exit;
	}

	// 2. Direct wp-login.php → 404.
	if ( 'wp-login.php' === $pagenow ) {
		aria_send_404();
	}

	// 3. wp-admin for visitors who are not signed in → 404 (Ajax, admin-post and cron keep working).
	if ( is_admin() && ! is_user_logged_in() && ! wp_doing_ajax() && ! wp_doing_cron() && 'admin-post.php' !== $pagenow ) {
		aria_send_404();
	}

	// 4. Old guesses that reveal WordPress.
	if ( in_array( $path, array( 'login', 'admin', 'wp-login', 'dashboard' ), true ) && ! is_user_logged_in() ) {
		aria_send_404();
	}
}, 1 );

/* Core's /login, /admin shortcuts would otherwise redirect to the secret address. */
remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );

/* ---------------------------------------------------------------- Login limits */

function aria_client_ip() {
	$ip = isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : ( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '' ); // phpcs:ignore
	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
}

function aria_login_key() {
	return 'aria_lf_' . md5( aria_client_ip() );
}

add_filter( 'authenticate', function ( $user ) {
	if ( (int) get_transient( aria_login_key() ) >= 6 ) {
		return new WP_Error( 'aria_locked', __( 'Too many failed attempts. Please wait 20 minutes and try again.', 'aria' ) );
	}
	return $user;
}, 30 );

add_action( 'wp_login_failed', function () {
	$k = aria_login_key();
	set_transient( $k, (int) get_transient( $k ) + 1, 20 * MINUTE_IN_SECONDS );
} );

add_action( 'wp_login', function () {
	delete_transient( aria_login_key() );
} );

/* Same message for wrong user or wrong password: do not confirm which usernames exist. */
add_filter( 'login_errors', function ( $msg ) {
	return false !== strpos( $msg, 'Too many' ) ? $msg : __( 'The login details are not correct.', 'aria' );
} );

/* Login screen: our logo, link to the site, not to wordpress.org. */
add_filter( 'login_headerurl', function () {
	return home_url( '/' );
} );
add_filter( 'login_headertext', function () {
	return aria_opt( 'company' );
} );
add_action( 'login_enqueue_scripts', function () {
	echo '<style>#login h1 a{background-image:url(' . esc_url( aria_logo_url() ) . ');background-size:contain;width:120px;height:104px}.language-switcher{display:none}</style>';
} );
add_filter( 'login_display_language_dropdown', '__return_false' );

/* ---------------------------------------------------------------- Hide the stack */

/* No WordPress version anywhere. */
add_filter( 'the_generator', '__return_empty_string' );
function aria_strip_core_ver( $src ) {
	if ( is_string( $src ) && false !== strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
add_filter( 'style_loader_src', 'aria_strip_core_ver', 99 );
add_filter( 'script_loader_src', 'aria_strip_core_ver', 99 );

add_action( 'send_headers', function () {
	header_remove( 'X-Powered-By' );
	header_remove( 'X-Pingback' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'X-Frame-Options: SAMEORIGIN' );
} );

/* REST API only for signed-in users (the editor needs it; the public site does not). */
add_filter( 'rest_authentication_errors', function ( $result ) {
	if ( ! empty( $result ) || is_user_logged_in() ) {
		return $result;
	}
	return new WP_Error( 'rest_not_found', 'Not found', array( 'status' => 404 ) );
} );

/* ?author=1 user enumeration. */
add_action( 'parse_request', function ( $wp ) {
	if ( ! is_admin() && isset( $_GET['author'] ) ) { // phpcs:ignore
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
} );

/* Files that reveal WordPress answer 404 (Apache / LiteSpeed). Written once per rules version. */
add_action( 'admin_init', function () {
	$ver = 1;
	if ( (int) get_option( 'aria_htaccess' ) === $ver || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	$file = ABSPATH . '.htaccess';
	if ( ( file_exists( $file ) && ! wp_is_writable( $file ) ) || ( ! file_exists( $file ) && ! wp_is_writable( ABSPATH ) ) ) {
		return;
	}
	$ok = insert_with_markers( $file, 'Aria hardening', array(
		'<IfModule mod_rewrite.c>',
		'RewriteEngine On',
		'RewriteRule ^(readme\.html|license\.txt|wp-config-sample\.php|xmlrpc\.php|wp-trackback\.php)$ - [R=404,L]',
		'RewriteRule ^wp-content/(debug\.log|upgrade/) - [R=404,L]',
		'</IfModule>',
	) );
	if ( $ok ) {
		update_option( 'aria_htaccess', $ver, false );
	}
} );

/* robots.txt without the WordPress-only lines (wp-admin answers 404 to visitors anyway). */
add_filter( 'robots_txt', function ( $txt ) {
	return preg_replace( '#^(Disallow: /wp-admin/|Allow: /wp-admin/admin-ajax\.php)\R#m', '', $txt );
}, 99 );
