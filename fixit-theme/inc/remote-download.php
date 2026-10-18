<?php
/**
 * Fixit Remote-un saytdan yüklənməsi.
 *
 * Saytdakı bütün "Yüklə" düymələri bura gəlir: fixit.az/yukle (və /download).
 * Ünvan qısadır ki, dəstək telefonda "fixit.az/yukle yazın" deyə bilsin.
 * Sorğu ən son quraşdırmadan işləyən EXE-yə yönləndirilir (FIXIT_REMOTE_WINDOWS).
 *
 * Yükləmələr gün və mənbə üzrə sayılır, «Demo statistikası → Fixit Remote»
 * səhifəsində görünür. IP və brauzer saxlanılmır, yalnız say.
 *
 * Yalnız 404 olanda işləyir: eyni adda səhifə yaradılsa, o üstündür.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yükləmə düyməsinin ünvanı.
 *
 * @param string $src Düymə harada durur (statistika üçün): home, home-slayd, uzaqdan...
 */
function fixit_remote_download_link( $src = '' ) {
	$url = home_url( '/yukle/' );
	return $src ? add_query_arg( 'src', rawurlencode( $src ), $url ) : $url;
}

/**
 * Ünvan fixit.az/yukle (dil önşəkilçisi ilə də) olanda yükləməyə yönləndirir.
 */
function fixit_remote_download_route() {
	if ( ! is_404() || ! FIXIT_REMOTE_WINDOWS_READY ) {
		return;
	}

	global $wp;
	$path = strtolower( trim( (string) $wp->request, '/' ) );
	$path = preg_replace( '#^(en|ru)/#', '', $path );

	if ( ! in_array( $path, array( 'yukle', 'download' ), true ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- yalnız sayğac
	$src = isset( $_GET['src'] ) ? sanitize_key( wp_unslash( $_GET['src'] ) ) : '';
	fixit_remote_count_download( $src );

	nocache_headers();
	wp_redirect( FIXIT_REMOTE_WINDOWS, 302, 'FIXIT' ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- sabit GitHub ünvanı
	exit;
}
add_action( 'template_redirect', 'fixit_remote_download_route', 1 );

/**
 * Yükləməni sayır. Botlar (axtarış sistemləri, link önizləməsi) sayılmır.
 *
 * @param string $src Mənbə.
 */
function fixit_remote_count_download( $src ) {
	$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- yalnız yoxlanılır
	if ( '' === $agent || preg_match( '/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|telegram|curl|wget|python/i', $agent ) ) {
		return;
	}

	$data = get_option( 'fixit_remote_downloads', array() );
	if ( ! is_array( $data ) ) {
		$data = array();
	}

	$day     = wp_date( 'Y-m-d' );
	$days    = isset( $data['days'] ) && is_array( $data['days'] ) ? $data['days'] : array();
	$sources = isset( $data['sources'] ) && is_array( $data['sources'] ) ? $data['sources'] : array();

	$days[ $day ] = ( isset( $days[ $day ] ) ? (int) $days[ $day ] : 0 ) + 1;

	// Mənbə gün üzrə də saxlanılır: dövr seçiləndə düzgün sayılsın.
	$key = substr( $src ? $src : '(birbaşa)', 0, 40 );
	if ( ! isset( $sources[ $day ] ) || ! is_array( $sources[ $day ] ) ) {
		$sources[ $day ] = array();
	}
	$sources[ $day ][ $key ] = ( isset( $sources[ $day ][ $key ] ) ? (int) $sources[ $day ][ $key ] : 0 ) + 1;

	// Bir ildən köhnəsi silinir.
	$cutoff = wp_date( 'Y-m-d', time() - 400 * DAY_IN_SECONDS );
	foreach ( array_keys( $days ) as $d ) {
		if ( $d < $cutoff ) {
			unset( $days[ $d ], $sources[ $d ] );
		}
	}

	update_option(
		'fixit_remote_downloads',
		array(
			'days'    => $days,
			'sources' => $sources,
		),
		false
	);
}

/**
 * Son `$days` günün yükləmələri: cəmi, gün və mənbə üzrə.
 *
 * @param int $days Dövr.
 * @return array{total:int, daily:array<string,int>, sources:array<string,int>}
 */
function fixit_remote_download_stats( $days ) {
	$data    = get_option( 'fixit_remote_downloads', array() );
	$all     = isset( $data['days'] ) && is_array( $data['days'] ) ? $data['days'] : array();
	$by_src  = isset( $data['sources'] ) && is_array( $data['sources'] ) ? $data['sources'] : array();
	$daily   = array();
	$sources = array();
	$total   = 0;

	for ( $i = $days - 1; $i >= 0; $i-- ) {
		$d           = wp_date( 'Y-m-d', time() - $i * DAY_IN_SECONDS );
		$daily[ $d ] = isset( $all[ $d ] ) ? (int) $all[ $d ] : 0;
		$total      += $daily[ $d ];

		if ( isset( $by_src[ $d ] ) && is_array( $by_src[ $d ] ) ) {
			foreach ( $by_src[ $d ] as $src => $n ) {
				$sources[ $src ] = ( isset( $sources[ $src ] ) ? $sources[ $src ] : 0 ) + (int) $n;
			}
		}
	}

	arsort( $sources );

	return array(
		'total'   => $total,
		'daily'   => $daily,
		'sources' => $sources,
	);
}
