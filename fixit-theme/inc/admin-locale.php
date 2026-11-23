<?php
/**
 * Arxa hissənin dili: ingiliscə (standart) və ya azərbaycanca.
 *
 * Saytın ön hissəsi AZ / EN / RU dillərindədir (inc/i18n.php). Admin panel,
 * temanın admin səhifələri və giriş səhifəsi isə hər istifadəçinin öz
 * seçimindədir: üst paneldəki «EN | AZ» keçidi ilə. Seçim istifadəçinin
 * profilində (fixit_admin_lang) və kukidə (giriş səhifəsi üçün) saxlanılır.
 *
 * WordPress öz dil faylını temadan əvvəl yüklədiyi üçün switch_to_locale()
 * işlədilir. Azərbaycanca üçün WordPress-in «az» dil paketi lazımdır: seçiləndə
 * quraşdırılır. Paket quraşdırıla bilməsə, WordPress-in öz mətnləri ingiliscə,
 * temanınkı azərbaycanca göstərilir.
 *
 * Müştəriyə gedən məktublar müştərinin dilindədir (fixit_trial_in_lang),
 * sahibə gedən bildirişlər ingiliscədir.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const FIXIT_ADMIN_LANG_COOKIE = 'fixit_admin_lang';

/**
 * Bu sorğu arxa hissəyədirmi (admin panel və ya giriş səhifəsi).
 *
 * AJAX, REST və cron ön hissənin də işidir: onların dilinə toxunulmur.
 */
function fixit_is_back_office_request() {
	if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return false;
	}
	if ( is_admin() ) {
		return true;
	}
	return isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'];
}

/**
 * İstifadəçinin seçdiyi arxa hissə dili: 'en' və ya 'az'.
 */
function fixit_admin_lang() {
	$user = get_current_user_id();
	$lang = $user ? (string) get_user_meta( $user, 'fixit_admin_lang', true ) : '';
	if ( '' === $lang && isset( $_COOKIE[ FIXIT_ADMIN_LANG_COOKIE ] ) ) {
		$lang = sanitize_key( wp_unslash( $_COOKIE[ FIXIT_ADMIN_LANG_COOKIE ] ) );
	}
	return 'az' === $lang ? 'az' : 'en';
}

/**
 * WordPress-in «az» dil paketi quraşdırılıbmı.
 */
function fixit_admin_az_pack_ready() {
	return in_array( 'az', get_available_languages(), true );
}

/**
 * Arxa hissədə dili seçimə keçirir.
 */
function fixit_back_office_locale() {
	if ( ! fixit_is_back_office_request() ) {
		return;
	}

	$want = 'en_US';
	if ( 'az' === fixit_admin_lang() ) {
		if ( fixit_admin_az_pack_ready() ) {
			$want = 'az';
		} else {
			// WordPress-in paketi yoxdur: onun mətnləri ingiliscə, temanınkı azərbaycanca (mənbə dili).
			add_action(
				'after_setup_theme',
				function () {
					unload_textdomain( 'fixit' );
				},
				20
			);
		}
	}

	if ( determine_locale() !== $want ) {
		switch_to_locale( $want );
	}
}
add_action( 'after_setup_theme', 'fixit_back_office_locale', 0 );

/**
 * Dil keçidinin ünvanı.
 *
 * @param string $lang en / az.
 */
function fixit_admin_lang_url( $lang ) {
	return wp_nonce_url(
		add_query_arg(
			array(
				'action' => 'fixit_admin_lang',
				'lang'   => $lang,
			),
			admin_url( 'admin-post.php' )
		),
		'fixit_admin_lang'
	);
}

/**
 * Seçimi yadda saxlayır; azərbaycanca üçün WordPress-in dil paketini quraşdırır.
 */
function fixit_admin_lang_switch() {
	check_admin_referer( 'fixit_admin_lang' );
	if ( ! is_user_logged_in() ) {
		wp_die( esc_html__( 'İcazə yoxdur.', 'fixit' ) );
	}

	$lang = isset( $_GET['lang'] ) && 'az' === $_GET['lang'] ? 'az' : 'en';
	update_user_meta( get_current_user_id(), 'fixit_admin_lang', $lang );
	setcookie(
		FIXIT_ADMIN_LANG_COOKIE,
		$lang,
		array(
			'expires'  => time() + YEAR_IN_SECONDS,
			'path'     => COOKIEPATH ? COOKIEPATH : '/',
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		)
	);

	$note = '';
	if ( 'az' === $lang && ! fixit_admin_az_pack_ready() ) {
		$note = 'pack_missing';
		if ( current_user_can( 'install_languages' ) ) {
			require_once ABSPATH . 'wp-admin/includes/translation-install.php';
			$note = wp_download_language_pack( 'az' ) ? 'pack_installed' : 'pack_failed';
		}
	}

	$back = wp_get_referer();
	$back = $back ? $back : admin_url();
	if ( $note ) {
		$back = add_query_arg( 'fixit_lang_note', $note, $back );
	}
	wp_safe_redirect( $back );
	exit;
}
add_action( 'admin_post_fixit_admin_lang', 'fixit_admin_lang_switch' );

/**
 * Üst paneldə «EN | AZ» keçidi (admin paneldə).
 *
 * @param WP_Admin_Bar $bar Panel.
 */
function fixit_admin_lang_bar( $bar ) {
	if ( ! is_admin() || ! is_user_logged_in() ) {
		return;
	}
	$current = fixit_admin_lang();
	$bar->add_node(
		array(
			'id'     => 'fixit-admin-lang',
			'parent' => 'top-secondary',
			'title'  => '<span class="ab-icon dashicons dashicons-translation" style="top:2px"></span>' . ( 'az' === $current ? 'AZ' : 'EN' ),
			'href'   => fixit_admin_lang_url( 'az' === $current ? 'en' : 'az' ),
			'meta'   => array( 'title' => __( 'Admin panelin dili', 'fixit' ) ),
		)
	);
	foreach ( array( 'en' => 'English', 'az' => 'Azərbaycanca' ) as $code => $label ) {
		$bar->add_node(
			array(
				'id'     => 'fixit-admin-lang-' . $code,
				'parent' => 'fixit-admin-lang',
				'title'  => ( $code === $current ? '✓ ' : '' ) . $label,
				'href'   => fixit_admin_lang_url( $code ),
			)
		);
	}
}
add_action( 'admin_bar_menu', 'fixit_admin_lang_bar', 5 );

/**
 * Dil paketi haqqında bildiriş.
 */
function fixit_admin_lang_notice() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- yalnız bildiriş
	$note = isset( $_GET['fixit_lang_note'] ) ? sanitize_key( wp_unslash( $_GET['fixit_lang_note'] ) ) : '';
	$text = array(
		'pack_installed' => array( 'success', __( 'WordPress-in Azərbaycan dili paketi quraşdırıldı.', 'fixit' ) ),
		'pack_failed'    => array( 'warning', __( 'WordPress-in Azərbaycan dili paketi quraşdırıla bilmədi: WordPress-in öz menyuları ingiliscə qalacaq, FIXIT səhifələri azərbaycancadır.', 'fixit' ) ),
		'pack_missing'   => array( 'warning', __( 'WordPress-in Azərbaycan dili paketi yoxdur və onu quraşdırmağa icazəniz yoxdur: WordPress-in öz menyuları ingiliscə qalacaq.', 'fixit' ) ),
	);
	if ( isset( $text[ $note ] ) ) {
		printf( '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>', esc_attr( $text[ $note ][0] ), esc_html( $text[ $note ][1] ) );
	}
}
add_action( 'admin_notices', 'fixit_admin_lang_notice' );
