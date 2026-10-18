<?php
/**
 * Əlaqə formu — heç bir plagin tələb etmir.
 *
 * Mesajlar həm e-poçtla göndərilir, həm də admin paneldə
 * "Mesajlar" bölməsində saxlanılır (poçt işləməsə belə itmir).
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   1. Mesajların saxlanması üçün post növü
   ============================================================ */
function fixit_register_message_cpt() {
	register_post_type(
		'fixit_message',
		array(
			'labels'          => array(
				'name'          => __( 'Mesajlar', 'fixit' ),
				'singular_name' => __( 'Mesaj', 'fixit' ),
				'menu_name'     => __( 'Form mesajları', 'fixit' ),
				'edit_item'     => __( 'Mesaja bax', 'fixit' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'menu_icon'       => 'dashicons-email-alt',
			'menu_position'   => 23,
			'supports'        => array( 'title', 'editor' ),
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		)
	);
}
add_action( 'init', 'fixit_register_message_cpt' );

/**
 * Mesaj siyahısında əlavə sütunlar.
 */
function fixit_message_columns( $columns ) {
	return array(
		'cb'            => $columns['cb'],
		'fixit_kind'    => __( 'Növ', 'fixit' ),
		'title'         => __( 'Ad', 'fixit' ),
		'fixit_company' => __( 'Şirkət', 'fixit' ),
		'fixit_size'    => __( 'Say', 'fixit' ),
		'fixit_email'   => __( 'E-poçt', 'fixit' ),
		'fixit_phone'   => __( 'Telefon', 'fixit' ),
		'fixit_topic'   => __( 'Mövzu', 'fixit' ),
		'date'          => __( 'Tarix', 'fixit' ),
	);
}
add_filter( 'manage_fixit_message_posts_columns', 'fixit_message_columns' );

function fixit_message_column_content( $column, $post_id ) {
	if ( 'fixit_kind' === $column ) {
		$is_demo = ( 'demo' === get_post_meta( $post_id, '_fixit_msg_kind', true ) );
		printf(
			'<span style="display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;%s">%s</span>',
			$is_demo ? 'background:#e7f0ff;color:#0b63e5' : 'background:#f0f0f1;color:#50575e',
			esc_html( $is_demo ? __( 'Demo', 'fixit' ) : __( 'Müraciət', 'fixit' ) )
		);
		return;
	}

	$map = array(
		'fixit_company' => '_fixit_msg_company',
		'fixit_size'    => '_fixit_msg_size',
		'fixit_email'   => '_fixit_msg_email',
		'fixit_phone'   => '_fixit_msg_phone',
		'fixit_topic'   => '_fixit_msg_topic',
	);
	if ( isset( $map[ $column ] ) ) {
		echo esc_html( get_post_meta( $post_id, $map[ $column ], true ) );
	}
}
add_action( 'manage_fixit_message_posts_custom_column', 'fixit_message_column_content', 10, 2 );

/* ============================================================
   2. Formun qorunması
   ============================================================
   WordPress nonce-u ən çox 24 saat etibarlıdır, səhifə keşi isə səhifəni
   günlərlə saxlayır — keşdən gələn köhnə səhifədə forma "Texniki xəta"
   verərdi. Ona görə imzalı vaxt nişanı işlədilir: keşdə də etibarlı qalır,
   saxtalaşdırıla bilmir və formanın neçə saniyəyə doldurulduğunu göstərir. */

define( 'FIXIT_FORM_TOKEN_TTL', 45 * DAY_IN_SECONDS );
define( 'FIXIT_FORM_MIN_SECONDS', 3 );   // İnsan formanı 3 saniyədən tez doldura bilməz.
define( 'FIXIT_FORM_IP_LIMIT', 5 );      // Bir IP-dən 10 dəqiqədə.
define( 'FIXIT_FORM_TOTAL_LIMIT', 60 );  // Bütün saytdan 1 saatda.

/**
 * Vaxt nişanının imzası.
 *
 * @param string|int $time Unix vaxtı.
 */
function fixit_form_token_sign( $time ) {
	return substr( hash_hmac( 'sha256', 'fixit_contact|' . $time, wp_salt( 'nonce' ) ), 0, 32 );
}

/**
 * Formun gizli sahələri: imzalı vaxt nişanı və qayıdış ünvanı.
 *
 * Qayıdış ünvanı hazırkı dildədir — ziyarətçi /en/ və ya /ru/ səhifəsindən
 * yazıbsa, nəticəni də həmin dildə görür.
 */
function fixit_form_fields() {
	$time = time();

	printf(
		'<input type="hidden" name="fixit_ts" value="%s">',
		esc_attr( $time . '.' . fixit_form_token_sign( $time ) )
	);
	printf(
		'<input type="hidden" name="fixit_back" value="%s">',
		esc_url( remove_query_arg( 'fixit_msg', fixit_current_url_in( fixit_lang() ) ) )
	);
}

/**
 * Göndərilən formanın nişanı etibarlıdırmı?
 *
 * @return string 'ok', 'fast' (bot kimi tez) və ya 'bad'.
 */
function fixit_form_token_check() {
	$raw = isset( $_POST['fixit_ts'] ) ? sanitize_text_field( wp_unslash( $_POST['fixit_ts'] ) ) : '';

	if ( preg_match( '/^(\d{9,11})\.([a-f0-9]{32})$/', $raw, $m ) && hash_equals( fixit_form_token_sign( $m[1] ), $m[2] ) ) {
		$age = time() - (int) $m[1];

		if ( $age < FIXIT_FORM_MIN_SECONDS ) {
			return 'fast';
		}

		return $age <= FIXIT_FORM_TOKEN_TTL ? 'ok' : 'bad';
	}

	// Bu yeniləmədən əvvəl keşlənmiş səhifələrdə köhnə nonce sahəsi qalıb.
	if ( isset( $_POST['fixit_contact_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fixit_contact_nonce'] ) ), 'fixit_contact' ) ) {
		return 'ok';
	}

	return 'bad';
}

/**
 * Göndərmə limiti: bir IP-dən və bütün saytdan.
 *
 * Bot formanı minlərlə dəfə göndərsə, poçt qutusu və baza dolmasın.
 */
function fixit_form_rate_ok() {
	$ip_key = 'fixit_form_ip_' . md5( fixit_client_ip() );
	$ip_n   = (int) get_transient( $ip_key );
	$all_n  = (int) get_transient( 'fixit_form_total' );

	if ( $ip_n >= FIXIT_FORM_IP_LIMIT || $all_n >= FIXIT_FORM_TOTAL_LIMIT ) {
		return false;
	}

	set_transient( $ip_key, $ip_n + 1, 10 * MINUTE_IN_SECONDS );
	set_transient( 'fixit_form_total', $all_n + 1, HOUR_IN_SECONDS );

	return true;
}

/**
 * "İstifadəçi və ya kompüter sayı" seçimləri.
 *
 * Məhsul qiyməti saytda yazılmır, fərdi təklif istifadəçi sayına görə
 * hazırlanır — bu sahə təklifi ilk məktubdan hazırlamağa imkan verir.
 * Formadan yalnız bu siyahıdakı dəyər qəbul olunur.
 *
 * @return string[]
 */
function fixit_form_sizes() {
	return array( '1–10', '11–50', '51–200', '201–500', '500+' );
}

/**
 * Sahənin uzunluğunu məhdudlaşdırır.
 *
 * @param string $value Dəyər.
 * @param int    $max   Ən çox simvol.
 */
function fixit_form_clip( $value, $max ) {
	return trim( mb_substr( (string) $value, 0, $max ) );
}

/* ============================================================
   3. Formun emalı
   ============================================================ */
function fixit_handle_contact() {

	$redirect = isset( $_POST['fixit_back'] ) ? wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['fixit_back'] ) ), '' ) : '';
	if ( ! $redirect ) {
		$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	}

	// Nəticə bildirişinin göstəriləcəyi yer (forma özü göndərir).
	$anchor = isset( $_POST['fixit_anchor'] ) ? sanitize_key( wp_unslash( $_POST['fixit_anchor'] ) ) : '';
	$anchor = '#' . ( $anchor ? $anchor : 'elaqe-form' );

	$token = fixit_form_token_check();

	// Bot tələsi (honeypot) doldurulub və ya forma insan üçün qeyri-mümkün sürətlə
	// göndərilib — səssizcə uğurlu kimi göstər, heç nə saxlama.
	if ( ! empty( $_POST['fixit_website'] ) || 'fast' === $token ) {
		wp_safe_redirect( add_query_arg( 'fixit_msg', 'ok', $redirect ) . $anchor );
		exit;
	}

	if ( 'ok' !== $token ) {
		wp_safe_redirect( add_query_arg( 'fixit_msg', 'error', $redirect ) . $anchor );
		exit;
	}

	$name    = isset( $_POST['fixit_name'] ) ? fixit_form_clip( sanitize_text_field( wp_unslash( $_POST['fixit_name'] ) ), 100 ) : '';
	$email   = isset( $_POST['fixit_email'] ) ? fixit_form_clip( sanitize_email( wp_unslash( $_POST['fixit_email'] ) ), 254 ) : '';
	$phone   = isset( $_POST['fixit_phone'] ) ? fixit_form_clip( sanitize_text_field( wp_unslash( $_POST['fixit_phone'] ) ), 40 ) : '';
	$topic   = isset( $_POST['fixit_topic'] ) ? fixit_form_clip( sanitize_text_field( wp_unslash( $_POST['fixit_topic'] ) ), 150 ) : '';
	$message = isset( $_POST['fixit_message'] ) ? fixit_form_clip( sanitize_textarea_field( wp_unslash( $_POST['fixit_message'] ) ), 5000 ) : '';
	$company = isset( $_POST['fixit_company'] ) ? fixit_form_clip( sanitize_text_field( wp_unslash( $_POST['fixit_company'] ) ), 150 ) : '';
	$size    = isset( $_POST['fixit_size'] ) ? sanitize_text_field( wp_unslash( $_POST['fixit_size'] ) ) : '';
	$size    = in_array( $size, fixit_form_sizes(), true ) ? $size : '';
	$kind    = ( isset( $_POST['fixit_kind'] ) && 'demo' === $_POST['fixit_kind'] ) ? 'demo' : 'contact';

	// Demo formunda mesaj sahəsi yoxdur — məzmunu özümüz yazırıq.
	if ( 'demo' === $kind && '' === $message ) {
		/* translators: %s: məhsulun adı */
		$message = sprintf( __( 'Demo sorğusu: %s', 'fixit' ), $topic );
	}

	// Məcburi sahələr.
	if ( '' === $name || ! is_email( $email ) || '' === $message ) {
		wp_safe_redirect( add_query_arg( 'fixit_msg', 'invalid', $redirect ) . $anchor );
		exit;
	}

	// Reklam spamı: mesajda çoxlu link — səssizcə uğurlu kimi göstər.
	if ( preg_match_all( '#https?://|www\.#i', $message ) > 3 ) {
		wp_safe_redirect( add_query_arg( 'fixit_msg', 'ok', $redirect ) . $anchor );
		exit;
	}

	if ( ! fixit_form_rate_ok() ) {
		wp_safe_redirect( add_query_arg( 'fixit_msg', 'busy', $redirect ) . $anchor );
		exit;
	}

	// Admin paneldə saxla.
	$post_id = wp_insert_post(
		array(
			'post_type'    => 'fixit_message',
			'post_status'  => 'publish',
			'post_title'   => $name,
			'post_content' => $message,
		)
	);

	if ( $post_id && ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_fixit_msg_email', $email );
		update_post_meta( $post_id, '_fixit_msg_phone', $phone );
		update_post_meta( $post_id, '_fixit_msg_topic', $topic );
		update_post_meta( $post_id, '_fixit_msg_company', $company );
		update_post_meta( $post_id, '_fixit_msg_size', $size );
		update_post_meta( $post_id, '_fixit_msg_kind', $kind );
	}

	// E-poçt göndər.
	$to      = fixit_get( 'fixit_form_email' );
	$subject = ( 'demo' === $kind )
		? sprintf( '[fixit.az] Demo sorğusu — %s (%s)', $name, $topic )
		: sprintf( '[fixit.az] Yeni müraciət — %s', $name );

	$body = ( 'demo' === $kind ? "Saytdan yeni DEMO sorğusu daxil oldu:\n\n" : "Saytdan yeni müraciət daxil oldu:\n\n" )
		. "Ad: {$name}\n"
		. ( $company ? "Şirkət: {$company}\n" : '' )
		. ( $size ? "İstifadəçi / kompüter sayı: {$size}\n" : '' )
		. "E-poçt: {$email}\n"
		. "Telefon: {$phone}\n"
		. "Mövzu: {$topic}\n\n"
		. "Mesaj:\n{$message}\n\n"
		. fixit_demo_mail_hint( $kind, $topic )
		. '— ' . home_url( '/' );

	// Ad başlığa düşür: ünvan ayırıcıları başlığı poza bilməsin.
	$reply_name = trim( str_replace( array( '"', '<', '>', ',', ';', ':', '\\' ), '', $name ) );

	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: "' . $reply_name . '" <' . $email . '>',
	);

	wp_mail( $to, $subject, $body, $headers );

	wp_safe_redirect( add_query_arg( 'fixit_msg', 'demo' === $kind ? 'demo_ok' : 'ok', $redirect ) . $anchor );
	exit;
}
add_action( 'admin_post_nopriv_fixit_contact', 'fixit_handle_contact' );
add_action( 'admin_post_fixit_contact', 'fixit_handle_contact' );

/**
 * Demo sorğusu məktubuna xatırlatma: müştəriyə hansı demonun girişini göndərmək lazımdır.
 *
 * Demo ünvanı saytda görünmür — yalnız sizə gələn məktuba yazılır.
 *
 * @param string $kind  Sorğu növü.
 * @param string $topic Məhsulun adı.
 */
function fixit_demo_mail_hint( $kind, $topic ) {
	if ( 'demo' !== $kind || ! function_exists( 'fixit_product_demo' ) ) {
		return '';
	}

	foreach ( fixit_get_products() as $prod ) {
		if ( $prod->post_title === $topic ) {
			$demo = fixit_product_demo( $prod->ID );
			if ( $demo['url'] ) {
				return "Demo ünvanı: {$demo['url']}\nMüştəriyə giriş məlumatını göndərməyi unutmayın.\n\n";
			}
		}
	}

	return '';
}

/* ============================================================
   4. Nəticə bildirişi
   ============================================================ */
function fixit_form_notice() {
	if ( ! isset( $_GET['fixit_msg'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return '';
	}

	$status = sanitize_key( wp_unslash( $_GET['fixit_msg'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$messages = array(
		'ok'      => array( 'ok', __( 'Təşəkkür edirik! Müraciətiniz qəbul olundu. Ən qısa zamanda sizinlə əlaqə saxlayacağıq.', 'fixit' ) ),
		'demo_ok' => array( 'ok', __( 'Təşəkkür edirik! Demo sorğunuz qəbul olundu — giriş məlumatlarını e-poçtunuza göndərəcəyik.', 'fixit' ) ),
		'invalid' => array( 'err', __( 'Zəhmət olmasa ad, e-poçt və mesaj sahələrini düzgün doldurun.', 'fixit' ) ),
		'error'   => array( 'err', __( 'Texniki xəta baş verdi. Zəhmət olmasa bir daha cəhd edin və ya bizə zəng edin.', 'fixit' ) ),
		'busy'    => array( 'err', __( 'Qısa müddətdə çox sayda müraciət göndərildi. Bir az sonra yenidən cəhd edin və ya bizə zəng edin.', 'fixit' ) ),
	);

	if ( ! isset( $messages[ $status ] ) ) {
		return '';
	}

	return sprintf(
		'<div class="alert alert--%s">%s</div>',
		esc_attr( $messages[ $status ][0] ),
		esc_html( $messages[ $status ][1] )
	);
}
