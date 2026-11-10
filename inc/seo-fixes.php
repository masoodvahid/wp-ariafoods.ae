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

/*
 * Full containers only (1.9.3): remove part-load (LCL) wording from page and product content,
 * FAQs and descriptions. Runs once; only exact original phrases are replaced.
 */
function aria_fcl_replacements() {
	return array(
		'LCL from 1 ton, 20 ft and 40 ft containers.'                                                          => 'Full 20 ft and 40 ft containers only.',
		'A: LCL shipments start from 1 ton. A 20 ft container'                                                  => 'A: The minimum order is one 20 ft container. A 20 ft container',
		'volume (LCL, 20 ft or 40 ft)'                                                                          => 'volume (20 ft or 40 ft containers)',
		'A: LCL shipments start from 1 ton; full 20 ft and 40 ft containers are available.'                     => 'A: The minimum order is one full 20 ft container; 40 ft and multi-container orders are available.',
		'Orders start from LCL volumes of 1 ton; a 20 ft container carries up to about 15 tons.'                => 'The minimum order is one 20 ft container, which carries up to about 15 tons.',
		'We export from LCL volumes of 1 ton up to full containers.'                                            => 'We export by the full container, from one 20 ft container.',
		'retail packs, LCL and containers.'                                                                     => 'retail packs, by the full container.',
		'FCL/LCL export'                                                                                        => 'full-container export',
	);
}

function aria_fcl_fix( $text ) {
	$text = strtr( (string) $text, aria_fcl_replacements() );
	return preg_replace( '#<tr><td>LCL</td><td>[^<]*</td></tr>\s*#', '', $text );
}

add_action( 'admin_init', function () {
	if ( get_option( 'aria_fcl_v1' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;
	$rows = $wpdb->get_results( "SELECT ID, post_content, post_excerpt FROM {$wpdb->posts} WHERE post_type IN ('page','post','product') AND post_status IN ('publish','draft','private') AND ( post_content LIKE '%LCL%' OR post_excerpt LIKE '%LCL%' )" );
	foreach ( $rows as $r ) {
		$c = aria_fcl_fix( $r->post_content );
		$e = aria_fcl_fix( $r->post_excerpt );
		if ( $c !== $r->post_content || $e !== $r->post_excerpt ) {
			$wpdb->update( $wpdb->posts, array( 'post_content' => $c, 'post_excerpt' => $e ), array( 'ID' => $r->ID ) );
			clean_post_cache( $r->ID );
		}
	}
	$metas = $wpdb->get_results( "SELECT meta_id, post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key IN ('_aria_faq','_aria_seo_desc') AND meta_value LIKE '%LCL%'" );
	foreach ( $metas as $mrow ) {
		$v = aria_fcl_fix( $mrow->meta_value );
		if ( $v !== $mrow->meta_value ) {
			update_post_meta( $mrow->post_id, $mrow->meta_key, $v );
		}
	}
	update_option( 'aria_fcl_v1', time() );
} );
