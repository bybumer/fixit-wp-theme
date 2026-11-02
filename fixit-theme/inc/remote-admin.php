<?php
/**
 * Admin panel: Fixit Remote.
 *
 * - Ən son versiya və onun faylları (yükləmə düymələri ilə)
 * - Saytdan yükləmələr: bu gün, 7 gün, 30 gün, bütün dövr; günlər və mənbələr üzrə
 * - GitHub sayğacı: saytdan kənar yükləmələr və avtomatik yenilənmələr də daxil
 * - Başlanğıc səhifəsində qısa vidcet
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   1. GitHub sayğacı (bütün buraxılışlar)
   ============================================================ */

/**
 * Fayl növləri: ad nümunəsi və admin paneldəki adı.
 */
function fixit_remote_asset_kinds() {
	return array(
		'exe'           => array( '/^FixitRemote\.exe$/', __( 'Windows proqramı (FixitRemote.exe) — saytdakı düymə bunu verir', 'fixit' ) ),
		'client_update' => array( '/^FixitRemote\.Client-.*\.exe$/i', __( 'Proqramın avtomatik yenilənməsi', 'fixit' ) ),
		'client_msi'    => array( '/^FixitRemoteClient.*\.msi$/i', __( 'Şirkət quraşdırıcısı — Client (MSI)', 'fixit' ) ),
		'agent_msi'     => array( '/^FixitRemoteAgent.*\.msi$/i', __( 'Şirkət quraşdırıcısı — Agent (MSI)', 'fixit' ) ),
		'agent_update'  => array( '/^(agent-[\d.]+\.zip|FixitRemote\.Agent-.*\.exe|fixitremote-agent-linux.*)$/i', __( 'Agentlərin avtomatik yenilənməsi', 'fixit' ) ),
		'server'        => array( '/^(FixitRemoteServer.*\.msi|fixitremote-server-.*\.tar\.gz|server-[\d.]+\.zip)$/i', __( 'Server paketləri', 'fixit' ) ),
		'apk'           => array( '/\.apk$/i', __( 'Android (APK)', 'fixit' ) ),
	);
}

/**
 * Bütün buraxılışların yükləmə sayları (GitHub-un öz sayğacı).
 *
 * Saatda bir dəfə yenilənir. GitHub cavab verməsə, sonuncu nəticə göstərilir.
 *
 * @param bool $force Keşə baxmadan yoxla.
 * @return array|WP_Error
 */
function fixit_remote_github_stats( $force = false ) {
	$cached = $force ? false : get_transient( 'fixit_remote_gh_stats' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$releases = array();
	$ok       = true;

	for ( $page = 1; $page <= 3; $page++ ) {
		$response = wp_remote_get(
			'https://api.github.com/repos/' . FIXIT_REMOTE_REPO . '/releases?per_page=100&page=' . $page,
			array(
				'timeout' => 15,
				'headers' => array( 'Accept' => 'application/vnd.github+json' ),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			$ok = false;
			break;
		}

		$batch = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $batch ) ) {
			$ok = false;
			break;
		}

		$releases = array_merge( $releases, $batch );
		if ( count( $batch ) < 100 ) {
			break;
		}
	}

	if ( ! $ok ) {
		$last = get_option( 'fixit_remote_gh_stats_last' );
		if ( is_array( $last ) ) {
			$last['stale'] = true;
			set_transient( 'fixit_remote_gh_stats', $last, 10 * MINUTE_IN_SECONDS );
			return $last;
		}
		return new WP_Error( 'fixit_remote_github', __( 'GitHub hazırda cavab vermir. Bir az sonra «İndi yoxla» düyməsini basın.', 'fixit' ) );
	}

	$kinds    = fixit_remote_asset_kinds();
	$totals   = array_fill_keys( array_keys( $kinds ), 0 );
	$versions = array();

	foreach ( $releases as $release ) {
		if ( ! empty( $release['draft'] ) ) {
			continue;
		}

		$row = array_fill_keys( array_keys( $kinds ), 0 );

		foreach ( (array) ( isset( $release['assets'] ) ? $release['assets'] : array() ) as $asset ) {
			foreach ( $kinds as $kind => $def ) {
				if ( preg_match( $def[0], (string) $asset['name'] ) ) {
					$row[ $kind ] += (int) $asset['download_count'];
					break;
				}
			}
		}

		foreach ( $row as $kind => $n ) {
			$totals[ $kind ] += $n;
		}

		// "mobile" kimi xüsusi kanallar versiya cədvəlinə düşmür, cəmə düşür.
		if ( preg_match( '/^v?\d/', (string) $release['tag_name'] ) ) {
			$versions[] = array(
				'version' => ltrim( (string) $release['tag_name'], 'v' ),
				'date'    => (string) $release['published_at'],
				'url'     => esc_url_raw( $release['html_url'] ),
				'counts'  => $row,
			);
		}
	}

	$stats = array(
		'totals'   => $totals,
		'versions' => array_slice( $versions, 0, 15 ),
		'releases' => count( $versions ),
		'fetched'  => time(),
		'stale'    => false,
	);

	update_option( 'fixit_remote_gh_stats_last', $stats, false );
	set_transient( 'fixit_remote_gh_stats', $stats, HOUR_IN_SECONDS );

	return $stats;
}

/* ============================================================
   2. Kiçik köməkçilər
   ============================================================ */

/**
 * "3 saat əvvəl" — admin dilindən asılı olmadan azərbaycanca.
 *
 * @param int $ts Unix vaxtı.
 */
function fixit_remote_ago( $ts ) {
	$diff = max( 0, time() - (int) $ts );

	if ( $diff < HOUR_IN_SECONDS ) {
		/* translators: %d: dəqiqə */
		return sprintf( __( '%d dəq əvvəl', 'fixit' ), max( 1, (int) round( $diff / MINUTE_IN_SECONDS ) ) );
	}
	if ( $diff < 2 * DAY_IN_SECONDS ) {
		/* translators: %d: saat */
		return sprintf( __( '%d saat əvvəl', 'fixit' ), (int) floor( $diff / HOUR_IN_SECONDS ) );
	}

	/* translators: %d: gün */
	return sprintf( __( '%d gün əvvəl', 'fixit' ), (int) floor( $diff / DAY_IN_SECONDS ) );
}

/**
 * Buraxılış tarixi: "02.11.2026 11:52 · 3 saat əvvəl".
 *
 * @param string $iso GitHub tarixi (UTC).
 */
function fixit_remote_when( $iso ) {
	$ts = $iso ? strtotime( $iso ) : 0;
	return $ts ? wp_date( 'd.m.Y H:i', $ts ) . ' · ' . fixit_remote_ago( $ts ) : '';
}

/**
 * Admin səhifəsinin ünvanı.
 *
 * @param array $args Əlavə parametrlər.
 */
function fixit_remote_admin_url( $args = array() ) {
	return add_query_arg( $args, admin_url( 'admin.php?page=fixit-remote' ) );
}

/* ============================================================
   3. Menyu və başlanğıc vidceti
   ============================================================ */
function fixit_remote_admin_menu() {
	add_menu_page(
		__( 'Fixit Remote', 'fixit' ),
		__( 'Fixit Remote', 'fixit' ),
		'manage_options',
		'fixit-remote',
		'fixit_remote_admin_page',
		'dashicons-download',
		24
	);
}
add_action( 'admin_menu', 'fixit_remote_admin_menu' );

function fixit_remote_dashboard_setup() {
	if ( current_user_can( 'manage_options' ) ) {
		wp_add_dashboard_widget( 'fixit_remote_widget', __( 'Fixit Remote', 'fixit' ), 'fixit_remote_dashboard_widget' );
	}
}
add_action( 'wp_dashboard_setup', 'fixit_remote_dashboard_setup' );

/**
 * Başlanğıc vidceti: son versiya və yükləmə sayı.
 */
function fixit_remote_dashboard_widget() {
	$release = fixit_remote_release();
	$totals  = fixit_remote_download_totals();

	if ( $release['version'] ) {
		printf(
			'<p style="margin-top:0"><b style="font-size:15px">%1$s %2$s</b><br><span style="color:#646970">%3$s</span></p>',
			esc_html__( 'Son versiya:', 'fixit' ),
			esc_html( $release['version'] ),
			esc_html( fixit_remote_when( $release['published'] ) )
		);
	}

	$items = array(
		array( $totals['today'], __( 'bu gün', 'fixit' ) ),
		array( $totals['d7'], __( '7 gün', 'fixit' ) ),
		array( $totals['d30'], __( '30 gün', 'fixit' ) ),
		array( $totals['all'], __( 'hamısı', 'fixit' ) ),
	);

	echo '<p style="margin:0 0 6px;color:#646970">' . esc_html__( 'Saytdan yüklənib (Windows):', 'fixit' ) . '</p>';
	echo '<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;text-align:center">';
	foreach ( $items as $item ) {
		printf(
			'<div style="background:#f6f7f7;border-radius:6px;padding:10px 4px"><b style="display:block;font-size:22px">%s</b><span style="color:#646970;font-size:12px">%s</span></div>',
			esc_html( number_format_i18n( (int) $item[0] ) ),
			esc_html( $item[1] )
		);
	}
	echo '</div>';

	printf(
		'<p style="margin:10px 0 0;display:flex;gap:8px;flex-wrap:wrap"><a class="button button-primary" href="%1$s">%2$s</a><a class="button" href="%3$s">%4$s</a></p>',
		esc_url( $release['url'] ? $release['url'] : FIXIT_REMOTE_WINDOWS ),
		esc_html__( 'Ən son versiyanı yüklə', 'fixit' ),
		esc_url( fixit_remote_admin_url() ),
		esc_html__( 'Ətraflı statistika', 'fixit' )
	);
}

/* ============================================================
   4. Səhifə
   ============================================================ */
function fixit_remote_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$periods = array(
		7   => __( '7 gün', 'fixit' ),
		30  => __( '30 gün', 'fixit' ),
		90  => __( '90 gün', 'fixit' ),
		365 => __( '1 il', 'fixit' ),
	);

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- yalnız oxuma parametrləri
	$days  = isset( $_GET['days'] ) ? absint( $_GET['days'] ) : 30;
	$days  = isset( $periods[ $days ] ) ? $days : 30;
	$force = isset( $_GET['refresh'], $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'fixit_remote_refresh' );
	// phpcs:enable

	$release = fixit_remote_release( $force );
	$github  = fixit_remote_github_stats( $force );
	$totals  = fixit_remote_download_totals();
	$period  = fixit_remote_download_stats( $days );
	$refresh = wp_nonce_url( fixit_remote_admin_url( array( 'days' => $days, 'refresh' => 1 ) ), 'fixit_remote_refresh' );
	$short   = preg_replace( '#^https?://(www\.)?#', '', untrailingslashit( home_url( '/yukle/' ) ) );
	?>
	<style>
		.frm-wrap{max-width:1240px}
		.frm-top{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin:14px 0 4px}
		.frm-top .spacer{flex:1}
		.frm-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin:18px 0}
		.frm-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px 18px}
		.frm-card b{display:block;font-size:28px;line-height:1.1;margin-bottom:4px;font-variant-numeric:tabular-nums}
		.frm-card span{color:#646970;font-size:13px}
		.frm-card--ver{border-color:#2271b1;box-shadow:inset 3px 0 0 #2271b1}
		.frm-card small{display:block;color:#646970;margin-top:4px}
		.frm-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(380px,1fr));gap:16px;margin-bottom:16px}
		.frm-box{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px 18px}
		.frm-box h2{margin:0 0 12px;font-size:14px;display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap}
		.frm-box h2 small{font-weight:400;color:#646970}
		.frm-table{width:100%;border-collapse:collapse}
		.frm-table th{text-align:left;color:#646970;font-weight:400;font-size:12px;padding:0 0 6px}
		.frm-table td{padding:7px 0;border-bottom:1px solid #f0f0f1;vertical-align:middle}
		.frm-table tr:last-child td{border-bottom:0}
		.frm-table .num{text-align:right;font-variant-numeric:tabular-nums;font-weight:600;white-space:nowrap;padding-left:12px}
		.frm-table .muted{color:#8c8f94;font-weight:400}
		.frm-bar{display:block;height:4px;background:#f0f0f1;border-radius:4px;margin-top:5px;max-width:300px}
		.frm-bar i{display:block;height:100%;background:#2271b1;border-radius:4px}
		.frm-chart{width:100%;height:150px;display:block}
		.frm-chart rect{fill:#2271b1}
		.frm-chart g:hover rect{fill:#135e96}
		.frm-axis{display:flex;justify-content:space-between;color:#8c8f94;font-size:12px;margin-top:4px}
		.frm-note{color:#646970;font-size:12px;margin:10px 0 0}
		.frm-empty{color:#646970;margin:0}
	</style>

	<div class="wrap frm-wrap">
		<h1><?php esc_html_e( 'Fixit Remote', 'fixit' ); ?></h1>
		<p class="description">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: qısa ünvan */
					__( 'Saytdakı bütün «Yüklə» düymələri və %s ünvanı həmişə ən son versiyanı verir — yeni versiya çıxanda saytda heç nə dəyişmək lazım deyil. Versiya 10 dəqiqədən bir yoxlanılır.', 'fixit' ),
					$short
				)
			);
			?>
		</p>

		<div class="frm-top">
			<?php foreach ( $periods as $p => $label ) : ?>
				<a class="button <?php echo $p === $days ? 'button-primary' : ''; ?>" href="<?php echo esc_url( fixit_remote_admin_url( array( 'days' => $p ) ) ); ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
			<span class="spacer"></span>
			<a class="button" href="<?php echo esc_url( $refresh ); ?>"><span class="dashicons dashicons-update" style="vertical-align:-4px"></span> <?php esc_html_e( 'İndi yoxla', 'fixit' ); ?></a>
		</div>

		<!-- Əsas rəqəmlər -->
		<div class="frm-cards">
			<div class="frm-card frm-card--ver">
				<b><?php echo esc_html( $release['version'] ? $release['version'] : '—' ); ?></b>
				<span><?php esc_html_e( 'ən son versiya', 'fixit' ); ?></span>
				<?php if ( $release['published'] ) : ?>
					<small><?php echo esc_html( fixit_remote_when( $release['published'] ) ); ?></small>
				<?php endif; ?>
			</div>
			<?php
			$cards = array(
				array( $totals['today'], __( 'bu gün yüklənib', 'fixit' ), '' ),
				array( $totals['d7'], __( 'son 7 gündə', 'fixit' ), '' ),
				array( $totals['d30'], __( 'son 30 gündə', 'fixit' ), '' ),
				array(
					$totals['all'],
					__( 'bütün dövrdə (saytdan)', 'fixit' ),
					/* translators: %s: tarix */
					$totals['since'] ? sprintf( __( '%s tarixindən', 'fixit' ), wp_date( 'd.m.Y', strtotime( $totals['since'] . ' 12:00' ) ) ) : '',
				),
			);
			foreach ( $cards as $card ) :
				?>
				<div class="frm-card">
					<b><?php echo esc_html( number_format_i18n( (int) $card[0] ) ); ?></b>
					<span><?php echo esc_html( $card[1] ); ?></span>
					<?php if ( $card[2] ) : ?>
						<small><?php echo esc_html( $card[2] ); ?></small>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="frm-grid">
			<?php
			fixit_remote_admin_chart( $period['daily'], $period['total'], $periods[ $days ] );
			fixit_remote_admin_sources( $period['sources'], $period['total'] );
			?>
		</div>

		<div class="frm-grid">
			<?php
			fixit_remote_admin_files( $release );
			fixit_remote_admin_github( $github );
			?>
		</div>

		<?php if ( ! is_wp_error( $github ) && $github['versions'] ) : ?>
			<div class="frm-box">
				<h2><?php esc_html_e( 'Versiyalar üzrə (GitHub sayğacı)', 'fixit' ); ?></h2>
				<table class="frm-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Versiya', 'fixit' ); ?></th>
							<th><?php esc_html_e( 'Tarix', 'fixit' ); ?></th>
							<th class="num"><?php esc_html_e( 'Proqram (EXE)', 'fixit' ); ?></th>
							<th class="num"><?php esc_html_e( 'Avtomatik yenilənmə', 'fixit' ); ?></th>
							<th class="num"><?php esc_html_e( 'MSI', 'fixit' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $github['versions'] as $ver ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( $ver['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $ver['version'] ); ?></a></td>
								<td class="muted"><?php echo esc_html( $ver['date'] ? wp_date( 'd.m.Y H:i', strtotime( $ver['date'] ) ) : '' ); ?></td>
								<td class="num"><?php echo esc_html( number_format_i18n( $ver['counts']['exe'] ) ); ?></td>
								<td class="num"><?php echo esc_html( number_format_i18n( $ver['counts']['client_update'] + $ver['counts']['agent_update'] ) ); ?></td>
								<td class="num"><?php echo esc_html( number_format_i18n( $ver['counts']['client_msi'] + $ver['counts']['agent_msi'] ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Günlər üzrə yükləmə qrafiki (SVG, xarici kitabxana yoxdur).
 *
 * @param array  $daily  tarix => say.
 * @param int    $total  Dövr üzrə cəm.
 * @param string $label  Dövrün adı.
 */
function fixit_remote_admin_chart( $daily, $total, $label ) {
	$n   = count( $daily );
	$max = max( 1, $daily ? max( $daily ) : 1 );
	$w   = 10;
	$h   = 120;
	$svg = '';
	$i   = 0;

	foreach ( $daily as $date => $count ) {
		$bh   = $count ? max( 2, round( $h * $count / $max, 1 ) ) : 0;
		$svg .= sprintf(
			'<g><title>%1$s</title><rect x="%2$s" y="%3$s" width="%4$s" height="%5$s"/></g>',
			esc_html( wp_date( 'd.m.Y', strtotime( $date . ' 12:00' ) ) . ' — ' . number_format_i18n( (int) $count ) ),
			esc_attr( $i * $w + 1 ),
			esc_attr( $h - $bh ),
			esc_attr( $w - 2 ),
			esc_attr( $bh )
		);
		++$i;
	}

	$keys = array_keys( $daily );
	?>
	<div class="frm-box">
		<h2>
			<?php esc_html_e( 'Saytdan yükləmələr — günlər üzrə', 'fixit' ); ?>
			<small>
				<?php
				/* translators: 1: dövr, 2: say, 3: ən çox */
				echo esc_html( sprintf( __( '%1$s: %2$s · ən çox bir gündə: %3$s', 'fixit' ), $label, number_format_i18n( (int) $total ), number_format_i18n( (int) ( $daily ? max( $daily ) : 0 ) ) ) );
				?>
			</small>
		</h2>
		<svg class="frm-chart" viewBox="0 0 <?php echo esc_attr( max( 1, $n ) * $w ); ?> <?php echo esc_attr( $h ); ?>" preserveAspectRatio="none" role="img" aria-label="<?php esc_attr_e( 'Günlük yükləmə qrafiki', 'fixit' ); ?>">
			<?php echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- yuxarıda hər dəyər escape olunub ?>
		</svg>
		<div class="frm-axis">
			<span><?php echo esc_html( $keys ? wp_date( 'd.m', strtotime( $keys[0] . ' 12:00' ) ) : '' ); ?></span>
			<span><?php echo esc_html( $keys ? wp_date( 'd.m', strtotime( end( $keys ) . ' 12:00' ) ) : '' ); ?></span>
		</div>
		<p class="frm-note"><?php esc_html_e( 'Saytdakı «Yüklə» düymələri və qısa ünvanla edilən yükləmələr. Axtarış robotları və link önizləmələri sayılmır. IP ünvanı saxlanılmır.', 'fixit' ); ?></p>
	</div>
	<?php
}

/**
 * Hansı düymədən yükləndi.
 *
 * @param array $sources mənbə => say.
 * @param int   $total   Cəm.
 */
function fixit_remote_admin_sources( $sources, $total ) {
	$labels = function_exists( 'fixit_demo_stats_labels' ) ? fixit_demo_stats_labels( 'download' ) : array();
	?>
	<div class="frm-box">
		<h2><?php esc_html_e( 'Haradan yükləndi (saytdakı düymə)', 'fixit' ); ?></h2>
		<?php if ( ! $sources ) : ?>
			<p class="frm-empty"><?php esc_html_e( 'Bu dövrdə hələ yükləmə yoxdur.', 'fixit' ); ?></p>
		<?php else : ?>
			<table class="frm-table"><tbody>
				<?php
				$max = max( $sources );
				foreach ( $sources as $key => $count ) :
					?>
					<tr>
						<td>
							<?php echo esc_html( isset( $labels[ $key ] ) ? $labels[ $key ] : $key ); ?>
							<span class="frm-bar"><i style="width:<?php echo esc_attr( $max ? round( 100 * $count / $max ) : 0 ); ?>%"></i></span>
						</td>
						<td class="num"><?php echo esc_html( number_format_i18n( (int) $count ) ); ?></td>
						<td class="num muted"><?php echo esc_html( $total ? round( 100 * $count / $total ) : 0 ); ?>%</td>
					</tr>
				<?php endforeach; ?>
			</tbody></table>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Ən son versiyanın faylları — admin buradan birbaşa yükləyə bilər.
 *
 * @param array $release fixit_remote_release().
 */
function fixit_remote_admin_files( $release ) {
	?>
	<div class="frm-box">
		<h2>
			<?php
			/* translators: %s: versiya */
			echo esc_html( sprintf( __( 'Ən son versiyanın faylları (%s)', 'fixit' ), $release['version'] ? $release['version'] : '—' ) );
			?>
			<?php if ( $release['page'] ) : ?>
				<small><a href="<?php echo esc_url( $release['page'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'GitHub-da bax', 'fixit' ); ?> ↗</a></small>
			<?php endif; ?>
		</h2>

		<p style="margin:0 0 12px">
			<a class="button button-primary" href="<?php echo esc_url( $release['url'] ? $release['url'] : FIXIT_REMOTE_WINDOWS ); ?>">
				<span class="dashicons dashicons-download" style="vertical-align:-4px"></span>
				<?php esc_html_e( 'Windows proqramını yüklə (FixitRemote.exe)', 'fixit' ); ?>
			</a>
		</p>

		<?php if ( ! $release['assets'] ) : ?>
			<p class="frm-empty"><?php esc_html_e( 'Faylların siyahısı hazırda alınmadı — yuxarıdakı düymə yenə də ən son versiyanı verir.', 'fixit' ); ?></p>
		<?php else : ?>
			<table class="frm-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Fayl', 'fixit' ); ?></th>
						<th class="num"><?php esc_html_e( 'Həcm', 'fixit' ); ?></th>
						<th class="num"><?php esc_html_e( 'Yüklənib', 'fixit' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $release['assets'] as $asset ) : ?>
						<tr>
							<td><a href="<?php echo esc_url( $asset['url'] ); ?>"><?php echo esc_html( $asset['name'] ); ?></a></td>
							<td class="num muted"><?php echo esc_html( size_format( $asset['size'], 1 ) ); ?></td>
							<td class="num"><?php echo esc_html( number_format_i18n( $asset['count'] ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="frm-note"><?php esc_html_e( '«Yüklənib» — GitHub-un sayğacı (bu versiya üçün, bütün mənbələrdən).', 'fixit' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * GitHub sayğacı: bütün buraxılışlar üzrə cəm.
 *
 * @param array|WP_Error $github fixit_remote_github_stats().
 */
function fixit_remote_admin_github( $github ) {
	?>
	<div class="frm-box">
		<h2>
			<?php esc_html_e( 'Bütün versiyalar üzrə cəm (GitHub sayğacı)', 'fixit' ); ?>
			<?php if ( ! is_wp_error( $github ) ) : ?>
				<small>
					<?php
					/* translators: 1: buraxılış sayı, 2: vaxt */
					echo esc_html( sprintf( __( '%1$s buraxılış · yeniləndi: %2$s', 'fixit' ), number_format_i18n( $github['releases'] ), wp_date( 'H:i', $github['fetched'] ) ) );
					?>
				</small>
			<?php endif; ?>
		</h2>

		<?php if ( is_wp_error( $github ) ) : ?>
			<p class="frm-empty"><?php echo esc_html( $github->get_error_message() ); ?></p>
		<?php else : ?>
			<?php if ( ! empty( $github['stale'] ) ) : ?>
				<p class="frm-note" style="margin:0 0 10px"><?php esc_html_e( 'GitHub hazırda cavab vermir — sonuncu alınmış rəqəmlər göstərilir.', 'fixit' ); ?></p>
			<?php endif; ?>
			<table class="frm-table"><tbody>
				<?php foreach ( fixit_remote_asset_kinds() as $kind => $def ) : ?>
					<tr>
						<td><?php echo esc_html( $def[1] ); ?></td>
						<td class="num"><?php echo esc_html( number_format_i18n( (int) $github['totals'][ $kind ] ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody></table>
			<p class="frm-note"><?php esc_html_e( 'GitHub hər faylın neçə dəfə yükləndiyini özü sayır: saytdan, birbaşa linkdən, proqramın və agentlərin avtomatik yenilənməsindən. Buna sınaq yükləmələri də daxildir, ona görə saytın öz sayğacından çox olur.', 'fixit' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}
