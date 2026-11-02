<?php
/**
 * /my-account/ — sign in, register, reset password; for customers: requests and profile.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;
get_header();

$notice = aria_account_notice();
$tab    = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore
$field  = function ( $name, $label, $type = 'text', $value = '', $attrs = '' ) {
	$id = 'acc-' . $name;
	printf(
		'<p class="field"><label for="%1$s">%2$s</label><input id="%1$s" name="%3$s" type="%4$s" value="%5$s" %6$s></p>',
		esc_attr( $id ),
		wp_kses( $label, array( 'span' => array( 'class' => true ) ) ),
		esc_attr( $name ),
		esc_attr( $type ),
		esc_attr( $value ),
		$attrs // phpcs:ignore -- fixed attribute strings from this template.
	);
};
$nonce = function ( $action ) {
	echo '<input type="hidden" name="aria_account" value="' . esc_attr( $action ) . '">';
	wp_nonce_field( 'aria_account_' . $action, '_aria_nonce', false );
};
$business_select = function ( $current = '' ) {
	echo '<p class="field"><label for="acc-business_type">' . esc_html__( 'Business type', 'aria' ) . '</label><select id="acc-business_type" name="business_type"><option value="">' . esc_html__( 'Choose one', 'aria' ) . '</option>';
	foreach ( aria_business_types() as $t ) {
		echo '<option' . selected( $current, $t, false ) . '>' . esc_html( $t ) . '</option>';
	}
	echo '</select></p>';
};

if ( is_user_logged_in() ) :
	$u    = wp_get_current_user();
	$pre  = aria_customer_prefill();
	aria_page_head( sprintf( __( 'Welcome, %s', 'aria' ), $u->first_name ? $u->first_name : $u->display_name ), $pre['company'] ? $pre['company'] : '' );
	?>
	<div class="wrap section section--tight account">
		<nav class="acc-tabs" aria-label="<?php esc_attr_e( 'Account', 'aria' ); ?>">
			<a href="<?php echo esc_url( aria_account_url() ); ?>"<?php echo '' === $tab ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'My requests', 'aria' ); ?></a>
			<a href="<?php echo esc_url( aria_account_url( 'profile' ) ); ?>"<?php echo 'profile' === $tab ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Company profile', 'aria' ); ?></a>
			<a href="<?php echo esc_url( wp_logout_url( add_query_arg( 'logged-out', 1, aria_account_url() ) ) ); ?>" data-no-pjax><?php esc_html_e( 'Sign out', 'aria' ); ?></a>
		</nav>

		<?php if ( $notice ) : ?>
			<p class="form-status<?php echo 'ok' === $notice[1] ? '' : ' is-error'; ?>" role="alert"><?php echo esc_html( $notice[0] ); ?></p>
		<?php elseif ( isset( $_GET['welcome'] ) ) : // phpcs:ignore ?>
			<p class="form-status" role="status"><?php esc_html_e( 'Your account is ready. Your details are now filled in on every quote and sample form.', 'aria' ); ?></p>
		<?php elseif ( isset( $_GET['saved'] ) ) : // phpcs:ignore ?>
			<p class="form-status" role="status"><?php esc_html_e( 'Profile saved.', 'aria' ); ?></p>
		<?php endif; ?>

		<?php if ( 'profile' === $tab ) : ?>
			<form class="form form-card acc-form" method="post" action="<?php echo esc_url( aria_account_url( 'profile' ) ); ?>">
				<?php $nonce( 'profile' ); ?>
				<div class="form-grid">
					<?php
					$field( 'full_name', __( 'Your name', 'aria' ), 'text', $pre['name'], 'required autocomplete="name"' );
					$field( 'company', __( 'Company', 'aria' ), 'text', $pre['company'], 'required autocomplete="organization"' );
					$business_select( $pre['business_type'] );
					$field( 'country', __( 'Country', 'aria' ), 'text', $pre['country'], 'autocomplete="country-name"' );
					$field( 'whatsapp', __( 'WhatsApp number', 'aria' ), 'tel', $pre['whatsapp'], 'autocomplete="tel"' );
					$field( 'port', __( 'Usual destination port', 'aria' ), 'text', $pre['port'], '' );
					?>
					<p class="field field--full"><label><?php esc_html_e( 'Email', 'aria' ); ?></label><input value="<?php echo esc_attr( $pre['email'] ); ?>" disabled></p>
					<?php
					$field( 'current_password', __( 'Current password <span class="opt">(only to change it)</span>', 'aria' ), 'password', '', 'autocomplete="current-password"' );
					$field( 'new_password', __( 'New password <span class="opt">(8+ characters)</span>', 'aria' ), 'password', '', 'autocomplete="new-password" minlength="8"' );
					?>
				</div>
				<div class="form-foot"><button class="btn btn--primary" type="submit"><?php esc_html_e( 'Save profile', 'aria' ); ?></button></div>
			</form>
		<?php else : ?>
			<div class="acc-actions">
				<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>"><?php esc_html_e( 'Request a quote', 'aria' ); ?></a>
				<a class="btn btn--outline" href="<?php echo esc_url( home_url( '/sample-request/' ) ); ?>"><?php esc_html_e( 'Request a sample', 'aria' ); ?></a>
				<a class="btn btn--outline" href="<?php echo esc_url( aria_opt( 'catalogue' ) ); ?>" target="_blank" rel="noopener"><?php echo aria_icon( 'download' ); // phpcs:ignore ?> <?php esc_html_e( 'Catalogue', 'aria' ); ?></a>
			</div>
			<?php
			$leads = get_posts( array(
				'post_type'      => 'aria_lead',
				'post_status'    => 'private',
				'posts_per_page' => 50,
				'meta_query'     => array(
					'relation' => 'OR',
					array( 'key' => '_aria_user', 'value' => $u->ID ),
					array( 'key' => '_aria_lead_email', 'value' => $u->user_email ),
				),
			) );
			?>
			<h2 class="h3"><?php esc_html_e( 'Your requests', 'aria' ); ?></h2>
			<?php if ( $leads ) : ?>
				<div class="table-scroll" tabindex="0">
					<table class="compare">
						<thead><tr><th><?php esc_html_e( 'Date', 'aria' ); ?></th><th><?php esc_html_e( 'Request', 'aria' ); ?></th><th><?php esc_html_e( 'Product', 'aria' ); ?></th><th><?php esc_html_e( 'Quantity', 'aria' ); ?></th><th><?php esc_html_e( 'Status', 'aria' ); ?></th></tr></thead>
						<tbody>
						<?php
						$types = array( 'quote' => 'Quote', 'sample' => 'Sample', 'contact' => 'Message', 'tracking' => 'Order status' );
						foreach ( $leads as $l ) :
							$d = (array) get_post_meta( $l->ID, '_aria_lead', true );
							$s = get_post_meta( $l->ID, '_aria_lead_status', true );
							?>
							<tr>
								<td><?php echo esc_html( get_the_date( 'j M Y', $l ) ); ?></td>
								<td><?php echo esc_html( $types[ get_post_meta( $l->ID, '_aria_lead_type', true ) ] ?? '' ); ?></td>
								<td><?php echo esc_html( $d['Product'] ?? '—' ); ?></td>
								<td><?php echo esc_html( $d['Quantity'] ?? '—' ); ?></td>
								<td><?php echo esc_html( $s ? $s : __( 'Received', 'aria' ) ); ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<p class="muted"><?php esc_html_e( 'No requests yet. Quotes and sample requests you send while signed in appear here.', 'aria' ); ?></p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
else :
	aria_page_head( __( 'Customer account', 'aria' ), __( 'For importers, wholesalers and manufacturers: keep your company details on file and follow your quote and sample requests.', 'aria' ) );
	$mode = in_array( $tab, array( 'register', 'lost' ), true ) ? $tab : 'login';
	?>
	<div class="wrap section section--tight account account--guest">
		<div class="acc-guest">
			<nav class="acc-tabs" aria-label="<?php esc_attr_e( 'Account', 'aria' ); ?>">
				<a href="<?php echo esc_url( aria_account_url() ); ?>"<?php echo 'login' === $mode ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Sign in', 'aria' ); ?></a>
				<a href="<?php echo esc_url( aria_account_url( 'register' ) ); ?>"<?php echo 'register' === $mode ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Create an account', 'aria' ); ?></a>
			</nav>
			<?php if ( $notice ) : ?>
				<p class="form-status<?php echo 'ok' === $notice[1] ? '' : ' is-error'; ?>" role="alert"><?php echo esc_html( $notice[0] ); ?></p>
			<?php endif; ?>

			<?php if ( 'register' === $mode ) : ?>
				<form class="form form-card acc-form" method="post" action="<?php echo esc_url( aria_account_url( 'register' ) ); ?>">
					<?php $nonce( 'register' ); ?>
					<div class="hp" aria-hidden="true"><label>Leave empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
					<div class="form-grid">
						<?php
						$p = wp_unslash( $_POST ); // phpcs:ignore -- refill after a validation error.
						$field( 'full_name', __( 'Your name', 'aria' ), 'text', $p['full_name'] ?? '', 'required autocomplete="name"' );
						$field( 'company', __( 'Company', 'aria' ), 'text', $p['company'] ?? '', 'required autocomplete="organization"' );
						$business_select( $p['business_type'] ?? '' );
						$field( 'country', __( 'Country', 'aria' ), 'text', $p['country'] ?? '', 'required autocomplete="country-name"' );
						$field( 'email', __( 'Business email', 'aria' ), 'email', $p['email'] ?? '', 'required autocomplete="email"' );
						$field( 'whatsapp', __( 'WhatsApp number <span class="opt">(optional)</span>', 'aria' ), 'tel', $p['whatsapp'] ?? '', 'autocomplete="tel"' );
						$field( 'password', __( 'Password <span class="opt">(8+ characters)</span>', 'aria' ), 'password', '', 'required minlength="8" autocomplete="new-password"' );
						?>
					</div>
					<div class="form-foot">
						<button class="btn btn--primary" type="submit"><?php esc_html_e( 'Create account', 'aria' ); ?></button>
						<p class="form-note"><?php esc_html_e( 'Accounts are for businesses buying wholesale or for export.', 'aria' ); ?></p>
					</div>
				</form>
			<?php elseif ( 'lost' === $mode ) : ?>
				<form class="form form-card acc-form" method="post" action="<?php echo esc_url( aria_account_url( 'lost' ) ); ?>">
					<?php $nonce( 'lost' ); ?>
					<p><?php esc_html_e( 'Enter your email and we will send a link to choose a new password.', 'aria' ); ?></p>
					<?php $field( 'user_login', __( 'Email', 'aria' ), 'email', '', 'required autocomplete="email"' ); ?>
					<div class="form-foot"><button class="btn btn--primary" type="submit"><?php esc_html_e( 'Send reset link', 'aria' ); ?></button> <a href="<?php echo esc_url( aria_account_url() ); ?>"><?php esc_html_e( 'Back to sign in', 'aria' ); ?></a></div>
				</form>
			<?php else : ?>
				<form class="form form-card acc-form" method="post" action="<?php echo esc_url( aria_account_url() ); ?>">
					<?php $nonce( 'login' ); ?>
					<?php
					$field( 'log', __( 'Email', 'aria' ), 'text', '', 'required autocomplete="username"' );
					$field( 'pwd', __( 'Password', 'aria' ), 'password', '', 'required autocomplete="current-password"' );
					?>
					<p class="field-check"><label><input type="checkbox" name="rememberme" value="1" checked> <?php esc_html_e( 'Keep me signed in', 'aria' ); ?></label></p>
					<div class="form-foot"><button class="btn btn--primary" type="submit"><?php esc_html_e( 'Sign in', 'aria' ); ?></button> <a href="<?php echo esc_url( aria_account_url( 'lost' ) ); ?>"><?php esc_html_e( 'Forgot your password?', 'aria' ); ?></a></div>
				</form>
			<?php endif; ?>
		</div>
		<aside class="acc-why">
			<h2 class="h3"><?php esc_html_e( 'Why open an account', 'aria' ); ?></h2>
			<ul class="ticks">
				<li><?php esc_html_e( 'Your company details fill in every quote and sample form', 'aria' ); ?></li>
				<li><?php esc_html_e( 'See all your requests in one place', 'aria' ); ?></li>
				<li><?php esc_html_e( 'Faster replies from the trade desk, who see your history', 'aria' ); ?></li>
			</ul>
			<?php aria_contact_list(); ?>
		</aside>
	</div>
	<?php
endif;

get_footer();
