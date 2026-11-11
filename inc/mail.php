<?php
/**
 * Reliable e-mail without a plugin.
 *
 * - The envelope sender (Return-Path) is set to the From address, so SPF and DKIM line up with
 *   the domain. ariafoods.ae publishes a strict DMARC policy (p=reject, aspf=s, adkim=s); mail sent
 *   with the server's own envelope sender is rejected by Gmail, Outlook and most mail servers.
 * - Optional SMTP: send through a real mailbox (e.g. noreply@ariafoods.ae on mail.ariafoods.ae).
 *   Settings → Forms & email. The password can live in wp-config.php instead of the database:
 *     define( 'ARIA_SMTP_PASS', '…' );
 * - Failures are recorded and shown on the settings page; "Send test email" checks the setup.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

function aria_mail_settings() {
	$s = wp_parse_args( (array) get_option( 'aria_mail', array() ), array(
		'smtp'   => 0,
		'host'   => 'mail.' . aria_mail_domain(),
		'port'   => 587,
		'secure' => 'tls',
		'user'   => '',
		'pass'   => '',
		'from'   => '',
	) );
	if ( defined( 'ARIA_SMTP_PASS' ) && ARIA_SMTP_PASS ) {
		$s['pass'] = ARIA_SMTP_PASS;
	}
	return $s;
}

function aria_mail_domain() {
	return preg_replace( '/^www\./', '', (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
}

/** The address mail is sent from. With SMTP it must be the mailbox itself. */
function aria_mail_from_address() {
	$s = aria_mail_settings();
	if ( $s['smtp'] && is_email( $s['user'] ) ) {
		return $s['user'];
	}
	if ( is_email( $s['from'] ) ) {
		return $s['from'];
	}
	return 'noreply@' . aria_mail_domain();
}

add_filter( 'wp_mail_from', function ( $from ) {
	return ( 0 === strpos( $from, 'wordpress@' ) || 0 === strpos( $from, 'noreply@' ) ) ? aria_mail_from_address() : $from;
}, 20 );

add_action( 'phpmailer_init', function ( $mailer ) {
	$s = aria_mail_settings();
	if ( $s['smtp'] && $s['host'] && $s['user'] && $s['pass'] ) {
		$mailer->isSMTP();
		$mailer->Host       = $s['host'];
		$mailer->Port       = (int) $s['port'];
		$mailer->SMTPAuth   = true;
		$mailer->Username   = $s['user'];
		$mailer->Password   = $s['pass'];
		$mailer->SMTPSecure = in_array( $s['secure'], array( 'tls', 'ssl' ), true ) ? $s['secure'] : '';
		$mailer->SMTPAutoTLS = 'tls' === $s['secure'];
		$mailer->Timeout    = 15;
	}
	// Envelope sender = From address, so SPF/DKIM align with DMARC.
	if ( is_email( $mailer->From ) ) {
		$mailer->Sender = $mailer->From;
	}
} );

add_action( 'wp_mail_failed', function ( $error ) {
	update_option( 'aria_mail_error', array(
		'time'    => time(),
		'message' => is_wp_error( $error ) ? $error->get_error_message() : (string) $error,
	), false );
} );

/* ---------- Settings → Forms & email ---------- */

if ( ! is_admin() ) {
	return;
}

add_action( 'admin_menu', function () {
	add_options_page( __( 'Forms & email', 'aria' ), __( 'Forms & email', 'aria' ), 'manage_options', 'aria-mail', 'aria_mail_page' );
} );

add_action( 'admin_post_aria_mail_save', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed', 403 );
	}
	check_admin_referer( 'aria_mail_save' );
	$old = (array) get_option( 'aria_mail', array() );
	$in  = isset( $_POST['aria_mail'] ) ? (array) wp_unslash( $_POST['aria_mail'] ) : array(); // phpcs:ignore
	$new = array(
		'smtp'   => empty( $in['smtp'] ) ? 0 : 1,
		'host'   => sanitize_text_field( isset( $in['host'] ) ? $in['host'] : '' ),
		'port'   => absint( isset( $in['port'] ) ? $in['port'] : 587 ),
		'secure' => in_array( isset( $in['secure'] ) ? $in['secure'] : '', array( 'tls', 'ssl', '' ), true ) ? $in['secure'] : 'tls',
		'user'   => sanitize_text_field( isset( $in['user'] ) ? $in['user'] : '' ),
		'from'   => sanitize_email( isset( $in['from'] ) ? $in['from'] : '' ),
		// Keep the stored password unless a new one is typed.
		'pass'   => ( isset( $in['pass'] ) && '' !== $in['pass'] ) ? (string) $in['pass'] : ( isset( $old['pass'] ) ? $old['pass'] : '' ),
		'ts_site'   => sanitize_text_field( isset( $in['ts_site'] ) ? $in['ts_site'] : '' ),
		'ts_secret' => ( isset( $in['ts_secret'] ) && '' !== $in['ts_secret'] ) ? sanitize_text_field( $in['ts_secret'] ) : ( isset( $old['ts_secret'] ) ? $old['ts_secret'] : '' ),
	);
	if ( ! empty( $in['ts_clear'] ) ) {
		$new['ts_site']   = '';
		$new['ts_secret'] = '';
	}
	update_option( 'aria_mail', $new, false );
	wp_safe_redirect( admin_url( 'options-general.php?page=aria-mail&saved=1' ) );
	exit;
} );

add_action( 'admin_post_aria_mail_test', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed', 403 );
	}
	check_admin_referer( 'aria_mail_test' );
	$to = aria_opt( 'leads_email' ) ? aria_opt( 'leads_email' ) : get_option( 'admin_email' );
	delete_option( 'aria_mail_error' );
	$ok = wp_mail( $to, '[Aria Foods] Test email', "This is a test from Settings → Forms & email.\n\nIf you can read this, inquiry notifications will reach this inbox.\n\nSent: " . wp_date( 'Y-m-d H:i:s' ) );
	wp_safe_redirect( admin_url( 'options-general.php?page=aria-mail&test=' . ( $ok ? 'ok' : 'fail' ) . '&to=' . rawurlencode( $to ) ) );
	exit;
} );

function aria_mail_page() {
	$s     = aria_mail_settings();
	$err   = get_option( 'aria_mail_error' );
	$to    = aria_opt( 'leads_email' ) ? aria_opt( 'leads_email' ) : get_option( 'admin_email' );
	$smtp  = $s['smtp'] && $s['host'] && $s['user'] && $s['pass'];
	$const = defined( 'ARIA_SMTP_PASS' ) && ARIA_SMTP_PASS;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Forms & email', 'aria' ); ?></h1>
		<?php if ( isset( $_GET['saved'] ) ) : // phpcs:ignore ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'Saved. Send a test email to check it.', 'aria' ); ?></p></div>
		<?php endif; ?>
		<?php if ( isset( $_GET['test'] ) ) : // phpcs:ignore ?>
			<?php if ( 'ok' === $_GET['test'] && ! $err ) : // phpcs:ignore ?>
				<div class="notice notice-success"><p><?php echo esc_html( sprintf( __( 'The server accepted the test email for %s. Check that inbox (and the spam folder).', 'aria' ), sanitize_email( wp_unslash( $_GET['to'] ?? '' ) ) ) ); // phpcs:ignore ?></p></div>
			<?php else : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'The test email could not be sent.', 'aria' ); ?> <?php echo $err ? esc_html( $err['message'] ) : ''; ?></p></div>
			<?php endif; ?>
		<?php endif; ?>

		<table class="widefat striped" style="max-width:820px;margin:16px 0">
			<tbody>
				<tr><th style="width:220px"><?php esc_html_e( 'Inquiries are sent to', 'aria' ); ?></th><td><code><?php echo esc_html( $to ); ?></code> <span class="description"><?php esc_html_e( '(Customizer → Aria Foods settings → “Send inquiries to”)', 'aria' ); ?></span></td></tr>
				<tr><th><?php esc_html_e( 'Sent from', 'aria' ); ?></th><td><code><?php echo esc_html( aria_mail_from_address() ); ?></code> <span class="description"><?php esc_html_e( 'envelope sender set to the same address', 'aria' ); ?></span></td></tr>
				<tr><th><?php esc_html_e( 'Method', 'aria' ); ?></th><td><?php echo $smtp ? esc_html( sprintf( 'SMTP – %s:%d (%s)', $s['host'], $s['port'], $s['secure'] ? strtoupper( $s['secure'] ) : 'none' ) ) : esc_html__( 'Server mail (PHP mail) – SMTP recommended', 'aria' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Last error', 'aria' ); ?></th><td><?php echo $err ? esc_html( wp_date( 'Y-m-d H:i', $err['time'] ) . ' – ' . $err['message'] ) : esc_html__( 'none recorded', 'aria' ); ?></td></tr>
			</tbody>
		</table>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:28px">
			<?php wp_nonce_field( 'aria_mail_test' ); ?>
			<input type="hidden" name="action" value="aria_mail_test">
			<?php submit_button( __( 'Send test email', 'aria' ), 'secondary', 'submit', false ); ?>
		</form>

		<h2><?php esc_html_e( 'Send through a mailbox (SMTP)', 'aria' ); ?></h2>
		<p class="description" style="max-width:760px"><?php esc_html_e( 'Create a mailbox such as noreply@ariafoods.ae in your hosting panel, then enter it here. Mail is then signed and sent by your mail server and passes the domain’s strict DMARC policy.', 'aria' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'aria_mail_save' ); ?>
			<input type="hidden" name="action" value="aria_mail_save">
			<table class="form-table" role="presentation">
				<tr><th><?php esc_html_e( 'Use SMTP', 'aria' ); ?></th><td><label><input type="checkbox" name="aria_mail[smtp]" value="1" <?php checked( $s['smtp'] ); ?>> <?php esc_html_e( 'Send all site email through this mailbox', 'aria' ); ?></label></td></tr>
				<tr><th><label for="am-host"><?php esc_html_e( 'SMTP server', 'aria' ); ?></label></th><td><input id="am-host" class="regular-text" name="aria_mail[host]" value="<?php echo esc_attr( $s['host'] ); ?>"></td></tr>
				<tr><th><label for="am-port"><?php esc_html_e( 'Port and security', 'aria' ); ?></label></th><td><input id="am-port" class="small-text" name="aria_mail[port]" value="<?php echo esc_attr( $s['port'] ); ?>">
					<select name="aria_mail[secure]"><option value="tls" <?php selected( $s['secure'], 'tls' ); ?>>STARTTLS (587)</option><option value="ssl" <?php selected( $s['secure'], 'ssl' ); ?>>SSL (465)</option><option value="" <?php selected( $s['secure'], '' ); ?>><?php esc_html_e( 'None', 'aria' ); ?></option></select></td></tr>
				<tr><th><label for="am-user"><?php esc_html_e( 'Mailbox (username)', 'aria' ); ?></label></th><td><input id="am-user" class="regular-text" name="aria_mail[user]" value="<?php echo esc_attr( $s['user'] ); ?>" placeholder="noreply@<?php echo esc_attr( aria_mail_domain() ); ?>" autocomplete="off"></td></tr>
				<tr><th><label for="am-pass"><?php esc_html_e( 'Password', 'aria' ); ?></label></th><td>
					<?php if ( $const ) : ?>
						<em><?php esc_html_e( 'Set in wp-config.php (ARIA_SMTP_PASS).', 'aria' ); ?></em>
					<?php else : ?>
						<input id="am-pass" class="regular-text" type="password" name="aria_mail[pass]" value="" autocomplete="new-password" placeholder="<?php echo $s['pass'] ? esc_attr__( 'saved – type to replace', 'aria' ) : ''; ?>">
						<p class="description"><?php esc_html_e( 'Or keep it out of the database: add define( \'ARIA_SMTP_PASS\', \'…\' ); to wp-config.php.', 'aria' ); ?></p>
					<?php endif; ?>
				</td></tr>
				<tr><th colspan="2"><h2 style="margin:12px 0 0"><?php esc_html_e( 'Spam protection – Cloudflare Turnstile (optional)', 'aria' ); ?></h2>
					<p class="description" style="font-weight:400"><?php esc_html_e( 'Forms already block bots with a signed token, a timing check, a hidden trap field, a human-interaction check and spam filters. For extra protection create a free Turnstile widget in Cloudflare (Turnstile → Add widget, domain ariafoods.ae, mode Managed) and paste both keys here.', 'aria' ); ?></p></th></tr>
				<tr><th><label for="am-tss"><?php esc_html_e( 'Turnstile site key', 'aria' ); ?></label></th><td><input id="am-tss" class="regular-text" name="aria_mail[ts_site]" value="<?php echo esc_attr( function_exists( 'aria_turnstile_site' ) ? aria_turnstile_site() : '' ); ?>" autocomplete="off"></td></tr>
				<tr><th><label for="am-tsk"><?php esc_html_e( 'Turnstile secret key', 'aria' ); ?></label></th><td><input id="am-tsk" class="regular-text" type="password" name="aria_mail[ts_secret]" value="" autocomplete="new-password" placeholder="<?php echo ( function_exists( 'aria_turnstile_secret' ) && aria_turnstile_secret() ) ? esc_attr__( 'saved – type to replace', 'aria' ) : ''; ?>">
					<p><label><input type="checkbox" name="aria_mail[ts_clear]" value="1"> <?php esc_html_e( 'Turn Turnstile off (clear both keys)', 'aria' ); ?></label></p></td></tr>
				<tr><th colspan="2"><h2 style="margin:12px 0 0"><?php esc_html_e( 'Without SMTP', 'aria' ); ?></h2></th></tr>
				<tr><th><label for="am-from"><?php esc_html_e( 'From address without SMTP', 'aria' ); ?></label></th><td><input id="am-from" class="regular-text" name="aria_mail[from]" value="<?php echo esc_attr( $s['from'] ); ?>" placeholder="noreply@<?php echo esc_attr( aria_mail_domain() ); ?>"></td></tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
