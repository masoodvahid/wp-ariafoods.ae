<?php
/**
 * Product catalogue (/dates-products/) and product categories.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;
get_header();

$is_tax = is_tax( 'product_cat' );
$term   = $is_tax ? get_queried_object() : null;
$title  = $is_tax ? $term->name : __( 'Iranian dates and date ingredients', 'aria' );
$lead   = $is_tax && $term->description ? wp_strip_all_tags( $term->description ) : __( 'Specifications for every variety and ingredient we export. Bulk, food service and private label; LCL from 1 ton to full containers.', 'aria' );
if ( $is_tax && ! $term->description ) {
	$lead = 'dates' === $term->slug
		? __( 'Nine Iranian date varieties with moisture, size, harvest and shelf-life data – packed in bulk cartons, bags or private label.', 'aria' )
		: __( 'Date syrup, paste, powder, liquid date sugar and chopped dates for food and beverage manufacturers.', 'aria' );
}
aria_page_head( $title, $lead );
?>
<section class="section section--tight">
	<div class="wrap">
		<?php if ( ! $is_tax ) : ?>
			<div class="filter filter--bar" role="group" aria-label="<?php esc_attr_e( 'Filter products', 'aria' ); ?>" data-filter="#catalogue">
				<button type="button" aria-pressed="true" data-kind="all"><?php esc_html_e( 'All', 'aria' ); ?></button>
				<button type="button" aria-pressed="false" data-kind="dates"><?php esc_html_e( 'Date varieties', 'aria' ); ?></button>
				<button type="button" aria-pressed="false" data-kind="ingredients"><?php esc_html_e( 'Ingredients', 'aria' ); ?></button>
			</div>
		<?php endif; ?>
		<?php if ( have_posts() ) : ?>
			<div class="pgrid" id="catalogue">
				<?php
				$i = 0;
				while ( have_posts() ) {
					the_post();
					aria_product_card( get_the_ID(), $i++ < 4 );
				}
				?>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'No products yet.', 'aria' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php if ( have_posts() && ( ! $is_tax || 'dates' === $term->slug ) ) : rewind_posts(); ?>
<section class="section section--tint" aria-labelledby="compare-title">
	<div class="wrap">
		<div class="section-head"><h2 id="compare-title"><?php esc_html_e( 'Compare specifications', 'aria' ); ?></h2></div>
		<div class="table-scroll" tabindex="0">
			<table class="compare">
				<thead><tr><th scope="col"><?php esc_html_e( 'Product', 'aria' ); ?></th><th scope="col"><?php esc_html_e( 'Moisture', 'aria' ); ?></th><th scope="col"><?php esc_html_e( 'Size', 'aria' ); ?></th><th scope="col"><?php esc_html_e( 'Harvest', 'aria' ); ?></th><th scope="col"><?php esc_html_e( 'Shelf life', 'aria' ); ?></th></tr></thead>
				<tbody>
				<?php
				while ( have_posts() ) :
					the_post();
					if ( ! aria_is_variety( get_the_ID() ) ) {
						continue;
					}
					?>
					<tr>
						<th scope="row"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></th>
						<td><?php echo esc_html( aria_spec( get_the_ID(), 'moist' ) ); ?></td>
						<td><?php echo esc_html( aria_spec( get_the_ID(), '^size' ) ); ?></td>
						<td><?php echo esc_html( aria_spec( get_the_ID(), 'harvest' ) ); ?></td>
						<td><?php echo esc_html( aria_spec( get_the_ID(), 'shelf' ) ); ?></td>
					</tr>
				<?php endwhile; ?>
				</tbody>
			</table>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
aria_quote_band();
get_footer();
