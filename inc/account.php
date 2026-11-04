<?php
/**
 * Customer accounts without WooCommerce: role "customer", sign-up, sign-in, lost password,
 * profile and the customer's own inquiries — all on /my-account/ (never wp-admin).
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

/* Role (WooCommerce used the same name, so existing customers keep working). */
function aria_ensure_customer_role() {
	if ( ! get_role( 'customer' ) ) {
		add_role( 'customer', __( 'Customer', 'aria' ), array( 'read' => true ) );
	}
}
add_action( 'after_switch_theme', 'aria_ensure_customer_role' );
add_action( 'admin_init', 'aria_ensure_customer_role' );

/* Customers stay on the front end. */
add_action( 'admin_init', function () {
	if ( wp_doing_ajax() || ! aria_is_customer() ) {
		return;
	}
	wp_safe_redirect( aria_account_url() );
	exit;
} );
add_filter( 'show_admin_bar', function ( $show ) {
	return aria_is_customer() ? false : $show;
} );
add_filter( 'login_redirect', function ( $to, $requested, $user ) {
	return ( $user instanceof WP_User && aria_is_customer( $user ) ) ? aria_account_url() : $to;
}, 10, 3 );

/** Business types offered at sign-up. */
function aria_business_types() {
	return array( 'Importer', 'Wholesaler / distributor', 'Food or beverage manufacturer', 'Retail chain', 'Repacker / private label brand', 'HoReCa / catering', 'Other' );
}

/** Messages for the account page (set during POST handling). */
function aria_account_notice( $msg = null, $type = 'error' ) {
	static $notice = null;
	if ( null !== $msg ) {
		$notice = array( $msg, $type );
	}
	return $notice;
}

/* Handle account forms before the page renders. */
add_action( 'template_redirect', function () {
	if ( ! is_page( 'my-account' ) ) {
		return;
	}
	nocache_headers();
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['aria_account'] ) ) { // phpcs:ignore
		if ( isset( $_GET['logged-out'] ) ) { // phpcs:ignore
			aria_account_notice( __( 'You are signed out.', 'aria' ), 'ok' );
		}
		return;
	}
	$action = sanitize_key( wp_unslash( $_POST['aria_account'] ) ); // phpcs:ignore
	if ( ! isset( $_POST['_aria_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_aria_nonce'] ) ), 'aria_account_' . $action ) ) {
		aria_account_notice( __( 'The form expired. Please try again.', 'aria' ) );
		return;
	}
	$ip   = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$rate = 'aria_acc_' . md5( $ip );
	if ( in_array( $action, array( 'login', 'register', 'lost' ), true ) && (int) get_transient( $rate ) >= 10 ) {
		aria_account_notice( __( 'Too many attempts. Please wait 15 minutes or contact us on WhatsApp.', 'aria' ) );
		return;
	}
	$fail = function ( $msg ) use ( $rate ) {
		set_transient( $rate, (int) get_transient( $rate ) + 1, 15 * MINUTE_IN_SECONDS );
		aria_account_notice( $msg );
	};
	$p = wp_unslash( $_POST ); // phpcs:ignore -- sanitised per field below.

	switch ( $action ) {
		case 'login':
			$user = wp_signon( array(
				'user_login'    => sanitize_text_field( $p['log'] ?? '' ),
				'user_password' => (string) ( $p['pwd'] ?? '' ),
				'remember'      => ! empty( $p['rememberme'] ),
			), is_ssl() );
			if ( is_wp_error( $user ) ) {
				$fail( __( 'The email or password is not correct.', 'aria' ) );
				return;
			}
			$to = ( ! aria_is_customer( $user ) && user_can( $user, 'edit_posts' ) ) ? admin_url() : aria_account_url();
			wp_safe_redirect( $to );
			exit;

		case 'register':
			if ( ! empty( $p['website'] ) ) {
				$fail( __( 'Registration failed. Please try again.', 'aria' ) );
				return;
			}
			$email = sanitize_email( $p['email'] ?? '' );
			$name  = sanitize_text_field( $p['full_name'] ?? '' );
			$co    = sanitize_text_field( $p['company'] ?? '' );
			$pass  = (string) ( $p['password'] ?? '' );
			if ( ! $name || ! $co ) {
				aria_account_notice( __( 'Enter your name and company.', 'aria' ) );
				return;
			}
			if ( ! is_email( $email ) ) {
				aria_account_notice( __( 'Enter a valid business email.', 'aria' ) );
				return;
			}
			if ( email_exists( $email ) ) {
				aria_account_notice( __( 'An account with this email already exists. Sign in or reset your password.', 'aria' ) );
				return;
			}
			if ( strlen( $pass ) < 8 ) {
				aria_account_notice( __( 'Use a password of at least 8 characters.', 'aria' ) );
				return;
			}
			$login = sanitize_user( current( explode( '@', $email ) ), true );
			$login = $login ? $login : 'customer';
			$base  = $login;
			$n     = 1;
			while ( username_exists( $login ) ) {
				$login = $base . ++$n;
			}
			$parts   = preg_split( '/\s+/', $name, 2 );
			$user_id = wp_insert_user( array(
				'user_login'   => $login,
				'user_email'   => $email,
				'user_pass'    => $pass,
				'display_name' => $name,
				'first_name'   => $parts[0],
				'last_name'    => $parts[1] ?? '',
				'role'         => 'customer',
			) );
			if ( is_wp_error( $user_id ) ) {
				$fail( $user_id->get_error_message() );
				return;
			}
			aria_save_customer_meta( $user_id, $p );
			set_transient( $rate, (int) get_transient( $rate ) + 1, 15 * MINUTE_IN_SECONDS );
			wp_new_user_notification( $user_id, null, 'admin' );
			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id, true, is_ssl() );
			wp_safe_redirect( add_query_arg( 'welcome', 1, aria_account_url() ) );
			exit;

		case 'lost':
			$login = sanitize_text_field( $p['user_login'] ?? '' );
			if ( $login ) {
				retrieve_password( $login ); // Same response either way: do not reveal which emails exist.
				set_transient( $rate, (int) get_transient( $rate ) + 1, 15 * MINUTE_IN_SECONDS );
			}
			aria_account_notice( __( 'If an account exists for that email, a reset link is on its way.', 'aria' ), 'ok' );
			return;

		case 'profile':
			if ( ! is_user_logged_in() ) {
				return;
			}
			$uid   = get_current_user_id();
			$name  = sanitize_text_field( $p['full_name'] ?? '' );
			$parts = preg_split( '/\s+/', $name, 2 );
			$data  = array( 'ID' => $uid, 'display_name' => $name, 'first_name' => $parts[0], 'last_name' => $parts[1] ?? '' );
			if ( ! empty( $p['new_password'] ) ) {
				if ( strlen( $p['new_password'] ) < 8 ) {
					aria_account_notice( __( 'Use a password of at least 8 characters.', 'aria' ) );
					return;
				}
				$u = wp_get_current_user();
				if ( ! wp_check_password( (string) ( $p['current_password'] ?? '' ), $u->user_pass, $uid ) ) {
					aria_account_notice( __( 'Your current password is not correct.', 'aria' ) );
					return;
				}
				$data['user_pass'] = (string) $p['new_password'];
			}
			$r = wp_update_user( $data );
			if ( is_wp_error( $r ) ) {
				aria_account_notice( $r->get_error_message() );
				return;
			}
			aria_save_customer_meta( $uid, $p );
			if ( isset( $data['user_pass'] ) ) {
				wp_set_auth_cookie( $uid, true, is_ssl() );
			}
			wp_safe_redirect( add_query_arg( array( 'tab' => 'profile', 'saved' => 1 ), aria_account_url() ) );
			exit;
	}
} );

function aria_save_customer_meta( $user_id, $p ) {
	update_user_meta( $user_id, 'billing_company', sanitize_text_field( $p['company'] ?? '' ) );
	update_user_meta( $user_id, 'billing_country', sanitize_text_field( $p['country'] ?? '' ) );
	update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $p['whatsapp'] ?? '' ) );
	$type = sanitize_text_field( $p['business_type'] ?? '' );
	update_user_meta( $user_id, 'aria_business_type', in_array( $type, aria_business_types(), true ) ? $type : '' );
	update_user_meta( $user_id, 'aria_port', sanitize_text_field( $p['port'] ?? '' ) );
}

/** Values to prefill forms for a signed-in customer. */
function aria_customer_prefill() {
	if ( ! is_user_logged_in() ) {
		return array();
	}
	$u = wp_get_current_user();
	return array(
		'name'     => $u->display_name,
		'email'    => $u->user_email,
		'company'  => get_user_meta( $u->ID, 'billing_company', true ),
		'whatsapp' => get_user_meta( $u->ID, 'billing_phone', true ),
		'country'  => get_user_meta( $u->ID, 'billing_country', true ),
		'port'     => get_user_meta( $u->ID, 'aria_port', true ),
		'business_type' => get_user_meta( $u->ID, 'aria_business_type', true ),
	);
}

/* Logout returns to the account page. */
add_filter( 'logout_redirect', function ( $to, $requested, $user ) {
	return ( $user instanceof WP_User && aria_is_customer( $user ) ) ? add_query_arg( 'logged-out', 1, aria_account_url() ) : $to;
}, 10, 3 );

/* Admin: customer details on the user profile screen and in the users list. */
add_action( 'show_user_profile', 'aria_user_admin_fields' );
add_action( 'edit_user_profile', 'aria_user_admin_fields' );
function aria_user_admin_fields( $user ) {
	if ( ! in_array( 'customer', (array) $user->roles, true ) ) {
		return;
	}
	echo '<h2>' . esc_html__( 'Customer details', 'aria' ) . '</h2><table class="form-table">';
	foreach ( array( 'billing_company' => 'Company', 'aria_business_type' => 'Business type', 'billing_country' => 'Country', 'billing_phone' => 'WhatsApp', 'aria_port' => 'Usual destination port' ) as $k => $label ) {
		echo '<tr><th>' . esc_html( $label ) . '</th><td>' . esc_html( get_user_meta( $user->ID, $k, true ) ) . '</td></tr>';
	}
	echo '</table>';
}
add_filter( 'manage_users_columns', function ( $c ) {
	$c['aria_company'] = __( 'Company', 'aria' );
	return $c;
} );
add_filter( 'manage_users_custom_column', function ( $val, $col, $uid ) {
	return 'aria_company' === $col ? esc_html( get_user_meta( $uid, 'billing_company', true ) ) : $val;
}, 10, 3 );

/* Make sure /my-account/ exists (sites imported before accounts were added). */
add_action( 'admin_init', function () {
	if ( get_option( 'aria_account_page' ) || ! current_user_can( 'publish_pages' ) ) {
		return;
	}
	$page = get_page_by_path( 'my-account' );
	if ( $page && 'trash' === $page->post_status ) {
		wp_untrash_post( $page->ID );
		wp_update_post( array( 'ID' => $page->ID, 'post_status' => 'publish' ) );
	} elseif ( ! $page ) {
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'my-account', 'post_title' => 'My account', 'post_content' => '' ) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_aria_noindex', 1 );
		}
	} else {
		wp_update_post( array( 'ID' => $page->ID, 'post_content' => '' ) ); // Drop the old [woocommerce_my_account] shortcode.
	}
	update_option( 'aria_account_page', 1 );
} );
