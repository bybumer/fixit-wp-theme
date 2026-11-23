<?php
/**
 * Fixit Remote: id.fixit.az mərkəzindən sessiya statistikası (ictimai proqram).
 *
 * Admin → Fixit Remote → Sessiyalar: hazırda onlayn cihazlar və gedən sessiyalar (30
 * saniyədən bir özü yenilənir), son günlərin sessiya, müddət, aktiv cihaz, yeni
 * quraşdırma sayları.
 *
 * Mərkəz yalnız saylar və müddətlər verir: ID-nin ortası gizlidir, kompüterin adı,
 * operator, IP gəlmir. Açar (FIXITREMOTE_STATS_TOKEN) mərkəzin update.env faylında,
 * saytda isə şifrələnmiş saxlanılır və səhifədə göstərilmir.
 *
 * Lokal sınaq: wp-config.php və ya mu-plugin-də
 * define( 'FIXIT_REMOTE_STATS_SERVER', 'http://host.docker.internal:5080' ); (https tələbi
 * yalnız admin paneldə yazılan ünvana aiddir).
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Şifrələmənin nonce uzunluğu (sodium secretbox). */
define( 'FIXIT_RS_NONCE', 24 );

/* ============================================================
   1. Ayarlar: ünvan və açar
   ============================================================ */

/**
 * Mərkəzin ünvanı (sonunda / olmadan).
 */
function fixit_rs_server() {
	if ( defined( 'FIXIT_REMOTE_STATS_SERVER' ) && FIXIT_REMOTE_STATS_SERVER ) {
		return untrailingslashit( (string) FIXIT_REMOTE_STATS_SERVER );
	}
	$opt = get_option( 'fixit_rs_settings', array() );
	$url = is_array( $opt ) && ! empty( $opt['url'] ) ? (string) $opt['url'] : 'https://id.fixit.az';
	return untrailingslashit( $url );
}

/**
 * Açarın şifrələnməsi üçün açar: wp-config.php-dəki duzlardan.
 */
function fixit_rs_key() {
	return hash( 'sha256', wp_salt( 'auth' ) . '|fixit-remote-stats', true );
}

/**
 * Açarı şifrələnmiş saxlayır (boş — silir).
 *
 * @param string $plain Açar.
 */
function fixit_rs_store_token( $plain ) {
	if ( '' === $plain ) {
		delete_option( 'fixit_rs_token' );
		return;
	}
	$nonce = random_bytes( FIXIT_RS_NONCE );
	$box   = sodium_crypto_secretbox( $plain, $nonce, fixit_rs_key() );
	update_option( 'fixit_rs_token', base64_encode( $nonce . $box ), false ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- şifrələnmiş açarın saxlanma forması
}

/**
 * Açar; yoxdursa və ya açılmırsa boş.
 */
function fixit_rs_token() {
	$raw = base64_decode( (string) get_option( 'fixit_rs_token', '' ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- şifrələnmiş açarın saxlanma forması
	if ( ! is_string( $raw ) || strlen( $raw ) <= FIXIT_RS_NONCE ) {
		return '';
	}
	$plain = sodium_crypto_secretbox_open( substr( $raw, FIXIT_RS_NONCE ), substr( $raw, 0, FIXIT_RS_NONCE ), fixit_rs_key() );
	return is_string( $plain ) ? $plain : '';
}

/**
 * Ayarları saxlayır (admin-post).
 */
function fixit_rs_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'İcazə yoxdur.', 'fixit' ) );
	}
	check_admin_referer( 'fixit_rs_save' );

	$url = isset( $_POST['fixit_rs_url'] ) ? esc_url_raw( trim( (string) wp_unslash( $_POST['fixit_rs_url'] ) ) ) : '';
	if ( '' === $url || 0 !== strpos( $url, 'https://' ) ) {
		$url = 'https://id.fixit.az';
	}
	update_option( 'fixit_rs_settings', array( 'url' => untrailingslashit( $url ) ), false );

	$token = isset( $_POST['fixit_rs_token'] ) ? trim( (string) wp_unslash( $_POST['fixit_rs_token'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- açar olduğu kimi şifrələnir
	if ( ! empty( $_POST['fixit_rs_forget'] ) ) {
		fixit_rs_store_token( '' );
	} elseif ( '' !== $token ) {
		fixit_rs_store_token( preg_replace( '/\s+/', '', $token ) );
	}

	fixit_rs_flush();
	wp_safe_redirect( admin_url( 'admin.php?page=fixit-remote-sessions&saved=1' ) );
	exit;
}
add_action( 'admin_post_fixit_rs_save', 'fixit_rs_save' );

/* ============================================================
   2. Mərkəzdən oxumaq
   ============================================================ */

/**
 * Keşi silir (ayar dəyişəndə).
 */
function fixit_rs_flush() {
	foreach ( array( 7, 30, 90 ) as $days ) {
		delete_transient( 'fixit_rs_' . $days );
	}
}

/**
 * Mərkəzin hesabatı (30 saniyə keşlənir; xəta 60 saniyə).
 *
 * @param int  $days  Neçə gün (7, 30, 90).
 * @param bool $force Keşə baxmadan.
 * @return array { ok: bool, data?: array, error?: string, at: int }
 */
function fixit_rs_fetch( $days = 30, $force = false ) {
	$days = in_array( (int) $days, array( 7, 30, 90 ), true ) ? (int) $days : 30;
	$key  = 'fixit_rs_' . $days;

	if ( ! $force ) {
		$cached = get_transient( $key );
		if ( is_array( $cached ) ) {
			return $cached;
		}
	}

	$token = fixit_rs_token();
	if ( '' === $token ) {
		return array(
			'ok'    => false,
			'error' => __( 'Mərkəzin statistika açarı yazılmayıb. Aşağıdakı «Bağlantı» bölməsində yazın.', 'fixit' ),
			'setup' => true,
			'at'    => time(),
		);
	}

	$response = wp_remote_get(
		fixit_rs_server() . '/api/stats?days=' . $days,
		array(
			'timeout'     => 8,
			'redirection' => 0,
			'headers'     => array(
				'Authorization' => 'Bearer ' . $token,
				'Accept'        => 'application/json',
			),
		)
	);

	$result = array( 'at' => time() );

	if ( is_wp_error( $response ) ) {
		$result['ok'] = false;
		/* translators: %s: xəta mətni */
		$result['error'] = sprintf( __( 'Mərkəzə qoşulmaq alınmadı: %s', 'fixit' ), $response->get_error_message() );
	} else {
		$code = (int) wp_remote_retrieve_response_code( $response );
		$data = json_decode( (string) wp_remote_retrieve_body( $response ), true );

		if ( 200 === $code && is_array( $data ) && isset( $data['now'], $data['days'] ) ) {
			$result['ok']   = true;
			$result['data'] = $data;

			// FIXIT icmalı xarici sorğu etmir: son vəziyyəti buradan göstərir.
			$today = isset( $data['days'][0]['public'] ) ? $data['days'][0]['public'] : array();
			update_option(
				'fixit_rs_last',
				array(
					'at'       => time(),
					'online'   => (int) $data['now']['onlinePublic'],
					'sessions' => (int) $data['now']['sessionsPublic'],
					'today'    => isset( $today['sessions'] ) ? (int) $today['sessions'] : 0,
					'active'   => isset( $today['activeDevices'] ) ? (int) $today['activeDevices'] : 0,
				),
				false
			);
		} else {
			$result['ok'] = false;
			switch ( $code ) {
				case 401:
					$result['error'] = __( 'Açar yanlışdır: saytdakı açar mərkəzdəki FIXITREMOTE_STATS_TOKEN ilə eyni deyil.', 'fixit' );
					$result['setup'] = true;
					break;
				case 503:
					$result['error'] = __( 'Mərkəzdə statistika açarı qurulmayıb (FIXITREMOTE_STATS_TOKEN).', 'fixit' );
					break;
				case 404:
					$result['error'] = __( 'Mərkəz bu statistikanı hələ vermir: id.fixit.az 1.17.4 və ya daha yeni versiyaya yenilənməlidir.', 'fixit' );
					break;
				case 429:
					$result['error'] = __( 'Çox sorğu. Bir dəqiqədən sonra yenidən baxın.', 'fixit' );
					break;
				default:
					/* translators: %d: HTTP kodu */
					$result['error'] = sprintf( __( 'Mərkəz gözlənilməz cavab verdi (HTTP %d).', 'fixit' ), $code );
			}
		}
	}

	set_transient( $key, $result, $result['ok'] ? 30 : 60 );
	return $result;
}

/**
 * REST: səhifə «indi» bölməsini 30 saniyədən bir buradan yeniləyir.
 */
function fixit_rs_rest() {
	register_rest_route(
		'fixit/v1',
		'/remote-sessions',
		array(
			'methods'             => 'GET',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
			'callback'            => function ( WP_REST_Request $request ) {
				$days = (int) $request->get_param( 'days' );
				return rest_ensure_response( fixit_rs_fetch( $days ? $days : 30 ) );
			},
		)
	);
}
add_action( 'rest_api_init', 'fixit_rs_rest' );

/* ============================================================
   3. Köməkçilər
   ============================================================ */

/**
 * Müddət: «2 saat 5 dəq», «4 dəq», «35 san».
 *
 * @param int $seconds Saniyə.
 */
function fixit_rs_duration( $seconds ) {
	$seconds = max( 0, (int) $seconds );
	if ( $seconds < 60 ) {
		/* translators: %d: saniyə */
		return sprintf( __( '%d san', 'fixit' ), $seconds );
	}
	$minutes = (int) floor( $seconds / 60 );
	return fixit_rs_minutes( $minutes );
}

/**
 * Dəqiqələr: «2 saat 5 dəq», «45 dəq».
 *
 * @param int $minutes Dəqiqə.
 */
function fixit_rs_minutes( $minutes ) {
	$minutes = max( 0, (int) $minutes );
	if ( $minutes < 60 ) {
		/* translators: %d: dəqiqə */
		return sprintf( __( '%d dəq', 'fixit' ), $minutes );
	}
	/* translators: 1: saat, 2: dəqiqə */
	return sprintf( __( '%1$d saat %2$d dəq', 'fixit' ), (int) floor( $minutes / 60 ), $minutes % 60 );
}

/**
 * Gün etiketi: 23.11 (B.e.) — qrafik və cədvəl üçün.
 *
 * @param string $day Y-m-d.
 */
function fixit_rs_day_label( $day ) {
	$ts = strtotime( $day . ' 12:00:00' );
	return $ts ? wp_date( 'd.m.Y', $ts ) : $day;
}

/* ============================================================
   4. Menyu və səhifə
   ============================================================ */

function fixit_rs_menu() {
	add_submenu_page( 'fixit-remote', __( 'Fixit Remote sessiyaları', 'fixit' ), __( 'Sessiyalar', 'fixit' ), 'manage_options', 'fixit-remote-sessions', 'fixit_rs_page' );
}
add_action( 'admin_menu', 'fixit_rs_menu', 12 );

/**
 * Admin → Fixit Remote → Sessiyalar.
 */
function fixit_rs_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- yalnız oxuma parametrləri
	$days  = isset( $_GET['days'] ) ? absint( $_GET['days'] ) : 30;
	$days  = in_array( $days, array( 7, 30, 90 ), true ) ? $days : 30;
	$force = isset( $_GET['refresh'], $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'fixit_rs_refresh' );
	$saved = ! empty( $_GET['saved'] );
	// phpcs:enable

	$res     = fixit_rs_fetch( $days, $force || $saved );
	$data    = ! empty( $res['ok'] ) ? $res['data'] : null;
	$public  = $data ? fixit_rs_public_sessions( $data['now']['sessions'] ) : array();
	$base    = admin_url( 'admin.php?page=fixit-remote-sessions' );
	$refresh = wp_nonce_url( add_query_arg( array( 'days' => $days, 'refresh' => 1 ), $base ), 'fixit_rs_refresh' );
	$opt     = get_option( 'fixit_rs_settings', array() );
	$url     = is_array( $opt ) && ! empty( $opt['url'] ) ? $opt['url'] : 'https://id.fixit.az';
	$has     = '' !== (string) get_option( 'fixit_rs_token', '' );
	?>
	<style>
		.frs-wrap{max-width:1240px}
		.frs-top{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin:14px 0 4px}
		.frs-top .spacer{flex:1}
		.frs-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin:18px 0}
		.frs-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px 18px}
		.frs-card b{display:block;font-size:28px;line-height:1.1;margin-bottom:4px;font-variant-numeric:tabular-nums}
		.frs-card span{color:#646970;font-size:13px}
		.frs-card small{display:block;color:#8c8f94;margin-top:4px;font-size:12px}
		.frs-card--live{border-color:#00a32a;box-shadow:inset 3px 0 0 #00a32a}
		.frs-box{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px 18px;margin-bottom:16px}
		.frs-box h2{margin:0 0 12px;font-size:14px;display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:baseline}
		.frs-box h2 small{font-weight:400;color:#646970}
		.frs-table{width:100%;border-collapse:collapse}
		.frs-table th{text-align:left;color:#646970;font-weight:400;font-size:12px;padding:0 8px 6px 0;white-space:nowrap}
		.frs-table td{padding:7px 8px 7px 0;border-bottom:1px solid #f0f0f1;vertical-align:middle}
		.frs-table tr:last-child td{border-bottom:0}
		.frs-table .num{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
		.frs-table th.num{text-align:right}
		.frs-table .muted{color:#a7aaad}
		.frs-table tr.is-today td{font-weight:600}
		.frs-id{font-family:ui-monospace,Consolas,monospace;letter-spacing:.04em}
		.frs-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:#00a32a;margin-right:6px;animation:frs-pulse 2s infinite}
		@keyframes frs-pulse{50%{opacity:.35}}
		.frs-chart{width:100%;height:150px;display:block}
		.frs-chart rect{fill:#2271b1}
		.frs-chart g:hover rect{fill:#135e96}
		.frs-axis{display:flex;justify-content:space-between;color:#8c8f94;font-size:12px;margin-top:4px}
		.frs-note{color:#646970;font-size:12px;margin:10px 0 0}
		.frs-empty{color:#646970;margin:0}
		.frs-scroll{overflow-x:auto}
		.frs-setup input[type=text],.frs-setup input[type=password]{width:100%;max-width:520px}
		@media (prefers-reduced-motion:reduce){.frs-dot{animation:none}}
	</style>

	<div class="wrap frs-wrap">
		<h1><?php esc_html_e( 'Fixit Remote sessiyaları', 'fixit' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Pulsuz (ictimai) Fixit Remote proqramı ilə id.fixit.az üzərindən edilən qoşulmalar. Şirkət serverlərinin öz daxili sessiyaları burada görünmür.', 'fixit' ); ?></p>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Bağlantı ayarları saxlanıldı.', 'fixit' ); ?></p></div>
		<?php endif; ?>

		<div class="frs-top">
			<?php foreach ( array( 7, 30, 90 ) as $d ) : ?>
				<a class="button<?php echo $d === $days ? ' button-primary' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'days', $d, $base ) ); ?>">
					<?php
					/* translators: %d: gün sayı */
					echo esc_html( sprintf( __( '%d gün', 'fixit' ), $d ) );
					?>
				</a>
			<?php endforeach; ?>
			<span class="spacer"></span>
			<span class="description" id="frs-updated">
				<?php
				/* translators: %s: vaxt */
				echo esc_html( sprintf( __( 'Yeniləndi: %s', 'fixit' ), wp_date( 'H:i:s', (int) $res['at'], new DateTimeZone( 'Asia/Baku' ) ) ) );
				?>
			</span>
			<a class="button" href="<?php echo esc_url( $refresh ); ?>"><?php esc_html_e( 'Yenilə', 'fixit' ); ?></a>
		</div>

		<?php if ( ! $data ) : ?>
			<div class="notice notice-<?php echo empty( $res['setup'] ) ? 'error' : 'warning'; ?> inline"><p><?php echo esc_html( $res['error'] ); ?></p></div>
		<?php else : ?>
			<?php
			$now      = $data['now'];
			$day_rows = $data['days'];
			$today    = $day_rows[0];
			$pub      = $today['public'];
			$sum      = array( 'sessions' => 0, 'minutes' => 0, 'newDevices' => 0, 'tickets' => 0 );
			foreach ( $day_rows as $row ) {
				foreach ( $sum as $k => $v ) {
					$sum[ $k ] += (int) $row['public'][ $k ];
				}
			}
			?>
			<div class="frs-cards" id="frs-cards">
				<div class="frs-card frs-card--live">
					<b data-k="onlinePublic"><?php echo esc_html( number_format_i18n( (int) $now['onlinePublic'] ) ); ?></b>
					<span><?php esc_html_e( 'Hazırda onlayn cihaz', 'fixit' ); ?></span>
				</div>
				<div class="frs-card frs-card--live">
					<b data-k="sessionsPublic"><?php echo esc_html( number_format_i18n( (int) $now['sessionsPublic'] ) ); ?></b>
					<span><?php esc_html_e( 'Hazırda aktiv sessiya', 'fixit' ); ?></span>
				</div>
				<div class="frs-card">
					<b data-k="todaySessions"><?php echo esc_html( number_format_i18n( (int) $pub['sessions'] ) ); ?></b>
					<span><?php esc_html_e( 'Bu gün sessiya', 'fixit' ); ?></span>
					<small data-k="todayMinutes"><?php echo esc_html( fixit_rs_minutes( (int) $pub['minutes'] ) ); ?></small>
				</div>
				<div class="frs-card">
					<b data-k="todayActive"><?php echo esc_html( number_format_i18n( (int) $pub['activeDevices'] ) ); ?></b>
					<span><?php esc_html_e( 'Bu gün aktiv cihaz', 'fixit' ); ?></span>
					<small>
						<?php
						/* translators: %s: say */
						echo esc_html( sprintf( __( 'pik eyni anda: %s', 'fixit' ), number_format_i18n( (int) $today['peakOnline'] ) ) );
						?>
					</small>
				</div>
				<div class="frs-card">
					<b><?php echo esc_html( number_format_i18n( (int) $pub['newDevices'] ) ); ?></b>
					<span><?php esc_html_e( 'Bu gün yeni quraşdırma', 'fixit' ); ?></span>
					<small>
						<?php
						/* translators: %s: say */
						echo esc_html( sprintf( __( 'cəmi cihaz: %s', 'fixit' ), number_format_i18n( (int) $data['devices']['public'] ) ) );
						?>
					</small>
				</div>
			</div>

			<div class="frs-box">
				<h2>
					<span><span class="frs-dot" aria-hidden="true"></span><?php esc_html_e( 'Hazırda gedən sessiyalar', 'fixit' ); ?></span>
					<small><?php esc_html_e( '30 saniyədən bir özü yenilənir', 'fixit' ); ?></small>
				</h2>
				<div id="frs-live"><?php fixit_rs_live_table( $public ); ?></div>
				<p class="frs-note" id="frs-company">
					<?php
					/* translators: 1: onlayn şirkət agenti, 2: şirkət sessiyası */
					echo esc_html( sprintf( __( 'Müqayisə üçün, şirkət agentləri (mərkəz üzərindən): %1$s onlayn, %2$s sessiya.', 'fixit' ), number_format_i18n( (int) $now['onlineCompany'] ), number_format_i18n( (int) $now['sessionsCompany'] ) ) );
					?>
				</p>
			</div>

			<?php fixit_rs_chart( $day_rows, $days, $sum ); ?>

			<div class="frs-box">
				<h2><?php esc_html_e( 'Günlər üzrə', 'fixit' ); ?><small><?php esc_html_e( 'Bakı vaxtı ilə', 'fixit' ); ?></small></h2>
				<div class="frs-scroll">
					<table class="frs-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Gün', 'fixit' ); ?></th>
								<th class="num"><?php esc_html_e( 'Sessiya', 'fixit' ); ?></th>
								<th class="num"><?php esc_html_e( 'Müddət', 'fixit' ); ?></th>
								<th class="num"><?php esc_html_e( 'Aktiv cihaz', 'fixit' ); ?></th>
								<th class="num"><?php esc_html_e( 'İdarə olunan cihaz', 'fixit' ); ?></th>
								<th class="num"><?php esc_html_e( 'Pik onlayn', 'fixit' ); ?></th>
								<th class="num"><?php esc_html_e( 'Pik sessiya', 'fixit' ); ?></th>
								<th class="num"><?php esc_html_e( 'Yeni quraşdırma', 'fixit' ); ?></th>
								<th class="num"><?php esc_html_e( 'Qoşulma icazəsi', 'fixit' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$since = isset( $data['statsSince'] ) ? (string) $data['statsSince'] : '';
							foreach ( $day_rows as $i => $row ) :
								$p       = $row['public'];
								$tracked = '' !== $since && $row['day'] >= $since;
								$dash    = '<span class="muted">—</span>';
								?>
								<tr<?php echo 0 === $i ? ' class="is-today"' : ''; ?>>
									<td><?php echo esc_html( fixit_rs_day_label( $row['day'] ) ); ?></td>
									<td class="num"><?php echo $tracked ? esc_html( number_format_i18n( (int) $p['sessions'] ) ) : $dash; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $dash sabit HTML-dir ?></td>
									<td class="num"><?php echo $tracked ? esc_html( fixit_rs_minutes( (int) $p['minutes'] ) ) : $dash; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
									<td class="num"><?php echo $tracked ? esc_html( number_format_i18n( (int) $p['activeDevices'] ) ) : $dash; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
									<td class="num"><?php echo $tracked ? esc_html( number_format_i18n( (int) $p['sessionDevices'] ) ) : $dash; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
									<td class="num"><?php echo $tracked ? esc_html( number_format_i18n( (int) $row['peakOnline'] ) ) : $dash; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
									<td class="num"><?php echo $tracked ? esc_html( number_format_i18n( (int) $row['peakSessions'] ) ) : $dash; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
									<td class="num"><?php echo esc_html( number_format_i18n( (int) $p['newDevices'] ) ); ?></td>
									<td class="num"><?php echo esc_html( number_format_i18n( (int) $p['tickets'] ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<p class="frs-note">
					<?php esc_html_e( 'Sessiya: qarşı tərəf qəbul etdikdən sonra açılmış qoşulma (rədd olunan və cavabsız qalan sayılmır). Aktiv cihaz: gün ərzində proqramı açıq olan kompüterlər. Qoşulma icazəsi: ID və parol (və ya istək) qəbul olunub, sessiya açılmaya da bilər.', 'fixit' ); ?>
					<?php if ( $since ) : ?>
						<?php
						/* translators: %s: tarix */
						echo esc_html( sprintf( __( 'Sessiya, müddət, aktiv cihaz və pik %s tarixindən yığılır; yeni quraşdırma və qoşulma icazəsi daha əvvəldən var.', 'fixit' ), fixit_rs_day_label( $since ) ) );
						?>
					<?php endif; ?>
				</p>
			</div>
		<?php endif; ?>

		<details class="frs-box frs-setup"<?php echo ( ! $data && ! empty( $res['setup'] ) ) || ! $has ? ' open' : ''; ?>>
			<summary><strong><?php esc_html_e( 'Bağlantı', 'fixit' ); ?></strong> · <?php echo esc_html( $has ? __( 'açar yazılıb', 'fixit' ) : __( 'açar yazılmayıb', 'fixit' ) ); ?></summary>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:12px">
				<input type="hidden" name="action" value="fixit_rs_save">
				<?php wp_nonce_field( 'fixit_rs_save' ); ?>
				<p>
					<label for="fixit_rs_url"><strong><?php esc_html_e( 'Mərkəzin ünvanı', 'fixit' ); ?></strong></label><br>
					<input type="text" id="fixit_rs_url" name="fixit_rs_url" value="<?php echo esc_attr( $url ); ?>" class="regular-text" <?php disabled( defined( 'FIXIT_REMOTE_STATS_SERVER' ) && FIXIT_REMOTE_STATS_SERVER ); ?>>
				</p>
				<p>
					<label for="fixit_rs_token"><strong><?php esc_html_e( 'Statistika açarı', 'fixit' ); ?></strong></label><br>
					<input type="password" id="fixit_rs_token" name="fixit_rs_token" value="" autocomplete="off" placeholder="<?php echo esc_attr( $has ? __( 'Yazılıb. Dəyişmək üçün yenisini yapışdırın.', 'fixit' ) : __( 'Mərkəzdəki FIXITREMOTE_STATS_TOKEN', 'fixit' ) ); ?>">
					<br><span class="description"><?php esc_html_e( 'Serverdə /etc/fixitremote-central/update.env faylındakı FIXITREMOTE_STATS_TOKEN dəyəri. Saytda şifrələnmiş saxlanılır və bir daha göstərilmir.', 'fixit' ); ?></span>
				</p>
				<?php if ( $has ) : ?>
					<p><label><input type="checkbox" name="fixit_rs_forget" value="1"> <?php esc_html_e( 'Açarı sil', 'fixit' ); ?></label></p>
				<?php endif; ?>
				<?php submit_button( __( 'Saxla', 'fixit' ), 'secondary', 'submit', false ); ?>
			</form>
		</details>
	</div>

	<?php if ( $data ) : ?>
		<script>
		(function () {
			var cfg = <?php echo wp_json_encode( fixit_rs_js_config( $days ) ); ?>;
			var live = document.getElementById('frs-live');
			var started = Date.now();
			var base = <?php echo wp_json_encode( array_map( 'intval', wp_list_pluck( $public, 'seconds' ) ) ); ?>;

			function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
			function dur(sec) {
				sec = Math.max(0, Math.floor(sec));
				if (sec < 60) return cfg.s.sec.replace('%d', sec);
				var m = Math.floor(sec / 60);
				return m < 60 ? cfg.s.min.replace('%d', m) : cfg.s.hour.replace('%1$d', Math.floor(m / 60)).replace('%2$d', m % 60);
			}
			function time(iso) {
				var d = new Date(iso);
				return isNaN(d) ? '' : d.toLocaleTimeString(cfg.locale, { hour: '2-digit', minute: '2-digit', hourCycle: 'h23', timeZone: 'Asia/Baku' });
			}
			function num(n) { return Number(n || 0).toLocaleString(cfg.locale); }
			function set(k, v) { var el = document.querySelector('#frs-cards [data-k="' + k + '"]'); if (el) el.textContent = v; }

			function table(list) {
				if (!list.length) return '<p class="frs-empty">' + esc(cfg.s.none) + '</p>';
				var h = '<div class="frs-scroll"><table class="frs-table"><thead><tr><th>ID</th><th>' + esc(cfg.s.since) + '</th><th class="num">' + esc(cfg.s.length) + '</th><th class="num">' + esc(cfg.s.viewers) + '</th><th>' + esc(cfg.s.mode) + '</th></tr></thead><tbody>';
				list.forEach(function (x, i) {
					h += '<tr><td class="frs-id">' + esc(x.id) + '</td><td>' + esc(time(x.since)) + '</td><td class="num" data-i="' + i + '">' + esc(dur(x.seconds)) + '</td><td class="num">' + esc(num(x.viewers)) + '</td><td>' + esc(x.viewOnly ? cfg.s.view : cfg.s.control) + '</td></tr>';
				});
				return h + '</tbody></table></div>';
			}

			// Müddətlər hər saniyə artır (səhifə yenilənmədən).
			setInterval(function () {
				var passed = (Date.now() - started) / 1000;
				live.querySelectorAll('td[data-i]').forEach(function (td) {
					var i = +td.getAttribute('data-i');
					if (base[i] !== undefined) td.textContent = dur(base[i] + passed);
				});
			}, 1000);

			function refresh() {
				if (document.hidden) return;
				fetch(cfg.rest, { headers: { 'X-WP-Nonce': cfg.nonce }, credentials: 'same-origin' })
					.then(function (r) { return r.json(); })
					.then(function (res) {
						if (!res || !res.ok) return;
						var now = res.data.now, today = res.data.days[0];
						set('onlinePublic', num(now.onlinePublic));
						set('sessionsPublic', num(now.sessionsPublic));
						set('todaySessions', num(today.public.sessions));
						set('todayActive', num(today.public.activeDevices));
						set('todayMinutes', dur(today.public.minutes * 60));
						var pub = now.sessions.filter(function (x) { return x.edition !== 'company'; });
						live.innerHTML = table(pub);
						base = pub.map(function (x) { return x.seconds; });
						started = Date.now();
						var c = document.getElementById('frs-company');
						if (c) c.textContent = cfg.s.company.replace('%1$s', num(now.onlineCompany)).replace('%2$s', num(now.sessionsCompany));
						var u = document.getElementById('frs-updated');
						if (u) u.textContent = cfg.s.updated.replace('%s', new Date().toLocaleTimeString(cfg.locale, { hourCycle: 'h23', timeZone: 'Asia/Baku' }));
					})
					.catch(function () {});
			}
			setInterval(refresh, 30000);
		})();
		</script>
	<?php endif; ?>
	<?php
}

/**
 * Brauzerin yeniləməsi üçün ayarlar və mətnlər.
 *
 * @param int $days Dövr.
 */
function fixit_rs_js_config( $days ) {
	return array(
		'rest'   => rest_url( 'fixit/v1/remote-sessions?days=' . (int) $days ),
		'nonce'  => wp_create_nonce( 'wp_rest' ),
		'locale' => str_replace( '_', '-', determine_locale() ),
		's'      => array(
			/* translators: %d: saniyə */
			'sec'     => __( '%d san', 'fixit' ),
			/* translators: %d: dəqiqə */
			'min'     => __( '%d dəq', 'fixit' ),
			/* translators: 1: saat, 2: dəqiqə */
			'hour'    => __( '%1$d saat %2$d dəq', 'fixit' ),
			'none'    => __( 'Hazırda aktiv sessiya yoxdur.', 'fixit' ),
			'since'   => __( 'Başlayıb', 'fixit' ),
			'length'  => __( 'Müddət', 'fixit' ),
			'viewers' => __( 'Qoşulan', 'fixit' ),
			'mode'    => __( 'Rejim', 'fixit' ),
			'view'    => __( 'Yalnız baxış', 'fixit' ),
			'control' => __( 'İdarə', 'fixit' ),
			/* translators: 1: onlayn şirkət agenti, 2: şirkət sessiyası */
			'company' => __( 'Müqayisə üçün, şirkət agentləri (mərkəz üzərindən): %1$s onlayn, %2$s sessiya.', 'fixit' ),
			/* translators: %s: vaxt */
			'updated' => __( 'Yeniləndi: %s', 'fixit' ),
		),
	);
}

/**
 * Yalnız ictimai proqramın sessiyaları (şirkət agentləri ayrıca sayılır).
 *
 * @param array $sessions Mərkəzin siyahısı.
 */
function fixit_rs_public_sessions( $sessions ) {
	return array_values(
		array_filter(
			(array) $sessions,
			function ( $s ) {
				return isset( $s['edition'] ) && 'company' !== $s['edition'];
			}
		)
	);
}

/**
 * Hazırda gedən sessiyalar (ilk göstəriş; sonra brauzer yeniləyir).
 *
 * @param array $sessions Mərkəzin siyahısı.
 */
function fixit_rs_live_table( $sessions ) {
	if ( empty( $sessions ) ) {
		echo '<p class="frs-empty">' . esc_html__( 'Hazırda aktiv sessiya yoxdur.', 'fixit' ) . '</p>';
		return;
	}
	?>
	<div class="frs-scroll">
		<table class="frs-table">
			<thead>
				<tr>
					<th>ID</th>
					<th><?php esc_html_e( 'Başlayıb', 'fixit' ); ?></th>
					<th class="num"><?php esc_html_e( 'Müddət', 'fixit' ); ?></th>
					<th class="num"><?php esc_html_e( 'Qoşulan', 'fixit' ); ?></th>
					<th><?php esc_html_e( 'Rejim', 'fixit' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $sessions as $i => $s ) : ?>
					<?php $since = strtotime( (string) $s['since'] ); ?>
					<tr>
						<td class="frs-id"><?php echo esc_html( $s['id'] ); ?></td>
						<td><?php echo esc_html( $since ? wp_date( 'H:i', $since, new DateTimeZone( 'Asia/Baku' ) ) : '' ); ?></td>
						<td class="num" data-i="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( fixit_rs_duration( (int) $s['seconds'] ) ); ?></td>
						<td class="num"><?php echo esc_html( number_format_i18n( (int) $s['viewers'] ) ); ?></td>
						<td><?php echo esc_html( ! empty( $s['viewOnly'] ) ? __( 'Yalnız baxış', 'fixit' ) : __( 'İdarə', 'fixit' ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Günlər üzrə sessiya qrafiki (soldan sağa: köhnədən bu günə).
 *
 * @param array $rows Günlər (bu gün birinci).
 * @param int   $days Dövr.
 * @param array $sum  Dövrün cəmi.
 */
function fixit_rs_chart( $rows, $days, $sum ) {
	$rows = array_reverse( $rows );
	$vals = array_map(
		function ( $r ) {
			return (int) $r['public']['sessions'];
		},
		$rows
	);
	$max  = max( 1, $vals ? max( $vals ) : 1 );
	$w    = 10;
	$h    = 120;
	$svg  = '';

	foreach ( $rows as $i => $r ) {
		$count = (int) $r['public']['sessions'];
		$bh    = $count ? max( 2, round( $h * $count / $max, 1 ) ) : 0;
		$svg  .= sprintf(
			'<g><title>%1$s</title><rect x="%2$s" y="%3$s" width="%4$s" height="%5$s"/></g>',
			esc_html( fixit_rs_day_label( $r['day'] ) . ': ' . number_format_i18n( $count ) . ' · ' . fixit_rs_minutes( (int) $r['public']['minutes'] ) ),
			esc_attr( $i * $w + 1 ),
			esc_attr( $h - $bh ),
			esc_attr( $w - 2 ),
			esc_attr( $bh )
		);
	}
	?>
	<div class="frs-box">
		<h2>
			<?php esc_html_e( 'Sessiyalar, günlər üzrə', 'fixit' ); ?>
			<small>
				<?php
				/* translators: 1: gün sayı, 2: sessiya sayı, 3: müddət, 4: yeni quraşdırma */
				echo esc_html( sprintf( __( 'Son %1$d gün: %2$s sessiya, %3$s · %4$s yeni quraşdırma', 'fixit' ), (int) $days, number_format_i18n( (int) $sum['sessions'] ), fixit_rs_minutes( (int) $sum['minutes'] ), number_format_i18n( (int) $sum['newDevices'] ) ) );
				?>
			</small>
		</h2>
		<svg class="frs-chart" viewBox="0 0 <?php echo esc_attr( max( 1, count( $rows ) ) * $w ); ?> <?php echo esc_attr( $h ); ?>" preserveAspectRatio="none" role="img" aria-label="<?php esc_attr_e( 'Günlük sessiya qrafiki', 'fixit' ); ?>">
			<?php echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- yuxarıda hər dəyər escape olunub ?>
		</svg>
		<div class="frs-axis">
			<span><?php echo esc_html( $rows ? fixit_rs_day_label( $rows[0]['day'] ) : '' ); ?></span>
			<span><?php echo esc_html( $rows ? fixit_rs_day_label( $rows[ count( $rows ) - 1 ]['day'] ) : '' ); ?></span>
		</div>
	</div>
	<?php
}
