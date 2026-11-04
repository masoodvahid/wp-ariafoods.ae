<?php
/**
 * Navigation: one renderer for the WP menu (if assigned) or the built-in default.
 * A top-level item with CSS class "mega-products" renders the live product mega panel.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

/** Default structure, modelled on the ariafoods.co menu and reorganised by buyer task. */
function aria_default_menu() {
	$p = function ( $slug ) {
		return home_url( '/' . trim( $slug, '/' ) . '/' );
	};
	return array(
		array( 'title' => 'Products', 'url' => $p( 'dates-products' ), 'classes' => array( 'mega-products' ), 'children' => array() ),
		array( 'title' => 'Date syrup', 'url' => $p( 'product/date-syrup' ), 'classes' => array(), 'children' => array() ),
		array( 'title' => 'Services', 'url' => $p( 'ariafoods-bulk-service' ), 'classes' => array(), 'children' => array(
			array( 'title' => 'Bulk & food service', 'url' => $p( 'ariafoods-bulk-service' ), 'desc' => 'Cartons, bags, IBC tanks for importers and factories' ),
			array( 'title' => 'Private label', 'url' => $p( 'customer-packing' ), 'desc' => 'Your brand on 250–700 g retail packs' ),
			array( 'title' => 'Request a sample', 'url' => $p( 'sample-request' ), 'desc' => 'Check quality before you order' ),
			array( 'title' => 'Track an order', 'url' => $p( 'tracking-orders' ), 'desc' => 'Status of a confirmed shipment' ),
		) ),
		array( 'title' => 'Insights', 'url' => $p( 'news' ), 'classes' => array(), 'children' => array() ),
		array( 'title' => 'Company', 'url' => $p( 'about-us' ), 'classes' => array(), 'children' => array(
			array( 'title' => 'About Aria Foods', 'url' => $p( 'about-us' ), 'desc' => 'Factories, quality control, export team' ),
			array( 'title' => 'Managing board', 'url' => $p( 'managing-board' ), 'desc' => 'The people behind the company' ),
			array( 'title' => 'Catalogue 2025', 'url' => $p( 'ariafoods-dates-catalogue' ), 'desc' => 'Full range with specifications' ),
			array( 'title' => 'Contact', 'url' => $p( 'contact-us' ), 'desc' => 'Office, phone and trade desk' ),
		) ),
	);
}

/** Build a tree from a WP menu location, or return null. */
function aria_menu_tree( $location ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return null;
	}
	$items = wp_get_nav_menu_items( $locations[ $location ] );
	if ( ! $items ) {
		return null;
	}
	_wp_menu_item_classes_by_context( $items );
	$by_parent = array();
	foreach ( $items as $it ) {
		$by_parent[ (int) $it->menu_item_parent ][] = $it;
	}
	$build = function ( $parent ) use ( &$build, $by_parent ) {
		$out = array();
		if ( empty( $by_parent[ $parent ] ) ) {
			return $out;
		}
		foreach ( $by_parent[ $parent ] as $it ) {
			$out[] = array(
				'title'    => $it->title,
				'url'      => aria_fix_host( $it->url ),
				'desc'     => $it->description,
				'classes'  => array_filter( (array) $it->classes ),
				'current'  => in_array( 'current-menu-item', (array) $it->classes, true ) || in_array( 'current-menu-ancestor', (array) $it->classes, true ),
				'children' => $build( (int) $it->ID ),
			);
		}
		return $out;
	};
	return $build( 0 );
}

function aria_is_current_url( $url ) {
	$req = untrailingslashit( home_url( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) ) );
	return untrailingslashit( $url ) === $req;
}

/** Live product mega panel: varieties | ingredients | date syrup feature. */
function aria_mega_products() {
	$groups = array(
		'dates'       => __( 'Date varieties', 'aria' ),
		'ingredients' => __( 'Date ingredients', 'aria' ),
	);
	ob_start();
	echo '<div class="mega">';
	foreach ( $groups as $slug => $label ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		$q    = new WP_Query( array(
			'post_type'      => 'product',
			'posts_per_page' => 20,
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'no_found_rows'  => true,
			'tax_query'      => $term ? array( array( 'taxonomy' => 'product_cat', 'terms' => $term->term_id ) ) : array(),
		) );
		if ( ! $term || ! $q->have_posts() ) {
			continue;
		}
		echo '<div class="mega-col"><p class="mega-title"><a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $label ) . '</a></p><ul>';
		while ( $q->have_posts() ) {
			$q->the_post();
			if ( 'date-syrup' === get_post_field( 'post_name' ) ) {
				continue;
			}
			$facts = array_filter( array( aria_spec( get_the_ID(), 'moist' ), aria_spec( get_the_ID(), '^size' ) ) );
			echo '<li><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() );
			if ( 'dates' === $slug && $facts ) {
				echo '<small>' . esc_html( implode( ', ', array_slice( $facts, 0, 1 ) ) ) . '</small>';
			}
			echo '</a></li>';
		}
		wp_reset_postdata();
		echo '</ul></div>';
	}
	$syrup = get_page_by_path( 'date-syrup', OBJECT, 'product' );
	if ( $syrup ) {
		echo '<a class="mega-feature" href="' . esc_url( get_permalink( $syrup ) ) . '">';
		echo '<span class="mega-feature-media">' . aria_product_image( $syrup->ID, 'aria-card', array( 'alt' => '', 'loading' => 'lazy' ) ) . '</span>'; // phpcs:ignore
		echo '<strong>' . esc_html__( 'Date syrup in bulk', 'aria' ) . '</strong>';
		echo '<span>' . esc_html__( 'Brix 72–78, in drums, gallons and 1,400 kg IBC tanks', 'aria' ) . '</span></a>';
	}
	echo '<p class="mega-foot"><a href="' . esc_url( get_post_type_archive_link( 'product' ) ) . '">' . esc_html__( 'See the full range', 'aria' ) . '</a> <a href="' . esc_url( aria_opt( 'catalogue' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Download catalogue (PDF)', 'aria' ) . '</a></p>';
	echo '</div>';
	return ob_get_clean();
}

/** Render primary navigation. */
function aria_primary_nav() {
	$tree = aria_menu_tree( 'primary' );
	if ( null === $tree ) {
		$tree = aria_default_menu();
	}
	$mega_html = null;
	echo '<ul class="nav-list">';
	foreach ( $tree as $i => $item ) {
		$is_mega = in_array( 'mega-products', (array) $item['classes'], true );
		$has_sub = $is_mega || ! empty( $item['children'] );
		$current = ! empty( $item['current'] ) || aria_is_current_url( $item['url'] );
		$id      = 'sub-' . $i;
		echo '<li class="nav-item' . ( $has_sub ? ' has-sub' : '' ) . ( $is_mega ? ' is-mega' : '' ) . '">';
		echo '<a class="nav-link" href="' . esc_url( $item['url'] ) . '"' . ( $current ? ' aria-current="page"' : '' ) . '>' . esc_html( $item['title'] ) . '</a>';
		if ( $has_sub ) {
			echo '<button class="sub-toggle" type="button" aria-expanded="false" aria-controls="' . esc_attr( $id ) . '"><span class="screen-reader-text">' . esc_html( sprintf( __( 'Show %s submenu', 'aria' ), $item['title'] ) ) . '</span>' . aria_icon( 'chevron' ) . '</button>'; // phpcs:ignore
			echo '<div class="sub" id="' . esc_attr( $id ) . '">';
			if ( $is_mega ) {
				if ( null === $mega_html ) {
					$mega_html = get_transient( 'aria_mega' );
					if ( false === $mega_html ) {
						$mega_html = aria_mega_products();
						set_transient( 'aria_mega', $mega_html, DAY_IN_SECONDS );
					}
				}
				echo $mega_html; // phpcs:ignore -- built from escaped parts above.
			} else {
				echo '<ul class="sub-list">';
				foreach ( $item['children'] as $c ) {
					echo '<li><a href="' . esc_url( $c['url'] ) . '"' . ( aria_is_current_url( $c['url'] ) ? ' aria-current="page"' : '' ) . '><strong>' . esc_html( $c['title'] ) . '</strong>';
					if ( ! empty( $c['desc'] ) ) {
						echo '<span>' . esc_html( $c['desc'] ) . '</span>';
					}
					echo '</a></li>';
				}
				echo '</ul>';
			}
			echo '</div>';
		}
		echo '</li>';
	}
	echo '</ul>';
}

/* Clear cached mega panel whenever products change. */
foreach ( array( 'save_post_product', 'deleted_post', 'edited_product_cat', 'wp_update_nav_menu' ) as $hook ) {
	add_action( $hook, function () {
		delete_transient( 'aria_mega' );
	} );
}

/** Simple flat footer menu (WP menu or defaults). */
function aria_footer_links( $location, $fallback ) {
	$tree = aria_menu_tree( $location );
	$list = $tree ? $tree : $fallback;
	echo '<ul>';
	foreach ( $list as $l ) {
		echo '<li><a href="' . esc_url( $l['url'] ) . '">' . esc_html( $l['title'] ) . '</a></li>';
	}
	echo '</ul>';
}
