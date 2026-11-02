<?php
/**
 * Article.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$content = apply_filters( 'the_content', get_the_content() );
	$toc     = aria_toc( $content );
	$cats    = get_the_category();
	?>
	<article class="article">
		<header class="phead phead--article">
			<div class="wrap narrow">
				<?php aria_breadcrumbs(); ?>
				<h1 class="phead-title"><?php the_title(); ?></h1>
				<p class="article-meta">
					<?php if ( $cats ) : ?>
						<a href="<?php echo esc_url( get_category_link( $cats[0] ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a> ·
					<?php endif; ?>
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					· <?php echo esc_html( sprintf( _n( '%d min read', '%d min read', aria_read_time(), 'aria' ), aria_read_time() ) ); ?>
				</p>
			</div>
		</header>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="wrap narrow article-cover">
				<?php the_post_thumbnail( 'aria-hero', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(min-width: 820px) 760px, 100vw', 'alt' => '' ) ); ?>
			</figure>
		<?php endif; ?>
		<div class="wrap narrow">
			<?php if ( count( $toc ) >= 3 ) : ?>
				<details class="toc toc--article" open>
					<summary><?php esc_html_e( 'In this article', 'aria' ); ?><?php echo aria_icon( 'chevron' ); // phpcs:ignore ?></summary>
					<ol>
						<?php foreach ( $toc as $t ) : ?>
							<li><a href="#<?php echo esc_attr( $t[0] ); ?>"><?php echo esc_html( $t[1] ); ?></a></li>
						<?php endforeach; ?>
					</ol>
				</details>
			<?php endif; ?>
			<div class="prose">
				<?php echo $content; // phpcs:ignore ?>
			</div>
			<?php aria_faq_block( aria_faq_items( get_the_ID() ) ); ?>
			<aside class="inline-cta">
				<div>
					<h2 class="h3"><?php esc_html_e( 'Sourcing dates or date ingredients?', 'aria' ); ?></h2>
					<p><?php esc_html_e( 'Send your specification and volume. We reply with price, packing and a shipping window.', 'aria' ); ?></p>
				</div>
				<div class="inline-cta-actions">
					<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>"><?php esc_html_e( 'Request a quote', 'aria' ); ?></a>
					<a class="btn btn--outline" href="<?php echo esc_url( aria_wa_url( 'Hello Aria Foods, I read "' . get_the_title() . '" and would like a quote.' ) ); ?>" target="_blank" rel="noopener"><?php echo aria_icon( 'whatsapp' ); // phpcs:ignore ?> WhatsApp</a>
				</div>
			</aside>
		</div>
	</article>
	<?php
	$more = get_posts( array(
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'post__not_in'   => array( get_the_ID() ),
		'category__in'   => $cats ? array( $cats[0]->term_id ) : array(),
	) );
	if ( $more ) :
		?>
		<section class="section section--tint" aria-labelledby="more-title">
			<div class="wrap">
				<div class="section-head"><h2 id="more-title"><?php esc_html_e( 'Keep reading', 'aria' ); ?></h2></div>
				<div class="agrid">
					<?php
					foreach ( $more as $m ) {
						aria_post_card( $m->ID );
					}
					?>
				</div>
			</div>
		</section>
		<?php
	endif;
endwhile;

get_footer();
