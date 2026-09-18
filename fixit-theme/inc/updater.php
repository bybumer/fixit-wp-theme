<?php
/**
 * Temanın GitHub relizlərindən avtomatik yenilənməsi.
 *
 * Necə işləyir:
 *  1) WordPress vaxtaşırı (təxminən 12 saatdan bir) temaların yenilənməsini yoxlayır.
 *  2) Bu fayl həmin yoxlamaya GitHub-dakı son relizi əlavə edir.
 *  3) Relizin versiyası quraşdırılmış versiyadan yüksəkdirsə, WordPress onu
 *     adi "Yeniləmə mövcuddur" kimi göstərir. Yeniləmə WordPress-in öz
 *     quraşdırıcısı ilə aparılır — ZIP əl ilə yüklənən kimi.
 *
 * Yeni versiya buraxmaq üçün: style.css və functions.php-də versiyanı artırın,
 * sonra `python reliz-yarat.py` işlədin (README-də izah var).
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** GitHub reposu (sahib/ad). */
define( 'FIXIT_UPDATE_REPO', 'bybumer/fixit-wp-theme' );

/** Relizə əlavə olunan arxivin adı. */
define( 'FIXIT_UPDATE_ASSET', 'fixit-theme.zip' );

/** Temanın qovluq adı — WordPress onu bu adla tanıyır. */
define( 'FIXIT_THEME_SLUG', 'fixit-theme' );

/**
 * GitHub-dan son relizi gətirir (keşlə).
 *
 * GitHub açar olmadan saatda 60 sorğuya icazə verir; paylaşılan hostinqdə
 * bu limit başqa saytlarla bölünə bilər. Ona görə nəticə 6 saat saxlanılır,
 * xəta olarsa 1 saat gözlənilir — GitHub-a lazımsız sorğu getmir.
 *
 * @param bool $force Keşi keçib dərhal yoxlasın?
 * @return array|null ['version','package','url','notes'] və ya null.
 */
function fixit_update_fetch_release( $force = false ) {
	$cache_key = 'fixit_update_release';

	if ( ! $force ) {
		$cached = get_site_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached ? $cached : null; // Boş massiv = son yoxlama uğursuz olub.
		}
	}

	$response = wp_remote_get(
		'https://api.github.com/repos/' . FIXIT_UPDATE_REPO . '/releases/latest',
		array(
			'timeout' => 10,
			'headers' => array(
				'Accept'     => 'application/vnd.github+json',
				'User-Agent' => 'FIXIT-Theme-Updater',
			),
		)
	);

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		set_site_transient( $cache_key, array(), HOUR_IN_SECONDS );
		update_option( 'fixit_update_last_error', is_wp_error( $response ) ? $response->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code( $response ), false );
		return null;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( empty( $data['tag_name'] ) || empty( $data['assets'] ) ) {
		set_site_transient( $cache_key, array(), HOUR_IN_SECONDS );
		update_option( 'fixit_update_last_error', __( 'Relizdə arxiv tapılmadı.', 'fixit' ), false );
		return null;
	}

	// Relizə əlavə olunmuş fixit-theme.zip faylını tapırıq.
	$package = '';
	foreach ( $data['assets'] as $asset ) {
		if ( isset( $asset['name'], $asset['browser_download_url'] ) && FIXIT_UPDATE_ASSET === $asset['name'] ) {
			$package = $asset['browser_download_url'];
			break;
		}
	}

	if ( ! $package ) {
		set_site_transient( $cache_key, array(), HOUR_IN_SECONDS );
		update_option( 'fixit_update_last_error', __( 'Relizdə fixit-theme.zip yoxdur.', 'fixit' ), false );
		return null;
	}

	$release = array(
		'version' => ltrim( $data['tag_name'], 'vV' ),
		'package' => $package,
		'url'     => isset( $data['html_url'] ) ? $data['html_url'] : 'https://github.com/' . FIXIT_UPDATE_REPO . '/releases',
		'notes'   => isset( $data['body'] ) ? (string) $data['body'] : '',
	);

	set_site_transient( $cache_key, $release, 6 * HOUR_IN_SECONDS );
	update_option( 'fixit_update_last_check', time(), false );
	delete_option( 'fixit_update_last_error' );

	return $release;
}

/**
 * WordPress-in yeniləmə siyahısına relizi əlavə edir.
 *
 * @param object $transient WordPress-in tema yeniləmə məlumatı.
 * @return object
 */
function fixit_update_inject( $transient ) {
	if ( ! is_object( $transient ) ) {
		$transient = new stdClass();
	}

	$theme   = wp_get_theme( FIXIT_THEME_SLUG );
	$current = $theme->exists() ? $theme->get( 'Version' ) : FIXIT_VERSION;
	$release = fixit_update_fetch_release();

	if ( ! $release ) {
		return $transient;
	}

	$item = array(
		'theme'        => FIXIT_THEME_SLUG,
		'new_version'  => $release['version'],
		'url'          => $release['url'],
		'package'      => $release['package'],
		'requires'     => '6.0',
		'requires_php' => '7.4',
	);

	if ( version_compare( $release['version'], $current, '>' ) ) {
		$transient->response[ FIXIT_THEME_SLUG ] = $item;
		unset( $transient->no_update[ FIXIT_THEME_SLUG ] );
	} else {
		// "Yeniləmə yoxdur" siyahısında olsun ki, avtomatik yeniləmə açarı görünsün.
		$transient->no_update[ FIXIT_THEME_SLUG ] = $item;
		unset( $transient->response[ FIXIT_THEME_SLUG ] );
	}

	return $transient;
}
add_filter( 'pre_set_site_transient_update_themes', 'fixit_update_inject' );

/**
 * Yeniləmədən sonra keşi təmizləyir ki, köhnə məlumat qalmasın.
 */
function fixit_update_after_upgrade( $upgrader, $options ) {
	if ( isset( $options['type'], $options['themes'] ) && 'theme' === $options['type'] && in_array( FIXIT_THEME_SLUG, (array) $options['themes'], true ) ) {
		delete_site_transient( 'fixit_update_release' );
	}
}
add_action( 'upgrader_process_complete', 'fixit_update_after_upgrade', 10, 2 );

/* ============================================================
   Admin: vəziyyət və "İndi yoxla" düyməsi
   ============================================================ */

/**
 * Yeniləmə vəziyyəti blokunu çap edir (FIXIT quraşdırma səhifəsində).
 */
function fixit_update_status_box() {
	if ( ! current_user_can( 'update_themes' ) ) {
		return;
	}

	$checked_now = false;

	if ( isset( $_POST['fixit_update_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fixit_update_nonce'] ) ), 'fixit_update_check' ) ) {
		fixit_update_fetch_release( true );
		delete_site_transient( 'update_themes' );
		wp_update_themes();
		$checked_now = true;
	}

	$theme      = wp_get_theme( FIXIT_THEME_SLUG );
	$current    = $theme->exists() ? $theme->get( 'Version' ) : FIXIT_VERSION;
	$release    = fixit_update_fetch_release();
	$last_check = (int) get_option( 'fixit_update_last_check' );
	$last_error = get_option( 'fixit_update_last_error' );
	$has_update = $release && version_compare( $release['version'], $current, '>' );
	$auto_on    = in_array( FIXIT_THEME_SLUG, (array) get_site_option( 'auto_update_themes', array() ), true );
	?>
	<h2 style="margin-top:36px"><?php esc_html_e( 'Tema yeniləmələri', 'fixit' ); ?></h2>

	<?php if ( $checked_now ) : ?>
		<div class="notice notice-info inline"><p><?php esc_html_e( 'GitHub yoxlanıldı.', 'fixit' ); ?></p></div>
	<?php endif; ?>

	<table class="widefat striped" style="max-width:720px">
		<tbody>
			<tr>
				<td style="width:220px"><?php esc_html_e( 'Quraşdırılmış versiya', 'fixit' ); ?></td>
				<td><strong><?php echo esc_html( $current ); ?></strong></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Son buraxılmış versiya', 'fixit' ); ?></td>
				<td>
					<?php if ( $release ) : ?>
						<strong><?php echo esc_html( $release['version'] ); ?></strong>
						— <a href="<?php echo esc_url( $release['url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'dəyişikliklər', 'fixit' ); ?></a>
					<?php else : ?>
						<span style="color:#b32d2e"><?php esc_html_e( 'yoxlanıla bilmədi', 'fixit' ); ?></span>
						<?php if ( $last_error ) : ?>
							<small>(<?php echo esc_html( $last_error ); ?>)</small>
						<?php endif; ?>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Vəziyyət', 'fixit' ); ?></td>
				<td>
					<?php if ( $has_update ) : ?>
						<strong style="color:#b26200"><?php esc_html_e( 'Yeniləmə mövcuddur', 'fixit' ); ?></strong>
						— <a href="<?php echo esc_url( admin_url( 'update-core.php' ) ); ?>"><?php esc_html_e( 'indi yenilə', 'fixit' ); ?></a>
					<?php elseif ( $release ) : ?>
						<span style="color:#008a20">✓ <?php esc_html_e( 'Ən son versiyadır', 'fixit' ); ?></span>
					<?php else : ?>
						—
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Avtomatik yeniləmə', 'fixit' ); ?></td>
				<td>
					<?php if ( $auto_on ) : ?>
						<span style="color:#008a20">✓ <?php esc_html_e( 'Aktivdir — yeni versiyalar özü quraşdırılır', 'fixit' ); ?></span>
					<?php else : ?>
						<span style="color:#b26200"><?php esc_html_e( 'Söndürülüb', 'fixit' ); ?></span>
						— <a href="<?php echo esc_url( admin_url( 'themes.php?theme=' . FIXIT_THEME_SLUG ) ); ?>"><?php esc_html_e( 'aktivləşdir', 'fixit' ); ?></a>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Son yoxlama', 'fixit' ); ?></td>
				<td>
					<?php
					echo $last_check
						? esc_html( sprintf( __( '%s əvvəl', 'fixit' ), human_time_diff( $last_check ) ) )
						: esc_html__( 'hələ yoxlanılmayıb', 'fixit' );
					?>
				</td>
			</tr>
		</tbody>
	</table>

	<form method="post" style="margin-top:12px">
		<?php wp_nonce_field( 'fixit_update_check', 'fixit_update_nonce' ); ?>
		<button type="submit" class="button"><?php esc_html_e( 'Yeniləməni indi yoxla', 'fixit' ); ?></button>
	</form>
	<?php

	// Diaqnostika bloku (inc/updater-package.php).
	do_action( 'fixit_update_status_after' );
}
add_action( 'fixit_setup_page_after', 'fixit_update_status_box' );
