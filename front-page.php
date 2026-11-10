<?php
/**
 * Home page.
 *
 * Structure drawn from UAE competitors (Bateel: category-first navigation; Al Foah: key
 * facts + certification row; Al Barakah: ingredients for manufacturers + private label;
 * Emirates Delight: buyer segments; Tulsidas: WhatsApp-first B2B CTAs, FAQ, closing CTA),
 * rebuilt around what a wholesale buyer needs to decide: spec, packing, volume, trust.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;
get_header();

$prod = function ( $slug ) {
	return get_page_by_path( $slug, OBJECT, 'product' );
};
$syrup  = $prod( 'date-syrup' );
$hero   = aria_opt( 'hero_image' );
$pics   = (string) aria_opt( 'hero_products' );
if ( $pics && 0 === strpos( $pics, '/' ) ) {
	$pics = home_url( $pics );
}
?>

<section class="hero<?php echo $pics ? ' hero--visual' : ''; ?>" aria-labelledby="hero-title">
	<div class="wrap hero-grid">
		<div class="hero-copy">
			<h1 id="hero-title"><?php esc_html_e( 'Iranian dates and date syrup, supplied by the container', 'aria' ); ?></h1>
			<?php if ( ! $pics ) : ?>
			<p class="hero-lead"><?php esc_html_e( 'Nine date varieties and five date ingredients for importers, wholesalers and food manufacturers – packed in bulk or under your own label, shipped by the full container – from one 20 ft to multi-container orders.', 'aria' ); ?></p>
			<div class="hero-ctas">
				<a class="btn btn--primary btn--lg" href="#quote" data-quote-open><?php esc_html_e( 'Request a quote', 'aria' ); ?></a>
				<a class="btn btn--ghost-light btn--lg" href="<?php echo esc_url( aria_wa_url( 'Hello Aria Foods, I would like a wholesale quote.' ) ); ?>" target="_blank" rel="noopener"><?php echo aria_icon( 'whatsapp' ); // phpcs:ignore ?> <?php esc_html_e( 'WhatsApp the trade desk', 'aria' ); ?></a>
			</div>
			<?php endif; ?>
			<ul class="hero-proof">
				<li>ISO 9001</li><li>ISO 22000</li><li>HACCP</li><li><?php esc_html_e( 'Exporting to five continents', 'aria' ); ?></li>
			</ul>
		</div>

		<?php if ( $pics ) : ?>
		<div class="hero-visual">
			<img src="<?php echo esc_url( $pics ); ?>" alt="<?php esc_attr_e( 'Aria Foods range: date syrup, date paste, Mazafati and Piarom dates, gift boxes and bulk packs', 'aria' ); ?>" width="1774" height="887" fetchpriority="high" decoding="async">
		</div>
		<?php endif; ?>
	</div>
	<?php if ( ! $pics && $hero ) : ?>
		<picture class="hero-photo"><source media="(max-width: 899px)" srcset="data:image/gif;base64,R0lGODlhAQABAAAAACw="><img src="<?php echo esc_url( $hero ); ?>" alt="" fetchpriority="high" decoding="async"></picture>
	<?php endif; ?>
</section>

<section class="facts" aria-label="<?php esc_attr_e( 'Company facts', 'aria' ); ?>">
	<div class="wrap facts-row">
		<p><strong><?php esc_html_e( 'Three factories', 'aria' ); ?></strong> <?php esc_html_e( 'for sorting, washing, processing and packing', 'aria' ); ?></p>
		<p><strong><?php esc_html_e( '30+ years', 'aria' ); ?></strong> <?php esc_html_e( 'of family experience in the date trade', 'aria' ); ?></p>
		<p><strong><?php esc_html_e( '20 ft to 40 ft', 'aria' ); ?></strong> <?php esc_html_e( 'full containers – minimum one 20 ft', 'aria' ); ?></p>
		<p><strong><?php esc_html_e( 'One export desk', 'aria' ); ?></strong> <?php esc_html_e( 'for transport, customs and documents', 'aria' ); ?></p>
	</div>
</section>

<section class="section segments" aria-labelledby="seg-title">
	<div class="wrap">
		<div class="section-head">
			<h2 id="seg-title"><?php esc_html_e( 'Who we supply', 'aria' ); ?></h2>
			<p><?php esc_html_e( 'We sell only to businesses. Choose the route that matches how you buy.', 'aria' ); ?></p>
		</div>
		<div class="seg-grid">
			<a class="seg" href="<?php echo esc_url( home_url( '/ariafoods-bulk-service/' ) ); ?>">
				<?php echo aria_icon( 'ship' ); // phpcs:ignore ?>
				<h3><?php esc_html_e( 'Importers and wholesalers', 'aria' ); ?></h3>
				<p><?php esc_html_e( 'Whole dates in 5–10 kg cartons and 30 kg bags for fruit markets, distributors and repackers.', 'aria' ); ?></p>
				<span class="link-more"><?php esc_html_e( 'Bulk & food service', 'aria' ); ?></span>
			</a>
			<a class="seg" href="<?php echo esc_url( get_term_link( 'ingredients', 'product_cat' ) && ! is_wp_error( get_term_link( 'ingredients', 'product_cat' ) ) ? get_term_link( 'ingredients', 'product_cat' ) : home_url( '/dates-products/' ) ); ?>">
				<?php echo aria_icon( 'flask' ); // phpcs:ignore ?>
				<h3><?php esc_html_e( 'Food and beverage manufacturers', 'aria' ); ?></h3>
				<p><?php esc_html_e( 'Date syrup, paste, powder, liquid date sugar and chopped dates to replace refined sugar in your recipes.', 'aria' ); ?></p>
				<span class="link-more"><?php esc_html_e( 'Date ingredients', 'aria' ); ?></span>
			</a>
			<a class="seg" href="<?php echo esc_url( home_url( '/customer-packing/' ) ); ?>">
				<?php echo aria_icon( 'tag' ); // phpcs:ignore ?>
				<h3><?php esc_html_e( 'Brands and retail chains', 'aria' ); ?></h3>
				<p><?php esc_html_e( 'Private label packs from 250 to 700 g – bags, vacuum, pressed, plastic and cartons – designed with your brand.', 'aria' ); ?></p>
				<span class="link-more"><?php esc_html_e( 'Private label', 'aria' ); ?></span>
			</a>
		</div>
	</div>
</section>

<?php if ( $syrup ) : ?>
<section class="syrup" aria-labelledby="syrup-title">
	<div class="wrap syrup-grid">
		<div class="syrup-media">
			<?php echo aria_product_image( $syrup->ID, 'aria-card', array( 'alt' => __( 'Aria Foods date syrup', 'aria' ), 'loading' => 'lazy', 'sizes' => '(min-width: 900px) 480px, 100vw' ) ); // phpcs:ignore ?>
		</div>
		<div class="syrup-copy">
			<p class="kicker"><?php esc_html_e( 'For manufacturers', 'aria' ); ?></p>
			<h2 id="syrup-title"><?php esc_html_e( 'Bulk date syrup, made to your Brix', 'aria' ); ?></h2>
			<p><?php esc_html_e( 'A clean-label replacement for refined sugar, glucose syrup and honey. We set the Brix between 72 and 78 for your recipe and ship it in gallons or 1,400 kg IBC tanks.', 'aria' ); ?></p>
			<dl class="syrup-specs">
				<div><dt>Brix</dt><dd>72–78</dd></div>
				<div><dt>pH</dt><dd>3.4–3.5</dd></div>
				<div><dt><?php esc_html_e( 'Additives', 'aria' ); ?></dt><dd><?php esc_html_e( 'Citric acid only', 'aria' ); ?></dd></div>
				<div><dt><?php esc_html_e( 'Bulk pack', 'aria' ); ?></dt><dd><?php esc_html_e( '1,400 kg IBC', 'aria' ); ?></dd></div>
			</dl>
			<p class="syrup-uses"><?php esc_html_e( 'Used in bakery and cereal bars, juices and sports drinks, dairy and ice cream, chocolate fillings and sauces.', 'aria' ); ?></p>
			<div class="hero-ctas">
				<a class="btn btn--gold" href="<?php echo esc_url( get_permalink( $syrup ) ); ?>"><?php esc_html_e( 'Date syrup specification', 'aria' ); ?></a>
				<a class="btn btn--ghost-light" href="#quote" data-product="<?php echo esc_attr( get_the_title( $syrup ) ); ?>"><?php esc_html_e( 'Price for date syrup', 'aria' ); ?></a>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
$products = new WP_Query( array(
	'post_type'      => 'product',
	'posts_per_page' => 30,
	'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	'no_found_rows'  => true,
) );
if ( $products->have_posts() ) :
	?>
<section class="section" aria-labelledby="range-title">
	<div class="wrap">
		<div class="section-head section-head--row">
			<div>
				<h2 id="range-title"><?php esc_html_e( 'The range', 'aria' ); ?></h2>
				<p><?php esc_html_e( 'Key specifications at a glance. Open a product for the full sheet, packing and order volumes.', 'aria' ); ?></p>
			</div>
			<div class="filter" role="group" aria-label="<?php esc_attr_e( 'Filter products', 'aria' ); ?>" data-filter="#range-grid">
				<button type="button" aria-pressed="true" data-kind="all"><?php esc_html_e( 'All', 'aria' ); ?></button>
				<button type="button" aria-pressed="false" data-kind="dates"><?php esc_html_e( 'Date varieties', 'aria' ); ?></button>
				<button type="button" aria-pressed="false" data-kind="ingredients"><?php esc_html_e( 'Ingredients', 'aria' ); ?></button>
			</div>
		</div>
		<div class="pgrid" id="range-grid">
			<?php
			while ( $products->have_posts() ) {
				$products->the_post();
				aria_product_card();
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="section section--tint" aria-labelledby="pack-title">
	<div class="wrap">
		<div class="section-head">
			<h2 id="pack-title"><?php esc_html_e( 'Packed for how you sell', 'aria' ); ?></h2>
		</div>
		<div class="pack-grid">
			<div class="pack">
				<?php echo aria_icon( 'box' ); // phpcs:ignore ?>
				<h3><?php esc_html_e( 'Bulk', 'aria' ); ?></h3>
				<ul class="ticks">
					<li><?php esc_html_e( '5–10 kg cartons for every variety', 'aria' ); ?></li>
					<li><?php esc_html_e( '30 kg bags for processing and repacking', 'aria' ); ?></li>
					<li><?php esc_html_e( 'Gallons and 1,400 kg IBC tanks for syrup and liquid sugar', 'aria' ); ?></li>
				</ul>
				<a class="link-more" href="<?php echo esc_url( home_url( '/ariafoods-bulk-service/' ) ); ?>"><?php esc_html_e( 'Bulk & food service', 'aria' ); ?></a>
			</div>
			<div class="pack">
				<?php echo aria_icon( 'tag' ); // phpcs:ignore ?>
				<h3><?php esc_html_e( 'Private label', 'aria' ); ?></h3>
				<ul class="ticks">
					<li><?php esc_html_e( '250, 350, 450, 500, 600 and 700 g', 'aria' ); ?></li>
					<li><?php esc_html_e( 'Flexible bag, pressed, vacuum, plastic or carton', 'aria' ); ?></li>
					<li><?php esc_html_e( 'Your logo or ours, artwork designed in-house', 'aria' ); ?></li>
				</ul>
				<a class="link-more" href="<?php echo esc_url( home_url( '/customer-packing/' ) ); ?>"><?php esc_html_e( 'Private label', 'aria' ); ?></a>
			</div>
			<div class="pack pack--volumes">
				<?php echo aria_icon( 'ship' ); // phpcs:ignore ?>
				<h3><?php esc_html_e( 'Order volumes', 'aria' ); ?></h3>
				<table>
					<tbody>
						<tr><th><?php esc_html_e( 'Minimum', 'aria' ); ?></th><td><?php esc_html_e( '1 × 20 ft', 'aria' ); ?></td></tr>
						<tr><th>20 ft</th><td><?php esc_html_e( 'up to ~15 t', 'aria' ); ?></td></tr>
						<tr><th>40 ft</th><td><?php esc_html_e( '25 t and more', 'aria' ); ?></td></tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</section>

<section class="section" aria-labelledby="steps-title">
	<div class="wrap">
		<div class="section-head">
			<h2 id="steps-title"><?php esc_html_e( 'From inquiry to your port', 'aria' ); ?></h2>
		</div>
		<ol class="steps steps--row">
			<li><strong><?php esc_html_e( 'Inquiry', 'aria' ); ?></strong><span><?php esc_html_e( 'Product, grade, packing, volume and port.', 'aria' ); ?></span></li>
			<li><strong><?php esc_html_e( 'Sample', 'aria' ); ?></strong><span><?php esc_html_e( 'Check quality before you commit.', 'aria' ); ?></span></li>
			<li><strong><?php esc_html_e( 'Offer', 'aria' ); ?></strong><span><?php esc_html_e( 'Price, specification and shipping window.', 'aria' ); ?></span></li>
			<li><strong><?php esc_html_e( 'Production', 'aria' ); ?></strong><span><?php esc_html_e( 'Washed, graded and packed in certified plants.', 'aria' ); ?></span></li>
			<li><strong><?php esc_html_e( 'Shipping', 'aria' ); ?></strong><span><?php esc_html_e( 'Transport, customs and documents by our export team.', 'aria' ); ?></span></li>
		</ol>
		<p class="center-cta"><a class="btn btn--outline" href="<?php echo esc_url( home_url( '/sample-request/' ) ); ?>"><?php esc_html_e( 'Request a sample', 'aria' ); ?></a></p>
	</div>
</section>

<?php
$shorts = array_filter( array_map( 'trim', explode( ',', (string) aria_opt( 'shorts' ) ) ) );
if ( $shorts ) :
	?>
<section class="section section--ink" aria-labelledby="video-title">
	<div class="wrap">
		<div class="section-head section-head--row">
			<div>
				<h2 id="video-title"><?php esc_html_e( 'Inside the packing halls', 'aria' ); ?></h2>
				<p><?php esc_html_e( 'Short clips of sorting, packing and loading.', 'aria' ); ?></p>
			</div>
			<a class="link-more" href="<?php echo esc_url( aria_opt( 'youtube' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'YouTube channel', 'aria' ); ?></a>
		</div>
		<div class="shorts" tabindex="0" aria-label="<?php esc_attr_e( 'Videos, scroll sideways', 'aria' ); ?>">
			<?php
			foreach ( array_slice( $shorts, 0, 8 ) as $sid ) {
				echo aria_yt_lite( $sid ); // phpcs:ignore
			}
			?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
$latest = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 3, 'no_found_rows' => true, 'ignore_sticky_posts' => true ) );
if ( $latest->have_posts() ) :
	$blog = (int) get_option( 'page_for_posts' );
	?>
<section class="section" aria-labelledby="news-title">
	<div class="wrap">
		<div class="section-head section-head--row">
			<div>
				<h2 id="news-title"><?php esc_html_e( 'Market insights', 'aria' ); ?></h2>
				<p><?php esc_html_e( 'Harvest reports, prices and sourcing guides for buyers.', 'aria' ); ?></p>
			</div>
			<a class="link-more" href="<?php echo esc_url( $blog ? get_permalink( $blog ) : home_url( '/news/' ) ); ?>"><?php esc_html_e( 'All insights', 'aria' ); ?></a>
		</div>
		<div class="agrid">
			<?php
			while ( $latest->have_posts() ) {
				$latest->the_post();
				aria_post_card();
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="section section--tint">
	<div class="wrap narrow">
		<?php aria_faq_block( aria_faq_items( (int) get_option( 'page_on_front' ) ) ); ?>
	</div>
</section>

<?php
aria_quote_band();
get_footer();
