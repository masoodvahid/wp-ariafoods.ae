<?php
/**
 * HTML e-mail templates (editable in Aria settings → Email templates) and the e-mails sent
 * for each inquiry: an alert to staff and an optional confirmation to the customer.
 *
 * Placeholders: {{name}} {{company}} {{email}} {{whatsapp}} {{product}} {{quantity}} {{port}}
 * {{message}} {{order}} {{type}} {{id}} {{date}} {{details}} (table of all fields)
 * {{site_name}} {{site_url}} {{logo_url}} {{admin_link}} {{whatsapp_link}} {{phone}} {{year}}
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

function aria_mail_tpl_defaults() {
	$css = <<<'CSS'
body{margin:0;padding:0;background:#f4efe9;font-family:Arial,Helvetica,sans-serif;color:#2b211c}
.wrap{width:100%;background:#f4efe9;padding:28px 0}
.card{max-width:600px;margin:0 auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #eadfd3}
.top{background:#1c1512;padding:22px 28px;color:#f7f2ec}
.top img{height:40px;vertical-align:middle}
.top .brand{font-size:18px;font-weight:bold;margin-left:10px;vertical-align:middle;color:#f7f2ec}
.body{padding:28px}
h1{font-size:22px;line-height:1.3;margin:0 0 8px;color:#1c1512}
p{font-size:15px;line-height:1.6;margin:0 0 14px}
.muted{color:#7a6c62;font-size:13px}
.details{width:100%;border-collapse:collapse;margin:16px 0 20px}
.details th{text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#7a6c62;padding:9px 12px;background:#f7f2ec;border-bottom:1px solid #eadfd3;width:38%;vertical-align:top}
.details td{font-size:14px;padding:9px 12px;border-bottom:1px solid #eadfd3;vertical-align:top}
.btn{display:inline-block;background:#b0592a;color:#ffffff !important;text-decoration:none;font-weight:bold;font-size:14px;padding:12px 22px;border-radius:8px}
.btn-wa{background:#1f9d55}
.foot{padding:18px 28px;background:#f7f2ec;font-size:12px;color:#7a6c62;line-height:1.6}
.foot a{color:#8e4520}
CSS;

	$admin = <<<'HTML'
<div class="wrap"><div class="card">
  <div class="top"><span class="brand">{{site_name}} · New {{type}}</span></div>
  <div class="body">
    <h1>{{company}}</h1>
    <p class="muted">{{name}} · {{date}} · #{{id}}</p>
    {{details}}
    <p>
      <a class="btn" href="mailto:{{email}}?subject=Re: your {{type}}">Reply by email</a>
      &nbsp; <a class="btn btn-wa" href="{{whatsapp_link}}">WhatsApp</a>
    </p>
    <p class="muted"><a href="{{admin_link}}">Open this inquiry in WordPress</a> to change its status.</p>
  </div>
  <div class="foot">Sent by the website form on {{site_url}}. Replying to this email goes to the customer.</div>
</div></div>
HTML;

	$customer = <<<'HTML'
<div class="wrap"><div class="card">
  <div class="top"><img src="{{logo_url}}" alt=""><span class="brand">{{site_name}}</span></div>
  <div class="body">
    <h1>Thank you, {{name}}</h1>
    <p>We have received your {{type}} and our trade desk will reply within one working day with a written offer.</p>
    {{details}}
    <p>Need an answer sooner? Message our trade desk on WhatsApp.</p>
    <p><a class="btn btn-wa" href="https://wa.me/{{phone}}">WhatsApp the trade desk</a></p>
    <p class="muted">Aria Foods supplies Iranian dates and date ingredients by the full container from our Dubai warehouse. Minimum order: one 20 ft container.</p>
  </div>
  <div class="foot">{{site_name}} · <a href="{{site_url}}">{{site_url}}</a><br>You receive this email because you sent a request on our website. Reference #{{id}}.</div>
</div></div>
HTML;

	return array(
		'css'              => $css,
		'admin_subject'    => '[Aria Foods] New {{type}}: {{company}} – {{product}}',
		'admin_html'       => $admin,
		'customer_on'      => 1,
		'customer_subject' => 'We received your {{type}} – Aria Foods',
		'customer_html'    => $customer,
	);
}

function aria_mail_tpl() {
	$saved = (array) get_option( 'aria_mail_tpl', array() );
	$def   = aria_mail_tpl_defaults();
	foreach ( $def as $k => $v ) {
		if ( ! isset( $saved[ $k ] ) || ( 'customer_on' !== $k && '' === trim( (string) $saved[ $k ] ) ) ) {
			$saved[ $k ] = $v;
		}
	}
	return $saved;
}

/** Placeholder values for an inquiry (or sample data for tests and previews). */
function aria_mail_vars( $lead_id = 0 ) {
	$types = array( 'quote' => 'quote request', 'sample' => 'sample request', 'contact' => 'message', 'tracking' => 'order status request' );
	if ( $lead_id ) {
		$d    = (array) get_post_meta( $lead_id, '_aria_lead', true );
		$type = (string) get_post_meta( $lead_id, '_aria_lead_type', true );
		$date = get_the_date( 'j M Y, H:i', $lead_id );
	} else {
		$d    = array(
			'Name'           => 'Rahul Mehta',
			'Company'        => 'Mehta Dry Fruits Pvt Ltd',
			'Email'          => 'rahul@example.com',
			'WhatsApp'       => '+91 98200 00000',
			'Product'        => 'Mazafati Dates',
			'Quantity'       => '1 × 20 ft container (about 15 ton)',
			'Port / country' => 'Nhava Sheva',
			'Message'        => "Grade 70–80 pcs/kg, 5 kg cartons.\nDelivery before mid-January.",
		);
		$type = 'quote';
		$date = wp_date( 'j M Y, H:i' );
	}
	$g    = function ( $k ) use ( $d ) {
		return isset( $d[ $k ] ) ? (string) $d[ $k ] : '';
	};
	$rows = '';
	foreach ( array( 'Name', 'Company', 'Email', 'WhatsApp', 'Product', 'Quantity', 'Port / country', 'Order no.', 'Message' ) as $k ) {
		if ( '' !== $g( $k ) ) {
			$rows .= '<tr><th>' . esc_html( $k ) . '</th><td>' . nl2br( esc_html( $g( $k ) ) ) . '</td></tr>';
		}
	}
	$logo = function_exists( 'aria_logo_url' ) ? (string) aria_logo_url() : '';
	return array(
		'name'          => $g( 'Name' ) ? $g( 'Name' ) : 'there',
		'company'       => $g( 'Company' ) ? $g( 'Company' ) : $g( 'Name' ),
		'email'         => $g( 'Email' ),
		'whatsapp'      => $g( 'WhatsApp' ),
		'product'       => $g( 'Product' ),
		'quantity'      => $g( 'Quantity' ),
		'port'          => $g( 'Port / country' ),
		'message'       => $g( 'Message' ),
		'order'         => $g( 'Order no.' ),
		'type'          => isset( $types[ $type ] ) ? $types[ $type ] : 'inquiry',
		'id'            => $lead_id ? (string) $lead_id : '1234',
		'date'          => $date,
		'site_name'     => aria_opt( 'company' ) ? aria_opt( 'company' ) : get_bloginfo( 'name' ),
		'site_url'      => home_url( '/' ),
		'logo_url'      => $logo,
		'admin_link'    => $lead_id ? admin_url( 'post.php?post=' . $lead_id . '&action=edit' ) : admin_url( 'edit.php?post_type=aria_lead' ),
		'whatsapp_link' => $g( 'WhatsApp' ) ? 'https://wa.me/' . aria_digits( $g( 'WhatsApp' ) ) : 'mailto:' . $g( 'Email' ),
		'phone'         => aria_digits( (string) aria_opt( 'whatsapp' ) ),
		'year'          => wp_date( 'Y' ),
		'__details'     => '<table class="details" role="presentation" cellspacing="0" cellpadding="0">' . $rows . '</table>',
	);
}

/** Fill placeholders: values are HTML-escaped, {{details}} is the ready table. */
function aria_mail_fill( $tpl, $vars, $html = true ) {
	return preg_replace_callback( '/\{\{\s*([a-z_]+)\s*\}\}/', function ( $m ) use ( $vars, $html ) {
		if ( 'details' === $m[1] ) {
			return $html ? $vars['__details'] : '';
		}
		if ( ! isset( $vars[ $m[1] ] ) ) {
			return $m[0];
		}
		return $html ? esc_html( $vars[ $m[1] ] ) : $vars[ $m[1] ];
	}, (string) $tpl );
}

/** Full HTML document: the template body plus the shared CSS. */
function aria_mail_document( $body, $css ) {
	if ( false !== stripos( $body, '<html' ) ) {
		return $body;
	}
	return '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>' . wp_strip_all_tags( $css ) . '</style></head><body>' . $body . '</body></html>';
}

/** Render a template by key ('admin' or 'customer') for a lead (0 = sample). */
function aria_mail_render( $key, $lead_id = 0, $tpl = null ) {
	$tpl  = $tpl ? $tpl : aria_mail_tpl();
	$vars = aria_mail_vars( $lead_id );
	return array(
		'subject' => wp_strip_all_tags( aria_mail_fill( $tpl[ $key . '_subject' ], $vars, false ) ),
		'html'    => aria_mail_document( aria_mail_fill( $tpl[ $key . '_html' ], $vars ), $tpl['css'] ),
	);
}

/** Send an HTML e-mail with a plain-text alternative. */
function aria_send_html( $to, $subject, $html, $headers = array() ) {
	$GLOBALS['aria_mail_alt'] = trim( preg_replace( "/\n{3,}/", "\n\n", wp_strip_all_tags( preg_replace( array( '~<(br|/p|/tr|/h1|/div)[^>]*>~i', '~<style.*?</style>~is' ), array( "\n", '' ), $html ) ) ) );
	$headers[] = 'Content-Type: text/html; charset=UTF-8';
	$ok = wp_mail( $to, $subject, $html, $headers );
	unset( $GLOBALS['aria_mail_alt'] );
	return $ok;
}

add_action( 'phpmailer_init', function ( $mailer ) {
	if ( ! empty( $GLOBALS['aria_mail_alt'] ) ) {
		$mailer->AltBody = html_entity_decode( $GLOBALS['aria_mail_alt'], ENT_QUOTES, 'UTF-8' );
	}
}, 30 );

/** Inquiry e-mails: staff alert (reply goes to the customer) and the customer confirmation. */
function aria_lead_send_emails( $lead_id ) {
	$vars = aria_mail_vars( $lead_id );
	$tpl  = aria_mail_tpl();
	$a    = aria_mail_render( 'admin', $lead_id, $tpl );
	$h    = array();
	if ( is_email( $vars['email'] ) ) {
		$h[] = 'Reply-To: ' . str_replace( array( '"', '<', '>', ',' ), '', $vars['name'] ) . ' <' . $vars['email'] . '>';
	}
	aria_send_html( aria_leads_recipients(), $a['subject'], $a['html'], $h );

	if ( ! empty( $tpl['customer_on'] ) && is_email( $vars['email'] ) ) {
		$c   = aria_mail_render( 'customer', $lead_id, $tpl );
		$rcp = aria_leads_recipients();
		aria_send_html( $vars['email'], $c['subject'], $c['html'], array( 'Reply-To: ' . $vars['site_name'] . ' <' . $rcp[0] . '>' ) );
	}
}
