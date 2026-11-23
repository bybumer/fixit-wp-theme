<?php
/**
 * FIXIT admin paneli: bir menyu və icmal səhifəsi.
 *
 * Menyu «FIXIT» temanın gündəlik işlədilən səhifələrini bir yerə toplayır:
 * İcmal, SEO, SEO auditi, Form mesajları, Demo statistikası, E-poçt (SMTP).
 * Fixit Remote öz menyusunda qalır (sınaq müraciətləri, qiymətlər).
 *
 * İcmal səhifəsi yalnız saxlanmış məlumatı göstərir (xarici sorğu getmir):
 * yeni müraciətlər, yükləmələr, son SEO auditi, poçt, məzənnə, tema versiyası.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menyu (alt bəndlər öz fayllarında əlavə olunur: parent 'fixit').
 */
function fixit_panel_menu() {
	add_menu_page( __( 'FIXIT', 'fixit' ), __( 'FIXIT', 'fixit' ), 'edit_posts', 'fixit', 'fixit_panel_page', 'dashicons-admin-site-alt3', 3 );
	add_submenu_page( 'fixit', __( 'FIXIT icmalı', 'fixit' ), __( 'İcmal', 'fixit' ), 'edit_posts', 'fixit', 'fixit_panel_page' );
}
add_action( 'admin_menu', 'fixit_panel_menu', 5 );

/**
 * Köhnə ünvanlar (səhifə başqa menyuya keçib).
 */
function fixit_panel_old_urls() {
	global $pagenow;
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- yalnız yönləndirmə
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	if ( 'options-general.php' === $pagenow && 'fixit-smtp' === $page ) {
		wp_safe_redirect( admin_url( 'admin.php?page=fixit-smtp' ) );
		exit;
	}
}
add_action( 'admin_init', 'fixit_panel_old_urls' );

/**
 * Post növünün sayı (status və tarixə görə).
 *
 * @param string $type    Post növü.
 * @param array  $extra   Əlavə şərtlər (WP_Query).
 */
function fixit_panel_count( $type, $extra = array() ) {
	$q = new WP_Query(
		array_merge(
			array(
				'post_type'      => $type,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => false,
			),
			$extra
		)
	);
	return (int) $q->found_posts;
}

/**
 * Kart.
 *
 * @param string $title  Başlıq.
 * @param string $value  Böyük rəqəm / mətn.
 * @param string $sub    Altdakı mətn.
 * @param string $link   Keçid.
 * @param string $state  ok / warn / bad / ''.
 */
function fixit_panel_card( $title, $value, $sub, $link = '', $state = '' ) {
	?>
	<div class="fxp-card<?php echo $state ? ' fxp-' . esc_attr( $state ) : ''; ?>">
		<h3><?php echo esc_html( $title ); ?></h3>
		<div class="fxp-value"><?php echo esc_html( $value ); ?></div>
		<p><?php echo esc_html( $sub ); ?></p>
		<?php if ( $link ) : ?>
			<a href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Aç', 'fixit' ); ?> →</a>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * İcmal səhifəsi.
 */
function fixit_panel_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$week = array(
		'date_query' => array(
			array(
				'after'     => '7 days ago',
				'inclusive' => true,
			),
		),
	);

	$msg_week  = post_type_exists( 'fixit_message' ) ? fixit_panel_count( 'fixit_message', $week ) : 0;
	$msg_all   = post_type_exists( 'fixit_message' ) ? fixit_panel_count( 'fixit_message' ) : 0;
	$trial_new = post_type_exists( 'fixit_trial' ) ? fixit_panel_count(
		'fixit_trial',
		array(
			'meta_key'   => '_ft_status', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value' => 'new', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	) : 0;
	$trial_all = post_type_exists( 'fixit_trial' ) ? fixit_panel_count( 'fixit_trial' ) : 0;
	$dl        = function_exists( 'fixit_remote_download_totals' ) ? fixit_remote_download_totals() : null;
	$audit     = get_option( 'fixit_seo_audit_last' );
	$fx        = get_option( 'fixit_fx_rates' );
	$release   = get_site_transient( 'fixit_update_release' );
	$central   = get_transient( 'fixit_trial_server_check' );
	$mail      = function_exists( 'fixit_smtp_status' ) ? fixit_smtp_status() : null;
	$lang      = function_exists( 'fixit_admin_lang' ) ? fixit_admin_lang() : 'en';
	?>
	<style>
		.fxp{max-width:1180px}
		.fxp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:16px;margin:18px 0 26px}
		.fxp-card{background:#fff;border:1px solid #dcdcde;border-top:3px solid #2271b1;border-radius:4px;padding:14px 16px 16px}
		.fxp-card.fxp-ok{border-top-color:#00a32a}.fxp-card.fxp-warn{border-top-color:#dba617}.fxp-card.fxp-bad{border-top-color:#d63638}
		.fxp-card h3{margin:0 0 8px;font-size:13px;color:#50575e;font-weight:600;text-transform:uppercase;letter-spacing:.03em}
		.fxp-value{font-size:26px;font-weight:600;line-height:1.2;color:#1d2327}
		.fxp-card p{margin:6px 0 10px;color:#50575e}
		.fxp-card a{text-decoration:none;font-weight:600}
		.fxp-links{display:flex;flex-wrap:wrap;gap:8px}
		.fxp-hist{display:flex;align-items:flex-end;gap:4px;height:46px;margin:8px 0 4px}
		.fxp-hist span{display:block;width:14px;background:#2271b1;border-radius:2px 2px 0 0}
	</style>
	<div class="wrap fxp">
		<h1><?php esc_html_e( 'FIXIT icmalı', 'fixit' ); ?></h1>
		<p><?php esc_html_e( 'Saytın əsas göstəriciləri bir yerdə. Rəqəmlər saxlanmış məlumatdandır, səhifə açılanda xarici xidmətə sorğu getmir.', 'fixit' ); ?></p>

		<div class="fxp-grid">
			<?php
			fixit_panel_card(
				__( 'Form mesajları', 'fixit' ),
				(string) $msg_week,
				/* translators: %d: cəmi */
				sprintf( __( 'son 7 gündə · cəmi %d', 'fixit' ), $msg_all ),
				admin_url( 'edit.php?post_type=fixit_message' ),
				$msg_week ? 'warn' : ''
			);
			fixit_panel_card(
				__( 'Sınaq müraciətləri', 'fixit' ),
				(string) $trial_new,
				/* translators: %d: cəmi */
				sprintf( __( 'yoxlanmamış · cəmi %d', 'fixit' ), $trial_all ),
				admin_url( 'edit.php?post_type=fixit_trial' ),
				$trial_new ? 'warn' : 'ok'
			);
			if ( $dl ) {
				fixit_panel_card(
					__( 'Fixit Remote yükləmələri', 'fixit' ),
					(string) $dl['d7'],
					/* translators: 1: bu gün, 2: 30 gün */
					sprintf( __( 'son 7 gündə · bu gün %1$d · 30 gündə %2$d', 'fixit' ), $dl['today'], $dl['d30'] ),
					admin_url( 'admin.php?page=fixit-remote' )
				);
			}

			if ( is_array( $audit ) && isset( $audit['score'] ) ) {
				$score = (int) $audit['score'];
				fixit_panel_card(
					__( 'SEO auditi', 'fixit' ),
					$score . '/100',
					/* translators: 1: tarix, 2: kritik, 3: xəbərdarlıq */
					sprintf( __( '%1$s · kritik %2$d · xəbərdarlıq %3$d', 'fixit' ), wp_date( 'd.m.Y H:i', (int) $audit['time'] ), (int) $audit['critical'], (int) $audit['warning'] ),
					admin_url( 'admin.php?page=fixit-seo-audit' ),
					$score >= 85 ? 'ok' : ( $score >= 60 ? 'warn' : 'bad' )
				);
			} else {
				fixit_panel_card( __( 'SEO auditi', 'fixit' ), '—', __( 'Hələ işlədilməyib. Saytın bütün səhifələrini yoxlayın.', 'fixit' ), admin_url( 'admin.php?page=fixit-seo-audit' ), 'warn' );
			}

			if ( $mail ) {
				fixit_panel_card( __( 'E-poçt', 'fixit' ), 'ok' === $mail[0] ? 'SMTP' : 'mail()', $mail[1], admin_url( 'admin.php?page=fixit-smtp' ), 'ok' === $mail[0] ? 'ok' : 'warn' );
			}

			$latest = is_array( $release ) && ! empty( $release['version'] ) ? $release['version'] : '';
			fixit_panel_card(
				__( 'Tema', 'fixit' ),
				FIXIT_VERSION,
				$latest && version_compare( $latest, FIXIT_VERSION, '>' )
					/* translators: %s: versiya */
					? sprintf( __( 'Yeni versiya var: %s', 'fixit' ), $latest )
					: __( 'Ən son versiyadır (son yoxlamaya görə)', 'fixit' ),
				admin_url( 'themes.php?page=fixit-setup' ),
				$latest && version_compare( $latest, FIXIT_VERSION, '>' ) ? 'warn' : 'ok'
			);

			if ( is_array( $fx ) && ! empty( $fx['rates'] ) ) {
				fixit_panel_card(
					__( 'Məzənnə (AMB)', 'fixit' ),
					'1 USD = ' . number_format_i18n( (float) $fx['rates']['USD'], 4 ) . ' AZN',
					/* translators: 1: tarix, 2: RUB */
					sprintf( __( '%1$s · 100 RUB = %2$s AZN', 'fixit' ), $fx['date'], number_format_i18n( (float) $fx['rates']['RUB'] * 100, 4 ) ),
					'',
					'ok'
				);
			}

			if ( is_array( $central ) ) {
				fixit_panel_card(
					__( 'Sınaq paketi (id.fixit.az)', 'fixit' ),
					isset( $central['error'] ) ? __( 'Xəta', 'fixit' ) : ( isset( $central['version'] ) ? $central['version'] : '' ),
					isset( $central['error'] ) ? $central['error'] : __( 'Mərkəz saytın açarını tanıyır', 'fixit' ),
					admin_url( 'admin.php?page=fixit-remote-settings' ),
					isset( $central['error'] ) ? 'bad' : 'ok'
				);
			}
			?>

			<div class="fxp-card">
				<h3><?php esc_html_e( 'Admin panelin dili', 'fixit' ); ?></h3>
				<div class="fxp-value"><?php echo 'az' === $lang ? 'Azərbaycanca' : 'English'; ?></div>
				<p><?php esc_html_e( 'Hər istifadəçi özü seçir. Saytın ön hissəsinin dilinə təsir etmir.', 'fixit' ); ?></p>
				<a class="button" href="<?php echo esc_url( fixit_admin_lang_url( 'en' ) ); ?>">English</a>
				<a class="button" href="<?php echo esc_url( fixit_admin_lang_url( 'az' ) ); ?>">Azərbaycanca</a>
			</div>
		</div>

		<?php if ( is_array( $audit ) && ! empty( $audit['history'] ) && count( $audit['history'] ) > 1 ) : ?>
			<h2><?php esc_html_e( 'SEO balının dəyişməsi', 'fixit' ); ?></h2>
			<div class="fxp-hist" aria-hidden="true">
				<?php foreach ( $audit['history'] as $h ) : ?>
					<span style="height:<?php echo esc_attr( max( 3, (int) $h['score'] * 0.46 ) ); ?>px" title="<?php echo esc_attr( wp_date( 'd.m.Y', (int) $h['time'] ) . ': ' . (int) $h['score'] ); ?>"></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Tez keçidlər', 'fixit' ); ?></h2>
		<div class="fxp-links">
			<a class="button" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Əlaqə, loqo, ana səhifə mətni', 'fixit' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=fixit_product' ) ); ?>"><?php esc_html_e( 'Məhsullar', 'fixit' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=fixit_service' ) ); ?>"><?php esc_html_e( 'Xidmətlər', 'fixit' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>"><?php esc_html_e( 'Səhifələr', 'fixit' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php esc_html_e( 'Menyular', 'fixit' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=fixit-remote-settings' ) ); ?>"><?php esc_html_e( 'Qiymətlər və sınaq', 'fixit' ); ?></a>
			<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Sayta bax', 'fixit' ); ?> ↗</a>
		</div>
	</div>
	<?php
}

/**
 * FIXIT menyusunda bəndlərin sırası.
 */
function fixit_panel_menu_order() {
	global $submenu;
	if ( empty( $submenu['fixit'] ) ) {
		return;
	}
	$order = array( 'fixit', 'fixit-seo-audit', 'fixit-seo', 'edit.php?post_type=fixit_message', 'fixit-demo-stats', 'fixit-smtp' );
	usort(
		$submenu['fixit'],
		function ( $a, $b ) use ( $order ) {
			$ia = array_search( $a[2], $order, true );
			$ib = array_search( $b[2], $order, true );
			return ( false === $ia ? 99 : $ia ) - ( false === $ib ? 99 : $ib );
		}
	);
}
add_action( 'admin_menu', 'fixit_panel_menu_order', 999 );
