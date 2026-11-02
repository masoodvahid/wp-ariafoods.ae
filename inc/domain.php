<?php
/**
 * Tools → Change domain: replaces an old site address (e.g. modern.ariafoods.co) with the
 * current one everywhere it is stored — menus, page and product content, theme settings,
 * custom fields — safely inside serialized data. Runs table by table in small batches.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

const ARIA_OLD_HOSTS = array( 'modern.ariafoods.co' );

function aria_domain_new_base() {
	return untrailingslashit( home_url() );
}

/** Rows that mention a host, per table group. */
function aria_domain_count( $host ) {
	global $wpdb;
	$like = '%' . $wpdb->esc_like( $host ) . '%';
	return array(
		'posts'    => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE %s OR post_excerpt LIKE %s", $like, $like ) ),
		'postmeta' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE %s", $like ) ),
		'options'  => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_value LIKE %s AND option_name NOT LIKE %s", $like, $wpdb->esc_like( '_transient' ) . '%' ) ),
		'termmeta' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->termmeta} WHERE meta_value LIKE %s", $like ) ),
	);
}

/** Replace in a value that may be serialized (recursively, keeping lengths valid). */
function aria_domain_replace_value( $value, $from, $to ) {
	if ( is_string( $value ) && is_serialized( $value ) ) {
		$data = @unserialize( $value, array( 'allowed_classes' => false ) ); // phpcs:ignore
		if ( false !== $data || 'b:0;' === $value ) {
			return serialize( aria_domain_walk( $data, $from, $to ) ); // phpcs:ignore
		}
	}
	return is_string( $value ) ? str_replace( $from, $to, $value ) : $value;
}

function aria_domain_walk( $data, $from, $to ) {
	if ( is_string( $data ) ) {
		return aria_domain_replace_value( $data, $from, $to );
	}
	if ( is_array( $data ) ) {
		foreach ( $data as $k => $v ) {
			$data[ $k ] = aria_domain_walk( $v, $from, $to );
		}
	}
	return $data;
}

/** The pairs to replace for one old host: with scheme first, then bare host. */
function aria_domain_pairs( $host ) {
	$new  = aria_domain_new_base();
	$nh   = wp_parse_url( $new, PHP_URL_HOST );
	$from = array( 'https://' . $host, 'http://' . $host, 'https:\/\/' . $host, 'http:\/\/' . $host, '//' . $host, $host );
	$to   = array( $new, $new, str_replace( '/', '\/', $new ), str_replace( '/', '\/', $new ), '//' . $nh, $nh );
	return array( $from, $to );
}

/** One batch of one table. Returns number of rows changed. */
function aria_domain_run( $table, $host, $limit = 200 ) {
	global $wpdb;
	$like            = '%' . $wpdb->esc_like( $host ) . '%';
	list( $from, $to ) = aria_domain_pairs( $host );
	$changed         = 0;
	switch ( $table ) {
		case 'posts':
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_content, post_excerpt FROM {$wpdb->posts} WHERE post_content LIKE %s OR post_excerpt LIKE %s LIMIT %d", $like, $like, $limit ) );
			foreach ( $rows as $r ) {
				$wpdb->update( $wpdb->posts, array( 'post_content' => str_replace( $from, $to, $r->post_content ), 'post_excerpt' => str_replace( $from, $to, $r->post_excerpt ) ), array( 'ID' => $r->ID ) );
				clean_post_cache( $r->ID );
				++$changed;
			}
			break;
		case 'postmeta':
		case 'termmeta':
			$t    = 'postmeta' === $table ? $wpdb->postmeta : $wpdb->termmeta;
			$id   = 'postmeta' === $table ? 'meta_id' : 'meta_id';
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT {$id} AS id, meta_value FROM {$t} WHERE meta_value LIKE %s LIMIT %d", $like, $limit ) ); // phpcs:ignore
			foreach ( $rows as $r ) {
				$wpdb->update( $t, array( 'meta_value' => aria_domain_replace_value( $r->meta_value, $from, $to ) ), array( $id => $r->id ) );
				++$changed;
			}
			break;
		case 'options':
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT option_id, option_name, option_value FROM {$wpdb->options} WHERE option_value LIKE %s AND option_name NOT LIKE %s LIMIT %d", $like, $wpdb->esc_like( '_transient' ) . '%', $limit ) );
			foreach ( $rows as $r ) {
				if ( in_array( $r->option_name, array( 'siteurl', 'home' ), true ) ) {
					continue; // Never touch the site address itself here.
				}
				$wpdb->update( $wpdb->options, array( 'option_value' => aria_domain_replace_value( $r->option_value, $from, $to ) ), array( 'option_id' => $r->option_id ) );
				++$changed;
			}
			wp_cache_flush();
			break;
	}
	return $changed;
}

add_action( 'admin_menu', function () {
	add_management_page( __( 'Change domain', 'aria' ), __( 'Change domain', 'aria' ), 'manage_options', 'aria-domain', 'aria_domain_page' );
} );

function aria_domain_page() {
	$new   = aria_domain_new_base();
	$nonce = wp_create_nonce( 'aria_domain' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Change domain', 'aria' ); ?></h1>
		<p><?php echo esc_html( sprintf( __( 'Replaces an old site address with the current one (%s) in menus, content, product fields and theme settings. Links to ariafoods.co are not touched.', 'aria' ), $new ) ); ?></p>
		<form id="aria-dom">
			<p><label for="aria-dom-old"><strong><?php esc_html_e( 'Old address (host only)', 'aria' ); ?></strong></label><br>
				<input id="aria-dom-old" class="regular-text" value="<?php echo esc_attr( ARIA_OLD_HOSTS[0] ); ?>"></p>
			<p><button class="button" type="button" id="aria-dom-scan"><?php esc_html_e( 'Count references', 'aria' ); ?></button>
				<button class="button button-primary" type="submit"><?php esc_html_e( 'Replace with', 'aria' ); ?> <?php echo esc_html( $new ); ?></button></p>
		</form>
		<p class="description"><?php esc_html_e( 'Take a database backup first. Running it again is safe.', 'aria' ); ?></p>
		<ol id="aria-dom-log" style="font-family:monospace;font-size:12px"></ol>
	</div>
	<script>
	(function(){
		var AJAX=<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,NONCE=<?php echo wp_json_encode( $nonce ); ?>,log=document.getElementById('aria-dom-log'),host=document.getElementById('aria-dom-old');
		function say(t){var li=document.createElement('li');li.textContent=t;log.appendChild(li);}
		function call(step,table){var fd=new FormData();fd.append('action','aria_domain');fd.append('_wpnonce',NONCE);fd.append('step',step);fd.append('table',table||'');fd.append('host',host.value.trim());return fetch(AJAX,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();});}
		document.getElementById('aria-dom-scan').addEventListener('click',function(){call('count').then(function(j){say(j.success?JSON.stringify(j.data):(j.data&&j.data.message||'error'));});});
		document.getElementById('aria-dom').addEventListener('submit',async function(e){e.preventDefault();
			for(const t of ['posts','postmeta','termmeta','options']){let n=0,j;do{j=await call('run',t);if(!j.success){say(t+': '+(j.data&&j.data.message||'error'));break;}n+=j.data.changed;}while(j.data.changed>0);say(t+': '+n+' changed');}
			var c=await call('finish');say(c.success?c.data.message:'error');});
	})();
	</script>
	<?php
}

add_action( 'wp_ajax_aria_domain', function () {
	check_ajax_referer( 'aria_domain' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Permission denied' ), 403 );
	}
	$host = strtolower( preg_replace( '#^https?://#', '', trim( (string) wp_unslash( $_POST['host'] ?? '' ), " /\t" ) ) );
	if ( ! preg_match( '/^[a-z0-9.-]+\.[a-z]{2,}$/', $host ) ) {
		wp_send_json_error( array( 'message' => 'Enter a host such as modern.ariafoods.co' ) );
	}
	$cur = wp_parse_url( home_url(), PHP_URL_HOST );
	if ( $host === $cur || 'ariafoods.co' === $host || 'www.ariafoods.co' === $host ) {
		wp_send_json_error( array( 'message' => 'That address must not be replaced.' ) );
	}
	$step = sanitize_key( $_POST['step'] ?? '' );
	if ( 'count' === $step ) {
		wp_send_json_success( aria_domain_count( $host ) );
	}
	if ( 'run' === $step ) {
		$table = sanitize_key( $_POST['table'] ?? '' );
		if ( ! in_array( $table, array( 'posts', 'postmeta', 'termmeta', 'options' ), true ) ) {
			wp_send_json_error( array( 'message' => 'Unknown table' ) );
		}
		wp_send_json_success( array( 'changed' => aria_domain_run( $table, $host ) ) );
	}
	if ( 'finish' === $step ) {
		delete_transient( 'aria_mega' );
		delete_transient( 'aria_product_choices' );
		delete_site_transient( 'aria_gh_release' );
		delete_transient( 'aria_old_host_count' );
		update_option( 'aria_flush_rewrite', 1 );
		wp_cache_flush();
		$left = array_sum( aria_domain_count( $host ) );
		wp_send_json_success( array( 'message' => $left ? "Done, {$left} reference(s) left (check manually)." : 'Done. No references left. If Cloudflare caches pages, purge its cache.' ) );
	}
	wp_send_json_error( array( 'message' => 'Unknown step' ) );
} );

/* Remind the admin while old addresses remain. */
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) || ( isset( $_GET['page'] ) && 'aria-domain' === $_GET['page'] ) ) { // phpcs:ignore
		return;
	}
	$found = get_transient( 'aria_old_host_count' );
	if ( false === $found ) {
		$found = 0;
		foreach ( ARIA_OLD_HOSTS as $h ) {
			if ( wp_parse_url( home_url(), PHP_URL_HOST ) !== $h ) {
				$found += array_sum( aria_domain_count( $h ) );
			}
		}
		set_transient( 'aria_old_host_count', $found, 10 * MINUTE_IN_SECONDS );
	}
	if ( $found ) {
		echo '<div class="notice notice-error"><p><strong>Aria Foods theme:</strong> ' . esc_html( sprintf( __( '%d places (menus, images, links) still point to the old address modern.ariafoods.co, which no longer opens.', 'aria' ), $found ) ) . ' <a class="button button-primary" href="' . esc_url( admin_url( 'tools.php?page=aria-domain' ) ) . '">' . esc_html__( 'Fix now', 'aria' ) . '</a></p></div>';
	}
} );
