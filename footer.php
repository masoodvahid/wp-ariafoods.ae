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
	<div class="wrap foot-grid">
		<div class="foot-brand">
			<a class="brand brand--foot" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img src="<?php echo esc_url( aria_logo_url() ); ?>" alt="" width="46" height="40" loading="lazy">
				<span class="brand-name"><?php echo esc_html( aria_opt( 'company' ) ); ?></span>
			</a>
			<p><?php esc_html_e( 'Exporter of Iranian dates and date ingredients to importers, wholesalers and food manufacturers on five continents. Bulk, food service and private label.', 'aria' ); ?></p>
			<p><a class="btn btn--ghost-light" href="<?php echo esc_url( aria_opt( 'catalogue' ) ); ?>" target="_blank" rel="noopener"><?php echo aria_icon( 'download' ); // phpcs:ignore ?> <?php esc_html_e( 'Catalogue 2025 (PDF)', 'aria' ); ?></a></p>
		</div>
		<div>
			<h2 class="foot-title"><?php esc_html_e( 'Products', 'aria' ); ?></h2>
			<?php
			aria_footer_links( 'footer', array(
				array( 'title' => 'Date syrup', 'url' => $p( 'product/date-syrup' ) ),
				array( 'title' => 'Date paste', 'url' => $p( 'product/date-paste' ) ),
				array( 'title' => 'Date powder', 'url' => $p( 'product/date-powder' ) ),
				array( 'title' => 'Mazafati dates', 'url' => $p( 'product/mazafati-dates' ) ),
				array( 'title' => 'Piarom dates', 'url' => $p( 'product/piarom-dates' ) ),
				array( 'title' => 'Sayer dates', 'url' => $p( 'product/sayer-dates' ) ),
				array( 'title' => 'All products', 'url' => $p( 'dates-products' ) ),
			) );
			?>
		</div>
		<div>
			<h2 class="foot-title"><?php esc_html_e( 'For buyers', 'aria' ); ?></h2>
			<?php
			aria_footer_links( 'legal', array(
				array( 'title' => 'Bulk & food service', 'url' => $p( 'ariafoods-bulk-service' ) ),
				array( 'title' => 'Private label', 'url' => $p( 'customer-packing' ) ),
				array( 'title' => 'Request a quote', 'url' => $p( 'get-a-quote' ) ),
				array( 'title' => 'Request a sample', 'url' => $p( 'sample-request' ) ),
				array( 'title' => 'Track an order', 'url' => $p( 'tracking-orders' ) ),
				array( 'title' => 'Insights', 'url' => $p( 'news' ) ),
				array( 'title' => 'About us', 'url' => $p( 'about-us' ) ),
			) );
			?>
		</div>
		<div>
			<h2 class="foot-title"><?php esc_html_e( 'Trade desk', 'aria' ); ?></h2>
			<?php aria_contact_list( 'contact-list contact-list--foot' ); ?>
		</div>
	</div>
	<div class="wrap foot-certs">
		<?php aria_cert_row(); ?>
		<ul class="social">
			<?php
			foreach ( array( 'linkedin' => 'LinkedIn', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'facebook' => 'Facebook', 'telegram' => 'Telegram' ) as $k => $label ) {
				if ( aria_opt( $k ) ) {
					echo '<li><a href="' . esc_url( aria_opt( $k ) ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( $label ) . '">' . aria_icon( $k ) . '</a></li>'; // phpcs:ignore
				}
			}
			?>
		</ul>
	</div>
	<div class="wrap foot-bottom">
		<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( aria_opt( 'branch_legal' ) ? aria_opt( 'branch_legal' ) : aria_opt( 'company' ) ); ?><?php if ( aria_opt( 'license_no' ) ) : ?> · <?php echo esc_html( sprintf( __( 'Trade licence %1$s%2$s', 'aria' ), aria_opt( 'license_no' ), aria_opt( 'license_auth' ) ? ' (' . aria_opt( 'license_auth' ) . ')' : '' ) ); ?><?php endif; ?></p>
		<p><?php esc_html_e( 'Dubai branch of', 'aria' ); ?> <a href="<?php echo esc_url( aria_opt( 'parent_url' ) ); ?>"><?php echo esc_html( aria_opt( 'parent_name' ) ); ?></a></p>
		<p><?php esc_html_e( 'Wholesale and export only.', 'aria' ); ?></p>
	</div>
</footer>
<div class="fab" aria-label="<?php esc_attr_e( 'Quick contact', 'aria' ); ?>" role="group">
	<a class="fab-btn fab-wa" href="<?php echo esc_url( aria_wa_url( 'Hello Aria Foods, I would like a wholesale quote.' ) ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Chat with our trade desk on WhatsApp', 'aria' ); ?>"><?php echo aria_icon( 'whatsapp' ); // phpcs:ignore ?><span class="fab-tip"><?php esc_html_e( 'WhatsApp', 'aria' ); ?></span></a>
	<a class="fab-btn fab-quote" href="<?php echo esc_url( $p( 'get-a-quote' ) ); ?>" data-quote-open aria-haspopup="dialog"><?php echo aria_icon( 'doc' ); // phpcs:ignore ?><span><?php esc_html_e( 'Request a quote', 'aria' ); ?></span></a>
</div>
<dialog class="qmodal" id="quote-modal" aria-labelledby="qm-title">
	<div class="qmodal-box">
		<button class="qmodal-close" type="button" data-quote-close aria-label="<?php esc_attr_e( 'Close', 'aria' ); ?>"><?php echo aria_icon( 'close' ); // phpcs:ignore ?></button>
		<p class="kicker"><?php esc_html_e( 'Wholesale & export only', 'aria' ); ?></p>
		<h2 class="qmodal-title" id="qm-title"><?php esc_html_e( 'Request a quote', 'aria' ); ?></h2>
		<p class="qmodal-lead"><?php esc_html_e( 'Tell us the product, quantity and destination port. Our trade desk replies within one working day with price and shipping options.', 'aria' ); ?></p>
		<?php echo aria_form( 'quote', array( 'id' => 'modal-quote' ) ); // phpcs:ignore ?>
	</div>
</dialog>
<?php wp_footer(); ?>
</body>
</html>
