<?php
/**
 * One-time SEO clean-up (runs once in wp-admin, version-gated):
 * shorter titles (≤ 60 characters), off-topic posts to draft, broken internal links fixed.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', function () {
	if ( get_option( 'aria_seo_fix_v1' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;

	$titles = array(
		'product' => array(
			'date-syrup'        => 'Bulk Date Syrup Supplier in Dubai (Brix 72–78) | Aria Foods',
			'date-paste'        => 'Date Paste Supplier – Bulk Pitted Date Paste | Aria Foods',
			'chopped-dates'     => 'Chopped Dates Supplier for Bakery & Cereal | Aria Foods',
			'liquid-date-sugar' => 'Liquid Date Sugar Supplier – Brix 72–78 | Aria Foods',
			'mazafati-dates'    => 'Mazafati Dates Wholesale Supplier (Kimia) | Aria Foods',
			'date-powder'       => 'Date Powder Supplier – Bulk Date Sugar | Aria Foods',
			'piarom-dates'      => 'Piarom (Maryam) Dates Wholesale Supplier | Aria Foods',
		),
		'page'    => array(
			'ariafoods-bulk-service' => 'Bulk Iranian Dates Supplier by the Container | Aria Foods',
			'customer-packing'       => 'Private Label Dates – Custom Packing | Aria Foods',
		),
	);
	foreach ( $titles as $type => $list ) {
		foreach ( $list as $slug => $t ) {
			$p = get_page_by_path( $slug, OBJECT, $type );
			if ( $p ) {
				update_post_meta( $p->ID, '_aria_seo_title', $t );
			}
		}
	}
	foreach ( array(
		'dates'       => 'Iranian Dates Wholesale: Mazafati, Piarom, Sayer | Aria Foods',
		'ingredients' => 'Date Syrup, Paste & Powder in Bulk | Aria Foods',
	) as $slug => $t ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term ) {
			update_term_meta( $term->term_id, 'aria_seo_title', $t );
		}
	}

	// Off-topic or near-empty posts copied from ariafoods.co: unpublish (kept as drafts, URLs redirect).
	foreach ( array( 'rice', 'introduction-letter', 'online-services-oct2019', 'ingredients-2025' ) as $slug ) {
		$p = get_page_by_path( $slug, OBJECT, 'post' );
		if ( $p && 'publish' === $p->post_status ) {
			wp_update_post( array( 'ID' => $p->ID, 'post_status' => 'draft' ) );
		}
	}

	// Internal links to pages that no longer exist.
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	foreach ( array(
		'/exclusive-packaging-options/'  => '/customer-packing/',
		'/iranian-mazafati-dates-fruit/' => '/product/mazafati-dates/',
	) as $from => $to ) {
		$wpdb->query( $wpdb->prepare(
			"UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s) WHERE post_content LIKE %s",
			$host . $from,
			$host . $to,
			'%' . $wpdb->esc_like( $host . $from ) . '%'
		) );
	}

	update_option( 'aria_seo_fix_v1', 1, false );
} );
