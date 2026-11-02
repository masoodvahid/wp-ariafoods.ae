<?php
/**
 * Footer.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;
$p = function ( $s ) {
	return home_url( '/' . $s . '/' );
};
?>
</main>
<footer class="site-foot">
	<div class="wrap foot-row">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img src="<?php echo esc_url( aria_logo_url() ); ?>" alt="" width="48" height="48" loading="lazy">
			<span class="brand-text"><strong><?php echo esc_html( aria_opt( 'company' ) ); ?></strong><small><?php esc_html_e( 'Iranian dates & ingredients', 'aria' ); ?></small></span>
		</a>
		<nav class="foot-links" aria-label="<?php esc_attr_e( 'Footer', 'aria' ); ?>">
			<?php
			aria_footer_links( 'footer', array(
				array( 'title' => 'Products', 'url' => $p( 'dates-products' ) ),
				array( 'title' => 'Quality', 'url' => $p( 'quality' ) ),
				array( 'title' => 'Contact', 'url' => $p( 'contact-us' ) ),
			) );
			?>
		</nav>
	</div>
	<div class="wrap foot-legal">
		<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( aria_opt( 'branch_legal' ) ? aria_opt( 'branch_legal' ) : aria_opt( 'company' ) ); ?><?php if ( aria_opt( 'license_no' ) ) : ?> · <?php echo esc_html( sprintf( __( 'Trade licence %1$s%2$s', 'aria' ), aria_opt( 'license_no' ), aria_opt( 'license_auth' ) ? ' (' . aria_opt( 'license_auth' ) . ')' : '' ) ); ?><?php endif; ?></p>
		<p><?php esc_html_e( 'Dubai branch of', 'aria' ); ?> <a href="<?php echo esc_url( aria_opt( 'parent_url' ) ); ?>"><?php echo esc_html( aria_opt( 'parent_name' ) ); ?></a> · <?php esc_html_e( 'Wholesale and export only', 'aria' ); ?></p>
	</div>
</footer>
<nav class="dock" aria-label="<?php esc_attr_e( 'Quick contact', 'aria' ); ?>">
	<a class="dock-wa" href="<?php echo esc_url( aria_wa_url( 'Hello Aria Foods, I would like a wholesale quote.' ) ); ?>" target="_blank" rel="noopener"><?php echo aria_icon( 'whatsapp' ); // phpcs:ignore ?> WhatsApp</a>
	<a class="dock-quote" href="<?php echo esc_url( $p( 'get-a-quote' ) ); ?>"><?php esc_html_e( 'Request a quote', 'aria' ); ?></a>
</nav>
<?php wp_footer(); ?>
</body>
</html>
