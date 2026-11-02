<?php
/**
 * Theme updates straight from GitHub, no plugin needed.
 *
 * Source: the "main" branch of ARIA_GITHUB_REPO. When the Version line in style.css on GitHub is
 * higher than the installed one, WordPress shows the normal "Update available" notice and updates
 * the theme from the branch zip.
 *
 * No waiting for caches:
 * - "Check GitHub now" (Appearance → Themes notice, admin bar, Dashboard → Updates) asks GitHub
 *   immediately and refreshes WordPress's own update list.
 * - Opening Dashboard → Updates or Appearance → Themes also re-checks (at most once a minute).
 * - Otherwise the result is kept 15 minutes so wp-admin stays fast.
 *
 * Private repository? Add to wp-config.php:  define( 'ARIA_GITHUB_TOKEN', 'github_pat_…' );
 * (fine-grained token, read-only "Contents" access to this repository only).
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'ARIA_GITHUB_REPO' ) ) {
	define( 'ARIA_GITHUB_REPO', 'masoodvahid/wp-ariafoods.ae' );
}
if ( ! defined( 'ARIA_GITHUB_BRANCH' ) ) {
	define( 'ARIA_GITHUB_BRANCH', 'main' );
}

function aria_gh_headers( $accept = 'application/vnd.github+json' ) {
	$h = array(
		'Accept'        => $accept,
		'User-Agent'    => 'AriaModern-Theme-Updater',
		'Cache-Control' => 'no-cache',
	);
	if ( defined( 'ARIA_GITHUB_TOKEN' ) && ARIA_GITHUB_TOKEN ) {
		$h['Authorization'] = 'Bearer ' . ARIA_GITHUB_TOKEN;
	}
	return $h;
}

/** Version on GitHub, read from style.css of the branch. Cached 15 minutes. */
function aria_github_release( $force = false ) {
	$key = 'aria_gh_info';
	if ( ! $force ) {
		$cached = get_site_transient( $key );
		if ( false !== $cached ) {
			return $cached;
		}
	}
	$api  = 'https://api.github.com/repos/' . ARIA_GITHUB_REPO;
	$res  = wp_remote_get( $api . '/contents/style.css?ref=' . rawurlencode( ARIA_GITHUB_BRANCH ) . '&t=' . time(), array(
		'timeout' => 10,
		'headers' => aria_gh_headers( 'application/vnd.github.raw+json' ),
	) );
	$info = array();
	if ( ! is_wp_error( $res ) && 200 === wp_remote_retrieve_response_code( $res ) && preg_match( '/^\s*Version:\s*([0-9][0-9.]*)/mi', wp_remote_retrieve_body( $res ), $m ) ) {
		$info = array(
			'version' => $m[1],
			'package' => $api . '/zipball/' . rawurlencode( ARIA_GITHUB_BRANCH ),
			'url'     => 'https://github.com/' . ARIA_GITHUB_REPO . '/commits/' . ARIA_GITHUB_BRANCH,
			'checked' => time(),
		);
	} else {
		$info = array( 'error' => is_wp_error( $res ) ? $res->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code( $res ), 'checked' => time() );
	}
	set_site_transient( $key, $info, 15 * MINUTE_IN_SECONDS );
	return $info;
}

/* Offer the update to WordPress. */
add_filter( 'pre_set_site_transient_update_themes', function ( $transient ) {
	if ( ! is_object( $transient ) ) {
		return $transient;
	}
	$slug  = get_template();
	$theme = wp_get_theme( $slug );
	$rel   = aria_github_release( isset( $_GET['force-check'] ) ); // phpcs:ignore -- core's "Check again".
	$item  = array(
		'theme'       => $slug,
		'new_version' => $theme->get( 'Version' ),
		'url'         => 'https://github.com/' . ARIA_GITHUB_REPO,
		'package'     => '',
	);
	if ( ! empty( $rel['version'] ) && version_compare( $rel['version'], $theme->get( 'Version' ), '>' ) ) {
		$item['new_version']           = $rel['version'];
		$item['url']                   = $rel['url'];
		$item['package']               = $rel['package'];
		$transient->response[ $slug ]  = $item;
		unset( $transient->no_update[ $slug ] );
	} else {
		unset( $transient->response[ $slug ] );
		$transient->no_update[ $slug ] = $item;
	}
	return $transient;
} );

/** Ask GitHub now and rebuild WordPress's theme update list. */
function aria_github_check_now() {
	delete_site_transient( 'aria_gh_info' );
	aria_github_release( true );
	delete_site_transient( 'update_themes' );
	wp_update_themes();
}

/* Opening the Updates or Themes screen re-checks (at most once a minute). */
add_action( 'load-update-core.php', 'aria_github_soft_check' );
add_action( 'load-themes.php', 'aria_github_soft_check' );
function aria_github_soft_check() {
	if ( ! current_user_can( 'update_themes' ) ) {
		return;
	}
	$info = get_site_transient( 'aria_gh_info' );
	if ( ! $info || empty( $info['checked'] ) || time() - (int) $info['checked'] > MINUTE_IN_SECONDS ) {
		aria_github_check_now();
	}
}

/* "Check GitHub now" button. */
add_action( 'admin_post_aria_gh_check', function () {
	if ( ! current_user_can( 'update_themes' ) ) {
		wp_die( 'Not allowed', 403 );
	}
	check_admin_referer( 'aria_gh_check' );
	aria_github_check_now();
	wp_safe_redirect( admin_url( 'themes.php?aria_gh=1' ) );
	exit;
} );

function aria_github_check_url() {
	return wp_nonce_url( admin_url( 'admin-post.php?action=aria_gh_check' ), 'aria_gh_check' );
}

add_action( 'admin_bar_menu', function ( $bar ) {
	if ( is_admin() && current_user_can( 'update_themes' ) ) {
		$bar->add_node( array(
			'id'    => 'aria-gh-check',
			'title' => __( 'Theme: check GitHub', 'aria' ),
			'href'  => aria_github_check_url(),
		) );
	}
}, 90 );

add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->id, array( 'themes', 'update-core' ), true ) || ! current_user_can( 'update_themes' ) ) {
		return;
	}
	$info  = get_site_transient( 'aria_gh_info' );
	$local = wp_get_theme( get_template() )->get( 'Version' );
	echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'Theme updates from GitHub', 'aria' ) . '</strong> (' . esc_html( ARIA_GITHUB_REPO ) . ') — ';
	echo esc_html( sprintf( __( 'installed: %s', 'aria' ), $local ) );
	if ( ! empty( $info['version'] ) ) {
		echo ' · ' . esc_html( sprintf( __( 'on GitHub: %s', 'aria' ), $info['version'] ) );
		if ( version_compare( $info['version'], $local, '>' ) ) {
			echo ' · <a href="' . esc_url( admin_url( 'update-core.php' ) ) . '"><strong>' . esc_html__( 'Update now', 'aria' ) . '</strong></a>';
		}
	} elseif ( ! empty( $info['error'] ) ) {
		echo ' · ' . esc_html( sprintf( __( 'GitHub could not be reached (%s)', 'aria' ), $info['error'] ) );
	}
	echo ' · <a class="button button-small" href="' . esc_url( aria_github_check_url() ) . '">' . esc_html__( 'Check GitHub now', 'aria' ) . '</a></p></div>';
} );

/* After the update: forget the old answer so the notice clears at once. */
add_action( 'upgrader_process_complete', function ( $upgrader, $extra ) {
	if ( ! empty( $extra['type'] ) && 'theme' === $extra['type'] ) {
		delete_site_transient( 'aria_gh_info' );
		if ( function_exists( 'opcache_reset' ) ) {
			@opcache_reset(); // phpcs:ignore -- new PHP files take effect immediately.
		}
	}
}, 10, 2 );

/* Send the token with downloads when the repository is private. */
add_filter( 'http_request_args', function ( $args, $url ) {
	if ( defined( 'ARIA_GITHUB_TOKEN' ) && ARIA_GITHUB_TOKEN && preg_match( '#^https://(api\.github\.com/repos/' . preg_quote( ARIA_GITHUB_REPO, '#' ) . '|codeload\.github\.com/)#i', $url ) ) {
		$args['headers']['Authorization'] = 'Bearer ' . ARIA_GITHUB_TOKEN;
	}
	return $args;
}, 10, 2 );

/*
 * GitHub zips unpack to "owner-repo-<sha>/". Rename that folder to the installed theme's folder
 * so the update replaces the theme instead of installing a second copy.
 */
add_filter( 'upgrader_source_selection', function ( $source, $remote_source, $upgrader, $hook_extra = array() ) {
	global $wp_filesystem;
	$slug = get_template();
	if ( empty( $hook_extra['theme'] ) || $hook_extra['theme'] !== $slug || basename( untrailingslashit( $source ) ) === $slug ) {
		return $source;
	}
	$target = trailingslashit( $remote_source ) . $slug . '/';
	if ( $wp_filesystem->move( trailingslashit( $source ), $target, true ) ) {
		return $target;
	}
	return new WP_Error( 'aria_rename', __( 'Could not prepare the theme update folder.', 'aria' ) );
}, 10, 4 );
