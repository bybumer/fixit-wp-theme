<?php
/**
 * Saytın ünvanları ingiliscə: /about/, /services/, /product/remote/ ...
 *
 * Məzmun AZ / EN / RU dillərindədir (dil önşəkilçisi /en/, /ru/ olduğu kimi
 * qalır), ünvanların özü isə ingiliscədir. Köhnə Azərbaycan ünvanları
 * (/haqqimizda/, /mehsul/sorgu-sistemi/ ...) yenilərinə 301 ilə yönləndirilir:
 * axtarış sistemləri, Google Play-dəki məxfilik linkləri, göndərilmiş
 * məktublar və proqramların içindəki keçidlər işləməyə davam edir.
 *
 * Köçürmə bir dəfə işləyir (option fixit_url_rev): səhifələrin və
 * məhsulların slug-u dəyişir, yazı növlərinin ünvanları yenilənir.
 *
 * Məhsulun daxili açarı (mətnlər, şəkil qovluğu) köhnə slug-dur:
 * fixit_product_key() onu qaytarır.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Səhifələr: köhnə slug → yeni. */
function fixit_page_slug_map() {
	return array(
		'ana-sehife'            => 'home',
		'xidmetler'             => 'services',
		'mehsullar'             => 'products',
		'haqqimizda'            => 'about',
		'uzaqdan-destek'        => 'remote-access',
		'elaqe'                 => 'contact',
		'fixit-remote-mexfilik' => 'fixit-remote-privacy',
		'fixit-ticket-mexfilik' => 'fixit-ticket-privacy',
		'mexfilik-siyaseti'     => 'privacy-policy',
		'fixit-remote-qurulum'  => 'fixit-remote-setup',
	);
}

/** Məhsullar: köhnə slug → yeni (remote dəyişmir). */
function fixit_product_slug_map() {
	return array(
		'sorgu-sistemi'      => 'ticket-system',
		'sebeke-monitorinqi' => 'network-monitoring',
	);
}

/** Yazı növlərinin ünvanı: köhnə → yeni. */
function fixit_type_slug_map() {
	return array(
		'mehsul' => 'product',
		'xidmet' => 'service',
	);
}

/** Səhifə daxilindəki lövbərlər: köhnə → yeni (JS köhnə linkləri yenisinə çevirir). */
function fixit_anchor_map() {
	return array(
		'qiymetler'  => 'pricing',
		'sinaq'      => 'trial',
		'elaqe-form' => 'contact-form',
		'elaqe'      => 'contact',
		'biznes'     => 'business',
		'ferdi'      => 'personal',
	);
}

/**
 * Məhsulun daxili açarı (product-data.php, şəkil qovluğu): köhnə slug.
 *
 * @param WP_Post|null $post Məhsul.
 */
function fixit_product_key( $post ) {
	if ( ! $post ) {
		return '';
	}
	$back = array_flip( fixit_product_slug_map() );
	return isset( $back[ $post->post_name ] ) ? $back[ $post->post_name ] : $post->post_name;
}

/**
 * Məhsulu açarla tapır (yeni və ya köhnə slug).
 *
 * @param string $key Köhnə açar (məs. sorgu-sistemi).
 * @return WP_Post|null
 */
function fixit_product_by_key( $key ) {
	$map  = fixit_product_slug_map();
	$slug = isset( $map[ $key ] ) ? $map[ $key ] : $key;
	$post = get_page_by_path( $slug, OBJECT, 'fixit_product' );
	if ( ! $post && $slug !== $key ) {
		$post = get_page_by_path( $key, OBJECT, 'fixit_product' );
	}
	return $post instanceof WP_Post ? $post : null;
}

/**
 * Bir dəfəlik köçürmə: köhnə slug-lu səhifə və məhsullar yeni ada keçir.
 *
 * Slug birbaşa bazada dəyişir: save_post hook-ları («əl ilə redaktə olunub»
 * nişanı və s.) işə düşmür.
 */
function fixit_url_migrate() {
	if ( (int) get_option( 'fixit_url_rev' ) >= 1 ) {
		return;
	}

	// Eyni anda iki sorğu köçürməsin.
	$lock = (int) get_option( 'fixit_url_migrating' );
	if ( $lock && time() - $lock < 300 ) {
		return;
	}
	update_option( 'fixit_url_migrating', time(), false );

	global $wpdb;
	$renamed = array();

	$sets = array(
		'page'          => fixit_page_slug_map(),
		'fixit_product' => fixit_product_slug_map(),
	);
	foreach ( $sets as $type => $map ) {
		foreach ( $map as $old => $new ) {
			$post = get_page_by_path( $old, OBJECT, $type );
			if ( ! $post || get_page_by_path( $new, OBJECT, $type ) ) {
				continue;
			}
			$wpdb->update( $wpdb->posts, array( 'post_name' => $new ), array( 'ID' => $post->ID ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			clean_post_cache( $post->ID );
			$renamed[] = $type . ':' . $old . '→' . $new;
		}
	}

	// Səhifələrin siyahısı (menyu və ayarlar üçün) yeni açarlarla.
	$ids = get_option( 'fixit_page_ids' );
	if ( is_array( $ids ) ) {
		$map  = fixit_page_slug_map();
		$next = array();
		foreach ( $ids as $key => $id ) {
			$next[ isset( $map[ $key ] ) ? $map[ $key ] : $key ] = $id;
		}
		update_option( 'fixit_page_ids', $next );
	}

	flush_rewrite_rules( false );
	update_option( 'fixit_url_rev', 1 );
	update_option( 'fixit_url_migrated', array( 'time' => time(), 'renamed' => $renamed ), false );
	delete_option( 'fixit_url_migrating' );

	do_action( 'litespeed_purge_all' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- LiteSpeed Cache-in öz hook-u
}
add_action( 'init', 'fixit_url_migrate', 99 );

/**
 * Köhnə ünvan → yeni (301). Yalnız səhifə tapılmayanda işləyir.
 */
function fixit_legacy_redirect() {
	if ( ! is_404() ) {
		return;
	}

	global $wp;
	$path   = trim( (string) $wp->request, '/' );
	$prefix = '';
	if ( preg_match( '#^(en|ru)(/|$)#', $path, $m ) ) {
		$prefix = $m[1];
		$path   = ltrim( (string) substr( $path, strlen( $m[1] ) ), '/' );
	}
	if ( '' === $path ) {
		return;
	}

	$parts = explode( '/', $path );
	$pages = fixit_page_slug_map();
	$types = fixit_type_slug_map();
	$prods = fixit_product_slug_map();
	$found = false;

	if ( isset( $pages[ $parts[0] ] ) ) {
		$parts[0] = 'home' === $pages[ $parts[0] ] ? '' : $pages[ $parts[0] ];
		$found    = true;
	} elseif ( isset( $types[ $parts[0] ] ) ) {
		$parts[0] = $types[ $parts[0] ];
		$found    = true;
	}
	if ( isset( $parts[1] ) && in_array( $parts[0], array( 'product' ), true ) && isset( $prods[ $parts[1] ] ) ) {
		$parts[1] = $prods[ $parts[1] ];
		$found    = true;
	}
	if ( ! $found ) {
		return;
	}

	$rel = implode( '/', array_filter( $parts, 'strlen' ) );
	$url = home_url( '/' . ( $prefix ? $prefix . '/' : '' ) . ( '' !== $rel ? $rel . '/' : '' ) );

	$query = isset( $_SERVER['QUERY_STRING'] ) ? (string) wp_unslash( $_SERVER['QUERY_STRING'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- yalnız ötürülür, esc_url_raw ilə təmizlənir
	if ( '' !== $query ) {
		$url .= '?' . $query;
	}

	wp_safe_redirect( esc_url_raw( $url ), 301, 'FIXIT' );
	exit;
}
add_action( 'template_redirect', 'fixit_legacy_redirect', 0 );
