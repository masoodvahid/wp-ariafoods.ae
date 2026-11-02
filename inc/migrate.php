<?php
/**
 * Products → Upgrade products.
 *
 * Brings products created before v1.1 (WooCommerce products, or products from the first
 * version of the importer) into the theme's structure:
 * - WooCommerce SKU, gallery, price and stock status → theme fields
 * - specification table inside the content → Specification sheet (table removed from content)
 * - Yoast title/description → theme SEO fields (only where empty)
 * - missing category, packing, applications, FAQ and SEO text → filled from the theme's defaults
 * - optional: SKU for products without one, gallery from images attached to the product,
 *   download of featured images still loaded from ariafoods.co
 *
 * Runs product by product over AJAX (no timeouts), only fills empty fields, never deletes
 * WooCommerce data, and keeps a copy of the original content so it can be restored.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=product', __( 'Upgrade products', 'aria' ), __( 'Upgrade products', 'aria' ), 'manage_options', 'aria-migrate', 'aria_migrate_page' );
} );

/** Products to look at (any status except trash). */
function aria_migrate_ids() {
	return get_posts( array(
		'post_type'      => 'product',
		'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'orderby'        => 'ID',
		'order'          => 'ASC',
	) );
}

/** What a product still needs. */
function aria_migrate_needs( $id ) {
	$content = (string) get_post_field( 'post_content', $id );
	$n       = array();
	if ( ! get_post_meta( $id, '_aria_sku', true ) && get_post_meta( $id, '_sku', true ) ) {
		$n['woo_sku'] = 1;
	}
	if ( ! get_post_meta( $id, '_aria_gallery', true ) && get_post_meta( $id, '_product_image_gallery', true ) ) {
		$n['woo_gallery'] = 1;
	}
	if ( '' === (string) get_post_meta( $id, '_aria_price', true ) && (float) ( get_post_meta( $id, '_regular_price', true ) ?: get_post_meta( $id, '_price', true ) ) > 0 ) {
		$n['woo_price'] = 1;
	}
	if ( ! aria_specs( $id ) && false !== stripos( $content, '<table' ) ) {
		$n['spec_table'] = 1;
	}
	if ( ! get_post_meta( $id, '_aria_seo_title', true ) && ( get_post_meta( $id, '_yoast_wpseo_title', true ) || get_post_meta( $id, '_yoast_wpseo_metadesc', true ) ) ) {
		$n['yoast'] = 1;
	}
	$terms = get_the_terms( $id, 'product_cat' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		$n['category'] = 1;
	}
	if ( ! get_post_meta( $id, '_aria_sku', true ) && ! get_post_meta( $id, '_sku', true ) ) {
		$n['no_sku'] = 1;
	}
	if ( ! has_post_thumbnail( $id ) && get_post_meta( $id, '_aria_remote_thumb', true ) ) {
		$n['remote_thumb'] = 1;
	}
	$seeds = aria_seed_products();
	$slug  = get_post_field( 'post_name', $id );
	if ( isset( $seeds[ $slug ] ) ) {
		foreach ( array( 'packing', 'uses', 'faq', 'seo_title' ) as $k ) {
			if ( ! empty( $seeds[ $slug ][ $k ] ) && ! get_post_meta( $id, '_aria_' . $k, true ) ) {
				$n['defaults'] = 1;
				break;
			}
		}
	}
	if ( preg_match( '/elementor|et_pb_|vc_row|wp-block-uagb/i', $content ) ) {
		$n['builder'] = 1;
	}
	return $n;
}

function aria_migrate_labels() {
	return array(
		'woo_sku'      => __( 'WooCommerce SKU to copy', 'aria' ),
		'woo_gallery'  => __( 'WooCommerce gallery to copy', 'aria' ),
		'woo_price'    => __( 'WooCommerce price to copy', 'aria' ),
		'spec_table'   => __( 'Specification table inside the text', 'aria' ),
		'yoast'        => __( 'Yoast SEO title/description to copy', 'aria' ),
		'category'     => __( 'No product category', 'aria' ),
		'defaults'     => __( 'Packing / applications / FAQ / SEO text missing', 'aria' ),
		'no_sku'       => __( 'No SKU at all', 'aria' ),
		'remote_thumb' => __( 'Image still loaded from ariafoods.co', 'aria' ),
		'builder'      => __( 'Page-builder markup in the text', 'aria' ),
	);
}

function aria_migrate_page() {
	$ids    = aria_migrate_ids();
	$counts = array_fill_keys( array_keys( aria_migrate_labels() ), 0 );
	$todo   = array();
	foreach ( $ids as $id ) {
		$n = aria_migrate_needs( $id );
		foreach ( $n as $k => $v ) {
			++$counts[ $k ];
		}
		if ( $n ) {
			$todo[] = $id;
		}
	}
	$backups = get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'meta_key' => '_aria_backup_content', 'fields' => 'ids', 'posts_per_page' => -1 ) );
	$nonce   = wp_create_nonce( 'aria_migrate' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Upgrade products to the new structure', 'aria' ); ?></h1>
		<p><?php esc_html_e( 'For products added before this version of the theme. Only empty fields are filled; WooCommerce data stays in the database untouched, and the original text of every changed product is kept so you can restore it.', 'aria' ); ?></p>
		<?php if ( class_exists( 'WooCommerce' ) ) : ?>
			<div class="notice notice-error inline"><p><?php esc_html_e( 'Deactivate WooCommerce first.', 'aria' ); ?></p></div>
		<?php endif; ?>

		<h2><?php echo esc_html( sprintf( _n( '%d product found', '%d products found', count( $ids ), 'aria' ), count( $ids ) ) ); ?></h2>
		<table class="widefat striped" style="max-width:640px">
			<tbody>
			<?php foreach ( aria_migrate_labels() as $k => $label ) : ?>
				<tr><td><?php echo esc_html( $label ); ?></td><td style="width:80px;text-align:right"><strong><?php echo (int) $counts[ $k ]; ?></strong></td></tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( ! $todo ) : ?>
			<p><strong><?php esc_html_e( 'All products already use the new structure.', 'aria' ); ?></strong></p>
		<?php else : ?>
			<h2><?php esc_html_e( 'Options', 'aria' ); ?></h2>
			<form id="aria-mig-form">
				<p><label><input type="checkbox" name="sku" value="1" checked> <?php esc_html_e( 'Create a SKU for products without one (e.g. AF-MAZAFATI)', 'aria' ); ?></label></p>
				<p><label><input type="checkbox" name="attached" value="1" checked> <?php esc_html_e( 'Build a gallery from images attached to the product, when it has none', 'aria' ); ?></label></p>
				<p><label><input type="checkbox" name="download" value="1" checked> <?php esc_html_e( 'Download images still loaded from ariafoods.co into the media library', 'aria' ); ?></label></p>
				<p><label><input type="checkbox" name="clean" value="1" checked> <?php esc_html_e( 'Remove page-builder markup from product text (headings, lists and tables are kept)', 'aria' ); ?></label></p>
				<p><label><input type="checkbox" name="defaults" value="1" checked> <?php esc_html_e( 'Fill missing packing, applications, FAQ and SEO text from the theme defaults', 'aria' ); ?></label></p>
				<p><label><input type="checkbox" name="show_price" value="1"> <?php esc_html_e( 'Show copied WooCommerce prices on product pages (otherwise used only in search schema)', 'aria' ); ?></label></p>
				<p><button type="submit" class="button button-primary button-hero"><?php echo esc_html( sprintf( _n( 'Upgrade %d product', 'Upgrade %d products', count( $todo ), 'aria' ), count( $todo ) ) ); ?></button></p>
			</form>
		<?php endif; ?>

		<progress id="aria-mig-bar" value="0" max="1" style="width:100%;max-width:640px;height:18px;display:none"></progress>
		<ol id="aria-mig-log" style="max-width:900px;font-family:monospace;font-size:12px"></ol>

		<?php if ( $backups ) : ?>
			<hr>
			<h2><?php esc_html_e( 'Restore', 'aria' ); ?></h2>
			<p><?php echo esc_html( sprintf( _n( '%d product has a saved copy of its original text.', '%d products have a saved copy of their original text.', count( $backups ), 'aria' ), count( $backups ) ) ); ?></p>
			<p><button type="button" class="button" id="aria-mig-restore"><?php esc_html_e( 'Restore original product text', 'aria' ); ?></button></p>
		<?php endif; ?>
	</div>
	<script>
	(function(){
		var AJAX=<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,NONCE=<?php echo wp_json_encode( $nonce ); ?>,IDS=<?php echo wp_json_encode( array_map( 'intval', $todo ) ); ?>,BACK=<?php echo wp_json_encode( array_map( 'intval', $backups ) ); ?>;
		var log=document.getElementById('aria-mig-log'),bar=document.getElementById('aria-mig-bar');
		function say(t,bad){var li=document.createElement('li');li.textContent=t;if(bad)li.style.color='#b32d2e';log.appendChild(li);li.scrollIntoView({block:'nearest'});}
		function call(step,id,opts){var fd=new FormData();fd.append('action','aria_migrate');fd.append('_wpnonce',NONCE);fd.append('step',step);fd.append('id',id);Object.keys(opts||{}).forEach(function(k){fd.append(k,opts[k]);});return fetch(AJAX,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();});}
		async function run(step,ids,opts){
			bar.style.display='block';bar.max=ids.length;bar.value=0;
			for(var i=0;i<ids.length;i++){
				try{var j=await call(step,ids[i],opts);if(j.success){say(j.data.message);}else{say('#'+ids[i]+': '+(j.data&&j.data.message||'error'),true);}}catch(e){say('#'+ids[i]+': '+e.message,true);}
				bar.value=i+1;
			}
			await call('finish',0,{});
			say(<?php echo wp_json_encode( __( 'Done. Reload this page to see what is left.', 'aria' ) ); ?>);
		}
		var form=document.getElementById('aria-mig-form');
		if(form){form.addEventListener('submit',function(e){e.preventDefault();var o={};new FormData(form).forEach(function(v,k){o[k]=v;});form.querySelector('button').disabled=true;run('upgrade',IDS,o);});}
		var rb=document.getElementById('aria-mig-restore');
		if(rb){rb.addEventListener('click',function(){if(!confirm(<?php echo wp_json_encode( __( 'Put back the original text of these products? Other upgraded fields stay as they are.', 'aria' ) ); ?>))return;rb.disabled=true;run('restore',BACK,{});});}
	})();
	</script>
	<?php
}

add_action( 'wp_ajax_aria_migrate', function () {
	check_ajax_referer( 'aria_migrate' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Permission denied' ), 403 );
	}
	$step = sanitize_key( $_POST['step'] ?? '' );
	$id   = absint( $_POST['id'] ?? 0 );
	if ( 'finish' === $step ) {
		delete_transient( 'aria_mega' );
		delete_transient( 'aria_product_choices' );
		update_option( 'aria_migrated', time() );
		wp_send_json_success( array( 'message' => 'ok' ) );
	}
	if ( ! $id || 'product' !== get_post_type( $id ) ) {
		wp_send_json_error( array( 'message' => 'Not a product' ) );
	}
	@set_time_limit( 120 ); // phpcs:ignore
	if ( 'restore' === $step ) {
		$orig = get_post_meta( $id, '_aria_backup_content', true );
		if ( '' !== (string) $orig ) {
			wp_update_post( array( 'ID' => $id, 'post_content' => wp_slash( $orig ) ) );
			delete_post_meta( $id, '_aria_backup_content' );
		}
		wp_send_json_success( array( 'message' => get_the_title( $id ) . ' – original text restored' ) );
	}
	$opts = array();
	foreach ( array( 'sku', 'attached', 'download', 'clean', 'defaults', 'show_price' ) as $k ) {
		$opts[ $k ] = ! empty( $_POST[ $k ] );
	}
	wp_send_json_success( array( 'message' => aria_migrate_product( $id, $opts ) ) );
} );

/** Upgrade one product. Returns a one-line summary. */
function aria_migrate_product( $id, $opts ) {
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$done  = array();
	$title = get_the_title( $id );
	$slug  = get_post_field( 'post_name', $id );
	$seeds = aria_seed_products();
	$seed  = $seeds[ $slug ] ?? array();

	// 1. WooCommerce fields.
	$woo_sku = trim( (string) get_post_meta( $id, '_sku', true ) );
	if ( $woo_sku && ! get_post_meta( $id, '_aria_sku', true ) ) {
		update_post_meta( $id, '_aria_sku', strtoupper( $woo_sku ) );
		$done[] = 'SKU ' . strtoupper( $woo_sku );
	}
	$woo_gal = (string) get_post_meta( $id, '_product_image_gallery', true );
	if ( $woo_gal && ! get_post_meta( $id, '_aria_gallery', true ) ) {
		$ids = array_values( array_filter( array_map( 'absint', explode( ',', $woo_gal ) ), 'wp_attachment_is_image' ) );
		if ( $ids ) {
			update_post_meta( $id, '_aria_gallery', $ids );
			$done[] = count( $ids ) . ' gallery image(s)';
		}
	}
	$woo_price = (float) ( get_post_meta( $id, '_regular_price', true ) ?: get_post_meta( $id, '_price', true ) );
	if ( $woo_price > 0 && '' === (string) get_post_meta( $id, '_aria_price', true ) ) {
		update_post_meta( $id, '_aria_price', (string) $woo_price );
		update_post_meta( $id, '_aria_currency', get_option( 'woocommerce_currency' ) && in_array( get_option( 'woocommerce_currency' ), array( 'USD', 'EUR', 'AED' ), true ) ? get_option( 'woocommerce_currency' ) : 'USD' );
		update_post_meta( $id, '_aria_unit', 'TNE' );
		update_post_meta( $id, '_aria_show_price', $opts['show_price'] ? 1 : 0 );
		$done[] = 'price ' . $woo_price;
	}
	$stock = (string) get_post_meta( $id, '_stock_status', true );
	if ( $stock && ! get_post_meta( $id, '_aria_availability', true ) ) {
		$map = array( 'instock' => 'InStock', 'outofstock' => 'OutOfStock', 'onbackorder' => 'PreOrder' );
		update_post_meta( $id, '_aria_availability', $map[ $stock ] ?? 'InStock' );
	}

	// 2. Content: spec table out, builder markup off (original kept once).
	$content  = (string) get_post_field( 'post_content', $id );
	$original = $content;
	if ( ! aria_specs( $id ) ) {
		list( $content, $specs ) = aria_extract_spec_table( $content );
		if ( ! empty( $seed['specs'] ) ) {
			$specs = $seed['specs'];
		}
		if ( $specs ) {
			update_post_meta( $id, '_aria_specs', $specs );
			$done[] = count( $specs ) . ' spec rows';
		}
	}
	if ( $opts['clean'] && preg_match( '/elementor|et_pb_|vc_row|wp-block-uagb|<div|<span|style="/i', $content ) ) {
		$content = aria_clean_html( $content, $title );
		$done[]  = 'text cleaned';
	}
	if ( $opts['download'] ) {
		$n       = 0;
		$content = aria_localise_images( $content, $id, $n );
		if ( $n ) {
			$done[] = $n . ' inline image(s) downloaded';
		}
	}
	if ( $content !== $original ) {
		if ( '' === (string) get_post_meta( $id, '_aria_backup_content', true ) ) {
			update_post_meta( $id, '_aria_backup_content', wp_slash( $original ) );
		}
		wp_update_post( array( 'ID' => $id, 'post_content' => wp_slash( $content ) ) );
	}

	// 3. Images.
	if ( $opts['download'] && ! has_post_thumbnail( $id ) ) {
		$remote = get_post_meta( $id, '_aria_remote_thumb', true );
		if ( $remote ) {
			$att = aria_sideload( $remote, $id, $title );
			if ( $att ) {
				set_post_thumbnail( $id, $att );
				$done[] = 'featured image downloaded';
			}
		}
	}
	if ( $opts['attached'] && ! aria_gallery_ids( $id ) ) {
		$att = get_posts( array( 'post_type' => 'attachment', 'post_mime_type' => 'image', 'post_parent' => $id, 'posts_per_page' => 12, 'fields' => 'ids', 'orderby' => 'menu_order ID', 'order' => 'ASC' ) );
		$att = array_values( array_diff( $att, array( (int) get_post_thumbnail_id( $id ) ) ) );
		if ( $att ) {
			update_post_meta( $id, '_aria_gallery', $att );
			$done[] = count( $att ) . ' attached image(s) to gallery';
		}
	}

	// 4. Category.
	$terms = get_the_terms( $id, 'product_cat' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		$guess = preg_match( '/-dates?$|\bdates?\b/i', $slug . ' ' . $title ) && ! preg_match( '/syrup|paste|powder|sugar|chopped/i', $slug ) ? 'dates' : 'ingredients';
		$t     = get_term_by( 'slug', $guess, 'product_cat' );
		if ( ! $t ) {
			$r = wp_insert_term( 'dates' === $guess ? 'Date varieties' : 'Date ingredients', 'product_cat', array( 'slug' => $guess ) );
			$t = is_wp_error( $r ) ? null : get_term( $r['term_id'] );
		}
		if ( $t ) {
			wp_set_object_terms( $id, array( (int) $t->term_id ), 'product_cat' );
			$done[] = 'category ' . $t->name;
		}
	}

	// 5. SEO: Yoast first, then theme defaults.
	if ( ! get_post_meta( $id, '_aria_seo_title', true ) ) {
		$yt = aria_yoast_vars( (string) get_post_meta( $id, '_yoast_wpseo_title', true ), $id );
		$st = $yt ? $yt : ( $opts['defaults'] ? ( $seed['seo_title'] ?? '' ) : '' );
		if ( $st ) {
			update_post_meta( $id, '_aria_seo_title', $st );
			$done[] = 'SEO title';
		}
	}
	if ( ! get_post_meta( $id, '_aria_seo_desc', true ) ) {
		$yd = aria_yoast_vars( (string) get_post_meta( $id, '_yoast_wpseo_metadesc', true ), $id );
		$sd = $yd ? $yd : ( $opts['defaults'] ? ( $seed['seo_desc'] ?? '' ) : '' );
		if ( $sd ) {
			update_post_meta( $id, '_aria_seo_desc', $sd );
			$done[] = 'meta description';
		}
	}

	// 6. Theme defaults for this product.
	if ( $opts['defaults'] && $seed ) {
		foreach ( array( 'packing' => 'packing', 'uses' => 'applications', 'faq' => 'FAQ' ) as $k => $label ) {
			if ( ! empty( $seed[ $k ] ) && ! get_post_meta( $id, '_aria_' . $k, true ) ) {
				update_post_meta( $id, '_aria_' . $k, $seed[ $k ] );
				$done[] = $label;
			}
		}
		if ( ! has_excerpt( $id ) && ! empty( $seed['excerpt'] ) ) {
			wp_update_post( array( 'ID' => $id, 'post_excerpt' => $seed['excerpt'] ) );
			$done[] = 'summary';
		}
		if ( ! empty( $seed['order'] ) && 0 === (int) get_post_field( 'menu_order', $id ) ) {
			wp_update_post( array( 'ID' => $id, 'menu_order' => (int) $seed['order'] ) );
		}
	}

	// 7. SKU for products without one.
	if ( $opts['sku'] && ! get_post_meta( $id, '_aria_sku', true ) ) {
		$base = 'AF-' . strtoupper( preg_replace( '/[^A-Z0-9]+/i', '-', preg_replace( '/-?dates?$/', '', $slug ) ) );
		$base = rtrim( substr( $base, 0, 24 ), '-' );
		$sku  = $base;
		$n    = 1;
		while ( get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'meta_key' => '_aria_sku', 'meta_value' => $sku, 'fields' => 'ids', 'numberposts' => 1, 'post__not_in' => array( $id ) ) ) ) {
			$sku = $base . '-' . ( ++$n );
		}
		update_post_meta( $id, '_aria_sku', $sku );
		$done[] = 'new SKU ' . $sku;
	}

	update_post_meta( $id, '_aria_migrated', ARIA_VER );
	clean_post_cache( $id );
	return $title . ' – ' . ( $done ? implode( ', ', $done ) : 'nothing to change' );
}

/** Resolve the common Yoast variables; give up on anything else. */
function aria_yoast_vars( $s, $id ) {
	if ( '' === trim( $s ) ) {
		return '';
	}
	$terms = get_the_terms( $id, 'product_cat' );
	$s     = strtr( $s, array(
		'%%title%%'           => get_the_title( $id ),
		'%%sitename%%'        => aria_opt( 'company' ),
		'%%sep%%'             => '|',
		'%%page%%'            => '',
		'%%primary_category%%' => $terms && ! is_wp_error( $terms ) ? $terms[0]->name : '',
		'%%excerpt%%'         => wp_strip_all_tags( get_post_field( 'post_excerpt', $id ) ),
	) );
	if ( false !== strpos( $s, '%%' ) ) {
		return '';
	}
	return trim( preg_replace( '/\s+/', ' ', html_entity_decode( $s, ENT_QUOTES, 'UTF-8' ) ), ' |' );
}

/* Tell the admin once that older products can be upgraded. */
add_action( 'admin_notices', function () {
	if ( get_option( 'aria_migrated' ) || ! current_user_can( 'manage_options' ) || class_exists( 'WooCommerce' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && 'product_page_aria-migrate' === $screen->id ) {
		return;
	}
	$pending = 0;
	foreach ( array_slice( aria_migrate_ids(), 0, 200 ) as $id ) {
		if ( ! get_post_meta( $id, '_aria_migrated', true ) && aria_migrate_needs( $id ) ) {
			++$pending;
		}
	}
	if ( $pending ) {
		echo '<div class="notice notice-warning"><p><strong>Aria Foods theme:</strong> ' . esc_html( sprintf( _n( '%d product was created before the new product structure.', '%d products were created before the new product structure.', $pending, 'aria' ), $pending ) ) . ' <a class="button button-primary" href="' . esc_url( admin_url( 'edit.php?post_type=product&page=aria-migrate' ) ) . '">' . esc_html__( 'Upgrade products', 'aria' ) . '</a></p></div>';
	} else {
		update_option( 'aria_migrated', time() );
	}
} );
