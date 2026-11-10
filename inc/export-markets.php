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
	return array(
		'india' => array(
			'country'   => 'India',
			'flag'      => array( '#FF9933', '#FFFFFF', '#138808' ),
			'emblem'    => 'chakra',
			'demonym'   => 'Indian',
			'title'     => 'Iranian Dates Supplier for India',
			'h1'        => 'Iranian dates for Indian importers – full containers from our Dubai warehouse',
			'lead'      => 'Mazafati (Kimia), Piarom, Zahedi and Sayer dates in 20 ft and 40 ft containers for wholesalers, importers and food manufacturers in India. Invoiced by our UAE company and loaded from Dubai stock, so your cargo sails within days, not weeks.',
			'seo_title' => 'Iranian Dates Supplier for India – FCL from Dubai | Aria Foods',
			'seo_desc'  => 'Wholesale Iranian dates for Indian importers: Kimia/Mazafati, Piarom, Zahedi and Sayer in full containers, shipped CIF Nhava Sheva, Mundra or Chennai from Dubai stock.',
			'ports'     => array(
				array( 'Nhava Sheva (JNPT)', 'Mumbai, Pune and western India' ),
				array( 'Mundra', 'Gujarat, Rajasthan, and Delhi NCR by rail to ICD' ),
				array( 'Chennai', 'Tamil Nadu, Karnataka and southern India' ),
				array( 'Kolkata', 'Eastern India and onward to the north-east' ),
			),
			'transit'   => 'Jebel Ali to the west coast of India is a short sea route – usually less than a week of sailing.',
			'season'    => array(
				'title' => 'Ramadan 2027 starts around 8 February',
				'text'  => 'Demand for Kimia and Piarom peaks in January and February. Book your containers in December so they are on your shelves before the first week of Ramadan – later bookings compete for the same Dubai stock.',
			),
			'varieties' => array(
				array( 'mazafati-dates', 'Kimia / Mazafati', 'Soft, dark and the best-selling Iranian date in India – the Ramadan staple for retail and wholesale markets.', 'Reefer container' ),
				array( 'piarom-dates', 'Piarom (Maryam)', 'Semi-dry premium date for gift boxes, Diwali and Eid hampers, and high-end dry-fruit shops.', 'Dry container' ),
				array( 'zahedi-dates', 'Zahedi', 'Dry, golden and economical – for wholesale markets, repackers and processing.', 'Dry container' ),
				array( 'sayer-dates', 'Sayer (Stamaran)', 'Semi-soft amber date for mass-market packs and paste production.', 'Dry container' ),
				array( 'date-paste', 'Date paste', 'Pitted paste in bulk blocks for bakeries, sweets and food factories.', 'Dry container' ),
				array( 'date-syrup', 'Date syrup', 'Brix 72–78 syrup in drums and 1,400 kg IBC tanks for food and beverage plants.', 'Dry container' ),
			),
			'docs'      => array(
				'Certificate of origin (Iran)',
				'Phytosanitary certificate',
				'Health certificate and lab analysis',
				'Commercial invoice from our UAE company',
				'Packing list and bill of lading',
				'Labels to FSSAI rules on request (printed at packing)',
			),
			'buyer'     => 'You need your IEC and FSSAI import licence; your customs broker clears the cargo. We prepare the full document set before loading so clearance is not held up.',
			// Section in the buyers' own language (lang = BCP-47 code, dir = ltr or rtl).
			'local'     => array(
				'lang'   => 'hi',
				'dir'    => 'ltr',
				'label'  => 'हिन्दी',
				'title'  => 'हम ईरानी खजूर के थोक निर्यातक हैं',
				'paras'  => array(
					'Aria Foods ईरानी खजूर और खजूर से बने उत्पादों का निर्यातक है। हम भारत के आयातकों, थोक विक्रेताओं और खाद्य उद्योगों को कीमिया (मज़ाफ़ती), पियारोम, ज़ाहेदी और सायर खजूर, साथ ही खजूर का पेस्ट और खजूर का सिरप थोक में सप्लाई करते हैं।',
					'हमारा माल दुबई के गोदाम में तैयार रहता है। अनुबंध और इनवॉइस हमारी यूएई कंपनी से जारी होते हैं, और आपका कंटेनर कुछ ही दिनों में जेबेल अली से न्हावा शेवा, मुंद्रा, चेन्नई या कोलकाता के लिए रवाना हो जाता है।',
					'अपनी पसंद की किस्म, पैकिंग, मात्रा और बंदरगाह बताइए – हम एक कार्यदिवस के भीतर लिखित कोटेशन भेजते हैं। हमारा पत्राचार अंग्रेज़ी में होता है।',
				),
				'points' => array(
					'न्यूनतम ऑर्डर: एक 20 फ़ुट कंटेनर (लगभग 15 टन)',
					'केवल थोक और निर्यात ऑर्डर – खुदरा बिक्री नहीं',
					'भुगतान: यूएई बैंकों के माध्यम से TT या साख पत्र (LC), US डॉलर या दिरहम में',
					'लोडिंग से पहले सभी दस्तावेज़ तैयार: मूल प्रमाणपत्र, फाइटोसैनिटरी और स्वास्थ्य प्रमाणपत्र',
					'ISO 22000, ISO 9001 और HACCP प्रमाणित उत्पादन',
				),
				'cta'    => 'कोटेशन का अनुरोध करें',
			),
		),
	);
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
	return '';
}

function aria_export_default_faq( $slug ) {
	if ( 'india' === $slug ) {
		return "Q: What is the minimum order for India?\nA: One 20 ft container per order. A 20 ft container carries up to about 15 tonnes of packed dates and a 40 ft container 25 tonnes or more, depending on the variety and packing.\nQ: How fast can you ship to India?\nA: Orders are loaded from our Dubai warehouse, usually within a few working days of payment, and the sailing from Jebel Ali to the west coast of India is usually less than a week.\nQ: Which Indian ports do you ship to?\nA: Nhava Sheva (JNPT), Mundra, Chennai and Kolkata, and other ports on request. Inland destinations such as Delhi can be reached by rail through an inland container depot.\nQ: How do I pay?\nA: Our UAE company issues the contract and invoice. You pay by bank transfer in US dollars or dirhams, or by letter of credit through a UAE bank.\nQ: Are the dates really from Iran?\nA: Yes. Every shipment carries a certificate of origin stating Iran, together with the phytosanitary certificate, health certificate, invoice, packing list and bill of lading.\nQ: Can you pack under our own brand for the Indian market?\nA: Yes. We pack consumer packs from 250 g to 1 kg under your label, with labelling prepared to FSSAI requirements.\nQ: Which dates sell best in India?\nA: Mazafati (sold as Kimia) is the best-selling Iranian date, especially before Ramadan. Piarom sells well for gifting, and Zahedi and Sayer for wholesale markets and processing.";
	}
	return '';
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
		'flag'      => array(),
		'emblem'    => '',
		'local'     => array(),
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
