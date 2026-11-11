<?php
/**
 * Date varieties we do not hold in Dubai but source on request.
 *
 * Each page ranks for the variety's name, says plainly that it is not in Dubai stock, offers to
 * source it, and points buyers to similar varieties that ship today (the "_aria_alternatives" list).
 * Created once in wp-admin; afterwards they are normal products, editable in WordPress.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

function aria_on_request_products() {
	$p = function ( $t ) {
		return '<!-- wp:paragraph --><p>' . $t . '</p><!-- /wp:paragraph -->' . "\n";
	};
	$h = function ( $t ) {
		return '<!-- wp:heading --><h2>' . $t . '</h2><!-- /wp:heading -->' . "\n";
	};
	$src = function ( $name ) use ( $h, $p ) {
		return $h( 'Sourcing ' . $name . ' on request' ) . $p( $name . ' is not part of our regular Dubai stock. Send us the grade, packing, volume and destination port and our team in Iran checks availability with growers and packers, then confirms price, lead time and loading date in writing. Sourced orders follow the same rules as stock orders: one 20 ft container minimum, a UAE contract and invoice, and the full export document set with the origin stated as Iran.' );
	};
	return array(
		'kharak-dates' => array(
			'title'   => 'Kharak Dates (Chhuhara)',
			'excerpt' => 'Dried Iranian dates picked at the hard khalal stage, boiled and sun-dried – sold as chhuhara or kharek in India and Pakistan. Sourced on request; Zahedi and Piarom ship today from Dubai stock.',
			'content' => $p( 'Kharak (also written kharek, and known as <strong>chhuhara</strong> or chuara in India and Pakistan) is made from dates picked at the firm khalal stage, then boiled and dried until hard and wrinkled. The result keeps for a long time without refrigeration and is a staple of the dry-fruit trade.' )
				. $h( 'Where kharak sells' ) . $p( 'In India and Pakistan chhuhara is used at weddings and religious occasions, in dry-fruit mixes and gift trays, in milk drinks and sweets, and in traditional remedies. Wholesale dry-fruit markets buy it by the container ahead of the wedding season and Ramadan.' )
				. $src( 'Kharak' )
				. $h( 'Ship today instead' ) . $p( 'If you need stock now, <a href="/product/zahedi-dates/">Zahedi</a> is the closest dry date in our Dubai warehouse and is widely used by repackers and the dry-fruit trade, and <a href="/product/piarom-dates/">Piarom</a> sells to premium dry-fruit shops. See also <a href="/export/india/">Iranian dates for India</a> and <a href="/export/pakistan/">for Pakistan</a>.' ),
			'specs'   => "Type | Dried khalal-stage date (boiled and sun-dried)\nTexture | Hard, wrinkled\nColour | Light to dark brown\nMoisture | Low – dry\nShelf life | 12+ months (dry storage)\nAvailability | Sourced on request",
			'packing' => "Bulk cartons of 10 kg\nPP bags of 20–25 kg\nPrivate-label packs on request",
			'uses'    => "Dry-fruit wholesalers\nWedding and festival trade\nSweets and milk-drink makers\nRepackers",
			'faq'     => "Q: What is the difference between kharak and chhuhara?\nA: They are the same product: dates picked at the hard khalal stage, boiled and dried. Kharak is the Persian name; chhuhara or chuara is used in India and Pakistan.\nQ: Do you have kharak in your Dubai warehouse?\nA: Not at the moment. We source it on request – send us grade, packing and volume and we confirm availability, price and lead time.\nQ: What can I buy from stock instead?\nA: Zahedi, a dry golden date, ships from Dubai stock today and suits the dry-fruit and repacking trade.",
			'seo'     => array( 'Kharak Dates (Chhuhara) – Sourced on Request | Aria Foods', 'Iranian kharak (chhuhara) dried dates for India and Pakistan, sourced on request by the container. Zahedi and Piarom ship today from our Dubai stock.' ),
			'alt'     => array( 'zahedi-dates', 'piarom-dates' ),
		),
		'medjool-dates' => array(
			'title'   => 'Medjool Dates (Iranian-grown)',
			'excerpt' => 'Large, soft, premium Medjool dates grown in southern Iran in limited volume. Sourced on request; Piarom and Mazafati ship today from Dubai stock.',
			'content' => $p( 'Medjool is the large, soft, caramel-sweet date that leads premium shelves worldwide. It is grown in southern Iran in limited volume alongside the traditional Iranian varieties, and is sold by size grade (large, jumbo, super jumbo).' )
				. $h( 'Who buys Medjool' ) . $p( 'Premium supermarkets, gift-box packers and the Ramadan and Christmas trade buy Medjool for its size and soft texture. It travels refrigerated to keep its quality.' )
				. $src( 'Iranian-grown Medjool' )
				. $h( 'Ship today instead' ) . $p( 'For premium shelves with stock available now, <a href="/product/piarom-dates/">Piarom (Maryam)</a> is Iran’s premium semi-dry date with a long shelf life, and <a href="/product/mazafati-dates/">Mazafati</a> is the soft, dark Iranian favourite. Our guide <a href="/articles/reefer-or-dry-container-shipping-dates/">reefer or dry container?</a> explains how each travels.' ),
			'specs'   => "Type | Soft, large date\nSize | Large to super jumbo grades\nColour | Amber to dark brown\nStorage | Chilled, 0–4 °C\nAvailability | Sourced on request",
			'packing' => "5 kg cartons by size grade\nGift boxes and retail packs on request",
			'uses'    => "Premium retail\nGift-box packers\nRamadan and Christmas trade",
			'faq'     => "Q: Is Medjool grown in Iran?\nA: Yes, in southern Iran, in limited volume compared with Iran’s traditional varieties.\nQ: Do you have Medjool in stock in Dubai?\nA: Not at the moment. We source it on request and confirm availability, size grade, price and lead time in writing.\nQ: What premium date can you ship today?\nA: Piarom (Maryam) and Mazafati ship today from our Dubai warehouse.",
			'seo'     => array( 'Iranian Medjool Dates – Sourced on Request | Aria Foods', 'Iranian-grown Medjool dates sourced on request by the container. Need stock now? Premium Piarom and soft Mazafati ship today from our Dubai warehouse.' ),
			'alt'     => array( 'piarom-dates', 'mazafati-dates' ),
		),
		'barhi-dates' => array(
			'title'   => 'Barhi Dates',
			'excerpt' => 'Round Barhi dates, eaten fresh and crunchy at the yellow khalal stage or soft as rutab. Seasonal and sourced on request; Mazafati and Sayer ship today from Dubai stock.',
			'content' => $p( 'Barhi (also spelt Barhee) is a round date best known in its fresh, yellow <em>khalal</em> stage, when it is crisp and mildly sweet, and later as soft amber <em>rutab</em>. In Iran it grows mainly in Khuzestan in the south-west.' )
				. $h( 'A seasonal product' ) . $p( 'Fresh Barhi is available only for a short season in late summer and needs an unbroken cold chain, so it is traded on the branch or in chilled packs. It is especially popular in the Gulf.' )
				. $src( 'Barhi' )
				. $h( 'Ship today instead' ) . $p( 'For a soft date available all year from our Dubai warehouse, choose <a href="/product/mazafati-dates/">Mazafati</a> (reefer) or semi-soft <a href="/product/sayer-dates/">Sayer</a>. Gulf buyers can see <a href="/export/saudi-arabia/">Saudi Arabia</a> and <a href="/export/oman/">Oman</a> for truck delivery from Dubai.' ),
			'specs'   => "Type | Round date, eaten at khalal (yellow) or rutab stage\nTexture | Crisp at khalal, soft at rutab\nSeason | Late summer\nStorage | Chilled\nAvailability | Seasonal – sourced on request",
			'packing' => "Chilled cartons and on-the-branch packs (seasonal)",
			'uses'    => "Fresh-produce importers\nGulf supermarkets\nSeasonal retail",
			'faq'     => "Q: When is Barhi available?\nA: Fresh Barhi has a short season in late summer. Outside the season it is not traded fresh.\nQ: Do you hold Barhi in Dubai?\nA: No. We source it on request in season and confirm availability, price and logistics in writing.\nQ: Which soft date can you ship now?\nA: Mazafati, in reefer containers, ships today from our Dubai stock.",
			'seo'     => array( 'Barhi Dates (Barhee) – Sourced on Request | Aria Foods', 'Iranian Barhi (Barhee) dates, fresh yellow khalal or rutab, sourced on request in season. Mazafati and Sayer ship all year from our Dubai warehouse.' ),
			'alt'     => array( 'mazafati-dates', 'sayer-dates' ),
		),
		'khadrawy-dates' => array(
			'title'   => 'Khadrawy Dates',
			'excerpt' => 'Soft, sweet Khadrawy (Khazravi) dates from south-west Iran. Sourced on request; Mazafati and Sayer ship today from Dubai stock.',
			'content' => $p( 'Khadrawy (Khazravi, Khadrawi) is a soft, sweet date with amber to reddish-brown skin, grown in Khuzestan in south-west Iran and across the region. It is eaten fresh and as a soft table date.' )
				. $src( 'Khadrawy' )
				. $h( 'Ship today instead' ) . $p( 'For a soft date from stock, <a href="/product/mazafati-dates/">Mazafati</a> ships in reefer containers and semi-soft <a href="/product/sayer-dates/">Sayer</a> in dry containers, both from our Dubai warehouse today.' ),
			'specs'   => "Type | Soft date\nColour | Amber to reddish brown\nStorage | Chilled for soft grades\nAvailability | Sourced on request",
			'packing' => "5 kg and 10 kg cartons\nPrivate-label packs on request",
			'uses'    => "Retail and wholesale\nRamadan trade",
			'faq'     => "Q: Do you have Khadrawy dates in stock?\nA: Not in our Dubai warehouse. We source them on request.\nQ: What is a similar date I can buy now?\nA: Mazafati (soft) and Sayer (semi-soft) ship today from Dubai stock.",
			'seo'     => array( 'Khadrawy Dates (Khazravi) – Sourced on Request | Aria Foods', 'Iranian Khadrawy (Khazravi) soft dates sourced on request by the container. Soft Mazafati and Sayer ship today from our Dubai stock.' ),
			'alt'     => array( 'mazafati-dates', 'sayer-dates' ),
		),
		'shahani-dates' => array(
			'title'   => 'Shahani Dates',
			'excerpt' => 'Shahani dates from Fars province in southern Iran. Sourced on request; Sayer and Kabkab ship today from Dubai stock.',
			'content' => $p( 'Shahani is a traditional date of Fars province in southern Iran, with thin skin and a sweet, soft to semi-soft flesh. Most of the crop is sold in Iran’s domestic market and to processors, so export volumes are arranged case by case.' )
				. $src( 'Shahani' )
				. $h( 'Ship today instead' ) . $p( 'For a similar everyday date from stock, choose semi-soft <a href="/product/sayer-dates/">Sayer</a> or <a href="/product/kabkab-dates/">Kabkab</a> from our Dubai warehouse. For processing, <a href="/product/date-paste/">date paste</a> ships today.' ),
			'specs'   => "Type | Soft to semi-soft date\nOrigin | Fars province, Iran\nAvailability | Sourced on request",
			'packing' => "Cartons and bulk packs on request",
			'uses'    => "Wholesale markets\nProcessors",
			'faq'     => "Q: Do you export Shahani dates?\nA: On request. They are not in our Dubai stock; we check availability and confirm price and lead time.\nQ: What can I buy from stock instead?\nA: Sayer and Kabkab ship today from Dubai.",
			'seo'     => array( 'Shahani Dates – Sourced on Request | Aria Foods', 'Iranian Shahani dates from Fars province, sourced on request. Need stock now? Sayer and Kabkab ship today from our Dubai warehouse.' ),
			'alt'     => array( 'sayer-dates', 'kabkab-dates' ),
		),
		'halileh-dates' => array(
			'title'   => 'Halileh Dates',
			'excerpt' => 'Halileh dates from Iran’s southern Gulf coast. Sourced on request; Sayer and Kabkab ship today from Dubai stock.',
			'content' => $p( 'Halileh is a soft date grown along Iran’s southern coast, valued locally for its sweetness. Export volumes are limited and arranged on request.' )
				. $src( 'Halileh' )
				. $h( 'Ship today instead' ) . $p( 'From our Dubai warehouse we can ship semi-soft <a href="/product/sayer-dates/">Sayer</a> or <a href="/product/kabkab-dates/">Kabkab</a> today.' ),
			'specs'   => "Type | Soft date\nOrigin | Southern Iran\nAvailability | Sourced on request",
			'packing' => "Cartons on request",
			'uses'    => "Wholesale markets\nRetail",
			'faq'     => "Q: Can you supply Halileh dates?\nA: On request – they are not in our Dubai stock.\nQ: What ships today?\nA: Sayer and Kabkab ship from our Dubai warehouse.",
			'seo'     => array( 'Halileh Dates – Sourced on Request | Aria Foods', 'Iranian Halileh dates sourced on request. Sayer and Kabkab ship today from our Dubai warehouse in full containers.' ),
			'alt'     => array( 'sayer-dates', 'kabkab-dates' ),
		),
		'deiri-dates' => array(
			'title'   => 'Deiri Dates',
			'excerpt' => 'Dark, semi-dry Deiri (Dayri) dates from south-west Iran. Sourced on request; Rabbi and Zahedi ship today from Dubai stock.',
			'content' => $p( 'Deiri (Dayri) is a dark, semi-dry date with a long, slender shape, grown in south-west Iran and southern Iraq.' )
				. $src( 'Deiri' )
				. $h( 'Ship today instead' ) . $p( 'For a dark semi-dry date from stock, <a href="/product/rabbee-dates/">Rabbi</a> is the closest match, and <a href="/product/zahedi-dates/">Zahedi</a> suits repacking and processing. Both ship today from our Dubai warehouse.' ),
			'specs'   => "Type | Semi-dry date\nColour | Dark brown\nAvailability | Sourced on request",
			'packing' => "Cartons and bulk packs on request",
			'uses'    => "Wholesale markets\nRepackers",
			'faq'     => "Q: Do you have Deiri dates in stock?\nA: Not in Dubai. We source them on request.\nQ: What is the closest date in stock?\nA: Rabbi, a dark semi-dry date, ships today from our Dubai warehouse.",
			'seo'     => array( 'Deiri Dates (Dayri) – Sourced on Request | Aria Foods', 'Iranian Deiri (Dayri) semi-dry dates sourced on request. Rabbi and Zahedi ship today from our Dubai warehouse.' ),
			'alt'     => array( 'rabbee-dates', 'zahedi-dates' ),
		),
	);
}

add_action( 'admin_init', function () {
	if ( get_option( 'aria_onreq_v1' ) || ! current_user_can( 'manage_options' ) || ! function_exists( 'aria_parse_spec_lines' ) ) {
		return;
	}
	$dates = get_term_by( 'slug', 'dates', 'product_cat' );
	$order = 100;
	foreach ( aria_on_request_products() as $slug => $d ) {
		++$order;
		if ( get_page_by_path( $slug, OBJECT, 'product' ) ) {
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'    => 'product',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $d['title'],
			'post_excerpt' => $d['excerpt'],
			'post_content' => $d['content'],
			'menu_order'   => $order,
		) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		if ( $dates ) {
			wp_set_object_terms( $id, array( (int) $dates->term_id ), 'product_cat' );
		}
		update_post_meta( $id, '_aria_specs', aria_parse_spec_lines( $d['specs'] ) );
		update_post_meta( $id, '_aria_packing', $d['packing'] );
		update_post_meta( $id, '_aria_uses', $d['uses'] );
		update_post_meta( $id, '_aria_faq', $d['faq'] );
		update_post_meta( $id, '_aria_seo_title', $d['seo'][0] );
		update_post_meta( $id, '_aria_seo_desc', $d['seo'][1] );
		update_post_meta( $id, '_aria_availability', 'BackOrder' );
		update_post_meta( $id, '_aria_alternatives', implode( ',', $d['alt'] ) );
	}
	delete_transient( 'aria_product_choices' );
	delete_transient( 'aria_mega' );
	update_option( 'aria_onreq_v1', time() );
} );
