<?php
/**
 * /export/ – list of export market pages. Loaded from page.php.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

$kids = get_pages( array( 'parent' => get_the_ID(), 'sort_column' => 'menu_order,post_title', 'post_status' => 'publish' ) );
aria_page_head( get_the_title(), has_excerpt() ? get_the_excerpt() : '' );
?>
<section class="section section--tight">
	<div class="wrap">
		<div class="prose narrow"><?php the_content(); ?></div>
		<?php if ( $kids ) : ?>
			<div class="seg-grid mk-hub">
				<?php
				foreach ( $kids as $k ) :
					$mk = aria_export_market( $k->ID );
					$cn = $mk ? $mk['country'] : $k->post_title;
					?>
					<a class="seg" href="<?php echo esc_url( get_permalink( $k ) ); ?>">
						<?php
						$fl = ( $mk && ! empty( $mk['flag'] ) ) ? array_values( array_filter( (array) $mk['flag'], function ( $c ) {
							return (bool) preg_match( '/^#[0-9a-f]{3,8}$/i', $c );
						} ) ) : array();
						if ( $fl ) {
							$stops = array();
							$n     = count( $fl );
							foreach ( $fl as $i => $col ) {
								$stops[] = $col . ' ' . round( $i * 100 / $n, 2 ) . '% ' . round( ( $i + 1 ) * 100 / $n, 2 ) . '%';
							}
							echo '<span class="mk-hub-flag" aria-hidden="true" style="background:linear-gradient(' . esc_attr( implode( ',', $stops ) ) . ')"></span>';
						} else {
							echo aria_icon( 'globe' ); // phpcs:ignore
						}
						?>
						<h3><?php echo esc_html( $cn ); ?></h3>
						<p><?php echo esc_html( wp_trim_words( rtrim( $k->post_excerpt, '. ' ), 22 ) ); ?></p>
						<span class="link-more"><?php echo esc_html( sprintf( __( 'Dates for %s', 'aria' ), $cn ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
aria_quote_band();
