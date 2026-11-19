<?php
/**
 * Fixit Remote: 14 günlük sınaq müraciəti.
 *
 * Axın:
 * 1. Ziyarətçi məhsul səhifəsindəki qısa formanı doldurur (ad, şirkət, telefon, e-poçt).
 * 2. Müraciət admin paneldə saxlanılır: Fixit Remote → Sınaq müraciətləri. Sizə
 *    bildiriş gəlir, ziyarətçiyə isə «yoxlayıb göndərəcəyik» deyilir.
 * 3. Siz müraciətin həqiqi olduğunu yoxlayırsınız və «Bələdçini göndər» düyməsi ilə
 *    müştəriyə öz dilində hazır məktub gedir: qurulum bələdçisi, qiymətlər və şəxsi
 *    yükləmə linki (müddətli). İstəsəniz ayarlarda avtomatik göndərmə də açılır.
 * 4. Server paketi gizli GitHub reposundadır. Link fixit.az/sinaq-paketi/
 *    ünvanından keçir: sayt açarı yoxlayır, yükləməni qeyd edir və paketi
 *    GitHub-dan bir neçə dəqiqəlik ünvanla verir. Repo və açar heç yerdə görünmür.
 *
 * Ayarlar: Fixit Remote → Qiymətlər və sınaq.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   1. Müraciətlərin saxlanması
   ============================================================ */
function fixit_trial_register_cpt() {
	$cap = 'manage_options';

	register_post_type(
		'fixit_trial',
		array(
			'labels'       => array(
				'name'          => __( 'Sınaq müraciətləri', 'fixit' ),
				'singular_name' => __( 'Sınaq müraciəti', 'fixit' ),
				'menu_name'     => __( 'Sınaq müraciətləri', 'fixit' ),
				'all_items'     => __( 'Sınaq müraciətləri', 'fixit' ),
				'edit_item'     => __( 'Sınaq müraciəti', 'fixit' ),
				'search_items'  => __( 'Müraciət axtar', 'fixit' ),
				'not_found'     => __( 'Hələ müraciət yoxdur.', 'fixit' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => 'fixit-remote',
			'show_in_rest' => false,
			'supports'     => array( 'title' ),
			'map_meta_cap' => false,
			'capabilities' => array(
				'edit_post'          => $cap,
				'read_post'          => $cap,
				'delete_post'        => $cap,
				'edit_posts'         => $cap,
				'edit_others_posts'  => $cap,
				'delete_posts'       => $cap,
				'publish_posts'      => $cap,
				'read_private_posts' => $cap,
				'create_posts'       => 'do_not_allow',
			),
		)
	);
}
add_action( 'init', 'fixit_trial_register_cpt' );

/**
 * Müraciətin vəziyyətləri.
 */
function fixit_trial_statuses() {
	return array(
		'new'       => __( 'Yeni', 'fixit' ),
		'sent'      => __( 'Bələdçi göndərilib', 'fixit' ),
		'contacted' => __( 'Əlaqə saxlanılıb', 'fixit' ),
		'installed' => __( 'Qurub, sınayır', 'fixit' ),
		'customer'  => __( 'Müştəri oldu', 'fixit' ),
		'lost'      => __( 'İmtina etdi', 'fixit' ),
		'rejected'  => __( 'Rədd edildi', 'fixit' ),
	);
}

/**
 * Sınaq ayarları.
 *
 * @return array{days:int,link_days:int,auto_send:bool,notify:string,repo:string}
 */
function fixit_trial_settings() {
	$saved = get_option( 'fixit_remote_trial', array() );
	$saved = is_array( $saved ) ? $saved : array();

	$s = wp_parse_args(
		$saved,
		array(
			'link_days' => 14,
			'auto_send' => false,
			'notify'    => '',
			'repo'      => 'bybumer/fixit-remote-server',
		)
	);

	return array(
		'days'      => 14,
		'link_days' => min( 60, max( 1, (int) $s['link_days'] ) ),
		'auto_send' => (bool) $s['auto_send'],
		'notify'    => is_email( $s['notify'] ) ? $s['notify'] : fixit_get( 'fixit_form_email' ),
		'repo'      => preg_match( '#^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$#', (string) $s['repo'] ) ? (string) $s['repo'] : 'bybumer/fixit-remote-server',
	);
}

/**
 * Gizli repoya giriş açarı (yalnız oxuma). Saytda göstərilmir.
 */
function fixit_trial_repo_token() {
	return (string) get_option( 'fixit_remote_repo_token', '' );
}

/* ============================================================
   2. Sayt: sınaq bölməsi və forma
   ============================================================ */

/**
 * Göndərmənin nəticəsi.
 */
function fixit_trial_notice() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- yalnız bildiriş
	$status = isset( $_GET['fixit_trial'] ) ? sanitize_key( wp_unslash( $_GET['fixit_trial'] ) ) : '';

	$messages = array(
		'received' => array( 'ok', __( 'Təşəkkür edirik! Müraciətiniz qəbul olundu. Mütəxəssisimiz məlumatlarınızı yoxlayıb qurulum bələdçisini və serverin yükləmə linkini iş saatı ərzində e-poçtunuza göndərəcək.', 'fixit' ) ),
		'ok'      => array( 'ok', __( 'Təşəkkür edirik! Qurulum bələdçisini və qiymət məlumatını e-poçtunuza göndərdik. Məktubu bir neçə dəqiqəyə görməsəniz, «Spam» qovluğuna baxın.', 'fixit' ) ),
		'invalid' => array( 'err', __( 'Zəhmət olmasa bütün sahələri düzgün doldurun: ad, şirkət, telefon və e-poçt.', 'fixit' ) ),
		'busy'    => array( 'err', __( 'Qısa müddətdə çox sayda müraciət göndərildi. Bir az sonra yenidən cəhd edin və ya bizə zəng edin.', 'fixit' ) ),
		'queued'  => array( 'ok', __( 'Müraciətiniz qəbul olundu. Məktubu hazırda göndərə bilmədik: mütəxəssisimiz bələdçini və qiymətləri qısa müddətdə özü göndərəcək.', 'fixit' ) ),
		'error'   => array( 'err', __( 'Texniki xəta baş verdi. Zəhmət olmasa bir daha cəhd edin və ya bizə zəng edin.', 'fixit' ) ),
	);

	if ( ! isset( $messages[ $status ] ) ) {
		return '';
	}

	return sprintf(
		'<div class="alert alert--%1$s" role="status">%2$s</div>',
		esc_attr( $messages[ $status ][0] ),
		esc_html( $messages[ $status ][1] )
	);
}

/**
 * «14 gün pulsuz sınaq» bölməsi (Fixit Remote məhsul səhifəsində).
 */
function fixit_trial_section() {
	$s = fixit_trial_settings();
	?>
	<section class="section" id="sinaq">
		<div class="container">
			<div class="trial reveal">
				<div class="trial__text">
					<span class="eyebrow"><?php fixit_icon( 'gift' ); ?><?php esc_html_e( 'Pulsuz sınaq', 'fixit' ); ?></span>
					<h2><?php echo esc_html( sprintf( /* translators: %d: gün */ __( 'Serveri %d gün pulsuz sınayın', 'fixit' ), $s['days'] ) ); ?></h2>
					<p><?php esc_html_e( 'Formanı doldurun: mütəxəssisimiz müraciətinizi yoxlayıb qurulum bələdçisini və serverin yükləmə linkini e-poçtunuza göndərəcək. Server öz avadanlığınızda qurulur, sınaq müddətində bütün imkanlar açıqdır.', 'fixit' ); ?></p>
					<ul class="trial__list">
						<li><?php fixit_icon( 'check' ); ?><span><?php esc_html_e( 'Ödəniş və kart tələb olunmur', 'fixit' ); ?></span></li>
						<li><?php fixit_icon( 'check' ); ?><span><?php esc_html_e( 'Sınaq müddətində kompüter sayı məhdud deyil', 'fixit' ); ?></span></li>
						<li><?php fixit_icon( 'check' ); ?><span><?php esc_html_e( 'Linux serverdə bir əmrlə qurulur', 'fixit' ); ?></span></li>
						<li><?php fixit_icon( 'check' ); ?><span><?php esc_html_e( 'Lisenziya yazılan kimi eyni server işləməyə davam edir, heç nə yenidən qurulmur', 'fixit' ); ?></span></li>
						<li><?php fixit_icon( 'check' ); ?><span><?php esc_html_e( 'Qurulumda kömək lazım olsa, mütəxəssisimiz qoşulur', 'fixit' ); ?></span></li>
					</ul>
				</div>

				<div class="trial__form">
					<?php echo fixit_trial_notice(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- funksiya içində escape olunub ?>

					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="form">
						<input type="hidden" name="action" value="fixit_trial">
						<input type="hidden" name="fixit_lang" value="<?php echo esc_attr( fixit_lang() ); ?>">
						<?php fixit_form_fields(); ?>

						<div class="hp-field" aria-hidden="true">
							<label for="fixit_website_t"><?php esc_html_e( 'Sayt', 'fixit' ); ?></label>
							<input type="text" name="fixit_website" id="fixit_website_t" tabindex="-1" autocomplete="off">
						</div>

						<div class="field">
							<label for="fixit_name_t"><?php esc_html_e( 'Ad, soyad', 'fixit' ); ?> *</label>
							<input type="text" name="fixit_name" id="fixit_name_t" required maxlength="100" autocomplete="name">
						</div>
						<div class="field">
							<label for="fixit_company_t"><?php esc_html_e( 'Şirkətin adı', 'fixit' ); ?> *</label>
							<input type="text" name="fixit_company" id="fixit_company_t" required maxlength="150" autocomplete="organization">
						</div>
						<div class="field-2">
							<div class="field">
								<label for="fixit_phone_t"><?php esc_html_e( 'Telefon', 'fixit' ); ?> *</label>
								<input type="tel" name="fixit_phone" id="fixit_phone_t" required maxlength="40" autocomplete="tel" placeholder="+994 __ ___ __ __">
							</div>
							<div class="field">
								<label for="fixit_email_t"><?php esc_html_e( 'E-poçt', 'fixit' ); ?> *</label>
								<input type="email" name="fixit_email" id="fixit_email_t" required maxlength="254" autocomplete="email">
							</div>
						</div>

						<button type="submit" class="btn btn--primary btn--block"><?php fixit_icon( 'send' ); ?><?php esc_html_e( 'Sınaq üçün müraciət edin', 'fixit' ); ?></button>
						<?php fixit_form_privacy_note(); ?>
					</form>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/* ============================================================
   3. Formanın emalı
   ============================================================ */
function fixit_trial_handle() {
	$back = isset( $_POST['fixit_back'] ) ? wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['fixit_back'] ) ), '' ) : '';
	if ( ! $back ) {
		$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	}
	$go = function ( $code ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'fixit_trial', $code, remove_query_arg( 'fixit_trial', $back ) ) . '#sinaq' );
		exit;
	};

	$token = fixit_form_token_check();

	// Bot: tələ doldurulub və ya insan üçün qeyri-mümkün sürət — uğurlu kimi göstər, heç nə etmə.
	if ( ! empty( $_POST['fixit_website'] ) || 'fast' === $token ) {
		$go( 'ok' );
	}
	if ( 'ok' !== $token ) {
		$go( 'error' );
	}

	$name    = isset( $_POST['fixit_name'] ) ? fixit_form_clip( sanitize_text_field( wp_unslash( $_POST['fixit_name'] ) ), 100 ) : '';
	$company = isset( $_POST['fixit_company'] ) ? fixit_form_clip( sanitize_text_field( wp_unslash( $_POST['fixit_company'] ) ), 150 ) : '';
	$phone   = isset( $_POST['fixit_phone'] ) ? fixit_form_clip( sanitize_text_field( wp_unslash( $_POST['fixit_phone'] ) ), 40 ) : '';
	$email   = isset( $_POST['fixit_email'] ) ? fixit_form_clip( sanitize_email( wp_unslash( $_POST['fixit_email'] ) ), 254 ) : '';
	$lang    = isset( $_POST['fixit_lang'] ) ? sanitize_key( wp_unslash( $_POST['fixit_lang'] ) ) : '';
	$lang    = array_key_exists( $lang, fixit_langs() ) ? $lang : fixit_default_lang();

	if ( '' === $name || '' === $company || strlen( preg_replace( '/\D/', '', $phone ) ) < 7 || ! is_email( $email ) ) {
		$go( 'invalid' );
	}

	if ( ! fixit_form_rate_ok() ) {
		$go( 'busy' );
	}

	// Eyni e-poçtdan bir gün ərzində təkrar müraciət: yeni qeyd yaratmırıq, məktubu yeniləyirik.
	$existing = get_posts(
		array(
			'post_type'      => 'fixit_trial',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'meta_key'       => '_ft_email', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $email, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'date_query'     => array( array( 'after' => '1 day ago' ) ),
			'fields'         => 'ids',
		)
	);

	if ( $existing ) {
		$id = (int) $existing[0];
		wp_update_post(
			array(
				'ID'         => $id,
				'post_title' => $name,
			)
		);
		update_post_meta( $id, '_ft_repeats', (int) get_post_meta( $id, '_ft_repeats', true ) + 1 );
	} else {
		$id = wp_insert_post(
			array(
				'post_type'   => 'fixit_trial',
				'post_status' => 'publish',
				'post_title'  => $name,
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			$go( 'error' );
		}
		update_post_meta( $id, '_ft_status', 'new' );
	}

	update_post_meta( $id, '_ft_company', $company );
	update_post_meta( $id, '_ft_phone', $phone );
	update_post_meta( $id, '_ft_email', $email );
	update_post_meta( $id, '_ft_lang', $lang );
	update_post_meta( $id, '_ft_source', esc_url_raw( remove_query_arg( 'fixit_trial', $back ) ) );

	// Standart: müraciət yoxlanılandan sonra admin paneldən bir düymə ilə göndərilir.
	// Avtomatik göndərmə ayarda açıqdırsa (məs. gələcəkdə saytdan satış) dərhal gedir.
	$auto = fixit_trial_settings()['auto_send'];
	$sent = $auto ? fixit_trial_send_customer_mail( $id ) : false;

	if ( ! $existing ) {
		fixit_trial_send_admin_mail( $id );
	}

	if ( ! $auto ) {
		$go( 'received' );
	}

	// Məktub getmədisə ziyarətçiyə «göndərdik» demirik: müraciət saxlanılıb, biz əlaqə saxlayırıq.
	$go( $sent ? 'ok' : 'queued' );
}
add_action( 'admin_post_nopriv_fixit_trial', 'fixit_trial_handle' );
add_action( 'admin_post_fixit_trial', 'fixit_trial_handle' );

/* ============================================================
   4. Şəxsi yükləmə linki
   ============================================================ */

/**
 * Müraciət üçün yeni link yaradır (köhnəsi etibarsız olur).
 *
 * @param int $id Müraciət.
 * @return array{url:string,sig:string,expires:int}
 */
function fixit_trial_new_link( $id ) {
	$key     = wp_generate_password( 32, false, false );
	$expires = time() + fixit_trial_settings()['link_days'] * DAY_IN_SECONDS;

	update_post_meta( $id, '_ft_key', hash( 'sha256', $key ) );
	update_post_meta( $id, '_ft_key_expires', $expires );

	// Linkdə müraciətin dili: müddəti bitəndə və s. çıxan səhifə də həmin dildə olsun.
	$lang = (string) get_post_meta( $id, '_ft_lang', true );
	$base = fixit_add_lang_to_url( fixit_strip_lang_from_url( home_url( '/sinaq-paketi/' ) ), $lang ? $lang : fixit_default_lang() );

	return array(
		'url'     => add_query_arg( array( 'k' => $key ), $base ),
		'sig'     => add_query_arg( array( 'k' => $key, 'f' => 'sig' ), $base ),
		'expires' => $expires,
	);
}

/**
 * Gizli reponun son buraxılışı (10 dəqiqəlik keş).
 *
 * @param bool $force Keşə baxma.
 * @return array|WP_Error {tag, assets: [name => api url]}
 */
function fixit_trial_repo_release( $force = false ) {
	$token = fixit_trial_repo_token();
	if ( '' === $token ) {
		return new WP_Error( 'fixit_trial_token', __( 'Repo açarı yazılmayıb.', 'fixit' ) );
	}

	$cached = $force ? false : get_transient( 'fixit_trial_repo_release' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$response = wp_remote_get(
		'https://api.github.com/repos/' . fixit_trial_settings()['repo'] . '/releases/latest',
		array(
			'timeout' => 12,
			'headers' => array(
				'Accept'               => 'application/vnd.github+json',
				'Authorization'        => 'Bearer ' . $token,
				'X-GitHub-Api-Version' => '2022-11-28',
			),
		)
	);

	$code = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		return new WP_Error(
			'fixit_trial_repo',
			/* translators: %s: HTTP kodu */
			sprintf( __( 'GitHub cavab vermədi (kod %s). Açarın müddətini və icazəsini yoxlayın.', 'fixit' ), $code ? $code : '—' )
		);
	}

	// Fine-grained açarın bitmə tarixi (məs. "2027-11-19 12:00:00 UTC"): adminə vaxtında xatırladılır.
	$expires = strtotime( (string) wp_remote_retrieve_header( $response, 'github-authentication-token-expiration' ) );
	if ( $expires ) {
		update_option( 'fixit_remote_repo_token_exp', $expires, false );
	}

	$data   = json_decode( wp_remote_retrieve_body( $response ), true );
	$assets = array();
	foreach ( (array) ( isset( $data['assets'] ) ? $data['assets'] : array() ) as $asset ) {
		if ( ! empty( $asset['name'] ) && ! empty( $asset['url'] ) ) {
			$assets[ (string) $asset['name'] ] = esc_url_raw( $asset['url'] );
		}
	}

	$release = array(
		'tag'    => isset( $data['tag_name'] ) ? ltrim( (string) $data['tag_name'], 'v' ) : '',
		'assets' => $assets,
	);

	set_transient( 'fixit_trial_repo_release', $release, 10 * MINUTE_IN_SECONDS );

	return $release;
}

/**
 * Buraxılışda serverin paketini (və ya imzasını) tapır.
 *
 * @param array  $release fixit_trial_repo_release().
 * @param string $kind    'pkg' və ya 'sig'.
 * @return array{name:string,url:string}|null
 */
function fixit_trial_find_asset( $release, $kind ) {
	foreach ( $release['assets'] as $name => $url ) {
		$is_pkg = (bool) preg_match( '/^fixitremote-server-linux-x64-[\d.]+\.tar\.gz$/', $name );
		$is_sig = (bool) preg_match( '/^fixitremote-server-linux-x64-[\d.]+\.tar\.gz\.sig$/', $name );

		if ( ( 'pkg' === $kind && $is_pkg ) || ( 'sig' === $kind && $is_sig ) ) {
			return array(
				'name' => $name,
				'url'  => $url,
			);
		}
	}
	return null;
}

/**
 * fixit.az/sinaq-paketi/?k=... — açarı yoxlayır, yükləməni qeyd edir,
 * paketi GitHub-ın qısamüddətli ünvanına yönləndirir.
 */
function fixit_trial_download_route() {
	if ( ! is_404() ) {
		return;
	}

	global $wp;
	$path = strtolower( trim( (string) $wp->request, '/' ) );
	$path = preg_replace( '#^(en|ru)/#', '', $path );
	if ( 'sinaq-paketi' !== $path ) {
		return;
	}

	nocache_headers();

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- açar özü yoxlamadır
	$key  = isset( $_GET['k'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', (string) wp_unslash( $_GET['k'] ) ) : '';
	$kind = isset( $_GET['f'] ) && 'sig' === $_GET['f'] ? 'sig' : 'pkg';
	// phpcs:enable

	$found = '' === $key ? array() : get_posts(
		array(
			'post_type'      => 'fixit_trial',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'meta_key'       => '_ft_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => hash( 'sha256', $key ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'fields'         => 'ids',
		)
	);

	$back = '<p><a href="' . esc_url( fixit_product_url( 'remote' ) . '#sinaq' ) . '">' . esc_html__( 'Yeni link almaq üçün formanı yenidən doldurun', 'fixit' ) . '</a></p>';

	if ( ! $found ) {
		wp_die( '<h1>' . esc_html__( 'Link etibarsızdır', 'fixit' ) . '</h1>' . $back, esc_html__( 'Link etibarsızdır', 'fixit' ), array( 'response' => 403 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- yuxarıda escape olunub
	}

	$id = (int) $found[0];

	if ( (int) get_post_meta( $id, '_ft_key_expires', true ) < time() ) {
		wp_die( '<h1>' . esc_html__( 'Linkin müddəti bitib', 'fixit' ) . '</h1>' . $back, esc_html__( 'Linkin müddəti bitib', 'fixit' ), array( 'response' => 410 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- yuxarıda escape olunub
	}

	$release = fixit_trial_repo_release();
	$asset   = is_wp_error( $release ) ? null : fixit_trial_find_asset( $release, $kind );
	$target  = '';

	if ( $asset ) {
		$response = wp_remote_get(
			$asset['url'],
			array(
				'timeout'     => 12,
				'redirection' => 0,
				'headers'     => array(
					'Accept'               => 'application/octet-stream',
					'Authorization'        => 'Bearer ' . fixit_trial_repo_token(),
					'X-GitHub-Api-Version' => '2022-11-28',
				),
			)
		);
		$code   = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
		$target = in_array( $code, array( 301, 302, 303, 307 ), true ) ? (string) wp_remote_retrieve_header( $response, 'location' ) : '';
	}

	if ( ! $target || ! preg_match( '#^https://#', $target ) ) {
		fixit_trial_alert_admin_unavailable( $id, is_wp_error( $release ) ? $release->get_error_message() : '' );

		$contact = '<p>' . esc_html__( 'Paket hazırda əlçatmazdır. Mütəxəssisimizə xəbər getdi, qısa müddətdə sizinlə əlaqə saxlayacağıq.', 'fixit' ) . '</p>';
		wp_die( '<h1>' . esc_html__( 'Paket hazırda əlçatmazdır', 'fixit' ) . '</h1>' . $contact, esc_html__( 'Paket hazırda əlçatmazdır', 'fixit' ), array( 'response' => 503 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- yuxarıda escape olunub
	}

	if ( 'pkg' === $kind ) {
		update_post_meta( $id, '_ft_downloads', (int) get_post_meta( $id, '_ft_downloads', true ) + 1 );
		update_post_meta( $id, '_ft_last_download', time() );
		update_post_meta( $id, '_ft_version', is_wp_error( $release ) ? '' : $release['tag'] );
	}

	wp_redirect( $target, 302, 'FIXIT' ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- GitHub-ın imzalı, qısamüddətli ünvanı
	exit;
}
add_action( 'template_redirect', 'fixit_trial_download_route', 1 );

/**
 * Paket verilə bilmədi — sahibə bir dəfə (saatda) xəbər verir.
 *
 * @param int    $id     Müraciət.
 * @param string $reason Səbəb.
 */
function fixit_trial_alert_admin_unavailable( $id, $reason ) {
	if ( get_transient( 'fixit_trial_unavailable_alert' ) ) {
		return;
	}
	set_transient( 'fixit_trial_unavailable_alert', 1, HOUR_IN_SECONDS );

	wp_mail(
		fixit_trial_settings()['notify'],
		'[fixit.az] Fixit Remote: sınaq paketi verilə bilmədi',
		"Müştəri sınaq paketini yükləməyə çalışdı, amma paket verilə bilmədi.\n\n"
			. 'Müraciət: ' . get_the_title( $id ) . ' (' . get_post_meta( $id, '_ft_company', true ) . ")\n"
			. 'Səbəb: ' . ( $reason ? $reason : 'GitHub-dan paket alınmadı' ) . "\n\n"
			. 'Yoxlayın: ' . admin_url( 'admin.php?page=fixit-remote-settings' ) . "\n"
	);
}

/* ============================================================
   5. Məktublar
   ============================================================ */

/**
 * Funksiyanı müraciətin dilində işlədir (məktub ziyarətçinin dilində getsin).
 *
 * Admin dili fərqli ola bilər, WordPress-in həmin dil paketi quraşdırılmamış
 * da ola bilər — ona görə temanın tərcümə faylı birbaşa yüklənir.
 *
 * @param string   $lang     az / en / ru.
 * @param callable $callback Funksiya.
 * @return mixed
 */
function fixit_trial_in_lang( $lang, $callback ) {
	$langs  = fixit_langs();
	$locale = isset( $langs[ $lang ] ) ? $langs[ $lang ]['locale'] : 'az';
	$mo     = get_template_directory() . '/languages/' . $locale . '.mo';

	unload_textdomain( 'fixit', true );
	if ( fixit_default_lang() !== $lang && file_exists( $mo ) ) {
		load_textdomain( 'fixit', $mo, $locale );
	}

	$previous             = isset( $GLOBALS['fixit_lang'] ) ? $GLOBALS['fixit_lang'] : null;
	$GLOBALS['fixit_lang'] = $lang;

	try {
		$out = $callback();
	} finally {
		$GLOBALS['fixit_lang'] = $previous;
		unload_textdomain( 'fixit', true );
		load_theme_textdomain( 'fixit', get_template_directory() . '/languages' );
	}

	return $out;
}

/**
 * Müştəriyə məktub: bələdçi, link, qiymətlər.
 *
 * @param int $id Müraciət.
 * @return bool
 */
function fixit_trial_send_customer_mail( $id ) {
	$email = (string) get_post_meta( $id, '_ft_email', true );

	$s    = fixit_trial_settings();
	$link = '' !== fixit_trial_repo_token() ? fixit_trial_new_link( $id ) : null;
	$mail = fixit_trial_build_mail( $id, $link );

	$sent = wp_mail(
		$email,
		$mail['subject'],
		$mail['body'],
		array(
			'Content-Type: text/html; charset=UTF-8',
			'Reply-To: FIXIT <' . $s['notify'] . '>',
		)
	);

	update_post_meta( $id, $sent ? '_ft_mail_sent' : '_ft_mail_failed', time() );
	if ( $sent ) {
		delete_post_meta( $id, '_ft_mail_failed' );
		update_post_meta( $id, '_ft_link_sent', $link ? 1 : 0 );

		if ( in_array( (string) get_post_meta( $id, '_ft_status', true ), array( '', 'new', 'rejected' ), true ) ) {
			update_post_meta( $id, '_ft_status', 'sent' );
		}
	}

	return $sent;
}

/**
 * Müştəriyə gedəcək məktub (mövzu və HTML), müraciətin dilində.
 *
 * Admin paneldəki «Məktuba bax» da bunu göstərir: göndərilən ilə baxılan eynidir.
 *
 * @param int        $id   Müraciət.
 * @param array|null $link Yükləmə linki (önizləmədə nümunə).
 * @return array{subject:string,body:string}
 */
function fixit_trial_build_mail( $id, $link ) {
	$lang = (string) get_post_meta( $id, '_ft_lang', true );
	$name = get_the_title( $id );

	return fixit_trial_in_lang(
		$lang ? $lang : fixit_default_lang(),
		function () use ( $name, $link ) {
			return array(
				/* translators: %d: gün */
				'subject' => sprintf( __( 'Fixit Remote: %d günlük sınaq və qurulum bələdçisi', 'fixit' ), 14 ),
				'body'    => fixit_trial_mail_html( $name, $link ),
			);
		}
	);
}

/**
 * Sahibə bildiriş.
 *
 * @param int $id Müraciət.
 */
function fixit_trial_send_admin_mail( $id ) {
	$body = "Saytdan Fixit Remote 14 günlük sınaq müraciəti gəldi:\n\n"
		. 'Ad: ' . get_the_title( $id ) . "\n"
		. 'Şirkət: ' . get_post_meta( $id, '_ft_company', true ) . "\n"
		. 'Telefon: ' . get_post_meta( $id, '_ft_phone', true ) . "\n"
		. 'E-poçt: ' . get_post_meta( $id, '_ft_email', true ) . "\n"
		. 'Dil: ' . strtoupper( (string) get_post_meta( $id, '_ft_lang', true ) ) . "\n\n"
		. ( fixit_trial_settings()['auto_send']
			? ( get_post_meta( $id, '_ft_mail_sent', true ) ? "Müştəriyə bələdçi avtomatik göndərildi.\n" : "DİQQƏT: müştəriyə məktub göndərilə bilmədi.\n" )
			: "Müştəriyə hələ heç nə getməyib. Müraciəti yoxlayın və «Bələdçini göndər» düyməsini basın.\n" )
		. "\nBax və göndər: " . admin_url( 'post.php?post=' . $id . '&action=edit' ) . "\n";

	$email = (string) get_post_meta( $id, '_ft_email', true );

	wp_mail(
		fixit_trial_settings()['notify'],
		'[fixit.az] Fixit Remote sınaq müraciəti: ' . get_post_meta( $id, '_ft_company', true ),
		$body,
		array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . trim( str_replace( array( '"', '<', '>', ',', ';', ':' ), '', get_the_title( $id ) ) ) . ' <' . $email . '>',
		)
	);
}

/**
 * Qurulum bələdçisinin addımları (məktub və bələdçi səhifəsi üçün ortaq).
 *
 * @return array<int,array{title:string,text:string,code?:string}>
 */
function fixit_trial_guide_steps() {
	return array(
		array(
			'title' => __( 'Serveri hazırlayın', 'fixit' ),
			'text'  => __( 'Linux server və ya virtual maşın (x86_64, systemd): Ubuntu Server 22.04 / 24.04 / 26.04, Debian 12 / 13 və ya RHEL, Rocky, AlmaLinux, Oracle Linux 9 / 10. 200 kompüterə qədər 2 vCPU, 2 GB yaddaş və 20 GB disk kifayətdir; 1000 kompüterə qədər 4 vCPU, 4 GB və 40 GB SSD. Serverə statik IP və ya DNS adı verin: kompüterlər ona bu ünvanla qoşulur.', 'fixit' ),
		),
		array(
			'title' => __( 'Paketi yükləyib açın', 'fixit' ),
			'text'  => __( 'Şəxsi linklə paketi serverə yükləyin, açın və qovluğa keçin. İstəsəniz əvvəlcə sistemi yoxlayın: yoxla.sh heç nəyi dəyişmir.', 'fixit' ),
			'code'  => "mkdir fixit-remote\ntar -xzf fixitremote-server-linux-x64-*.tar.gz -C fixit-remote\ncd fixit-remote/fixitremote-server-*\nsudo bash yoxla.sh --port 8443",
		),
		array(
			'title' => __( 'Quraşdırın', 'fixit' ),
			'text'  => __( 'Quraşdırma bir əmrdir: paketin bütövlüyü yoxlanılır, lazım olan kitabxanalar qurulur, xidmət işə düşür. Panelin standart portu 8443-dür, başqa port üçün --port seçimini verin.', 'fixit' ),
			'code'  => 'sudo bash kur.sh',
		),
		array(
			'title' => __( 'Şəbəkəni yoxlayın', 'fixit' ),
			'text'  => __( 'Kompüterlər və operatorlar serverə 8443/TCP ilə qoşulur, 8442/UDP isə yerli şəbəkədə serverin tapılması üçündür. ufw və ya firewalld açıqdırsa, quraşdırma bu portları özü açır. Kompüterlərin özündə port açmaq lazım deyil: agent serverə özü qoşulur.', 'fixit' ),
		),
		array(
			'title' => __( 'İlk administratoru yaradın', 'fixit' ),
			'text'  => __( 'Windows kompüterdə brauzerlə https://SERVER-ÜNVANI:8443 ünvanını açın və Fixit Remote Client proqramını endirin. Client-də serverin ünvanını yazın, göstərilən sertifikat izini serverdəki ilə tutuşdurun, sonra birdəfəlik qurulum kodunu yazıb admin hesabı yaradın:', 'fixit' ),
			'code'  => "sudo cat /var/lib/fixit-remote/server/sertifikat-barmaq-izi.txt\nsudo cat /var/lib/fixit-remote/server/ilk-qurulum-kodu.txt",
		),
		array(
			'title' => __( 'Kompüterləri qoşun', 'fixit' ),
			'text'  => __( 'Paneldə «Agentlər» bölməsindən Windows MSI-ni endirin: serverin ünvanı və qeydiyyat açarı içindədir, GPO, Intune və ya SCCM ilə səssiz qurulur. Linux kompüterlər üçün ayrıca agent paketi var. Qoşulan kompüter dərhal siyahıda görünür.', 'fixit' ),
		),
		array(
			'title' => __( 'Sınaq və lisenziya', 'fixit' ),
			'text'  => __( 'Sınaq serverin ilk açılışından 14 gün davam edir, kompüter sayı məhdud deyil. Lisenziya üçün paneldə Sistem → Lisenziya səhifəsindəki Qurulum ID-ni bizə göndərin, aldığınız açarı həmin səhifəyə yazın. Server və bütün ayarlar olduğu kimi qalır.', 'fixit' ),
		),
	);
}

/**
 * Məktubun HTML-i (sadə, bütün poçt proqramlarında düzgün görünür).
 *
 * @param string     $name Ad.
 * @param array|null $link Yükləmə linki.
 */
function fixit_trial_mail_html( $name, $link ) {
	$brand   = '#0b63e5';
	$phone   = fixit_get( 'fixit_phone' );
	$wa      = fixit_get( 'fixit_whatsapp' );
	$email   = fixit_trial_settings()['notify'];
	// Bələdçi məktubun dilində (məktub admin-post-dan gedir, orada ünvanlara dil özü düşmür).
	$guide   = fixit_add_lang_to_url( fixit_strip_lang_from_url( fixit_page_url( 'template-remote-guide.php' ) ), fixit_lang() );
	$pricing = fixit_pricing();
	$names   = fixit_pricing_tier_names();

	ob_start();
	?>
	<div style="margin:0;padding:24px 12px;background:#f3f6fb;font-family:Segoe UI,Arial,sans-serif;color:#1d2533">
		<div style="max-width:620px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e3e8f0">
			<div style="background:<?php echo esc_attr( $brand ); ?>;padding:22px 28px;color:#ffffff">
				<div style="font-size:20px;font-weight:700">Fixit Remote</div>
				<div style="opacity:.85;font-size:14px;margin-top:4px"><?php echo esc_html( sprintf( /* translators: %d: gün */ __( '%d günlük pulsuz sınaq', 'fixit' ), 14 ) ); ?></div>
			</div>

			<div style="padding:26px 28px;font-size:15px;line-height:1.6">
				<p style="margin:0 0 14px"><?php echo esc_html( sprintf( /* translators: %s: ad */ __( 'Salam, %s!', 'fixit' ), $name ) ); ?></p>
				<p style="margin:0 0 18px"><?php esc_html_e( 'Fixit Remote ilə maraqlandığınız üçün təşəkkür edirik. Aşağıda serveri öz avadanlığınızda qurmaq üçün lazım olan hər şey var. Sınaq qurulum anından 14 gün davam edir, bütün imkanlar açıqdır.', 'fixit' ); ?></p>

				<?php if ( $link ) : ?>
					<div style="background:#eef4ff;border:1px solid #cfe0ff;border-radius:10px;padding:16px 18px;margin:0 0 22px">
						<div style="font-weight:700;margin-bottom:6px"><?php esc_html_e( 'Şəxsi yükləmə linkiniz', 'fixit' ); ?></div>
						<p style="margin:0 0 12px;font-size:14px">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: tarix */
									__( 'Link yalnız sizin üçündür və %s tarixinədək işləyir.', 'fixit' ),
									wp_date( 'd.m.Y', $link['expires'] )
								)
							);
							?>
						</p>
						<a href="<?php echo esc_url( $link['url'] ); ?>" style="display:inline-block;background:<?php echo esc_attr( $brand ); ?>;color:#ffffff;text-decoration:none;font-weight:600;padding:11px 20px;border-radius:8px"><?php esc_html_e( 'Server paketini yüklə (Linux)', 'fixit' ); ?></a>
						<p style="margin:10px 0 0;font-size:13px;color:#5b6678">
							<?php esc_html_e( 'Paketin rəqəmsal imzası:', 'fixit' ); ?>
							<a href="<?php echo esc_url( $link['sig'] ); ?>" style="color:<?php echo esc_attr( $brand ); ?>">.sig</a>
						</p>
					</div>
				<?php else : ?>
					<div style="background:#fff7e6;border:1px solid #ffe2a8;border-radius:10px;padding:14px 18px;margin:0 0 22px;font-size:14px">
						<?php esc_html_e( 'Server paketinin yükləmə linkini mütəxəssisimiz iş saatı ərzində ayrıca məktubla göndərəcək.', 'fixit' ); ?>
					</div>
				<?php endif; ?>

				<div style="font-size:17px;font-weight:700;margin:0 0 12px"><?php esc_html_e( 'Qurulum addım-addım', 'fixit' ); ?></div>
				<?php foreach ( fixit_trial_guide_steps() as $i => $step ) : ?>
					<div style="margin:0 0 16px">
						<div style="font-weight:600"><?php echo esc_html( ( $i + 1 ) . '. ' . $step['title'] ); ?></div>
						<div style="color:#3b4658;font-size:14px"><?php echo esc_html( $step['text'] ); ?></div>
						<?php if ( ! empty( $step['code'] ) ) : ?>
							<pre style="margin:8px 0 0;background:#0f1724;color:#e6edf6;padding:10px 12px;border-radius:8px;font-size:13px;white-space:pre-wrap;font-family:Consolas,monospace"><?php echo esc_html( $step['code'] ); ?></pre>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>

				<?php if ( $guide && false === strpos( $guide, home_url( '/' ) . '#' ) ) : ?>
					<p style="margin:4px 0 22px;font-size:14px">
						<?php esc_html_e( 'Ətraflı bələdçi və problem olarsa nə etməli:', 'fixit' ); ?>
						<a href="<?php echo esc_url( $guide ); ?>" style="color:<?php echo esc_attr( $brand ); ?>"><?php echo esc_html( preg_replace( '#^https?://#', '', untrailingslashit( $guide ) ) ); ?></a>
					</p>
				<?php endif; ?>

				<div style="font-size:17px;font-weight:700;margin:0 0 10px"><?php esc_html_e( 'Qiymətlər', 'fixit' ); ?></div>
				<?php if ( $pricing['show'] ) : ?>
					<p style="margin:0 0 10px;font-size:14px;color:#3b4658"><?php esc_html_e( 'Qiymət idarə olunan kompüterlərin sayına görədir, operator sayı məhdud deyil. Bütün imkanlar hər paketdədir, aylıq və ya illik ödəyə bilərsiniz.', 'fixit' ); ?></p>
					<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:14px;margin:0 0 8px">
						<?php foreach ( $pricing['tiers'] as $tier ) : ?>
							<tr>
								<td style="padding:8px 0;border-bottom:1px solid #eef1f6"><b><?php echo esc_html( $names[ $tier['key'] ] ); ?></b><br><span style="color:#5b6678"><?php echo esc_html( sprintf( /* translators: %s: say */ __( '%s kompüterə qədər', 'fixit' ), number_format_i18n( $tier['max'] ) ) ); ?></span></td>
								<td style="padding:8px 0;border-bottom:1px solid #eef1f6;text-align:right;white-space:nowrap"><b><?php echo esc_html( fixit_pricing_money( $tier['month'] ) ); ?></b> <?php esc_html_e( 'ayda', 'fixit' ); ?><br><span style="color:#5b6678"><?php echo esc_html( fixit_pricing_money( $tier['year'] ) ); ?> <?php esc_html_e( 'ildə', 'fixit' ); ?></span></td>
							</tr>
						<?php endforeach; ?>
					</table>
					<p style="margin:0 0 6px;font-size:14px"><?php echo esc_html( sprintf( /* translators: %s: say */ __( 'Paketdən artıq kompüter: hər %s kompüter həmin paketin qiyməti ilə.', 'fixit' ), $pricing['step'] ) ); ?></p>
					<p style="margin:0 0 6px;font-size:14px"><?php echo esc_html( sprintf( /* translators: %s: say */ __( '%s kompüterdən çox olduqda xüsusi təklif hazırlayırıq.', 'fixit' ), number_format_i18n( end( $pricing['tiers'] )['max'] ) ) ); ?></p>
				<?php else : ?>
					<p style="margin:0 0 10px;font-size:14px;color:#3b4658"><?php esc_html_e( 'Qiymət idarə olunan kompüterlərin sayına görədir, operator sayı məhdud deyil. Kompüter sayınızı bildirin, dəqiq təklifi göndərək.', 'fixit' ); ?></p>
				<?php endif; ?>
				<p style="margin:0 0 22px;font-size:14px;color:#3b4658"><?php esc_html_e( 'Lisenziya müqavilə əsasında rəsmiləşdirilir, ödəniş bank köçürməsi ilə. Lisenziya üçün bu məktuba cavab yazın və ya zəng edin: müqaviləni və hesab-fakturanı hazırlayaq.', 'fixit' ); ?></p>

				<div style="border-top:1px solid #eef1f6;padding-top:16px;font-size:14px;color:#3b4658">
					<?php esc_html_e( 'Sualınız olsa, bu məktuba cavab yazın və ya zəng edin:', 'fixit' ); ?>
					<b><?php echo esc_html( $phone ); ?></b>
					<?php if ( $wa ) : ?>
						· <a href="https://wa.me/<?php echo esc_attr( fixit_tel( $wa ) ); ?>" style="color:<?php echo esc_attr( $brand ); ?>">WhatsApp</a>
					<?php endif; ?>
					· <a href="mailto:<?php echo esc_attr( $email ); ?>" style="color:<?php echo esc_attr( $brand ); ?>"><?php echo esc_html( $email ); ?></a>
				</div>
			</div>
		</div>
		<p style="text-align:center;font-size:12px;color:#8a94a6;margin:14px 0 0">FIXIT · fixit.az</p>
	</div>
	<?php
	return (string) ob_get_clean();
}
