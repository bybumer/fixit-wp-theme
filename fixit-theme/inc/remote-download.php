<?php
/**
 * Fixit Remote-un saytdan yüklənməsi.
 *
 * Saytdakı bütün "Yüklə" düymələri bura gəlir: fixit.az/yukle (və /download).
 * Ünvan qısadır ki, dəstək telefonda "fixit.az/yukle yazın" deyə bilsin.
 * Sorğu ən son quraşdırmadan işləyən EXE-yə yönləndirilir (FIXIT_REMOTE_WINDOWS).
 *
 * Yükləmələr gün və mənbə üzrə sayılır, admin paneldə «Fixit Remote»
 * səhifəsində görünür (inc/remote-admin.php). IP və brauzer saxlanılmır, yalnız say.
 *
 * Yalnız 404 olanda işləyir: eyni adda səhifə yaradılsa, o üstündür.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FIXIT_REMOTE_REPO', 'bybumer/fixit-remote-releases' );

/* ============================================================
   1. Ən son versiya
   ============================================================
   Yükləmə ünvanı (releases/latest) onsuz da həmişə ən son faylı verir.
   Burada yalnız "hansı versiyadır" məlumatı alınır: saytdakı yazı və
   admin paneli üçün. 10 dəqiqədən bir yoxlanılır. GitHub cavab verməsə,
   sonuncu bilinən məlumat qalır — yazı boşalmır. */

/**
 * Ən son buraxılış: versiya, həcm, tarix, fayllar.
 *
 * @param bool $force Keşə baxmadan yoxla.
 * @return array{version:string,size:int,published:string,url:string,page:string,assets:array}
 */
function fixit_remote_release( $force = false ) {
	$cached = $force ? false : get_transient( 'fixit_remote_release' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$last = get_option( 'fixit_remote_release_last', array() );
	$last = is_array( $last ) ? $last : array();

	$info  = fixit_remote_release_from_api();
	$fresh = (bool) $info;

	if ( ! $info ) {
		$info  = fixit_remote_release_from_redirect( $last );
		$fresh = (bool) $info;
	}
	if ( ! $info ) {
		$info = $last;
	}

	$info = wp_parse_args(
		$info,
		array(
			'version'   => '',
			'size'      => 0,
			'published' => '',
			'url'       => '',
			'page'      => '',
			'assets'    => array(),
		)
	);

	if ( $info['version'] && $info !== $last ) {
		update_option( 'fixit_remote_release_last', $info, false );
	}

	set_transient( 'fixit_remote_release', $info, $fresh ? 10 * MINUTE_IN_SECONDS : 5 * MINUTE_IN_SECONDS );

	return $info;
}

/**
 * GitHub API-dən son buraxılış.
 *
 * @return array|null
 */
function fixit_remote_release_from_api() {
	$response = wp_remote_get(
		'https://api.github.com/repos/' . FIXIT_REMOTE_REPO . '/releases/latest',
		array(
			'timeout' => 8,
			'headers' => array( 'Accept' => 'application/vnd.github+json' ),
		)
	);

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return null;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $data['tag_name'] ) ) {
		return null;
	}

	$info = array(
		'version'   => ltrim( (string) $data['tag_name'], 'v' ),
		'size'      => 0,
		'published' => isset( $data['published_at'] ) ? (string) $data['published_at'] : '',
		'url'       => FIXIT_REMOTE_WINDOWS,
		'page'      => isset( $data['html_url'] ) ? esc_url_raw( $data['html_url'] ) : '',
		'assets'    => array(),
	);

	foreach ( (array) ( isset( $data['assets'] ) ? $data['assets'] : array() ) as $asset ) {
		$name = isset( $asset['name'] ) ? (string) $asset['name'] : '';

		if ( 'FixitRemote.exe' === $name ) {
			$info['size'] = (int) $asset['size'];
			$info['url']  = esc_url_raw( $asset['browser_download_url'] );
		}

		// İmza faylları və saytın demo paketi admin siyahısında lazım deyil.
		if ( '' === $name || preg_match( '/\.sig$|webdemo/i', $name ) ) {
			continue;
		}

		$info['assets'][] = array(
			'name'  => $name,
			'size'  => (int) $asset['size'],
			'count' => (int) $asset['download_count'],
			'url'   => esc_url_raw( $asset['browser_download_url'] ),
		);
	}

	return $info;
}

/**
 * Ehtiyat yol: API limiti dolanda versiya yükləmə ünvanının
 * yönləndirməsindən oxunur (…/download/v1.16.95/FixitRemote.exe).
 *
 * @param array $last Sonuncu bilinən məlumat.
 * @return array|null
 */
function fixit_remote_release_from_redirect( $last ) {
	$response = wp_remote_head(
		FIXIT_REMOTE_WINDOWS,
		array(
			'timeout'     => 8,
			'redirection' => 0,
		)
	);

	if ( is_wp_error( $response ) ) {
		return null;
	}

	$location = (string) wp_remote_retrieve_header( $response, 'location' );
	if ( ! preg_match( '#/releases/download/v?([^/]+)/FixitRemote\.exe#', $location, $m ) ) {
		return null;
	}

	$same = ! empty( $last['version'] ) && $last['version'] === $m[1];

	return array(
		'version'   => $m[1],
		'size'      => $same ? (int) $last['size'] : 0,
		'published' => $same ? $last['published'] : '',
		'url'       => esc_url_raw( $location ),
		'page'      => 'https://github.com/' . FIXIT_REMOTE_REPO . '/releases/tag/v' . rawurlencode( $m[1] ),
		'assets'    => $same && isset( $last['assets'] ) ? $last['assets'] : array(),
	);
}

/**
 * /wp-json/fixit/v1/remote-release — saytdakı versiya yazısı üçün.
 *
 * Səhifə keşdə günlərlə qala bilər; yazı brauzerdə bu cavabla təzələnir.
 */
function fixit_remote_register_rest() {
	register_rest_route(
		'fixit/v1',
		'/remote-release',
		array(
			'methods'             => 'GET',
			'permission_callback' => '__return_true',
			'callback'            => function () {
				$info     = fixit_remote_release();
				$response = rest_ensure_response(
					array(
						'version' => $info['version'],
						'size'    => (int) $info['size'],
					)
				);
				$response->header( 'Cache-Control', 'no-store, max-age=0' );
				return $response;
			},
		)
	);
}
add_action( 'rest_api_init', 'fixit_remote_register_rest' );

/* ============================================================
   2. fixit.az/yukle və sayğac
   ============================================================ */

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

/**
 * Qısa xülasə: bu gün, 7 gün, 30 gün, bütün dövr.
 *
 * @return array{today:int,d7:int,d30:int,all:int,since:string}
 */
function fixit_remote_download_totals() {
	$data = get_option( 'fixit_remote_downloads', array() );
	$all  = isset( $data['days'] ) && is_array( $data['days'] ) ? $data['days'] : array();

	$out = array(
		'today' => 0,
		'd7'    => 0,
		'd30'   => 0,
		'all'   => (int) array_sum( $all ),
		'since' => $all ? min( array_keys( $all ) ) : '',
	);

	$today = wp_date( 'Y-m-d' );
	$d7    = wp_date( 'Y-m-d', time() - 6 * DAY_IN_SECONDS );
	$d30   = wp_date( 'Y-m-d', time() - 29 * DAY_IN_SECONDS );

	foreach ( $all as $day => $n ) {
		if ( $day === $today ) {
			$out['today'] += (int) $n;
		}
		if ( $day >= $d7 ) {
			$out['d7'] += (int) $n;
		}
		if ( $day >= $d30 ) {
			$out['d30'] += (int) $n;
		}
	}

	return $out;
}
