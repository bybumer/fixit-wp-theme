<?php
/**
 * Üç dil: Azərbaycan, İngilis, Rus.
 *
 * Ünvanlar:
 *   fixit.az/...      → Azərbaycanca (əsas dil, ünvan dəyişmir)
 *   fixit.az/en/...   → İngiliscə
 *   fixit.az/ru/...   → Rusca
 *
 * Dil necə seçilir:
 *   1. Ünvandakı önşəkilçi (/en/, /ru/) — həmişə üstündür
 *   2. İstifadəçi başlıqdakı keçiddən seçibsə, seçim kukidə saxlanılır
 *   3. İlk ziyarətdə dil ölkəyə görə təklif olunur (bax: assets/js/main.js
 *      və /wp-json/fixit/v1/lang). Bu, brauzer tərəfində edilir ki, nə
 *      axtarış robotlarına, nə də səhifə keşinə zərər dəyməsin.
 *
 * Mətnlər:
 *   - Şablonlardakı mətnlər languages/fixit-en_US.mo və fixit-ru_RU.mo-da
 *   - Bazadakı məzmun (məhsul, xidmət, rəy) `_fixit_i18n_en` / `_fixit_i18n_ru`
 *     meta sahələrində; onlar da kodda saxlanılır və relizlə gəlir
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Kukinin adı — istifadəçinin seçdiyi dil. */
define( 'FIXIT_LANG_COOKIE', 'fixit_lang' );

/**
 * Dillər.
 */
function fixit_langs() {
	return array(
		'az' => array(
			'label'    => 'AZ',
			'name'     => 'Azərbaycanca',
			'locale'   => 'az',
			'hreflang' => 'az',
		),
		'en' => array(
			'label'    => 'EN',
			'name'     => 'English',
			'locale'   => 'en_US',
			'hreflang' => 'en',
		),
		'ru' => array(
			'label'    => 'RU',
			'name'     => 'Русский',
			'locale'   => 'ru_RU',
			'hreflang' => 'ru',
		),
	);
}

/**
 * Əsas dil — ünvanda önşəkilçisi olmayan dil.
 */
function fixit_default_lang() {
	return 'az';
}

/**
 * Hazırkı dil.
 */
function fixit_lang() {
	return isset( $GLOBALS['fixit_lang'] ) ? $GLOBALS['fixit_lang'] : fixit_default_lang();
}

/**
 * Bu, əsas dildirmi?
 */
function fixit_is_default_lang() {
	return fixit_default_lang() === fixit_lang();
}

/**
 * Saytın kök yolu (məs. "/" və ya alt qovluqda "/sayt/").
 */
function fixit_site_path() {
	$path = wp_parse_url( get_option( 'home' ), PHP_URL_PATH );
	return $path ? trailingslashit( $path ) : '/';
}

/* ============================================================
   1. Sorğudan dili tapmaq
   ============================================================ */

/**
 * Ünvandakı /en/ və ya /ru/ önşəkilçisini tapır və onu ünvandan çıxarır.
 *
 * Önşəkilçi çıxarıldıqdan sonra WordPress səhifəni adi qaydada tapır —
 * ayrıca yönləndirmə qaydası lazım gəlmir.
 */
function fixit_parse_request_lang() {
	if ( is_admin() ) {
		return;
	}

	$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$base = fixit_site_path();

	if ( 0 !== strpos( $path, $base ) ) {
		return;
	}

	$rest  = substr( $path, strlen( $base ) );
	$codes = array_keys( fixit_langs() );

	foreach ( $codes as $code ) {
		if ( fixit_default_lang() === $code ) {
			continue;
		}
		if ( $rest === $code || 0 === strpos( $rest, $code . '/' ) ) {
			$GLOBALS['fixit_lang']         = $code;
			$GLOBALS['fixit_original_uri'] = $uri;

			// Qalan yolu önşəkilçisiz hala gətiririk.
			$stripped = substr( $rest, strlen( $code ) );
			$stripped = ltrim( (string) $stripped, '/' );

			$query = (string) wp_parse_url( $uri, PHP_URL_QUERY );
			$_SERVER['REQUEST_URI'] = $base . $stripped . ( $query ? '?' . $query : '' );
			return;
		}
	}
}
fixit_parse_request_lang();

/* ============================================================
   2. Tərcümə faylları (WordPress locale-i)
   ============================================================ */

/**
 * Bu sorğu saytın ziyarətçiyə görünən hissəsidirmi?
 *
 * Admin panel, giriş səhifəsi, REST, AJAX və cron öz dilində qalır.
 */
function fixit_is_frontend_request() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return false;
	}
	if ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) {
		return false;
	}

	$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	foreach ( array( '/wp-json/', 'rest_route=', '/wp-login', '/wp-admin' ) as $needle ) {
		if ( false !== strpos( $uri, $needle ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Ön üzdə WordPress-in dilini seçilmiş dilə uyğunlaşdırır.
 *
 * Dil HƏMİŞƏ açıq şəkildə təyin olunur — azərbaycanca da daxil olmaqla.
 * Əks halda AZ səhifəsi saytın admin dilini götürərdi: admin dili
 * ingiliscə və ya rusca olsa, temanın həmin .mo faylı yüklənər və
 * azərbaycanca sayt o dilə çevrilərdi.
 *
 * Admin panelin, giriş səhifəsinin və REST-in dili dəyişmir.
 *
 * @param string $locale Mövcud locale.
 */
function fixit_filter_locale( $locale ) {
	if ( ! fixit_is_frontend_request() ) {
		return $locale;
	}

	$langs = fixit_langs();
	$lang  = fixit_lang();

	return isset( $langs[ $lang ] ) ? $langs[ $lang ]['locale'] : $locale;
}
add_filter( 'determine_locale', 'fixit_filter_locale', 20 );
add_filter( 'locale', 'fixit_filter_locale', 20 );

/* ============================================================
   3. Ünvanların qurulması
   ============================================================ */

/**
 * Yola dil önşəkilçisi əlavə edir.
 *
 * @param string $url  Tam ünvan.
 * @param string $lang Dil.
 */
function fixit_add_lang_to_url( $url, $lang ) {
	if ( fixit_default_lang() === $lang || ! $url ) {
		return $url;
	}

	$parts = wp_parse_url( $url );
	$base  = fixit_site_path();

	// "http://host" (yolsuz) — WordPress sonra özü "/" əlavə edir,
	// ona görə "/en" qaytarırıq, "/en/" yox (əks halda "/en//" alınır).
	if ( empty( $parts['path'] ) || ( untrailingslashit( $parts['path'] ) === untrailingslashit( $base ) && '/' !== substr( $parts['path'], -1 ) ) ) {
		$parts['path'] = untrailingslashit( $base ) . '/' . $lang;
	} else {
		if ( 0 !== strpos( $parts['path'], $base ) ) {
			return $url;
		}

		$rest = substr( $parts['path'], strlen( $base ) );

		// Artıq önşəkilçi varsa təkrarlamırıq.
		foreach ( array_keys( fixit_langs() ) as $code ) {
			if ( $rest === $code || 0 === strpos( $rest, $code . '/' ) ) {
				return $url;
			}
		}

		$parts['path'] = $base . $lang . '/' . $rest;
	}

	$out = ( isset( $parts['scheme'] ) ? $parts['scheme'] . '://' : '' )
		. ( isset( $parts['host'] ) ? $parts['host'] : '' )
		. ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' )
		. $parts['path']
		. ( isset( $parts['query'] ) ? '?' . $parts['query'] : '' )
		. ( isset( $parts['fragment'] ) ? '#' . $parts['fragment'] : '' );

	return $out;
}

/**
 * Sayt daxilindəki bütün ünvanlara hazırkı dilin önşəkilçisini qoyur.
 *
 * home_url() demək olar bütün daxili linklərin (səhifə, məhsul, menyu)
 * əsasıdır — ona görə bir filtr kifayət edir.
 *
 * @param string $url  Ünvan.
 * @param string $path Yol.
 */
function fixit_filter_home_url( $url, $path = '' ) {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $url;
	}
	if ( fixit_is_default_lang() ) {
		return $url;
	}

	// REST, admin və giriş ünvanlarına toxunmuruq.
	$skip = array( 'wp-json', 'wp-admin', 'wp-login', 'wp-cron' );
	foreach ( $skip as $needle ) {
		if ( false !== strpos( (string) $path, $needle ) || false !== strpos( $url, '/' . $needle ) ) {
			return $url;
		}
	}

	return fixit_add_lang_to_url( $url, fixit_lang() );
}
add_filter( 'home_url', 'fixit_filter_home_url', 10, 2 );

/**
 * Hazırkı səhifənin başqa dildəki ünvanı (dil keçidi üçün).
 *
 * @param string $lang Hədəf dil.
 */
function fixit_current_url_in( $lang ) {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';

	// REQUEST_URI artıq önşəkilçisizdir (bax: fixit_parse_request_lang).
	$path  = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$query = (string) wp_parse_url( $uri, PHP_URL_QUERY );

	$url = home_url( $path );
	// home_url filtri hazırkı dili qoyur — onu təmizləyib hədəf dili qoyuruq.
	$url = fixit_strip_lang_from_url( $url );
	$url = fixit_add_lang_to_url( $url, $lang );

	return $url . ( $query ? '?' . $query : '' );
}

/**
 * Ünvandan dil önşəkilçisini çıxarır.
 *
 * @param string $url Ünvan.
 */
function fixit_strip_lang_from_url( $url ) {
	$parts = wp_parse_url( $url );
	if ( empty( $parts['path'] ) ) {
		return $url;
	}

	$base = fixit_site_path();
	if ( 0 !== strpos( $parts['path'], $base ) ) {
		return $url;
	}

	$rest = substr( $parts['path'], strlen( $base ) );

	foreach ( array_keys( fixit_langs() ) as $code ) {
		if ( fixit_default_lang() === $code ) {
			continue;
		}
		if ( $rest === $code || 0 === strpos( $rest, $code . '/' ) ) {
			$parts['path'] = $base . ltrim( substr( $rest, strlen( $code ) ), '/' );
			break;
		}
	}

	return ( isset( $parts['scheme'] ) ? $parts['scheme'] . '://' : '' )
		. ( isset( $parts['host'] ) ? $parts['host'] : '' )
		. ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' )
		. $parts['path']
		. ( isset( $parts['query'] ) ? '?' . $parts['query'] : '' )
		. ( isset( $parts['fragment'] ) ? '#' . $parts['fragment'] : '' );
}

/**
 * WordPress-in kanonik yönləndirməsi dil önşəkilçisini bilmir.
 *
 * Sorğu ünvanından önşəkilçi çıxarıldığı üçün WordPress məs. /en/mehsullar
 * üçün /mehsullar/-a yönləndirmək istəyir — ziyarətçi başqa dilə düşərdi.
 * Burada hədəfə dili geri qoyuruq. Hədəf ziyarətçinin açdığı ünvanın özüdürsə
 * (məs. /en/), yönləndirmə ləğv olunur — sonsuz dövrə yaranmır.
 *
 * @param string|false $redirect_url  WordPress-in təklif etdiyi ünvan.
 * @param string       $requested_url Sorğu ünvanı (önşəkilçisiz).
 */
function fixit_redirect_canonical( $redirect_url, $requested_url ) {
	if ( fixit_is_default_lang() || ! $redirect_url ) {
		return $redirect_url;
	}

	$target = fixit_add_lang_to_url( fixit_strip_lang_from_url( $redirect_url ), fixit_lang() );

	if ( ! empty( $GLOBALS['fixit_original_uri'] ) ) {
		$parts    = wp_parse_url( $requested_url );
		$original = ( isset( $parts['scheme'] ) ? $parts['scheme'] . '://' : '' )
			. ( isset( $parts['host'] ) ? $parts['host'] : '' )
			. ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' )
			. $GLOBALS['fixit_original_uri'];

		if ( $target === $original ) {
			return false;
		}
	}

	return $target;
}
add_filter( 'redirect_canonical', 'fixit_redirect_canonical', 20, 2 );

/* ============================================================
   4. Axtarış sistemləri üçün dil əlaqələri
   ============================================================ */

/**
 * <head> içində hreflang — hansı səhifənin hansı dildə qarşılığı olduğunu bildirir.
 */
function fixit_hreflang_tags() {
	$langs = fixit_langs();

	foreach ( $langs as $code => $info ) {
		printf(
			'<link rel="alternate" hreflang="%s" href="%s">' . "\n",
			esc_attr( $info['hreflang'] ),
			esc_url( fixit_current_url_in( $code ) )
		);
	}

	printf(
		'<link rel="alternate" hreflang="x-default" href="%s">' . "\n",
		esc_url( fixit_current_url_in( fixit_default_lang() ) )
	);
}
add_action( 'wp_head', 'fixit_hreflang_tags', 3 );

/**
 * <html lang="..."> atributu.
 *
 * @param string $output Mövcud atributlar.
 */
function fixit_language_attributes( $output ) {
	if ( fixit_is_default_lang() ) {
		return $output;
	}

	$langs = fixit_langs();
	$lang  = fixit_lang();

	return preg_replace( '/lang="[^"]*"/', 'lang="' . esc_attr( $langs[ $lang ]['hreflang'] ) . '"', $output );
}
add_filter( 'language_attributes', 'fixit_language_attributes' );

/* ============================================================
   5. Ölkəyə görə dil təklifi (brauzer tərəfdən çağırılır)
   ============================================================ */

/**
 * Rus dilinin işlək olduğu ölkələr.
 */
function fixit_ru_countries() {
	return array( 'RU', 'BY', 'KZ', 'KG', 'TJ', 'TM', 'UZ', 'AM', 'MD' );
}

/**
 * Ziyarətçi üçün uyğun dil.
 *
 * Ölkə Cloudflare-in başlığından gəlir (sayt Cloudflare arxasındadır).
 * Başlıq yoxdursa, brauzerin dil üstünlüyünə baxılır.
 */
function fixit_suggest_lang() {
	// Robot hər dili öz ünvanından (hreflang ilə) indeksləyir — onu yönləndirmirik.
	if ( fixit_is_bot() ) {
		return fixit_default_lang();
	}

	$country = isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) ) : '';

	// Türkiyədə azərbaycanca ingiliscədən daha rahat oxunur.
	if ( in_array( $country, array( 'AZ', 'TR' ), true ) ) {
		return 'az';
	}
	if ( $country && in_array( $country, fixit_ru_countries(), true ) ) {
		return 'ru';
	}
	if ( $country && 'XX' !== $country && 'T1' !== $country ) {
		return 'en';
	}

	// Ölkə bilinmir — brauzerin dilinə baxırıq.
	$accept = isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) ) : '';

	if ( 0 === strpos( $accept, 'az' ) || 0 === strpos( $accept, 'tr' ) ) {
		return 'az';
	}
	if ( 0 === strpos( $accept, 'ru' ) ) {
		return 'ru';
	}
	if ( $accept ) {
		return 'en';
	}

	return fixit_default_lang();
}

/**
 * Sorğu axtarış robotundan və ya avtomatik yoxlama alətindəndirmi?
 *
 * Google-un robotu JavaScript-i icra edir və əsasən ABŞ-dan gəlir:
 * yönləndirilsəydi, azərbaycanca səhifələr Google-a ingiliscə ünvana
 * köçmüş kimi görünərdi.
 */
function fixit_is_bot() {
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : '';

	return '' === $ua || (bool) preg_match( '/' . fixit_bot_pattern() . '/i', $ua );
}

/**
 * Robotların brauzer identifikatorunda olan sözlər (PHP və JS üçün ortaq).
 */
function fixit_bot_pattern() {
	return 'bot|crawl|spider|slurp|google|bing|yandex|baidu|duckduck|facebookexternalhit|lighthouse|headless|preview';
}

/**
 * /wp-json/fixit/v1/lang — brauzerə hansı dilin təklif olunduğunu deyir.
 */
function fixit_register_lang_route() {
	register_rest_route(
		'fixit/v1',
		'/lang',
		array(
			'methods'             => 'GET',
			'permission_callback' => '__return_true',
			'callback'            => function () {
				$response = rest_ensure_response(
					array(
						'suggested' => fixit_suggest_lang(),
						'country'   => isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) ) : '',
					)
				);
				$response->header( 'Cache-Control', 'no-store, max-age=0' );
				return $response;
			},
		)
	);
}
add_action( 'rest_api_init', 'fixit_register_lang_route' );

/**
 * Brauzer tərəfdəki skriptə lazım olan məlumat.
 */
function fixit_lang_js_data() {
	$urls = array();
	foreach ( array_keys( fixit_langs() ) as $code ) {
		$urls[ $code ] = fixit_current_url_in( $code );
	}

	return array(
		'current' => fixit_lang(),
		'default' => fixit_default_lang(),
		'cookie'  => FIXIT_LANG_COOKIE,
		'urls'    => $urls,
		'api'     => esc_url_raw( rest_url( 'fixit/v1/lang' ) ),
		'bots'    => fixit_bot_pattern(),
	);
}

/* ============================================================
   6. Dil keçidi (başlıqda göstərilir)
   ============================================================ */

/**
 * AZ / EN / RU keçidini çap edir.
 *
 * @param string $class Əlavə sinif.
 */
function fixit_lang_switcher( $class = '' ) {
	$current = fixit_lang();
	?>
	<div class="lang-switch <?php echo esc_attr( $class ); ?>" role="group" aria-label="<?php esc_attr_e( 'Saytın dili', 'fixit' ); ?>">
		<?php foreach ( fixit_langs() as $code => $info ) : ?>
			<a
				href="<?php echo esc_url( fixit_current_url_in( $code ) ); ?>"
				data-lang="<?php echo esc_attr( $code ); ?>"
				hreflang="<?php echo esc_attr( $info['hreflang'] ); ?>"
				title="<?php echo esc_attr( $info['name'] ); ?>"
				class="<?php echo $code === $current ? 'is-active' : ''; ?>"
				<?php echo $code === $current ? 'aria-current="true"' : ''; ?>
			><?php echo esc_html( $info['label'] ); ?></a>
		<?php endforeach; ?>
	</div>
	<?php
}
