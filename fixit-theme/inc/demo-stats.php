<?php
/**
 * Admin paneldə «Demo statistikası»: demolara gələnlər nə edir.
 *
 * İki demo var: Sorğu Sistemi (demo.fixit.az, `GET /public/demo/stats`) və
 * Fixit Remote (remote-demo.fixit.az, `GET /api/demo/stats`). Hər ikisi eyni
 * formatda cavab verir. Məlumat demo serverindədir - sayt onu açarla oxuyur
 * və göstərir, öz bazasına heç nə yazmır. Səbəb: ziyarətçi demonun içində nə
 * edirsə (rol, bölmə, gördüyü iş) onu yalnız demo serveri görür; sayt yalnız
 * oraya keçidi görərdi.
 *
 * Bir dəfəlik qurulum (hər demo üçün ayrıca): bu səhifədə «Açar yarat» →
 * açarı demo serverinə yazmaq (əmr «Ayarlar» bölməsində hazır verilir).
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FIXIT_DEMO_STATS_URL', 'https://demo.fixit.az/public/demo/stats' );
define( 'FIXIT_REMOTE_DEMO_STATS_URL', 'https://remote-demo.fixit.az/api/demo/stats' );

/**
 * Demolar. Sorğu Sisteminin ayarları köhnə adlarla qalır (əvvəlki qurulum
 * pozulmasın), Fixit Remote-unku `_remote` şəkilçisi ilə.
 */
function fixit_demo_stats_products() {
	return array(
		'ticket' => array(
			'label'  => 'Ticket + Task',
			'host'   => 'demo.fixit.az',
			'url'    => FIXIT_DEMO_STATS_URL,
			'suffix' => '',
		),
		'remote' => array(
			'label'  => 'Fixit Remote',
			'host'   => 'remote-demo.fixit.az',
			'url'    => FIXIT_REMOTE_DEMO_STATS_URL,
			'suffix' => '_remote',
		),
	);
}

function fixit_demo_stats_product( $value ) {
	return 'remote' === $value ? 'remote' : 'ticket';
}

function fixit_demo_stats_info( $product ) {
	$products = fixit_demo_stats_products();
	return $products[ fixit_demo_stats_product( $product ) ];
}

/**
 * Seçilə bilən dövrlər (gün).
 */
function fixit_demo_stats_periods() {
	return array(
		7   => __( '7 gün', 'fixit' ),
		30  => __( '30 gün', 'fixit' ),
		90  => __( '90 gün', 'fixit' ),
		365 => __( '1 il', 'fixit' ),
	);
}

function fixit_demo_stats_url( $product = 'ticket' ) {
	$info = fixit_demo_stats_info( $product );
	$url  = (string) get_option( 'fixit_demo_stats_url' . $info['suffix'], '' );
	return $url ? $url : $info['url'];
}

function fixit_demo_stats_token( $product = 'ticket' ) {
	$info = fixit_demo_stats_info( $product );
	return (string) get_option( 'fixit_demo_stats_token' . $info['suffix'], '' );
}

/* ============================================================
   1. Menyu və başlanğıc vidceti
   ============================================================ */
function fixit_demo_stats_menu() {
	add_menu_page(
		__( 'Demo statistikası', 'fixit' ),
		__( 'Demo statistikası', 'fixit' ),
		'manage_options',
		'fixit-demo-stats',
		'fixit_demo_stats_page',
		'dashicons-visibility',
		24
	);
}
add_action( 'admin_menu', 'fixit_demo_stats_menu' );

/**
 * Başlanğıc səhifəsində qısa xülasə - hər gün açılan yerdə görünsün.
 */
function fixit_demo_stats_dashboard_widget() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	wp_add_dashboard_widget( 'fixit_demo_stats', __( 'Demo — son 7 gün', 'fixit' ), 'fixit_demo_stats_widget' );
}
add_action( 'wp_dashboard_setup', 'fixit_demo_stats_dashboard_widget' );

function fixit_demo_stats_widget() {
	$shown = 0;

	foreach ( fixit_demo_stats_products() as $product => $info ) {
		// Açarı qurulmayan Remote demosu başlanğıc səhifəsində xəta ilə görünməsin
		if ( 'ticket' !== $product && ! fixit_demo_stats_token( $product ) ) {
			continue;
		}

		$page = admin_url( 'admin.php?page=fixit-demo-stats&product=' . $product );
		$data = fixit_demo_stats_fetch( 7, false, $product );

		printf( '<h3 style="margin:%s 0 8px;font-size:13px">%s</h3>', $shown ? '16px' : '0', esc_html( $info['label'] ) );
		++$shown;

		if ( is_wp_error( $data ) ) {
			printf(
				'<p>%s</p><p><a class="button" href="%s">%s</a></p>',
				esc_html( $data->get_error_message() ),
				esc_url( $page ),
				esc_html__( 'Demo statistikasına keç', 'fixit' )
			);
			continue;
		}

		$t     = $data['totals'];
		$items = array(
			array( $t['visits'], __( 'ziyarət', 'fixit' ) ),
			array( $t['logins'], __( 'rol seçdi', 'fixit' ) ),
			array( $t['active'], __( 'iş gördü', 'fixit' ) ),
			array( $t['contact_visits'], __( '«Tam versiya»', 'fixit' ) ),
		);
		echo '<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;text-align:center">';
		foreach ( $items as $item ) {
			printf(
				'<div style="background:#f6f7f7;border-radius:6px;padding:10px 4px"><b style="display:block;font-size:22px">%s</b><span style="color:#646970;font-size:12px">%s</span></div>',
				esc_html( number_format_i18n( (int) $item[0] ) ),
				esc_html( $item[1] )
			);
		}
		echo '</div>';
		printf( '<p style="margin:8px 0 0"><a href="%s">%s →</a></p>', esc_url( $page ), esc_html__( 'Ətraflı statistika', 'fixit' ) );
	}
}

/* ============================================================
   2. Ayarlar (açar)
   ============================================================ */
function fixit_demo_stats_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'İcazə yoxdur.', 'fixit' ) );
	}
	check_admin_referer( 'fixit_demo_stats_save' );

	$product = fixit_demo_stats_product( isset( $_POST['fixit_product'] ) ? sanitize_key( wp_unslash( $_POST['fixit_product'] ) ) : '' );
	$info    = fixit_demo_stats_info( $product );
	$notice  = 'saved';

	if ( isset( $_POST['fixit_generate'] ) ) {
		// Uzun, təsadüfi, yalnız hərf-rəqəm: serverin ayar faylında dırnaq tələb etməsin
		update_option( 'fixit_demo_stats_token' . $info['suffix'], wp_generate_password( 40, false, false ), false );
		$notice = 'generated';
	} else {
		$token = isset( $_POST['fixit_demo_stats_token'] ) ? sanitize_text_field( wp_unslash( $_POST['fixit_demo_stats_token'] ) ) : '';
		update_option( 'fixit_demo_stats_token' . $info['suffix'], preg_replace( '/[^A-Za-z0-9_-]/', '', $token ), false );

		$url = isset( $_POST['fixit_demo_stats_url'] ) ? esc_url_raw( wp_unslash( $_POST['fixit_demo_stats_url'] ) ) : '';
		update_option( 'fixit_demo_stats_url' . $info['suffix'], $info['url'] === $url ? '' : $url, false );
	}

	fixit_demo_stats_flush( $product );

	wp_safe_redirect(
		add_query_arg(
			array(
				'fixit_notice' => $notice,
				'product'      => $product,
				'tab'          => 'settings',
			),
			admin_url( 'admin.php?page=fixit-demo-stats' )
		)
	);
	exit;
}
add_action( 'admin_post_fixit_demo_stats_save', 'fixit_demo_stats_save' );

function fixit_demo_stats_cache_key( $product, $days ) {
	return 'fixit_demo_stats_' . fixit_demo_stats_product( $product ) . '_' . (int) $days;
}

/**
 * Keşi təmizləyir - açar dəyişəndə köhnə cavab (və ya köhnə xəta) qalmasın.
 */
function fixit_demo_stats_flush( $product = 'ticket' ) {
	foreach ( array_keys( fixit_demo_stats_periods() ) as $days ) {
		delete_transient( fixit_demo_stats_cache_key( $product, $days ) );
	}
}

/* ============================================================
   3. Demo serverindən oxuma
   ============================================================ */

/**
 * Statistikanı gətirir (5 dəqiqəlik keşlə).
 *
 * @param int    $days    Dövr.
 * @param bool   $force   Keşi keç (Yenilə düyməsi).
 * @param string $product ticket | remote.
 * @return array|WP_Error
 */
function fixit_demo_stats_fetch( $days, $force = false, $product = 'ticket' ) {
	$token = fixit_demo_stats_token( $product );
	if ( ! $token ) {
		return new WP_Error( 'fixit_no_token', __( 'Statistika hələ qurulmayıb: «Ayarlar» bölməsində açar yaradın və demo serverinə yazın.', 'fixit' ) );
	}

	$key = fixit_demo_stats_cache_key( $product, $days );
	if ( ! $force ) {
		$cached = get_transient( $key );
		if ( is_array( $cached ) ) {
			return $cached;
		}
	}

	$url      = fixit_demo_stats_url( $product );
	$response = wp_remote_get(
		add_query_arg( 'days', (int) $days, $url ),
		array(
			'timeout'    => 12,
			'user-agent' => 'FIXIT-WP/' . FIXIT_VERSION . '; ' . home_url( '/' ),
			'headers'    => array(
				'Authorization' => 'Bearer ' . $token,
				'Accept'        => 'application/json',
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'fixit_network', sprintf( /* translators: %s: xəta mətni */ __( 'Demo serverinə qoşulmaq olmadı: %s', 'fixit' ), $response->get_error_message() ) );
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( 200 !== $code || ! is_array( $data ) || ! isset( $data['totals'] ) ) {
		if ( 401 === $code ) {
			$message = __( 'Açar uyğun gəlmir: demo serverinə yazılan açar bu səhifədəki açarla eyni olmalıdır.', 'fixit' );
		} elseif ( 404 === $code ) {
			$message = __( 'Demo serveri statistikanı vermir: ya açar serverə yazılmayıb, ya da demo hələ yenilənməyib.', 'fixit' );
		} elseif ( false !== stripos( $body, 'cloudflare' ) ) {
			// Bot qoruması saytın serverindən gələn sorğunu yoxlamaya göndərə bilər
			$message = sprintf(
				/* translators: %s: ünvanın yolu */
				__( 'Cloudflare sorğunu dayandırdı. Cloudflare-də %s ünvanı üçün yoxlamanı söndürən qayda (WAF → Skip) əlavə edin.', 'fixit' ),
				(string) wp_parse_url( $url, PHP_URL_PATH )
			);
		} else {
			/* translators: %d: HTTP status kodu */
			$message = sprintf( __( 'Demo serveri gözlənilməz cavab verdi (HTTP %d).', 'fixit' ), $code );
		}
		return new WP_Error( 'fixit_bad_response', $message );
	}

	set_transient( $key, $data, 5 * MINUTE_IN_SECONDS );
	return $data;
}

/* ============================================================
   4. Adlar (serverin açarları → oxunaqlı mətn)
   ============================================================ */
function fixit_demo_stats_labels( $group ) {
	$labels = array(
		'source'  => array(
			'home'         => __( 'Ana səhifə — flaqman blok', 'fixit' ),
			'mehsul-hero'  => __( 'Məhsul səhifəsi — yuxarı düymə', 'fixit' ),
			'mehsul-demo'  => __( 'Məhsul səhifəsi — demo bloku', 'fixit' ),
			'mehsul-rol'   => __( 'Məhsul səhifəsi — rol kartı', 'fixit' ),
			'mehsullar'    => __( 'Məhsullar siyahısı', 'fixit' ),
			'(birbaşa)'    => __( 'Birbaşa / başqa yerdən', 'fixit' ),
		),
		'role'    => array(
			'admin'    => __( 'Admin', 'fixit' ),
			'agent'    => __( 'Agent', 'fixit' ),
			'manager'  => __( 'Menecer', 'fixit' ),
			'customer' => __( 'Müştəri', 'fixit' ),
		),
		'section' => array(
			'dashboard'  => __( 'Dashboard', 'fixit' ),
			'tickets'    => __( 'Sorğular siyahısı', 'fixit' ),
			'ticket'     => __( 'Sorğunun içi', 'fixit' ),
			'ticket_new' => __( 'Yeni sorğu forması', 'fixit' ),
			'board'      => __( 'Ticket lövhəsi', 'fixit' ),
			'tasks'      => __( 'Tapşırıqlar lövhəsi', 'fixit' ),
			'task'       => __( 'Tapşırığın içi', 'fixit' ),
			'task_new'   => __( 'Yeni tapşırıq forması', 'fixit' ),
			'recurring'  => __( 'Təkrarlanan işlər', 'fixit' ),
			'reports'    => __( 'Hesabatlar', 'fixit' ),
			'time'       => __( 'Vaxt hesabatı', 'fixit' ),
			'admin'      => __( 'İdarəetmə (bağlı bölmələr)', 'fixit' ),
			'profile'    => __( 'Profil', 'fixit' ),
			'other'      => __( 'Digər', 'fixit' ),
		),
		'action'  => array(
			'ticket_created'   => __( 'Sorğu yaradıb', 'fixit' ),
			'task_created'     => __( 'Tapşırıq yaradıb', 'fixit' ),
			'task_from_ticket' => __( 'Sorğudan tapşırıq', 'fixit' ),
			'comment'          => __( 'Cavab / şərh yazıb', 'fixit' ),
			'updated'          => __( 'Status, sahə dəyişib', 'fixit' ),
			'assigned'         => __( 'İcraçı təyin edib', 'fixit' ),
			'checklist'        => __( 'Checklist', 'fixit' ),
			'time_logged'      => __( 'Vaxt qeyd edib', 'fixit' ),
			'recurrence'       => __( 'Təkrarlanan qayda', 'fixit' ),
			'dashboard'        => __( 'Dashboard qurub', 'fixit' ),
			'saved_view'       => __( 'Görünüş / hesabat saxlayıb', 'fixit' ),
			'other'            => __( 'Digər', 'fixit' ),
		),
		'device'  => array(
			'desktop' => __( 'Kompüter', 'fixit' ),
			'mobile'  => __( 'Telefon', 'fixit' ),
			'tablet'  => __( 'Planşet', 'fixit' ),
		),
		'country' => array(
			'AZ' => __( 'Azərbaycan', 'fixit' ),
			'TR' => __( 'Türkiyə', 'fixit' ),
			'RU' => __( 'Rusiya', 'fixit' ),
			'GE' => __( 'Gürcüstan', 'fixit' ),
			'KZ' => __( 'Qazaxıstan', 'fixit' ),
			'UZ' => __( 'Özbəkistan', 'fixit' ),
			'UA' => __( 'Ukrayna', 'fixit' ),
			'IR' => __( 'İran', 'fixit' ),
			'AE' => __( 'BƏƏ', 'fixit' ),
			'US' => __( 'ABŞ', 'fixit' ),
			'GB' => __( 'Böyük Britaniya', 'fixit' ),
			'DE' => __( 'Almaniya', 'fixit' ),
			'NL' => __( 'Niderland', 'fixit' ),
			'FR' => __( 'Fransa', 'fixit' ),
			'??' => __( 'Naməlum', 'fixit' ),
		),
		'other'   => array(
			'(yoxdur)' => __( 'Yoxdur (birbaşa açılıb)', 'fixit' ),
			'??'       => __( 'Naməlum', 'fixit' ),
		),
	);

	// Fixit Remote: rollar, panelin bölmələri və orada görülən işlər başqadır
	if ( 'remote' === fixit_demo_stats_current() ) {
		$labels['role']    = array(
			'admin'    => __( 'Admin', 'fixit' ),
			'manager'  => __( 'Menecer', 'fixit' ),
			'operator' => __( 'Operator (helpdesk)', 'fixit' ),
			'ceo'      => __( 'Rəhbər', 'fixit' ),
		);
		$labels['section'] = array(
			'dashboard'     => __( 'İdarə lövhəsi', 'fixit' ),
			'devices'       => __( 'Cihazlar', 'fixit' ),
			'viewer'        => __( 'Ekran pəncərəsi', 'fixit' ),
			'agent-install' => __( 'Agent yüklə', 'fixit' ),
			'sessions'      => __( 'Sessiyalar', 'fixit' ),
			'teams'         => __( 'Komandalar', 'fixit' ),
			'groups'        => __( 'Qruplar', 'fixit' ),
			'users'         => __( 'İstifadəçilər', 'fixit' ),
			'audit'         => __( 'Sistem logu', 'fixit' ),
			'exports'       => __( 'Log yükləmə', 'fixit' ),
			'settings'      => __( 'Ayarlar', 'fixit' ),
			'ldap'          => __( 'Active Directory', 'fixit' ),
			'update'        => __( 'Yenilənmə', 'fixit' ),
			'maintenance'   => __( 'Baxım', 'fixit' ),
			'about'         => __( 'Haqqında', 'fixit' ),
			'account'       => __( 'Hesabım', 'fixit' ),
			'other'         => __( 'Digər', 'fixit' ),
		);
		$labels['action']  = array(
			'connect'         => __( 'Ekrana qoşulub', 'fixit' ),
			'view'            => __( 'Ekrana baxıb (yalnız baxış)', 'fixit' ),
			'viewer_command'  => __( 'Qısayol / əmr göndərib', 'fixit' ),
			'viewer_settings' => __( 'Görünüş, icazə dəyişib', 'fixit' ),
			'screenshot'      => __( 'Ekran şəkli çəkib', 'fixit' ),
			'record'          => __( 'Video yazıb', 'fixit' ),
			'chat'            => __( 'Yazışmanı açıb', 'fixit' ),
			'admin_tools'     => __( 'Admin alətlərini açıb', 'fixit' ),
			'export'          => __( 'PDF / CSV yükləyib', 'fixit' ),
			'agent_download'  => __( 'Agenti yükləmək istəyib', 'fixit' ),
			'other'           => __( 'Digər', 'fixit' ),
		);
	}

	return isset( $labels[ $group ] ) ? $labels[ $group ] : array();
}

/**
 * Hazırda göstərilən demo (adlar ona görə seçilir).
 *
 * @param string|null $set Təyin etmək üçün.
 */
function fixit_demo_stats_current( $set = null ) {
	static $current = 'ticket';
	if ( null !== $set ) {
		$current = fixit_demo_stats_product( $set );
	}
	return $current;
}

function fixit_demo_stats_label( $group, $key ) {
	$labels = fixit_demo_stats_labels( $group );
	return isset( $labels[ $key ] ) ? $labels[ $key ] : (string) $key;
}

/**
 * Ölkə kodundan bayraq (regional göstərici simvolları).
 */
function fixit_demo_stats_flag( $code ) {
	if ( ! preg_match( '/^[A-Z]{2}$/', (string) $code ) ) {
		return '';
	}
	return '&#' . ( 127397 + ord( $code[0] ) ) . ';&#' . ( 127397 + ord( $code[1] ) ) . ';';
}

/* ============================================================
   5. Səhifə
   ============================================================ */

/**
 * Bir bölgü cədvəli: ad, say, nisbi zolaq.
 *
 * @param string $title  Başlıq.
 * @param array  $rows   açar => say.
 * @param string $group  Ad qrupu (fixit_demo_stats_labels).
 * @param int    $total  Faiz üçün əsas (ziyarət sayı).
 * @param string $empty  Boş olanda mətn.
 */
function fixit_demo_stats_table( $title, $rows, $group, $total, $empty = '' ) {
	echo '<div class="fds-box"><h3>' . esc_html( $title ) . '</h3>';
	if ( ! $rows ) {
		echo '<p class="fds-empty">' . esc_html( $empty ? $empty : __( 'Hələ məlumat yoxdur.', 'fixit' ) ) . '</p></div>';
		return;
	}
	$max = max( $rows );
	echo '<table class="fds-table"><tbody>';
	foreach ( $rows as $key => $count ) {
		$label = fixit_demo_stats_label( $group, $key );
		$flag  = 'country' === $group ? fixit_demo_stats_flag( $key ) : '';
		$pct   = $total ? round( 100 * $count / $total ) : 0;
		printf(
			'<tr><td>%1$s%2$s<span class="fds-bar"><i style="width:%3$s%%"></i></span></td><td class="num">%4$s</td><td class="num muted">%5$s%%</td></tr>',
			$flag ? '<span class="fds-flag">' . $flag . '</span> ' : '', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- yalnız rəqəmli HTML entity
			esc_html( $label ),
			esc_attr( $max ? round( 100 * $count / $max ) : 0 ),
			esc_html( number_format_i18n( (int) $count ) ),
			esc_html( $pct )
		);
	}
	echo '</tbody></table></div>';
}

/**
 * Günlük ziyarət qrafiki (SVG, xarici kitabxana yoxdur).
 *
 * @param array $daily [{date, visits, logins, active}].
 */
function fixit_demo_stats_chart( $daily ) {
	$n   = count( $daily );
	$max = 1;
	foreach ( $daily as $d ) {
		$max = max( $max, (int) $d['visits'] );
	}
	$w   = 10;
	$h   = 120;
	$svg = '';
	foreach ( $daily as $i => $d ) {
		$x  = $i * $w;
		$vh = round( $h * $d['visits'] / $max, 1 );
		$lh = round( $h * $d['logins'] / $max, 1 );
		$t  = sprintf(
			/* translators: 1: tarix, 2: ziyarət, 3: rol seçən, 4: iş görən */
			__( '%1$s — %2$d ziyarət, %3$d rol seçdi, %4$d iş gördü', 'fixit' ),
			wp_date( 'j M', strtotime( $d['date'] . ' 12:00' ) ),
			$d['visits'],
			$d['logins'],
			$d['active']
		);
		$svg .= sprintf(
			'<g><title>%1$s</title><rect x="%2$s" y="%3$s" width="%4$s" height="%5$s" class="v"/><rect x="%2$s" y="%6$s" width="%4$s" height="%7$s" class="l"/></g>',
			esc_html( $t ),
			esc_attr( $x + 1 ),
			esc_attr( $h - $vh ),
			esc_attr( $w - 2 ),
			esc_attr( $vh ),
			esc_attr( $h - $lh ),
			esc_attr( $lh )
		);
	}

	$first = $n ? wp_date( 'j M', strtotime( $daily[0]['date'] . ' 12:00' ) ) : '';
	$last  = $n ? wp_date( 'j M', strtotime( $daily[ $n - 1 ]['date'] . ' 12:00' ) ) : '';
	?>
	<div class="fds-box">
		<h3><?php esc_html_e( 'Günlər üzrə', 'fixit' ); ?>
			<span class="fds-legend"><i class="v"></i><?php esc_html_e( 'ziyarət', 'fixit' ); ?> <i class="l"></i><?php esc_html_e( 'rol seçdi', 'fixit' ); ?> · <?php echo esc_html( sprintf( /* translators: %d: ən çox */ __( 'ən çox: %d', 'fixit' ), $max ) ); ?></span>
		</h3>
		<svg class="fds-chart" viewBox="0 0 <?php echo esc_attr( max( 1, $n ) * $w ); ?> <?php echo esc_attr( $h ); ?>" preserveAspectRatio="none" role="img" aria-label="<?php esc_attr_e( 'Günlük ziyarət qrafiki', 'fixit' ); ?>">
			<?php echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- yuxarıda hər dəyər escape olunub ?>
		</svg>
		<div class="fds-axis"><span><?php echo esc_html( $first ); ?></span><span><?php echo esc_html( $last ); ?></span></div>
	</div>
	<?php
}

/**
 * Günün saatları üzrə (Bakı vaxtı) - demo nə vaxt açılır.
 */
function fixit_demo_stats_hours( $hours ) {
	$max = max( 1, max( $hours ) );
	echo '<div class="fds-box"><h3>' . esc_html__( 'Günün saatı (Bakı vaxtı)', 'fixit' ) . '</h3><div class="fds-hours">';
	foreach ( $hours as $hour => $count ) {
		printf(
			'<span title="%1$s"><i style="height:%2$s%%"></i><small>%3$s</small></span>',
			esc_attr( sprintf( '%02d:00 — %d', $hour, $count ) ),
			esc_attr( max( 2, round( 100 * $count / $max ) ) ),
			esc_html( 0 === $hour % 3 ? sprintf( '%02d', $hour ) : '' )
		);
	}
	echo '</div></div>';
}

function fixit_demo_stats_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$periods = fixit_demo_stats_periods();
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- yalnız oxuma parametrləri
	$tab  = isset( $_GET['tab'] ) && 'settings' === $_GET['tab'] ? 'settings' : 'stats';
	$days = isset( $_GET['days'] ) ? absint( $_GET['days'] ) : 30;
	$days = isset( $periods[ $days ] ) ? $days : 30;
	$notice = isset( $_GET['fixit_notice'] ) ? sanitize_key( $_GET['fixit_notice'] ) : '';
	$force  = isset( $_GET['refresh'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'fixit_demo_stats_refresh' );
	$product = fixit_demo_stats_product( isset( $_GET['product'] ) ? sanitize_key( $_GET['product'] ) : '' );
	// phpcs:enable

	fixit_demo_stats_current( $product );
	$info  = fixit_demo_stats_info( $product );
	$root  = admin_url( 'admin.php?page=fixit-demo-stats' );
	$base  = add_query_arg( 'product', $product, $root );
	$data  = 'stats' === $tab ? fixit_demo_stats_fetch( $days, $force, $product ) : null;
	?>
	<style>
		.fds-wrap{max-width:1240px}
		.fds-top{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin:14px 0 4px}
		.fds-top .spacer{flex:1}
		.fds-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin:18px 0}
		.fds-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px 18px}
		.fds-card b{display:block;font-size:28px;line-height:1.1;margin-bottom:4px}
		.fds-card span{color:#646970;font-size:13px}
		.fds-card small{display:block;color:#008a20;font-weight:600;margin-top:4px}
		.fds-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:16px;margin-bottom:16px}
		.fds-box{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px 18px}
		.fds-box h3{margin:0 0 12px;font-size:14px;display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap}
		.fds-empty{color:#646970;margin:0}
		.fds-table{width:100%;border-collapse:collapse}
		.fds-table td{padding:6px 0;border-bottom:1px solid #f0f0f1;vertical-align:middle}
		.fds-table tr:last-child td{border-bottom:0}
		.fds-table .num{text-align:right;width:52px;font-variant-numeric:tabular-nums;font-weight:600}
		.fds-table .muted{color:#8c8f94;font-weight:400;width:44px}
		.fds-bar{display:block;height:4px;background:#f0f0f1;border-radius:4px;margin-top:5px;max-width:280px}
		.fds-bar i{display:block;height:100%;background:#2271b1;border-radius:4px}
		.fds-flag{font-size:16px}
		.fds-funnel{display:grid;gap:10px}
		.fds-step{display:grid;grid-template-columns:200px 1fr 90px;gap:12px;align-items:center}
		.fds-step .track{height:22px;background:#f0f0f1;border-radius:4px;overflow:hidden}
		.fds-step .track i{display:block;height:100%;background:linear-gradient(90deg,#2271b1,#00a0d2)}
		.fds-step .val{text-align:right;font-weight:600;font-variant-numeric:tabular-nums}
		.fds-step .val small{color:#8c8f94;font-weight:400}
		.fds-chart{width:100%;height:150px;display:block}
		.fds-chart .v{fill:#c5d9ed}
		.fds-chart .l{fill:#2271b1}
		.fds-chart g:hover .v{fill:#9ec2e6}
		.fds-axis{display:flex;justify-content:space-between;color:#8c8f94;font-size:12px;margin-top:4px}
		.fds-legend{font-weight:400;color:#646970;font-size:12px}
		.fds-legend i{display:inline-block;width:10px;height:10px;border-radius:2px;margin:0 4px 0 8px;vertical-align:-1px}
		.fds-legend i.v{background:#c5d9ed}
		.fds-legend i.l{background:#2271b1}
		.fds-hours{display:grid;grid-template-columns:repeat(24,1fr);gap:3px;align-items:end;height:110px}
		.fds-hours span{display:flex;flex-direction:column;justify-content:flex-end;height:100%;text-align:center}
		.fds-hours i{display:block;background:#2271b1;border-radius:2px 2px 0 0;min-height:2px}
		.fds-hours small{color:#8c8f94;font-size:10px;height:14px;line-height:14px}
		.fds-recent td,.fds-recent th{vertical-align:top}
		.fds-tag{display:inline-block;background:#f0f6fc;color:#1d4f7a;border-radius:3px;padding:1px 6px;margin:0 3px 3px 0;font-size:12px}
		.fds-tag.act{background:#edfaef;color:#1a5e2a}
		.fds-tag.hot{background:#fcf0e3;color:#8a4b00}
		.fds-note{color:#646970;font-size:12px;margin-top:6px}
		.fds-code{display:flex;gap:8px;align-items:center;max-width:760px}
		.fds-code input{flex:1;font-family:monospace}
		@media (max-width:782px){.fds-step{grid-template-columns:1fr 70px}.fds-step .track{grid-column:1/-1;order:3}}
	</style>

	<div class="wrap fds-wrap">
		<h1><?php esc_html_e( 'Demo statistikası', 'fixit' ); ?></h1>

		<ul class="subsubsub" style="float:none;margin:6px 0 0">
			<?php
			$links = array();
			foreach ( fixit_demo_stats_products() as $key => $item ) {
				$links[] = sprintf(
					'<li><a href="%s"%s>%s</a></li>',
					esc_url( add_query_arg( array( 'product' => $key, 'tab' => $tab ), $root ) ),
					$key === $product ? ' class="current" aria-current="page"' : '',
					esc_html( $item['label'] )
				);
			}
			echo implode( ' | ', $links ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- yuxarıda escape olunub
			?>
		</ul>

		<p class="description">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: demonun ünvanı */
					__( '%s ünvanına gələnlər: haradan gəldilər, hansı rolu seçdilər, nəyə baxdılar və nə etdilər. IP ünvanları saxlanılmır.', 'fixit' ),
					$info['host']
				)
			);
			?>
		</p>

		<nav class="nav-tab-wrapper" style="margin-top:14px">
			<a class="nav-tab <?php echo 'stats' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( $base ); ?>"><?php esc_html_e( 'Statistika', 'fixit' ); ?></a>
			<a class="nav-tab <?php echo 'settings' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'tab', 'settings', $base ) ); ?>"><?php esc_html_e( 'Ayarlar', 'fixit' ); ?></a>
		</nav>

		<?php
		if ( 'settings' === $tab ) {
			fixit_demo_stats_settings( $notice, $product );
		} else {
			fixit_demo_stats_report( $data, $days, $base, $product );
		}
		?>
	</div>
	<?php
}

/**
 * «Ayarlar» bölməsi: açar və (lazım olsa) ünvan.
 */
function fixit_demo_stats_settings( $notice, $product = 'ticket' ) {
	$token = fixit_demo_stats_token( $product );
	$url   = fixit_demo_stats_url( $product );
	?>
	<?php if ( 'generated' === $notice ) : ?>
		<div class="notice notice-success"><p><?php esc_html_e( 'Yeni açar yaradıldı. İndi onu demo serverinə yazın (aşağıdakı əmr).', 'fixit' ); ?></p></div>
	<?php elseif ( 'saved' === $notice ) : ?>
		<div class="notice notice-success"><p><?php esc_html_e( 'Yadda saxlanıldı.', 'fixit' ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="fixit_demo_stats_save">
		<input type="hidden" name="fixit_product" value="<?php echo esc_attr( $product ); ?>">
		<?php wp_nonce_field( 'fixit_demo_stats_save' ); ?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="fixit_demo_stats_token"><?php esc_html_e( 'Açar', 'fixit' ); ?></label></th>
				<td>
					<div class="fds-code">
						<input type="text" id="fixit_demo_stats_token" name="fixit_demo_stats_token" value="<?php echo esc_attr( $token ); ?>" autocomplete="off" spellcheck="false">
						<button type="submit" name="fixit_generate" value="1" class="button" onclick="return !this.form.fixit_demo_stats_token.value || confirm('<?php echo esc_js( __( 'Yeni açar köhnəsini əvəz edəcək və demo serverində də dəyişmək lazım olacaq. Davam edək?', 'fixit' ) ); ?>')">
							<?php echo esc_html( $token ? __( 'Yeni açar yarat', 'fixit' ) : __( 'Açar yarat', 'fixit' ) ); ?>
						</button>
					</div>
					<p class="fds-note"><?php esc_html_e( 'Sayt bu açarla demo serverindən statistikanı oxuyur. Eyni açar demo serverinə də yazılmalıdır (aşağıdakı əmr).', 'fixit' ); ?></p>
				</td>
			</tr>
			<?php if ( $token ) : ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Demo serverində (bir dəfə)', 'fixit' ); ?></th>
					<td>
						<?php
						if ( 'remote' === $product ) {
							// Xidmətin əlavə ayar faylı: quraşdırma skripti əsas faylı yenidən yazsa da qalır
							$dir = '/etc/systemd/system/fixitremote-webdemo.service.d';
							$cmd = "mkdir -p $dir && printf '[Service]\\nEnvironment=DemoStatsToken=" . $token . "\\n' > $dir/stats.conf && chmod 600 $dir/stats.conf && systemctl daemon-reload && systemctl restart fixitremote-webdemo";
							$tip = __( 'SSH ilə serverə root kimi girib bu əmri işlədin. Açarı dəyişəndə də eyni əmr kifayətdir: köhnə açarın yerinə yazır.', 'fixit' );
						} else {
							$cmd = "cd /root/fixit-ticket-system && echo 'DEMO_STATS_TOKEN=" . $token . "' >> .env && docker compose -f docker-compose.yml -f docker-compose.prod.yml -f docker-compose.demo.yml up -d app";
							$tip = __( 'SSH ilə serverə girib bu əmri işlədin. Açar əvvəl yazılıbsa, .env-dəki köhnə DEMO_STATS_TOKEN sətrini silin.', 'fixit' );
						}
						?>
						<textarea class="large-text code" rows="3" readonly onclick="this.select()"><?php echo esc_textarea( $cmd ); ?></textarea>
						<p class="fds-note"><?php echo esc_html( $tip ); ?></p>
					</td>
				</tr>
			<?php endif; ?>
			<tr>
				<th scope="row"><label for="fixit_demo_stats_url"><?php esc_html_e( 'Statistika ünvanı', 'fixit' ); ?></label></th>
				<td>
					<input type="url" class="regular-text code" id="fixit_demo_stats_url" name="fixit_demo_stats_url" value="<?php echo esc_attr( $url ); ?>">
					<p class="fds-note"><?php esc_html_e( 'Adətən dəyişmək lazım deyil. Demo başqa ünvana köçsə yazın.', 'fixit' ); ?></p>
				</td>
			</tr>
		</table>

		<?php submit_button( __( 'Yadda saxla', 'fixit' ) ); ?>
	</form>
	<?php
}

/**
 * «Statistika» bölməsi.
 *
 * @param array|WP_Error $data Serverin cavabı.
 * @param int            $days Dövr.
 * @param string         $base Səhifənin ünvanı.
 * @param string         $product ticket | remote.
 */
function fixit_demo_stats_report( $data, $days, $base, $product = 'ticket' ) {
	$remote = 'remote' === $product;
	$refresh = wp_nonce_url( add_query_arg( array( 'days' => $days, 'refresh' => 1 ), $base ), 'fixit_demo_stats_refresh' );
	?>
	<div class="fds-top">
		<?php foreach ( fixit_demo_stats_periods() as $p => $label ) : ?>
			<a class="button <?php echo $p === $days ? 'button-primary' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'days', $p, $base ) ); ?>"><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
		<span class="spacer"></span>
		<?php if ( is_array( $data ) ) : ?>
			<span class="fds-note">
				<?php
				/* translators: %s: vaxt */
				echo esc_html( sprintf( __( 'Yeniləndi: %s', 'fixit' ), wp_date( 'H:i', strtotime( $data['generated_at'] ) ) ) );
				?>
			</span>
		<?php endif; ?>
		<a class="button" href="<?php echo esc_url( $refresh ); ?>"><span class="dashicons dashicons-update" style="vertical-align:-4px"></span> <?php esc_html_e( 'Yenilə', 'fixit' ); ?></a>
	</div>

	<?php
	if ( is_wp_error( $data ) ) {
		printf(
			'<div class="notice notice-warning inline" style="margin-top:16px"><p>%s</p><p><a class="button" href="%s">%s</a></p></div>',
			esc_html( $data->get_error_message() ),
			esc_url( add_query_arg( 'tab', 'settings', $base ) ),
			esc_html__( 'Ayarlara keç', 'fixit' )
		);
		return;
	}

	$t      = $data['totals'];
	$visits = (int) $t['visits'];
	$pct    = function ( $n ) use ( $visits ) {
		return $visits ? round( 100 * $n / $visits ) . '%' : '—';
	};

	$cards = array(
		array( $visits, __( 'ziyarət', 'fixit' ), '' ),
		array( $t['visitors'], __( 'ayrı ziyarətçi', 'fixit' ), $t['returning'] ? sprintf( /* translators: %d: say */ __( '%d təkrar gəlib', 'fixit' ), $t['returning'] ) : '' ),
		array( $t['logins'], __( 'rol seçib içəri girdi', 'fixit' ), $pct( $t['logins'] ) ),
		array( $t['active'], $remote ? __( 'iş gördü (qoşuldu, alətlər...)', 'fixit' ) : __( 'iş gördü (sorğu, tapşırıq...)', 'fixit' ), $pct( $t['active'] ) ),
		array( $t['contact_visits'], __( '«Tam versiya»ya keçdi', 'fixit' ), $pct( $t['contact_visits'] ) ),
		// Median: açıq unudulmuş bir tab ortanı saatlarla şişirdərdi
		array( number_format_i18n( (float) $t['median_minutes'], 1 ) . ' ' . __( 'dəq', 'fixit' ), __( 'tipik müddət (içəri girənlər)', 'fixit' ), '' ),
	);
	?>
	<div class="fds-cards">
		<?php foreach ( $cards as $card ) : ?>
			<div class="fds-card">
				<b><?php echo esc_html( is_numeric( $card[0] ) ? number_format_i18n( (int) $card[0] ) : $card[0] ); ?></b>
				<span><?php echo esc_html( $card[1] ); ?></span>
				<?php if ( $card[2] ) : ?>
					<small><?php echo esc_html( $card[2] ); ?></small>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( ! $visits ) : ?>
		<div class="notice notice-info inline"><p><?php esc_html_e( 'Bu dövrdə hələ ziyarət yoxdur. Demo açılan kimi burada görünəcək (keş 5 dəqiqədir — «Yenilə» ilə dərhal).', 'fixit' ); ?></p></div>
		<?php
		return;
	endif;

	$funnel = array(
		'opened'  => __( 'Demonu açdı', 'fixit' ),
		'login'   => __( 'Rol seçib girdi', 'fixit' ),
		'active'  => __( 'İş gördü', 'fixit' ),
		'contact' => __( '«Tam versiya»ya keçdi', 'fixit' ),
	);
	?>
	<div class="fds-grid">
		<div class="fds-box">
			<h3><?php esc_html_e( 'Huni: açılışdan maraqa qədər', 'fixit' ); ?></h3>
			<div class="fds-funnel">
				<?php foreach ( $data['funnel'] as $step ) : ?>
					<div class="fds-step">
						<span><?php echo esc_html( isset( $funnel[ $step['key'] ] ) ? $funnel[ $step['key'] ] : $step['key'] ); ?></span>
						<span class="track"><i style="width:<?php echo esc_attr( $visits ? round( 100 * $step['count'] / $visits ) : 0 ); ?>%"></i></span>
						<span class="val"><?php echo esc_html( number_format_i18n( (int) $step['count'] ) ); ?> <small><?php echo esc_html( $pct( $step['count'] ) ); ?></small></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php fixit_demo_stats_chart( $data['daily'] ); ?>
	</div>

	<div class="fds-grid">
		<?php
		fixit_demo_stats_table( __( 'Haradan gəldi (saytdakı düymə)', 'fixit' ), $data['sources'], 'source', $visits );
		fixit_demo_stats_table( __( 'Əvvəlki sayt', 'fixit' ), $data['referrers'], 'other', $visits );
		fixit_demo_stats_table( __( 'Seçilən rol', 'fixit' ), $data['roles'], 'role', $visits, __( 'Hələ heç kim rol seçməyib.', 'fixit' ) );
		fixit_demo_stats_table( __( 'Baxılan bölmələr', 'fixit' ), $data['sections'], 'section', $visits, __( 'Hələ heç kim içəri girməyib.', 'fixit' ) );
		fixit_demo_stats_table(
			__( 'Görülən işlər (cəmi)', 'fixit' ),
			$data['actions'],
			'action',
			(int) $t['actions'],
			$remote ? __( 'Hələ heç kim ekrana qoşulmayıb, alətləri də açmayıb.', 'fixit' ) : __( 'Hələ heç kim nə sorğu, nə tapşırıq yaratmayıb.', 'fixit' )
		);
		fixit_demo_stats_table( __( 'Ölkə', 'fixit' ), $data['countries'], 'country', $visits );
		fixit_demo_stats_table( __( 'Cihaz', 'fixit' ), $data['devices'], 'device', $visits );
		fixit_demo_stats_table( __( 'Brauzer', 'fixit' ), $data['browsers'], 'other', $visits );
		fixit_demo_stats_table( __( 'Əməliyyat sistemi', 'fixit' ), $data['os'], 'other', $visits );
		fixit_demo_stats_hours( $data['hours'] );
		?>
	</div>

	<h2><?php esc_html_e( 'Son ziyarətlər', 'fixit' ); ?></h2>
	<table class="widefat striped fds-recent">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Vaxt', 'fixit' ); ?></th>
				<th><?php esc_html_e( 'Haradan', 'fixit' ); ?></th>
				<th><?php esc_html_e( 'Ölkə · cihaz', 'fixit' ); ?></th>
				<th><?php esc_html_e( 'Rol', 'fixit' ); ?></th>
				<th><?php esc_html_e( 'Baxdığı bölmələr', 'fixit' ); ?></th>
				<th><?php esc_html_e( 'Gördüyü iş', 'fixit' ); ?></th>
				<th><?php esc_html_e( 'Müddət', 'fixit' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $data['recent'] as $v ) : ?>
				<tr>
					<td>
						<?php echo esc_html( wp_date( 'j M, H:i', strtotime( $v['started_at'] ) ) ); ?>
						<?php if ( $v['returning'] ) : ?>
							<br><span class="fds-tag" title="<?php esc_attr_e( 'Bu ziyarətçi əvvəl də gəlib', 'fixit' ); ?>"><?php esc_html_e( 'təkrar', 'fixit' ); ?> · <?php echo esc_html( $v['visitor'] ); ?></span>
						<?php endif; ?>
					</td>
					<td>
						<?php echo esc_html( fixit_demo_stats_label( 'source', $v['source'] ? $v['source'] : '(birbaşa)' ) ); ?>
						<?php if ( $v['referrer'] ) : ?>
							<br><span class="fds-note"><?php echo esc_html( $v['referrer'] ); ?></span>
						<?php endif; ?>
					</td>
					<td>
						<?php if ( $v['country'] ) : ?>
							<span class="fds-flag"><?php echo fixit_demo_stats_flag( $v['country'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rəqəmli entity ?></span>
							<?php echo esc_html( fixit_demo_stats_label( 'country', $v['country'] ) ); ?><br>
						<?php endif; ?>
						<span class="fds-note"><?php echo esc_html( fixit_demo_stats_label( 'device', $v['device'] ) . ( $v['browser'] ? ' · ' . $v['browser'] : '' ) ); ?></span>
					</td>
					<td>
						<?php
						if ( $v['roles'] ) {
							foreach ( $v['roles'] as $role ) {
								echo '<span class="fds-tag">' . esc_html( fixit_demo_stats_label( 'role', $role ) ) . '</span>';
							}
						} else {
							echo '<span class="fds-note">' . esc_html__( 'girmədi', 'fixit' ) . '</span>';
						}
						?>
					</td>
					<td>
						<?php
						foreach ( $v['sections'] as $section ) {
							echo '<span class="fds-tag">' . esc_html( fixit_demo_stats_label( 'section', $section ) ) . '</span>';
						}
						if ( ! $v['sections'] ) {
							echo '<span class="fds-note">—</span>';
						}
						?>
					</td>
					<td>
						<?php
						foreach ( $v['actions'] as $action => $count ) {
							echo '<span class="fds-tag act">' . esc_html( fixit_demo_stats_label( 'action', $action ) . ( $count > 1 ? ' ×' . $count : '' ) ) . '</span>';
						}
						if ( $v['contact_clicks'] ) {
							echo '<span class="fds-tag hot">' . esc_html__( '«Tam versiya»', 'fixit' ) . '</span>';
						}
						if ( ! $v['actions'] && ! $v['contact_clicks'] ) {
							echo '<span class="fds-note">—</span>';
						}
						?>
					</td>
					<td>
						<?php
						echo esc_html(
							$v['minutes'] >= 1
								? sprintf( /* translators: %s: dəqiqə */ __( '%s dəq', 'fixit' ), number_format_i18n( $v['minutes'], $v['minutes'] < 10 ? 1 : 0 ) )
								: __( '< 1 dəq', 'fixit' )
						);
						?>
						<br><span class="fds-note"><?php echo esc_html( sprintf( /* translators: %d: səhifə sayı */ _n( '%d səhifə', '%d səhifə', (int) $v['page_views'], 'fixit' ), (int) $v['page_views'] ) ); ?></span>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="fds-note">
		<?php
		/* translators: %d: say */
		echo esc_html( sprintf( __( 'Son %d ziyarət göstərilir. «Təkrar» — eyni brauzer və şəbəkədən əvvəl də gəlib (IP yox, geri açılmayan kod ilə tanınır).', 'fixit' ), count( $data['recent'] ) ) );
		?>
	</p>
	<?php
}
