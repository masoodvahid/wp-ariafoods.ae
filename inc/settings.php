<?php
/**
 * "Aria settings" admin menu: notifications (e-mail recipients, Telegram bot),
 * e-mail delivery (SMTP), spam protection (Turnstile) and fonts – in one place.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_menu_page( __( 'Aria settings', 'aria' ), __( 'Aria settings', 'aria' ), 'manage_options', 'aria-settings', 'aria_settings_page', 'dashicons-admin-settings', 3 );
} );

/* Old address of the e-mail page. */
add_action( 'admin_init', function () {
	if ( isset( $_GET['page'] ) && 'aria-mail' === $_GET['page'] ) { // phpcs:ignore
		wp_safe_redirect( admin_url( 'admin.php?page=aria-settings&tab=email' ) );
		exit;
	}
} );

function aria_settings_url( $tab, $args = array() ) {
	return add_query_arg( array_merge( array( 'page' => 'aria-settings', 'tab' => $tab ), $args ), admin_url( 'admin.php' ) );
}

/* ---------- Save handlers ---------- */

add_action( 'admin_post_aria_settings', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed', 403 );
	}
	check_admin_referer( 'aria_settings' );
	$tab = isset( $_POST['tab'] ) ? sanitize_key( $_POST['tab'] ) : 'notify';
	$in  = isset( $_POST['s'] ) ? (array) wp_unslash( $_POST['s'] ) : array(); // phpcs:ignore
	$msg = 'saved';

	if ( 'notify' === $tab ) {
		$emails = array_filter( array_map( 'sanitize_email', preg_split( '/[\s,;]+/', (string) ( $in['emails'] ?? '' ) ) ), 'is_email' );
		update_option( 'aria_leads_emails', implode( "\n", array_unique( $emails ) ), false );
		$tg = array( 'bot' => sanitize_text_field( ltrim( (string) ( $in['bot'] ?? '' ), '@' ) ) );
		if ( '' !== (string) ( $in['token'] ?? '' ) ) {
			$tg['token'] = sanitize_text_field( $in['token'] );
		}
		if ( ! empty( $in['token_clear'] ) ) {
			$tg['token']  = '';
			$tg['hooked'] = 0;
		}
		aria_tg_save( $tg );
		if ( ! empty( $tg['token'] ) ) {
			$msg = aria_tg_connect() ? 'tg_ok' : 'tg_fail';
		}
	} elseif ( 'email' === $tab ) {
		$old = (array) get_option( 'aria_mail', array() );
		update_option( 'aria_mail', array_merge( $old, array(
			'smtp'   => empty( $in['smtp'] ) ? 0 : 1,
			'host'   => sanitize_text_field( $in['host'] ?? '' ),
			'port'   => absint( $in['port'] ?? 587 ),
			'secure' => in_array( $in['secure'] ?? '', array( 'tls', 'ssl', '' ), true ) ? $in['secure'] : 'tls',
			'user'   => sanitize_text_field( $in['user'] ?? '' ),
			'from'   => sanitize_email( $in['from'] ?? '' ),
			'pass'   => ( '' !== (string) ( $in['pass'] ?? '' ) ) ? (string) $in['pass'] : ( $old['pass'] ?? '' ),
		) ), false );
	} elseif ( 'spam' === $tab ) {
		$old = (array) get_option( 'aria_mail', array() );
		$new = array(
			'ts_site'   => sanitize_text_field( $in['ts_site'] ?? '' ),
			'ts_secret' => ( '' !== (string) ( $in['ts_secret'] ?? '' ) ) ? sanitize_text_field( $in['ts_secret'] ) : ( $old['ts_secret'] ?? '' ),
		);
		if ( ! empty( $in['ts_clear'] ) ) {
			$new = array( 'ts_site' => '', 'ts_secret' => '' );
		}
		update_option( 'aria_mail', array_merge( $old, $new ), false );
	} elseif ( 'fonts' === $tab ) {
		$lib = aria_font_library();
		foreach ( array( 'head', 'body' ) as $r ) {
			$v = (string) ( $in[ 'font_' . $r ] ?? '' );
			set_theme_mod( 'aria_font_' . $r, isset( $lib[ $v ] ) ? $v : '' );
		}
	}
	wp_safe_redirect( aria_settings_url( $tab, array( 'msg' => $msg ) ) );
	exit;
} );

add_action( 'admin_post_aria_settings_do', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed', 403 );
	}
	check_admin_referer( 'aria_settings_do' );
	$do  = isset( $_POST['do'] ) ? sanitize_key( $_POST['do'] ) : '';
	$tab = 'notify';
	$msg = '';
	switch ( $do ) {
		case 'tg_connect':
			$msg = aria_tg_connect() ? 'tg_ok' : 'tg_fail';
			break;
		case 'tg_newcode':
			aria_tg_save( array( 'code' => strtoupper( wp_generate_password( 8, false, false ) ) ) );
			$msg = 'code';
			break;
		case 'tg_remove':
			$chat  = isset( $_POST['chat'] ) ? sanitize_text_field( wp_unslash( $_POST['chat'] ) ) : '';
			$staff = (array) aria_tg_settings()['staff'];
			if ( isset( $staff[ $chat ] ) ) {
				aria_tg_api( 'sendMessage', array( 'chat_id' => $chat, 'text' => 'Your access to Aria Foods inquiries was removed.' ) );
				unset( $staff[ $chat ] );
				aria_tg_save( array( 'staff' => $staff ) );
			}
			$msg = 'removed';
			break;
		case 'tg_test':
			$ok = false;
			foreach ( array_keys( (array) aria_tg_settings()['staff'] ) as $chat ) {
				$ok = (bool) aria_tg_api( 'sendMessage', array( 'chat_id' => $chat, 'text' => '✅ Test from ' . wp_parse_url( home_url(), PHP_URL_HOST ) . ': Telegram alerts are working.' ) ) || $ok;
			}
			$msg = $ok ? 'tg_test_ok' : 'tg_fail';
			break;
		case 'mail_test':
			$tab = isset( $_POST['tab'] ) ? sanitize_key( $_POST['tab'] ) : 'notify';
			delete_option( 'aria_mail_error' );
			$ok  = wp_mail( aria_leads_recipients(), '[Aria Foods] Test email', "This is a test from Aria settings.\n\nIf you can read this, inquiry notifications reach this inbox.\n\nSent: " . wp_date( 'Y-m-d H:i:s' ) );
			$msg = $ok && ! get_option( 'aria_mail_error' ) ? 'mail_ok' : 'mail_fail';
			break;
	}
	wp_safe_redirect( aria_settings_url( $tab, array( 'msg' => $msg ) ) );
	exit;
} );

/* ---------- Page ---------- */

function aria_settings_do_button( $do, $label, $class = 'secondary', $extra = array() ) {
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;margin:0 6px 6px 0">';
	wp_nonce_field( 'aria_settings_do' );
	echo '<input type="hidden" name="action" value="aria_settings_do"><input type="hidden" name="do" value="' . esc_attr( $do ) . '">';
	foreach ( $extra as $k => $v ) {
		echo '<input type="hidden" name="' . esc_attr( $k ) . '" value="' . esc_attr( $v ) . '">';
	}
	echo '<button class="button button-' . esc_attr( $class ) . '">' . esc_html( $label ) . '</button></form>';
}

function aria_settings_page() {
	$tabs = array(
		'notify' => __( 'Notifications', 'aria' ),
		'email'  => __( 'Email delivery', 'aria' ),
		'spam'   => __( 'Spam protection', 'aria' ),
		'fonts'  => __( 'Fonts', 'aria' ),
	);
	$tab  = isset( $_GET['tab'], $tabs[ $_GET['tab'] ] ) ? sanitize_key( $_GET['tab'] ) : 'notify'; // phpcs:ignore
	$msg  = isset( $_GET['msg'] ) ? sanitize_key( $_GET['msg'] ) : ''; // phpcs:ignore
	$msgs = array(
		'saved'      => array( 'success', __( 'Settings saved.', 'aria' ) ),
		'tg_ok'      => array( 'success', __( 'Telegram bot connected to this site.', 'aria' ) ),
		'tg_test_ok' => array( 'success', __( 'Test message sent to all staff members.', 'aria' ) ),
		'tg_fail'    => array( 'error', __( 'Telegram did not accept the request.', 'aria' ) . ' ' . ( get_option( 'aria_tg_error' ) ? get_option( 'aria_tg_error' )['message'] : '' ) ),
		'code'       => array( 'success', __( 'New join link created. The old link no longer works; current members stay.', 'aria' ) ),
		'removed'    => array( 'success', __( 'Staff member removed.', 'aria' ) ),
		'mail_ok'    => array( 'success', __( 'The server accepted the test email. Check the inbox (and spam folder) of each recipient.', 'aria' ) ),
		'mail_fail'  => array( 'error', __( 'The test email could not be sent:', 'aria' ) . ' ' . ( get_option( 'aria_mail_error' ) ? get_option( 'aria_mail_error' )['message'] : '' ) ),
	);
	?>
	<div class="wrap aria-set">
		<h1><?php esc_html_e( 'Aria settings', 'aria' ); ?></h1>
		<?php if ( isset( $msgs[ $msg ] ) ) : ?>
			<div class="notice notice-<?php echo esc_attr( $msgs[ $msg ][0] ); ?> is-dismissible"><p><?php echo esc_html( $msgs[ $msg ][1] ); ?></p></div>
		<?php endif; ?>
		<nav class="nav-tab-wrapper">
			<?php foreach ( $tabs as $k => $l ) : ?>
				<a class="nav-tab<?php echo $k === $tab ? ' nav-tab-active' : ''; ?>" href="<?php echo esc_url( aria_settings_url( $k ) ); ?>"><?php echo esc_html( $l ); ?></a>
			<?php endforeach; ?>
		</nav>
		<style>.aria-set .card{max-width:860px;padding:8px 22px 18px}.aria-set .card h2{margin-top:14px}.aria-set code.big{font-size:13px;padding:6px 10px;display:inline-block}.aria-set .staff td,.aria-set .staff th{vertical-align:middle}.aria-set .ok{color:#1f8a5b;font-weight:600}.aria-set .bad{color:#b32d2e;font-weight:600}</style>
		<?php call_user_func( 'aria_settings_tab_' . $tab ); ?>
	</div>
	<?php
}

function aria_settings_form_open( $tab ) {
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'aria_settings' );
	echo '<input type="hidden" name="action" value="aria_settings"><input type="hidden" name="tab" value="' . esc_attr( $tab ) . '">';
}

function aria_settings_tab_notify() {
	$tg     = aria_tg_settings();
	$emails = (string) get_option( 'aria_leads_emails', '' );
	if ( '' === $emails ) {
		$emails = implode( "\n", aria_leads_recipients() );
	}
	$const = defined( 'ARIA_TG_TOKEN' ) && ARIA_TG_TOKEN;
	$link  = 'https://t.me/' . rawurlencode( $tg['bot'] ) . '?start=' . rawurlencode( $tg['code'] );
	$err   = get_option( 'aria_tg_error' );
	aria_settings_form_open( 'notify' );
	?>
	<div class="card">
		<h2><?php esc_html_e( 'Email recipients', 'aria' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Every quote, sample and contact request is emailed to these addresses – one per line.', 'aria' ); ?></p>
		<textarea name="s[emails]" rows="4" class="large-text code"><?php echo esc_textarea( $emails ); ?></textarea>
	</div>
	<div class="card">
		<h2><?php esc_html_e( 'Telegram bot', 'aria' ); ?></h2>
		<p class="description"><?php esc_html_e( 'New inquiries are sent to staff in Telegram with buttons to change the status. Create the bot with @BotFather, paste its token here and save – the site connects the bot automatically.', 'aria' ); ?></p>
		<table class="form-table" role="presentation">
			<tr><th><label for="tg-bot"><?php esc_html_e( 'Bot username', 'aria' ); ?></label></th><td>@<input id="tg-bot" name="s[bot]" value="<?php echo esc_attr( $tg['bot'] ); ?>" class="regular-text"></td></tr>
			<tr><th><label for="tg-token"><?php esc_html_e( 'Bot token', 'aria' ); ?></label></th><td>
				<?php if ( $const ) : ?>
					<em><?php esc_html_e( 'Set in wp-config.php (ARIA_TG_TOKEN).', 'aria' ); ?></em>
				<?php else : ?>
					<input id="tg-token" type="password" name="s[token]" class="regular-text" autocomplete="new-password" placeholder="<?php echo $tg['token'] ? esc_attr__( 'saved – type to replace', 'aria' ) : '123456789:AA…'; ?>">
					<?php if ( $tg['token'] ) : ?><label style="margin-left:10px"><input type="checkbox" name="s[token_clear]" value="1"> <?php esc_html_e( 'Remove token (turn alerts off)', 'aria' ); ?></label><?php endif; ?>
				<?php endif; ?>
			</td></tr>
			<tr><th><?php esc_html_e( 'Connection', 'aria' ); ?></th><td>
				<?php if ( ! $tg['token'] ) : ?>
					<span class="bad"><?php esc_html_e( 'No token yet', 'aria' ); ?></span>
				<?php elseif ( $tg['hooked'] ) : ?>
					<span class="ok">✓ <?php echo esc_html( sprintf( __( 'Connected %s', 'aria' ), wp_date( 'j M Y H:i', $tg['hooked'] ) ) ); ?></span>
				<?php else : ?>
					<span class="bad"><?php esc_html_e( 'Not connected yet', 'aria' ); ?></span>
				<?php endif; ?>
				<?php if ( $err ) : ?><p class="description"><?php echo esc_html( sprintf( __( 'Last error (%1$s): %2$s', 'aria' ), wp_date( 'j M H:i', $err['time'] ), $err['message'] ) ); ?></p><?php endif; ?>
			</td></tr>
		</table>
	</div>
	<?php submit_button( __( 'Save notifications', 'aria' ) ); ?>
	</form>

	<div class="card">
		<h2><?php esc_html_e( 'Staff in the bot', 'aria' ); ?></h2>
		<p><?php esc_html_e( 'Only people with this private link can join. Send it to company staff only (in Telegram they tap Start).', 'aria' ); ?></p>
		<p><code class="big" id="tg-link"><?php echo esc_html( $link ); ?></code> <button type="button" class="button" onclick="navigator.clipboard.writeText(document.getElementById('tg-link').textContent);this.textContent='Copied'"><?php esc_html_e( 'Copy', 'aria' ); ?></button></p>
		<?php
		$staff = (array) $tg['staff'];
		if ( $staff ) {
			echo '<table class="widefat striped staff"><thead><tr><th>' . esc_html__( 'Name', 'aria' ) . '</th><th>' . esc_html__( 'Telegram', 'aria' ) . '</th><th>' . esc_html__( 'Joined', 'aria' ) . '</th><th></th></tr></thead><tbody>';
			foreach ( $staff as $chat => $p ) {
				echo '<tr><td>' . esc_html( $p['name'] ) . '</td><td>' . ( $p['username'] ? '@' . esc_html( $p['username'] ) : '—' ) . '</td><td>' . esc_html( wp_date( 'j M Y', $p['joined'] ) ) . '</td><td style="text-align:right">';
				aria_settings_do_button( 'tg_remove', __( 'Remove', 'aria' ), 'link-delete', array( 'chat' => $chat ) );
				echo '</td></tr>';
			}
			echo '</tbody></table><p></p>';
		} else {
			echo '<p class="description">' . esc_html__( 'Nobody has joined yet.', 'aria' ) . '</p>';
		}
		if ( $tg['token'] ) {
			aria_settings_do_button( 'tg_test', __( 'Send test message', 'aria' ) );
			aria_settings_do_button( 'tg_connect', __( 'Reconnect bot', 'aria' ) );
		}
		aria_settings_do_button( 'tg_newcode', __( 'New join link (old link stops working)', 'aria' ) );
		aria_settings_do_button( 'mail_test', __( 'Send test email', 'aria' ), 'secondary', array( 'tab' => 'notify' ) );
		?>
	</div>
	<?php
}

function aria_settings_tab_email() {
	$s     = aria_mail_settings();
	$err   = get_option( 'aria_mail_error' );
	$smtp  = $s['smtp'] && $s['host'] && $s['user'] && $s['pass'];
	$const = defined( 'ARIA_SMTP_PASS' ) && ARIA_SMTP_PASS;
	?>
	<div class="card">
		<h2><?php esc_html_e( 'Status', 'aria' ); ?></h2>
		<table class="widefat striped"><tbody>
			<tr><th style="width:200px"><?php esc_html_e( 'Recipients', 'aria' ); ?></th><td><code><?php echo esc_html( implode( ', ', aria_leads_recipients() ) ); ?></code></td></tr>
			<tr><th><?php esc_html_e( 'Sent from', 'aria' ); ?></th><td><code><?php echo esc_html( aria_mail_from_address() ); ?></code> <span class="description"><?php esc_html_e( '(envelope sender matches, for SPF/DMARC)', 'aria' ); ?></span></td></tr>
			<tr><th><?php esc_html_e( 'Method', 'aria' ); ?></th><td><?php echo $smtp ? esc_html( sprintf( 'SMTP – %s:%d', $s['host'], $s['port'] ) ) : esc_html__( 'Server mail (PHP) – SMTP recommended', 'aria' ); ?></td></tr>
			<tr><th><?php esc_html_e( 'Last error', 'aria' ); ?></th><td><?php echo $err ? esc_html( wp_date( 'Y-m-d H:i', $err['time'] ) . ' – ' . $err['message'] ) : esc_html__( 'none recorded', 'aria' ); ?></td></tr>
		</tbody></table>
		<p><?php aria_settings_do_button( 'mail_test', __( 'Send test email', 'aria' ), 'secondary', array( 'tab' => 'email' ) ); ?></p>
	</div>
	<?php aria_settings_form_open( 'email' ); ?>
	<div class="card">
		<h2><?php esc_html_e( 'Send through a mailbox (SMTP)', 'aria' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Create a mailbox such as noreply@ariafoods.ae in the hosting panel and enter it here. Your mail server then signs and sends the email, which passes the domain’s strict DMARC policy.', 'aria' ); ?></p>
		<table class="form-table" role="presentation">
			<tr><th><?php esc_html_e( 'Use SMTP', 'aria' ); ?></th><td><label><input type="checkbox" name="s[smtp]" value="1" <?php checked( $s['smtp'] ); ?>> <?php esc_html_e( 'Send all site email through this mailbox', 'aria' ); ?></label></td></tr>
			<tr><th><label for="am-host"><?php esc_html_e( 'SMTP server', 'aria' ); ?></label></th><td><input id="am-host" class="regular-text" name="s[host]" value="<?php echo esc_attr( $s['host'] ); ?>"></td></tr>
			<tr><th><label for="am-port"><?php esc_html_e( 'Port and security', 'aria' ); ?></label></th><td><input id="am-port" class="small-text" name="s[port]" value="<?php echo esc_attr( $s['port'] ); ?>">
				<select name="s[secure]"><option value="tls" <?php selected( $s['secure'], 'tls' ); ?>>STARTTLS (587)</option><option value="ssl" <?php selected( $s['secure'], 'ssl' ); ?>>SSL (465)</option><option value="" <?php selected( $s['secure'], '' ); ?>><?php esc_html_e( 'None', 'aria' ); ?></option></select></td></tr>
			<tr><th><label for="am-user"><?php esc_html_e( 'Mailbox (username)', 'aria' ); ?></label></th><td><input id="am-user" class="regular-text" name="s[user]" value="<?php echo esc_attr( $s['user'] ); ?>" placeholder="noreply@<?php echo esc_attr( aria_mail_domain() ); ?>" autocomplete="off"></td></tr>
			<tr><th><label for="am-pass"><?php esc_html_e( 'Password', 'aria' ); ?></label></th><td>
				<?php if ( $const ) : ?><em><?php esc_html_e( 'Set in wp-config.php (ARIA_SMTP_PASS).', 'aria' ); ?></em>
				<?php else : ?><input id="am-pass" class="regular-text" type="password" name="s[pass]" autocomplete="new-password" placeholder="<?php echo $s['pass'] ? esc_attr__( 'saved – type to replace', 'aria' ) : ''; ?>"><?php endif; ?>
			</td></tr>
			<tr><th><label for="am-from"><?php esc_html_e( 'From address without SMTP', 'aria' ); ?></label></th><td><input id="am-from" class="regular-text" name="s[from]" value="<?php echo esc_attr( $s['from'] ); ?>" placeholder="noreply@<?php echo esc_attr( aria_mail_domain() ); ?>"></td></tr>
		</table>
	</div>
	<?php
	submit_button();
	echo '</form>';
}

function aria_settings_tab_spam() {
	aria_settings_form_open( 'spam' );
	?>
	<div class="card">
		<h2><?php esc_html_e( 'Built-in protection (always on)', 'aria' ); ?></h2>
		<ul style="list-style:disc;margin-left:20px">
			<li><?php esc_html_e( 'Signed form token – copied or forged forms are rejected; so are forms sent in under 4 seconds.', 'aria' ); ?></li>
			<li><?php esc_html_e( 'Human check – the form only counts after a real click, tap or key press.', 'aria' ); ?></li>
			<li><?php esc_html_e( 'Hidden trap field and a limit of 6 requests per hour from one network.', 'aria' ); ?></li>
			<li><?php esc_html_e( 'Content filter – links, spam phrases and throw-away addresses are filed as [Spam] without an email or Telegram alert.', 'aria' ); ?></li>
		</ul>
	</div>
	<div class="card">
		<h2><?php esc_html_e( 'Cloudflare Turnstile (optional)', 'aria' ); ?></h2>
		<p class="description"><?php esc_html_e( 'In Cloudflare: Turnstile → Add widget → domain ariafoods.ae, mode Managed. Paste both keys. Usually invisible to real buyers.', 'aria' ); ?></p>
		<table class="form-table" role="presentation">
			<tr><th><label for="ts-site"><?php esc_html_e( 'Site key', 'aria' ); ?></label></th><td><input id="ts-site" class="regular-text" name="s[ts_site]" value="<?php echo esc_attr( aria_turnstile_site() ); ?>" autocomplete="off"></td></tr>
			<tr><th><label for="ts-secret"><?php esc_html_e( 'Secret key', 'aria' ); ?></label></th><td><input id="ts-secret" class="regular-text" type="password" name="s[ts_secret]" autocomplete="new-password" placeholder="<?php echo aria_turnstile_secret() ? esc_attr__( 'saved – type to replace', 'aria' ) : ''; ?>">
				<p><label><input type="checkbox" name="s[ts_clear]" value="1"> <?php esc_html_e( 'Turn Turnstile off (clear both keys)', 'aria' ); ?></label></p></td></tr>
			<tr><th><?php esc_html_e( 'Status', 'aria' ); ?></th><td><?php echo aria_turnstile_keys() ? '<span class="ok">✓ ' . esc_html__( 'On', 'aria' ) . '</span>' : esc_html__( 'Off', 'aria' ); ?></td></tr>
		</table>
	</div>
	<?php
	submit_button();
	echo '</form>';
}

function aria_settings_tab_fonts() {
	$lib = aria_font_library();
	aria_settings_form_open( 'fonts' );
	?>
	<div class="card">
		<h2><?php esc_html_e( 'Theme fonts', 'aria' ); ?></h2>
		<p class="description"><?php esc_html_e( '“Theme default” is self-hosted and fastest. Other fonts load from Google Fonts. Purge the Cloudflare cache after changing.', 'aria' ); ?></p>
		<table class="form-table" role="presentation">
			<?php foreach ( array( 'head' => __( 'Headings', 'aria' ), 'body' => __( 'Body text', 'aria' ) ) as $r => $label ) : $cur = aria_font_choice( $r ); ?>
				<tr><th><label for="f-<?php echo esc_attr( $r ); ?>"><?php echo esc_html( $label ); ?></label></th><td>
					<select id="f-<?php echo esc_attr( $r ); ?>" name="s[font_<?php echo esc_attr( $r ); ?>]" data-preview="pv-<?php echo esc_attr( $r ); ?>">
						<option value=""><?php esc_html_e( 'Theme default', 'aria' ); ?></option>
						<?php foreach ( array_keys( $lib ) as $f ) : ?>
							<option <?php selected( $cur, $f ); ?>><?php echo esc_html( $f ); ?></option>
						<?php endforeach; ?>
					</select>
					<p id="pv-<?php echo esc_attr( $r ); ?>" style="font-size:<?php echo 'head' === $r ? '26px;font-weight:700' : '16px'; ?>;margin:10px 0 0;font-family:'<?php echo esc_attr( $cur ); ?>',sans-serif"><?php echo 'head' === $r ? 'Iranian dates, supplied by the container' : 'Mazafati, Piarom and Zahedi dates in full containers from our Dubai warehouse.'; ?></p>
				</td></tr>
			<?php endforeach; ?>
		</table>
	</div>
	<script>
	document.querySelectorAll('select[data-preview]').forEach(function (s) {
		var load = function () {
			var f = s.value, p = document.getElementById(s.dataset.preview);
			if (f) { var l = document.createElement('link'); l.rel = 'stylesheet'; l.href = 'https://fonts.googleapis.com/css2?family=' + f.replace(/ /g, '+') + ':wght@400;700&display=swap'; document.head.appendChild(l); }
			p.style.fontFamily = f ? "'" + f + "',sans-serif" : '';
		};
		s.addEventListener('change', load); if (s.value) load();
	});
	</script>
	<?php
	submit_button();
	echo '</form>';
}
