<?php
/**
 * Pages. A page can carry a form (meta _aria_form: quote|sample|contact|tracking),
 * which turns it into a two-column page with the form beside the content.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$id      = get_the_ID();
	$form    = get_post_meta( $id, '_aria_form', true );
	$content = trim( get_the_content() );
	$faq     = aria_faq_items( $id );
	$slug    = get_post_field( 'post_name', $id );
	$lead    = has_excerpt() ? get_the_excerpt() : '';

	aria_page_head( get_the_title(), $lead );

	if ( 'quote' === $form && in_array( $slug, array( 'ariafoods-bulk-service', 'customer-packing', 'ariafoods-dates-catalogue' ), true ) ) :
		// Service pages: content first, then the shared quote band.
		?>
		<div class="wrap narrow section section--tight">
			<div class="prose"><?php the_content(); ?></div>
			<?php aria_faq_block( $faq ); ?>
		</div>
		<?php
		aria_quote_band();
	elseif ( $form ) :
		?>
		<div class="wrap page-split section section--tight">
			<div class="page-split-main">
				<?php if ( $content ) : ?>
					<div class="prose"><?php the_content(); ?></div>
				<?php endif; ?>
				<?php if ( 'contact' === $form || 'quote' === $form || 'sample' === $form ) : ?>
					<div class="card-box">
						<h2 class="h3"><?php esc_html_e( 'Talk to the trade desk', 'aria' ); ?></h2>
						<?php aria_contact_list(); ?>
					</div>
				<?php endif; ?>
				<?php if ( 'about-us' === $slug ) : ?>
					<div class="certs-wrap"><?php aria_cert_row(); ?></div>
				<?php endif; ?>
				<?php aria_faq_block( $faq ); ?>
			</div>
			<div class="page-split-form" id="form-<?php echo esc_attr( $form ); ?>-wrap">
				<div class="form-card">
					<h2 class="h3">
						<?php
						$heads = array(
							'quote'    => __( 'Request a quote', 'aria' ),
							'sample'   => __( 'Request a sample', 'aria' ),
							'contact'  => __( 'Send us a message', 'aria' ),
							'tracking' => __( 'Order status', 'aria' ),
						);
						echo esc_html( $heads[ $form ] ?? '' );
						?>
					</h2>
					<?php
					$preset = isset( $_GET['product'] ) ? sanitize_text_field( wp_unslash( $_GET['product'] ) ) : ''; // phpcs:ignore
					echo aria_form( $form, array( 'product' => $preset ) ); // phpcs:ignore
					?>
				</div>
			</div>
		</div>
		<?php
	else :
		?>
		<div class="wrap narrow section section--tight">
			<div class="prose"><?php the_content(); ?></div>
			<?php aria_faq_block( $faq ); ?>
		</div>
		<?php
	endif;
endwhile;

get_footer();
