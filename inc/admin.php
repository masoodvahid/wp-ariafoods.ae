<?php
/**
 * Editor fields: product specifications, packing, FAQ and search appearance.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes', function () {
	foreach ( array( 'post', 'page', 'product' ) as $pt ) {
		add_meta_box( 'aria_seo', __( 'Search appearance & FAQ', 'aria' ), 'aria_box_seo', $pt, 'normal', 'default' );
	}
	add_meta_box( 'aria_product', __( 'Product specifications', 'aria' ), 'aria_box_product', 'product', 'normal', 'high' );
} );

function aria_box_seo( $post ) {
	wp_nonce_field( 'aria_save_meta', 'aria_meta_nonce' );
	$title = get_post_meta( $post->ID, '_aria_seo_title', true );
	$desc  = get_post_meta( $post->ID, '_aria_seo_desc', true );
	$noidx = get_post_meta( $post->ID, '_aria_noindex', true );
	$faq   = get_post_meta( $post->ID, '_aria_faq', true );
	?>
	<p><label for="aria_seo_title"><strong><?php esc_html_e( 'SEO title', 'aria' ); ?></strong> <span class="description"><?php esc_html_e( 'Up to ~60 characters. Empty = page title + brand.', 'aria' ); ?></span></label>
		<input class="widefat" id="aria_seo_title" name="aria_seo_title" value="<?php echo esc_attr( $title ); ?>" maxlength="90" data-count></p>
	<p><label for="aria_seo_desc"><strong><?php esc_html_e( 'Meta description', 'aria' ); ?></strong> <span class="description"><?php esc_html_e( '140–160 characters, written for buyers. Empty = excerpt.', 'aria' ); ?></span></label>
		<textarea class="widefat" id="aria_seo_desc" name="aria_seo_desc" rows="2" maxlength="300" data-count><?php echo esc_textarea( $desc ); ?></textarea></p>
	<p><label><input type="checkbox" name="aria_noindex" value="1" <?php checked( $noidx ); ?>> <?php esc_html_e( 'Hide this page from search engines (noindex)', 'aria' ); ?></label></p>
	<?php if ( get_post_meta( $post->ID, '_aria_source_id', true ) && 'post' === $post->post_type ) : ?>
		<p><label><input type="checkbox" name="aria_own_canonical" value="1" <?php checked( get_post_meta( $post->ID, '_aria_own_canonical', true ) ); ?>> <?php esc_html_e( 'Rewritten for ariafoods.ae (stop pointing the canonical to the original article on ariafoods.co)', 'aria' ); ?></label></p>
	<?php endif; ?>
	<p><label for="aria_faq"><strong><?php esc_html_e( 'FAQ', 'aria' ); ?></strong> <span class="description"><?php esc_html_e( 'One question per pair of lines: "Q: …" then "A: …". Shown on the page and marked up for search.', 'aria' ); ?></span></label>
		<textarea class="widefat code" id="aria_faq" name="aria_faq" rows="6"><?php echo esc_textarea( $faq ); ?></textarea></p>
	<script>document.querySelectorAll('[data-count]').forEach(function(el){var s=document.createElement('span');s.className='description';el.after(s);function u(){s.textContent=' '+el.value.length+' characters';}el.addEventListener('input',u);u();});</script>
	<?php
}

function aria_box_product( $post ) {
	$specs = aria_specs( $post->ID );
	$lines = array();
	foreach ( $specs as $r ) {
		$lines[] = $r[0] . ' | ' . $r[1];
	}
	$packing = get_post_meta( $post->ID, '_aria_packing', true );
	$uses    = get_post_meta( $post->ID, '_aria_uses', true );
	$remote  = get_post_meta( $post->ID, '_aria_remote_thumb', true );
	?>
	<p><label for="aria_specs"><strong><?php esc_html_e( 'Specification table', 'aria' ); ?></strong> <span class="description"><?php esc_html_e( 'One row per line: "Label | Value". Rows named Moisture, Size, Shelf life, Brix are also shown on product cards.', 'aria' ); ?></span></label>
		<textarea class="widefat code" id="aria_specs" name="aria_specs" rows="10"><?php echo esc_textarea( implode( "\n", $lines ) ); ?></textarea></p>
	<p><label for="aria_packing"><strong><?php esc_html_e( 'Packing options', 'aria' ); ?></strong> <span class="description"><?php esc_html_e( 'One per line.', 'aria' ); ?></span></label>
		<textarea class="widefat" id="aria_packing" name="aria_packing" rows="4"><?php echo esc_textarea( $packing ); ?></textarea></p>
	<p><label for="aria_uses"><strong><?php esc_html_e( 'Typical buyers / applications', 'aria' ); ?></strong> <span class="description"><?php esc_html_e( 'One per line.', 'aria' ); ?></span></label>
		<textarea class="widefat" id="aria_uses" name="aria_uses" rows="4"><?php echo esc_textarea( $uses ); ?></textarea></p>
	<p><label for="aria_remote_thumb"><strong><?php esc_html_e( 'Fallback image URL', 'aria' ); ?></strong> <span class="description"><?php esc_html_e( 'Used only when no featured image is set.', 'aria' ); ?></span></label>
		<input class="widefat" id="aria_remote_thumb" name="aria_remote_thumb" value="<?php echo esc_attr( $remote ); ?>"></p>
	<?php
}

add_action( 'save_post', function ( $post_id ) {
	if ( ! isset( $_POST['aria_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aria_meta_nonce'] ) ), 'aria_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_aria_seo_title', sanitize_text_field( wp_unslash( $_POST['aria_seo_title'] ?? '' ) ) );
	update_post_meta( $post_id, '_aria_seo_desc', sanitize_textarea_field( wp_unslash( $_POST['aria_seo_desc'] ?? '' ) ) );
	update_post_meta( $post_id, '_aria_faq', sanitize_textarea_field( wp_unslash( $_POST['aria_faq'] ?? '' ) ) );
	if ( ! empty( $_POST['aria_own_canonical'] ) ) {
		update_post_meta( $post_id, '_aria_own_canonical', 1 );
	} else {
		delete_post_meta( $post_id, '_aria_own_canonical' );
	}
	if ( ! empty( $_POST['aria_noindex'] ) ) {
		update_post_meta( $post_id, '_aria_noindex', 1 );
	} else {
		delete_post_meta( $post_id, '_aria_noindex' );
	}
	if ( 'product' === get_post_type( $post_id ) && isset( $_POST['aria_specs'] ) ) {
		update_post_meta( $post_id, '_aria_specs', aria_parse_spec_lines( sanitize_textarea_field( wp_unslash( $_POST['aria_specs'] ) ) ) );
		update_post_meta( $post_id, '_aria_packing', sanitize_textarea_field( wp_unslash( $_POST['aria_packing'] ?? '' ) ) );
		update_post_meta( $post_id, '_aria_uses', sanitize_textarea_field( wp_unslash( $_POST['aria_uses'] ?? '' ) ) );
		update_post_meta( $post_id, '_aria_remote_thumb', esc_url_raw( wp_unslash( $_POST['aria_remote_thumb'] ?? '' ) ) );
	}
} );

/* ---------- Product data (SKU, indicative price) and gallery ---------- */

add_action( 'add_meta_boxes_product', function () {
	add_meta_box( 'aria_product_data', __( 'Product data', 'aria' ), 'aria_box_product_data', 'product', 'side', 'high' );
	add_meta_box( 'aria_gallery', __( 'Product gallery', 'aria' ), 'aria_box_gallery', 'product', 'side', 'default' );
} );

function aria_box_product_data( $post ) {
	$d = aria_product_data( $post->ID );
	?>
	<p><label for="aria_sku"><strong>SKU</strong></label><input class="widefat" id="aria_sku" name="aria_sku" value="<?php echo esc_attr( $d['sku'] ); ?>" placeholder="e.g. AF-DS-75"></p>
	<p><label for="aria_price"><strong><?php esc_html_e( 'Indicative price (optional)', 'aria' ); ?></strong></label>
		<span style="display:flex;gap:6px"><input id="aria_price" name="aria_price" value="<?php echo esc_attr( $d['price'] ); ?>" inputmode="decimal" style="width:55%" placeholder="1250">
		<select name="aria_currency" style="width:45%"><?php foreach ( array( 'USD', 'EUR', 'AED' ) as $c ) : ?><option <?php selected( $d['currency'], $c ); ?>><?php echo esc_html( $c ); ?></option><?php endforeach; ?></select></span></p>
	<p><label for="aria_unit"><?php esc_html_e( 'Price unit', 'aria' ); ?></label>
		<select class="widefat" id="aria_unit" name="aria_unit"><?php foreach ( aria_price_units() as $k => $u ) : ?><option value="<?php echo esc_attr( $k ); ?>" <?php selected( $d['unit'], $k ); ?>><?php echo esc_html( $u[0] ); ?></option><?php endforeach; ?></select></p>
	<p><label for="aria_valid"><?php esc_html_e( 'Price valid until', 'aria' ); ?></label><input class="widefat" type="date" id="aria_valid" name="aria_valid" value="<?php echo esc_attr( $d['valid'] ); ?>"></p>
	<p><label for="aria_availability"><?php esc_html_e( 'Availability', 'aria' ); ?></label>
		<select class="widefat" id="aria_availability" name="aria_availability"><?php foreach ( aria_availability_options() as $k => $label ) : ?><option value="<?php echo esc_attr( $k ); ?>" <?php selected( $d['availability'], $k ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></p>
	<p><label for="aria_alternatives"><?php esc_html_e( 'Suggest instead (in-stock product slugs, comma separated)', 'aria' ); ?></label><input class="widefat" id="aria_alternatives" name="aria_alternatives" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, '_aria_alternatives', true ) ); ?>" placeholder="zahedi-dates, piarom-dates"><span class="description"><?php esc_html_e( 'Shown when availability is “sourced on request”.', 'aria' ); ?></span></p>
	<p><label><input type="checkbox" name="aria_show_price" value="1" <?php checked( $d['show_price'] ); ?>> <?php esc_html_e( 'Show the price on the product page', 'aria' ); ?></label></p>
	<p class="description"><?php esc_html_e( 'With a price, the Product schema includes an Offer, which Google needs to show product rich results. Without one, the schema still describes the product.', 'aria' ); ?></p>
	<?php
}

function aria_box_gallery( $post ) {
	$ids = aria_gallery_ids( $post->ID );
	?>
	<div id="aria-gallery-box">
		<ul class="aria-gal" style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin:0 0 10px;padding:0;list-style:none">
			<?php foreach ( $ids as $id ) : ?>
				<li data-id="<?php echo (int) $id; ?>" style="position:relative;cursor:move"><?php echo wp_get_attachment_image( $id, 'thumbnail', false, array( 'style' => 'width:100%;height:auto;display:block' ) ); ?><button type="button" class="aria-gal-x" aria-label="<?php esc_attr_e( 'Remove image', 'aria' ); ?>" style="position:absolute;top:2px;right:2px;border:0;background:#fff;border-radius:50%;width:20px;height:20px;line-height:18px;cursor:pointer">×</button></li>
			<?php endforeach; ?>
		</ul>
		<input type="hidden" name="aria_gallery" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>">
		<button type="button" class="button aria-gal-add"><?php esc_html_e( 'Add gallery images', 'aria' ); ?></button>
		<p class="description"><?php esc_html_e( 'The featured image is shown first. Drag to reorder.', 'aria' ); ?></p>
	</div>
	<script>
	jQuery(function($){
		var box=$('#aria-gallery-box'),list=box.find('.aria-gal'),input=box.find('input[name=aria_gallery]'),frame;
		function sync(){input.val(list.children().map(function(){return $(this).data('id');}).get().join(','));}
		list.sortable({update:sync});
		list.on('click','.aria-gal-x',function(){$(this).parent().remove();sync();});
		box.find('.aria-gal-add').on('click',function(){
			if(!frame){frame=wp.media({title:'Product gallery',button:{text:'Add to gallery'},library:{type:'image'},multiple:'add'});
				frame.on('select',function(){frame.state().get('selection').each(function(a){a=a.toJSON();if(list.find('[data-id='+a.id+']').length)return;var u=(a.sizes&&a.sizes.thumbnail||a).url;list.append('<li data-id="'+a.id+'" style="position:relative;cursor:move"><img src="'+u+'" style="width:100%;height:auto;display:block" alt=""><button type="button" class="aria-gal-x" style="position:absolute;top:2px;right:2px;border:0;background:#fff;border-radius:50%;width:20px;height:20px;line-height:18px;cursor:pointer">×</button></li>');});sync();});}
			frame.open();
		});
	});
	</script>
	<?php
}

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && 'product' === get_post_type() ) {
		wp_enqueue_media();
		wp_enqueue_script( 'jquery-ui-sortable' );
	}
} );

add_action( 'save_post_product', function ( $post_id ) {
	if ( ! isset( $_POST['aria_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aria_meta_nonce'] ) ), 'aria_save_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$sku = strtoupper( sanitize_text_field( wp_unslash( $_POST['aria_sku'] ?? '' ) ) );
	if ( $sku ) {
		$dupe = get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'meta_key' => '_aria_sku', 'meta_value' => $sku, 'post__not_in' => array( $post_id ), 'fields' => 'ids', 'numberposts' => 1 ) );
		if ( $dupe ) {
			set_transient( 'aria_sku_dupe_' . get_current_user_id(), $sku . '|' . get_the_title( $dupe[0] ), 60 );
			$sku = '';
		}
	}
	update_post_meta( $post_id, '_aria_sku', $sku );
	$price = preg_replace( '/[^\d.]/', '', (string) wp_unslash( $_POST['aria_price'] ?? '' ) );
	update_post_meta( $post_id, '_aria_price', $price );
	update_post_meta( $post_id, '_aria_currency', in_array( $_POST['aria_currency'] ?? '', array( 'USD', 'EUR', 'AED' ), true ) ? $_POST['aria_currency'] : 'USD' ); // phpcs:ignore
	update_post_meta( $post_id, '_aria_unit', array_key_exists( $_POST['aria_unit'] ?? '', aria_price_units() ) ? $_POST['aria_unit'] : 'TNE' ); // phpcs:ignore
	update_post_meta( $post_id, '_aria_valid', preg_match( '/^\d{4}-\d{2}-\d{2}$/', $_POST['aria_valid'] ?? '' ) ? $_POST['aria_valid'] : '' ); // phpcs:ignore
	update_post_meta( $post_id, '_aria_availability', array_key_exists( $_POST['aria_availability'] ?? '', aria_availability_options() ) ? $_POST['aria_availability'] : 'InStock' ); // phpcs:ignore
	update_post_meta( $post_id, '_aria_show_price', empty( $_POST['aria_show_price'] ) ? 0 : 1 );
	update_post_meta( $post_id, '_aria_alternatives', implode( ',', array_filter( array_map( 'sanitize_title', explode( ',', (string) wp_unslash( $_POST['aria_alternatives'] ?? '' ) ) ) ) ) );
	$ids = array_filter( array_map( 'absint', explode( ',', (string) wp_unslash( $_POST['aria_gallery'] ?? '' ) ) ) );
	update_post_meta( $post_id, '_aria_gallery', array_values( array_unique( $ids ) ) );
} );

add_action( 'admin_notices', function () {
	$d = get_transient( 'aria_sku_dupe_' . get_current_user_id() );
	if ( $d ) {
		delete_transient( 'aria_sku_dupe_' . get_current_user_id() );
		list( $sku, $title ) = explode( '|', $d, 2 );
		echo '<div class="notice notice-error"><p>' . esc_html( sprintf( __( 'SKU %1$s is already used by “%2$s”. The SKU was not saved.', 'aria' ), $sku, $title ) ) . '</p></div>';
	}
} );

/* SKU column and search by SKU in the product list. */
add_filter( 'manage_product_posts_columns', function ( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		if ( 'title' === $k ) {
			$new['aria_thumb'] = '<span class="screen-reader-text">' . esc_html__( 'Image', 'aria' ) . '</span>';
		}
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['aria_sku'] = 'SKU';
		}
	}
	return $new;
} );
add_action( 'manage_product_posts_custom_column', function ( $col, $id ) {
	if ( 'aria_sku' === $col ) {
		echo esc_html( get_post_meta( $id, '_aria_sku', true ) ?: '—' );
	}
	if ( 'aria_thumb' === $col && has_post_thumbnail( $id ) ) {
		echo get_the_post_thumbnail( $id, array( 40, 40 ), array( 'style' => 'width:40px;height:40px;object-fit:cover;border-radius:3px' ) );
	}
}, 10, 2 );
add_action( 'admin_head-edit.php', function () {
	echo '<style>.column-aria_thumb{width:48px}.column-aria_sku{width:120px}</style>';
} );
add_filter( 'posts_search', function ( $search, $q ) {
	global $wpdb;
	if ( is_admin() && $q->is_main_query() && 'product' === $q->get( 'post_type' ) && $q->get( 's' ) ) {
		$like   = '%' . $wpdb->esc_like( $q->get( 's' ) ) . '%';
		$search = preg_replace( '/^\s*AND\s*\(/', $wpdb->prepare( " AND ( {$wpdb->posts}.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_aria_sku' AND meta_value LIKE %s) OR (", $like ), $search, 1 );
		$search = $search ? $search . ')' : $search;
	}
	return $search;
}, 10, 2 );

function aria_parse_spec_lines( $text ) {
	$rows = array();
	foreach ( preg_split( '/\R/', $text ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( 2 === count( $parts ) && '' !== $parts[0] && '' !== $parts[1] ) {
			$rows[] = $parts;
		}
	}
	return $rows;
}

/* Category / product category: SEO title and description. */
foreach ( array( 'category', 'product_cat' ) as $tax ) {
	add_action( $tax . '_edit_form_fields', function ( $term ) {
		?>
		<tr class="form-field"><th><label for="aria_seo_title"><?php esc_html_e( 'SEO title', 'aria' ); ?></label></th><td><input id="aria_seo_title" name="aria_seo_title" value="<?php echo esc_attr( get_term_meta( $term->term_id, 'aria_seo_title', true ) ); ?>"></td></tr>
		<tr class="form-field"><th><label for="aria_seo_desc"><?php esc_html_e( 'Meta description', 'aria' ); ?></label></th><td><textarea id="aria_seo_desc" name="aria_seo_desc" rows="3"><?php echo esc_textarea( get_term_meta( $term->term_id, 'aria_seo_desc', true ) ); ?></textarea></td></tr>
		<?php
	} );
	add_action( 'edited_' . $tax, function ( $term_id ) {
		if ( ! current_user_can( 'manage_categories' ) || ! isset( $_POST['aria_seo_title'] ) ) { // phpcs:ignore -- core verifies the term edit nonce.
			return;
		}
		update_term_meta( $term_id, 'aria_seo_title', sanitize_text_field( wp_unslash( $_POST['aria_seo_title'] ) ) ); // phpcs:ignore
		update_term_meta( $term_id, 'aria_seo_desc', sanitize_textarea_field( wp_unslash( $_POST['aria_seo_desc'] ?? '' ) ) ); // phpcs:ignore
	} );
}

/* Notices. */
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	if ( class_exists( 'WooCommerce' ) ) {
		echo '<div class="notice notice-warning"><p><strong>Aria Foods theme:</strong> ' . esc_html__( 'The theme manages products and inquiries itself. Deactivate WooCommerce so the /product/ pages use the theme catalogue.', 'aria' ) . ' <a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">' . esc_html__( 'Plugins', 'aria' ) . '</a></p></div>';
	}
	if ( ! get_option( 'aria_import_done' ) && ! class_exists( 'WooCommerce' ) ) {
		echo '<div class="notice notice-info"><p><strong>Aria Foods theme:</strong> ' . esc_html__( 'Import products, articles, pages and menus from ariafoods.co.', 'aria' ) . ' <a class="button button-primary" href="' . esc_url( admin_url( 'themes.php?page=aria-import' ) ) . '">' . esc_html__( 'Open importer', 'aria' ) . '</a></p></div>';
	}
} );
