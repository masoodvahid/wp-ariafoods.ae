<?php
/**
 * Product page: specification sheet first, story second, quote always within reach.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$id      = get_the_ID();
	$specs   = aria_specs( $id );
	$packing = aria_meta_lines( $id, '_aria_packing' );
	$uses    = aria_meta_lines( $id, '_aria_uses' );
	$faq     = aria_faq_items( $id );
	$title   = get_the_title();
	$content = apply_filters( 'the_content', get_the_content() );
	$toc     = aria_toc( $content );
	$is_var  = aria_is_variety( $id );
	$onreq   = aria_on_request( $id );
	$alts    = $onreq ? aria_alternatives( $id ) : array();
	?>
	<article class="product">
		<header class="prod-head">
			<div class="wrap prod-grid">
				<?php aria_product_gallery( $id, $title ); ?>
				<div class="prod-summary">
					<?php aria_breadcrumbs(); ?>
					<h1 class="prod-title"><?php echo esc_html( $title ); ?></h1>
					<?php
					$pd = aria_product_data( $id );
					if ( $pd['sku'] || ( $pd['show_price'] && $pd['price'] ) ) :
						$units = aria_price_units();
						?>
						<p class="prod-meta">
							<?php if ( $pd['sku'] ) : ?>
								<span>SKU <strong><?php echo esc_html( $pd['sku'] ); ?></strong></span>
							<?php endif; ?>
							<?php if ( $pd['show_price'] && $pd['price'] ) : ?>
								<span><?php esc_html_e( 'Indicative price', 'aria' ); ?> <strong><?php echo esc_html( $pd['currency'] . ' ' . number_format_i18n( (float) $pd['price'], (float) $pd['price'] == (int) $pd['price'] ? 0 : 2 ) ); ?></strong> <?php echo esc_html( $units[ $pd['unit'] ][0] ?? '' ); ?></span>
							<?php endif; ?>
							<span class="avail avail--<?php echo esc_attr( strtolower( $pd['availability'] ) ); ?>"><?php echo esc_html( aria_availability_options()[ $pd['availability'] ] ?? '' ); ?></span>
						</p>
					<?php endif; ?>
					<?php if ( has_excerpt() ) : ?>
						<p class="prod-lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
					<?php
					$facts = aria_card_facts( $id );
					if ( $facts ) :
						?>
						<dl class="prod-facts">
							<?php foreach ( $facts as $k => $v ) : ?>
								<div><dt><?php echo esc_html( $k ); ?></dt><dd><?php echo esc_html( $v ); ?></dd></div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
					<?php if ( $onreq ) : ?>
						<div class="onreq" role="note">
							<p class="onreq-tag"><?php echo aria_icon( 'clock' ); // phpcs:ignore ?> <?php esc_html_e( 'Not in our Dubai stock – sourced on request', 'aria' ); ?></p>
							<p><?php echo esc_html( sprintf( __( 'We don’t hold %s in our Dubai warehouse right now, but we can source it for you. Tell us grade, packing and volume and we confirm availability, price and lead time.', 'aria' ), $title ) ); ?></p>
							<?php if ( $alts ) : ?>
								<p class="onreq-alt"><strong><?php esc_html_e( 'Need dates now?', 'aria' ); ?></strong>
									<?php
									$links = array_map( function ( $a ) {
										return '<a href="' . esc_url( get_permalink( $a ) ) . '">' . esc_html( get_the_title( $a ) ) . '</a>';
									}, $alts );
									/* translators: 1: product, 2: in-stock alternatives */
									echo wp_kses_post( sprintf( __( 'Instead of %1$s we can load %2$s from our Dubai warehouse within days.', 'aria' ), esc_html( $title ), implode( ' ' . esc_html__( 'or', 'aria' ) . ' ', $links ) ) );
									echo ' <a href="#stock-title">' . esc_html__( 'Compare →', 'aria' ) . '</a>';
									?>
								</p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<div class="hero-ctas">
						<a class="btn btn--primary btn--lg" href="#quote" data-quote-open data-product="<?php echo esc_attr( $title ); ?>"><?php echo $onreq ? esc_html__( 'Ask us to source it', 'aria' ) : esc_html__( 'Request a quote', 'aria' ); ?></a>
						<a class="btn btn--outline btn--lg" href="<?php echo esc_url( aria_wa_url( sprintf( 'Hello Aria Foods, I would like a price for %s.', $title ) ) ); ?>" target="_blank" rel="noopener"><?php echo aria_icon( 'whatsapp' ); // phpcs:ignore ?> WhatsApp</a>
					</div>
					<p class="prod-note"><a href="<?php echo esc_url( home_url( '/sample-request/' ) ); ?>"><?php esc_html_e( 'Request a sample', 'aria' ); ?></a> · <a href="<?php echo esc_url( aria_opt( 'catalogue' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Catalogue (PDF)', 'aria' ); ?></a></p>
				</div>
			</div>
		</header>

		<div class="wrap prod-body">
			<aside class="prod-aside">
				<?php if ( $specs ) : ?>
					<section class="spec-sheet" aria-labelledby="spec-title">
						<h2 id="spec-title"><?php esc_html_e( 'Specification', 'aria' ); ?></h2>
						<table>
							<tbody>
								<?php foreach ( $specs as $r ) : ?>
									<tr><th scope="row"><?php echo esc_html( $r[0] ); ?></th><td><?php echo esc_html( $r[1] ); ?></td></tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</section>
				<?php endif; ?>
				<?php if ( $packing ) : ?>
					<section class="side-box">
						<h2><?php esc_html_e( 'Packing', 'aria' ); ?></h2>
						<ul class="ticks">
							<?php foreach ( $packing as $l ) : ?>
								<li><?php echo esc_html( $l ); ?></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>
				<section class="side-box">
					<h2><?php esc_html_e( 'Order volumes', 'aria' ); ?></h2>
					<ul class="ticks">
						<?php foreach ( aria_quantity_options() as $q ) : ?>
							<li><?php echo esc_html( $q ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			</aside>

			<div class="prod-main">
				<?php if ( $uses ) : ?>
					<section class="uses">
						<h2><?php echo $is_var ? esc_html__( 'Who buys it', 'aria' ) : esc_html__( 'Applications', 'aria' ); ?></h2>
						<ul class="chips">
							<?php foreach ( $uses as $u ) : ?>
								<li><?php echo esc_html( $u ); ?></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>
				<?php if ( count( $toc ) > 3 ) : ?>
					<nav class="toc" aria-label="<?php esc_attr_e( 'On this page', 'aria' ); ?>">
						<p><?php esc_html_e( 'On this page', 'aria' ); ?></p>
						<ol>
							<?php foreach ( $toc as $t ) : ?>
								<li><a href="#<?php echo esc_attr( $t[0] ); ?>"><?php echo esc_html( $t[1] ); ?></a></li>
							<?php endforeach; ?>
						</ol>
					</nav>
				<?php endif; ?>
				<div class="prose">
					<?php echo $content; // phpcs:ignore -- filtered post content. ?>
				</div>
				<?php aria_faq_block( $faq, sprintf( __( '%s: questions buyers ask', 'aria' ), $title ) ); ?>
			</div>
		</div>

		<?php if ( $onreq ) :
			$notes = (array) get_post_meta( $id, '_aria_alt_notes', true );
			$stock = array_values( array_diff( aria_in_stock_dates(), $alts ) );
			?>
			<section class="section section--tint stock-alt" aria-labelledby="stock-title">
				<div class="wrap">
					<div class="section-head">
						<p class="kicker"><?php esc_html_e( 'Ready in Dubai', 'aria' ); ?></p>
						<h2 id="stock-title"><?php echo esc_html( sprintf( __( 'Need dates now? Ship from our Dubai stock instead of %s', 'aria' ), $title ) ); ?></h2>
					</div>

					<div class="sa-compare">
						<div class="sa-opt sa-opt--wait">
							<span class="sa-ic"><?php echo aria_icon( 'clock' ); // phpcs:ignore ?></span>
							<div>
								<h3><?php echo esc_html( sprintf( __( '%s – sourced on request', 'aria' ), $title ) ); ?></h3>
								<ul>
									<li><?php esc_html_e( 'We check availability with growers and packers in Iran', 'aria' ); ?></li>
									<li><?php esc_html_e( 'Price and lead time confirmed per order', 'aria' ); ?></li>
									<li><?php esc_html_e( 'Best for planned orders with time to spare', 'aria' ); ?></li>
								</ul>
							</div>
						</div>
						<div class="sa-opt sa-opt--now">
							<span class="sa-ic"><?php echo aria_icon( 'container' ); // phpcs:ignore ?></span>
							<div>
								<h3><?php esc_html_e( 'From our Dubai warehouse', 'aria' ); ?></h3>
								<ul>
									<li><?php esc_html_e( 'Already graded and packed – no waiting for production', 'aria' ); ?></li>
									<li><?php esc_html_e( 'Loaded within a few working days of payment', 'aria' ); ?></li>
									<li><?php esc_html_e( 'Fixed price, UAE invoice, documents ready before loading', 'aria' ); ?></li>
								</ul>
							</div>
						</div>
					</div>

					<?php if ( $alts ) : ?>
						<h3 class="sa-sub"><?php echo esc_html( sprintf( __( 'Closest to %s', 'aria' ), $title ) ); ?></h3>
						<div class="sa-alts">
							<?php foreach ( $alts as $a ) :
								$aslug = get_post_field( 'post_name', $a );
								$atitle = get_the_title( $a );
								$note  = isset( $notes[ $aslug ] ) ? $notes[ $aslug ] : get_the_excerpt( $a );
								$af    = aria_card_facts( $a );
								?>
								<article class="sa-card">
									<a class="sa-media" href="<?php echo esc_url( get_permalink( $a ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo aria_product_image( $a, 'aria-card', array( 'alt' => '' ) ); // phpcs:ignore ?></a>
									<div class="sa-body">
										<p class="sa-flag"><?php echo aria_icon( 'check' ); // phpcs:ignore ?> <?php esc_html_e( 'In Dubai stock', 'aria' ); ?></p>
										<h4><a href="<?php echo esc_url( get_permalink( $a ) ); ?>"><?php echo esc_html( $atitle ); ?></a></h4>
										<p class="sa-why"><?php echo esc_html( $note ); ?></p>
										<?php if ( $af ) : ?>
											<dl class="sa-facts"><?php foreach ( $af as $k => $v ) : ?><div><dt><?php echo esc_html( $k ); ?></dt><dd><?php echo esc_html( $v ); ?></dd></div><?php endforeach; ?></dl>
										<?php endif; ?>
										<p class="sa-ctas">
											<button type="button" class="btn btn--primary" data-quote-open data-product="<?php echo esc_attr( $atitle ); ?>"><?php esc_html_e( 'Get a price', 'aria' ); ?></button>
											<a class="btn btn--outline" href="<?php echo esc_url( get_permalink( $a ) ); ?>"><?php esc_html_e( 'View product', 'aria' ); ?></a>
										</p>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ( $stock ) : ?>
						<h3 class="sa-sub"><?php esc_html_e( 'All date varieties in Dubai stock', 'aria' ); ?></h3>
						<ul class="sa-list">
							<?php foreach ( $stock as $sid ) :
								$sf = aria_card_facts( $sid );
								?>
								<li><a href="<?php echo esc_url( get_permalink( $sid ) ); ?>">
									<span class="sa-thumb"><?php echo aria_product_image( $sid, 'thumbnail', array( 'alt' => '' ) ); // phpcs:ignore ?></span>
									<span><strong><?php echo esc_html( get_the_title( $sid ) ); ?></strong><?php if ( isset( $sf['Moisture'] ) ) : ?><small><?php echo esc_html( __( 'Moisture', 'aria' ) . ' ' . $sf['Moisture'] ); ?></small><?php endif; ?></span>
								</a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<p class="sa-note"><?php esc_html_e( 'Not sure which to choose? Send us your market and use – our trade desk recommends the closest match and can send samples.', 'aria' ); ?> <a href="<?php echo esc_url( aria_wa_url( sprintf( 'Hello Aria Foods, I was looking for %s. Which date from your Dubai stock is closest?', $title ) ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Ask on WhatsApp', 'aria' ); ?></a></p>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$related = $onreq ? array() : get_posts( array(
			'post_type'      => 'product',
			'posts_per_page' => 4,
			'post__not_in'   => array( $id ),
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'tax_query'      => array( array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $is_var ? 'dates' : 'ingredients' ) ),
		) );
		if ( $related ) :
			?>
			<section class="section section--tint" aria-labelledby="rel-title">
				<div class="wrap">
					<div class="section-head"><h2 id="rel-title"><?php echo $is_var ? esc_html__( 'Other date varieties', 'aria' ) : esc_html__( 'Other date ingredients', 'aria' ); ?></h2></div>
					<div class="pgrid pgrid--4">
						<?php
						foreach ( $related as $r ) {
							aria_product_card( $r->ID );
						}
						?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$reading = aria_related_posts( $id, 3 );
		if ( $reading ) :
			?>
			<section class="section" aria-labelledby="read-title">
				<div class="wrap">
					<div class="section-head"><h2 id="read-title"><?php esc_html_e( 'Further reading', 'aria' ); ?></h2></div>
					<div class="agrid">
						<?php
						foreach ( $reading as $p ) {
							aria_post_card( $p->ID );
						}
						?>
					</div>
				</div>
			</section>
		<?php endif; ?>
	</article>
	<?php
	aria_quote_band( $title, sprintf( __( 'Get a price for %s', 'aria' ), $title ) );
endwhile;

get_footer();
