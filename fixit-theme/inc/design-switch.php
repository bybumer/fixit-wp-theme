<?php
/**
 * Sayt dizaynı: əsas (əvvəlki) və yeni (test).
 *
 * Yeni dizayn sınaq üçündür: sahib istədiyi vaxt əsas dizayna qayıda bilər,
 * yeni reliz gözləmədən. Əvvəlki dizaynın üslubu temanın içində saxlanılır
 * (assets/css/klassik.css, v1.15.5-in style.css-i), şablonlar ikisi üçün eynidir.
 *
 * Görünüş → Sayt dizaynı:
 * - hamı üçün dizayn seçilir (seçiləndə LiteSpeed keşi təmizlənir);
 * - admin hər iki dizayna ziyarətçilərə təsir etmədən baxa bilər
 *   (?fixit_dizayn=klassik / yeni, yalnız admin üçün, keşlənmir).
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const FIXIT_DESIGN_COOKIE = 'fixit_dizayn_baxis';

/**
 * Dizaynlar.
 */
function fixit_designs() {
	return array(
		'yeni'    => __( 'Yeni dizayn (test)', 'fixit' ),
		'klassik' => __( 'Əsas dizayn (əvvəlki)', 'fixit' ),
	);
}

/**
 * Hamı üçün seçilmiş dizayn.
 */
function fixit_design_saved() {
	$d = get_option( 'fixit_design', 'yeni' );
	return array_key_exists( $d, fixit_designs() ) ? $d : 'yeni';
}

/**
 * Adminin baxış rejimi (yalnız özü görür); yoxdursa boş.
 */
function fixit_design_preview() {
	if ( ! is_user_logged_in() || ! current_user_can( 'edit_theme_options' ) ) {
		return '';
	}
	$d = isset( $_COOKIE[ FIXIT_DESIGN_COOKIE ] ) ? sanitize_key( wp_unslash( $_COOKIE[ FIXIT_DESIGN_COOKIE ] ) ) : '';
	return array_key_exists( $d, fixit_designs() ) ? $d : '';
}

/**
 * Bu sorğu üçün dizayn.
 */
function fixit_design() {
	$preview = fixit_design_preview();
	return '' !== $preview ? $preview : fixit_design_saved();
}

/**
 * ?fixit_dizayn=klassik|yeni|sifirla — adminin baxışı (sessiya kukisi).
 */
function fixit_design_preview_param() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- yalnız adminin öz baxışı, heç nə yadda saxlanmır
	if ( ! isset( $_GET['fixit_dizayn'] ) || ! is_user_logged_in() || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$d    = sanitize_key( wp_unslash( $_GET['fixit_dizayn'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$opts = array(
		'expires'  => 0,
		'path'     => COOKIEPATH ? COOKIEPATH : '/',
		'secure'   => is_ssl(),
		'httponly' => true,
		'samesite' => 'Lax',
	);

	if ( array_key_exists( $d, fixit_designs() ) ) {
		setcookie( FIXIT_DESIGN_COOKIE, $d, $opts );
		$_COOKIE[ FIXIT_DESIGN_COOKIE ] = $d;
	} else {
		$opts['expires'] = time() - HOUR_IN_SECONDS;
		setcookie( FIXIT_DESIGN_COOKIE, '', $opts );
		unset( $_COOKIE[ FIXIT_DESIGN_COOKIE ] );
	}
}
add_action( 'init', 'fixit_design_preview_param', 1 );

/**
 * Baxış rejimindəki səhifə keşə düşməsin.
 */
function fixit_design_preview_nocache() {
	if ( '' === fixit_design_preview() || is_admin() ) {
		return;
	}
	nocache_headers();
	do_action( 'litespeed_control_set_nocache', 'fixit dizayn baxışı' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- LiteSpeed Cache-in öz hook-u
}
add_action( 'template_redirect', 'fixit_design_preview_nocache', 0 );

/**
 * Üslub və şrift faylları dizayna görə.
 *
 * @return array{css:string,ver:string,fonts:string}
 */
function fixit_design_assets() {
	if ( 'klassik' === fixit_design() ) {
		return array(
			'css'   => get_template_directory_uri() . '/assets/css/klassik.css',
			'ver'   => fixit_ver( 'assets/css/klassik.css' ),
			'fonts' => 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
		);
	}
	return array(
		'css'   => get_stylesheet_uri(),
		'ver'   => fixit_ver( 'style.css' ),
		'fonts' => 'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap',
	);
}

/**
 * <body>-də dizaynın sinfi.
 *
 * @param string[] $classes Siniflər.
 */
function fixit_design_body_class( $classes ) {
	$classes[] = 'dizayn-' . fixit_design();
	return $classes;
}
add_filter( 'body_class', 'fixit_design_body_class' );

/* ============================================================
   Admin: Görünüş → Sayt dizaynı
   ============================================================ */

/**
 * Menyu.
 */
function fixit_design_menu() {
	add_theme_page( __( 'Sayt dizaynı', 'fixit' ), __( 'Sayt dizaynı', 'fixit' ), 'edit_theme_options', 'fixit-design', 'fixit_design_page' );
}
add_action( 'admin_menu', 'fixit_design_menu' );

/**
 * Baxış linki.
 *
 * @param string $d Dizayn və ya «sifirla».
 */
function fixit_design_preview_url( $d ) {
	return add_query_arg( 'fixit_dizayn', $d, home_url( '/' ) );
}

/**
 * Səhifə.
 */
function fixit_design_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$saved = fixit_design_saved();
	?>
	<div class="wrap" style="max-width:820px">
		<h1><?php esc_html_e( 'Sayt dizaynı', 'fixit' ); ?></h1>
		<?php if ( isset( $_GET['saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- yalnız bildiriş ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Dizayn dəyişdi. Sayt keşi təmizləndi; Cloudflare işlədirsinizsə, orada da keşi təmizləyin.', 'fixit' ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'Yeni dizayn sınaq üçündür. Bəyənməsəniz, burada əsas dizayna qayıdın: dəyişiklik dərhal bütün ziyarətçilərə tətbiq olunur, mətnlər və ayarlar eyni qalır.', 'fixit' ); ?></p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="fixit_design_save">
			<?php wp_nonce_field( 'fixit_design_save' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Ziyarətçilər görür', 'fixit' ); ?></th>
					<td>
						<?php foreach ( fixit_designs() as $key => $label ) : ?>
							<p><label><input type="radio" name="design" value="<?php echo esc_attr( $key ); ?>" <?php checked( $saved, $key ); ?>> <strong><?php echo esc_html( $label ); ?></strong></label></p>
						<?php endforeach; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Özünüz baxın', 'fixit' ); ?></th>
					<td>
						<p>
							<a class="button" href="<?php echo esc_url( fixit_design_preview_url( 'yeni' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Yeni dizayna bax', 'fixit' ); ?></a>
							<a class="button" href="<?php echo esc_url( fixit_design_preview_url( 'klassik' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Əsas dizayna bax', 'fixit' ); ?></a>
							<a class="button-link" href="<?php echo esc_url( fixit_design_preview_url( 'sifirla' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Baxışı bağla', 'fixit' ); ?></a>
						</p>
						<p class="description"><?php esc_html_e( 'Baxış yalnız sizdə görünür (admin kimi daxil olduğunuz brauzerdə), ziyarətçilərə təsir etmir. Brauzer bağlananda özü sönür.', 'fixit' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Yadda saxla', 'fixit' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Seçimi yadda saxlayır və sayt keşini təmizləyir.
 */
function fixit_design_save() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'İcazə yoxdur.', 'fixit' ) );
	}
	check_admin_referer( 'fixit_design_save' );

	$d = isset( $_POST['design'] ) ? sanitize_key( wp_unslash( $_POST['design'] ) ) : 'yeni';
	update_option( 'fixit_design', array_key_exists( $d, fixit_designs() ) ? $d : 'yeni' );

	do_action( 'litespeed_purge_all' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- LiteSpeed Cache-in öz hook-u

	wp_safe_redirect( add_query_arg( 'saved', 1, admin_url( 'themes.php?page=fixit-design' ) ) );
	exit;
}
add_action( 'admin_post_fixit_design_save', 'fixit_design_save' );

/**
 * Sayt üzərindəki admin panelində: hansı dizayn göstərilir, tez keçid.
 *
 * @param WP_Admin_Bar $bar Panel.
 */
function fixit_design_admin_bar( $bar ) {
	if ( is_admin() || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$designs = fixit_designs();
	$preview = fixit_design_preview();
	$title   = '' !== $preview
		/* translators: %s: dizayn */
		? sprintf( __( 'Baxış: %s', 'fixit' ), $designs[ $preview ] )
		: $designs[ fixit_design_saved() ];

	$bar->add_node(
		array(
			'id'    => 'fixit-design',
			'title' => esc_html( $title ),
			'href'  => admin_url( 'themes.php?page=fixit-design' ),
		)
	);
	foreach ( array( 'yeni', 'klassik' ) as $d ) {
		$bar->add_node(
			array(
				'parent' => 'fixit-design',
				'id'     => 'fixit-design-' . $d,
				/* translators: %s: dizayn */
				'title'  => esc_html( sprintf( __( '%s: bax', 'fixit' ), $designs[ $d ] ) ),
				'href'   => fixit_design_preview_url( $d ),
			)
		);
	}
	if ( '' !== $preview ) {
		$bar->add_node(
			array(
				'parent' => 'fixit-design',
				'id'     => 'fixit-design-off',
				'title'  => esc_html__( 'Baxışı bağla', 'fixit' ),
				'href'   => fixit_design_preview_url( 'sifirla' ),
			)
		);
	}
}
add_action( 'admin_bar_menu', 'fixit_design_admin_bar', 100 );
