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
		<input type="hidden" name="ts" value="<?php echo esc_attr( aria_form_token() ); ?>">
		<input type="hidden" name="human" value="">
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
					<p class="field"><label for="<?php echo esc_attr( $args['id'] ); ?>-qty"><?php esc_html_e( 'Quantity', 'aria' ); ?> <span class="opt"><?php esc_html_e( '(min. one 20 ft container)', 'aria' ); ?></span></label>
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
			<?php if ( aria_turnstile_keys() ) : ?>
				<div class="field field--full cf-box" data-turnstile="<?php echo esc_attr( aria_turnstile_site() ); ?>"></div>
			<?php endif; ?>
			<button class="btn btn--primary" type="submit"><?php echo esc_html( $labels[ $type ] ); ?></button>
			<p class="form-note"><?php echo 'quote' === $type ? esc_html__( 'We reply within one working day. Full containers only – minimum order one 20 ft container.', 'aria' ) : esc_html__( 'We reply within one working day. Wholesale and export orders only.', 'aria' ); ?></p>
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

	// Bots: honeypot filled, missing or forged form token, sent too fast, or no human interaction (JS).
	$started = aria_form_token_time( $f['ts'] );
	if ( '' !== trim( $f['website'] ) || ! $started || time() - $started < 4 || time() - $started > 3 * DAY_IN_SECONDS
		|| ! hash_equals( aria_human_value( $f['ts'] ), (string) ( isset( $_POST['human'] ) ? wp_unslash( $_POST['human'] ) : '' ) ) ) { // phpcs:ignore
		$fail( __( 'Your request could not be sent. Please reload the page and try again.', 'aria' ) );
	}
	// Cloudflare Turnstile, when keys are set.
	if ( aria_turnstile_keys() && ! aria_turnstile_ok( isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '' ) ) { // phpcs:ignore
		$fail( __( 'Please complete the security check and send again.', 'aria' ) );
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

	// Content checks: spam is stored for review but not e-mailed, and the sender sees a normal reply.
	$spam = aria_spam_reason( $clean );

	$titles = array( 'quote' => 'Quote request', 'sample' => 'Sample request', 'contact' => 'Message', 'tracking' => 'Order status' );
	$who    = isset( $clean['Company'] ) ? $clean['Company'] : ( isset( $clean['Name'] ) ? $clean['Name'] : $email );
	$title  = $titles[ $type ] . ': ' . $who . ( isset( $clean['Product'] ) ? ' — ' . $clean['Product'] : '' );

	$lead_id = wp_insert_post( array(
		'post_type'   => 'aria_lead',
		'post_status' => 'private',
		'post_title'  => ( $spam ? '[Spam] ' : '' ) . $title,
	) );
	if ( $spam && $lead_id && ! is_wp_error( $lead_id ) ) {
		update_post_meta( $lead_id, '_aria_spam', $spam );
	}
	if ( $lead_id && ! is_wp_error( $lead_id ) ) {
		update_post_meta( $lead_id, '_aria_lead', $clean );
		update_post_meta( $lead_id, '_aria_lead_type', $type );
		update_post_meta( $lead_id, '_aria_lead_email', $email );
		if ( is_user_logged_in() ) {
			update_post_meta( $lead_id, '_aria_user', get_current_user_id() );
		}
		update_post_meta( $lead_id, '_aria_lead_status', 'Received' );
		if ( ! $spam ) {
			do_action( 'aria_new_lead', $lead_id );
		}
	}

	if ( ! $spam && $lead_id && ! is_wp_error( $lead_id ) ) {
		// Send after the visitor has the reply, so a slow mail server never delays the form.
		register_shutdown_function( function () use ( $lead_id ) {
			if ( function_exists( 'fastcgi_finish_request' ) ) {
				fastcgi_finish_request();
			}
			aria_lead_send_emails( $lead_id );
		} );
	}

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

/* ---------- Spam protection ---------- */

/** Signed timestamp: "time.signature". Pages can be cached; the token stays valid for 3 days. */
function aria_form_token() {
	$t = (string) time();
	return $t . '.' . substr( hash_hmac( 'sha256', $t, wp_salt( 'nonce' ) ), 0, 16 );
}

function aria_form_token_time( $token ) {
	$p = explode( '.', (string) $token );
	if ( 2 !== count( $p ) || ! ctype_digit( $p[0] ) ) {
		return 0;
	}
	return hash_equals( substr( hash_hmac( 'sha256', $p[0], wp_salt( 'nonce' ) ), 0, 16 ), $p[1] ) ? (int) $p[0] : 0;
}

/** Value the page script writes once a person types, taps or clicks in the form (must match assets/js/main.js). */
function aria_human_value( $token ) {
	$p = explode( '.', (string) $token );
	return isset( $p[1] ) ? strrev( $p[1] ) . 'h' : 'x';
}

function aria_turnstile_site() {
	$s = (array) get_option( 'aria_mail', array() );
	return isset( $s['ts_site'] ) ? (string) $s['ts_site'] : '';
}

function aria_turnstile_secret() {
	if ( defined( 'ARIA_TURNSTILE_SECRET' ) && ARIA_TURNSTILE_SECRET ) {
		return ARIA_TURNSTILE_SECRET;
	}
	$s = (array) get_option( 'aria_mail', array() );
	return isset( $s['ts_secret'] ) ? (string) $s['ts_secret'] : '';
}

function aria_turnstile_keys() {
	return aria_turnstile_site() && aria_turnstile_secret();
}

function aria_turnstile_ok( $token ) {
	if ( '' === $token ) {
		return false;
	}
	$res = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', array(
		'timeout' => 8,
		'body'    => array(
			'secret'   => aria_turnstile_secret(),
			'response' => $token,
			'remoteip' => function_exists( 'aria_client_ip' ) ? aria_client_ip() : '',
		),
	) );
	if ( is_wp_error( $res ) ) {
		return true; // Cloudflare unreachable: do not lose real inquiries; other checks still apply.
	}
	$j = json_decode( wp_remote_retrieve_body( $res ), true );
	return ! empty( $j['success'] );
}

/** Why an inquiry looks like spam, or '' when it looks genuine. */
function aria_spam_reason( $c ) {
	$text  = implode( ' ', $c );
	$name  = ( isset( $c['Name'] ) ? $c['Name'] : '' ) . ' ' . ( isset( $c['Company'] ) ? $c['Company'] : '' );
	$msg   = isset( $c['Message'] ) ? $c['Message'] : '';
	if ( preg_match( '~https?://|www\.|\.(com|ru|xyz|top|io|net)\b/~i', $name ) ) {
		return 'link in name or company';
	}
	if ( preg_match_all( '~https?://|www\.~i', $msg ) > 1 ) {
		return 'several links in message';
	}
	if ( preg_match( '~\[url|<a\s|href=~i', $text ) ) {
		return 'html or bbcode links';
	}
	if ( preg_match( '~\b(seo|backlinks?|casino|betting|crypto|bitcoin|forex|viagra|cialis|porn|escort|payday|loan offer|guest post|rank(ing)? your (site|website)|web ?design services|digital marketing services|app development services|lead generation services|increase your (traffic|sales))\b~i', $text ) ) {
		return 'spam keywords';
	}
	if ( isset( $c['Email'] ) && preg_match( '~@(mailinator|guerrillamail|10minutemail|tempmail|temp-mail|yopmail|sharklasers|trashmail|getnada|dispostable)\.~i', $c['Email'] ) ) {
		return 'disposable email';
	}
	return '';
}
