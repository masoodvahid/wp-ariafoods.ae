<?php
/**
 * Products (replaces WooCommerce for a B2B, quote-based catalogue) and leads.
 *
 * URLs match ariafoods.co: /product/{slug}/, /product-category/{slug}/, archive /dates-products/.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		register_post_type( 'product', array(
			'labels'        => array(
				'name'          => __( 'Products', 'aria' ),
				'singular_name' => __( 'Product', 'aria' ),
				'add_new_item'  => __( 'Add product', 'aria' ),
				'edit_item'     => __( 'Edit product', 'aria' ),
				'all_items'     => __( 'All products', 'aria' ),
				'menu_name'     => __( 'Products', 'aria' ),
			),
			'public'        => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-carrot',
			'menu_position' => 20,
			'has_archive'   => 'dates-products',
			'rewrite'       => array( 'slug' => 'product', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
		) );

		register_taxonomy( 'product_cat', 'product', array(
			'labels'            => array(
				'name'          => __( 'Product categories', 'aria' ),
				'singular_name' => __( 'Product category', 'aria' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'product-category', 'with_front' => false ),
		) );
	}

	register_post_type( 'aria_lead', array(
		'labels'          => array(
			'name'          => __( 'Inquiries', 'aria' ),
			'singular_name' => __( 'Inquiry', 'aria' ),
			'menu_name'     => __( 'Inquiries', 'aria' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_rest'    => false,
		'menu_icon'       => 'dashicons-email-alt',
		'menu_position'   => 21,
		'capability_type' => 'post',
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'    => true,
		'supports'        => array( 'title' ),
	) );
} );

/* Product archive: menu order, all items on one page (catalogue is small). */
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_post_type_archive( 'product' ) || $q->is_tax( 'product_cat' ) ) {
		$q->set( 'posts_per_page', 60 );
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
	}
	if ( $q->is_search() ) {
		$q->set( 'post_type', array( 'post', 'page', 'product' ) );
	}
} );

/* Leave leads out of core sitemaps; also users (no author archives on a B2B site). */
add_filter( 'wp_sitemaps_post_types', function ( $types ) {
	unset( $types['aria_lead'], $types['attachment'] );
	return $types;
} );
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}, 10, 2 );

/* Theme activation: permalinks like ariafoods.co, then flush. */
add_action( 'after_switch_theme', function () {
	if ( '/%category%/%postname%/' !== get_option( 'permalink_structure' ) ) {
		update_option( 'aria_prev_permalinks', get_option( 'permalink_structure' ) );
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%category%/%postname%/' );
	}
	update_option( 'aria_flush_rewrite', 1 );
} );
add_action( 'init', function () {
	if ( get_option( 'aria_flush_rewrite' ) ) {
		flush_rewrite_rules( false );
		delete_option( 'aria_flush_rewrite' );
	}
}, 99 );

/* Old ariafoods.co URLs that changed name on the new site. */
add_action( 'template_redirect', function () {
	if ( ! is_404() ) {
		return;
	}
	$path = trim( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
	$map  = array(
		'test-inquiry'      => 'sample-request',
		'bulk-business'     => 'ariafoods-bulk-service',
		'products'          => 'dates-products',
		'about'             => 'about-us',
		'export/premium-date-syrup' => 'product/date-syrup',
		'online-inquiry'    => 'get-a-quote',
		'contact-us/about'  => 'about-us',
		'exclusive-packaging-options'   => 'customer-packing',
		'iranian-mazafati-dates-fruit'  => 'product/mazafati-dates',
		'import/rice'                   => 'dates-products',
		'articles/introduction-letter'  => 'about-us',
		'newsletters/online-services-oct2019' => 'news',
		'cataloge/ingredients-2025'     => 'ariafoods-dates-catalogue',
	);
	if ( isset( $map[ $path ] ) ) {
		wp_safe_redirect( home_url( '/' . $map[ $path ] . '/' ), 301 );
		exit;
	}
} );
