<?php
/**
 * Not found.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;
get_header();
aria_page_head( __( 'This page has moved or no longer exists', 'aria' ), __( 'Search for a product or go to one of the pages buyers use most.', 'aria' ) );
?>
<section class="section section--tight">
	<div class="wrap narrow">
		<?php get_search_form(); ?>
		<ul class="ticks link-list">
			<li><a href="<?php echo esc_url( home_url( '/dates-products/' ) ); ?>"><?php esc_html_e( 'All products and specifications', 'aria' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/product/date-syrup/' ) ); ?>"><?php esc_html_e( 'Bulk date syrup', 'aria' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/ariafoods-bulk-service/' ) ); ?>"><?php esc_html_e( 'Bulk & food service', 'aria' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>"><?php esc_html_e( 'Request a quote', 'aria' ); ?></a></li>
		</ul>
	</div>
</section>
<?php
get_footer();
