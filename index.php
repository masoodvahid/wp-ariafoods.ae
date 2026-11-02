<?php
/**
 * Insights, categories, search results and fallback.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;
get_header();

if ( is_search() ) {
	$title = sprintf( __( 'Results for “%s”', 'aria' ), get_search_query() );
	$lead  = '';
} elseif ( is_category() || is_tag() ) {
	$title = single_term_title( '', false );
	$lead  = wp_strip_all_tags( term_description() );
} else {
	$blog  = (int) get_option( 'page_for_posts' );
	$title = $blog ? get_the_title( $blog ) : __( 'Insights', 'aria' );
	$lead  = $blog && has_excerpt( $blog ) ? get_the_excerpt( $blog ) : __( 'Harvest reports, prices and sourcing guides for importers and food manufacturers.', 'aria' );
}

ob_start();
if ( ! is_search() ) {
	$current = is_category() ? get_queried_object_id() : 0;
	$cats    = get_categories( array( 'hide_empty' => true, 'exclude' => array( get_option( 'default_category' ) ) ) );
	if ( count( $cats ) > 1 ) {
		$blog = (int) get_option( 'page_for_posts' );
		echo '<nav class="chips chips--nav" aria-label="' . esc_attr__( 'Topics', 'aria' ) . '">';
		echo '<a href="' . esc_url( $blog ? get_permalink( $blog ) : home_url( '/' ) ) . '"' . ( $current ? '' : ' aria-current="page"' ) . '>' . esc_html__( 'All', 'aria' ) . '</a>';
		foreach ( $cats as $c ) {
			echo '<a href="' . esc_url( get_category_link( $c ) ) . '"' . ( $current === $c->term_id ? ' aria-current="page"' : '' ) . '>' . esc_html( $c->name ) . '</a>';
		}
		echo '</nav>';
	}
}
get_search_form();
$extra = ob_get_clean();

aria_page_head( $title, $lead, $extra );
?>
<section class="section section--tight">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<div class="agrid" id="post-grid">
				<?php
				$i = 0;
				while ( have_posts() ) {
					the_post();
					if ( 'product' === get_post_type() ) {
						echo '<div class="agrid-product">';
						aria_product_card( get_the_ID() );
						echo '</div>';
					} elseif ( 'page' === get_post_type() ) {
						echo '<article class="acard acard--page"><a class="acard-link" href="' . esc_url( get_permalink() ) . '"><span class="acard-body"><span class="acard-meta">' . esc_html__( 'Page', 'aria' ) . '</span><h3 class="acard-title">' . esc_html( get_the_title() ) . '</h3></span></a></article>';
					} else {
						aria_post_card( get_the_ID(), $i < 3 );
					}
					++$i;
				}
				?>
			</div>
			<?php
			$next = get_next_posts_page_link();
			if ( get_next_posts_link() ) :
				?>
				<p class="load-more-wrap">
					<a class="btn btn--outline" href="<?php echo esc_url( $next ); ?>" data-load-more="#post-grid"><?php esc_html_e( 'Show more articles', 'aria' ); ?></a>
				</p>
			<?php endif; ?>
			<?php
			the_posts_pagination( array(
				'mid_size'  => 1,
				'prev_text' => __( 'Previous', 'aria' ),
				'next_text' => __( 'Next', 'aria' ),
				'class'     => 'pager',
			) );
			?>
		<?php else : ?>
			<div class="empty">
				<h2><?php esc_html_e( 'Nothing matched your search', 'aria' ); ?></h2>
				<p><?php esc_html_e( 'Try a product name such as “date syrup” or “Mazafati”, or browse the full range.', 'aria' ); ?></p>
				<p><a class="btn btn--primary" href="<?php echo esc_url( home_url( '/dates-products/' ) ); ?>"><?php esc_html_e( 'Browse products', 'aria' ); ?></a></p>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
