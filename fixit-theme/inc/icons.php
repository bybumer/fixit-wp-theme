<?php
/**
 * SVG ikon kitabxanası.
 *
 * Yeni ikon əlavə etmək üçün massivə bir sətir yazmaq kifayətdir.
 * İstifadə:  fixit_icon( 'cloud' );   və ya   $svg = fixit_icon( 'cloud', false );
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * İkon siyahısı (stroke əsaslı, 24x24).
 */
function fixit_icon_list() {
	return array(
		// --- Xidmət ikonları ---
		'computer'  => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>',
		'camera'    => '<path d="M2 8a2 2 0 0 1 2-2h3l1.5-2h7L17 6h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2z"/><circle cx="12" cy="12.5" r="3.5"/>',
		'cloud'     => '<path d="M17.5 19a4.5 4.5 0 0 0 .5-8.97 6 6 0 0 0-11.66-1.5A4.5 4.5 0 0 0 6.5 19z"/><path d="M12 16v-5m0 0-2 2m2-2 2 2"/>',
		'mail'      => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/>',
		'phone'     => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.7a2 2 0 0 1-.4 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.5 2.7.6a2 2 0 0 1 1.7 2z"/>',
		'support'   => '<path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>',
		'shield'    => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
		'server'    => '<rect x="2" y="3" width="20" height="7" rx="2"/><rect x="2" y="14" width="20" height="7" rx="2"/><path d="M6 6.5h.01M6 17.5h.01"/>',
		'network'   => '<rect x="9" y="2" width="6" height="6" rx="1"/><rect x="2" y="16" width="6" height="6" rx="1"/><rect x="16" y="16" width="6" height="6" rx="1"/><path d="M12 8v4M5 16v-2h14v2"/>',
		'wrench'    => '<path d="M14.7 6.3a4 4 0 0 0 5 5l-9.4 9.4a2.1 2.1 0 0 1-3-3z"/><path d="M14.7 6.3 17.5 3.5a4 4 0 0 1 3 3l-2.8 2.8"/>',
		'lock'      => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
		'wifi'      => '<path d="M5 12.5a10 10 0 0 1 14 0M8.5 16a5.5 5.5 0 0 1 7 0"/><circle cx="12" cy="19.5" r="1"/>',
		'printer'   => '<path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="7" rx="2"/><path d="M6 14h12v7H6z"/>',
		'database'  => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>',

		'ticket'    => '<path d="M3 9V7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2.5 2.5 0 0 0 0 6v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2.5 2.5 0 0 0 0-6z"/><path d="M13 5v2m0 4v2m0 4v2"/>',
		'activity'  => '<path d="M3 12h4l3 8 4-16 3 8h4"/>',
		'gauge'     => '<path d="M3.5 18a9 9 0 1 1 17 0"/><path d="m12 14 4-4"/><circle cx="12" cy="14" r="1.6"/>',

		// --- Ümumi ---
		'check'     => '<path d="m4 12 5 5L20 6"/>',
		'check-c'   => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12 2.5 2.5 4.5-5"/>',
		'arrow-r'   => '<path d="M5 12h14m-6-6 6 6-6 6"/>',
		'arrow-up'  => '<path d="M12 19V5m-6 6 6-6 6 6"/>',
		'star'      => '<path d="m12 2.8 2.9 5.9 6.5.9-4.7 4.6 1.1 6.5-5.8-3-5.8 3 1.1-6.5L2.6 9.6l6.5-.9z" fill="currentColor" stroke="none"/>',
		'pin'       => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
		'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
		'users'     => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9"/><path d="M16 3.1a4 4 0 0 1 0 7.8"/>',
		'zap'       => '<path d="M13 2 4 14h7l-1 8 9-12h-7z"/>',
		'award'     => '<circle cx="12" cy="9" r="6"/><path d="m8.2 14-1.7 7L12 18l5.5 3-1.7-7"/>',
		'chart'     => '<path d="M3 3v18h18"/><path d="m7 15 4-5 3 3 5-7"/>',
		'search'    => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'     => '<path d="M6 6l12 12M18 6 6 18"/>',
		'sun'       => '<circle cx="12" cy="12" r="4.2"/><path d="M12 2v2m0 16v2M4.2 4.2l1.4 1.4m12.8 12.8 1.4 1.4M2 12h2m16 0h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/>',
		'moon'      => '<path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5z"/>',
		'send'      => '<path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4z"/>',

		// --- Sosial şəbəkələr (fill) ---
		'whatsapp'  => '<path fill="currentColor" stroke="none" d="M17.5 14.4c-.3-.2-1.8-.9-2-1-.3-.1-.5-.2-.7.1-.2.3-.7 1-.9 1.2-.2.2-.3.2-.6.1-1.7-.9-2.9-1.6-4-3.6-.3-.5.3-.5.8-1.5.1-.2 0-.4 0-.5 0-.2-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.2.2 2.1 3.2 5.1 4.5 1.9.8 2.6.9 3.5.7.6-.1 1.8-.7 2-1.4.3-.7.3-1.3.2-1.4 0-.2-.2-.3-.5-.4z"/><path fill="currentColor" stroke="none" d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2zm0 18.2c-1.6 0-3.1-.4-4.4-1.2l-.3-.2-3.1.8.8-3-.2-.3A8.2 8.2 0 1 1 12 20.2z"/>',
		'facebook'  => '<path fill="currentColor" stroke="none" d="M13.5 22v-8h2.7l.4-3.1h-3.1V8.9c0-.9.25-1.5 1.55-1.5h1.65V4.6A22 22 0 0 0 14.3 4.5c-2.4 0-4 1.45-4 4.12v2.3H7.6V14h2.7v8z"/>',
		'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1.2" fill="currentColor" stroke="none"/>',
		'tiktok'    => '<path fill="currentColor" stroke="none" d="M16.6 2h-3v13.1a2.6 2.6 0 1 1-2.2-2.6V9.4a5.7 5.7 0 1 0 5.2 5.7V8.6a6.6 6.6 0 0 0 3.7 1.15V6.7a3.7 3.7 0 0 1-3.7-3.7z"/>',
		'linkedin'  => '<path fill="currentColor" stroke="none" d="M6.1 8.6H3.2V21h2.9zM4.65 3.5a1.7 1.7 0 1 0 0 3.4 1.7 1.7 0 0 0 0-3.4zM21 13.9c0-3.2-1.7-4.7-4-4.7-1.8 0-2.6 1-3.05 1.7V8.6H11.1V21H14v-6.7c0-1.4.6-2.3 1.8-2.3s1.7.85 1.7 2.3V21H21z"/>',
	);
}

/**
 * İkonu göstərir və ya qaytarır.
 *
 * @param string $name İkon adı.
 * @param bool   $echo Ekrana çıxarılsın?
 */
function fixit_icon( $name, $echo = true ) {
	$icons = fixit_icon_list();

	if ( ! isset( $icons[ $name ] ) ) {
		$name = 'check-c';
	}

	$svg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[ $name ] . '</svg>';

	if ( $echo ) {
		echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- daxili SVG.
		return;
	}

	return $svg;
}
