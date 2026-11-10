<?php
/**
 * Inquiry forms (quote, sample, contact, order tracking) without plugins.
 * Posts to admin-post.php; JS submits the same form with fetch and gets JSON back.
 * Every inquiry is saved under Inquiries in wp-admin and emailed to the trade desk.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

function aria_product_choices() {
	$choices = get_transient( 'aria_product_choices' );
	if ( false === $choices ) {
		$choices = array();
		$posts   = get_posts( array(
			'post_type'      => 'product',
			'posts_per_page' => 50,
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		) );
		foreach ( $posts as $p ) {
			$choices[] = $p->post_title;
		}
		set_transient( 'aria_product_choices', $choices, DAY_IN_SECONDS );
	}
	return $choices ? $choices : array( 'Mazafati dates', 'Piarom dates', 'Zahedi dates', 'Sayer dates', 'Date syrup', 'Date paste', 'Date powder', 'Liquid date sugar', 'Chopped dates' );
}
add_action( 'save_post_product', function () {
	delete_transient( 'aria_product_choices' );
} );

/**
 * Render a form.
 *
 * @param string $type    quote|sample|contact|tracking.
 * @param array  $args    product (preselected), title, id.
 */
function aria_form( $type = 'quote', $args = array() ) {
	$args    = wp_parse_args( $args, array( 'product' => '', 'id' => 'form-' . $type, 'compact' => false ) );
	$labels  = array(
		'quote'    => __( 'Send quote request', 'aria' ),
		'sample'   => __( 'Request sample', 'aria' ),
		'contact'  => __( 'Send message', 'aria' ),
		'tracking' => __( 'Check order status', 'aria' ),
	);
	$action  = esc_url( home_url( '/?aria-inquiry=1' ) );
	$sent    = isset( $_GET['sent'] ) && $type === sanitize_key( wp_unslash( $_GET['sent'] ) ); // phpcs:ignore
	$product = $args['product'];
	$pre     = function_exists( 'aria_customer_prefill' ) ? aria_customer_prefill() : array();
	$v       = function ( $k ) use ( $pre ) {
		return isset( $pre[ $k ] ) ? ' value="' . esc_attr( $pre[ $k ] ) . '"' : '';
	};
	ob_start();
	?>
	<form class="form form--<?php echo esc_attr( $type ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" action="<?php echo $action; ?>" method="post" data-ajax-form novalidate>
		<input type="hidden" name="aria_inquiry" value="1">
		<input type="hidden" name="type" value="<?php echo esc_attr( $type ); ?>">
		<input type="hidden" name="ts" value="<?php echo esc_attr( base64_encode( (string) time() ) ); ?>">
		<input type="hidden" name="page" value="">
		<div class="hp" aria-hidden="true"><label>Leave empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
		<div class="form-grid">
			<?php if ( 'tracking' === $type ) : ?>
				<p class="field"><label for="<?php echo esc_attr( $args['id'] ); ?>-order"><?php esc_html_e( 'Order or invoice number', 'aria' ); ?></label><input id="<?php echo esc_attr( $args['id'] ); ?>-order" name="order" required autocomplete="off"></p>
				<p class="field"><label for="<?php echo esc_attr( $args['id'] ); ?>-email"><?php esc_html_e( 'Email used for the order', 'aria' ); ?></label><input id="<?php echo esc_attr( $args['id'] ); ?>-email" name="email" type="email" required autocomplete="email"></p>
			<?php else : ?>
				<p class="field"><label for="<?php echo esc_attr( $args['id'] ); ?>-name"><?php esc_html_e( 'Your name', 'aria' ); ?></label><input id="<?php echo esc_attr( $args['id'] ); ?>-name" name="name" required autocomplete="name"<?php echo $v( 'name' ); // phpcs:ignore ?>></p>
				<p class="field"><label for="<?php echo esc_attr( $args['id'] ); ?>-company"><?php esc_html_e( 'Company', 'aria' ); ?></label><input id="<?php echo esc_attr( $args['id'] ); ?>-company" name="company" <?php echo 'contact' === $type ? '' : 'required'; ?> autocomplete="organization"<?php echo $v( 'company' ); // phpcs:ignore ?>></p>
				<p class="field"><label for="<?php echo esc_attr( $args['id'] ); ?>-email"><?php esc_html_e( 'Business email', 'aria' ); ?></label><input id="<?php echo esc_attr( $args['id'] ); ?>-email" name="email" type="email" required autocomplete="email"<?php echo $v( 'email' ); // phpcs:ignore ?>></p>
				<p class="field"><label for="<?php echo esc_attr( $args['id'] ); ?>-wa"><?php esc_html_e( 'WhatsApp number', 'aria' ); ?> <span class="opt"><?php esc_html_e( '(optional)', 'aria' ); ?></span></label><input id="<?php echo esc_attr( $args['id'] ); ?>-wa" name="whatsapp" type="tel" autocomplete="tel" inputmode="tel"<?php echo $v( 'whatsapp' ); // phpcs:ignore ?>></p>
				<?php if ( 'contact' !== $type ) : ?>
					<p class="field"><label for="<?php echo esc_attr( $args['id'] ); ?>-product"><?php esc_html_e( 'Product', 'aria' ); ?></label>
						<select id="<?php echo esc_attr( $args['id'] ); ?>-product" name="product" required>
							<option value=""><?php esc_html_e( 'Choose a product', 'aria' ); ?></option>
							<?php foreach ( aria_product_choices() as $c ) : ?>
								<option<?php selected( 0 === strcasecmp( $c, $product ) ); ?>><?php echo esc_html( $c ); ?></option>
							<?php endforeach; ?>
							<option><?php esc_html_e( 'Several products', 'aria' ); ?></option>
						</select>
					</p>
				<?php endif; ?>
				<?php if ( 'quote' === $type ) : ?>
					<p class="field"><label for="<?php echo esc_attr( $args['id'] ); ?>-qty"><?php esc_html_e( 'Estimated quantity', 'aria' ); ?></label>
						<select id="<?php echo esc_attr( $args['id'] ); ?>-qty" name="quantity" required>
							<option value=""><?php esc_html_e( 'Choose a volume', 'aria' ); ?></option>
							<?php foreach ( aria_quantity_options() as $q ) : ?>
								<option><?php echo esc_html( $q ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p class="field"><label for="<?php echo esc_attr( $args['id'] ); ?>-port"><?php esc_html_e( 'Destination port', 'aria' ); ?></label><input id="<?php echo esc_attr( $args['id'] ); ?>-port" name="port" required placeholder="<?php esc_attr_e( 'e.g. Jebel Ali, Nhava Sheva, Hamburg', 'aria' ); ?>"<?php echo $v( 'port' ); // phpcs:ignore ?>></p>
				<?php elseif ( 'sample' === $type ) : ?>
					<p class="field"><label for="<?php echo esc_attr( $args['id'] ); ?>-country"><?php esc_html_e( 'Delivery country', 'aria' ); ?></label><input id="<?php echo esc_attr( $args['id'] ); ?>-country" name="port" required autocomplete="country-name"<?php echo $v( 'country' ); // phpcs:ignore ?>></p>
				<?php endif; ?>
			<?php endif; ?>
			<p class="field field--full"><label for="<?php echo esc_attr( $args['id'] ); ?>-msg"><?php echo 'contact' === $type ? esc_html__( 'Message', 'aria' ) : esc_html__( 'Specifications or notes', 'aria' ); ?> <?php if ( 'contact' !== $type ) : ?><span class="opt"><?php esc_html_e( '(optional)', 'aria' ); ?></span><?php endif; ?></label><textarea id="<?php echo esc_attr( $args['id'] ); ?>-msg" name="message" rows="<?php echo $args['compact'] ? 2 : 4; ?>" <?php echo 'contact' === $type ? 'required' : ''; ?> placeholder="<?php echo 'quote' === $type ? esc_attr__( 'Grade, packing, Brix, labelling, delivery date…', 'aria' ) : ''; ?>"></textarea></p>
		</div>
		<div class="form-foot">
			<button class="btn btn--primary" type="submit"><?php echo esc_html( $labels[ $type ] ); ?></button>
			<p class="form-note"><?php esc_html_e( 'We reply within one working day. Wholesale and export orders only.', 'aria' ); ?></p>
		</div>
		<p class="form-status" role="status" aria-live="polite"<?php echo $sent ? '' : ' hidden'; ?>><?php echo $sent ? esc_html__( 'Thank you. Your request reached our trade desk.', 'aria' ) : ''; ?></p>
	</form>
	<?php
	return ob_get_clean();
}

/*
 * Handler: works with and without JavaScript. Posts go to the front end (not wp-admin),
 * so the hidden-login plugin and the host firewall never stand between a buyer and the form.
 */
add_action( 'init', function () {
	if ( 'POST' === ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) && ! empty( $_POST['aria_inquiry'] ) ) { // phpcs:ignore
		aria_handle_inquiry();
	}
}, 20 );

function aria_handle_inquiry() {
	$is_ajax = isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && 'fetch' === $_SERVER['HTTP_X_REQUESTED_WITH'];
	$fail    = function ( $msg, $code = 400 ) use ( $is_ajax ) {
		if ( $is_ajax ) {
			wp_send_json_error( array( 'message' => $msg ), $code );
		}
		wp_die( esc_html( $msg ), '', array( 'response' => $code, 'back_link' => true ) );
	};

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- public form, guarded by honeypot, timing and rate limit.
	$f = array();
	foreach ( array( 'type', 'name', 'company', 'email', 'whatsapp', 'product', 'quantity', 'port', 'message', 'order', 'page', 'website', 'ts' ) as $k ) {
		$f[ $k ] = isset( $_POST[ $k ] ) ? wp_unslash( $_POST[ $k ] ) : '';
	}
	// phpcs:enable

	$type = in_array( $f['type'], array( 'quote', 'sample', 'contact', 'tracking' ), true ) ? $f['type'] : 'quote';

	// Bots: honeypot filled, or submitted faster than 3 seconds.
	$started = (int) base64_decode( (string) $f['ts'] );
	if ( '' !== trim( $f['website'] ) || ( $started && time() - $started < 3 ) ) {
		$fail( __( 'Your request could not be sent. Please try again in a few seconds.', 'aria' ) );
	}

	// Rate limit: 6 submissions per IP per hour.
	$ip  = function_exists( 'aria_client_ip' ) ? aria_client_ip() : ( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
	$key = 'aria_rl_' . md5( $ip );
	$hit = (int) get_transient( $key );
	if ( $hit >= 6 ) {
		$fail( __( 'Too many requests from your network. Please message us on WhatsApp instead.', 'aria' ), 429 );
	}

	$email = sanitize_email( $f['email'] );
	if ( ! is_email( $email ) ) {
		$fail( __( 'Enter a valid email address so we can reply.', 'aria' ) );
	}
	$clean = array(
		'Name'        => sanitize_text_field( $f['name'] ),
		'Company'     => sanitize_text_field( $f['company'] ),
		'Email'       => $email,
		'WhatsApp'    => sanitize_text_field( $f['whatsapp'] ),
		'Product'     => sanitize_text_field( $f['product'] ),
		'Quantity'    => sanitize_text_field( $f['quantity'] ),
		'Port / country' => sanitize_text_field( $f['port'] ),
		'Order no.'   => sanitize_text_field( $f['order'] ),
		'Message'     => sanitize_textarea_field( $f['message'] ),
		'Sent from'   => esc_url_raw( $f['page'] ),
	);
	$required = array(
		'quote'    => array( 'Name', 'Company', 'Product', 'Quantity', 'Port / country' ),
		'sample'   => array( 'Name', 'Company', 'Product', 'Port / country' ),
		'contact'  => array( 'Name', 'Message' ),
		'tracking' => array( 'Order no.' ),
	);
	foreach ( $required[ $type ] as $r ) {
		if ( '' === $clean[ $r ] ) {
			/* translators: %s field name */
			$fail( sprintf( __( 'Please fill in: %s.', 'aria' ), $r ) );
		}
	}
	$clean = array_filter( $clean, 'strlen' );

	$titles = array( 'quote' => 'Quote request', 'sample' => 'Sample request', 'contact' => 'Message', 'tracking' => 'Order status' );
	$who    = isset( $clean['Company'] ) ? $clean['Company'] : ( isset( $clean['Name'] ) ? $clean['Name'] : $email );
	$title  = $titles[ $type ] . ': ' . $who . ( isset( $clean['Product'] ) ? ' — ' . $clean['Product'] : '' );

	$lead_id = wp_insert_post( array(
		'post_type'   => 'aria_lead',
		'post_status' => 'private',
		'post_title'  => $title,
	) );
	if ( $lead_id && ! is_wp_error( $lead_id ) ) {
		update_post_meta( $lead_id, '_aria_lead', $clean );
		update_post_meta( $lead_id, '_aria_lead_type', $type );
		update_post_meta( $lead_id, '_aria_lead_email', $email );
		if ( is_user_logged_in() ) {
			update_post_meta( $lead_id, '_aria_user', get_current_user_id() );
		}
	}

	$body = '';
	foreach ( $clean as $k => $v ) {
		$body .= $k . ': ' . $v . "\n";
	}
	$to = aria_opt( 'leads_email' ) ? aria_opt( 'leads_email' ) : get_option( 'admin_email' );
	wp_mail( $to, '[Aria Foods] ' . $title, $body, array( 'Reply-To: ' . ( isset( $clean['Name'] ) ? $clean['Name'] : '' ) . ' <' . $email . '>' ) );

	set_transient( $key, $hit + 1, HOUR_IN_SECONDS );

	$ok = 'tracking' === $type
		? __( 'Thank you. Our export team will send the shipment status to your email.', 'aria' )
		: __( 'Thank you. Your request reached our trade desk; we reply within one working day.', 'aria' );
	if ( $is_ajax ) {
		wp_send_json_success( array( 'message' => $ok ) );
	}
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	wp_safe_redirect( add_query_arg( 'sent', $type, $back ) . '#form-' . $type );
	exit;
}

/* Admin: show the inquiry details on the edit screen and in the list. */
add_action( 'add_meta_boxes_aria_lead', function () {
	add_meta_box( 'aria_lead_box', __( 'Inquiry details', 'aria' ), function ( $post ) {
		$data = get_post_meta( $post->ID, '_aria_lead', true );
		if ( ! is_array( $data ) ) {
			return;
		}
		echo '<table class="widefat striped"><tbody>';
		foreach ( $data as $k => $v ) {
			$val = 'Email' === $k ? '<a href="mailto:' . esc_attr( $v ) . '">' . esc_html( $v ) . '</a>' : ( 'WhatsApp' === $k ? '<a href="https://wa.me/' . esc_attr( aria_digits( $v ) ) . '" target="_blank">' . esc_html( $v ) . '</a>' : nl2br( esc_html( $v ) ) );
			echo '<tr><th style="width:160px">' . esc_html( $k ) . '</th><td>' . $val . '</td></tr>'; // phpcs:ignore
		}
		echo '</tbody></table><p>' . esc_html( get_the_date( 'Y-m-d H:i', $post ) ) . '</p>';
		$status = get_post_meta( $post->ID, '_aria_lead_status', true );
		wp_nonce_field( 'aria_lead_status', 'aria_lead_nonce' );
		echo '<p><label for="aria_lead_status"><strong>' . esc_html__( 'Status shown to the customer', 'aria' ) . '</strong></label><br><select id="aria_lead_status" name="aria_lead_status">';
		foreach ( aria_lead_statuses() as $st ) {
			echo '<option' . selected( $status, $st, false ) . '>' . esc_html( $st ) . '</option>';
		}
		echo '</select></p>';
		$uid = (int) get_post_meta( $post->ID, '_aria_user', true );
		if ( $uid ) {
			echo '<p>' . esc_html__( 'Customer account:', 'aria' ) . ' <a href="' . esc_url( get_edit_user_link( $uid ) ) . '">' . esc_html( get_the_author_meta( 'display_name', $uid ) ) . '</a></p>';
		}
	}, 'aria_lead', 'normal', 'high' );
} );
add_filter( 'manage_aria_lead_posts_columns', function ( $cols ) {
	return array( 'cb' => $cols['cb'], 'title' => __( 'Inquiry', 'aria' ), 'aria_email' => __( 'Email', 'aria' ), 'aria_qty' => __( 'Quantity', 'aria' ), 'date' => $cols['date'] );
} );
add_action( 'manage_aria_lead_posts_custom_column', function ( $col, $id ) {
	$d = get_post_meta( $id, '_aria_lead', true );
	if ( 'aria_email' === $col && ! empty( $d['Email'] ) ) {
		echo '<a href="mailto:' . esc_attr( $d['Email'] ) . '">' . esc_html( $d['Email'] ) . '</a>';
	}
	if ( 'aria_qty' === $col && ! empty( $d['Quantity'] ) ) {
		echo esc_html( $d['Quantity'] );
	}
}, 10, 2 );

function aria_lead_statuses() {
	return array( 'Received', 'Quoted', 'Sample sent', 'Order confirmed', 'In production', 'Shipped', 'Closed' );
}
add_action( 'save_post_aria_lead', function ( $post_id ) {
	if ( ! isset( $_POST['aria_lead_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aria_lead_nonce'] ) ), 'aria_lead_status' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$st = sanitize_text_field( wp_unslash( $_POST['aria_lead_status'] ?? '' ) );
	if ( in_array( $st, aria_lead_statuses(), true ) ) {
		update_post_meta( $post_id, '_aria_lead_status', $st );
	}
} );
