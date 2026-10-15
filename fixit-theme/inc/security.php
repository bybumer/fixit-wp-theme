<?php
/**
 * Təhlükəsizlik.
 *
 * - İstifadəçi adları (giriş adı, e-poçt) kənardan görünmür
 * - Səhv giriş cəhdləri IP üzrə məhdudlaşdırılır
 * - XML-RPC söndürülür
 * - Brauzer üçün təhlükəsizlik başlıqları
 *
 * Plagin tələb etmir; hostinqdən və CDN-dən asılı deyil.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   1. Ziyarətçinin IP ünvanı
   ============================================================ */

/**
 * Cloudflare-in ünvan blokları.
 *
 * Sayt Cloudflare arxasında olanda REMOTE_ADDR Cloudflare serverinin
 * ünvanıdır, ziyarətçinin real ünvanı isə CF-Connecting-IP başlığındadır.
 * Bu başlığa yalnız sorğu həqiqətən Cloudflare-dən gəlibsə inanırıq —
 * əks halda hər kəs başlığı özü yazıb məhdudiyyəti keçə bilərdi.
 */
function fixit_cloudflare_ranges() {
	return array(
		'173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
		'141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
		'197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
		'104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
		'2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
		'2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
	);
}

/**
 * IP ünvan verilmiş bloka daxildirmi (IPv4 və IPv6).
 *
 * @param string $ip   Ünvan.
 * @param string $cidr Blok, məs. 104.16.0.0/13.
 */
function fixit_ip_in_cidr( $ip, $cidr ) {
	list( $subnet, $bits ) = explode( '/', $cidr );

	$ip_bin  = @inet_pton( $ip ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	$net_bin = @inet_pton( $subnet ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	if ( false === $ip_bin || false === $net_bin || strlen( $ip_bin ) !== strlen( $net_bin ) ) {
		return false;
	}

	$bits  = (int) $bits;
	$bytes = intdiv( $bits, 8 );
	$rest  = $bits % 8;

	if ( substr( $ip_bin, 0, $bytes ) !== substr( $net_bin, 0, $bytes ) ) {
		return false;
	}
	if ( 0 === $rest ) {
		return true;
	}

	$mask = ( 0xFF << ( 8 - $rest ) ) & 0xFF;

	return ( ord( $ip_bin[ $bytes ] ) & $mask ) === ( ord( $net_bin[ $bytes ] ) & $mask );
}

/**
 * Ziyarətçinin real IP ünvanı.
 */
function fixit_client_ip() {
	$remote = isset( $_SERVER['REMOTE_ADDR'] ) ? trim( (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

	if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) && $remote ) {
		$cf = trim( (string) wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );

		if ( filter_var( $cf, FILTER_VALIDATE_IP ) ) {
			foreach ( fixit_cloudflare_ranges() as $range ) {
				if ( fixit_ip_in_cidr( $remote, $range ) ) {
					return $cf;
				}
			}
		}
	}

	return filter_var( $remote, FILTER_VALIDATE_IP ) ? $remote : '0.0.0.0';
}

/* ============================================================
   2. İstifadəçi adları kənardan görünməsin
   ============================================================
   WordPress standart olaraq istifadəçi adını bir neçə yerdə açır:
   /wp-json/wp/v2/users, /?author=1 → /author/<ad>/, sitemap, RSS.
   Giriş adı bilinəndə parol təxmini xeyli asanlaşır. */

/**
 * /wp-json/wp/v2/users — yalnız daxil olmuş istifadəçilər üçün.
 *
 * Admin paneldəki redaktor bu siyahıdan istifadə edir, ona təsiri yoxdur.
 *
 * @param mixed           $result  Cavab.
 * @param WP_REST_Server  $server  Server.
 * @param WP_REST_Request $request Sorğu.
 */
function fixit_block_rest_users( $result, $server, $request ) {
	if ( is_user_logged_in() ) {
		return $result;
	}

	if ( preg_match( '#^/wp/v2/users(/|$)#', $request->get_route() ) ) {
		return new WP_Error(
			'rest_user_cannot_view',
			__( 'Bu məlumata baxmaq üçün icazə yoxdur.', 'fixit' ),
			array( 'status' => 401 )
		);
	}

	return $result;
}
add_filter( 'rest_pre_dispatch', 'fixit_block_rest_users', 10, 3 );

/**
 * Müəllif səhifələri (/?author=1, /author/<ad>/) — 404.
 *
 * `request` mərhələsində kəsilir: WordPress-in özünün /?author=1 → /author/<ad>/
 * yönləndirməsi (ad məhz orada açılırdı) heç işə düşmür.
 *
 * @param array $vars Sorğu dəyişənləri.
 */
function fixit_block_author_archives( $vars ) {
	if ( is_admin() ) {
		return $vars;
	}

	if ( isset( $vars['author'] ) || isset( $vars['author_name'] ) ) {
		unset( $vars['author'], $vars['author_name'] );
		$vars['error'] = '404';
	}

	return $vars;
}
add_filter( 'request', 'fixit_block_author_archives' );

/**
 * Sitemap-dan istifadəçilər bölməsini çıxarır.
 *
 * @param WP_Sitemaps_Provider $provider Bölmə.
 * @param string               $name     Bölmənin adı.
 */
function fixit_sitemap_without_users( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'fixit_sitemap_without_users', 10, 2 );

/**
 * RSS-də müəllif adı əvəzinə saytın adı.
 *
 * @param string $name Müəllifin adı.
 */
function fixit_feed_author( $name ) {
	return is_feed() ? get_bloginfo( 'name' ) : $name;
}
add_filter( 'the_author', 'fixit_feed_author' );

/**
 * oEmbed cavabında müəllif əvəzinə sayt.
 *
 * @param array $data Cavab.
 */
function fixit_oembed_author( $data ) {
	if ( isset( $data['author_name'] ) ) {
		$data['author_name'] = get_bloginfo( 'name' );
		$data['author_url']  = home_url( '/' );
	}
	return $data;
}
add_filter( 'oembed_response_data', 'fixit_oembed_author' );

/* ============================================================
   3. Səhv giriş cəhdlərinin məhdudlaşdırılması
   ============================================================
   15 dəqiqə ərzində 5 səhv cəhd → həmin IP 30 dəqiqə giriş edə bilmir.
   Hesab bloklanmır: əks halda kənar biri qəsdən səhv parol yazaraq
   adminin özünü sistemdən kənarda saxlaya bilərdi. */

define( 'FIXIT_LOGIN_MAX_FAILS', 5 );
define( 'FIXIT_LOGIN_WINDOW', 15 * MINUTE_IN_SECONDS );
define( 'FIXIT_LOGIN_LOCK', 30 * MINUTE_IN_SECONDS );

/**
 * Bu IP üçün açar.
 *
 * @param string $kind 'fails' və ya 'lock'.
 */
function fixit_login_key( $kind ) {
	return 'fixit_login_' . $kind . '_' . md5( fixit_client_ip() );
}

/**
 * IP bloklanıbsa, parol düz olsa belə giriş verilmir.
 *
 * WordPress-in öz yoxlamasından (prioritet 20) sonra işləyir.
 *
 * @param WP_User|WP_Error|null $user İstifadəçi.
 */
function fixit_login_enforce_lock( $user ) {
	if ( get_transient( fixit_login_key( 'lock' ) ) ) {
		return new WP_Error(
			'fixit_login_locked',
			sprintf(
				/* translators: %d: dəqiqə */
				__( 'Çox sayda səhv cəhd edildi. %d dəqiqə sonra yenidən yoxlayın.', 'fixit' ),
				(int) ceil( FIXIT_LOGIN_LOCK / MINUTE_IN_SECONDS )
			)
		);
	}

	return $user;
}
add_filter( 'authenticate', 'fixit_login_enforce_lock', 30 );

/**
 * Səhv cəhdi sayır.
 */
function fixit_login_failed() {
	$fails = (int) get_transient( fixit_login_key( 'fails' ) ) + 1;

	if ( $fails >= FIXIT_LOGIN_MAX_FAILS ) {
		set_transient( fixit_login_key( 'lock' ), 1, FIXIT_LOGIN_LOCK );
		delete_transient( fixit_login_key( 'fails' ) );
		return;
	}

	set_transient( fixit_login_key( 'fails' ), $fails, FIXIT_LOGIN_WINDOW );
}
add_action( 'wp_login_failed', 'fixit_login_failed' );

/**
 * Uğurlu girişdən sonra sayğac sıfırlanır.
 */
function fixit_login_succeeded() {
	delete_transient( fixit_login_key( 'fails' ) );
}
add_action( 'wp_login', 'fixit_login_succeeded' );

/**
 * Giriş xətası istifadəçinin mövcud olub-olmadığını açmasın.
 *
 * WordPress standart olaraq "bu adda istifadəçi yoxdur" və "X istifadəçisi
 * üçün parol səhvdir" deyə fərqli cavab verir.
 *
 * @param WP_Error $errors Xətalar.
 */
function fixit_login_generic_errors( $errors ) {
	if ( ! is_wp_error( $errors ) ) {
		return $errors;
	}

	$codes = array( 'invalid_username', 'invalid_email', 'incorrect_password' );

	if ( array_intersect( $codes, $errors->get_error_codes() ) ) {
		foreach ( $codes as $code ) {
			$errors->remove( $code );
		}
		$errors->add( 'fixit_invalid_login', __( 'İstifadəçi adı və ya parol səhvdir.', 'fixit' ) );
	}

	return $errors;
}
add_filter( 'wp_login_errors', 'fixit_login_generic_errors' );

/* ============================================================
   4. XML-RPC
   ============================================================
   Köhnə uzaqdan idarə protokolu. Sayt onu işlətmir, amma bir sorğuda
   yüzlərlə parol yoxlamağa imkan verdiyi üçün parol təxmininin əsas
   yoludur. */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', '__return_empty_array' );
remove_action( 'wp_head', 'rsd_link' );

/**
 * xmlrpc.php sorğusu ümumiyyətlə emal olunmur — sorğunun məzmunu oxunmadan rədd edilir.
 */
function fixit_block_xmlrpc() {
	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
		status_header( 403 );
		nocache_headers();
		header( 'Content-Type: text/plain; charset=UTF-8' );
		echo 'XML-RPC is disabled.';
		exit;
	}
}
add_action( 'init', 'fixit_block_xmlrpc', 0 );

/**
 * X-Pingback başlığını çıxarır.
 *
 * @param array $headers Başlıqlar.
 */
function fixit_remove_pingback_header( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
}
add_filter( 'wp_headers', 'fixit_remove_pingback_header' );

/* ============================================================
   5. Versiya məlumatı
   ============================================================ */
add_filter( 'the_generator', '__return_empty_string' );

/* ============================================================
   6. Brauzer üçün təhlükəsizlik başlıqları
   ============================================================ */

/**
 * Ön üz və giriş səhifəsi üçün başlıqlar.
 */
function fixit_security_headers() {
	if ( headers_sent() ) {
		return;
	}

	header_remove( 'X-Powered-By' ); // PHP versiyası açılmasın.

	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' ); // Sayt başqa saytın çərçivəsində açılmasın.
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), payment=(), usb=()' );

	if ( is_ssl() || 'https' === wp_parse_url( home_url(), PHP_URL_SCHEME ) ) {
		header( 'Strict-Transport-Security: max-age=31536000' );
	}
}
add_action( 'send_headers', 'fixit_security_headers' );
add_action( 'login_init', 'fixit_security_headers' );

/**
 * Admin panel və REST: yalnız PHP versiyasını gizlədirik
 * (qalan başlıqları WordPress özü göndərir).
 */
function fixit_hide_php_version() {
	if ( ! headers_sent() ) {
		header_remove( 'X-Powered-By' );
	}
}
add_action( 'admin_init', 'fixit_hide_php_version' );

/**
 * REST cavabları.
 *
 * @param bool $served Cavab artıq göndərilibmi.
 */
function fixit_rest_hide_php_version( $served ) {
	fixit_hide_php_version();
	return $served;
}
add_filter( 'rest_pre_serve_request', 'fixit_rest_hide_php_version' );

/**
 * Səhifələrin brauzer keşi.
 *
 * Hostinq səhifəni brauzerdə 7 gün saxlamağı əmr edirdi — sayt yenilənəndə
 * ziyarətçi bir həftə köhnə səhifəni görürdü. 5 dəqiqə kifayətdir: serverdəki
 * səhifə keşi (LiteSpeed) bundan asılı deyil və sürət dəyişmir.
 *
 * WordPress özü "keşləmə" başlığı göndəribsə (404, daxil olmuş istifadəçi və s.),
 * ona toxunulmur.
 */
function fixit_browser_cache_headers() {
	if ( headers_sent() || is_user_logged_in() ) {
		return;
	}

	foreach ( headers_list() as $line ) {
		if ( 0 === stripos( $line, 'Cache-Control:' ) ) {
			return;
		}
	}

	header( 'Cache-Control: public, max-age=300' );
	header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 300 ) . ' GMT' );
}
add_action( 'send_headers', 'fixit_browser_cache_headers', 20 );
