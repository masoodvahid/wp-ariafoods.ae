<?php
/**
 * Inquiries (aria_lead): statuses, history and the wp-admin screens.
 *
 * Status is shown to the customer in My account. Every change is logged with who made it and
 * where (WordPress or Telegram), and fires `aria_lead_status_changed` so the Telegram copies
 * of the inquiry stay in sync.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

function aria_lead_statuses() {
	return array( 'Received', 'Quoted', 'Sample sent', 'Order confirmed', 'In production', 'Shipped', 'Closed' );
}

/** Colour and short label for each status. */
function aria_lead_status_meta( $st ) {
	$m = array(
		'Received'        => array( '#b0592a', '🆕' ),
		'Quoted'          => array( '#2f6fb3', '💬' ),
		'Sample sent'     => array( '#7a5ab8', '📦' ),
		'Order confirmed' => array( '#1f8a5b', '✅' ),
		'In production'   => array( '#b58a1a', '🏭' ),
		'Shipped'         => array( '#0f7c86', '🚢' ),
		'Closed'          => array( '#6b6f76', '⏹' ),
		'Spam'            => array( '#a33', '🚫' ),
	);
	return isset( $m[ $st ] ) ? $m[ $st ] : array( '#6b6f76', '•' );
}

function aria_lead_status( $id ) {
	if ( get_post_meta( $id, '_aria_spam', true ) ) {
		return 'Spam';
	}
	$s = (string) get_post_meta( $id, '_aria_lead_status', true );
	return $s ? $s : 'Received';
}

/**
 * Change status (or mark/unmark spam) and log it.
 *
 * @param int    $id     Lead ID.
 * @param string $status One of aria_lead_statuses(), or 'Spam'.
 * @param string $by     Who changed it (display name).
 * @param string $via    'WordPress' or 'Telegram'.
 */
function aria_lead_set_status( $id, $status, $by = '', $via = 'WordPress' ) {
	$old = aria_lead_status( $id );
	if ( $old === $status ) {
		return false;
	}
	if ( 'Spam' === $status ) {
		update_post_meta( $id, '_aria_spam', 'marked by ' . ( $by ? $by : 'staff' ) );
		$t = get_post_field( 'post_title', $id );
		if ( 0 !== strpos( $t, '[Spam] ' ) ) {
			wp_update_post( array( 'ID' => $id, 'post_title' => '[Spam] ' . $t ) );
		}
	} else {
		if ( 'Spam' === $old ) {
			delete_post_meta( $id, '_aria_spam' );
			wp_update_post( array( 'ID' => $id, 'post_title' => preg_replace( '/^\[Spam\] /', '', get_post_field( 'post_title', $id ) ) ) );
		}
		if ( ! in_array( $status, aria_lead_statuses(), true ) ) {
			return false;
		}
		update_post_meta( $id, '_aria_lead_status', $status );
	}
	$log   = (array) get_post_meta( $id, '_aria_lead_log', true );
	$log[] = array( 't' => time(), 'from' => $old, 'to' => $status, 'by' => $by, 'via' => $via );
	update_post_meta( $id, '_aria_lead_log', array_slice( array_filter( $log ), -50 ) );
	do_action( 'aria_lead_status_changed', $id, $status, $old, $by, $via );
	return true;
}

function aria_lead_badge( $st ) {
	$m = aria_lead_status_meta( $st );
	return '<span class="al-badge" style="--c:' . esc_attr( $m[0] ) . '">' . esc_html( $st ) . '</span>';
}

if ( ! is_admin() ) {
	return;
}

/* ---------- Menu bubble: inquiries still at "Received" ---------- */

function aria_leads_new_count() {
	$n = get_transient( 'aria_leads_new' );
	if ( false === $n ) {
		$q = new WP_Query( array(
			'post_type'      => 'aria_lead',
			'post_status'    => 'private',
			'fields'         => 'ids',
			'posts_per_page' => 99,
			'no_found_rows'  => true,
			'meta_query'     => array(
				'relation' => 'AND',
				array( 'key' => '_aria_spam', 'compare' => 'NOT EXISTS' ),
				array(
					'relation' => 'OR',
					array( 'key' => '_aria_lead_status', 'value' => 'Received' ),
					array( 'key' => '_aria_lead_status', 'compare' => 'NOT EXISTS' ),
				),
			),
		) );
		$n = count( $q->posts );
		set_transient( 'aria_leads_new', $n, 10 * MINUTE_IN_SECONDS );
	}
	return (int) $n;
}
add_action( 'aria_lead_status_changed', function () {
	delete_transient( 'aria_leads_new' );
} );
add_action( 'aria_new_lead', function () {
	delete_transient( 'aria_leads_new' );
} );

add_action( 'admin_menu', function () {
	global $menu;
	$n = aria_leads_new_count();
	if ( ! $n ) {
		return;
	}
	foreach ( (array) $menu as $i => $item ) {
		if ( isset( $item[2] ) && 'edit.php?post_type=aria_lead' === $item[2] ) {
			$menu[ $i ][0] .= ' <span class="awaiting-mod count-' . $n . '"><span class="pending-count">' . $n . '</span></span>'; // phpcs:ignore
		}
	}
}, 99 );

/* ---------- List screen ---------- */

add_filter( 'manage_aria_lead_posts_columns', function ( $cols ) {
	return array(
		'cb'         => $cols['cb'],
		'al_status'  => __( 'Status', 'aria' ),
		'title'      => __( 'Inquiry', 'aria' ),
		'al_product' => __( 'Product & volume', 'aria' ),
		'al_dest'    => __( 'Destination', 'aria' ),
		'al_contact' => __( 'Contact', 'aria' ),
		'al_date'    => __( 'Received', 'aria' ),
	);
} );

add_action( 'manage_aria_lead_posts_custom_column', function ( $col, $id ) {
	$d = (array) get_post_meta( $id, '_aria_lead', true );
	$g = function ( $k ) use ( $d ) {
		return isset( $d[ $k ] ) ? $d[ $k ] : '';
	};
	switch ( $col ) {
		case 'al_status':
			$st = aria_lead_status( $id );
			echo '<select class="al-quick" data-id="' . (int) $id . '" aria-label="' . esc_attr__( 'Status', 'aria' ) . '" style="--c:' . esc_attr( aria_lead_status_meta( $st )[0] ) . '">';
			foreach ( array_merge( aria_lead_statuses(), array( 'Spam' ) ) as $s ) {
				echo '<option' . selected( $st, $s, false ) . '>' . esc_html( $s ) . '</option>';
			}
			echo '</select>';
			break;
		case 'al_product':
			echo '<strong>' . esc_html( $g( 'Product' ) ) . '</strong>';
			if ( $g( 'Quantity' ) ) {
				echo '<br><span class="al-muted">' . esc_html( $g( 'Quantity' ) ) . '</span>';
			}
			break;
		case 'al_dest':
			echo esc_html( $g( 'Port / country' ) );
			break;
		case 'al_contact':
			if ( $g( 'Email' ) ) {
				echo '<a class="al-ico" title="' . esc_attr( $g( 'Email' ) ) . '" href="mailto:' . esc_attr( $g( 'Email' ) ) . '"><span class="dashicons dashicons-email"></span></a>';
			}
			if ( $g( 'WhatsApp' ) ) {
				echo '<a class="al-ico al-wa" title="' . esc_attr( $g( 'WhatsApp' ) ) . '" target="_blank" rel="noopener" href="https://wa.me/' . esc_attr( aria_digits( $g( 'WhatsApp' ) ) ) . '"><span class="dashicons dashicons-whatsapp"></span></a>';
			}
			echo '<div class="al-muted al-small">' . esc_html( $g( 'Name' ) ) . '</div>';
			break;
		case 'al_date':
			$t = get_post_time( 'U', true, $id );
			echo '<span title="' . esc_attr( get_the_date( 'Y-m-d H:i', $id ) ) . '">' . esc_html( sprintf( __( '%s ago', 'aria' ), human_time_diff( $t ) ) ) . '</span>';
			break;
	}
}, 10, 2 );

/* Title column: company, type chip and spam reason. */
add_filter( 'the_title', function ( $title, $id = 0 ) {
	if ( ! is_admin() || 'aria_lead' !== get_post_type( $id ) || ! function_exists( 'get_current_screen' ) ) {
		return $title;
	}
	$sc = get_current_screen();
	if ( ! $sc || 'edit-aria_lead' !== $sc->id ) {
		return $title;
	}
	$d    = (array) get_post_meta( $id, '_aria_lead', true );
	$who  = ! empty( $d['Company'] ) ? $d['Company'] : ( ! empty( $d['Name'] ) ? $d['Name'] : $title );
	return $who;
}, 10, 2 );

add_filter( 'display_post_states', function ( $states, $post ) {
	return 'aria_lead' === $post->post_type ? array() : $states;
}, 10, 2 );
add_filter( 'views_edit-aria_lead', function ( $views ) {
	return array_intersect_key( $views, array( 'all' => 1, 'trash' => 1 ) );
} );

add_filter( 'post_row_actions', function ( $actions, $post ) {
	if ( 'aria_lead' !== $post->post_type ) {
		return $actions;
	}
	$type  = (string) get_post_meta( $post->ID, '_aria_lead_type', true );
	$types = array( 'quote' => 'Quote', 'sample' => 'Sample', 'contact' => 'Message', 'tracking' => 'Order status' );
	$out   = array( 'al_type' => '<span class="al-chip">' . esc_html( isset( $types[ $type ] ) ? $types[ $type ] : 'Inquiry' ) . '</span>' );
	if ( isset( $actions['edit'] ) ) {
		$out['edit'] = '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">' . esc_html__( 'Open', 'aria' ) . '</a>';
	}
	if ( isset( $actions['trash'] ) ) {
		$out['trash'] = $actions['trash'];
	}
	$why = get_post_meta( $post->ID, '_aria_spam', true );
	if ( $why ) {
		$out['al_spam'] = '<span class="al-muted">' . esc_html( sprintf( __( 'Spam: %s', 'aria' ), $why ) ) . '</span>';
	}
	return $out;
}, 10, 2 );

/* Status and type filters; spam hidden unless asked for. */
add_action( 'restrict_manage_posts', function ( $pt ) {
	if ( 'aria_lead' !== $pt ) {
		return;
	}
	$cur = isset( $_GET['al_status'] ) ? sanitize_text_field( wp_unslash( $_GET['al_status'] ) ) : ''; // phpcs:ignore
	echo '<select name="al_status"><option value="">' . esc_html__( 'All statuses (no spam)', 'aria' ) . '</option>';
	foreach ( array_merge( aria_lead_statuses(), array( 'Spam' ) ) as $s ) {
		echo '<option' . selected( $cur, $s, false ) . '>' . esc_html( $s ) . '</option>';
	}
	echo '</select>';
	$ty = isset( $_GET['al_type'] ) ? sanitize_key( wp_unslash( $_GET['al_type'] ) ) : ''; // phpcs:ignore
	echo '<select name="al_type"><option value="">' . esc_html__( 'All types', 'aria' ) . '</option>';
	foreach ( array( 'quote' => 'Quote', 'sample' => 'Sample', 'contact' => 'Message', 'tracking' => 'Order status' ) as $k => $l ) {
		echo '<option value="' . esc_attr( $k ) . '"' . selected( $ty, $k, false ) . '>' . esc_html( $l ) . '</option>';
	}
	echo '</select>';
} );

add_action( 'pre_get_posts', function ( $q ) {
	if ( ! $q->is_main_query() || 'aria_lead' !== $q->get( 'post_type' ) || ! function_exists( 'get_current_screen' ) ) {
		return;
	}
	$st = isset( $_GET['al_status'] ) ? sanitize_text_field( wp_unslash( $_GET['al_status'] ) ) : ''; // phpcs:ignore
	$ty = isset( $_GET['al_type'] ) ? sanitize_key( wp_unslash( $_GET['al_type'] ) ) : ''; // phpcs:ignore
	$mq = array( 'relation' => 'AND' );
	if ( 'Spam' === $st ) {
		$mq[] = array( 'key' => '_aria_spam', 'compare' => 'EXISTS' );
	} else {
		$mq[] = array( 'key' => '_aria_spam', 'compare' => 'NOT EXISTS' );
		if ( 'Received' === $st ) {
			$mq[] = array( 'relation' => 'OR', array( 'key' => '_aria_lead_status', 'value' => 'Received' ), array( 'key' => '_aria_lead_status', 'compare' => 'NOT EXISTS' ) );
		} elseif ( $st ) {
			$mq[] = array( 'key' => '_aria_lead_status', 'value' => $st );
		}
	}
	if ( $ty ) {
		$mq[] = array( 'key' => '_aria_lead_type', 'value' => $ty );
	}
	$q->set( 'meta_query', $mq );
	if ( ! $q->get( 'orderby' ) ) {
		$q->set( 'orderby', 'date' );
		$q->set( 'order', 'DESC' );
	}
} );

/* Summary cards above the list. */
add_action( 'all_admin_notices', function () {
	$sc = get_current_screen();
	if ( ! $sc || 'edit-aria_lead' !== $sc->id ) {
		return;
	}
	global $wpdb;
	$rows  = $wpdb->get_results( "SELECT p.ID, p.post_date_gmt, s.meta_value AS st, sp.meta_value AS spam FROM {$wpdb->posts} p
		LEFT JOIN {$wpdb->postmeta} s ON s.post_id = p.ID AND s.meta_key = '_aria_lead_status'
		LEFT JOIN {$wpdb->postmeta} sp ON sp.post_id = p.ID AND sp.meta_key = '_aria_spam'
		WHERE p.post_type = 'aria_lead' AND p.post_status = 'private'" );
	$c     = array( 'new' => 0, 'open' => 0, 'won' => 0, 'month' => 0, 'spam' => 0 );
	$month = strtotime( gmdate( 'Y-m-01 00:00:00' ) );
	foreach ( $rows as $r ) {
		if ( $r->spam ) {
			++$c['spam'];
			continue;
		}
		$st = $r->st ? $r->st : 'Received';
		if ( 'Received' === $st ) {
			++$c['new'];
		} elseif ( in_array( $st, array( 'Quoted', 'Sample sent' ), true ) ) {
			++$c['open'];
		} elseif ( in_array( $st, array( 'Order confirmed', 'In production', 'Shipped' ), true ) ) {
			++$c['won'];
		}
		if ( strtotime( $r->post_date_gmt ) >= $month ) {
			++$c['month'];
		}
	}
	$base  = admin_url( 'edit.php?post_type=aria_lead' );
	$cards = array(
		array( __( 'New – reply needed', 'aria' ), $c['new'], add_query_arg( 'al_status', 'Received', $base ), '#b0592a' ),
		array( __( 'Quoted / sample sent', 'aria' ), $c['open'], add_query_arg( 'al_status', 'Quoted', $base ), '#2f6fb3' ),
		array( __( 'Orders in progress', 'aria' ), $c['won'], add_query_arg( 'al_status', 'Order confirmed', $base ), '#1f8a5b' ),
		array( __( 'This month', 'aria' ), $c['month'], $base, '#555' ),
		array( __( 'Spam caught', 'aria' ), $c['spam'], add_query_arg( 'al_status', 'Spam', $base ), '#a33' ),
	);
	echo '<div class="notice al-cards">';
	foreach ( $cards as $cd ) {
		echo '<a class="al-card" style="--c:' . esc_attr( $cd[3] ) . '" href="' . esc_url( $cd[2] ) . '"><strong>' . (int) $cd[1] . '</strong><span>' . esc_html( $cd[0] ) . '</span></a>';
	}
	echo '</div>';
} );

/* Quick status change from the list. */
add_action( 'wp_ajax_aria_lead_status', function () {
	check_ajax_referer( 'aria_lead_quick' );
	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	$st = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
	if ( ! $id || 'aria_lead' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) ) {
		wp_send_json_error( 'Not allowed', 403 );
	}
	aria_lead_set_status( $id, $st, wp_get_current_user()->display_name, 'WordPress' );
	wp_send_json_success( array( 'status' => aria_lead_status( $id ), 'color' => aria_lead_status_meta( aria_lead_status( $id ) )[0] ) );
} );

/* ---------- Edit screen ---------- */

add_action( 'add_meta_boxes_aria_lead', function () {
	remove_meta_box( 'submitdiv', 'aria_lead', 'side' );
	add_meta_box( 'aria_lead_box', __( 'Inquiry', 'aria' ), 'aria_lead_box', 'aria_lead', 'normal', 'high' );
	add_meta_box( 'aria_lead_side', __( 'Status', 'aria' ), 'aria_lead_side', 'aria_lead', 'side', 'high' );
	add_meta_box( 'aria_lead_log', __( 'History', 'aria' ), 'aria_lead_log_box', 'aria_lead', 'side', 'default' );
} );

/* The title field is not needed: the inquiry is read-only. */
add_action( 'admin_head-post.php', function () {
	if ( 'aria_lead' === get_post_type() ) {
		echo '<style>#titlediv{display:none}</style>';
	}
} );

function aria_lead_box( $post ) {
	$d     = (array) get_post_meta( $post->ID, '_aria_lead', true );
	$g     = function ( $k ) use ( $d ) {
		return isset( $d[ $k ] ) ? $d[ $k ] : '';
	};
	$type  = (string) get_post_meta( $post->ID, '_aria_lead_type', true );
	$types = array( 'quote' => 'Quote request', 'sample' => 'Sample request', 'contact' => 'Message', 'tracking' => 'Order status' );
	$subj  = rawurlencode( 'Aria Foods – your ' . strtolower( isset( $types[ $type ] ) ? $types[ $type ] : 'inquiry' ) . ( $g( 'Product' ) ? ' for ' . $g( 'Product' ) : '' ) );
	echo '<div class="al-head"><div><span class="al-chip">' . esc_html( isset( $types[ $type ] ) ? $types[ $type ] : 'Inquiry' ) . '</span>';
	echo '<h2 class="al-title">' . esc_html( $g( 'Company' ) ? $g( 'Company' ) : $g( 'Name' ) ) . '</h2>';
	echo '<p class="al-muted">' . esc_html( trim( $g( 'Name' ) . ( $g( 'Company' ) ? '' : '' ) ) ) . ' · ' . esc_html( get_the_date( 'j M Y, H:i', $post ) ) . '</p></div>';
	echo '<div class="al-actions">';
	if ( $g( 'Email' ) ) {
		echo '<a class="button button-primary" href="mailto:' . esc_attr( $g( 'Email' ) ) . '?subject=' . esc_attr( $subj ) . '"><span class="dashicons dashicons-email"></span> ' . esc_html__( 'Reply by email', 'aria' ) . '</a>';
	}
	if ( $g( 'WhatsApp' ) ) {
		echo '<a class="button al-wa-btn" target="_blank" rel="noopener" href="https://wa.me/' . esc_attr( aria_digits( $g( 'WhatsApp' ) ) ) . '"><span class="dashicons dashicons-whatsapp"></span> WhatsApp</a>';
	}
	echo '</div></div>';

	$why = get_post_meta( $post->ID, '_aria_spam', true );
	if ( $why ) {
		echo '<div class="notice notice-warning inline"><p>' . esc_html( sprintf( __( 'Marked as spam (%s). No email or Telegram alert was sent.', 'aria' ), $why ) ) . '</p></div>';
	}

	$order = array( 'Product', 'Quantity', 'Port / country', 'Order no.', 'Email', 'WhatsApp', 'Name', 'Company' );
	echo '<dl class="al-grid">';
	foreach ( $order as $k ) {
		if ( '' === $g( $k ) ) {
			continue;
		}
		$v = $g( $k );
		if ( 'Email' === $k ) {
			$h = '<a href="mailto:' . esc_attr( $v ) . '">' . esc_html( $v ) . '</a>';
		} elseif ( 'WhatsApp' === $k ) {
			$h = '<a href="https://wa.me/' . esc_attr( aria_digits( $v ) ) . '" target="_blank" rel="noopener">' . esc_html( $v ) . '</a>';
		} else {
			$h = esc_html( $v );
		}
		echo '<div><dt>' . esc_html( $k ) . '</dt><dd>' . $h . '</dd></div>'; // phpcs:ignore
	}
	echo '</dl>';
	if ( $g( 'Message' ) ) {
		echo '<h3 class="al-sub">' . esc_html__( 'Message', 'aria' ) . '</h3><div class="al-msg">' . nl2br( esc_html( $g( 'Message' ) ) ) . '</div>';
	}
	if ( $g( 'Sent from' ) ) {
		echo '<p class="al-muted al-small">' . esc_html__( 'Sent from', 'aria' ) . ': <a href="' . esc_url( $g( 'Sent from' ) ) . '" target="_blank">' . esc_html( preg_replace( '~^https?://[^/]+~', '', $g( 'Sent from' ) ) ) . '</a></p>';
	}
	$uid = (int) get_post_meta( $post->ID, '_aria_user', true );
	if ( $uid ) {
		echo '<p class="al-small">' . esc_html__( 'Customer account:', 'aria' ) . ' <a href="' . esc_url( get_edit_user_link( $uid ) ) . '">' . esc_html( get_the_author_meta( 'display_name', $uid ) ) . '</a></p>';
	}
}

function aria_lead_side( $post ) {
	$st = aria_lead_status( $post->ID );
	wp_nonce_field( 'aria_lead_status', 'aria_lead_nonce' );
	echo '<p>' . aria_lead_badge( $st ) . '</p>'; // phpcs:ignore
	echo '<p><label for="aria_lead_status" class="al-small">' . esc_html__( 'Change status (customers see it in My account)', 'aria' ) . '</label><br><select id="aria_lead_status" name="aria_lead_status" style="width:100%">';
	foreach ( array_merge( aria_lead_statuses(), array( 'Spam' ) ) as $s ) {
		echo '<option' . selected( $st, $s, false ) . '>' . esc_html( $s ) . '</option>';
	}
	echo '</select></p>';
	echo '<p><label for="aria_lead_notes" class="al-small">' . esc_html__( 'Internal notes (staff only)', 'aria' ) . '</label><textarea id="aria_lead_notes" name="aria_lead_notes" rows="4" style="width:100%">' . esc_textarea( (string) get_post_meta( $post->ID, '_aria_lead_notes', true ) ) . '</textarea></p>';
	echo '<input type="hidden" name="post_status" value="private">';
	submit_button( __( 'Save', 'aria' ), 'primary', 'save', false );
	echo ' <a class="submitdelete deletion" style="margin-left:10px" href="' . esc_url( get_delete_post_link( $post->ID ) ) . '">' . esc_html__( 'Move to trash', 'aria' ) . '</a>';
}

function aria_lead_log_box( $post ) {
	$log = array_reverse( array_filter( (array) get_post_meta( $post->ID, '_aria_lead_log', true ) ) );
	echo '<ul class="al-log">';
	foreach ( $log as $l ) {
		echo '<li>' . aria_lead_badge( $l['to'] ) . ' <span class="al-small">' . esc_html( wp_date( 'j M, H:i', $l['t'] ) ) . ( $l['by'] ? ' · ' . esc_html( $l['by'] ) : '' ) . ' · ' . esc_html( $l['via'] ) . '</span></li>'; // phpcs:ignore
	}
	echo '<li>' . aria_lead_badge( 'Received' ) . ' <span class="al-small">' . esc_html( get_the_date( 'j M, H:i', $post ) ) . ' · ' . esc_html__( 'website form', 'aria' ) . '</span></li></ul>'; // phpcs:ignore
}

add_action( 'save_post_aria_lead', function ( $post_id ) {
	if ( ! isset( $_POST['aria_lead_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aria_lead_nonce'] ) ), 'aria_lead_status' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['aria_lead_notes'] ) ) {
		update_post_meta( $post_id, '_aria_lead_notes', sanitize_textarea_field( wp_unslash( $_POST['aria_lead_notes'] ) ) );
	}
	$st = sanitize_text_field( wp_unslash( $_POST['aria_lead_status'] ?? '' ) );
	if ( $st ) {
		remove_all_actions( 'save_post_aria_lead' ); // wp_update_post inside spam handling would loop.
		aria_lead_set_status( $post_id, $st, wp_get_current_user()->display_name, 'WordPress' );
	}
} );

/* Leads stay private when saved from the edit screen. */
add_filter( 'wp_insert_post_data', function ( $data ) {
	if ( 'aria_lead' === $data['post_type'] && in_array( $data['post_status'], array( 'publish', 'draft', 'pending' ), true ) ) {
		$data['post_status'] = 'private';
	}
	return $data;
} );

/* ---------- Styles and quick-edit script ---------- */

add_action( 'admin_head', function () {
	$sc = get_current_screen();
	if ( ! $sc || 'aria_lead' !== $sc->post_type ) {
		return;
	}
	?>
	<style>
	.al-badge{display:inline-block;padding:2px 10px;border-radius:99px;font-size:12px;font-weight:600;color:var(--c);background:color-mix(in srgb,var(--c) 12%,#fff);border:1px solid color-mix(in srgb,var(--c) 30%,#fff);white-space:nowrap}
	.al-quick{border-radius:99px!important;border:1px solid color-mix(in srgb,var(--c) 40%,#fff)!important;color:var(--c)!important;background-color:color-mix(in srgb,var(--c) 10%,#fff)!important;font-weight:600;min-height:28px!important;padding:0 26px 0 10px!important;font-size:12px!important;max-width:150px}
	.al-chip{display:inline-block;padding:1px 8px;border-radius:4px;background:#f0ebe5;color:#7a4a2a;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.03em}
	.al-muted{color:#6b6f76}.al-small{font-size:12px}
	.al-ico{display:inline-flex;width:28px;height:28px;align-items:center;justify-content:center;border-radius:50%;background:#f3f4f6;margin-right:4px;text-decoration:none}.al-ico:hover{background:#e7e9ee}.al-wa{color:#1f9d55}
	.al-cards.notice{border:0;background:none;box-shadow:none;padding:0}.al-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin:16px 0 4px;max-width:1100px}
	.al-card{display:flex;flex-direction:column;gap:2px;padding:14px 16px;background:#fff;border:1px solid #e3e5e8;border-left:4px solid var(--c);border-radius:8px;text-decoration:none;color:#1d2327}
	.al-card strong{font-size:24px;line-height:1.1;color:var(--c)}.al-card span{font-size:12px;color:#50575e}.al-card:hover{box-shadow:0 2px 8px rgba(0,0,0,.06)}
	.wp-list-table .column-al_status{width:160px}.wp-list-table .column-al_contact{width:130px}.wp-list-table .column-al_date{width:110px}
	.wp-list-table.posts .row-title{font-size:14px}
	.al-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;padding:4px 0 14px;border-bottom:1px solid #eee;margin-bottom:14px}
	.al-title{font-size:22px!important;margin:6px 0 2px!important;padding:0!important}
	.al-actions{display:flex;gap:8px;flex-wrap:wrap}.al-actions .dashicons{vertical-align:middle;margin-top:-2px}
	.al-wa-btn{color:#1f7a45!important;border-color:#1f9d55!important}
	.al-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;margin:0}
	.al-grid div{background:#f8f7f5;border-radius:8px;padding:10px 12px}.al-grid dt{font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#6b6f76;margin-bottom:3px}.al-grid dd{margin:0;font-size:14px;font-weight:600;word-break:break-word}
	.al-sub{margin:18px 0 6px!important;font-size:13px!important}.al-msg{background:#fffdf8;border:1px solid #eee3d3;border-radius:8px;padding:12px 14px;font-size:14px;line-height:1.6}
	.al-log{margin:0}.al-log li{display:flex;flex-wrap:wrap;gap:6px;align-items:center;margin-bottom:8px}
	</style>
	<?php
} );

add_action( 'admin_footer-edit.php', function () {
	$sc = get_current_screen();
	if ( ! $sc || 'edit-aria_lead' !== $sc->id ) {
		return;
	}
	?>
	<script>
	document.querySelectorAll('.al-quick').forEach(function (s) {
		s.addEventListener('change', function () {
			var fd = new FormData(); fd.append('action', 'aria_lead_status'); fd.append('id', s.dataset.id); fd.append('status', s.value);
			fd.append('_ajax_nonce', <?php echo wp_json_encode( wp_create_nonce( 'aria_lead_quick' ) ); ?>);
			s.disabled = true;
			fetch(ajaxurl, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (j) {
				s.disabled = false;
				if (j.success) { s.style.setProperty('--c', j.data.color); s.closest('tr').style.transition = 'background .6s'; s.closest('tr').style.background = '#f3fbf6'; setTimeout(function () { s.closest('tr').style.background = ''; }, 900); }
			}).catch(function () { s.disabled = false; });
		});
	});
	</script>
	<?php
} );
