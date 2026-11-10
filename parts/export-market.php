<?php
/**
 * Export market landing page (/export/{country}/). Loaded from page.php.
 * Facts come from aria_export_markets(); the long text is the page content.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

$slug  = aria_export_market_slug();
$m     = aria_export_market( get_the_ID() );
$c     = $m['country'];
$port0 = $m['ports'][0][0];
$wa    = aria_wa_url( sprintf( 'Hello Aria Foods, I am an importer in %s and would like a price for a container of Iranian dates.', $c ) );
$faq   = aria_faq_items( get_the_ID() );
$sea   = 'sea' === $m['mode'];
$road  = 'road' === $m['mode'];
$tshort = $m['transit_short'] ? $m['transit_short'] : __( 'Ask for the current schedule', 'aria' );
?>
<section class="hero hero--market" aria-labelledby="mk-title">
	<?php echo aria_export_flag( $m ); // phpcs:ignore -- built from validated colours. ?>
	<div class="wrap hero-grid">
		<div class="hero-copy">
			<?php aria_breadcrumbs(); ?>
			<p class="kicker kicker--light"><?php echo esc_html( sprintf( __( 'Export to %s', 'aria' ), $c ) ); ?></p>
			<h1 id="mk-title"><?php echo esc_html( $m['h1'] ); ?></h1>
			<p class="hero-lead"><?php echo esc_html( $m['lead'] ); ?></p>
			<div class="hero-ctas">
				<a class="btn btn--primary btn--lg" href="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>" data-quote-open data-port="<?php echo esc_attr( $port0 . ', ' . $c ); ?>"><?php esc_html_e( 'Get a container price', 'aria' ); ?></a>
				<a class="btn btn--ghost-light btn--lg" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener"><?php echo aria_icon( 'whatsapp' ); // phpcs:ignore ?> <?php esc_html_e( 'WhatsApp the trade desk', 'aria' ); ?></a>
			</div>
			<ul class="hero-proof">
				<li><?php esc_html_e( 'From one 20 ft container', 'aria' ); ?></li>
				<li><?php esc_html_e( 'Stock in Dubai', 'aria' ); ?></li>
				<li><?php esc_html_e( 'UAE invoice & banking', 'aria' ); ?></li>
				<li><?php esc_html_e( 'Origin: Iran', 'aria' ); ?></li>
			</ul>
		</div>
		<div class="ticket ship-card">
			<p class="ticket-head"><?php esc_html_e( 'Your shipment', 'aria' ); ?></p>
			<div class="route">
				<div><small><?php esc_html_e( 'Loaded at', 'aria' ); ?></small><strong><?php echo $road ? esc_html__( 'Dubai warehouse', 'aria' ) : 'Jebel Ali, Dubai'; ?></strong></div>
				<span class="route-line" aria-hidden="true"><?php echo aria_icon( $road ? 'truck' : 'ship' ); // phpcs:ignore ?></span>
				<div><small><?php esc_html_e( 'Delivered to', 'aria' ); ?></small><strong><?php echo esc_html( $port0 ); ?></strong></div>
			</div>
			<dl class="ticket-rows">
				<div><dt><?php esc_html_e( 'Minimum order', 'aria' ); ?></dt><dd><?php echo $road ? esc_html__( '1 full truck or 20 ft container (~15 t)', 'aria' ) : esc_html__( '1 × 20 ft container (up to ~15 t)', 'aria' ); ?></dd></div>
				<div><dt><?php esc_html_e( 'Larger orders', 'aria' ); ?></dt><dd><?php esc_html_e( '40 ft (25 t +), several containers per month', 'aria' ); ?></dd></div>
				<div><dt><?php esc_html_e( 'Loading', 'aria' ); ?></dt><dd><?php esc_html_e( 'From Dubai stock, within days of payment', 'aria' ); ?></dd></div>
				<div><dt><?php echo $sea ? esc_html__( 'Sailing time', 'aria' ) : esc_html__( 'Transit time', 'aria' ); ?></dt><dd><?php echo esc_html( $tshort ); ?></dd></div>
				<div><dt><?php esc_html_e( 'Terms', 'aria' ); ?></dt><dd><?php echo esc_html( $m['terms'] ); ?></dd></div>
				<div><dt><?php esc_html_e( 'Payment', 'aria' ); ?></dt><dd><?php esc_html_e( 'TT or LC through UAE banks (USD / AED)', 'aria' ); ?></dd></div>
			</dl>
			<div class="ticket-actions">
				<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>" data-quote-open data-port="<?php echo esc_attr( $port0 . ', ' . $c ); ?>"><?php esc_html_e( 'Quote my container', 'aria' ); ?></a>
			</div>
		</div>
	</div>
</section>

<section class="section" aria-labelledby="why-title">
	<div class="wrap">
		<div class="section-head">
			<h2 id="why-title"><?php echo esc_html( sprintf( __( 'Why %s importers buy from our Dubai warehouse', 'aria' ), $m['demonym'] ) ); ?></h2>
			<p><?php esc_html_e( 'Iranian quality, without the delays and paperwork of buying direct from origin.', 'aria' ); ?></p>
		</div>
		<div class="seg-grid seg-grid--4">
			<div class="seg">
				<?php echo aria_icon( 'container' ); // phpcs:ignore ?>
				<h3><?php esc_html_e( 'Stock ready in Dubai', 'aria' ); ?></h3>
				<p><?php esc_html_e( 'Graded and packed dates are already in our Dubai warehouse. Your container is loaded within days – no waiting for production or origin clearance.', 'aria' ); ?></p>
			</div>
			<div class="seg">
				<?php echo aria_icon( 'bank' ); // phpcs:ignore ?>
				<h3><?php esc_html_e( 'UAE contract and banking', 'aria' ); ?></h3>
				<p><?php esc_html_e( 'Contract and invoice from our UAE company. Pay by TT or letter of credit through UAE banks, in US dollars or dirhams.', 'aria' ); ?></p>
			</div>
			<div class="seg">
				<?php echo aria_icon( 'clock' ); // phpcs:ignore ?>
				<h3><?php esc_html_e( 'Fastest route to your port', 'aria' ); ?></h3>
				<p><?php echo esc_html( $m['transit'] ); ?></p>
			</div>
			<div class="seg">
				<?php echo aria_icon( 'doc' ); // phpcs:ignore ?>
				<h3><?php esc_html_e( 'Documents done before loading', 'aria' ); ?></h3>
				<p><?php esc_html_e( 'Certificate of origin, phytosanitary and health certificates, invoice and packing list are ready before the container leaves.', 'aria' ); ?></p>
			</div>
		</div>
	</div>
</section>

<?php
$loc = is_array( $m['local'] ) ? $m['local'] : array();
if ( ! empty( $loc['title'] ) ) :
	$ldir = ( isset( $loc['dir'] ) && 'rtl' === $loc['dir'] ) ? 'rtl' : 'ltr';
	?>
<section class="section section--tight mk-local-wrap" aria-labelledby="local-title">
	<div class="wrap">
		<div class="mk-local" lang="<?php echo esc_attr( isset( $loc['lang'] ) ? $loc['lang'] : '' ); ?>" dir="<?php echo esc_attr( $ldir ); ?>"<?php echo $m['flag'] ? ' style="--fl1:' . esc_attr( $m['flag'][0] ) . ';--fl3:' . esc_attr( end( $m['flag'] ) ) . '"' : ''; // phpcs:ignore ?>>
			<div class="mk-local-main">
				<?php if ( ! empty( $loc['label'] ) ) : ?><p class="mk-local-tag"><?php echo esc_html( $loc['label'] ); ?></p><?php endif; ?>
				<h2 id="local-title"><?php echo esc_html( $loc['title'] ); ?></h2>
				<?php foreach ( (array) ( isset( $loc['paras'] ) ? $loc['paras'] : array() ) as $para ) : ?>
					<p><?php echo esc_html( $para ); ?></p>
				<?php endforeach; ?>
				<?php if ( ! empty( $loc['cta'] ) ) : ?>
					<p class="mk-local-cta"><button type="button" class="btn btn--primary" data-quote-open><?php echo esc_html( $loc['cta'] ); ?></button></p>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $loc['points'] ) ) : ?>
				<ul class="mk-local-points">
					<?php foreach ( (array) $loc['points'] as $pt ) : ?>
						<li><?php echo aria_icon( 'check' ); // phpcs:ignore ?><span><?php echo esc_html( $pt ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="section section--tint" aria-labelledby="var-title">
	<div class="wrap">
		<div class="section-head section-head--row">
			<div>
				<h2 id="var-title"><?php echo esc_html( sprintf( __( 'What sells in %s', 'aria' ), $c ) ); ?></h2>
				<p><?php esc_html_e( 'Varieties and ingredients we ship by the container, with the container type each one needs.', 'aria' ); ?></p>
			</div>
			<a class="link-more" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>"><?php esc_html_e( 'All products', 'aria' ); ?></a>
		</div>
		<div class="mk-varieties">
			<?php
			foreach ( $m['varieties'] as $v ) :
				$p = get_page_by_path( $v[0], OBJECT, 'product' );
				?>
				<article class="mk-var">
					<?php if ( $p ) : ?>
						<a class="mk-var-media" href="<?php echo esc_url( get_permalink( $p ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo aria_product_image( $p->ID, 'aria-card', array( 'alt' => '', 'loading' => 'lazy', 'sizes' => '(min-width: 900px) 120px, 96px' ) ); // phpcs:ignore ?></a>
					<?php else : ?>
						<span class="mk-var-media" aria-hidden="true"><span class="img-placeholder"></span></span>
					<?php endif; ?>
					<div>
						<h3><?php echo $p ? '<a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( $v[1] ) . '</a>' : esc_html( $v[1] ); // phpcs:ignore ?></h3>
						<p><?php echo esc_html( $v[2] ); ?></p>
						<span class="mk-tag"><?php echo esc_html( $v[3] ); ?></span>
					</div>
					<button class="link-more mk-quote" type="button" data-quote-open data-product="<?php echo esc_attr( $p ? get_the_title( $p ) : $v[1] ); ?>" data-port="<?php echo esc_attr( $port0 . ', ' . $c ); ?>"><?php esc_html_e( 'Quote', 'aria' ); ?></button>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section" aria-labelledby="cont-title">
	<div class="wrap mk-split">
		<div>
			<h2 id="cont-title"><?php esc_html_e( 'Containers and ports', 'aria' ); ?></h2>
			<table class="mk-table">
				<thead><tr><th><?php esc_html_e( 'Container', 'aria' ); ?></th><th><?php esc_html_e( 'Typical load', 'aria' ); ?></th><th><?php esc_html_e( 'Best for', 'aria' ); ?></th></tr></thead>
				<tbody>
					<tr><th>20 ft</th><td><?php esc_html_e( 'up to ~15 t', 'aria' ); ?></td><td><?php esc_html_e( 'First orders, one or two varieties', 'aria' ); ?></td></tr>
					<tr><th>40 ft</th><td><?php esc_html_e( '25 t and more', 'aria' ); ?></td><td><?php esc_html_e( 'Wholesale markets and distributors', 'aria' ); ?></td></tr>
					<?php if ( $road ) : ?><tr><th><?php esc_html_e( 'Full truck', 'aria' ); ?></th><td><?php esc_html_e( 'similar to 20 ft', 'aria' ); ?></td><td><?php esc_html_e( 'Fast delivery by road from Dubai', 'aria' ); ?></td></tr><?php endif; ?>
					<tr><th><?php esc_html_e( 'Reefer', 'aria' ); ?></th><td><?php esc_html_e( '20 ft or 40 ft', 'aria' ); ?></td><td><?php esc_html_e( 'Soft dates such as Mazafati (Kimia)', 'aria' ); ?></td></tr>
				</tbody>
			</table>
			<p class="muted mk-note"><?php esc_html_e( 'Loads depend on variety and packing (5 kg and 10 kg cartons, or retail packs under your label).', 'aria' ); ?></p>
		</div>
		<div>
			<h3 class="mk-sub"><?php echo esc_html( sprintf( $sea ? __( 'Ports we ship to in %s', 'aria' ) : __( 'Where we deliver in %s', 'aria' ), $c ) ); ?></h3>
			<ul class="mk-ports">
				<?php foreach ( $m['ports'] as $p ) : ?>
					<li><?php echo aria_icon( 'pin' ); // phpcs:ignore ?><span><strong><?php echo esc_html( $p[0] ); ?></strong><small><?php echo esc_html( $p[1] ); ?></small></span></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>

<?php if ( ! empty( $m['season'] ) ) : ?>
<section class="mk-season" aria-labelledby="season-title">
	<div class="wrap mk-season-row">
		<?php echo aria_icon( 'clock' ); // phpcs:ignore ?>
		<div>
			<h2 id="season-title"><?php echo esc_html( $m['season']['title'] ); ?></h2>
			<p><?php echo esc_html( $m['season']['text'] ); ?></p>
		</div>
		<a class="btn btn--gold" href="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>" data-quote-open data-port="<?php echo esc_attr( $port0 . ', ' . $c ); ?>"><?php echo false !== stripos( $m['season']['title'], 'Christmas' ) || false === stripos( $m['season']['title'], 'Ramadan' ) ? esc_html__( 'Reserve seasonal stock', 'aria' ) : esc_html__( 'Reserve Ramadan stock', 'aria' ); ?></a>
	</div>
</section>
<?php endif; ?>

<section class="section" aria-labelledby="steps-title">
	<div class="wrap">
		<div class="section-head">
			<h2 id="steps-title"><?php esc_html_e( 'From inquiry to your port', 'aria' ); ?></h2>
		</div>
		<ol class="steps steps--row">
			<li><strong><?php esc_html_e( 'Inquiry', 'aria' ); ?></strong><span><?php esc_html_e( 'Variety, grade, packing, number of containers and port.', 'aria' ); ?></span></li>
			<li><strong><?php esc_html_e( 'Offer in 24 h', 'aria' ); ?></strong><span><?php echo esc_html( sprintf( __( 'Written price (%s) and loading date.', 'aria' ), $m['terms'] ) ); ?></span></li>
			<li><strong><?php esc_html_e( 'Sample', 'aria' ); ?></strong><span><?php esc_html_e( 'Optional – check the grade before you confirm.', 'aria' ); ?></span></li>
			<li><strong><?php esc_html_e( 'Contract & payment', 'aria' ); ?></strong><span><?php esc_html_e( 'UAE contract, TT or LC.', 'aria' ); ?></span></li>
			<li><strong><?php esc_html_e( 'Loading in Dubai', 'aria' ); ?></strong><span><?php esc_html_e( 'From stock, with documents and bill of lading.', 'aria' ); ?></span></li>
		</ol>
	</div>
</section>

<section class="section section--tint" aria-labelledby="docs-title">
	<div class="wrap mk-split">
		<div>
			<h2 id="docs-title"><?php esc_html_e( 'Documents with every container', 'aria' ); ?></h2>
			<ul class="ticks">
				<?php foreach ( $m['docs'] as $d ) : ?>
					<li><?php echo esc_html( $d ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="mk-callout">
			<?php echo aria_icon( 'shield' ); // phpcs:ignore ?>
			<h3><?php echo esc_html( sprintf( __( 'For the importer in %s', 'aria' ), $c ) ); ?></h3>
			<p><?php echo esc_html( $m['buyer'] ); ?></p>
			<?php aria_cert_row(); ?>
		</div>
	</div>
</section>

<?php if ( trim( wp_strip_all_tags( get_the_content() ) ) ) : ?>
<section class="section" aria-label="<?php echo esc_attr( sprintf( __( 'Buying Iranian dates for %s', 'aria' ), $c ) ); ?>">
	<div class="wrap narrow prose"><?php the_content(); ?></div>
</section>
<?php endif; ?>

<?php if ( $faq ) : ?>
<section class="section">
	<div class="wrap narrow">
		<?php aria_faq_block( $faq ); ?>
	</div>
</section>
<?php endif; ?>

<?php
aria_quote_band();
