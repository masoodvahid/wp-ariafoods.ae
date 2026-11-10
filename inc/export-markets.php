<?php
/**
 * Export market landing pages: /export/ (hub) and /export/{country}/.
 * Structured facts per market live here; the long text is the page content (editable in WordPress).
 * New markets: add an entry to aria_export_markets() — the page is created on the next wp-admin visit.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

/** Market facts. Keys are page slugs under /export/. */
function aria_export_markets() {
	static $m = null;
	if ( null === $m ) {
		$m = json_decode( (string) file_get_contents( __DIR__ . '/export-markets.json' ), true );
		$m = is_array( $m ) ? $m : array();
		uasort( $m, function ( $x, $y ) {
			return ( isset( $x['order'] ) ? $x['order'] : 99 ) - ( isset( $y['order'] ) ? $y['order'] : 99 );
		} );
	}
	return $m;
}

/* Create /export/ and each market page once (content stays editable afterwards). */
add_action( 'admin_init', function () {
	if ( ! current_user_can( 'publish_pages' ) ) {
		return;
	}
	$markets = aria_export_markets();
	$done    = (array) get_option( 'aria_export_pages', array() );
	if ( ! array_diff( array_merge( array( 'export' ), array_keys( $markets ) ), $done ) ) {
		return;
	}
	$hub = get_page_by_path( 'export' );
	if ( ! $hub ) {
		$hub_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => 'export',
			'post_title'   => 'Export markets',
			'post_excerpt' => 'Full-container supply of Iranian dates from our Dubai warehouse to importers in India and other markets.',
			'post_content' => '<!-- wp:paragraph --><p>Aria Foods Dubai supplies Iranian dates and date ingredients by the container to importers, wholesalers and food manufacturers. Stock is held in our Dubai warehouse and invoiced by our UAE company, so orders are loaded and shipped within days. Choose your market for ports, varieties, documents and shipping times.</p><!-- /wp:paragraph -->',
		) );
		update_post_meta( $hub_id, '_aria_seo_title', 'Export Iranian Dates by the Container from Dubai | Aria Foods' );
		update_post_meta( $hub_id, '_aria_seo_desc', 'Iranian dates in 20 ft and 40 ft containers from our Dubai warehouse: country guides with ports, varieties, documents and shipping times for importers.' );
	} else {
		$hub_id = $hub->ID;
		// Hub text written when only India existed: widen it once.
		if ( 0 === strpos( (string) $hub->post_excerpt, 'Full-container supply of Iranian dates from our Dubai warehouse to importers in India and other markets' ) ) {
			wp_update_post( array( 'ID' => $hub_id, 'post_excerpt' => 'Full-container supply of Iranian dates from our Dubai warehouse to importers in South Asia, the Middle East, Central Asia, South-East Asia, Europe and North Africa.' ) );
		}
	}
	$done[] = 'export';
	foreach ( $markets as $slug => $m ) {
		if ( in_array( $slug, $done, true ) ) {
			continue;
		}
		if ( ! get_page_by_path( 'export/' . $slug ) ) {
			$id = wp_insert_post( array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_parent'  => $hub_id,
				'menu_order'   => isset( $m['order'] ) ? (int) $m['order'] : 0,
				'post_name'    => $slug,
				'post_title'   => $m['title'],
				'post_excerpt' => $m['lead'],
				'post_content' => aria_export_default_content( $slug ),
			) );
			update_post_meta( $id, '_aria_seo_title', $m['seo_title'] );
			update_post_meta( $id, '_aria_seo_desc', $m['seo_desc'] );
			update_post_meta( $id, '_aria_faq', aria_export_default_faq( $slug ) );
		}
		$done[] = $slug;
	}
	update_option( 'aria_export_pages', array_values( array_unique( $done ) ), false );
} );

/** Starting text for a market page (edited later in WordPress). */
function aria_export_default_content( $slug ) {
	if ( 'india' === $slug ) {
		return '<!-- wp:heading --><h2>Why Indian importers buy Iranian dates through Dubai</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>India is the largest buyer of Iranian dates, and most of that trade passes through the Gulf. Buying through Aria Foods Dubai keeps the quality of Iranian origin while removing the usual friction of direct shipments: the dates are already graded, packed and stored in our Dubai warehouse, the contract and invoice are issued by our UAE company, and payment goes through UAE banks in US dollars or dirhams. When your payment arrives we book the next sailing and load your container from stock.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Built for container buyers</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>We work with importers, wholesalers, repackers and food manufacturers who buy one container or more per order. A 20 ft container carries up to about 15 tonnes of packed dates and a 40 ft container 25 tonnes or more, depending on variety and packing. Mazafati (Kimia) travels in reefer containers to keep it soft; Piarom, Zahedi, Sayer, date paste and date syrup travel in dry containers. Mixed containers with two or three varieties are possible for regular buyers.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Packing for the Indian market</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Standard export packing is 5 kg and 10 kg cartons for wholesale markets such as Khari Baoli in Delhi and the dry-fruit market in Vashi, Navi Mumbai. For retail chains and brands we pack 250 g to 1 kg consumer packs under your own label, with labelling prepared to FSSAI requirements so the cargo can go straight to distribution after clearance.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Price and payment</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>We quote CIF your Indian port, or FOB Jebel Ali if you arrange your own freight. Prices move with the harvest and the season, so we send a written offer for each inquiry, valid for a set number of days. Payment is by bank transfer (advance or against documents) or by letter of credit through a UAE bank. Tell us the variety, grade, packing, quantity and port, and we reply within one working day.</p><!-- /wp:paragraph -->';
	}
	$markets = aria_export_markets();
	$out     = '';
	if ( ! empty( $markets[ $slug ]['content'] ) ) {
		foreach ( $markets[ $slug ]['content'] as $sec ) {
			$out .= '<!-- wp:heading --><h2>' . esc_html( $sec[0] ) . '</h2><!-- /wp:heading -->' . "\n";
			$out .= '<!-- wp:paragraph --><p>' . esc_html( $sec[1] ) . '</p><!-- /wp:paragraph -->' . "\n";
		}
	}
	return $out;
}

function aria_export_default_faq( $slug ) {
	if ( 'india' === $slug ) {
		return "Q: What is the minimum order for India?\nA: One 20 ft container per order. A 20 ft container carries up to about 15 tonnes of packed dates and a 40 ft container 25 tonnes or more, depending on the variety and packing.\nQ: How fast can you ship to India?\nA: Orders are loaded from our Dubai warehouse, usually within a few working days of payment, and the sailing from Jebel Ali to the west coast of India is usually less than a week.\nQ: Which Indian ports do you ship to?\nA: Nhava Sheva (JNPT), Mundra, Chennai and Kolkata, and other ports on request. Inland destinations such as Delhi can be reached by rail through an inland container depot.\nQ: How do I pay?\nA: Our UAE company issues the contract and invoice. You pay by bank transfer in US dollars or dirhams, or by letter of credit through a UAE bank.\nQ: Are the dates really from Iran?\nA: Yes. Every shipment carries a certificate of origin stating Iran, together with the phytosanitary certificate, health certificate, invoice, packing list and bill of lading.\nQ: Can you pack under our own brand for the Indian market?\nA: Yes. We pack consumer packs from 250 g to 1 kg under your label, with labelling prepared to FSSAI requirements.\nQ: Which dates sell best in India?\nA: Mazafati (sold as Kimia) is the best-selling Iranian date, especially before Ramadan. Piarom sells well for gifting, and Zahedi and Sayer for wholesale markets and processing.";
	}
	$markets = aria_export_markets();
	return isset( $markets[ $slug ]['faq'] ) ? (string) $markets[ $slug ]['faq'] : '';
}

/**
 * Facts for a market page: defaults from aria_export_markets() (by slug), overridden by the
 * page's own "Export market facts" box (JSON). A child page of /export/ with only the box
 * filled in also works, so new countries need no theme update.
 */
function aria_export_market( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$parent  = wp_get_post_parent_id( $post_id );
	if ( ! $parent || 'export' !== get_post_field( 'post_name', $parent ) ) {
		return null;
	}
	$slug = get_post_field( 'post_name', $post_id );
	$base = aria_export_markets();
	$m    = isset( $base[ $slug ] ) ? $base[ $slug ] : array();
	$own  = json_decode( (string) get_post_meta( $post_id, '_aria_market', true ), true );
	if ( is_array( $own ) ) {
		$m = array_merge( $m, $own );
	}
	if ( empty( $m['country'] ) ) {
		return null;
	}
	return wp_parse_args( $m, array(
		'demonym'   => $m['country'],
		'h1'        => get_the_title( $post_id ),
		'lead'      => get_post_field( 'post_excerpt', $post_id ),
		'ports'     => array( array( $m['country'], '' ) ),
		'transit'   => 'Short sailing from Jebel Ali – ask us for the current schedule to your port.',
		'season'    => array(),
		'varieties' => array(),
		'docs'      => array( 'Certificate of origin (Iran)', 'Phytosanitary certificate', 'Health certificate', 'Commercial invoice from our UAE company', 'Packing list and bill of lading' ),
		'buyer'     => 'Your customs broker clears the cargo; we prepare the full document set before loading.',
		'flag'          => array(),
		'emblem'        => '',
		'emblem_color'  => '#FFFFFF',
		'local'         => array(),
		'iso'           => '',
		'mode'          => 'sea',
		'transit_short' => '',
		'terms'         => sprintf( 'CIF %s or FOB Jebel Ali', $m['country'] ),
	) );
}

/** Decorative flag behind the hero (colour bands, optional emblem). */
function aria_export_flag( $m ) {
	if ( empty( $m['flag'] ) || ! is_array( $m['flag'] ) ) {
		return '';
	}
	$bands = '';
	foreach ( array_values( $m['flag'] ) as $c ) {
		if ( preg_match( '/^#[0-9a-f]{3,8}$/i', $c ) ) {
			$bands .= '<i style="background:' . esc_attr( $c ) . '"></i>';
		}
	}
	$emblem = '';
	if ( 'chakra' === $m['emblem'] ) {
		$sp = '';
		for ( $k = 0; $k < 24; $k++ ) {
			$a   = deg2rad( $k * 15 );
			$sp .= sprintf( '<line x1="50" y1="50" x2="%.2f" y2="%.2f"/>', 50 + 40 * cos( $a ), 50 + 40 * sin( $a ) );
		}
		$emblem = '<svg class="mk-flag-emblem" viewBox="0 0 100 100" aria-hidden="true"><g fill="none" stroke="#0a3a8c" stroke-width="2.2"><circle cx="50" cy="50" r="42"/>' . $sp . '</g><circle cx="50" cy="50" r="7" fill="#0a3a8c"/></svg>';
	}
	$col = preg_match( '/^#[0-9a-f]{3,8}$/i', (string) $m['emblem_color'] ) ? $m['emblem_color'] : '#FFFFFF';
	$star = function ( $cx, $cy, $r ) {
		$pts = array();
		for ( $k = 0; $k < 10; $k++ ) {
			$a     = deg2rad( -90 + $k * 36 );
			$rr    = $k % 2 ? $r * 0.4 : $r;
			$pts[] = sprintf( '%.2f,%.2f', $cx + $rr * cos( $a ), $cy + $rr * sin( $a ) );
		}
		return implode( ' ', $pts );
	};
	if ( 'crescent' === $m['emblem'] ) {
		$emblem = '<svg class="mk-flag-emblem" viewBox="0 0 100 100" aria-hidden="true"><mask id="mk-cres"><rect width="100" height="100" fill="#fff"/><circle cx="56" cy="50" r="30" fill="#000"/></mask><circle cx="45" cy="50" r="36" fill="' . esc_attr( $col ) . '" mask="url(#mk-cres)"/><polygon fill="' . esc_attr( $col ) . '" points="' . $star( 74, 50, 13 ) . '"/></svg>';
	} elseif ( 'star' === $m['emblem'] ) {
		$emblem = '<svg class="mk-flag-emblem" viewBox="0 0 100 100" aria-hidden="true"><polygon fill="none" stroke="' . esc_attr( $col ) . '" stroke-width="4" stroke-linejoin="round" points="' . $star( 50, 54, 42 ) . '"/></svg>';
	} elseif ( 'disc' === $m['emblem'] ) {
		$emblem = '<svg class="mk-flag-emblem" viewBox="0 0 100 100" aria-hidden="true"><circle cx="50" cy="50" r="40" fill="' . esc_attr( $col ) . '"/></svg>';
	} elseif ( 'sun' === $m['emblem'] ) {
		$rays = '';
		for ( $k = 0; $k < 32; $k++ ) {
			$a     = deg2rad( $k * 11.25 );
			$rays .= sprintf( '<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f"/>', 50 + 26 * cos( $a ), 50 + 26 * sin( $a ), 50 + 44 * cos( $a ), 50 + 44 * sin( $a ) );
		}
		$emblem = '<svg class="mk-flag-emblem" viewBox="0 0 100 100" aria-hidden="true"><g stroke="' . esc_attr( $col ) . '" stroke-width="2.4" stroke-linecap="round">' . $rays . '</g><circle cx="50" cy="50" r="20" fill="' . esc_attr( $col ) . '"/></svg>';
	}
	return '<div class="mk-flag" aria-hidden="true"><div class="mk-flag-bands">' . $bands . '</div>' . $emblem . '</div>';
}

/* "Export market facts" box on market pages: edit everything in WordPress, no theme update. */
add_action( 'add_meta_boxes_page', function ( $post ) {
	$parent = wp_get_post_parent_id( $post->ID );
	if ( $parent && 'export' === get_post_field( 'post_name', $parent ) ) {
		add_meta_box( 'aria_market', __( 'Export market facts (JSON)', 'aria' ), 'aria_box_market', 'page', 'normal', 'default' );
	}
} );

function aria_box_market( $post ) {
	wp_nonce_field( 'aria_market', 'aria_market_nonce' );
	$m = aria_export_market( $post->ID );
	if ( ! $m ) {
		$m = array( 'country' => '', 'demonym' => '', 'h1' => '', 'lead' => '', 'flag' => array(), 'ports' => array( array( 'Port', 'Region served' ) ), 'transit' => '', 'varieties' => array( array( 'product-slug', 'Name', 'Why it sells', 'Dry container' ) ), 'docs' => array(), 'buyer' => '' );
	}
	unset( $m['seo_title'], $m['seo_desc'], $m['title'] );
	echo '<p>' . esc_html__( 'Country, ports, varieties, documents and flag colours for this page. Keep valid JSON; invalid JSON is not saved.', 'aria' ) . '</p>';
	echo '<textarea name="aria_market" rows="22" style="width:100%;font-family:monospace;font-size:12px">' . esc_textarea( wp_json_encode( $m, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ) . '</textarea>';
}

add_action( 'save_post_page', function ( $post_id ) {
	if ( ! isset( $_POST['aria_market_nonce'], $_POST['aria_market'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aria_market_nonce'] ) ), 'aria_market' ) || ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}
	$raw  = wp_unslash( $_POST['aria_market'] ); // phpcs:ignore -- validated as JSON below.
	$data = json_decode( $raw, true );
	if ( is_array( $data ) && ! empty( $data['country'] ) ) {
		array_walk_recursive( $data, function ( &$v ) {
			$v = is_string( $v ) ? sanitize_text_field( $v ) : $v;
		} );
		update_post_meta( $post_id, '_aria_market', wp_slash( wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ) );
	}
} );

/** Market slug of the current page, or ''. */
function aria_export_market_slug( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$parent  = wp_get_post_parent_id( $post_id );
	if ( ! $parent || 'export' !== get_post_field( 'post_name', $parent ) ) {
		return '';
	}
	return aria_export_market( $post_id ) ? get_post_field( 'post_name', $post_id ) : '';
}
