<?php
/**
 * SEO without plugins: titles, descriptions, canonical, Open Graph, JSON-LD.
 * Per-item fields live in the "Search appearance" box (inc/admin.php).
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

function aria_seo_title_override() {
	if ( is_singular() ) {
		return trim( (string) get_post_meta( get_queried_object_id(), '_aria_seo_title', true ) );
	}
	if ( is_category() || is_tax() || is_tag() ) {
		return trim( (string) get_term_meta( get_queried_object_id(), 'aria_seo_title', true ) );
	}
	if ( is_post_type_archive( 'product' ) ) {
		return 'Iranian Dates & Date Ingredients Catalogue | Wholesale Supplier';
	}
	return '';
}

add_filter( 'pre_get_document_title', function ( $title ) {
	$t = aria_seo_title_override();
	if ( $t ) {
		return $t;
	}
	if ( is_front_page() ) {
		$fp = (int) get_option( 'page_on_front' );
		$t  = $fp ? trim( (string) get_post_meta( $fp, '_aria_seo_title', true ) ) : '';
		return $t ? $t : 'Iranian Dates & Date Syrup Wholesale Supplier | Aria Foods';
	}
	return $title;
}, 20 );

add_filter( 'document_title_separator', function () {
	return '|';
} );
add_filter( 'document_title_parts', function ( $parts ) {
	unset( $parts['tagline'] );
	$parts['site'] = aria_opt( 'company' );
	return $parts;
} );

function aria_meta_description() {
	$d = '';
	if ( is_front_page() ) {
		$fp = (int) get_option( 'page_on_front' );
		$d  = $fp ? get_post_meta( $fp, '_aria_seo_desc', true ) : '';
		if ( ! $d ) {
			$d = 'Wholesale supplier of Iranian dates and date ingredients: Mazafati, Piarom, Zahedi, Sayer, bulk date syrup (Brix 72–78), date paste and powder. full-container export, private label, ISO 22000 & HACCP.';
		}
	} elseif ( is_singular() ) {
		$id = get_queried_object_id();
		$d  = get_post_meta( $id, '_aria_seo_desc', true );
		if ( ! $d ) {
			$d = has_excerpt( $id ) ? get_the_excerpt( $id ) : wp_trim_words( get_post_field( 'post_content', $id ), 30, '' );
		}
	} elseif ( is_category() || is_tax() || is_tag() ) {
		$id = get_queried_object_id();
		$d  = get_term_meta( $id, 'aria_seo_desc', true );
		if ( ! $d ) {
			$d = term_description( $id );
		}
	} elseif ( is_post_type_archive( 'product' ) ) {
		$d = 'Specifications for Iranian date varieties and date ingredients supplied in bulk and private label: moisture, size, shelf life, Brix and packing. Request a container quote.';
	} elseif ( is_home() ) {
		$blog = (int) get_option( 'page_for_posts' );
		$d    = $blog ? get_post_meta( $blog, '_aria_seo_desc', true ) : '';
		if ( ! $d ) {
			$d = 'Market updates, harvest reports and sourcing guides for importers and food manufacturers buying Iranian dates, date syrup, paste and powder.';
		}
	}
	$d = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $d ) ) );
	return mb_strlen( $d ) > 165 ? rtrim( mb_substr( $d, 0, 160 ), ' ,.;:' ) . '…' : $d;
}

function aria_canonical() {
	if ( is_singular() ) {
		$src = aria_source_canonical( get_queried_object_id() );
		return $src ? $src : wp_get_canonical_url( get_queried_object_id() );
	}
	$paged = max( 1, (int) get_query_var( 'paged' ) );
	$url   = '';
	if ( is_front_page() ) {
		$url = home_url( '/' );
	} elseif ( is_home() ) {
		$blog = (int) get_option( 'page_for_posts' );
		$url  = $blog ? get_permalink( $blog ) : home_url( '/' );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$url = get_term_link( get_queried_object() );
	} elseif ( is_post_type_archive() ) {
		$url = get_post_type_archive_link( get_query_var( 'post_type' ) ? get_query_var( 'post_type' ) : 'product' );
	}
	if ( $url && ! is_wp_error( $url ) && $paged > 1 ) {
		$url = trailingslashit( $url ) . 'page/' . $paged . '/';
	}
	return is_wp_error( $url ) ? '' : $url;
}

function aria_og_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'aria-wide' );
		if ( $img ) {
			return $img;
		}
	}
	if ( is_singular() ) {
		$remote = get_post_meta( get_queried_object_id(), '_aria_remote_thumb', true );
		if ( $remote ) {
			return array( $remote, 1100, 1100 );
		}
	}
	return array( aria_logo_url(), 297, 260 );
}

function aria_logo_url() {
	$id = get_theme_mod( 'custom_logo' );
	if ( $id ) {
		$src = wp_get_attachment_image_url( $id, 'full' );
		if ( $src ) {
			return $src;
		}
	}
	return 'https://ariafoods.co/wp-content/uploads/2020/10/test-logo.png';
}

/* Robots: no thin internal search pages. */
add_filter( 'wp_robots', function ( $robots ) {
	if ( is_search() || is_404() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	} elseif ( is_singular() && get_post_meta( get_queried_object_id(), '_aria_noindex', true ) ) {
		$robots['noindex'] = true;
	} elseif ( is_category() || is_tag() || is_author() || is_date() ) {
		// Blog archives only list articles that belong to ariafoods.co (canonical there): keep them out of Google.
		$robots['noindex'] = true;
		$robots['follow']  = true;
	} elseif ( ! get_option( 'blog_public' ) ) {
		return $robots;
	} else {
		$robots['max-image-preview'] = 'large';
		$robots['max-snippet']       = '-1';
	}
	return $robots;
} );

add_action( 'wp_head', function () {
	$desc  = aria_meta_description();
	$canon = aria_canonical();
	$img   = aria_og_image();
	$title = wp_get_document_title();

	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
	if ( $canon ) {
		echo '<link rel="canonical" href="' . esc_url( $canon ) . '">' . "\n";
	}
	$og = array(
		'og:locale'    => 'en_US',
		'og:type'      => is_singular( 'post' ) ? 'article' : 'website',
		'og:site_name' => aria_opt( 'company' ),
		'og:title'     => $title,
		'og:description' => $desc,
		'og:url'       => $canon,
		'og:image'     => $img[0],
		'og:image:width'  => $img[1],
		'og:image:height' => $img[2],
	);
	foreach ( array_filter( $og ) as $k => $v ) {
		echo '<meta property="' . esc_attr( $k ) . '" content="' . esc_attr( $v ) . '">' . "\n";
	}
	if ( is_singular( 'post' ) ) {
		echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '">' . "\n";
		echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . "\n";
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="theme-color" content="#2b1a12">' . "\n";

	$graph = aria_schema_graph( $canon, $desc, $img );
	echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 2 );

function aria_schema_graph( $canon, $desc, $img ) {
	$home  = home_url( '/' );
	$org   = $home . '#organization';
	$same  = array_values( array_filter( array( aria_opt( 'linkedin' ), aria_opt( 'instagram' ), aria_opt( 'facebook' ), aria_opt( 'youtube' ), aria_opt( 'telegram' ) ) ) );
	$graph = array();

	$graph[] = array(
		'@type'        => 'Organization',
		'@id'          => $org,
		'name'         => aria_opt( 'company' ),
		'legalName'    => aria_opt( 'branch_legal' ) ? aria_opt( 'branch_legal' ) : aria_opt( 'legal_name' ),
		'url'          => $home,
		'logo'         => array( '@type' => 'ImageObject', 'url' => aria_logo_url() ),
		'foundingDate' => aria_opt( 'founded' ),
		'email'        => aria_opt( 'email' ),
		'telephone'    => aria_opt( 'phone_ae' ) ? aria_opt( 'phone_ae' ) : aria_opt( 'phone' ),
		'address'      => aria_has_branch()
			? array( '@type' => 'PostalAddress', 'streetAddress' => aria_opt( 'street' ), 'addressLocality' => aria_opt( 'city' ), 'addressCountry' => aria_opt( 'country' ) )
			: array( '@type' => 'PostalAddress', 'addressLocality' => aria_opt( 'city' ), 'addressCountry' => aria_opt( 'country' ) ),
		'parentOrganization' => array(
			'@type'   => 'Organization',
			'name'    => aria_opt( 'parent_name' ),
			'url'     => aria_opt( 'parent_url' ),
			'address' => array( '@type' => 'PostalAddress', 'streetAddress' => 'Floor 2, Royal Complex, Mirdamad St', 'addressLocality' => 'Isfahan', 'addressCountry' => 'IR' ),
		),
		'contactPoint' => array(
			array( '@type' => 'ContactPoint', 'contactType' => 'sales', 'telephone' => aria_opt( 'whatsapp' ), 'email' => aria_opt( 'email' ), 'availableLanguage' => array( 'English' ), 'areaServed' => 'Worldwide' ),
		),
		'sameAs'       => $same,
		'knowsAbout'   => array( 'Iranian dates', 'Date syrup', 'Date paste', 'Date powder', 'Bulk food export', 'Private label packaging' ),
	);

	if ( ( is_front_page() || is_page( array( 'contact-us', 'about-us' ) ) ) && aria_has_branch() ) {
		$lb = array(
			'@type'              => 'WholesaleStore',
			'@id'                => $home . '#dubai-office',
			'name'               => aria_opt( 'company' ) . ' – Dubai',
			'url'                => $home,
			'image'              => aria_logo_url(),
			'telephone'          => aria_opt( 'phone_ae' ) ? aria_opt( 'phone_ae' ) : aria_opt( 'whatsapp' ),
			'email'              => aria_opt( 'email' ),
			'address'            => array( '@type' => 'PostalAddress', 'streetAddress' => aria_opt( 'street' ), 'addressLocality' => aria_opt( 'city' ), 'addressCountry' => aria_opt( 'country' ) ),
			'parentOrganization' => array( '@id' => $org ),
		);
		if ( aria_opt( 'hours' ) ) {
			$lb['openingHours'] = aria_opt( 'hours' );
		}
		if ( aria_opt( 'map_url' ) ) {
			$lb['hasMap'] = aria_opt( 'map_url' );
		}
		$graph[] = $lb;
	}

	if ( is_front_page() ) {
		$graph[] = array(
			'@type'           => 'WebSite',
			'@id'             => $home . '#website',
			'url'             => $home,
			'name'            => aria_opt( 'company' ),
			'inLanguage'      => 'en',
			'publisher'       => array( '@id' => $org ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array( '@type' => 'EntryPoint', 'urlTemplate' => $home . '?s={search_term_string}' ),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	$crumbs = aria_breadcrumb_items();
	if ( count( $crumbs ) > 1 ) {
		$list = array();
		foreach ( $crumbs as $i => $c ) {
			$el = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => wp_strip_all_tags( $c[0] ) );
			if ( $c[1] && ! is_wp_error( $c[1] ) ) {
				$el['item'] = $c[1];
			}
			$list[] = $el;
		}
		$graph[] = array( '@type' => 'BreadcrumbList', '@id' => $canon . '#breadcrumb', 'itemListElement' => $list );
	}

	if ( is_singular( 'post' ) ) {
		$graph[] = array(
			'@type'            => 'Article',
			'@id'              => $canon . '#article',
			'headline'         => wp_strip_all_tags( get_the_title() ),
			'description'      => $desc,
			'image'            => $img[0],
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'author'           => array( '@id' => $org ),
			'publisher'        => array( '@id' => $org ),
			'mainEntityOfPage' => $canon,
			'inLanguage'       => 'en',
		);
	}

	if ( is_singular( 'product' ) ) {
		$graph[] = aria_product_schema( get_queried_object_id(), $canon, $desc, $org );
	}

	if ( is_singular() ) {
		$faq = aria_faq_items( get_queried_object_id() );
		if ( $faq ) {
			$q = array();
			foreach ( $faq as $f ) {
				$q[] = array( '@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f[1] ) );
			}
			$graph[] = array( '@type' => 'FAQPage', '@id' => $canon . '#faq', 'mainEntity' => $q );
		}
	} elseif ( is_front_page() ) {
		// Front page with no static page: use built-in FAQ.
		$q = array();
		foreach ( aria_home_faq() as $f ) {
			$q[] = array( '@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f[1] ) );
		}
		$graph[] = array( '@type' => 'FAQPage', '@id' => $home . '#faq', 'mainEntity' => $q );
	}

	return $graph;
}

/**
 * FAQ items saved on a post (meta _aria_faq, lines "Q: … / A: …").
 *
 * @return array of [question, answer]
 */
function aria_faq_items( $post_id ) {
	if ( (int) get_option( 'page_on_front' ) === (int) $post_id ) {
		$raw = get_post_meta( $post_id, '_aria_faq', true );
		return $raw ? aria_parse_faq( $raw ) : aria_home_faq();
	}
	return aria_parse_faq( (string) get_post_meta( $post_id, '_aria_faq', true ) );
}

function aria_parse_faq( $raw ) {
	$out = array();
	$q   = null;
	foreach ( preg_split( '/\R/', (string) $raw ) as $line ) {
		$line = trim( $line );
		if ( preg_match( '/^Q:\s*(.+)$/i', $line, $m ) ) {
			$q = $m[1];
		} elseif ( $q && preg_match( '/^A:\s*(.+)$/i', $line, $m ) ) {
			$out[] = array( $q, $m[1] );
			$q     = null;
		}
	}
	return $out;
}

/** Home FAQ — written for importers; answers stick to facts published by Aria Foods. */
function aria_home_faq() {
	return array(
		array( 'What is the minimum order for Iranian dates?', 'The minimum order is one full 20 ft container, which carries up to about 15 tons; a 40 ft container carries 25 tons or more, depending on product and packing. We do not ship part loads (LCL).' ),
		array( 'Which date varieties can you supply in bulk?', 'Mazafati (Bam), Piarom (Maryam), Zahedi, Sayer (Stamaran), Kabkab, Rabbi, Lulu, Kalute and Khassui, plus date syrup, date paste, date powder, liquid date sugar and chopped dates.' ),
		array( 'What Brix is your date syrup and how is it packed?', 'Date syrup is supplied at Brix 72 to 78 to your order, with no additives except citric acid. Bulk packing is polyethylene gallons and 1,400 kg IBC tanks.' ),
		array( 'Can you pack under our own brand?', 'Yes. Private label packs are available from 250 g to 700 g in flexible bags, pressed packs, vacuum packs, plastic tubs and cartons, with your logo or ours.' ),
		array( 'Which certificates do you hold?', 'Our production and packing are certified to ISO 9001, ISO 22000 and HACCP. Certificates are available to download and we send product documents with every shipment.' ),
		array( 'Can I get a sample before ordering?', 'Yes. Use the sample request form with the products and delivery country, and our export team will arrange it.' ),
	);
}

/**
 * schema.org Product for a B2B catalogue item.
 * An Offer is added only when an indicative price is set (Google requires offers, review or
 * aggregateRating for product rich results; we never invent ratings).
 */
function aria_product_schema( $id, $canon, $desc, $org ) {
	$d      = aria_product_data( $id );
	$images = array();
	foreach ( aria_all_image_ids( $id ) as $img ) {
		$src = wp_get_attachment_image_url( $img, 'large' );
		if ( $src ) {
			$images[] = $src;
		}
	}
	if ( ! $images ) {
		$remote = get_post_meta( $id, '_aria_remote_thumb', true );
		$images = $remote ? array( $remote ) : array( aria_logo_url() );
	}
	$terms = get_the_terms( $id, 'product_cat' );
	$props = array();
	foreach ( aria_specs( $id ) as $row ) {
		if ( preg_match( '/persian|other names|^date name|^product$/i', $row[0] ) ) {
			continue;
		}
		$props[] = array( '@type' => 'PropertyValue', 'name' => $row[0], 'value' => $row[1] );
	}
	$p = array(
		'@type'          => 'Product',
		'@id'            => $canon . '#product',
		'name'           => wp_strip_all_tags( get_the_title( $id ) ),
		'url'            => $canon,
		'description'    => $desc,
		'image'          => $images,
		'brand'          => array( '@type' => 'Brand', 'name' => aria_opt( 'company' ) ),
		'manufacturer'   => array( '@id' => $org ),
		'countryOfOrigin' => array( '@type' => 'Country', 'name' => 'Iran' ),
	);
	if ( $d['sku'] ) {
		$p['sku'] = $d['sku'];
		$p['mpn'] = $d['sku'];
	}
	if ( $terms && ! is_wp_error( $terms ) ) {
		$p['category'] = $terms[0]->name;
	}
	if ( $props ) {
		$p['additionalProperty'] = $props;
	}
	if ( '' !== $d['price'] && (float) $d['price'] > 0 ) {
		$units = aria_price_units();
		$offer = array(
			'@type'              => 'Offer',
			'url'                => $canon,
			'price'              => number_format( (float) $d['price'], 2, '.', '' ),
			'priceCurrency'      => $d['currency'],
			'availability'       => 'https://schema.org/' . $d['availability'],
			'itemCondition'      => 'https://schema.org/NewCondition',
			'seller'             => array( '@id' => $org ),
			'businessFunction'   => 'http://purl.org/goodrelations/v1#Sell',
			'eligibleCustomerType' => 'http://purl.org/goodrelations/v1#Business',
			'priceSpecification' => array(
				'@type'          => 'UnitPriceSpecification',
				'price'          => number_format( (float) $d['price'], 2, '.', '' ),
				'priceCurrency'  => $d['currency'],
				'unitCode'       => $d['unit'],
				'unitText'       => $units[ $d['unit'] ][1] ?? 'ton',
			),
		);
		if ( $d['valid'] ) {
			$offer['priceValidUntil'] = $d['valid'];
		}
		$p['offers'] = $offer;
	}
	return $p;
}

/**
 * Articles copied from ariafoods.co point their canonical to the original, so Google sees
 * one source and the Dubai site is not treated as a copy. Tick "rewritten for this site"
 * in the Search appearance box once an article has its own text.
 */
function aria_source_canonical( $id ) {
	if ( 'post' !== get_post_type( $id ) || ! get_post_meta( $id, '_aria_source_id', true ) || get_post_meta( $id, '_aria_own_canonical', true ) ) {
		return '';
	}
	$path = wp_parse_url( get_permalink( $id ), PHP_URL_PATH );
	return untrailingslashit( aria_opt( 'parent_url' ) ) . $path;
}

/* ---------------------------------------------------------------- Sitemap
 * Core sitemap at /wp-sitemap.xml (/sitemap.xml redirects there). It only lists pages
 * this site wants indexed: no noindex pages, no articles that point their canonical to
 * ariafoods.co, no empty categories, no tags. Every URL gets a lastmod date.
 */
add_filter( 'wp_sitemaps_posts_query_args', function ( $args, $post_type ) {
	$meta = array(
		'relation' => 'AND',
		array( 'key' => '_aria_noindex', 'compare' => 'NOT EXISTS' ),
	);
	if ( 'post' === $post_type ) {
		$meta[] = array(
			'relation' => 'OR',
			array( 'key' => '_aria_source_id', 'compare' => 'NOT EXISTS' ),
			array( 'key' => '_aria_own_canonical', 'compare' => 'EXISTS' ),
		);
	}
	$args['meta_query'] = $meta; // phpcs:ignore
	return $args;
}, 10, 2 );

add_filter( 'wp_sitemaps_posts_entry', function ( $entry, $post ) {
	$entry['lastmod'] = get_post_modified_time( 'c', true, $post );
	return $entry;
}, 10, 2 );

add_filter( 'wp_sitemaps_posts_show_on_front_entry', function ( $entry ) {
	$entry['lastmod'] = gmdate( 'c', strtotime( get_lastpostmodified( 'GMT' ) ) );
	return $entry;
} );

add_filter( 'wp_sitemaps_taxonomies', function ( $tax ) {
	unset( $tax['post_tag'], $tax['post_format'], $tax['category'] );
	return $tax;
} );

add_filter( 'wp_sitemaps_taxonomies_query_args', function ( $args ) {
	$args['hide_empty'] = true;
	return $args;
} );

/* Sitemap is public even while REST is closed; keep it out of Cloudflare's long cache. */
add_action( 'template_redirect', function () {
	if ( get_query_var( 'sitemap' ) || get_query_var( 'sitemap-stylesheet' ) ) {
		header( 'Cache-Control: public, max-age=600' );
	}
}, 0 );
