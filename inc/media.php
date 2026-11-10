<?php
/**
 * Lighter images without plugins:
 * - New uploads: every resized copy (thumbnail, medium, card, hero…) is saved as WebP.
 *   The original file is kept as uploaded.
 * - Media → Optimise images: rebuilds the resized copies of existing images as WebP,
 *   a few at a time, so pages stop loading heavy PNG/JPEG versions.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

function aria_webp_supported() {
	static $ok = null;
	if ( null === $ok ) {
		$ok = wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) );
	}
	return $ok;
}

add_filter( 'image_editor_output_format', function ( $formats ) {
	if ( aria_webp_supported() ) {
		$formats['image/jpeg'] = 'image/webp';
		$formats['image/png']  = 'image/webp';
	}
	return $formats;
} );

add_filter( 'wp_editor_set_quality', function ( $q, $mime ) {
	return 'image/webp' === $mime ? 80 : $q;
}, 10, 2 );

if ( ! is_admin() ) {
	return;
}

add_action( 'admin_menu', function () {
	add_media_page( __( 'Optimise images', 'aria' ), __( 'Optimise images', 'aria' ), 'manage_options', 'aria-images', 'aria_images_page' );
} );

/** Images whose resized copies are not WebP yet. */
function aria_images_todo( $limit = 0 ) {
	global $wpdb;
	$ids = $wpdb->get_col(
		"SELECT p.ID FROM {$wpdb->posts} p
		 JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_wp_attachment_metadata'
		 WHERE p.post_type = 'attachment' AND p.post_mime_type IN ('image/jpeg','image/png')
		 AND m.meta_value NOT LIKE '%image/webp%'
		 ORDER BY p.ID DESC" . ( $limit ? ' LIMIT ' . (int) $limit : '' )
	);
	return array_map( 'intval', $ids );
}

function aria_images_page() {
	$todo = count( aria_images_todo() );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Optimise images', 'aria' ); ?></h1>
		<?php if ( ! aria_webp_supported() ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'This server’s image library cannot write WebP. Ask the host to enable WebP in GD or Imagick, then come back.', 'aria' ); ?></p></div>
		<?php else : ?>
			<p><?php esc_html_e( 'Rebuilds the resized copies of your images (the versions pages actually show) as WebP, usually 60–90% smaller than PNG. Originals are kept. New uploads are converted automatically.', 'aria' ); ?></p>
			<p><strong id="aria-img-left"><?php echo (int) $todo; ?></strong> <?php esc_html_e( 'images to optimise.', 'aria' ); ?></p>
			<p><button class="button button-primary" id="aria-img-go" <?php disabled( 0, $todo ); ?>><?php esc_html_e( 'Optimise now', 'aria' ); ?></button> <span id="aria-img-status"></span></p>
			<p class="description"><?php esc_html_e( 'Keep this page open until it finishes. When done, purge the Cloudflare cache.', 'aria' ); ?></p>
			<script>
			(function(){
				var go=document.getElementById('aria-img-go'),left=document.getElementById('aria-img-left'),st=document.getElementById('aria-img-status');
				var nonce=<?php echo wp_json_encode( wp_create_nonce( 'aria_img' ) ); ?>,saved=0,done=0,errors=0;
				function step(){
					var fd=new FormData();fd.append('action','aria_img_batch');fd.append('_wpnonce',nonce);
					fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json()}).then(function(j){
						if(!j.success){st.textContent=j.data||'Error';go.disabled=false;return;}
						done+=j.data.done;saved+=j.data.saved;errors+=j.data.errors;left.textContent=j.data.left;
						st.textContent=done+' done, '+Math.round(saved/1024)+' KB saved'+(errors?', '+errors+' skipped':'');
						if(j.data.left>0&&j.data.done+j.data.errors>0){step();}else{st.textContent+=' – finished.';}
					}).catch(function(){st.textContent='Network error – click again to continue.';go.disabled=false;});
				}
				go&&go.addEventListener('click',function(){go.disabled=true;st.textContent='Working…';step();});
			})();
			</script>
		<?php endif; ?>
	</div>
	<?php
}

add_action( 'wp_ajax_aria_img_batch', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'aria_img', '_wpnonce', false ) ) {
		wp_send_json_error( 'Not allowed' );
	}
	if ( ! aria_webp_supported() ) {
		wp_send_json_error( 'WebP not supported on this server' );
	}
	@set_time_limit( 120 ); // phpcs:ignore
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$done   = 0;
	$errors = 0;
	$saved  = 0;
	foreach ( aria_images_todo( 4 ) as $id ) {
		$file = get_attached_file( $id );
		$old  = wp_get_attachment_metadata( $id );
		if ( ! $file || ! file_exists( $file ) ) {
			// Mark so it is not picked again.
			$old              = is_array( $old ) ? $old : array();
			$old['aria_skip'] = 'image/webp-missing-file';
			wp_update_attachment_metadata( $id, $old );
			++$errors;
			continue;
		}
		$before = 0;
		if ( ! empty( $old['sizes'] ) ) {
			foreach ( $old['sizes'] as $s ) {
				$p = path_join( dirname( $file ), $s['file'] );
				$before += file_exists( $p ) ? filesize( $p ) : 0;
			}
		}
		$meta = wp_generate_attachment_metadata( $id, $file );
		if ( is_wp_error( $meta ) || empty( $meta ) ) {
			$old              = is_array( $old ) ? $old : array();
			$old['aria_skip'] = 'image/webp-failed';
			wp_update_attachment_metadata( $id, $old );
			++$errors;
			continue;
		}
		$after = 0;
		if ( ! empty( $meta['sizes'] ) ) {
			foreach ( $meta['sizes'] as $s ) {
				$p = path_join( dirname( $file ), $s['file'] );
				$after += file_exists( $p ) ? filesize( $p ) : 0;
			}
		} else {
			$meta['aria_skip'] = 'image/webp-no-sizes'; // Small image: nothing to resize.
		}
		wp_update_attachment_metadata( $id, $meta );
		$saved += max( 0, $before - $after );
		++$done;
	}
	delete_transient( 'aria_mega' );
	wp_send_json_success( array(
		'done'   => $done,
		'errors' => $errors,
		'saved'  => $saved,
		'left'   => count( aria_images_todo() ),
	) );
} );
