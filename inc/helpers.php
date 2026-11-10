<?php
/**
 * Small helpers used across templates.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

/**
 * Contact & brand settings (Customizer → Aria Foods), with defaults from ariafoods.co.
 */
function aria_defaults() {
	return array(
		'company'      => 'Aria Foods',
		'legal_name'   => 'Aria International Trade Development Co.',
		'tagline'      => 'Iranian dates and date ingredients, packed to your specification',
		'phone'        => '+98 31 3240 3844',
		'whatsapp'     => '+98 991 4901530',
		'email'        => 'info@ariafoods.co',
		'leads_email'  => '',
		'address'      => 'Floor 2, Royal Complex, Mirdamad St, Esfahan, Iran',
		'founded'      => '2016',
		'catalogue'    => 'https://ariafoods.co/wp-content/uploads/2025/07/Aria-Foods-Ctlg-2025.pdf',
		'cert_iso9001' => 'https://ariafoods.co/wp-content/uploads/2023/10/ISO-9001.pdf',
		'cert_iso22000'=> 'https://ariafoods.co/wp-content/uploads/2023/10/ISO-22000.pdf',
		'cert_haccp'   => 'https://ariafoods.co/wp-content/uploads/2023/10/HACCP.pdf',
		'facebook'     => 'https://www.facebook.com/profile.php?id=61562364509059',
		'instagram'    => 'https://www.instagram.com/ariafoods.co',
		'linkedin'     => 'https://www.linkedin.com/company/ariafoodsco/',
		'youtube'      => 'https://www.youtube.com/@AriaFoods-co',
		'telegram'     => 'https://t.me/Ariafoods',
		'hero_image'   => '',
		'hero_products' => '/wp-content/uploads/AriaFoods-Date-Product-Collection.webp',
		'branch_legal' => '',
		'license_no'   => '',
		'license_auth' => '',
		'street'       => '',
		'city'         => 'Dubai',
		'country'      => 'AE',
		'phone_ae'     => '',
		'hours'        => '',
		'map_url'      => '',
		'parent_name'  => 'Aria Foods (head office, Iran)',
		'parent_url'   => 'https://ariafoods.co',
		'shorts'       => '6fUuZKJzaYI,8yN2WXgYj38,iVxgSiTkwZ8,3amSMV7c678,siR75cQ0Wp4,ym7gxrpUarY,W13Qfz7wG3U,TTlPNGLm48g',
	);
}

function aria_opt( $key ) {
	$d = aria_defaults();
	$v = aria_fix_host( get_theme_mod( 'aria_' . $key, null ) );
	if ( null === $v || '' === $v ) {
		return isset( $d[ $key ] ) ? $d[ $key ] : '';
	}
	return $v;
}

function aria_digits( $phone ) {
	return preg_replace( '/\D+/', '', (string) $phone );
}

function aria_wa_url( $text = '' ) {
	$url = 'https://wa.me/' . aria_digits( aria_opt( 'whatsapp' ) );
	if ( $text ) {
		$url .= '?text=' . rawurlencode( $text );
	}
	return $url;
}

function aria_tel_url( $phone = null ) {
	return 'tel:+' . aria_digits( $phone ? $phone : aria_opt( 'phone' ) );
}

/**
 * Inline SVG icons (stroke icons, 24px grid). Kept tiny; no icon font.
 */
function aria_icon( $name, $class = 'icon' ) {
	$p = array(
		'whatsapp' => '<path fill="currentColor" stroke="none" fill-rule="evenodd" d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.87 9.87 0 0 0 4.74 1.21c5.46 0 9.91-4.45 9.91-9.91S17.5 2 12.04 2Zm0 18.15a8.2 8.2 0 0 1-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.25-8.24a8.24 8.24 0 0 1 0 16.48Z"/><path fill="currentColor" stroke="none" d="M16.56 13.98c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.13-.16.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-2-1.23-.74-.66-1.24-1.47-1.38-1.72-.15-.25-.02-.38.11-.51.11-.11.25-.29.37-.44.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.44-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.44.06-.66.31-.23.25-.87.85-.87 2.07s.89 2.4 1.01 2.56c.13.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.08.15-1.18-.06-.11-.23-.17-.48-.29Z"/>',
		'sun'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
		'moon'     => '<path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5Z"/>',
		'monitor'  => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/>',
		'phone'    => '<path d="M5 3h4l2 5-2.5 1.5a11 11 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 5a2 2 0 0 1 2-2Z"/>',
		'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'pin'      => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'download' => '<path d="M12 3v12m0 0-4.5-4.5M12 15l4.5-4.5M4 19h16"/>',
		'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'    => '<path d="M6 6l12 12M18 6 6 18"/>',
		'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'bank'     => '<path d="M3 10h18L12 4 3 10Z"/><path d="M5 10v8M9.5 10v8M14.5 10v8M19 10v8M3 21h18"/>',
		'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
		'container'=> '<rect x="2.5" y="7" width="19" height="11" rx="1"/><path d="M6.5 7v11M10.5 7v11M14.5 7v11M18.5 7v11"/>',
		'doc'      => '<path d="M7 3h7l5 5v13H7z"/><path d="M14 3v5h5M10 13h6M10 17h6"/>',
		'chevron'  => '<path d="m6 9 6 6 6-6"/>',
		'arrow'    => '<path d="M5 12h14m-6-6 6 6-6 6"/>',
		'check'    => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
		'play'     => '<path d="M8 5.5v13l11-6.5-11-6.5Z" fill="currentColor"/>',
		'box'      => '<path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z"/><path d="m3 7.5 9 4.5 9-4.5M12 12v9"/>',
		'tag'      => '<path d="M3 12V4h8l10 10-8 8L3 12Z"/><circle cx="7.5" cy="8" r="1.5"/>',
		'flask'    => '<path d="M9 3h6M10 3v6L4.5 19a1.5 1.5 0 0 0 1.3 2h12.4a1.5 1.5 0 0 0 1.3-2L14 9V3"/><path d="M7 15h10"/>',
		'ship'     => '<path d="M3 17l2 4h14l2-4H3ZM5 17V9h14v8M9 9V5h6v4"/>',
		'shield'   => '<path d="M12 3 4 6v6c0 4.5 3.4 8.2 8 9 4.6-.8 8-4.5 8-9V6l-8-3Z"/><path d="m8.5 12 2.5 2.5 4.5-5"/>',
		'linkedin' => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 10v7M8 7v.01M12 17v-4a2 2 0 0 1 4 0v4M12 10v7"/>',
		'instagram'=> '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5v.01"/>',
		'facebook' => '<path d="M14 8h3V4h-3a4 4 0 0 0-4 4v3H7v4h3v6h4v-6h3l1-4h-4V8Z"/>',
		'youtube'  => '<rect x="2.5" y="5" width="19" height="14" rx="4"/><path d="m10 9 5 3-5 3V9Z" fill="currentColor"/>',
		'telegram' => '<path d="m3 11 18-7-3 16-6-5-3 3v-5l9-7"/>',
		'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
		'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
	);
	if ( ! isset( $p[ $name ] ) ) {
		return '';
	}
	return '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $p[ $name ] . '</svg>';
}

/**
 * Product image with a remote fallback for items imported without a local copy.
 */
function aria_product_image( $post_id, $size = 'aria-card', $attr = array() ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail( $post_id, $size, $attr );
	}
	$remote = get_post_meta( $post_id, '_aria_remote_thumb', true );
	if ( $remote ) {
		$alt = isset( $attr['alt'] ) ? $attr['alt'] : get_the_title( $post_id );
		$loading = isset( $attr['loading'] ) ? $attr['loading'] : 'lazy';
		return sprintf( '<img src="%s" alt="%s" width="640" height="640" loading="%s" decoding="async">', esc_url( $remote ), esc_attr( $alt ), esc_attr( $loading ) );
	}
	return '<span class="img-placeholder" aria-hidden="true"></span>';
}

/**
 * Specification rows for a product: array of [label, value].
 */
function aria_specs( $post_id ) {
	$specs = get_post_meta( $post_id, '_aria_specs', true );
	return is_array( $specs ) ? $specs : array();
}

/** Find one spec value by label pattern (e.g. 'moist'). */
function aria_spec( $post_id, $pattern ) {
	foreach ( aria_specs( $post_id ) as $row ) {
		if ( isset( $row[0], $row[1] ) && preg_match( '/' . $pattern . '/i', $row[0] ) ) {
			return $row[1];
		}
	}
	return '';
}

/** Is this product a date variety (vs. an ingredient)? */
function aria_is_variety( $post_id ) {
	return has_term( 'dates', 'product_cat', $post_id );
}

/**
 * Breadcrumb trail as array of [label, url]. Used for markup and JSON-LD.
 */
function aria_breadcrumb_items() {
	$items = array( array( __( 'Home', 'aria' ), home_url( '/' ) ) );
	if ( is_singular( 'product' ) ) {
		$items[] = array( __( 'Products', 'aria' ), get_post_type_archive_link( 'product' ) );
		$terms = get_the_terms( get_the_ID(), 'product_cat' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$items[] = array( $terms[0]->name, get_term_link( $terms[0] ) );
		}
		$items[] = array( get_the_title(), get_permalink() );
	} elseif ( is_post_type_archive( 'product' ) ) {
		$items[] = array( __( 'Products', 'aria' ), get_post_type_archive_link( 'product' ) );
	} elseif ( is_tax( 'product_cat' ) ) {
		$items[] = array( __( 'Products', 'aria' ), get_post_type_archive_link( 'product' ) );
		$items[] = array( single_term_title( '', false ), get_term_link( get_queried_object() ) );
	} elseif ( is_singular( 'post' ) ) {
		$blog = (int) get_option( 'page_for_posts' );
		if ( $blog ) {
			$items[] = array( get_the_title( $blog ), get_permalink( $blog ) );
		}
		$cats = get_the_category();
		if ( $cats ) {
			$items[] = array( $cats[0]->name, get_category_link( $cats[0] ) );
		}
		$items[] = array( get_the_title(), get_permalink() );
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $anc ) {
			$items[] = array( get_the_title( $anc ), get_permalink( $anc ) );
		}
		$items[] = array( get_the_title(), get_permalink() );
	} elseif ( is_home() ) {
		$blog = (int) get_option( 'page_for_posts' );
		$items[] = array( $blog ? get_the_title( $blog ) : __( 'Insights', 'aria' ), $blog ? get_permalink( $blog ) : home_url( '/' ) );
	} elseif ( is_category() || is_tag() ) {
		$blog = (int) get_option( 'page_for_posts' );
		if ( $blog ) {
			$items[] = array( get_the_title( $blog ), get_permalink( $blog ) );
		}
		$items[] = array( single_term_title( '', false ), get_term_link( get_queried_object() ) );
	} elseif ( is_search() ) {
		$items[] = array( __( 'Search', 'aria' ), '' );
	}
	return $items;
}

function aria_breadcrumbs() {
	$items = aria_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return;
	}
	echo '<nav class="crumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'aria' ) . '"><ol>';
	$last = count( $items ) - 1;
	foreach ( $items as $i => $it ) {
		if ( $i === $last || ! $it[1] ) {
			echo '<li><span aria-current="page">' . esc_html( $it[0] ) . '</span></li>';
		} else {
			echo '<li><a href="' . esc_url( $it[1] ) . '">' . esc_html( $it[0] ) . '</a></li>';
		}
	}
	echo '</ol></nav>';
}

/** Estimated reading time in minutes. */
function aria_read_time( $post_id = null ) {
	$words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) );
	return max( 1, (int) round( $words / 220 ) );
}

/** Certificates list for trust rows. */
function aria_certs() {
	return array(
		array( 'ISO 9001', __( 'Quality management – valid to Aug 2029', 'aria' ), aria_cert_url( 'cert_iso9001', 'aria-foods-iso-9001-2015-certificate.jpg' ) ),
		array( 'ISO 22000', __( 'Food safety management – valid to Aug 2029', 'aria' ), aria_cert_url( 'cert_iso22000', 'aria-foods-iso-22000-2018-certificate.jpg' ) ),
		array( 'HACCP', __( 'Hazard analysis and critical control points – valid to Aug 2029', 'aria' ), aria_cert_url( 'cert_haccp', 'aria-foods-haccp-certificate.jpg' ) ),
	);
}

/** Order-quantity options shared by forms. */
function aria_quantity_options() {
	return array(
		'1 × 20 ft container (about 15 ton)',
		'1 × 40 ft container (about 25 ton)',
		'2–5 containers',
		'More than 5 containers / regular supply',
	);
}

/* ---------- Product data (SKU, price) and gallery ---------- */

function aria_price_units() {
	// UN/CEFACT codes used in schema.org unitCode.
	return array(
		'TNE' => array( 'per metric ton', 'ton' ),
		'KGM' => array( 'per kg', 'kg' ),
		'CT'  => array( 'per carton', 'carton' ),
		'H87' => array( 'per piece / pack', 'pack' ),
	);
}

function aria_availability_options() {
	return array(
		'InStock'    => __( 'In stock', 'aria' ),
		'PreOrder'   => __( 'Pre-order (next harvest)', 'aria' ),
		'LimitedAvailability' => __( 'Limited availability', 'aria' ),
		'OutOfStock' => __( 'Out of stock', 'aria' ),
	);
}

function aria_product_data( $post_id ) {
	return array(
		'sku'          => (string) get_post_meta( $post_id, '_aria_sku', true ),
		'price'        => (string) get_post_meta( $post_id, '_aria_price', true ),
		'currency'     => get_post_meta( $post_id, '_aria_currency', true ) ?: 'USD',
		'unit'         => get_post_meta( $post_id, '_aria_unit', true ) ?: 'TNE',
		'valid'        => (string) get_post_meta( $post_id, '_aria_valid', true ),
		'availability' => get_post_meta( $post_id, '_aria_availability', true ) ?: 'InStock',
		'show_price'   => (bool) get_post_meta( $post_id, '_aria_show_price', true ),
	);
}

/** Gallery attachment IDs (without the featured image). */
function aria_gallery_ids( $post_id ) {
	$ids = get_post_meta( $post_id, '_aria_gallery', true );
	$ids = is_array( $ids ) ? array_map( 'absint', $ids ) : array();
	return array_values( array_filter( $ids, function ( $id ) {
		return $id && wp_attachment_is_image( $id );
	} ) );
}

/** Featured image first, then gallery. */
function aria_all_image_ids( $post_id ) {
	$ids   = aria_gallery_ids( $post_id );
	$thumb = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb ) {
		$ids = array_values( array_diff( $ids, array( $thumb ) ) );
		array_unshift( $ids, $thumb );
	}
	return $ids;
}

/** Customer helpers. */
function aria_is_customer( $user = null ) {
	$user = $user ? $user : wp_get_current_user();
	return $user && $user->exists() && in_array( 'customer', (array) $user->roles, true ) && ! user_can( $user, 'edit_posts' );
}

function aria_account_url( $tab = '' ) {
	$url = home_url( '/my-account/' );
	return $tab ? add_query_arg( 'tab', $tab, $url ) : $url;
}

/**
 * Safety net after a domain change: links saved with the old staging address
 * (modern.ariafoods.co) are served with the current address until Tools → Change domain runs.
 */
function aria_fix_host( $s ) {
	if ( ! is_string( $s ) || false === strpos( $s, 'modern.ariafoods.co' ) ) {
		return $s;
	}
	$new = untrailingslashit( home_url() );
	return str_replace( array( 'https://modern.ariafoods.co', 'http://modern.ariafoods.co' ), $new, $s );
}
add_filter( 'the_content', 'aria_fix_host', 99 );
add_filter( 'wp_get_attachment_url', 'aria_fix_host' );

/** Dubai branch details are filled in (Customizer → Aria Foods settings → Dubai branch). */
function aria_has_branch() {
	return '' !== trim( (string) aria_opt( 'street' ) );
}

/** Address shown to visitors: Dubai office when set, otherwise the head-office text. */
function aria_display_address() {
	if ( aria_has_branch() ) {
		return trim( aria_opt( 'street' ) . ', ' . aria_opt( 'city' ) . ', United Arab Emirates', ', ' );
	}
	return aria_opt( 'address' );
}

/* Site e-mails come from noreply@<this domain> instead of wordpress@. */
add_filter( 'wp_mail_from', function ( $from ) {
	$host = preg_replace( '/^www\./', '', (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	return ( 0 === strpos( $from, 'wordpress@' ) && $host ) ? 'noreply@' . $host : $from;
} );
add_filter( 'wp_mail_from_name', function ( $name ) {
	return 'WordPress' === $name ? aria_opt( 'company' ) : $name;
} );

/**
 * Certificate link: the current certificate shipped with the theme (assets/certs/), unless the
 * Customizer holds a link to a newer file uploaded to the Media Library.
 */
function aria_cert_url( $key, $file ) {
	$opt = (string) aria_opt( $key );
	if ( $opt && 0 === strpos( $opt, content_url( '/uploads/' ) ) ) { // A newer file uploaded to this site wins.
		return $opt;
	}
	if ( file_exists( ARIA_DIR . '/assets/certs/' . $file ) ) {
		return ARIA_URI . '/assets/certs/' . $file;
	}
	return $opt;
}
