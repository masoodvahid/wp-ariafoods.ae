<?php
/**
 * Telegram alerts for new inquiries, with status buttons.
 *
 * - Staff join the bot with a private join code (Aria settings → Telegram). Nobody else can use it.
 * - Each new inquiry is sent to every staff member with buttons for the status and for spam.
 *   Pressing a button changes the status in WordPress and updates everyone's copy of the message.
 * - Changes made in WordPress update the Telegram messages too.
 * - Webhook: https://<site>/?aria-tg=1, checked with Telegram's secret-token header.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

function aria_tg_settings() {
	$s = wp_parse_args( (array) get_option( 'aria_tg', array() ), array(
		'token'  => '',
		'bot'    => 'ariafoodsae_bot',
		'code'   => '',
		'staff'  => array(),
		'hooked' => 0,
	) );
	if ( defined( 'ARIA_TG_TOKEN' ) && ARIA_TG_TOKEN ) {
		$s['token'] = ARIA_TG_TOKEN;
	}
	if ( ! $s['code'] ) {
		$s['code'] = strtoupper( wp_generate_password( 8, false, false ) );
		$raw         = (array) get_option( 'aria_tg', array() );
		$raw['code'] = $s['code'];
		update_option( 'aria_tg', $raw, false );
	}
	return $s;
}

function aria_tg_save( $changes ) {
	update_option( 'aria_tg', array_merge( (array) get_option( 'aria_tg', array() ), $changes ), false );
}

function aria_tg_secret() {
	$s = aria_tg_settings();
	return substr( hash_hmac( 'sha256', 'tg|' . $s['token'], wp_salt( 'auth' ) ), 0, 40 );
}

function aria_tg_api( $method, $params = array(), $timeout = 8 ) {
	$s = aria_tg_settings();
	if ( ! $s['token'] ) {
		return null;
	}
	$res = wp_remote_post( 'https://api.telegram.org/bot' . $s['token'] . '/' . $method, array(
		'timeout' => $timeout,
		'headers' => array( 'Content-Type' => 'application/json' ),
		'body'    => wp_json_encode( $params ),
	) );
	if ( is_wp_error( $res ) ) {
		update_option( 'aria_tg_error', array( 'time' => time(), 'message' => $res->get_error_message() ), false );
		return null;
	}
	$j = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( empty( $j['ok'] ) ) {
		update_option( 'aria_tg_error', array( 'time' => time(), 'message' => $method . ': ' . ( isset( $j['description'] ) ? $j['description'] : 'HTTP ' . wp_remote_retrieve_response_code( $res ) ) ), false );
		return null;
	}
	return isset( $j['result'] ) ? $j['result'] : true;
}

function aria_tg_e( $s ) {
	return htmlspecialchars( (string) $s, ENT_NOQUOTES, 'UTF-8' );
}

/** Message text for an inquiry. */
function aria_tg_text( $id ) {
	$d     = (array) get_post_meta( $id, '_aria_lead', true );
	$type  = (string) get_post_meta( $id, '_aria_lead_type', true );
	$types = array( 'quote' => 'Quote request', 'sample' => 'Sample request', 'contact' => 'Message', 'tracking' => 'Order status' );
	$st    = aria_lead_status( $id );
	$m     = aria_lead_status_meta( $st );
	$t     = $m[1] . ' <b>' . aria_tg_e( isset( $types[ $type ] ) ? $types[ $type ] : 'Inquiry' ) . '</b> · #' . (int) $id . "\n";
	$t    .= '<b>' . aria_tg_e( ! empty( $d['Company'] ) ? $d['Company'] : ( isset( $d['Name'] ) ? $d['Name'] : '' ) ) . "</b>\n\n";
	foreach ( array( 'Name', 'Product', 'Quantity', 'Port / country', 'Order no.', 'Email', 'WhatsApp' ) as $k ) {
		if ( ! empty( $d[ $k ] ) ) {
			$t .= aria_tg_e( $k ) . ': ' . aria_tg_e( $d[ $k ] ) . "\n";
		}
	}
	if ( ! empty( $d['Message'] ) ) {
		$t .= "\n<i>" . aria_tg_e( mb_substr( $d['Message'], 0, 900 ) ) . "</i>\n";
	}
	$t  .= "\nStatus: <b>" . aria_tg_e( $st ) . '</b>';
	$log = array_filter( (array) get_post_meta( $id, '_aria_lead_log', true ) );
	if ( $log ) {
		$l  = end( $log );
		$t .= ' – ' . aria_tg_e( trim( ( $l['by'] ? $l['by'] : '' ) . ', ' . wp_date( 'j M H:i', $l['t'] ), ', ' ) );
	}
	return $t;
}

function aria_tg_keyboard( $id ) {
	$st   = aria_lead_status( $id );
	$rows = array();
	$row  = array();
	foreach ( aria_lead_statuses() as $i => $s ) {
		$row[] = array( 'text' => ( $s === $st ? '● ' : '' ) . $s, 'callback_data' => 'st:' . (int) $id . ':' . $i );
		if ( 3 === count( $row ) ) {
			$rows[] = $row;
			$row    = array();
		}
	}
	if ( $row ) {
		$rows[] = $row;
	}
	$rows[] = array(
		array( 'text' => 'Spam' === $st ? '↩︎ Not spam' : '🚫 Spam', 'callback_data' => 'sp:' . (int) $id ),
		array( 'text' => 'Open in WordPress', 'url' => admin_url( 'post.php?post=' . (int) $id . '&action=edit' ) ),
	);
	$d = (array) get_post_meta( $id, '_aria_lead', true );
	if ( ! empty( $d['WhatsApp'] ) ) {
		$rows[ count( $rows ) - 1 ][] = array( 'text' => 'WhatsApp', 'url' => 'https://wa.me/' . aria_digits( $d['WhatsApp'] ) );
	}
	return array( 'inline_keyboard' => $rows );
}

/* New inquiry → every staff member, after the visitor has their reply. */
add_action( 'aria_new_lead', function ( $id ) {
	$s = aria_tg_settings();
	if ( ! $s['token'] || ! $s['staff'] ) {
		return;
	}
	register_shutdown_function( function () use ( $id ) {
		if ( function_exists( 'fastcgi_finish_request' ) ) {
			fastcgi_finish_request();
		}
		aria_tg_send_lead( $id );
	} );
} );

function aria_tg_send_lead( $id ) {
	$s    = aria_tg_settings();
	$msgs = array();
	foreach ( array_keys( (array) $s['staff'] ) as $chat ) {
		$r = aria_tg_api( 'sendMessage', array(
			'chat_id'                  => $chat,
			'text'                     => aria_tg_text( $id ),
			'parse_mode'               => 'HTML',
			'disable_web_page_preview' => true,
			'reply_markup'             => aria_tg_keyboard( $id ),
		) );
		if ( $r && isset( $r['message_id'] ) ) {
			$msgs[ $chat ] = (int) $r['message_id'];
		}
	}
	update_post_meta( $id, '_aria_tg_msgs', $msgs );
}

/* Status changed anywhere → refresh every copy. */
add_action( 'aria_lead_status_changed', function ( $id ) {
	$msgs = (array) get_post_meta( $id, '_aria_tg_msgs', true );
	foreach ( array_filter( $msgs ) as $chat => $mid ) {
		aria_tg_api( 'editMessageText', array(
			'chat_id'                  => $chat,
			'message_id'               => $mid,
			'text'                     => aria_tg_text( $id ),
			'parse_mode'               => 'HTML',
			'disable_web_page_preview' => true,
			'reply_markup'             => aria_tg_keyboard( $id ),
		), 5 );
	}
}, 20 );

/* ---------- Webhook ---------- */

add_action( 'init', function () {
	if ( empty( $_GET['aria-tg'] ) || 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) ) { // phpcs:ignore
		return;
	}
	$hdr = isset( $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ) ? (string) wp_unslash( $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ) : ''; // phpcs:ignore
	if ( ! aria_tg_settings()['token'] || ! hash_equals( aria_tg_secret(), $hdr ) ) {
		status_header( 403 );
		exit;
	}
	$u = json_decode( (string) file_get_contents( 'php://input' ), true );
	if ( is_array( $u ) ) {
		aria_tg_handle( $u );
	}
	status_header( 200 );
	echo 'ok';
	exit;
}, 1 );

function aria_tg_handle( $u ) {
	$s     = aria_tg_settings();
	$staff = (array) $s['staff'];

	if ( isset( $u['callback_query'] ) ) {
		$cq   = $u['callback_query'];
		$chat = isset( $cq['message']['chat']['id'] ) ? (string) $cq['message']['chat']['id'] : '';
		if ( ! isset( $staff[ $chat ] ) ) {
			aria_tg_api( 'answerCallbackQuery', array( 'callback_query_id' => $cq['id'], 'text' => 'Not allowed.' ) );
			return;
		}
		$by    = $staff[ $chat ]['name'];
		$parts = explode( ':', (string) $cq['data'] );
		$id    = isset( $parts[1] ) ? absint( $parts[1] ) : 0;
		if ( ! $id || 'aria_lead' !== get_post_type( $id ) ) {
			aria_tg_api( 'answerCallbackQuery', array( 'callback_query_id' => $cq['id'], 'text' => 'Inquiry not found.' ) );
			return;
		}
		$list = aria_lead_statuses();
		if ( 'st' === $parts[0] && isset( $parts[2], $list[ (int) $parts[2] ] ) ) {
			$new = $list[ (int) $parts[2] ];
		} elseif ( 'sp' === $parts[0] ) {
			$new = 'Spam' === aria_lead_status( $id ) ? 'Received' : 'Spam';
		} else {
			return;
		}
		$msgs = (array) get_post_meta( $id, '_aria_tg_msgs', true );
		if ( ! isset( $msgs[ $chat ] ) && isset( $cq['message']['message_id'] ) ) {
			$msgs[ $chat ] = (int) $cq['message']['message_id'];
			update_post_meta( $id, '_aria_tg_msgs', $msgs );
		}
		$changed = aria_lead_set_status( $id, $new, $by, 'Telegram' );
		aria_tg_api( 'answerCallbackQuery', array( 'callback_query_id' => $cq['id'], 'text' => $changed ? 'Status: ' . $new : 'Already ' . $new ) );
		return;
	}

	if ( empty( $u['message']['chat']['id'] ) || 'private' !== ( isset( $u['message']['chat']['type'] ) ? $u['message']['chat']['type'] : '' ) ) {
		return;
	}
	$msg  = $u['message'];
	$chat = (string) $msg['chat']['id'];
	$text = trim( isset( $msg['text'] ) ? $msg['text'] : '' );
	$name = trim( ( isset( $msg['from']['first_name'] ) ? $msg['from']['first_name'] : '' ) . ' ' . ( isset( $msg['from']['last_name'] ) ? $msg['from']['last_name'] : '' ) );
	$say  = function ( $t ) use ( $chat ) {
		aria_tg_api( 'sendMessage', array( 'chat_id' => $chat, 'text' => $t, 'parse_mode' => 'HTML' ) );
	};

	// Join: "/start CODE" (from the invite link) or "/join CODE".
	if ( preg_match( '~^/(start|join)\s+([A-Za-z0-9]+)~', $text, $m ) ) {
		if ( hash_equals( strtoupper( $s['code'] ), strtoupper( $m[2] ) ) ) {
			$staff[ $chat ] = array(
				'name'     => $name ? $name : 'Staff',
				'username' => isset( $msg['from']['username'] ) ? $msg['from']['username'] : '',
				'joined'   => time(),
			);
			aria_tg_save( array( 'staff' => $staff ) );
			$say( "✅ Welcome, <b>" . aria_tg_e( $name ) . "</b>. You will receive new inquiries from " . aria_tg_e( wp_parse_url( home_url(), PHP_URL_HOST ) ) . " here and can change their status with the buttons.\n\n/new – inquiries waiting for a reply" );
		} else {
			$say( 'This code is not valid. Ask your manager for the current staff link.' );
		}
		return;
	}
	if ( ! isset( $staff[ $chat ] ) ) {
		$say( 'This bot is for Aria Foods staff only.' );
		return;
	}
	if ( 0 === strpos( $text, '/new' ) ) {
		$q = get_posts( array(
			'post_type'   => 'aria_lead',
			'post_status' => 'private',
			'numberposts' => 10,
			'meta_query'  => array(
				'relation' => 'AND',
				array( 'key' => '_aria_spam', 'compare' => 'NOT EXISTS' ),
				array( 'relation' => 'OR', array( 'key' => '_aria_lead_status', 'value' => 'Received' ), array( 'key' => '_aria_lead_status', 'compare' => 'NOT EXISTS' ) ),
			),
		) );
		if ( ! $q ) {
			$say( '👍 No inquiries are waiting for a reply.' );
			return;
		}
		foreach ( $q as $p ) {
			$r = aria_tg_api( 'sendMessage', array( 'chat_id' => $chat, 'text' => aria_tg_text( $p->ID ), 'parse_mode' => 'HTML', 'disable_web_page_preview' => true, 'reply_markup' => aria_tg_keyboard( $p->ID ) ) );
			if ( $r && isset( $r['message_id'] ) ) {
				$msgs          = (array) get_post_meta( $p->ID, '_aria_tg_msgs', true );
				$msgs[ $chat ] = (int) $r['message_id'];
				update_post_meta( $p->ID, '_aria_tg_msgs', $msgs );
			}
		}
		return;
	}
	if ( 0 === strpos( $text, '/leave' ) ) {
		unset( $staff[ $chat ] );
		aria_tg_save( array( 'staff' => $staff ) );
		$say( 'You will no longer receive inquiries.' );
		return;
	}
	$say( "/new – inquiries waiting for a reply\n/leave – stop receiving inquiries" );
}

/** Connect the bot to this site (setWebhook) and set its command list. */
function aria_tg_connect() {
	$ok = aria_tg_api( 'setWebhook', array(
		'url'             => add_query_arg( 'aria-tg', '1', home_url( '/' ) ),
		'secret_token'    => aria_tg_secret(),
		'allowed_updates' => array( 'message', 'callback_query' ),
		'drop_pending_updates' => true,
	) );
	if ( $ok ) {
		aria_tg_api( 'setMyCommands', array( 'commands' => array(
			array( 'command' => 'new', 'description' => 'Inquiries waiting for a reply' ),
			array( 'command' => 'leave', 'description' => 'Stop receiving inquiries' ),
		) ) );
		$me = aria_tg_api( 'getMe' );
		aria_tg_save( array( 'hooked' => time(), 'bot' => ( $me && ! empty( $me['username'] ) ) ? $me['username'] : aria_tg_settings()['bot'] ) );
		delete_option( 'aria_tg_error' );
	}
	return (bool) $ok;
}
