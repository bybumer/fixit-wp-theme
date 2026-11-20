<?php
/**
 * Arxa hissə ingiliscə: admin panel, temanın admin səhifələri, giriş səhifəsi.
 *
 * Saytın ön hissəsi AZ / EN / RU dillərindədir (inc/i18n.php). Admin panel
 * isə saytın və istifadəçinin dil ayarından asılı olmayaraq ingiliscədir.
 * WordPress öz dil faylını temadan əvvəl yüklədiyi üçün switch_to_locale()
 * işlədilir: həm WordPress-in, həm temanın mətnləri ingiliscə yüklənir
 * (temanın admin mətnlərinin tərcüməsi tools/translations/17-19-dadır).
 *
 * Müştəriyə gedən məktublar müştərinin dilindədir (fixit_trial_in_lang),
 * sahibə gedən bildirişlər ingiliscədir.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
 * Arxa hissədə dili ingiliscəyə keçirir.
 */
function fixit_back_office_english() {
	if ( fixit_is_back_office_request() && 'en_US' !== determine_locale() ) {
		switch_to_locale( 'en_US' );
	}
}
add_action( 'after_setup_theme', 'fixit_back_office_english', 0 );
