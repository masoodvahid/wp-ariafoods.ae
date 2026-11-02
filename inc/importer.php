<?php
/**
 * One-click importer: English products, articles, pages, menus and logo from ariafoods.co.
 *
 * The browser reads the public REST API of ariafoods.co (WordPress allows cross-origin GET),
 * then sends each item here over admin-ajax. The server cleans the HTML (removes Elementor
 * and page-builder markup), downloads images into the media library and saves the item.
 * Safe to run again: items are matched by slug and updated.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

require_once ARIA_DIR . '/inc/content-seed.php';

const ARIA_SOURCE = 'https://ariafoods.co';

add_action( 'admin_menu', function () {
	add_theme_page( __( 'Import from ariafoods.co', 'aria' ), __( 'Import content', 'aria' ), 'manage_options', 'aria-import', 'aria_import_page' );
} );

function aria_import_page() {
	$nonce = wp_create_nonce( 'aria_import' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import English content from ariafoods.co', 'aria' ); ?></h1>
		<p><?php esc_html_e( 'Copies products (with specifications), articles, the logo and images, then creates the site pages, menus and settings. Arabic and Persian content is skipped. You can run it again; existing items are updated, not duplicated.', 'aria' ); ?></p>
		<?php if ( class_exists( 'WooCommerce' ) ) : ?>
			<div class="notice notice-error inline"><p><?php esc_html_e( 'Deactivate WooCommerce first: the theme needs the "product" type for its own catalogue.', 'aria' ); ?></p></div>
		<?php else : ?>
			<p><button class="button button-primary button-hero" id="aria-run"><?php esc_html_e( 'Start import', 'aria' ); ?></button></p>
		<?php endif; ?>
		<progress id="aria-bar" value="0" max="100" style="width:100%;max-width:640px;height:18px;display:none"></progress>
		<ol id="aria-log" style="max-width:860px;font-family:monospace;font-size:12px"></ol>
	</div>
	<script>
	(function(){
		var btn=document.getElementById('aria-run'); if(!btn) return;
		var log=document.getElementById('aria-log'), bar=document.getElementById('aria-bar');
		var SRC=<?php echo wp_json_encode( ARIA_SOURCE ); ?>, AJAX=<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>, NONCE=<?php echo wp_json_encode( $nonce ); ?>;
		function say(t,bad){var li=document.createElement('li');li.textContent=t;if(bad)li.style.color='#b32d2e';log.appendChild(li);li.scrollIntoView({block:'nearest'});}
		function post(step,data){var fd=new FormData();fd.append('action','aria_import');fd.append('_wpnonce',NONCE);fd.append('step',step);fd.append('data',JSON.stringify(data||{}));return fetch(AJAX,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){if(!j.success)throw new Error(j.data&&j.data.message||'Server error');return j.data;});}
		function get(path){return fetch(SRC+path,{credentials:'omit'}).then(function(r){if(!r.ok)throw new Error(path+' → HTTP '+r.status);return r.json();});}
		var nonLatin=/[؀-ۿ]/;
		btn.addEventListener('click',async function(){
			btn.disabled=true;bar.style.display='block';
			try{
				var cats=await get('/wp-json/wp/v2/categories?per_page=100&_fields=id,name,slug,description');
				var pcats=await get('/wp-json/wp/v2/product_cat?per_page=100&_fields=id,name,slug,description');
				var r=await post('setup',{categories:cats,product_cats:pcats});say(r.message);
				var fields='id,slug,title,content,excerpt,date,modified,featured_media,categories,product_cat,yoast_head_json,_links,_embedded';
				var products=await get('/wp-json/wp/v2/product?per_page=100&_embed=wp:featuredmedia');
				var posts=[];for(var p=1;p<10;p++){var batch=await get('/wp-json/wp/v2/posts?per_page=50&page='+p+'&_embed=wp:featuredmedia').catch(function(){return [];});posts=posts.concat(batch);if(batch.length<50)break;}
				var items=products.map(function(x){return ['product',x];}).concat(posts.map(function(x){return ['post',x];}));
				items=items.filter(function(it){return !nonLatin.test(it[1].title.rendered+it[1].slug);});
				var total=items.length+2,done=1;bar.max=total;bar.value=done;
				say('Found '+products.length+' products and '+posts.length+' articles (English).');
				for(var i=0;i<items.length;i++){
					var it=items[i],x=it[1],m=x._embedded&&x._embedded['wp:featuredmedia']&&x._embedded['wp:featuredmedia'][0];
					var payload={id:x.id,slug:x.slug,title:x.title.rendered,content:x.content.rendered,excerpt:x.excerpt?x.excerpt.rendered:'',date:x.date_gmt||x.date,modified:x.modified,categories:x.categories||[],product_cat:x.product_cat||[],seo_title:x.yoast_head_json&&x.yoast_head_json.title||'',seo_desc:x.yoast_head_json&&x.yoast_head_json.description||'',image:m&&m.source_url||'',image_alt:m&&m.alt_text||''};
					try{var res=await post(it[0],payload);say(res.message);}catch(e){say(it[0]+' '+x.slug+': '+e.message,true);}
					bar.value=++done;
				}
				r=await post('finish',{});say(r.message);bar.value=total;
				say('Done. Visit the site: <?php echo esc_js( home_url( '/' ) ); ?>');
			}catch(e){say(e.message,true);btn.disabled=false;}
		});
	})();
	</script>
	<?php
}

add_action( 'wp_ajax_aria_import', function () {
	check_ajax_referer( 'aria_import' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Permission denied' ), 403 );
	}
	@set_time_limit( 300 ); // phpcs:ignore
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$step = sanitize_key( $_POST['step'] ?? '' );
	$data = json_decode( wp_unslash( $_POST['data'] ?? '{}' ), true ); // phpcs:ignore -- decoded then sanitised per field.
	$data = is_array( $data ) ? $data : array();

	switch ( $step ) {
		case 'setup':
			wp_send_json_success( array( 'message' => aria_import_setup( $data ) ) );
		case 'product':
		case 'post':
			wp_send_json_success( array( 'message' => aria_import_item( $step, $data ) ) );
		case 'finish':
			wp_send_json_success( array( 'message' => aria_import_finish() ) );
	}
	wp_send_json_error( array( 'message' => 'Unknown step' ) );
} );

/** Terms, pages, settings, logo. */
function aria_import_setup( $data ) {
	$map = array();
	foreach ( (array) ( $data['categories'] ?? array() ) as $c ) {
		if ( empty( $c['slug'] ) || 'uncategorized' === $c['slug'] || preg_match( '/[\x{0600}-\x{06FF}]/u', $c['name'] ) ) {
			continue;
		}
		$t = term_exists( $c['slug'], 'category' );
		if ( ! $t ) {
			$t = wp_insert_term( wp_specialchars_decode( $c['name'] ), 'category', array( 'slug' => $c['slug'] ) );
		}
		if ( ! is_wp_error( $t ) ) {
			$map[ (int) $c['id'] ] = (int) $t['term_id'];
		}
	}
	update_option( 'aria_import_catmap', $map, false );

	$pmap = array();
	foreach ( (array) ( $data['product_cats'] ?? array() ) as $c ) {
		if ( empty( $c['slug'] ) || preg_match( '/[\x{0600}-\x{06FF}]/u', $c['name'] ) ) {
			continue;
		}
		$t = term_exists( $c['slug'], 'product_cat' );
		if ( ! $t ) {
			$t = wp_insert_term( wp_specialchars_decode( $c['name'] ), 'product_cat', array( 'slug' => $c['slug'] ) );
		}
		if ( ! is_wp_error( $t ) ) {
			$pmap[ (int) $c['id'] ] = (int) $t['term_id'];
		}
	}
	update_option( 'aria_import_pcatmap', $pmap, false );

	// Readable category names / SEO for the two product groups.
	foreach ( array(
		'dates'       => array( 'Date varieties', 'Iranian Date Varieties – Wholesale Mazafati, Piarom, Zahedi, Sayer | Aria Foods', 'Nine Iranian date varieties for importers and wholesalers, with moisture, size, harvest and shelf-life specifications. Bulk cartons, bags and private label.' ),
		'ingredients' => array( 'Date ingredients', 'Date Ingredients Supplier – Date Syrup, Paste, Powder in Bulk | Aria Foods', 'Date syrup, date paste, date powder, liquid date sugar and chopped dates in bulk for food and beverage manufacturers. Request specifications and a quote.' ),
	) as $slug => $info ) {
		$t = get_term_by( 'slug', $slug, 'product_cat' );
		if ( ! $t ) {
			$r = wp_insert_term( $info[0], 'product_cat', array( 'slug' => $slug ) );
			$t = is_wp_error( $r ) ? null : get_term( $r['term_id'] );
		}
		if ( $t ) {
			wp_update_term( $t->term_id, 'product_cat', array( 'name' => $info[0] ) );
			update_term_meta( $t->term_id, 'aria_seo_title', $info[1] );
			update_term_meta( $t->term_id, 'aria_seo_desc', $info[2] );
		}
	}

	// Pages.
	$made = 0;
	foreach ( aria_seed_pages() as $slug => $p ) {
		$existing = get_page_by_path( $slug );
		$content  = aria_localise_images( $p['content'], 0 );
		$args     = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $p['title'],
			'post_content' => $content,
			'post_excerpt' => $p['excerpt'] ?? '',
		);
		if ( $existing ) {
			$args['ID'] = $existing->ID;
			$id         = wp_update_post( $args );
		} else {
			$id = wp_insert_post( $args );
			++$made;
		}
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_aria_seo_title', $p['seo_title'] ?? '' );
			update_post_meta( $id, '_aria_seo_desc', $p['seo_desc'] ?? '' );
			update_post_meta( $id, '_aria_form', $p['form'] ?? '' );
			if ( ! empty( $p['faq'] ) ) {
				update_post_meta( $id, '_aria_faq', $p['faq'] );
			}
			if ( ! empty( $p['noindex'] ) ) {
				update_post_meta( $id, '_aria_noindex', 1 );
			}
		}
	}

	// Remove WooCommerce leftovers and the sample content.
	foreach ( array( 'shop', 'cart', 'checkout', 'sample-page' ) as $junk ) {
		$pg = get_page_by_path( $junk );
		if ( $pg ) {
			wp_trash_post( $pg->ID );
		}
	}
	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
	if ( $hello ) {
		wp_trash_post( $hello->ID );
	}

	// Reading settings.
	$home = get_page_by_path( 'home' );
	$news = get_page_by_path( 'news' );
	if ( $home && $news ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home->ID );
		update_option( 'page_for_posts', $news->ID );
	}
	update_option( 'posts_per_page', 12 );
	update_option( 'blogname', 'Aria Foods' );
	update_option( 'blogdescription', 'Iranian dates and date ingredients, wholesale and private label' );
	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );

	// Permalinks as on ariafoods.co.
	global $wp_rewrite;
	$wp_rewrite->set_permalink_structure( '/%category%/%postname%/' );
	update_option( 'aria_flush_rewrite', 1 );

	// Logo.
	$logo_msg = '';
	if ( ! get_theme_mod( 'custom_logo' ) ) {
		$logo = aria_sideload( ARIA_SOURCE . '/wp-content/uploads/2020/10/test-logo.png', 0, 'Aria Foods' );
		if ( $logo ) {
			aria_make_logo_transparent( $logo );
			set_theme_mod( 'custom_logo', $logo );
			update_option( 'site_icon', $logo );
			$logo_msg = ' Logo imported.';
		} else {
			$logo_msg = ' Logo could not be downloaded; using the ariafoods.co copy.';
		}
	}

	// Hero photo for the home page.
	if ( ! get_theme_mod( 'aria_hero_image' ) ) {
		$hero = aria_sideload( ARIA_SOURCE . '/wp-content/uploads/2026/10/AriaFoodsAllProducts.jpg', 0, 'Aria Foods dates and date ingredients' );
		if ( $hero ) {
			set_theme_mod( 'aria_hero_image', wp_get_attachment_image_url( $hero, 'aria-hero' ) );
		}
	}

	return sprintf( 'Setup: %d categories, %d new pages, reading settings and permalinks.%s', count( $map ) + count( $pmap ), $made, $logo_msg );
}

/** One product or post. */
function aria_import_item( $type, $d ) {
	$slug = sanitize_title( rawurldecode( $d['slug'] ?? '' ) );
	if ( ! $slug ) {
		return 'Skipped item without slug';
	}
	if ( 'post' === $type && 'premium-date-syrup' === $slug ) {
		return 'Skipped premium-date-syrup (merged into the Date syrup product page with a 301 redirect)';
	}
	$title   = wp_strip_all_tags( html_entity_decode( $d['title'] ?? '', ENT_QUOTES, 'UTF-8' ) );
	$html    = (string) ( $d['content'] ?? '' );
	$specs   = array();
	$seed    = array();

	if ( 'product' === $type ) {
		list( $html, $specs ) = aria_extract_spec_table( $html );
		$seeds = aria_seed_products();
		$seed  = $seeds[ $slug ] ?? array();
		$html  = aria_clean_html( $html, $title );
		if ( ! empty( $seed['append'] ) ) {
			$html = $seed['append'] . "\n" . $html;
		}
		if ( ! empty( $seed['specs'] ) ) {
			$specs = $seed['specs'];
		}
	} else {
		$html = aria_clean_html( $html, $title );
	}

	$existing = get_page_by_path( $slug, OBJECT, $type );
	$excerpt  = ! empty( $seed['excerpt'] ) ? $seed['excerpt'] : wp_strip_all_tags( html_entity_decode( $d['excerpt'] ?? '', ENT_QUOTES, 'UTF-8' ) );
	$excerpt  = trim( preg_replace( '/\s+/', ' ', str_replace( array( '[…]', '[&hellip;]', 'Read More »', 'Read More' ), '', $excerpt ) ) );

	$args = array(
		'post_type'     => $type,
		'post_status'   => 'publish',
		'post_name'     => $slug,
		'post_title'    => $title,
		'post_content'  => '',
		'post_excerpt'  => $excerpt,
		'post_date_gmt' => ! empty( $d['date'] ) ? get_gmt_from_date( $d['date'] ) : current_time( 'mysql', true ),
		'menu_order'    => (int) ( $seed['order'] ?? 50 ),
	);
	if ( ! empty( $d['date'] ) ) {
		$args['post_date'] = get_date_from_gmt( gmdate( 'Y-m-d H:i:s', strtotime( $d['date'] ) ) );
	}
	if ( $existing ) {
		$args['ID'] = $existing->ID;
		$id         = wp_update_post( $args, true );
	} else {
		$id = wp_insert_post( $args, true );
	}
	if ( is_wp_error( $id ) ) {
		return $type . ' ' . $slug . ': ' . $id->get_error_message();
	}

	// Images: featured + inline, into the media library.
	$imgs = 0;
	if ( ! empty( $d['image'] ) && ! has_post_thumbnail( $id ) ) {
		$att = aria_sideload( $d['image'], $id, $d['image_alt'] ? $d['image_alt'] : $title );
		if ( $att ) {
			set_post_thumbnail( $id, $att );
			++$imgs;
		} else {
			update_post_meta( $id, '_aria_remote_thumb', esc_url_raw( $d['image'] ) );
		}
	}
	$html = aria_localise_images( $html, $id, $imgs );
	wp_update_post( array( 'ID' => $id, 'post_content' => $html ) );

	// Taxonomies.
	if ( 'post' === $type ) {
		$cmap = get_option( 'aria_import_catmap', array() );
		$cats = array();
		foreach ( (array) $d['categories'] as $c ) {
			if ( isset( $cmap[ (int) $c ] ) ) {
				$cats[] = $cmap[ (int) $c ];
			}
		}
		if ( $cats ) {
			wp_set_post_categories( $id, $cats );
		}
	} else {
		$pmap  = get_option( 'aria_import_pcatmap', array() );
		$terms = array();
		foreach ( (array) $d['product_cat'] as $c ) {
			if ( isset( $pmap[ (int) $c ] ) ) {
				$terms[] = $pmap[ (int) $c ];
			}
		}
		if ( ! $terms ) {
			$guess = preg_match( '/-dates$/', $slug ) ? 'dates' : 'ingredients';
			$t     = get_term_by( 'slug', $guess, 'product_cat' );
			$terms = $t ? array( $t->term_id ) : array();
		}
		wp_set_object_terms( $id, $terms, 'product_cat' );
		update_post_meta( $id, '_aria_specs', $specs );
		foreach ( array( 'packing', 'uses', 'faq' ) as $k ) {
			if ( ! empty( $seed[ $k ] ) ) {
				update_post_meta( $id, '_aria_' . $k, $seed[ $k ] );
			}
		}
	}

	// SEO: improved text where we wrote it, otherwise the original Yoast fields.
	$seo_t = ! empty( $seed['seo_title'] ) ? $seed['seo_title'] : html_entity_decode( (string) $d['seo_title'], ENT_QUOTES, 'UTF-8' );
	$seo_d = ! empty( $seed['seo_desc'] ) ? $seed['seo_desc'] : html_entity_decode( (string) $d['seo_desc'], ENT_QUOTES, 'UTF-8' );
	update_post_meta( $id, '_aria_seo_title', sanitize_text_field( $seo_t ) );
	update_post_meta( $id, '_aria_seo_desc', sanitize_text_field( $seo_d ) );
	update_post_meta( $id, '_aria_source_id', (int) $d['id'] );

	return sprintf( '%s %s – %s%s', $type, $slug, $existing ? 'updated' : 'created', $imgs ? ', ' . $imgs . ' image(s)' : '' );
}

function aria_import_finish() {
	aria_build_menus();
	update_option( 'aria_import_done', time() );
	delete_transient( 'aria_mega' );
	delete_transient( 'aria_product_choices' );
	flush_rewrite_rules( false );
	return 'Menus created, caches cleared, permalinks refreshed.';
}

/** Create editable WP menus that mirror the theme defaults. */
function aria_build_menus() {
	$menu_name = 'Main menu';
	$menu      = wp_get_nav_menu_object( $menu_name );
	if ( $menu ) {
		wp_delete_nav_menu( $menu->term_id );
	}
	$menu_id = wp_create_nav_menu( $menu_name );
	foreach ( aria_default_menu() as $i => $item ) {
		$parent = wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'    => $item['title'],
			'menu-item-url'      => $item['url'],
			'menu-item-status'   => 'publish',
			'menu-item-classes'  => implode( ' ', $item['classes'] ),
			'menu-item-position' => $i + 1,
		) );
		foreach ( $item['children'] as $j => $c ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'       => $c['title'],
				'menu-item-url'         => $c['url'],
				'menu-item-description' => $c['desc'] ?? '',
				'menu-item-status'      => 'publish',
				'menu-item-parent-id'   => $parent,
				'menu-item-position'    => $j + 1,
			) );
		}
	}
	$locations            = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/** Pull the first spec table (rows of 2 cells) out of product HTML. */
function aria_extract_spec_table( $html ) {
	$specs = array();
	if ( preg_match( '#<table[^>]*>.*?</table>#is', $html, $m ) ) {
		preg_match_all( '#<tr[^>]*>(.*?)</tr>#is', $m[0], $rows );
		foreach ( $rows[1] as $row ) {
			preg_match_all( '#<t[hd][^>]*>(.*?)</t[hd]>#is', $row, $cells );
			$c = array_map( function ( $x ) {
				return trim( preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( $x ), ENT_QUOTES, 'UTF-8' ) ), " \t\n\r\0\x0B\xC2\xA0:" );
			}, $cells[1] );
			if ( count( $c ) >= 2 && '' !== $c[0] && '' !== $c[1] ) {
				$label = ucfirst( strtolower( $c[0] ) );
				$label = str_replace( array( 'Shelf time', 'Season of harvest' ), array( 'Shelf life', 'Harvest season' ), $label );
				$specs[] = array( $label, $c[1] );
			}
		}
		// Only treat it as a spec table if it has typical rows.
		$labels = strtolower( implode( ' ', wp_list_pluck( $specs, 0 ) ) );
		if ( false !== strpos( $labels, 'moist' ) || false !== strpos( $labels, 'persian' ) || false !== strpos( $labels, 'origin' ) ) {
			$html = str_replace( $m[0], '', $html );
		} else {
			$specs = array();
		}
	}
	return array( $html, $specs );
}

/** Strip page-builder markup; keep semantic content. */
function aria_clean_html( $html, $title = '' ) {
	// Lazy-load attributes → real src.
	$html = preg_replace( '#<img([^>]*?)\s(?:data-lazy-src|data-src)="([^"]+)"#i', '<img$1 src="$2"', $html );
	$html = preg_replace( '#\ssrc="data:image[^"]*"#i', '', $html );
	// Remove non-content blocks.
	$html = preg_replace( '#<(script|style|noscript|form|button|svg|select|textarea)\b[^>]*>.*?</\1>#is', '', $html );
	$html = preg_replace( '#<!--.*?-->#s', '', $html );

	$allowed = array(
		'h2' => array( 'id' => true ), 'h3' => array( 'id' => true ), 'h4' => array(), 'h5' => array(),
		'p' => array(), 'br' => array(), 'hr' => array(),
		'ul' => array(), 'ol' => array(), 'li' => array(),
		'strong' => array(), 'b' => array(), 'em' => array(), 'i' => array(), 'sup' => array(), 'sub' => array(),
		'a' => array( 'href' => true, 'title' => true, 'target' => true, 'rel' => true ),
		'img' => array( 'src' => true, 'alt' => true, 'width' => true, 'height' => true ),
		'figure' => array(), 'figcaption' => array(),
		'table' => array(), 'thead' => array(), 'tbody' => array(), 'tr' => array(), 'th' => array( 'colspan' => true, 'rowspan' => true ), 'td' => array( 'colspan' => true, 'rowspan' => true ),
		'blockquote' => array(), 'cite' => array(),
		'iframe' => array( 'src' => true, 'title' => true, 'width' => true, 'height' => true, 'allow' => true, 'allowfullscreen' => true ),
		'h1' => array(), 'h6' => array(),
	);
	$html = wp_kses( $html, $allowed );

	// h1 inside content → h2; h6 → h4.
	$html = preg_replace( '#<(/?)h1>#i', '<$1h2>', $html );
	$html = preg_replace( '#<(/?)h6>#i', '<$1h4>', $html );
	// Only YouTube embeds survive.
	$html = preg_replace_callback( '#<iframe[^>]*src="([^"]+)"[^>]*></iframe>#i', function ( $m ) {
		if ( preg_match( '#(?:youtube\.com/embed/|youtube-nocookie\.com/embed/)([\w-]{11})#', $m[1], $y ) ) {
			return '<div class="yt" data-yt="' . esc_attr( $y[1] ) . '"></div>';
		}
		return '';
	}, $html );
	// Links back to ariafoods.co pages become local; uploads stay for the image step.
	$html = preg_replace( '#https?://(?:www\.)?ariafoods\.co/(?!wp-content/)#i', home_url( '/' ), $html );
	$html = str_replace( '?lang=en', '', $html );
	// Drop the heading that only repeats the title.
	if ( $title ) {
		$t    = preg_quote( wp_strip_all_tags( $title ), '#' );
		$html = preg_replace( '#^\s*(?:<p>\s*)?<(?:strong|b|h2)>\s*' . $t . '\s*</(?:strong|b|h2)>\s*(?:</p>)?#iu', '', $html );
	}
	// Empty paragraphs and leftover whitespace.
	$html = preg_replace( '#<p>(?:\s|&nbsp;|\xC2\xA0|<br\s*/?>)*</p>#iu', '', $html );
	$html = preg_replace( '#<(strong|b|em)>\s*</\1>#i', '', $html );
	$html = preg_replace( "/\n{3,}/", "\n\n", $html );
	// Builder text with no heading/paragraph wrapper: wrap loose lines.
	$html = trim( $html );
	if ( false === stripos( $html, '<p' ) && false === stripos( $html, '<h2' ) ) {
		$html = wpautop( $html );
	}
	return $html;
}

/** Download every ariafoods.co image referenced in HTML and rewrite src. */
function aria_localise_images( $html, $post_id, &$count = 0 ) {
	if ( ! $html ) {
		return $html;
	}
	return preg_replace_callback( '#<img([^>]*?)src="(https?://(?:www\.)?ariafoods\.co/wp-content/uploads/[^"]+)"([^>]*)>#i', function ( $m ) use ( $post_id, &$count ) {
		$alt = '';
		if ( preg_match( '#alt="([^"]*)"#i', $m[0], $a ) ) {
			$alt = $a[1];
		}
		$att = aria_sideload( $m[2], $post_id, $alt );
		if ( ! $att ) {
			return $m[0];
		}
		++$count;
		$img = wp_get_attachment_image( $att, 'large', false, array( 'alt' => $alt, 'loading' => 'lazy', 'decoding' => 'async' ) );
		return $img ? $img : $m[0];
	}, $html );
}

/** Sideload once per source URL (re-runs reuse the same attachment). */
function aria_sideload( $url, $post_id, $alt = '' ) {
	$url = esc_url_raw( preg_replace( '#-\d+x\d+(\.(?:jpe?g|png|webp|gif))$#i', '$1', $url ) );
	if ( ! $url ) {
		return 0;
	}
	$found = get_posts( array(
		'post_type'   => 'attachment',
		'post_status' => 'inherit',
		'meta_key'    => '_aria_source_url',
		'meta_value'  => $url,
		'fields'      => 'ids',
		'numberposts' => 1,
	) );
	if ( $found ) {
		return (int) $found[0];
	}
	$id = media_sideload_image( $url, $post_id, $alt, 'id' );
	if ( is_wp_error( $id ) ) {
		return 0;
	}
	update_post_meta( $id, '_aria_source_url', $url );
	if ( $alt ) {
		update_post_meta( $id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );
	}
	return (int) $id;
}

/** The ariafoods.co logo has a flat light background; turn it transparent (GD). */
function aria_make_logo_transparent( $att_id ) {
	$file = get_attached_file( $att_id );
	if ( ! $file || ! function_exists( 'imagecreatefrompng' ) || ! preg_match( '/\.png$/i', $file ) ) {
		return;
	}
	$im = @imagecreatefrompng( $file ); // phpcs:ignore
	if ( ! $im ) {
		return;
	}
	imagepalettetotruecolor( $im );
	imagealphablending( $im, false );
	imagesavealpha( $im, true );
	$w  = imagesx( $im );
	$h  = imagesy( $im );
	$bg = imagecolorsforindex( $im, imagecolorat( $im, 2, 2 ) );
	if ( $bg['alpha'] > 100 ) {
		imagedestroy( $im );
		return; // Already transparent.
	}
	for ( $y = 0; $y < $h; $y++ ) {
		for ( $x = 0; $x < $w; $x++ ) {
			$c = imagecolorsforindex( $im, imagecolorat( $im, $x, $y ) );
			$d = sqrt( pow( $c['red'] - $bg['red'], 2 ) + pow( $c['green'] - $bg['green'], 2 ) + pow( $c['blue'] - $bg['blue'], 2 ) );
			if ( $d < 70 ) {
				$alpha = (int) round( 127 * ( 1 - $d / 70 ) );
				imagesetpixel( $im, $x, $y, imagecolorallocatealpha( $im, 201, 165, 92, $alpha ) );
			}
		}
	}
	imagepng( $im, $file, 9 );
	imagedestroy( $im );
	wp_update_attachment_metadata( $att_id, wp_generate_attachment_metadata( $att_id, $file ) );
}
