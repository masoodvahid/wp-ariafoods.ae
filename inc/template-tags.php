<?php
/**
 * Reusable markup blocks.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

/** Key facts shown on a product card: up to 3 spec values. */
function aria_card_facts( $post_id ) {
	$want = aria_is_variety( $post_id )
		? array( 'Moisture' => 'moist', 'Size' => '^size', 'Shelf life' => 'shelf' )
		: array( 'Brix' => 'brix', 'pH' => '^ph$', 'Moisture' => 'moist', 'Storage' => 'storage' );
	$out  = array();
	foreach ( $want as $label => $pattern ) {
		$v = aria_spec( $post_id, $pattern );
		if ( $v && count( $out ) < 3 ) {
			$out[ $label ] = wp_trim_words( $v, 5, '' );
		}
	}
	return $out;
}

function aria_product_card( $post_id = null, $eager = false ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$facts   = aria_card_facts( $post_id );
	$kind    = aria_is_variety( $post_id ) ? 'dates' : 'ingredients';
	?>
	<article class="pcard" data-kind="<?php echo esc_attr( $kind ); ?>">
		<a class="pcard-link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
			<span class="pcard-media"><?php echo aria_on_request( $post_id ) ? '<span class="pcard-badge">' . esc_html__( 'On request', 'aria' ) . '</span>' : ''; // phpcs:ignore ?><?php echo ( aria_on_request( $post_id ) && ! has_post_thumbnail( $post_id ) && ! get_post_meta( $post_id, '_aria_remote_thumb', true ) ) ? '<span class="onreq-media onreq-media--card">' . aria_icon( 'clock' ) . '<span>' . esc_html__( 'Sourced on request', 'aria' ) . '</span></span>' : aria_product_image( $post_id, 'aria-card', array( 'alt' => get_the_title( $post_id ), 'loading' => $eager ? 'eager' : 'lazy', 'sizes' => '(min-width: 1100px) 280px, (min-width: 700px) 33vw, 50vw' ) ); // phpcs:ignore ?></span>
			<span class="pcard-body">
				<h3 class="pcard-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
				<?php if ( $facts ) : ?>
					<dl class="pcard-facts">
						<?php foreach ( $facts as $k => $v ) : ?>
							<div><dt><?php echo esc_html( $k ); ?></dt><dd><?php echo esc_html( $v ); ?></dd></div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>
			</span>
		</a>
	</article>
	<?php
}

function aria_post_card( $post_id = null, $eager = false ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$cats    = get_the_category( $post_id );
	?>
	<article class="acard">
		<a class="acard-link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
			<span class="acard-media">
				<?php
				if ( has_post_thumbnail( $post_id ) ) {
					echo get_the_post_thumbnail( $post_id, 'aria-wide', array( 'loading' => $eager ? 'eager' : 'lazy', 'sizes' => '(min-width: 1100px) 380px, (min-width: 700px) 50vw, 100vw', 'alt' => '' ) );
				} else {
					echo aria_product_image( $post_id, 'aria-wide', array( 'alt' => '' ) ); // phpcs:ignore
				}
				?>
			</span>
			<span class="acard-body">
				<span class="acard-meta"><?php echo $cats ? esc_html( $cats[0]->name ) . ' · ' : ''; ?><time datetime="<?php echo esc_attr( get_the_date( 'c', $post_id ) ); ?>"><?php echo esc_html( get_the_date( 'M Y', $post_id ) ); ?></time></span>
				<h3 class="acard-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
			</span>
		</a>
	</article>
	<?php
}

/** Page header used on inner pages. */
function aria_page_head( $title, $lead = '', $extra = '' ) {
	?>
	<header class="phead">
		<div class="wrap">
			<?php aria_breadcrumbs(); ?>
			<h1 class="phead-title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( $lead ) : ?>
				<p class="phead-lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>
			<?php echo $extra; // phpcs:ignore -- callers pass escaped markup. ?>
		</div>
	</header>
	<?php
}

/** Contact methods block. */
function aria_contact_list( $class = 'contact-list' ) {
	?>
	<ul class="<?php echo esc_attr( $class ); ?>">
		<li><a href="<?php echo esc_url( aria_wa_url( 'Hello Aria Foods, I would like a wholesale quote.' ) ); ?>" target="_blank" rel="noopener"><?php echo aria_icon( 'whatsapp' ); // phpcs:ignore ?><span><strong><?php esc_html_e( 'WhatsApp trade desk', 'aria' ); ?></strong><?php echo esc_html( aria_opt( 'whatsapp' ) ); ?></span></a></li>
		<li><a href="mailto:<?php echo esc_attr( aria_opt( 'email' ) ); ?>"><?php echo aria_icon( 'mail' ); // phpcs:ignore ?><span><strong><?php esc_html_e( 'Email', 'aria' ); ?></strong><?php echo esc_html( aria_opt( 'email' ) ); ?></span></a></li>
		<li><a href="<?php echo esc_url( aria_tel_url() ); ?>"><?php echo aria_icon( 'phone' ); // phpcs:ignore ?><span><strong><?php esc_html_e( 'Office', 'aria' ); ?></strong><?php echo esc_html( aria_opt( 'phone' ) ); ?></span></a></li>
		<li><span class="contact-static"><?php echo aria_icon( 'pin' ); // phpcs:ignore ?><span><strong><?php esc_html_e( 'Office address', 'aria' ); ?></strong><?php echo esc_html( aria_display_address() ); ?></span></span></li>
	</ul>
	<?php
}

/** Certificates row. */
function aria_cert_row() {
	echo '<ul class="certs">';
	foreach ( aria_certs() as $c ) {
		$url   = (string) $c[2];
		$is_im = (bool) preg_match( '/\.(jpe?g|png|webp)(\?.*)?$/i', $url );
		$thumb = '';
		if ( $is_im ) {
			$t     = preg_replace( '/(\.jpe?g)$/i', '-thumb$1', $url );
			$local = ARIA_DIR . '/assets/certs/' . basename( (string) $t );
			$thumb = ( 0 === strpos( $url, ARIA_URI ) && file_exists( $local ) ) ? $t : $url;
		}
		$media = $thumb
			? '<img class="cert-thumb" src="' . esc_url( $thumb ) . '" alt="' . esc_attr( sprintf( __( 'Aria Foods %s certificate', 'aria' ), $c[0] ) ) . '" width="60" height="84" loading="lazy" decoding="async">'
			: aria_icon( 'shield' );
		echo '<li><a href="' . esc_url( $url ) . '" target="_blank" rel="noopener"' . ( $is_im ? ' data-cert-view' : '' ) . '>' . $media . '<span><strong>' . esc_html( $c[0] ) . '</strong>' . esc_html( $c[1] ) . '</span></a></li>'; // phpcs:ignore
	}
	echo '</ul>';
}

/** FAQ accordion from [q, a] pairs (details/summary — works without JS). */
function aria_faq_block( $items, $title = '' ) {
	if ( ! $items ) {
		return;
	}
	echo '<section class="faq" aria-labelledby="faq-title">';
	echo '<h2 id="faq-title">' . esc_html( $title ? $title : __( 'Questions buyers ask', 'aria' ) ) . '</h2>';
	foreach ( $items as $f ) {
		echo '<details><summary>' . esc_html( $f[0] ) . aria_icon( 'chevron' ) . '</summary><p>' . esc_html( $f[1] ) . '</p></details>'; // phpcs:ignore
	}
	echo '</section>';
}

/** Closing call to action with quote form. */
function aria_quote_band( $product = '', $heading = '' ) {
	?>
	<section class="quote-band" id="quote" aria-labelledby="quote-title">
		<div class="wrap quote-grid">
			<div class="quote-intro">
				<h2 id="quote-title"><?php echo esc_html( $heading ? $heading : __( 'Get a price for your next container', 'aria' ) ); ?></h2>
				<p><?php esc_html_e( 'Tell us the product, volume and destination port. Our export team replies with a price, packing options and a shipping window – usually within one working day.', 'aria' ); ?></p>
				<?php aria_contact_list( 'contact-list contact-list--dark' ); ?>
			</div>
			<div class="quote-form">
				<?php echo aria_form( 'quote', array( 'product' => $product, 'id' => 'form-quote' ) ); // phpcs:ignore ?>
			</div>
		</div>
	</section>
	<?php
}

/** Lines from a textarea meta field. */
function aria_meta_lines( $post_id, $key ) {
	$raw = (string) get_post_meta( $post_id, $key, true );
	return array_values( array_filter( array_map( 'trim', preg_split( '/\R/', $raw ) ) ) );
}

/** Articles related to a product, matched on the product's main word (e.g. "syrup", "Mazafati"). */
function aria_related_posts( $post_id, $limit = 3 ) {
	$slug = get_post_field( 'post_name', $post_id );
	$word = preg_replace( '/-?dates?$|^date-/', '', $slug );
	$word = str_replace( '-', ' ', $word );
	$q    = new WP_Query( array(
		'post_type'           => 'post',
		's'                   => $word,
		'posts_per_page'      => $limit,
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	) );
	if ( $q->post_count < $limit ) {
		$ids  = wp_list_pluck( $q->posts, 'ID' );
		$more = get_posts( array( 'post_type' => 'post', 'posts_per_page' => $limit - $q->post_count, 'post__not_in' => $ids, 'category_name' => 'newsletters' ) );
		$q->posts      = array_merge( $q->posts, $more );
		$q->post_count = count( $q->posts );
	}
	return $q->posts;
}

/** Add ids to h2s and return a table of contents. */
function aria_toc( &$content ) {
	$toc = array();
	$content = preg_replace_callback( '#<h2([^>]*)>(.*?)</h2>#is', function ( $m ) use ( &$toc ) {
		$text = wp_strip_all_tags( $m[2] );
		if ( preg_match( '#id="([^"]+)"#', $m[1], $idm ) ) {
			$id = $idm[1];
			$attrs = $m[1];
		} else {
			$id    = sanitize_title( $text );
			$attrs = $m[1] . ' id="' . esc_attr( $id ) . '"';
		}
		$toc[] = array( $id, $text );
		return '<h2' . $attrs . '>' . $m[2] . '</h2>';
	}, $content );
	return $toc;
}

/** Lite YouTube: thumbnail until click (no YouTube JS on page load). */
function aria_yt_lite( $id, $title = '' ) {
	$id = preg_replace( '/[^\w-]/', '', $id );
	return '<button class="yt yt--short" type="button" data-yt="' . esc_attr( $id ) . '" aria-label="' . esc_attr( sprintf( __( 'Play video: %s', 'aria' ), $title ? $title : 'Aria Foods' ) ) . '"><img src="https://i.ytimg.com/vi/' . esc_attr( $id ) . '/oar2.jpg" onerror="this.onerror=null;this.src=\'https://i.ytimg.com/vi/' . esc_attr( $id ) . '/hqdefault.jpg\'" alt="" loading="lazy" decoding="async" width="270" height="480">' . aria_icon( 'play', 'yt-play' ) . '</button>'; // phpcs:ignore
}

/** Product gallery: main image, thumbnails, full-screen viewer (native <dialog>). */
function aria_product_gallery( $post_id, $title ) {
	$ids = aria_all_image_ids( $post_id );
	echo '<div class="prod-media gallery" data-gallery>';
	if ( ! $ids && ! get_post_meta( $post_id, '_aria_remote_thumb', true ) && aria_on_request( $post_id ) ) {
		echo '<div class="gallery-main"><div class="onreq-media" role="img" aria-label="' . esc_attr( $title ) . '">' . aria_icon( 'clock' ) . '<strong>' . esc_html( $title ) . '</strong><span>' . esc_html__( 'Sourced on request · not in Dubai stock', 'aria' ) . '</span></div></div></div>'; // phpcs:ignore
		return;
	}
	if ( ! $ids ) {
		echo '<div class="gallery-main">' . aria_product_image( $post_id, 'large', array( 'alt' => $title, 'loading' => 'eager', 'fetchpriority' => 'high' ) ) . '</div></div>'; // phpcs:ignore
		return;
	}
	$sizes = '(min-width: 900px) 520px, 100vw';
	$first = $ids[0];
	$alt0  = get_post_meta( $first, '_wp_attachment_image_alt', true ) ?: $title;
	echo '<button type="button" class="gallery-main" data-index="0" aria-label="' . esc_attr__( 'Enlarge image', 'aria' ) . '">';
	echo wp_get_attachment_image( $first, 'large', false, array( 'alt' => $alt0, 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => $sizes, 'class' => 'gallery-img' ) );
	echo '</button>';
	if ( count( $ids ) > 1 ) {
		echo '<ul class="gallery-thumbs" aria-label="' . esc_attr__( 'Product images', 'aria' ) . '">';
		foreach ( $ids as $i => $img ) {
			$alt  = get_post_meta( $img, '_wp_attachment_image_alt', true ) ?: $title;
			$full = wp_get_attachment_image_src( $img, 'full' );
			printf(
				'<li><button type="button" data-index="%d" data-src="%s" data-srcset="%s" data-full="%s" data-alt="%s"%s><span class="screen-reader-text">%s</span>%s</button></li>',
				(int) $i,
				esc_url( wp_get_attachment_image_url( $img, 'large' ) ),
				esc_attr( (string) wp_get_attachment_image_srcset( $img, 'large' ) ),
				esc_url( $full ? $full[0] : '' ),
				esc_attr( $alt ),
				0 === $i ? ' aria-current="true"' : '',
				esc_html( sprintf( __( 'Show image %d', 'aria' ), $i + 1 ) ),
				wp_get_attachment_image( $img, 'thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) )
			);
		}
		echo '</ul>';
	} else {
		$full = wp_get_attachment_image_src( $first, 'full' );
		echo '<span hidden data-full="' . esc_url( $full ? $full[0] : '' ) . '" data-alt="' . esc_attr( $alt0 ) . '"></span>';
	}
	echo '<dialog class="lightbox" aria-label="' . esc_attr( $title ) . '"><img alt=""><button type="button" class="lb-close icon-btn" aria-label="' . esc_attr__( 'Close', 'aria' ) . '">' . aria_icon( 'close' ) . '</button>';
	if ( count( $ids ) > 1 ) {
		echo '<button type="button" class="lb-prev icon-btn" aria-label="' . esc_attr__( 'Previous image', 'aria' ) . '">' . aria_icon( 'chevron' ) . '</button><button type="button" class="lb-next icon-btn" aria-label="' . esc_attr__( 'Next image', 'aria' ) . '">' . aria_icon( 'chevron' ) . '</button>';
	}
	echo '</dialog></div>';
}
