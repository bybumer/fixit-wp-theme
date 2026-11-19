<?php
/**
 * Admin panel: sınaq müraciətləri və «Qiymətlər və sınaq» ayarları.
 *
 * Fixit Remote menyusunda:
 * - Sınaq müraciətləri — siyahı (vəziyyət, məktub, paketin yüklənməsi), hər
 *   müraciətdə vəziyyət və qeyd, «Məktubu yenidən göndər»;
 * - Qiymətlər və sınaq — paketlərin rəqəmləri, saytda göstərmək, göndərmə qaydası,
 *   yükləmə linkinin müddəti, gizli repoya giriş açarı.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   1. Menyu
   ============================================================ */
/**
 * Sıra: İcmal, Sınaq müraciətləri (post növünün öz bəndi), Qiymətlər və sınaq.
 * «İcmal» post növünün bəndindən (prioritet 10) əvvəl əlavə olunmalıdır.
 */
function fixit_trial_admin_menu_first() {
	add_submenu_page( 'fixit-remote', __( 'Fixit Remote', 'fixit' ), __( 'İcmal', 'fixit' ), 'manage_options', 'fixit-remote', 'fixit_remote_admin_page' );
}
add_action( 'admin_menu', 'fixit_trial_admin_menu_first', 9 );

function fixit_trial_admin_menu() {
	add_submenu_page( 'fixit-remote', __( 'Qiymətlər və sınaq', 'fixit' ), __( 'Qiymətlər və sınaq', 'fixit' ), 'manage_options', 'fixit-remote-settings', 'fixit_trial_settings_page' );
}
add_action( 'admin_menu', 'fixit_trial_admin_menu', 20 );

/**
 * Menyuda yeni müraciətlərin sayı.
 */
function fixit_trial_menu_badge() {
	global $submenu;

	$new = fixit_trial_count( 'new' );
	if ( ! $new || empty( $submenu['fixit-remote'] ) ) {
		return;
	}

	foreach ( $submenu['fixit-remote'] as $i => $item ) {
		if ( 'edit.php?post_type=fixit_trial' === $item[2] ) {
			$submenu['fixit-remote'][ $i ][0] .= ' <span class="awaiting-mod">' . (int) $new . '</span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}
}
add_action( 'admin_menu', 'fixit_trial_menu_badge', 99 );

/**
 * Vəziyyətə görə müraciət sayı.
 *
 * @param string $status Vəziyyət ('' — hamısı).
 */
function fixit_trial_count( $status = '' ) {
	$args = array(
		'post_type'      => 'fixit_trial',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	);
	if ( $status ) {
		$args['meta_key']   = '_ft_status'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$args['meta_value'] = $status; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	}
	$q = new WP_Query( $args );
	return (int) $q->found_posts;
}

/* ============================================================
   2. Siyahı
   ============================================================ */
function fixit_trial_columns() {
	return array(
		'cb'           => '<input type="checkbox">',
		'ft_company'   => __( 'Şirkət', 'fixit' ),
		'title'        => __( 'Ad, soyad', 'fixit' ),
		'ft_contact'   => __( 'Əlaqə', 'fixit' ),
		'ft_status'    => __( 'Vəziyyət', 'fixit' ),
		'ft_mail'      => __( 'Məktub', 'fixit' ),
		'ft_downloads' => __( 'Paket yüklənib', 'fixit' ),
		'date'         => __( 'Tarix', 'fixit' ),
	);
}
add_filter( 'manage_fixit_trial_posts_columns', 'fixit_trial_columns' );

/**
 * Vəziyyət nişanı.
 *
 * @param string $status Vəziyyət.
 */
function fixit_trial_badge( $status ) {
	$colors = array(
		'new'       => 'background:#e7f0ff;color:#0b63e5',
		'contacted' => 'background:#fff4e0;color:#8a5300',
		'installed' => 'background:#efe7ff;color:#5b2bb5',
		'customer'  => 'background:#e6f6ea;color:#1a7f37',
		'lost'      => 'background:#f0f0f1;color:#50575e',
		'sent'      => 'background:#e6f4fb;color:#0a6a96',
		'rejected'  => 'background:#fbeaea;color:#a12622',
	);
	$labels = fixit_trial_statuses();
	$status = isset( $labels[ $status ] ) ? $status : 'new';

	return sprintf(
		'<span style="display:inline-block;padding:2px 9px;border-radius:10px;font-size:12px;font-weight:600;%1$s">%2$s</span>',
		esc_attr( $colors[ $status ] ),
		esc_html( $labels[ $status ] )
	);
}

function fixit_trial_column_content( $column, $id ) {
	switch ( $column ) {
		case 'ft_company':
			printf( '<strong><a href="%1$s">%2$s</a></strong>', esc_url( get_edit_post_link( $id ) ), esc_html( get_post_meta( $id, '_ft_company', true ) ) );
			break;

		case 'ft_contact':
			$phone = (string) get_post_meta( $id, '_ft_phone', true );
			$email = (string) get_post_meta( $id, '_ft_email', true );
			printf(
				'<a href="tel:%1$s">%2$s</a><br><a href="mailto:%3$s">%3$s</a>',
				esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ),
				esc_html( $phone ),
				esc_html( $email )
			);
			break;

		case 'ft_status':
			echo fixit_trial_badge( (string) get_post_meta( $id, '_ft_status', true ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- funksiya içində escape olunub
			break;

		case 'ft_mail':
			$sent   = (int) get_post_meta( $id, '_ft_mail_sent', true );
			$failed = (int) get_post_meta( $id, '_ft_mail_failed', true );
			if ( $failed && $failed >= $sent ) {
				echo '<span style="color:#b32d2e">' . esc_html__( 'Göndərilmədi', 'fixit' ) . '</span>';
			} elseif ( $sent ) {
				echo esc_html( wp_date( 'd.m H:i', $sent ) );
				echo get_post_meta( $id, '_ft_link_sent', true ) ? '' : '<br><span style="color:#8a5300">' . esc_html__( 'linksiz', 'fixit' ) . '</span>';
			} else {
				echo '—';
			}
			break;

		case 'ft_downloads':
			$n    = (int) get_post_meta( $id, '_ft_downloads', true );
			$last = (int) get_post_meta( $id, '_ft_last_download', true );
			echo $n ? esc_html( $n . ' · ' . wp_date( 'd.m H:i', $last ) ) : '<span style="color:#8c8f94">' . esc_html__( 'hələ yox', 'fixit' ) . '</span>';
			break;
	}
}
add_action( 'manage_fixit_trial_posts_custom_column', 'fixit_trial_column_content', 10, 2 );

/**
 * Vəziyyət süzgəci (siyahının üstündəki keçidlər).
 *
 * @param array $views Keçidlər.
 */
function fixit_trial_views( $views ) {
	$base    = admin_url( 'edit.php?post_type=fixit_trial' );
	$current = isset( $_GET['ft_status'] ) ? sanitize_key( wp_unslash( $_GET['ft_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$out = array(
		'all' => sprintf( '<a href="%1$s"%2$s>%3$s <span class="count">(%4$d)</span></a>', esc_url( $base ), '' === $current ? ' class="current"' : '', esc_html__( 'Hamısı', 'fixit' ), fixit_trial_count() ),
	);
	foreach ( fixit_trial_statuses() as $key => $label ) {
		$out[ $key ] = sprintf(
			'<a href="%1$s"%2$s>%3$s <span class="count">(%4$d)</span></a>',
			esc_url( add_query_arg( 'ft_status', $key, $base ) ),
			$key === $current ? ' class="current"' : '',
			esc_html( $label ),
			fixit_trial_count( $key )
		);
	}
	if ( isset( $views['trash'] ) ) {
		$out['trash'] = $views['trash'];
	}
	return $out;
}
add_filter( 'views_edit-fixit_trial', 'fixit_trial_views' );

/**
 * Süzgəci sorğuya tətbiq edir.
 *
 * @param WP_Query $q Sorğu.
 */
function fixit_trial_filter_query( $q ) {
	if ( ! is_admin() || ! $q->is_main_query() || 'fixit_trial' !== $q->get( 'post_type' ) ) {
		return;
	}
	$status = isset( $_GET['ft_status'] ) ? sanitize_key( wp_unslash( $_GET['ft_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $status && array_key_exists( $status, fixit_trial_statuses() ) ) {
		$q->set( 'meta_key', '_ft_status' );
		$q->set( 'meta_value', $status );
	}
}
add_action( 'pre_get_posts', 'fixit_trial_filter_query' );

/**
 * Müştəriyə bələdçi artıq göndərilibmi.
 *
 * @param int $id Müraciət.
 */
function fixit_trial_is_sent( $id ) {
	return (bool) get_post_meta( $id, '_ft_mail_sent', true );
}

/**
 * Sətir əməliyyatları: bələdçini göndər / yenidən göndər, rədd et.
 *
 * @param array   $actions Əməliyyatlar.
 * @param WP_Post $post    Müraciət.
 */
function fixit_trial_row_actions( $actions, $post ) {
	if ( 'fixit_trial' !== $post->post_type ) {
		return $actions;
	}
	unset( $actions['inline hide-if-no-js'] );

	$sent = fixit_trial_is_sent( $post->ID );

	$actions['ft_send'] = sprintf(
		'<a href="%1$s" onclick="return confirm(%2$s)"><strong>%3$s</strong></a>',
		esc_url( fixit_trial_send_url( $post->ID ) ),
		esc_attr( wp_json_encode( sprintf( /* translators: %s: e-poçt */ __( 'Bələdçi %s ünvanına göndərilsin?', 'fixit' ), get_post_meta( $post->ID, '_ft_email', true ) ) ) ),
		esc_html( $sent ? __( 'Yenidən göndər', 'fixit' ) : __( 'Bələdçini göndər', 'fixit' ) )
	);
	if ( ! $sent && 'rejected' !== get_post_meta( $post->ID, '_ft_status', true ) ) {
		$actions['ft_reject'] = sprintf( '<a href="%1$s" style="color:#b32d2e">%2$s</a>', esc_url( fixit_trial_reject_url( $post->ID ) ), esc_html__( 'Rədd et', 'fixit' ) );
	}
	return $actions;
}
add_filter( 'post_row_actions', 'fixit_trial_row_actions', 10, 2 );

function fixit_trial_send_url( $id ) {
	return wp_nonce_url( admin_url( 'admin-post.php?action=fixit_trial_resend&post=' . (int) $id ), 'fixit_trial_resend_' . (int) $id );
}

/**
 * Köhnə ad (bələdçi linklərində və bildiriş məktublarında qala bilər).
 *
 * @param int $id Müraciət.
 */
function fixit_trial_resend_url( $id ) {
	return fixit_trial_send_url( $id );
}

function fixit_trial_reject_url( $id ) {
	return wp_nonce_url( admin_url( 'admin-post.php?action=fixit_trial_reject&post=' . (int) $id ), 'fixit_trial_reject_' . (int) $id );
}

function fixit_trial_preview_url( $id ) {
	return wp_nonce_url( admin_url( 'admin-post.php?action=fixit_trial_preview&post=' . (int) $id ), 'fixit_trial_preview_' . (int) $id );
}

/**
 * Admin əməliyyatları üçün ümumi yoxlama: icazə, nonce, müraciət.
 *
 * @param string $nonce Nonce adının əvvəli.
 * @return int Müraciət.
 */
function fixit_trial_admin_action_id( $nonce ) {
	$id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

	if ( ! current_user_can( 'manage_options' ) || 'fixit_trial' !== get_post_type( $id ) ) {
		wp_die( esc_html__( 'İcazə yoxdur.', 'fixit' ) );
	}
	check_admin_referer( $nonce . $id );

	return $id;
}

/**
 * Bələdçini göndərir (hər dəfə yeni yükləmə linki ilə).
 */
function fixit_trial_resend() {
	$id    = fixit_trial_admin_action_id( 'fixit_trial_resend_' );
	$again = fixit_trial_is_sent( $id );
	$ok    = fixit_trial_send_customer_mail( $id );

	$back = wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=fixit_trial' );
	wp_safe_redirect( add_query_arg( 'ft_notice', $ok ? ( $again ? 'resent' : 'sent' ) : 'mail_failed', $back ) );
	exit;
}
add_action( 'admin_post_fixit_trial_resend', 'fixit_trial_resend' );

/**
 * Müraciəti rədd edir (saxta, test və s.): müştəriyə heç nə getmir.
 */
function fixit_trial_reject() {
	$id = fixit_trial_admin_action_id( 'fixit_trial_reject_' );
	update_post_meta( $id, '_ft_status', 'rejected' );

	$back = wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=fixit_trial' );
	wp_safe_redirect( add_query_arg( 'ft_notice', 'rejected', $back ) );
	exit;
}
add_action( 'admin_post_fixit_trial_reject', 'fixit_trial_reject' );

/**
 * Məktubun önizləməsi: müştəri nə görəcəksə, eynisi (link nümunədir).
 */
function fixit_trial_preview() {
	$id   = fixit_trial_admin_action_id( 'fixit_trial_preview_' );
	$days = fixit_trial_settings()['link_days'];
	$link = fixit_trial_sign_key()
		? array(
			'url'     => '#onizleme',
			'sig'     => '#onizleme',
			'expires' => time() + $days * DAY_IN_SECONDS,
		)
		: null;
	$mail = fixit_trial_build_mail( $id, $link );

	nocache_headers();
	header( 'Content-Type: text/html; charset=UTF-8' );
	echo '<!doctype html><html><head><meta charset="utf-8"><title>' . esc_html( $mail['subject'] ) . '</title></head><body style="margin:0;background:#f3f6fb">';
	printf(
		'<div style="font:14px/1.5 Segoe UI,Arial,sans-serif;max-width:620px;margin:0 auto;padding:16px 12px 0;color:#1d2533"><b>%1$s</b> %2$s<br><b>%3$s</b> %4$s<br><span style="color:#8a5300">%5$s</span></div>',
		esc_html__( 'Kimə:', 'fixit' ),
		esc_html( (string) get_post_meta( $id, '_ft_email', true ) ),
		esc_html__( 'Mövzu:', 'fixit' ),
		esc_html( $mail['subject'] ),
		esc_html__( 'Önizləmə: yükləmə linki göndərilən anda yaradılır.', 'fixit' )
	);
	echo $mail['body']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixit_trial_mail_html içində escape olunub
	echo '</body></html>';
	exit;
}
add_action( 'admin_post_fixit_trial_preview', 'fixit_trial_preview' );

/**
 * Admin bildirişləri.
 */
function fixit_trial_admin_notices() {
	$notice = isset( $_GET['ft_notice'] ) ? sanitize_key( wp_unslash( $_GET['ft_notice'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$map    = array(
		'sent'        => array( 'success', __( 'Bələdçi müştəriyə göndərildi.', 'fixit' ) ),
		'resent'      => array( 'success', __( 'Məktub yenidən göndərildi.', 'fixit' ) ),
		'rejected'    => array( 'success', __( 'Müraciət rədd edildi. Müştəriyə heç nə göndərilmədi.', 'fixit' ) ),
		'mail_failed' => array( 'error', __( 'Məktub göndərilə bilmədi. Saytın poçt ayarlarını yoxlayın.', 'fixit' ) ),
		'saved'       => array( 'success', __( 'Ayarlar yadda saxlanıldı.', 'fixit' ) ),
	);
	if ( isset( $map[ $notice ] ) ) {
		printf( '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>', esc_attr( $map[ $notice ][0] ), esc_html( $map[ $notice ][1] ) );
	}
}
add_action( 'admin_notices', 'fixit_trial_admin_notices' );

/* ============================================================
   3. Müraciətin səhifəsi
   ============================================================ */
function fixit_trial_meta_boxes() {
	add_meta_box( 'fixit_trial_box', __( 'Müraciət', 'fixit' ), 'fixit_trial_box', 'fixit_trial', 'normal', 'high' );
	add_meta_box( 'fixit_trial_actions', __( 'Yoxla və göndər', 'fixit' ), 'fixit_trial_action_box', 'fixit_trial', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'fixit_trial_meta_boxes' );

function fixit_trial_box( $post ) {
	$id      = $post->ID;
	$status  = (string) get_post_meta( $id, '_ft_status', true );
	$sent    = (int) get_post_meta( $id, '_ft_mail_sent', true );
	$failed  = (int) get_post_meta( $id, '_ft_mail_failed', true );
	$expires = (int) get_post_meta( $id, '_ft_key_expires', true );
	$n       = (int) get_post_meta( $id, '_ft_downloads', true );
	$last    = (int) get_post_meta( $id, '_ft_last_download', true );
	$ver     = (string) get_post_meta( $id, '_ft_version', true );
	$phone   = (string) get_post_meta( $id, '_ft_phone', true );
	$email   = (string) get_post_meta( $id, '_ft_email', true );
	$langs   = fixit_langs();
	$lang    = (string) get_post_meta( $id, '_ft_lang', true );

	wp_nonce_field( 'fixit_trial_save', 'fixit_trial_nonce' );

	$rows = array(
		__( 'Şirkət', 'fixit' )            => esc_html( get_post_meta( $id, '_ft_company', true ) ),
		__( 'Telefon', 'fixit' )           => '<a href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a>',
		__( 'E-poçt', 'fixit' )            => '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>',
		__( 'Dil', 'fixit' )               => esc_html( isset( $langs[ $lang ] ) ? $langs[ $lang ]['name'] : $lang ),
		__( 'Göndərildiyi səhifə', 'fixit' ) => esc_html( (string) get_post_meta( $id, '_ft_source', true ) ),
		__( 'Məktub', 'fixit' )            => $failed && $failed >= $sent
			? '<span style="color:#b32d2e">' . esc_html__( 'göndərilmədi', 'fixit' ) . ' (' . esc_html( wp_date( 'd.m.Y H:i', $failed ) ) . ')</span>'
			: ( $sent ? esc_html( wp_date( 'd.m.Y H:i', $sent ) ) . ( get_post_meta( $id, '_ft_link_sent', true ) ? '' : ' · ' . esc_html__( 'yükləmə linki olmadan', 'fixit' ) ) : '—' ),
		__( 'Yükləmə linki', 'fixit' )     => $expires
			? esc_html( sprintf( /* translators: %s: tarix */ __( '%s tarixinədək etibarlıdır', 'fixit' ), wp_date( 'd.m.Y', $expires ) ) )
			: esc_html__( 'verilməyib', 'fixit' ),
		__( 'Paket yüklənib', 'fixit' )    => $n
			? esc_html( sprintf( /* translators: 1: say, 2: tarix, 3: versiya */ __( '%1$d dəfə, son: %2$s (versiya %3$s)', 'fixit' ), $n, wp_date( 'd.m.Y H:i', $last ), $ver ? $ver : '—' ) )
			: esc_html__( 'hələ yüklənməyib', 'fixit' ),
	);
	$repeats = (int) get_post_meta( $id, '_ft_repeats', true );
	if ( $repeats ) {
		$rows[ __( 'Təkrar müraciət', 'fixit' ) ] = esc_html( (string) $repeats );
	}
	?>
	<table class="form-table" role="presentation">
		<?php foreach ( $rows as $label => $value ) : ?>
			<tr><th scope="row"><?php echo esc_html( $label ); ?></th><td><?php echo $value; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- yuxarıda escape olunub ?></td></tr>
		<?php endforeach; ?>
		<tr>
			<th scope="row"><label for="ft_status"><?php esc_html_e( 'Vəziyyət', 'fixit' ); ?></label></th>
			<td>
				<select name="ft_status" id="ft_status">
					<?php foreach ( fixit_trial_statuses() as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status ? $status : 'new', $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="ft_note"><?php esc_html_e( 'Qeyd', 'fixit' ); ?></label></th>
			<td><textarea name="ft_note" id="ft_note" rows="4" class="large-text" placeholder="<?php esc_attr_e( 'Zəng, görüş, cihaz sayı, təklif...', 'fixit' ); ?>"><?php echo esc_textarea( (string) get_post_meta( $id, '_ft_note', true ) ); ?></textarea></td>
		</tr>
	</table>
	<?php
}

/**
 * Yan qutu: müraciəti yoxlamaq üçün ipucları və əsas düymələr.
 *
 * @param WP_Post $post Müraciət.
 */
function fixit_trial_action_box( $post ) {
	$id     = $post->ID;
	$email  = (string) get_post_meta( $id, '_ft_email', true );
	$domain = strtolower( (string) substr( strrchr( $email, '@' ), 1 ) );
	$free   = in_array( $domain, array( 'gmail.com', 'mail.ru', 'yandex.ru', 'yandex.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'live.com', 'icloud.com', 'inbox.ru', 'list.ru', 'bk.ru', 'rambler.ru', 'proton.me', 'protonmail.com' ), true );
	$sent   = fixit_trial_is_sent( $id );
	$key    = (bool) fixit_trial_sign_key();
	$checks = array(
		$free
			/* translators: %s: domen */
			? array( 'warn', sprintf( __( 'Pulsuz poçt (%s): şirkətin adını və telefonu yoxlayın', 'fixit' ), $domain ) )
			/* translators: %s: domen */
			: array( 'ok', sprintf( __( 'Şirkət domeni: %s', 'fixit' ), $domain ) ),
	);
	if ( (int) get_post_meta( $id, '_ft_repeats', true ) ) {
		$checks[] = array( 'warn', __( 'Bir gün ərzində təkrar müraciət edib', 'fixit' ) );
	}
	if ( ! $key ) {
		$checks[] = array( 'warn', __( 'Saytın imza açarı yaradılmayıb: məktubda yükləmə linki olmayacaq', 'fixit' ) );
	}
	if ( function_exists( 'fixit_smtp_ready' ) && ! fixit_smtp_ready() ) {
		$checks[] = array( 'warn', __( 'SMTP qurulmayıb: məktub spama düşə bilər (Parametrlər → E-poçt)', 'fixit' ) );
	}
	?>
	<ul style="margin:0 0 12px">
		<?php foreach ( $checks as $check ) : ?>
			<li style="margin:0 0 6px;padding-left:20px;position:relative">
				<span style="position:absolute;left:0;color:<?php echo 'ok' === $check[0] ? '#1a7f37' : '#8a5300'; ?>"><?php echo 'ok' === $check[0] ? '✓' : '!'; ?></span>
				<?php echo esc_html( $check[1] ); ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<p style="margin:0 0 8px">
		<a class="button button-primary button-large" style="width:100%;text-align:center" href="<?php echo esc_url( fixit_trial_send_url( $id ) ); ?>" onclick="return confirm(<?php echo esc_attr( wp_json_encode( sprintf( /* translators: %s: e-poçt */ __( 'Bələdçi %s ünvanına göndərilsin?', 'fixit' ), $email ) ) ); ?>)">
			<?php echo esc_html( $sent ? __( 'Yenidən göndər (yeni linklə)', 'fixit' ) : __( 'Bələdçini göndər', 'fixit' ) ); ?>
		</a>
	</p>
	<p style="margin:0 0 8px">
		<a class="button" style="width:100%;text-align:center" href="<?php echo esc_url( fixit_trial_preview_url( $id ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Məktuba bax', 'fixit' ); ?></a>
	</p>
	<?php if ( ! $sent && 'rejected' !== get_post_meta( $id, '_ft_status', true ) ) : ?>
		<p style="margin:0">
			<a class="button-link" style="color:#b32d2e" href="<?php echo esc_url( fixit_trial_reject_url( $id ) ); ?>"><?php esc_html_e( 'Rədd et (saxta və ya sınaq müraciəti)', 'fixit' ); ?></a>
		</p>
	<?php endif; ?>
	<?php
}

/**
 * Vəziyyət və qeydi yadda saxlayır.
 *
 * @param int $id Müraciət.
 */
function fixit_trial_save( $id ) {
	if ( ! isset( $_POST['fixit_trial_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fixit_trial_nonce'] ) ), 'fixit_trial_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$status = isset( $_POST['ft_status'] ) ? sanitize_key( wp_unslash( $_POST['ft_status'] ) ) : 'new';
	if ( array_key_exists( $status, fixit_trial_statuses() ) ) {
		update_post_meta( $id, '_ft_status', $status );
	}
	if ( isset( $_POST['ft_note'] ) ) {
		update_post_meta( $id, '_ft_note', sanitize_textarea_field( wp_unslash( $_POST['ft_note'] ) ) );
	}
}
add_action( 'save_post_fixit_trial', 'fixit_trial_save' );

/* ============================================================
   4. «Qiymətlər və sınaq» ayarları
   ============================================================ */
function fixit_trial_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$p     = fixit_pricing();
	$s     = fixit_trial_settings();
	$names = fixit_pricing_tier_names();
	$pub   = fixit_trial_public_key();
	$test  = null;

	// «Mərkəzi yoxla»: mərkəz saytın biletini tanıyır və paketi verə bilir.
	if ( isset( $_GET['ft_test'], $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'fixit_trial_test' ) ) {
		$test = fixit_trial_server_check( true );
	}
	?>
	<div class="wrap" style="max-width:980px">
		<h1><?php esc_html_e( 'Qiymətlər və sınaq', 'fixit' ); ?></h1>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="fixit_trial_settings_save">
			<?php wp_nonce_field( 'fixit_trial_settings' ); ?>

			<h2><?php esc_html_e( 'Qiymətlər', 'fixit' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Qiymət cihaz başına, ayda yazılır. Saytda yuxarıda «Aylıq / İllik» seçimi var: aylıqda paketin aylıq məbləği, illikdə illik məbləği görünür. Paketdən artıq cihaz 10-luq əlavə ilə həmin paketin qiymətinə hesablanır.', 'fixit' ); ?></p>

			<table class="widefat striped" style="max-width:720px;margin:12px 0 18px">
				<thead><tr>
					<th><?php esc_html_e( 'Paket', 'fixit' ); ?></th>
					<th><?php esc_html_e( 'Cihaza qədər', 'fixit' ); ?></th>
					<th><?php esc_html_e( 'Cihaz başına, ayda (AZN)', 'fixit' ); ?></th>
					<th><?php esc_html_e( 'Paket, ayda', 'fixit' ); ?></th>
					<th><?php esc_html_e( 'Paket, ildə', 'fixit' ); ?></th>
				</tr></thead>
				<tbody>
					<?php foreach ( $p['tiers'] as $i => $tier ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $names[ $tier['key'] ] ); ?></strong></td>
							<td><input type="number" min="1" name="tiers[<?php echo esc_attr( $i ); ?>][max]" value="<?php echo esc_attr( $tier['max'] ); ?>" style="width:100px"></td>
							<td><input type="number" min="0" step="0.05" name="tiers[<?php echo esc_attr( $i ); ?>][price]" value="<?php echo esc_attr( $tier['price'] ); ?>" style="width:100px"></td>
							<td><?php echo esc_html( fixit_pricing_money( $tier['month'] ) ); ?></td>
							<td><?php echo esc_html( fixit_pricing_money( $tier['year'] ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="gift"><?php esc_html_e( 'İllik ödənişdə hədiyyə', 'fixit' ); ?></label></th>
					<td>
						<select name="gift" id="gift">
							<?php for ( $g = 0; $g <= 3; $g++ ) : ?>
								<option value="<?php echo esc_attr( $g ); ?>" <?php selected( $p['gift'], $g ); ?>>
									<?php
									echo esc_html(
										0 === $g
											? __( 'Yoxdur: illik = 12 ay', 'fixit' )
											/* translators: 1: hədiyyə ay, 2: ödənilən ay, 3: faiz */
											: sprintf( __( '%1$d ay hədiyyə: illik = %2$d ay (%3$d%% qənaət)', 'fixit' ), $g, 12 - $g, (int) round( $g / 12 * 100 ) )
									);
									?>
								</option>
							<?php endfor; ?>
						</select>
						<p class="description"><?php esc_html_e( 'İllik ödəyənə endirim. Seçiləndə saytda «İllik ödəniş» düyməsinin yanında qənaət faizi görünür.', 'fixit' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Çoxillik müqavilə endirimi', 'fixit' ); ?></th>
					<td>
						<label><?php esc_html_e( '2 il:', 'fixit' ); ?> <input type="number" min="0" max="50" name="multi2" value="<?php echo esc_attr( $p['multi2'] ); ?>" style="width:70px"> %</label>
						&nbsp;&nbsp;
						<label><?php esc_html_e( '3 il:', 'fixit' ); ?> <input type="number" min="0" max="50" name="multi3" value="<?php echo esc_attr( $p['multi3'] ); ?>" style="width:70px"> %</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Saytda', 'fixit' ); ?></th>
					<td>
						<label><input type="checkbox" name="show" value="1" <?php checked( $p['show'] ); ?>> <strong><?php esc_html_e( 'Qiymətləri saytda və sınaq məktubunda göstər', 'fixit' ); ?></strong></label>
						<p class="description"><?php esc_html_e( 'Söndürülü olanda saytda paketlər qiymətsiz, «qiymət sorğu ilə» kimi görünür, məktubda da qiymət olmur. Rəqəmləri yoxlayın, sonra yandırın.', 'fixit' ); ?></p>
						<?php if ( function_exists( 'fixit_product_url' ) ) : ?>
							<p><a href="<?php echo esc_url( fixit_product_url( 'remote' ) . '#qiymetler' ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Saytda bax', 'fixit' ); ?> ↗</a></p>
						<?php endif; ?>
					</td>
				</tr>
			</table>

			<h2 style="margin-top:28px"><?php esc_html_e( '14 günlük sınaq', 'fixit' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Müraciət admin paneldə yaranır və sizə bildiriş gəlir. Yoxlayıb «Bələdçini göndər» basanda müştəriyə hazır məktub gedir: qurulum bələdçisi, qiymətlər və şəxsi yükləmə linki. Server paketini Fixit mərkəzi (id.fixit.az) verir: link sayt vasitəsilə keçir və hər yükləmə müraciətdə qeyd olunur.', 'fixit' ); ?></p>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Göndərmə', 'fixit' ); ?></th>
					<td>
						<label><input type="checkbox" name="auto_send" value="1" <?php checked( $s['auto_send'] ); ?>> <?php esc_html_e( 'Bələdçini yoxlamadan, müraciət gələn kimi avtomatik göndər', 'fixit' ); ?></label>
						<p class="description"><?php esc_html_e( 'Söndürülü olanda (tövsiyə olunur) müraciəti özünüz yoxlayıb bir düymə ilə göndərirsiniz. Saytdan birbaşa satışa keçəndə açmaq olar.', 'fixit' ); ?></p>
						<p><label><?php esc_html_e( 'Linkin müddəti:', 'fixit' ); ?> <input type="number" min="1" max="60" name="link_days" value="<?php echo esc_attr( $s['link_days'] ); ?>" style="width:70px"> <?php esc_html_e( 'gün', 'fixit' ); ?></label></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="notify"><?php esc_html_e( 'Bildiriş ünvanı', 'fixit' ); ?></label></th>
					<td><input type="email" name="notify" id="notify" value="<?php echo esc_attr( $s['notify'] ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Yeni müraciət haqqında məktub bura gəlir. Müştəri məktuba cavab yazanda da bu ünvana düşür.', 'fixit' ); ?></p>
					<?php if ( function_exists( 'fixit_smtp_status' ) ) : ?>
						<?php $fixit_mail = fixit_smtp_status(); ?>
						<p style="color:<?php echo 'ok' === $fixit_mail[0] ? '#1a7f37' : '#8a5300'; ?>"><?php echo esc_html( $fixit_mail[1] ); ?> · <a href="<?php echo esc_url( fixit_smtp_page_url() ); ?>"><?php esc_html_e( 'E-poçt ayarları', 'fixit' ); ?></a></p>
					<?php endif; ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="server"><?php esc_html_e( 'Paketi verən mərkəz', 'fixit' ); ?></label></th>
					<td>
						<input type="url" name="server" id="server" value="<?php echo esc_attr( $s['server'] ); ?>" class="regular-text">
						<p class="description"><?php esc_html_e( 'Müştəri server paketini bu ünvandan yükləyir. Sayt linki yoxlayır, yükləməni qeyd edir və müştərini 30 dəqiqəlik imzalı biletlə mərkəzə yönləndirir. GitHub açarı lazım deyil.', 'fixit' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Saytın imza açarı', 'fixit' ); ?></th>
					<td>
						<?php if ( '' === $pub ) : ?>
							<p><?php esc_html_e( 'Açar hələ yaradılmayıb. Açar bir dəfə yaradılır və bütün müştərilər üçün işləyir: gizli hissəsi yalnız bu saytdadır, ictimai hissəsini mərkəzə yazırsınız.', 'fixit' ); ?></p>
							<p><button type="submit" class="button button-primary" name="sign_key_create" value="1"><?php esc_html_e( 'Açarı yarat', 'fixit' ); ?></button></p>
						<?php else : ?>
							<p><?php esc_html_e( 'İctimai açar (gizli deyil):', 'fixit' ); ?></p>
							<input type="text" readonly class="large-text code" value="<?php echo esc_attr( $pub ); ?>" onclick="this.select()">
							<p class="description" style="margin-top:12px"><?php esc_html_e( 'Mərkəzə bir dəfə yazılır. id.fixit.az serverində (root kimi):', 'fixit' ); ?></p>
							<pre style="background:#1d2327;color:#f0f0f1;padding:10px 12px;border-radius:4px;white-space:pre-wrap;word-break:break-all;max-width:760px"><?php echo esc_html( fixit_trial_central_commands( $pub ) ); ?></pre>
							<p><a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'ft_test', 1, admin_url( 'admin.php?page=fixit-remote-settings' ) ), 'fixit_trial_test' ) ); ?>"><?php esc_html_e( 'Mərkəzi yoxla', 'fixit' ); ?></a></p>
							<?php if ( null !== $test ) : ?>
								<?php if ( is_wp_error( $test ) ) : ?>
									<div class="notice notice-error inline"><p><?php echo esc_html( $test->get_error_message() ); ?></p></div>
								<?php else : ?>
									<div class="notice notice-success inline"><p>
										<?php
										/* translators: 1: versiya, 2: faylın adı */
										echo esc_html( sprintf( __( 'Hazırdır: mərkəz saytın açarını tanıyır, versiya %1$s (%2$s).', 'fixit' ), $test['version'], $test['package'] ) );
										?>
									</p></div>
								<?php endif; ?>
							<?php endif; ?>
							<p style="margin-top:14px"><label><input type="checkbox" name="sign_key_renew" value="1"> <?php esc_html_e( 'Açarı yenilə: köhnə açar dərhal işləməz olur, yeni ictimai açarı mərkəzə yenidən yazmaq lazımdır', 'fixit' ); ?></label></p>
						<?php endif; ?>
					</td>
				</tr>
			</table>

			<?php submit_button( __( 'Yadda saxla', 'fixit' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Mərkəzdə saytın ictimai açarını yazan əmrlər (açar gizli deyil).
 *
 * @param string $pub İctimai açar (base64url).
 */
function fixit_trial_central_commands( $pub ) {
	return "sed -i '/^FIXITREMOTE_TRIAL_KEY=/d' /etc/fixitremote-central/update.env\n"
		. "echo 'FIXITREMOTE_TRIAL_KEY=" . $pub . "' >> /etc/fixitremote-central/update.env\n"
		. 'systemctl restart fixitremote-central';
}

/**
 * Köhnə GitHub açarı (1.15.2-yə qədər sınaq paketi GitHub-dan verilirdi): artıq
 * istifadə olunmur, bazada saxlanmır.
 */
function fixit_trial_drop_github_token() {
	if ( false !== get_option( 'fixit_remote_repo_token', false ) || false !== get_option( 'fixit_remote_repo_token_exp', false ) ) {
		delete_option( 'fixit_remote_repo_token' );
		delete_option( 'fixit_remote_repo_token_exp' );
		delete_transient( 'fixit_trial_repo_release' );
	}
}
add_action( 'admin_init', 'fixit_trial_drop_github_token' );

/**
 * Ayarları yadda saxlayır.
 */
function fixit_trial_settings_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'İcazə yoxdur.', 'fixit' ) );
	}
	check_admin_referer( 'fixit_trial_settings' );

	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- aşağıda tiplə təmizlənir
	$tiers = array();
	$raw   = isset( $_POST['tiers'] ) && is_array( $_POST['tiers'] ) ? wp_unslash( $_POST['tiers'] ) : array();
	foreach ( fixit_pricing_defaults()['tiers'] as $i => $def ) {
		$tiers[] = array(
			'max'   => isset( $raw[ $i ]['max'] ) ? absint( $raw[ $i ]['max'] ) : $def['max'],
			'price' => isset( $raw[ $i ]['price'] ) ? round( (float) str_replace( ',', '.', (string) $raw[ $i ]['price'] ), 2 ) : $def['price'],
		);
	}
	// phpcs:enable

	update_option(
		'fixit_remote_pricing',
		array(
			'show'   => ! empty( $_POST['show'] ),
			'gift'   => isset( $_POST['gift'] ) ? min( 3, absint( $_POST['gift'] ) ) : 0,
			'multi2' => isset( $_POST['multi2'] ) ? absint( $_POST['multi2'] ) : 0,
			'multi3' => isset( $_POST['multi3'] ) ? absint( $_POST['multi3'] ) : 0,
			'tiers'  => $tiers,
		),
		false
	);

	update_option(
		'fixit_remote_trial',
		array(
			'auto_send' => ! empty( $_POST['auto_send'] ),
			'link_days' => isset( $_POST['link_days'] ) ? absint( $_POST['link_days'] ) : 14,
			'notify'    => isset( $_POST['notify'] ) ? sanitize_email( wp_unslash( $_POST['notify'] ) ) : '',
			'server'    => isset( $_POST['server'] ) ? untrailingslashit( esc_url_raw( wp_unslash( $_POST['server'] ), array( 'https' ) ) ) : '',
		),
		false
	);

	if ( ! empty( $_POST['sign_key_renew'] ) ) {
		delete_option( 'fixit_trial_sign_key' );
	}
	if ( ! empty( $_POST['sign_key_create'] ) || ! empty( $_POST['sign_key_renew'] ) ) {
		fixit_trial_sign_key( true );
	}
	delete_transient( 'fixit_trial_server_check' );

	wp_safe_redirect( add_query_arg( 'ft_notice', 'saved', admin_url( 'admin.php?page=fixit-remote-settings' ) ) );
	exit;
}
add_action( 'admin_post_fixit_trial_settings_save', 'fixit_trial_settings_save' );
