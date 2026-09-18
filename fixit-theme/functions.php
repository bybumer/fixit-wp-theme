<?php
/**
 * FIXIT Pro — temanın əsas funksiya faylı
 *
 * Bütün kod sadə PHP-dir: build sistemi, npm və ya SASS tələb olunmur.
 * Faylı redaktə edib yükləmək kifayətdir.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Birbaşa girişi bağla.
}

define( 'FIXIT_VERSION', '1.5.1' );

/* ============================================================
   1. Tema dəstəkləri
   ============================================================ */
function fixit_setup() {
	load_theme_textdomain( 'fixit', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 60,
			'width'       => 220,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Əsas menyu', 'fixit' ),
			'footer'  => __( 'Alt menyu (Xidmətlər)', 'fixit' ),
		)
	);
}
add_action( 'after_setup_theme', 'fixit_setup' );

/* ============================================================
   2. Stil və skriptlər
   ============================================================ */
/**
 * Fayl versiyası — faylı dəyişdikdə brauzer keşi avtomatik təzələnir.
 *
 * @param string $rel Tema qovluğuna nisbi yol.
 */
function fixit_ver( $rel ) {
	$path = get_template_directory() . '/' . ltrim( $rel, '/' );
	return file_exists( $path ) ? (string) filemtime( $path ) : FIXIT_VERSION;
}

function fixit_assets() {
	// Google Fonts — Plus Jakarta Sans.
	wp_enqueue_style(
		'fixit-fonts',
		'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'fixit-style', get_stylesheet_uri(), array( 'fixit-fonts' ), fixit_ver( 'style.css' ) );

	wp_enqueue_script(
		'fixit-main',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		fixit_ver( 'assets/js/main.js' ),
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'fixit_assets' );

/**
 * Tema seçilməzdən əvvəl "ağ yanıp-sönmə" olmasın deyə <html> teqinə temanı erkən yazırıq.
 */
function fixit_theme_boot_script() {
	?>
	<script>
	(function(){
		try{
			var t = localStorage.getItem('fixit-theme');
			if(!t){ t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'; }
			document.documentElement.setAttribute('data-theme', t);
		}catch(e){}
		// JS işləyirsə bu sinif əlavə olunur. Animasiyalar YALNIZ bu sinif varsa
		// gizli başlayır — JS bloklansa belə məzmun görünməz qalmır.
		document.documentElement.className += ' fixit-js';
	})();
	</script>
	<?php
}
add_action( 'wp_head', 'fixit_theme_boot_script', 1 );

/* ============================================================
   3. Yardımçı fayllar
   ============================================================ */
require_once get_template_directory() . '/inc/icons.php';         // SVG ikon kitabxanası
require_once get_template_directory() . '/inc/slugs.php';         // Ünvanlarda ə → e (təmiz URL)
require_once get_template_directory() . '/inc/customizer.php';    // Fərdiləşdirmə (əlaqə, sosial şəbəkə)
require_once get_template_directory() . '/inc/cpt.php';           // Xidmət və rəy post növləri
require_once get_template_directory() . '/inc/product-demo.php';  // Məhsul: demo ayarları və ekran görüntüləri
require_once get_template_directory() . '/inc/product-data.php';  // Məhsul səhifələrinin detallı məzmunu
require_once get_template_directory() . '/inc/contact-form.php';  // Əlaqə formu (plagin tələb etmir)
require_once get_template_directory() . '/inc/demo-content.php';  // İlk quraşdırmada səhifə/menyu yaradılması
require_once get_template_directory() . '/inc/faq.php';           // Sual-cavab məzmunu (səhifə + schema)
require_once get_template_directory() . '/inc/seo.php';           // Meta teqlər və Schema.org
require_once get_template_directory() . '/inc/seo-admin.php';     // Admin paneldə SEO izləmə lövhəsi
require_once get_template_directory() . '/inc/updater.php';       // GitHub relizlərindən avtomatik yeniləmə
require_once get_template_directory() . '/inc/updater-package.php'; // Paketin yoxlanması, ehtiyat mənbə, diaqnostika

/* ============================================================
   4. Kiçik yardımçı funksiyalar
   ============================================================ */

/**
 * Customizer dəyərini qaytarır.
 */
function fixit_opt( $key, $default = '' ) {
	$value = get_theme_mod( $key, $default );
	return ( '' === $value || null === $value ) ? $default : $value;
}

/**
 * Telefon nömrəsini tel: linki üçün təmizləyir.
 */
function fixit_tel( $phone ) {
	return preg_replace( '/[^0-9+]/', '', $phone );
}

/**
 * Şablona görə səhifə linkini tapır (menyu/düymələr üçün).
 */
function fixit_page_url( $template ) {
	$cache_key = 'fixit_page_' . sanitize_key( $template );
	$cached    = wp_cache_get( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}

	$pages = get_pages(
		array(
			'meta_key'   => '_wp_page_template',
			'meta_value' => $template,
			'number'     => 1,
		)
	);

	$url = ! empty( $pages ) ? get_permalink( $pages[0]->ID ) : home_url( '/' );
	wp_cache_set( $cache_key, $url );

	return $url;
}

/**
 * Menyu təyin olunmayıbsa səhifələri göstərir.
 *
 * wp_nav_menu() bu funksiyaya öz arqumentlərini ötürür — <ul> sinfini
 * oradan götürürük ki, fallback da düzgün üslubda görünsün.
 *
 * @param array $args wp_nav_menu arqumentləri.
 */
function fixit_menu_fallback( $args = array() ) {
	$class = ! empty( $args['menu_class'] ) ? $args['menu_class'] : 'nav__links';

	echo '<ul class="' . esc_attr( $class ) . '">';
	wp_list_pages(
		array(
			'title_li' => '',
			'depth'    => 2,
		)
	);
	echo '</ul>';
}

/**
 * Ulduz reytinqi.
 */
function fixit_stars( $count = 5 ) {
	$out = '<div class="stars" aria-label="' . esc_attr( $count ) . '/5">';
	for ( $i = 0; $i < (int) $count; $i++ ) {
		$out .= fixit_icon( 'star', false );
	}
	return $out . '</div>';
}

/* ============================================================
   5. Kiçik optimallaşdırmalar
   ============================================================ */
// Emoji skriptlərini söndür (sürət üçün).
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
// WordPress versiyasını gizlət (təhlükəsizlik).
remove_action( 'wp_head', 'wp_generator' );

/**
 * Şəkillərə lazy-load və uyğun ölçü.
 */
function fixit_excerpt_length() {
	return 24;
}
add_filter( 'excerpt_length', 'fixit_excerpt_length' );

function fixit_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'fixit_excerpt_more' );

/**
 * Body class-a köməkçi siniflər.
 */
function fixit_body_class( $classes ) {
	if ( is_page_template( 'template-contact.php' ) ) {
		$classes[] = 'page-contact';
	}
	return $classes;
}
add_filter( 'body_class', 'fixit_body_class' );
