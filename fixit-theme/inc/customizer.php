<?php
/**
 * Fərdiləşdirmə (Customizer) — Görünüş → Fərdiləşdir
 *
 * Əlaqə məlumatları, sosial şəbəkələr və əsas mətnlər buradan
 * kod açmadan dəyişdirilir.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Standart dəyərlər — bir yerdən idarə olunur.
 */
function fixit_defaults() {
	return array(
		'fixit_phone'        => '+994 10 225 20 90',
		'fixit_phone_alt'    => '',
		'fixit_email'        => 'info@fixit.az',
		'fixit_whatsapp'     => '994102252090',
		'fixit_address_main' => 'Bakı, Nərimanov ray.',
		'fixit_hours_main'   => 'Həftənin 5 günü, 09:00 – 18:00',
		'fixit_address_br'   => 'Cəlilabad ray., Heydər Əliyev pr. 284A',
		'fixit_hours_br'     => 'Həftənin 7 günü, 09:00 – 20:00',
		'fixit_facebook'     => 'https://www.facebook.com/fixitservicesaz',
		'fixit_instagram'    => 'https://www.instagram.com/',
		'fixit_tiktok'       => 'https://www.tiktok.com/@fixit.az',
		'fixit_linkedin'     => '',
		'fixit_map_embed'    => 'https://www.google.com/maps?q=Cəlilabad+Heydər+Əliyev+prospekti+284A&output=embed',
		'fixit_form_email'   => 'info@fixit.az',
		'fixit_hero_title'   => 'Biznesiniz üçün <span>etibarlı İKT</span> infrastrukturu',
		'fixit_hero_text'    => 'Kompüter servisi, video müşahidə, bulud serverlər, korporativ mail və IP telefoniya — bütün texnoloji ehtiyaclarınız üçün vahid tərəfdaş.',
		'fixit_year_since'   => '2017',
		'fixit_stat_clients' => '40+',
		'fixit_stat_project' => '30+',
		'fixit_stat_years'   => '8+',
		'fixit_stat_support' => '7/24',
	);
}

/**
 * Standart dəyəri götür.
 */
function fixit_default( $key ) {
	$d = fixit_defaults();
	return isset( $d[ $key ] ) ? $d[ $key ] : '';
}

/**
 * Customizer sahələri.
 */
function fixit_customize_register( $wp_customize ) {

	/* ---------- Panel ---------- */
	$wp_customize->add_panel(
		'fixit_panel',
		array(
			'title'    => __( 'FIXIT tema ayarları', 'fixit' ),
			'priority' => 20,
		)
	);

	/**
	 * Kiçik köməkçi: sahə əlavə et.
	 */
	$add = function ( $id, $label, $section, $type = 'text', $desc = '' ) use ( $wp_customize ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => fixit_default( $id ),
				'sanitize_callback' => ( 'textarea' === $type ) ? 'wp_kses_post' : ( 'url' === $type ? 'esc_url_raw' : 'wp_kses_post' ),
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'       => $label,
				'section'     => $section,
				'type'        => ( 'url' === $type ) ? 'url' : $type,
				'description' => $desc,
			)
		);
	};

	/* ---------- Qaranlıq rejim üçün loqo ---------- */
	/*
	 * Başlıq gecə rejimində tündləşir, ona görə rəngli loqo orada oxunmur.
	 * Bu sahə boş qalarsa, hər iki rejimdə əsas loqo göstərilir.
	 */
	$wp_customize->add_setting(
		'fixit_logo_dark',
		array(
			'default'           => '',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'fixit_logo_dark',
			array(
				'label'       => __( 'Loqo — qaranlıq rejim', 'fixit' ),
				'description' => __( 'Ağ variantı yükləyin. Boş qalsa, hər iki rejimdə əsas loqo işlədilir.', 'fixit' ),
				'section'     => 'title_tagline',
				'mime_type'   => 'image',
				'priority'    => 9,
			)
		)
	);

	/* ---------- Əlaqə ---------- */
	$wp_customize->add_section(
		'fixit_contact',
		array(
			'title' => __( 'Əlaqə məlumatları', 'fixit' ),
			'panel' => 'fixit_panel',
		)
	);

	$add( 'fixit_phone', __( 'Telefon', 'fixit' ), 'fixit_contact' );
	$add( 'fixit_phone_alt', __( 'Əlavə telefon', 'fixit' ), 'fixit_contact' );
	$add( 'fixit_email', __( 'E-poçt', 'fixit' ), 'fixit_contact' );
	$add( 'fixit_whatsapp', __( 'WhatsApp nömrəsi', 'fixit' ), 'fixit_contact', 'text', __( 'Yalnız rəqəmlər, ölkə kodu ilə. Məs: 994102252090', 'fixit' ) );
	$add( 'fixit_address_main', __( 'Baş ofis ünvanı', 'fixit' ), 'fixit_contact' );
	$add( 'fixit_hours_main', __( 'Baş ofis iş saatları', 'fixit' ), 'fixit_contact' );
	$add( 'fixit_address_br', __( 'Filial ünvanı', 'fixit' ), 'fixit_contact' );
	$add( 'fixit_hours_br', __( 'Filial iş saatları', 'fixit' ), 'fixit_contact' );
	$add( 'fixit_form_email', __( 'Form mesajlarının gedəcəyi e-poçt', 'fixit' ), 'fixit_contact' );
	$add( 'fixit_map_embed', __( 'Xəritə embed linki', 'fixit' ), 'fixit_contact', 'url' );

	/* ---------- Sosial şəbəkələr ---------- */
	$wp_customize->add_section(
		'fixit_social',
		array(
			'title' => __( 'Sosial şəbəkələr', 'fixit' ),
			'panel' => 'fixit_panel',
		)
	);

	$add( 'fixit_facebook', 'Facebook', 'fixit_social', 'url' );
	$add( 'fixit_instagram', 'Instagram', 'fixit_social', 'url' );
	$add( 'fixit_tiktok', 'TikTok', 'fixit_social', 'url' );
	$add( 'fixit_linkedin', 'LinkedIn', 'fixit_social', 'url' );

	/* ---------- Ana səhifə ---------- */
	$wp_customize->add_section(
		'fixit_home',
		array(
			'title' => __( 'Ana səhifə (Hero)', 'fixit' ),
			'panel' => 'fixit_panel',
		)
	);

	$add( 'fixit_hero_title', __( 'Böyük başlıq', 'fixit' ), 'fixit_home', 'textarea', __( 'Rəngli hissəni &lt;span&gt;...&lt;/span&gt; içinə alın.', 'fixit' ) );
	$add( 'fixit_hero_text', __( 'Başlıq altı mətn', 'fixit' ), 'fixit_home', 'textarea' );
	$add( 'fixit_year_since', __( 'Fəaliyyət ili', 'fixit' ), 'fixit_home' );
	$add( 'fixit_stat_clients', __( 'Statistika: müştəri', 'fixit' ), 'fixit_home' );
	$add( 'fixit_stat_project', __( 'Statistika: layihə', 'fixit' ), 'fixit_home' );
	$add( 'fixit_stat_years', __( 'Statistika: təcrübə', 'fixit' ), 'fixit_home' );
	$add( 'fixit_stat_support', __( 'Statistika: dəstək', 'fixit' ), 'fixit_home' );
}
add_action( 'customize_register', 'fixit_customize_register' );

/**
 * Dəyər oxumaq üçün qısa funksiya (standart dəyərlə).
 */
function fixit_get( $key ) {
	return get_theme_mod( $key, fixit_default( $key ) );
}
