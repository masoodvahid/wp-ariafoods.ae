<?php
/**
 * Dashboard widgets: inquiry overview, inquiries per month by order size, inquiries by country.
 *
 * Country = destination country read from the "Port / country" field (port and city names are
 * mapped to their country); when that is empty or unknown, the visitor's country from Cloudflare.
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

/** Country for a port, city or country name typed by the buyer. */
function aria_guess_country( $text ) {
	$t = ' ' . strtolower( remove_accents( (string) $text ) ) . ' ';
	if ( '  ' === $t ) {
		return '';
	}
	$map = array(
		'India'        => 'india|nhava sheva|nhava|jnpt|jawaharlal|mundra|chennai|madras|kolkata|calcutta|mumbai|bombay|kandla|deendayal|delhi|cochin|kochi|tuticorin|vizag|visakhapatnam|pipavav|hazira|ahmedabad|bangalore|bengaluru|hyderabad|ludhiana|amritsar',
		'Pakistan'     => 'pakistan|karachi|qasim|lahore|faisalabad|peshawar|quetta|islamabad|gwadar',
		'Bangladesh'   => 'bangladesh|chattogram|chittagong|mongla|dhaka|pangaon',
		'Malaysia'     => 'malaysia|port klang|klang|penang|pasir gudang|tanjung pelepas|kuala lumpur|johor|kuching|kota kinabalu',
		'Turkey'       => 'turkey|turkiye|mersin|istanbul|ambarli|izmir|aliaga|gemlik|ankara',
		'Spain'        => 'spain|espana|valencia|algeciras|barcelona|madrid|bilbao',
		'Morocco'      => 'morocco|maroc|casablanca|tanger|tangier|agadir|rabat',
		'Saudi Arabia' => 'saudi|ksa|jeddah|jiddah|dammam|riyadh|makkah|mecca|madinah|medina',
		'Oman'         => 'oman|sohar|muscat|salalah|duqm',
		'Kazakhstan'   => 'kazakhstan|almaty|astana|nur-sultan|shymkent|aktau',
		'Tajikistan'   => 'tajikistan|dushanbe|khujand|kulob|bokhtar',
		'Uzbekistan'   => 'uzbekistan|tashkent|samarkand',
		'Afghanistan'  => 'afghanistan|kabul|herat|kandahar|mazar',
		'Iraq'         => 'iraq|umm qasr|basra|baghdad|erbil',
		'UAE'          => 'uae|emirates|jebel ali|dubai|sharjah|abu dhabi|khalifa|ajman',
		'Qatar'        => 'qatar|doha|hamad',
		'Kuwait'       => 'kuwait|shuwaikh|shuaiba',
		'Bahrain'      => 'bahrain|manama|khalifa bin salman',
		'Yemen'        => 'yemen|aden|hodeidah',
		'Egypt'        => 'egypt|alexandria|port said|sokhna|cairo|damietta',
		'Indonesia'    => 'indonesia|jakarta|tanjung priok|surabaya|medan|belawan|semarang',
		'Singapore'    => 'singapore',
		'Sri Lanka'    => 'sri lanka|colombo',
		'Nepal'        => 'nepal|kathmandu|birgunj',
		'China'        => 'china|shanghai|ningbo|shenzhen|guangzhou|qingdao|tianjin|xiamen',
		'Russia'       => 'russia|moscow|novorossiysk|st petersburg|saint petersburg|vladivostok|astrakhan',
		'Germany'      => 'germany|hamburg|bremerhaven|bremen|berlin|frankfurt',
		'Netherlands'  => 'netherlands|holland|rotterdam|amsterdam',
		'Belgium'      => 'belgium|antwerp',
		'France'       => 'france|marseille|le havre|paris|fos',
		'Italy'        => 'italy|genoa|genova|la spezia|naples|salerno|milan',
		'UK'           => 'united kingdom|england|felixstowe|southampton|london|liverpool|manchester| uk ',
		'USA'          => 'united states|usa| us |new york|new jersey|los angeles|long beach|houston|savannah|chicago|miami|seattle',
		'Canada'       => 'canada|toronto|vancouver|montreal|halifax',
		'Australia'    => 'australia|sydney|melbourne|brisbane|fremantle',
		'South Africa' => 'south africa|durban|cape town|johannesburg',
		'Kenya'        => 'kenya|mombasa|nairobi',
		'Tanzania'     => 'tanzania|dar es salaam',
		'Algeria'      => 'algeria|algiers|oran',
		'Tunisia'      => 'tunisia|tunis|rades|sfax',
		'Libya'        => 'libya|tripoli|benghazi|misrata',
		'Jordan'       => 'jordan|aqaba|amman',
		'Lebanon'      => 'lebanon|beirut',
		'Syria'        => 'syria|latakia|tartus|damascus',
		'Azerbaijan'   => 'azerbaijan|baku',
		'Georgia'      => 'georgia|poti|batumi|tbilisi',
		'Armenia'      => 'armenia|yerevan',
		'Turkmenistan' => 'turkmenistan|ashgabat|turkmenbashi',
		'Kyrgyzstan'   => 'kyrgyzstan|bishkek',
		'Iran'         => 'iran|bandar abbas|tehran|shahid rajaee',
		'Thailand'     => 'thailand|bangkok|laem chabang',
		'Vietnam'      => 'vietnam|ho chi minh|haiphong',
		'Philippines'  => 'philippines|manila',
		'Brunei'       => 'brunei',
		'Maldives'     => 'maldives|male',
		'Mauritius'    => 'mauritius|port louis',
		'Nigeria'      => 'nigeria|lagos|apapa',
		'Senegal'      => 'senegal|dakar',
		'Sudan'        => 'sudan|port sudan|khartoum',
		'Somalia'      => 'somalia|mogadishu|berbera',
		'Djibouti'     => 'djibouti',
		'Poland'       => 'poland|gdansk|gdynia|warsaw',
		'Sweden'       => 'sweden|gothenburg|stockholm',
		'Greece'       => 'greece|piraeus|thessaloniki|athens',
		'Bosnia'       => 'bosnia|sarajevo',
		'Albania'      => 'albania|durres|tirana',
	);
	foreach ( $map as $country => $re ) {
		if ( preg_match( '~(?<![a-z])(' . $re . ')(?![a-z])~', $t ) ) {
			return $country;
		}
	}
	return '';
}

function aria_country_name( $code ) {
	$c = array(
		'IN' => 'India', 'PK' => 'Pakistan', 'BD' => 'Bangladesh', 'MY' => 'Malaysia', 'TR' => 'Turkey', 'ES' => 'Spain', 'MA' => 'Morocco',
		'SA' => 'Saudi Arabia', 'OM' => 'Oman', 'KZ' => 'Kazakhstan', 'TJ' => 'Tajikistan', 'AE' => 'UAE', 'QA' => 'Qatar', 'KW' => 'Kuwait',
		'BH' => 'Bahrain', 'IQ' => 'Iraq', 'AF' => 'Afghanistan', 'UZ' => 'Uzbekistan', 'EG' => 'Egypt', 'ID' => 'Indonesia', 'SG' => 'Singapore',
		'LK' => 'Sri Lanka', 'NP' => 'Nepal', 'CN' => 'China', 'RU' => 'Russia', 'DE' => 'Germany', 'NL' => 'Netherlands', 'BE' => 'Belgium',
		'FR' => 'France', 'IT' => 'Italy', 'GB' => 'UK', 'US' => 'USA', 'CA' => 'Canada', 'AU' => 'Australia', 'ZA' => 'South Africa',
		'KE' => 'Kenya', 'TZ' => 'Tanzania', 'DZ' => 'Algeria', 'TN' => 'Tunisia', 'LY' => 'Libya', 'JO' => 'Jordan', 'LB' => 'Lebanon',
		'AZ' => 'Azerbaijan', 'GE' => 'Georgia', 'AM' => 'Armenia', 'TM' => 'Turkmenistan', 'KG' => 'Kyrgyzstan', 'IR' => 'Iran',
		'TH' => 'Thailand', 'VN' => 'Vietnam', 'PH' => 'Philippines', 'NG' => 'Nigeria', 'SE' => 'Sweden', 'PL' => 'Poland', 'GR' => 'Greece',
	);
	return isset( $c[ $code ] ) ? $c[ $code ] : ( $code && 'XX' !== $code && 'T1' !== $code ? $code : '' );
}

/** Order-size bucket for a quantity choice. */
function aria_qty_bucket( $q ) {
	$q = strtolower( (string) $q );
	if ( '' === $q ) {
		return 'none';
	}
	if ( false !== strpos( $q, 'more than 5' ) || false !== strpos( $q, 'regular' ) ) {
		return 'big';
	}
	if ( false !== strpos( $q, '2–5' ) || false !== strpos( $q, '2-5' ) ) {
		return 'multi';
	}
	if ( false !== strpos( $q, '40 ft' ) ) {
		return '40';
	}
	if ( false !== strpos( $q, '20 ft' ) ) {
		return '20';
	}
	return 'none';
}

/** All numbers the widgets need, cached for 10 minutes. */
function aria_dash_data() {
	$data = get_transient( 'aria_dash' );
	if ( false !== $data ) {
		return $data;
	}
	global $wpdb;
	$rows = $wpdb->get_results( "SELECT p.ID, p.post_date, MAX(CASE WHEN m.meta_key='_aria_lead' THEN m.meta_value END) AS d,
		MAX(CASE WHEN m.meta_key='_aria_lead_status' THEN m.meta_value END) AS st,
		MAX(CASE WHEN m.meta_key='_aria_spam' THEN 1 END) AS spam,
		MAX(CASE WHEN m.meta_key='_aria_ip_country' THEN m.meta_value END) AS ipc,
		MAX(CASE WHEN m.meta_key='_aria_lead_type' THEN m.meta_value END) AS type
		FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID
		WHERE p.post_type = 'aria_lead' AND p.post_status = 'private'
		GROUP BY p.ID" );

	$months = array();
	for ( $i = 11; $i >= 0; $i-- ) {
		$k            = wp_date( 'Y-m', strtotime( "first day of -$i month", current_time( 'timestamp' ) ) ); // phpcs:ignore
		$months[ $k ] = array( '20' => 0, '40' => 0, 'multi' => 0, 'big' => 0, 'none' => 0 );
	}
	$status  = array_fill_keys( aria_lead_statuses(), 0 );
	$dest    = array();
	$visitor = array();
	$now     = current_time( 'timestamp' ); // phpcs:ignore
	$c       = array( 'total' => 0, 'spam' => 0, 'today' => 0, 'last30' => 0, 'prev30' => 0, 'month' => 0, 'orders' => 0 );
	foreach ( $rows as $r ) {
		if ( $r->spam ) {
			++$c['spam'];
			continue;
		}
		++$c['total'];
		$t  = strtotime( $r->post_date );
		$st = $r->st ? $r->st : 'Received';
		if ( isset( $status[ $st ] ) ) {
			++$status[ $st ];
		}
		if ( in_array( $st, array( 'Order confirmed', 'In production', 'Shipped' ), true ) ) {
			++$c['orders'];
		}
		if ( wp_date( 'Y-m-d', $t ) === wp_date( 'Y-m-d', $now ) ) {
			++$c['today'];
		}
		if ( $t >= $now - 30 * DAY_IN_SECONDS ) {
			++$c['last30'];
		} elseif ( $t >= $now - 60 * DAY_IN_SECONDS ) {
			++$c['prev30'];
		}
		$mk = wp_date( 'Y-m', $t );
		if ( $mk === wp_date( 'Y-m', $now ) ) {
			++$c['month'];
		}
		$d = maybe_unserialize( $r->d );
		$d = is_array( $d ) ? $d : array();
		if ( isset( $months[ $mk ] ) ) {
			++$months[ $mk ][ aria_qty_bucket( isset( $d['Quantity'] ) ? $d['Quantity'] : '' ) ];
		}
		$vc = aria_country_name( (string) $r->ipc );
		$dc = aria_guess_country( isset( $d['Port / country'] ) ? $d['Port / country'] : '' );
		$dc = $dc ? $dc : ( $vc ? $vc : __( 'Unknown', 'aria' ) );
		$dest[ $dc ] = ( isset( $dest[ $dc ] ) ? $dest[ $dc ] : 0 ) + 1;
		$vc          = $vc ? $vc : __( 'Unknown', 'aria' );
		$visitor[ $vc ] = ( isset( $visitor[ $vc ] ) ? $visitor[ $vc ] : 0 ) + 1;
	}
	arsort( $dest );
	arsort( $visitor );
	$data = compact( 'months', 'status', 'dest', 'visitor', 'c' );
	set_transient( 'aria_dash', $data, 10 * MINUTE_IN_SECONDS );
	return $data;
}
foreach ( array( 'aria_new_lead', 'aria_lead_status_changed' ) as $h ) {
	add_action( $h, function () {
		delete_transient( 'aria_dash' );
	} );
}

add_action( 'wp_dashboard_setup', function () {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	wp_add_dashboard_widget( 'aria_dash_overview', __( 'Inquiries – overview', 'aria' ), 'aria_dash_overview' );
	wp_add_dashboard_widget( 'aria_dash_months', __( 'Inquiries per month by order size', 'aria' ), 'aria_dash_months' );
	wp_add_dashboard_widget( 'aria_dash_countries', __( 'Inquiries by country', 'aria' ), 'aria_dash_countries' );
	// Put them first.
	global $wp_meta_boxes;
	$n = $wp_meta_boxes['dashboard']['normal']['core'];
	$a = array();
	foreach ( array( 'aria_dash_overview', 'aria_dash_months', 'aria_dash_countries' ) as $k ) {
		$a[ $k ] = $n[ $k ];
		unset( $n[ $k ] );
	}
	$wp_meta_boxes['dashboard']['normal']['core'] = array_merge( $a, $n ); // phpcs:ignore
} );

function aria_dash_link( $args = array() ) {
	return esc_url( add_query_arg( $args, admin_url( 'edit.php?post_type=aria_lead' ) ) );
}

function aria_dash_overview() {
	$d     = aria_dash_data();
	$c     = $d['c'];
	$delta = $c['prev30'] ? round( ( $c['last30'] - $c['prev30'] ) * 100 / $c['prev30'] ) : null;
	$conv  = $c['total'] ? round( $c['orders'] * 100 / $c['total'] ) : 0;
	$max   = max( 1, max( $d['status'] ) );
	?>
	<div class="ad-tiles">
		<a class="ad-tile" href="<?php echo aria_dash_link( array( 'al_status' => 'Received' ) ); // phpcs:ignore ?>"><strong><?php echo (int) $d['status']['Received']; ?></strong><span><?php esc_html_e( 'Waiting for reply', 'aria' ); ?></span></a>
		<div class="ad-tile"><strong><?php echo (int) $c['last30']; ?></strong><span><?php esc_html_e( 'Last 30 days', 'aria' ); ?>
			<?php if ( null !== $delta ) : ?><em class="<?php echo $delta >= 0 ? 'up' : 'down'; ?>"><?php echo esc_html( ( $delta >= 0 ? '▲ ' : '▼ ' ) . abs( $delta ) . '%' ); ?></em><?php endif; ?></span></div>
		<div class="ad-tile"><strong><?php echo (int) $c['today']; ?></strong><span><?php esc_html_e( 'Today', 'aria' ); ?></span></div>
		<div class="ad-tile"><strong><?php echo (int) $conv; ?>%</strong><span><?php esc_html_e( 'Became orders', 'aria' ); ?></span></div>
	</div>
	<h3 class="ad-h"><?php esc_html_e( 'By status', 'aria' ); ?> <small><?php echo esc_html( sprintf( __( '%d inquiries in total', 'aria' ), $c['total'] ) ); ?></small></h3>
	<ul class="ad-status">
		<?php foreach ( $d['status'] as $st => $n ) : $m = aria_lead_status_meta( $st ); ?>
			<li><a href="<?php echo aria_dash_link( array( 'al_status' => $st ) ); // phpcs:ignore ?>">
				<span class="ad-name"><i style="background:<?php echo esc_attr( $m[0] ); ?>"></i><?php echo esc_html( $st ); ?></span>
				<span class="ad-bar"><b style="width:<?php echo esc_attr( max( $n ? 2 : 0, round( $n * 100 / $max ) ) ); ?>%;background:<?php echo esc_attr( $m[0] ); ?>"></b></span>
				<span class="ad-num"><?php echo (int) $n; ?></span>
			</a></li>
		<?php endforeach; ?>
	</ul>
	<p class="ad-foot"><a href="<?php echo aria_dash_link(); // phpcs:ignore ?>"><?php esc_html_e( 'All inquiries', 'aria' ); ?> →</a>
		<span><?php echo esc_html( sprintf( __( '%d spam blocked', 'aria' ), $c['spam'] ) ); ?></span></p>
	<?php
}

function aria_dash_months() {
	$d      = aria_dash_data();
	$series = array(
		'20'    => array( __( '1 × 20 ft', 'aria' ), '#2a78d6' ),
		'40'    => array( __( '1 × 40 ft', 'aria' ), '#eb6834' ),
		'multi' => array( __( '2–5 containers', 'aria' ), '#1baf7a' ),
		'big'   => array( __( 'More than 5', 'aria' ), '#eda100' ),
		'none'  => array( __( 'Not stated', 'aria' ), '#a7adb4' ),
	);
	$max    = 1;
	foreach ( $d['months'] as $m ) {
		$max = max( $max, array_sum( $m ) );
	}
	$top = max( 4, (int) ceil( $max / 4 ) * 4 );
	?>
	<ul class="ad-legend">
		<?php foreach ( $series as $s ) : ?><li><i style="background:<?php echo esc_attr( $s[1] ); ?>"></i><?php echo esc_html( $s[0] ); ?></li><?php endforeach; ?>
	</ul>
	<div class="ad-chart" role="img" aria-label="<?php esc_attr_e( 'Stacked bars: inquiries per month for the last 12 months, split by order size', 'aria' ); ?>">
		<div class="ad-grid"><?php for ( $g = 4; $g >= 0; $g-- ) : ?><span data-v="<?php echo (int) ( $top * $g / 4 ); ?>"></span><?php endfor; ?></div>
		<div class="ad-cols">
			<?php foreach ( $d['months'] as $k => $m ) :
				$sum = array_sum( $m );
				$tip = wp_date( 'F Y', strtotime( $k . '-01' ) ) . ': ' . $sum;
				foreach ( $series as $sk => $s ) {
					if ( $m[ $sk ] ) {
						$tip .= "\n" . $s[0] . ': ' . $m[ $sk ];
					}
				}
				?>
				<div class="ad-col" tabindex="0" data-tip="<?php echo esc_attr( $tip ); ?>">
					<div class="ad-stack" style="height:<?php echo esc_attr( round( $sum * 100 / $top, 2 ) ); ?>%">
						<?php foreach ( array_reverse( $series, true ) as $sk => $s ) : if ( ! $m[ $sk ] ) { continue; } ?>
							<b style="flex:<?php echo (int) $m[ $sk ]; ?>;background:<?php echo esc_attr( $s[1] ); ?>"></b>
						<?php endforeach; ?>
					</div>
					<span class="ad-x"><?php echo esc_html( wp_date( 'M', strtotime( $k . '-01' ) ) ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="ad-tip" hidden></div>
	</div>
	<details class="ad-table"><summary><?php esc_html_e( 'Show as table', 'aria' ); ?></summary>
		<table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Month', 'aria' ); ?></th><?php foreach ( $series as $s ) : ?><th><?php echo esc_html( $s[0] ); ?></th><?php endforeach; ?><th><?php esc_html_e( 'Total', 'aria' ); ?></th></tr></thead><tbody>
		<?php foreach ( array_reverse( $d['months'], true ) as $k => $m ) : ?>
			<tr><td><?php echo esc_html( wp_date( 'M Y', strtotime( $k . '-01' ) ) ); ?></td><?php foreach ( array_keys( $series ) as $sk ) : ?><td><?php echo (int) $m[ $sk ]; ?></td><?php endforeach; ?><td><strong><?php echo (int) array_sum( $m ); ?></strong></td></tr>
		<?php endforeach; ?>
		</tbody></table>
	</details>
	<?php
}

function aria_dash_countries() {
	$d = aria_dash_data();
	$views = array(
		'dest'    => array( __( 'Destination', 'aria' ), $d['dest'], __( 'From the port or country the buyer entered (visitor location when it is empty).', 'aria' ) ),
		'visitor' => array( __( 'Visitor location', 'aria' ), $d['visitor'], __( 'Where the buyer was when sending the form (Cloudflare). Recorded for new inquiries from now on.', 'aria' ) ),
	);
	?>
	<div class="ad-switch" role="tablist">
		<?php $first = true; foreach ( $views as $k => $v ) : ?>
			<button type="button" role="tab" aria-selected="<?php echo $first ? 'true' : 'false'; ?>" data-view="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $v[0] ); ?></button>
		<?php $first = false; endforeach; ?>
	</div>
	<?php $first = true; foreach ( $views as $k => $v ) :
		$list  = $v[1];
		$total = array_sum( $list );
		$top   = array_slice( $list, 0, 10, true );
		$rest  = $total - array_sum( $top );
		if ( $rest > 0 ) {
			$top[ __( 'Other countries', 'aria' ) ] = $rest;
		}
		$max = max( 1, $top ? max( $top ) : 1 );
		?>
		<div class="ad-view" data-view="<?php echo esc_attr( $k ); ?>"<?php echo $first ? '' : ' hidden'; ?>>
			<?php if ( ! $total ) : ?>
				<p class="ad-empty"><?php esc_html_e( 'No inquiries yet.', 'aria' ); ?></p>
			<?php else : ?>
				<ul class="ad-hbars">
					<?php foreach ( $top as $name => $n ) : $muted = in_array( $name, array( __( 'Unknown', 'aria' ), __( 'Other countries', 'aria' ) ), true ); ?>
						<li title="<?php echo esc_attr( $name . ': ' . $n . ' (' . round( $n * 100 / $total ) . '%)' ); ?>">
							<span class="ad-name"><?php echo esc_html( $name ); ?></span>
							<span class="ad-bar"><b style="width:<?php echo esc_attr( max( 2, round( $n * 100 / $max ) ) ); ?>%;background:<?php echo $muted ? '#a7adb4' : '#2a78d6'; ?>"></b></span>
							<span class="ad-num"><?php echo (int) $n; ?> <small><?php echo esc_html( round( $n * 100 / $total ) . '%' ); ?></small></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<p class="ad-note"><?php echo esc_html( $v[2] ); ?></p>
		</div>
	<?php $first = false; endforeach;
}

add_action( 'admin_head-index.php', function () {
	?>
	<style>
	#aria_dash_overview .inside,#aria_dash_months .inside,#aria_dash_countries .inside{margin:0;padding:14px 16px 12px}
	.ad-tiles{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}
	.ad-tile{display:flex;flex-direction:column;padding:10px 12px;border:1px solid #e6e0d9;border-radius:10px;background:#fbf8f5;text-decoration:none;color:#1d2327}
	a.ad-tile{border-left:3px solid #b0592a}a.ad-tile:hover{background:#f7f0e9}
	.ad-tile strong{font-size:24px;line-height:1.15;font-variant-numeric:tabular-nums}
	.ad-tile span{font-size:12px;color:#50575e;line-height:1.3}
	.ad-tile em{font-style:normal;font-weight:600;margin-left:4px}.ad-tile em.up{color:#1f8a5b}.ad-tile em.down{color:#b32d2e}
	.ad-h{margin:16px 0 6px!important;font-size:13px!important;display:flex;justify-content:space-between;align-items:baseline}.ad-h small{font-weight:400;color:#646970}
	.ad-status,.ad-hbars{margin:0}
	.ad-status a,.ad-hbars li{display:grid;grid-template-columns:118px minmax(0,1fr) 54px;gap:10px;align-items:center;padding:4px 0;text-decoration:none;color:#1d2327;margin:0}
	.ad-status a:hover .ad-name{text-decoration:underline}
	.ad-name{font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
	.ad-name i{display:inline-block;width:8px;height:8px;border-radius:2px;margin-right:7px;vertical-align:1px}
	.ad-bar{height:10px;background:#f0f0f1;border-radius:0 4px 4px 0;overflow:hidden}
	.ad-bar b{display:block;height:100%;border-radius:0 4px 4px 0}
	.ad-num{text-align:right;font-variant-numeric:tabular-nums;font-weight:600;font-size:13px}.ad-num small{font-weight:400;color:#646970}
	.ad-foot{display:flex;justify-content:space-between;margin:12px 0 0!important;padding-top:10px;border-top:1px solid #f0f0f1;font-size:12px;color:#646970}
	.ad-legend{display:flex;flex-wrap:wrap;gap:4px 14px;margin:0 0 10px;font-size:12px;color:#3c434a}.ad-legend li{margin:0;display:flex;align-items:center;gap:6px}.ad-legend i{width:10px;height:10px;border-radius:2px}
	.ad-chart{position:relative;height:200px;margin:0 0 4px 26px}
	.ad-grid{position:absolute;inset:0 0 20px;display:flex;flex-direction:column;justify-content:space-between}
	.ad-grid span{border-top:1px solid #f0f0f1;position:relative;height:0}
	.ad-grid span::before{content:attr(data-v);position:absolute;left:-26px;top:-7px;width:20px;text-align:right;font-size:11px;color:#8c8f94;font-variant-numeric:tabular-nums}
	.ad-cols{position:absolute;inset:0;display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:6px}
	.ad-col{position:relative;display:flex;flex-direction:column;justify-content:flex-end;padding-bottom:20px;outline:none;cursor:default}
	.ad-col:hover::before,.ad-col:focus::before{content:"";position:absolute;inset:0 -3px 20px;background:rgba(0,0,0,.035);border-radius:4px}
	.ad-stack{display:flex;flex-direction:column;gap:2px;min-height:0;position:relative}
	.ad-stack b{display:block;min-height:3px}.ad-stack b:first-child{border-radius:4px 4px 0 0}
	.ad-x{position:absolute;bottom:0;left:0;right:0;text-align:center;font-size:11px;color:#646970}
	.ad-tip{position:absolute;z-index:5;pointer-events:none;background:#1d2327;color:#fff;font-size:12px;line-height:1.5;padding:7px 10px;border-radius:6px;white-space:pre;box-shadow:0 6px 18px rgba(0,0,0,.2)}
	.ad-table{margin-top:10px;font-size:12px}.ad-table summary{cursor:pointer;color:#2271b1}.ad-table table{margin-top:8px}.ad-table td,.ad-table th{padding:5px 8px;font-size:12px;font-variant-numeric:tabular-nums}
	.ad-switch{display:inline-flex;background:#f0f0f1;border-radius:8px;padding:3px;margin-bottom:12px}
	.ad-switch button{border:0;background:none;padding:5px 12px;border-radius:6px;font-size:12px;cursor:pointer;color:#3c434a}
	.ad-switch button[aria-selected=true]{background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.1);color:#1d2327;font-weight:600}
	.ad-hbars li{grid-template-columns:120px minmax(0,1fr) 70px}
	.ad-note,.ad-empty{font-size:12px;color:#646970;margin:10px 0 0!important}
	@media (max-width:600px){.ad-tiles{grid-template-columns:repeat(2,minmax(0,1fr))}}
	</style>
	<?php
} );

add_action( 'admin_footer-index.php', function () {
	?>
	<script>
	(function () {
		document.querySelectorAll('.ad-chart').forEach(function (ch) {
			var tip = ch.querySelector('.ad-tip');
			var show = function (col) {
				tip.textContent = col.dataset.tip; tip.hidden = false;
				var cr = ch.getBoundingClientRect(), r = col.getBoundingClientRect();
				var x = r.left - cr.left + r.width / 2 - tip.offsetWidth / 2;
				tip.style.left = Math.max(-20, Math.min(cr.width - tip.offsetWidth, x)) + 'px';
				tip.style.top = Math.max(-10, (col.querySelector('.ad-stack').getBoundingClientRect().top - cr.top) - tip.offsetHeight - 8) + 'px';
			};
			ch.querySelectorAll('.ad-col').forEach(function (col) {
				col.addEventListener('mouseenter', function () { show(col); });
				col.addEventListener('focus', function () { show(col); });
				col.addEventListener('mouseleave', function () { tip.hidden = true; });
				col.addEventListener('blur', function () { tip.hidden = true; });
			});
		});
		document.querySelectorAll('.ad-switch').forEach(function (sw) {
			var box = sw.parentNode;
			sw.querySelectorAll('button').forEach(function (b) {
				b.addEventListener('click', function () {
					sw.querySelectorAll('button').forEach(function (x) { x.setAttribute('aria-selected', x === b ? 'true' : 'false'); });
					box.querySelectorAll('.ad-view').forEach(function (v) { v.hidden = v.dataset.view !== b.dataset.view; });
				});
			});
		});
	})();
	</script>
	<?php
} );
