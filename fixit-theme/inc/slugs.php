<?php
/**
 * Azərbaycan hərfləri üçün təmiz ünvanlar.
 *
 * WordPress ş, ç, ğ, ö, ü, ı hərflərini ünvanda latın hərfinə çevirir,
 * amma "ə" hərfini tanımır və onu kodlayır:
 *     "Video Müşahidə Sistemləri" → /video-musahid%c9%99-sisteml%c9%99ri/
 * Bu fayl "ə → e" çevirməsini əlavə edir:
 *     "Video Müşahidə Sistemləri" → /video-musahide-sistemleri/
 *
 * Mövcud kodlaşmış ünvanlar da təmizlənir; köhnə ünvan WordPress tərəfindən
 * avtomatik yeniyə yönləndirilir (heç bir link qırılmır).
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ünvan yaradılmazdan əvvəl "ə/Ə" hərfini "e/E" ilə əvəz edir.
 *
 * WordPress-in öz çevirməsindən (prioritet 10) əvvəl işləyir.
 *
 * @param string $title Başlıq.
 * @return string
 */
function fixit_az_slug_letters( $title ) {
	return str_replace( array( 'ə', 'Ə' ), array( 'e', 'E' ), $title );
}
add_filter( 'sanitize_title', 'fixit_az_slug_letters', 9 );

/**
 * "ə" üzündən kodlaşmış mövcud ünvanları təmizləyir.
 *
 * Yalnız kodlaşmış (%c9%99 / %c6%8f) ünvanlara toxunulur — bunlar həmişə
 * avtomatik yaranıb. Əl ilə yazılmış ünvan dəyişmir.
 */
function fixit_fix_encoded_slugs() {
	global $wpdb;

	$ids = $wpdb->get_col(
		"SELECT ID FROM {$wpdb->posts}
		 WHERE post_type IN ('page','post','fixit_service','fixit_product')
		   AND post_status = 'publish'
		   AND ( post_name LIKE '%\%c9\%99%' OR post_name LIKE '%\%c6\%8f%' )"
	);

	foreach ( $ids as $id ) {
		$post = get_post( $id );
		if ( ! $post ) {
			continue;
		}

		$clean = sanitize_title( $post->post_title );
		if ( ! $clean || $clean === $post->post_name ) {
			continue;
		}

		// wp_update_post köhnə ünvanı saxlayır (_wp_old_slug) → avtomatik yönləndirmə.
		wp_update_post(
			array(
				'ID'        => $post->ID,
				'post_name' => wp_unique_post_slug( $clean, $post->ID, $post->post_status, $post->post_type, $post->post_parent ),
			)
		);
	}
}
