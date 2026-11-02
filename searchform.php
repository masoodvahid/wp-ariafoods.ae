<?php
/**
 * Search form.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;
$sid = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $sid ); ?>"><?php esc_html_e( 'Search products and articles', 'aria' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $sid ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search products and articles', 'aria' ); ?>">
	<button type="submit" class="icon-btn"><span class="screen-reader-text"><?php esc_html_e( 'Search', 'aria' ); ?></span><?php echo aria_icon( 'search' ); // phpcs:ignore ?></button>
</form>
