<?php
/**
 * Home page (design v2): split hero, key figures, product finder, buyer routes,
 * date syrup feature, FAQ and the inquiry builder.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;
get_header();

$prod   = function ( $slug ) {
	return get_page_by_path( $slug, OBJECT, 'product' );
};
$syrup  = $prod( 'date-syrup' );
$hero   = aria_opt( 'hero_image' );
$count  = (int) wp_count_posts( 'product' )->publish;
$quote  = home_url( '/get-a-quote/' );
$term   = get_term_by( 'slug', 'ingredients', 'product_cat' );
$ingr   = $term ? get_term_link( $term ) : home_url( '/dates-products/' );
?>

<section class="hero" aria-labelledby="hero-title">
	<div class="hero-copy">
		<p class="kicker"><?php esc_html_e( 'For the global food trade', 'aria' ); ?></p>
		<h1 id="hero-title"><?php esc_html_e( 'Exceptional dates. Reliable supply. Built for business.', 'aria' ); ?></h1>
		<p class="hero-lead"><?php esc_html_e( 'Iranian dates and natural date ingredients for importers, manufacturers and private label brands – stocked and shipped from Dubai.', 'aria' ); ?></p>
		<div class="hero-ctas">
			<a class="btn btn--primary btn--lg" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>"><?php esc_html_e( 'Explore products', 'aria' ); ?></a>
			<a class="btn btn--line btn--lg" href="<?php echo esc_url( home_url( '/sample-request/' ) ); ?>"><?php esc_html_e( 'Request a sample', 'aria' ); ?></a>
		</div>
		<ul class="hero-points">
			<li><?php esc_html_e( 'Bulk supply', 'aria' ); ?></li>
			<li><?php esc_html_e( 'Private label', 'aria' ); ?></li>
			<li><?php esc_html_e( 'Export support', 'aria' ); ?></li>
		</ul>
	</div>
	<div class="hero-media">
		<?php
		if ( $hero ) {
			echo '<img src="' . esc_url( $hero ) . '" alt="' . esc_attr__( 'Iranian dates and date syrup', 'aria' ) . '" fetchpriority="high" decoding="async" width="1200" height="1000">';
		} elseif ( $syrup ) {
			echo aria_product_image( $syrup->ID, 'aria-hero', array( 'alt' => __( 'Date syrup and dates', 'aria' ), 'fetchpriority' => 'high', 'loading' => 'eager', 'sizes' => '(min-width: 900px) 58vw, 100vw' ) ); // phpcs:ignore
		}
		?>
		<p class="hero-badge"><small><?php esc_html_e( 'From origin to your port', 'aria' ); ?></small><strong><?php esc_html_e( '1 ton LCL → Full containers', 'aria' ); ?></strong></p>
	</div>
</section>

<section class="stats" aria-label="<?php esc_attr_e( 'Company facts', 'aria' ); ?>">
	<div class="wrap stats-row">
		<p><strong><?php esc_html_e( '30+ years', 'aria' ); ?></strong><?php esc_html_e( 'Family experience', 'aria' ); ?></p>
		<p><strong><?php esc_html_e( '3 factories', 'aria' ); ?></strong><?php esc_html_e( 'Processing & packing', 'aria' ); ?></p>
		<p><strong><?php echo esc_html( sprintf( _n( '%d product', '%d products', $count, 'aria' ), $count ) ); ?></strong><?php esc_html_e( 'Dates & ingredients', 'aria' ); ?></p>
		<p><strong><?php esc_html_e( '5 continents', 'aria' ); ?></strong><?php esc_html_e( 'Export reach', 'aria' ); ?></p>
	</div>
</section>

<?php
$products = new WP_Query( array(
	'post_type'      => 'product',
	'posts_per_page' => 8,
	'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	'no_found_rows'  => true,
) );
if ( $products->have_posts() ) :
	?>
<section class="section" aria-labelledby="range-title">
	<div class="wrap">
		<p class="kicker"><?php esc_html_e( 'Source with confidence', 'aria' ); ?></p>
		<div class="section-head section-head--row">
			<h2 id="range-title"><?php esc_html_e( 'Find the right product for your business.', 'aria' ); ?></h2>
			<a class="link-arrow" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>"><?php esc_html_e( 'View all products', 'aria' ); ?> <?php echo aria_icon( 'arrow' ); // phpcs:ignore ?></a>
		</div>
		<div class="filter" role="group" aria-label="<?php esc_attr_e( 'Filter products', 'aria' ); ?>" data-filter="#range-grid">
			<button type="button" aria-pressed="true" data-kind="all"><?php esc_html_e( 'All products', 'aria' ); ?></button>
			<button type="button" aria-pressed="false" data-kind="dates"><?php esc_html_e( 'Whole dates', 'aria' ); ?></button>
			<button type="button" aria-pressed="false" data-kind="ingredients"><?php esc_html_e( 'Date ingredients', 'aria' ); ?></button>
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

<section class="section section--sage" aria-labelledby="routes-title">
	<div class="wrap">
		<h2 id="routes-title"><?php esc_html_e( 'One partner. Three ways to source.', 'aria' ); ?></h2>
		<div class="routes">
			<a class="route" href="<?php echo esc_url( home_url( '/ariafoods-bulk-service/' ) ); ?>">
				<?php echo aria_icon( 'ship', 'icon route-icon' ); // phpcs:ignore ?>
				<h3><?php esc_html_e( 'Importers & wholesalers', 'aria' ); ?></h3>
				<p><?php esc_html_e( 'Bulk dates, graded and packed for your market.', 'aria' ); ?></p>
				<?php echo aria_icon( 'arrow', 'icon route-go' ); // phpcs:ignore ?>
			</a>
			<a class="route" href="<?php echo esc_url( $ingr ); ?>">
				<?php echo aria_icon( 'factory', 'icon route-icon' ); // phpcs:ignore ?>
				<h3><?php esc_html_e( 'Food manufacturers', 'aria' ); ?></h3>
				<p><?php esc_html_e( 'Date ingredients tailored to your applications.', 'aria' ); ?></p>
				<?php echo aria_icon( 'arrow', 'icon route-go' ); // phpcs:ignore ?>
			</a>
			<a class="route" href="<?php echo esc_url( home_url( '/customer-packing/' ) ); ?>">
				<?php echo aria_icon( 'tag', 'icon route-icon' ); // phpcs:ignore ?>
				<h3><?php esc_html_e( 'Private label brands', 'aria' ); ?></h3>
				<p><?php esc_html_e( 'Retail-ready packs with your own identity.', 'aria' ); ?></p>
				<?php echo aria_icon( 'arrow', 'icon route-go' ); // phpcs:ignore ?>
			</a>
		</div>
	</div>
</section>

<?php if ( $syrup ) : ?>
<section class="section" aria-labelledby="syrup-title">
	<div class="wrap feature">
		<div class="feature-media">
			<?php echo aria_product_image( $syrup->ID, 'aria-card', array( 'alt' => __( 'Aria Foods date syrup', 'aria' ), 'loading' => 'lazy', 'sizes' => '(min-width: 900px) 520px, 100vw' ) ); // phpcs:ignore ?>
		</div>
		<div class="feature-copy">
			<p class="kicker"><?php esc_html_e( 'For manufacturers', 'aria' ); ?></p>
			<h2 id="syrup-title"><?php esc_html_e( 'Bulk date syrup, made to your Brix.', 'aria' ); ?></h2>
			<p><?php esc_html_e( 'A clean-label replacement for refined sugar, glucose syrup and honey. We set the Brix between 72 and 78 for your recipe and ship it in gallons or 1,400 kg IBC tanks.', 'aria' ); ?></p>
			<dl class="spec-tiles">
				<div><dt>Brix</dt><dd>72–78</dd></div>
				<div><dt>pH</dt><dd>3.4–3.5</dd></div>
				<div><dt><?php esc_html_e( 'Additives', 'aria' ); ?></dt><dd><?php esc_html_e( 'Citric acid only', 'aria' ); ?></dd></div>
				<div><dt><?php esc_html_e( 'Bulk pack', 'aria' ); ?></dt><dd><?php esc_html_e( '1,400 kg IBC', 'aria' ); ?></dd></div>
			</dl>
			<div class="hero-ctas">
				<a class="btn btn--primary" href="<?php echo esc_url( get_permalink( $syrup ) ); ?>"><?php esc_html_e( 'Date syrup specification', 'aria' ); ?></a>
				<a class="btn btn--line" href="<?php echo esc_url( add_query_arg( 'item', rawurlencode( get_the_title( $syrup ) ), $quote ) ); ?>"><?php esc_html_e( 'Price for date syrup', 'aria' ); ?></a>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="section section--cream">
	<div class="wrap narrow">
		<?php aria_faq_block( aria_faq_items( (int) get_option( 'page_on_front' ) ) ); ?>
	</div>
</section>

<section class="cta-band" id="quote" aria-labelledby="cta-title">
	<div class="wrap cta-grid">
		<div>
			<h2 id="cta-title"><?php esc_html_e( 'Let’s plan your next shipment.', 'aria' ); ?></h2>
			<p><?php esc_html_e( 'Tell us what you need. Our export team will take it from here.', 'aria' ); ?></p>
		</div>
		<form class="builder" action="<?php echo esc_url( $quote ); ?>" method="get">
			<p class="field">
				<label for="b-product"><?php esc_html_e( 'Product', 'aria' ); ?></label>
				<select id="b-product" name="item">
					<option value=""><?php esc_html_e( 'Select a product', 'aria' ); ?></option>
					<?php foreach ( aria_product_choices() as $c ) : ?>
						<option><?php echo esc_html( $c ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p class="field">
				<label for="b-qty"><?php esc_html_e( 'Quantity', 'aria' ); ?></label>
				<select id="b-qty" name="quantity">
					<option value=""><?php esc_html_e( 'e.g. 1 container', 'aria' ); ?></option>
					<?php foreach ( aria_quantity_options() as $q ) : ?>
						<option><?php echo esc_html( $q ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p class="field">
				<label for="b-port"><?php esc_html_e( 'Destination port', 'aria' ); ?></label>
				<input id="b-port" name="port" placeholder="<?php esc_attr_e( 'e.g. Hamburg', 'aria' ); ?>" autocomplete="off">
			</p>
			<button class="btn btn--bronze" type="submit"><?php esc_html_e( 'Build your inquiry', 'aria' ); ?> <?php echo aria_icon( 'arrow' ); // phpcs:ignore ?></button>
		</form>
	</div>
</section>

<?php
get_footer();
