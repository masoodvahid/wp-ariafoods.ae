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

/** Who receives inquiry e-mails: list from Aria settings, else the Customizer field, else the admin e-mail. */
function aria_leads_recipients() {
	$list = array_filter( array_map( 'trim', preg_split( '/[\s,;]+/', (string) get_option( 'aria_leads_emails', '' ) ) ), 'is_email' );
	if ( ! $list && is_email( aria_opt( 'leads_email' ) ) ) {
		$list = array( aria_opt( 'leads_email' ) );
	}
	return $list ? array_values( array_unique( $list ) ) : array( get_option( 'admin_email' ) );
}
