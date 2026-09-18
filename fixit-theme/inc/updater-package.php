<?php
/**
 * Yeniləmə paketinin etibarlı yüklənməsi və diaqnostikası.
 *
 * Nəyə lazımdır: bəzi hostinqlər (məs. Hostinger) GitHub-ın arxiv serverindən
 * gələn faylı kəsə və ya dəyişdirə bilər. Onda WordPress sadəcə "Paket
 * quraşdırıla bilmədi" yazır və səbəbi göstərmir.
 *
 * Bu fayl:
 *  1) Öz temamızın paketini WordPress-dən əvvəl ÖZÜ yükləyir və yoxlayır:
 *     HTTP kodu, ölçü, ZIP imzası, içində fixit-theme/style.css olması.
 *  2) GitHub relizindən gələn fayl yararsızdırsa, ehtiyat mənbəni sınayır:
 *     codeload.github.com (repo arxivi — başqa server, başqa ünvan).
 *  3) Alınmasa, KONKRET səbəbi yazır — Temalar səhifəsindəki kartda görünür.
 *  4) Son cəhdin nəticəsini saxlayır və "Paketi sına" düyməsi verir
 *     (Görünüş → FIXIT quraşdırma).
 *
 * Yalnız bizim temanın yeniləməsinə təsir edir; əl ilə ZIP yükləməyə,
 * digər tema və plaginlərə toxunmur.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bu ünvan bizim relizin paketidirmi?
 *
 * @param string $url Paket ünvanı.
 * @return string Relizin teqi (məs. v1.5.1) və ya boş sətir.
 */
function fixit_pkg_tag_from_url( $url ) {
	$prefix = 'https://github.com/' . FIXIT_UPDATE_REPO . '/releases/download/';

	if ( 0 !== strpos( (string) $url, $prefix ) ) {
		return '';
	}

	$rest = substr( $url, strlen( $prefix ) );
	$tag  = strtok( $rest, '/' );

	return ( $tag && preg_match( '/^v?[0-9][0-9A-Za-z.\-]*$/', $tag ) ) ? $tag : '';
}

/**
 * Ehtiyat mənbə: repo arxivi başqa serverdən (codeload.github.com).
 *
 * @param string $tag Relizin teqi.
 */
function fixit_pkg_fallback_url( $tag ) {
	return 'https://codeload.github.com/' . FIXIT_UPDATE_REPO . '/zip/refs/tags/' . rawurlencode( $tag );
}

/**
 * Faylın ilk baytlarını oxunaqlı göstərir (xəta mesajı üçün).
 *
 * @param string $file Fayl yolu.
 */
function fixit_pkg_peek( $file ) {
	$head = (string) @file_get_contents( $file, false, null, 0, 120 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	$head = preg_replace( '/[^\x20-\x7E]/', '.', $head );
	return trim( substr( $head, 0, 80 ) );
}

/**
 * Arxivin içində tema faylının yerini tapır.
 *
 * @param string $file ZIP faylı.
 * @return string|WP_Error "fixit-theme/style.css" kimi yol, yoxdursa xəta.
 */
function fixit_pkg_find_style( $file ) {
	$names = array();

	if ( class_exists( 'ZipArchive' ) ) {
		$zip = new ZipArchive();
		if ( true !== $zip->open( $file ) ) {
			return new WP_Error( 'fixit_pkg_zip', __( 'Fayl ZIP kimi açılmır.', 'fixit' ) );
		}
		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$names[] = $zip->getNameIndex( $i );
		}
		$zip->close();
	} else {
		require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
		$pcl  = new PclZip( $file );
		$list = $pcl->listContent();
		if ( ! $list ) {
			return new WP_Error( 'fixit_pkg_zip', __( 'Fayl ZIP kimi açılmır (PclZip).', 'fixit' ) );
		}
		foreach ( $list as $entry ) {
			$names[] = $entry['filename'];
		}
	}

	foreach ( $names as $name ) {
		// Reliz arxivi: fixit-theme/style.css
		// Repo arxivi:   <repo>-<teq>/fixit-theme/style.css
		if ( preg_match( '#(^|/)' . preg_quote( FIXIT_THEME_SLUG, '#' ) . '/style\.css$#', $name ) ) {
			return $name;
		}
	}

	return new WP_Error(
		'fixit_pkg_nostyle',
		sprintf(
			/* translators: %s: arxivdəki ilk fayllar */
			__( 'Arxivdə fixit-theme/style.css yoxdur. İçindəkilər: %s', 'fixit' ),
			implode( ', ', array_slice( $names, 0, 6 ) )
		)
	);
}

/**
 * Paketi yükləyib yoxlayır.
 *
 * @param string $url Ünvan.
 * @return array ['ok'=>bool, 'file'=>string, 'steps'=>string[], 'error'=>string]
 */
function fixit_pkg_fetch( $url ) {
	$steps = array();
	$host  = wp_parse_url( $url, PHP_URL_HOST );

	if ( ! function_exists( 'wp_tempnam' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	$tmp = wp_tempnam( 'fixit-theme.zip' );
	if ( ! $tmp ) {
		return array(
			'ok'    => false,
			'file'  => '',
			'steps' => $steps,
			'error' => __( 'Müvəqqəti fayl yaradıla bilmədi (disk və ya icazə problemi).', 'fixit' ),
		);
	}

	$response = wp_safe_remote_get(
		$url,
		array(
			'timeout'  => 120,
			'stream'   => true,
			'filename' => $tmp,
			'headers'  => array(
				'User-Agent' => 'FIXIT-Theme-Updater',
				'Accept'     => 'application/octet-stream',
			),
		)
	);

	$fail = function ( $message ) use ( $tmp, &$steps ) {
		if ( file_exists( $tmp ) ) {
			wp_delete_file( $tmp );
		}
		$steps[] = '✗ ' . $message;
		return array(
			'ok'    => false,
			'file'  => '',
			'steps' => $steps,
			'error' => $message,
		);
	};

	if ( is_wp_error( $response ) ) {
		/* translators: 1: server, 2: xəta */
		return $fail( sprintf( __( '%1$s serverinə qoşulmaq alınmadı: %2$s', 'fixit' ), $host, $response->get_error_message() ) );
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	$type = (string) wp_remote_retrieve_header( $response, 'content-type' );
	$size = file_exists( $tmp ) ? (int) filesize( $tmp ) : 0;

	/* translators: 1: server, 2: HTTP kodu, 3: məzmun növü, 4: ölçü */
	$steps[] = sprintf( __( '%1$s → HTTP %2$d, %3$s, %4$s bayt', 'fixit' ), $host, $code, $type ? $type : '?', number_format_i18n( $size ) );

	if ( 200 !== $code ) {
		/* translators: %d: HTTP kodu */
		return $fail( sprintf( __( 'Server faylı vermədi (HTTP %d).', 'fixit' ), $code ) );
	}

	if ( $size < 1024 ) {
		/* translators: 1: ölçü, 2: faylın başlanğıcı */
		return $fail( sprintf( __( 'Gələn fayl çox kiçikdir (%1$d bayt). Başlanğıcı: "%2$s"', 'fixit' ), $size, fixit_pkg_peek( $tmp ) ) );
	}

	$magic = (string) @file_get_contents( $tmp, false, null, 0, 4 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	if ( "PK\x03\x04" !== $magic ) {
		/* translators: %s: faylın başlanğıcı */
		return $fail( sprintf( __( 'Gələn fayl ZIP deyil — ehtimal ki, hostinq onu əvəz edib. Başlanğıcı: "%s"', 'fixit' ), fixit_pkg_peek( $tmp ) ) );
	}
	$steps[] = '✓ ' . __( 'ZIP imzası düzgündür', 'fixit' );

	$style = fixit_pkg_find_style( $tmp );
	if ( is_wp_error( $style ) ) {
		return $fail( $style->get_error_message() );
	}
	/* translators: %s: arxivdəki yol */
	$steps[] = '✓ ' . sprintf( __( 'Tema tapıldı: %s', 'fixit' ), $style );

	return array(
		'ok'    => true,
		'file'  => $tmp,
		'steps' => $steps,
		'error' => '',
	);
}

/**
 * Son cəhdin nəticəsini saxlayır.
 */
function fixit_pkg_log( $ok, $tag, $steps, $error = '' ) {
	update_option(
		'fixit_update_last_attempt',
		array(
			'time'  => time(),
			'ok'    => (bool) $ok,
			'tag'   => $tag,
			'steps' => $steps,
			'error' => $error,
		),
		false
	);
}

/**
 * Paketi reliz ünvanından, alınmasa ehtiyat mənbədən gətirir.
 *
 * @param string $url Reliz paketinin ünvanı.
 * @return array fixit_pkg_fetch() nəticəsi + 'source'.
 */
function fixit_pkg_get( $url ) {
	$tag     = fixit_pkg_tag_from_url( $url );
	$primary = fixit_pkg_fetch( $url );

	if ( $primary['ok'] ) {
		$primary['source'] = 'release';
		return $primary;
	}

	$steps   = array_merge( array( __( 'Reliz arxivi:', 'fixit' ) ), $primary['steps'] );
	$steps[] = __( 'Ehtiyat mənbə sınanır (codeload.github.com):', 'fixit' );

	$backup = fixit_pkg_fetch( fixit_pkg_fallback_url( $tag ) );
	$steps  = array_merge( $steps, $backup['steps'] );

	$backup['steps']  = $steps;
	$backup['source'] = 'codeload';

	if ( ! $backup['ok'] ) {
		/* translators: 1: əsas xəta, 2: ehtiyat mənbənin xətası */
		$backup['error'] = sprintf( __( 'Yeniləmə paketi yüklənmədi. GitHub: %1$s Ehtiyat mənbə: %2$s', 'fixit' ), $primary['error'], $backup['error'] );
	}

	return $backup;
}

/**
 * WordPress paketi yükləməzdən əvvəl — öz temamızın paketini özümüz gətiririk.
 *
 * @param false|string|WP_Error $reply      Əvvəlki filtrlərin cavabı.
 * @param string                $package    Paket ünvanı.
 * @param WP_Upgrader           $upgrader   Quraşdırıcı.
 * @param array                 $hook_extra Əlavə məlumat.
 * @return false|string|WP_Error
 */
function fixit_pkg_pre_download( $reply, $package, $upgrader = null, $hook_extra = array() ) {
	if ( false !== $reply ) {
		return $reply;
	}

	$tag = fixit_pkg_tag_from_url( $package );
	if ( ! $tag ) {
		return $reply; // Bizim paket deyil — WordPress özü yükləsin.
	}

	if ( $upgrader && isset( $upgrader->skin ) ) {
		$upgrader->skin->feedback( __( 'FIXIT: paket yüklənir və yoxlanılır…', 'fixit' ) );
	}

	$result = fixit_pkg_get( $package );
	fixit_pkg_log( $result['ok'], $tag, $result['steps'], $result['error'] );

	if ( ! $result['ok'] ) {
		return new WP_Error( 'fixit_pkg_failed', $result['error'] );
	}

	return $result['file'];
}
add_filter( 'upgrader_pre_download', 'fixit_pkg_pre_download', 10, 4 );

/**
 * Açılmış arxivdə tema qovluğunu dəqiq seçir.
 *
 * Reliz arxivində qovluq birbaşa "fixit-theme"-dir. Ehtiyat mənbənin (repo)
 * arxivində isə o, "<repo>-<teq>/fixit-theme" altında olur — onu seçirik.
 * Yalnız bizim temanın YENİLƏNMƏSİNƏ təsir edir (əl ilə yükləməyə yox).
 *
 * @param string|WP_Error $source        Seçilmiş qovluq.
 * @param string          $remote_source Arxivin açıldığı qovluq.
 * @param WP_Upgrader     $upgrader      Quraşdırıcı.
 * @param array           $hook_extra    Əlavə məlumat.
 * @return string|WP_Error
 */
function fixit_pkg_source_selection( $source, $remote_source, $upgrader = null, $hook_extra = array() ) {
	if ( is_wp_error( $source ) || empty( $hook_extra['theme'] ) || FIXIT_THEME_SLUG !== $hook_extra['theme'] ) {
		return $source;
	}

	$source = trailingslashit( $source );

	if ( file_exists( $source . 'style.css' ) && FIXIT_THEME_SLUG === basename( untrailingslashit( $source ) ) ) {
		return $source; // Reliz arxivi — hər şey yerindədir.
	}

	// Repo arxivi: <repo>-<teq>/fixit-theme/ və ya bir səviyyə yuxarı.
	$candidates = array(
		$source . FIXIT_THEME_SLUG . '/',
		trailingslashit( $remote_source ) . FIXIT_THEME_SLUG . '/',
	);
	foreach ( (array) glob( trailingslashit( $remote_source ) . '*/' . FIXIT_THEME_SLUG . '/', GLOB_ONLYDIR ) as $dir ) {
		$candidates[] = trailingslashit( $dir );
	}

	foreach ( $candidates as $dir ) {
		if ( file_exists( $dir . 'style.css' ) ) {
			return $dir;
		}
	}

	$found = array();
	foreach ( (array) glob( $source . '*' ) as $path ) {
		$found[] = basename( $path );
	}

	return new WP_Error(
		'fixit_pkg_layout',
		sprintf(
			/* translators: %s: qovluqdakı fayllar */
			__( 'Açılmış arxivdə fixit-theme qovluğu tapılmadı. İçindəkilər: %s', 'fixit' ),
			implode( ', ', array_slice( $found, 0, 8 ) )
		)
	);
}
add_filter( 'upgrader_source_selection', 'fixit_pkg_source_selection', 9, 4 );

/**
 * Uğurlu yeniləmədən sonra jurnalda qeyd.
 */
function fixit_pkg_after_upgrade( $upgrader, $options ) {
	if ( isset( $options['type'], $options['themes'] ) && 'theme' === $options['type'] && in_array( FIXIT_THEME_SLUG, (array) $options['themes'], true ) ) {
		$last = get_option( 'fixit_update_last_attempt' );
		if ( is_array( $last ) && ! empty( $last['ok'] ) ) {
			$last['steps'][]   = '✓ ' . __( 'Quraşdırıldı', 'fixit' );
			$last['installed'] = true;
			update_option( 'fixit_update_last_attempt', $last, false );
		}
	}
}
add_action( 'upgrader_process_complete', 'fixit_pkg_after_upgrade', 20, 2 );

/* ============================================================
   Admin: son cəhd və "Paketi sına"
   ============================================================ */

/**
 * FIXIT quraşdırma səhifəsindəki yeniləmə blokunun altına diaqnostika.
 */
function fixit_pkg_status_box() {
	if ( ! current_user_can( 'update_themes' ) ) {
		return;
	}

	$test = null;

	if ( isset( $_POST['fixit_pkg_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fixit_pkg_nonce'] ) ), 'fixit_pkg_test' ) ) {
		$release = fixit_update_fetch_release( true );
		if ( $release ) {
			$test = fixit_pkg_get( $release['package'] );
			if ( ! empty( $test['file'] ) && file_exists( $test['file'] ) ) {
				wp_delete_file( $test['file'] ); // Yalnız sınaq idi — quraşdırmırıq.
			}
			$test['tag'] = $release['version'];
		} else {
			$test = array(
				'ok'    => false,
				'steps' => array(),
				'error' => __( 'GitHub-da son reliz tapılmadı.', 'fixit' ),
				'tag'   => '',
			);
		}
	}

	$last = get_option( 'fixit_update_last_attempt' );
	?>
	<h3 style="margin-top:26px"><?php esc_html_e( 'Yeniləmə diaqnostikası', 'fixit' ); ?></h3>

	<?php if ( $test ) : ?>
		<div class="notice <?php echo $test['ok'] ? 'notice-success' : 'notice-error'; ?> inline" style="max-width:720px">
			<p><strong>
				<?php
				echo $test['ok']
					? esc_html__( 'Paket yüklənir və qaydasındadır — yeniləmə bu serverdə işləməlidir.', 'fixit' )
					: esc_html( $test['error'] );
				?>
			</strong></p>
			<?php if ( $test['steps'] ) : ?>
				<ul style="margin:0 0 10px 18px;list-style:disc">
					<?php foreach ( $test['steps'] as $step ) : ?>
						<li><code style="white-space:normal"><?php echo esc_html( $step ); ?></code></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( is_array( $last ) ) : ?>
		<p>
			<?php esc_html_e( 'Son yeniləmə cəhdi:', 'fixit' ); ?>
			<strong><?php echo esc_html( $last['tag'] ); ?></strong>,
			<?php echo esc_html( sprintf( __( '%s əvvəl', 'fixit' ), human_time_diff( (int) $last['time'] ) ) ); ?> —
			<?php if ( ! empty( $last['ok'] ) ) : ?>
				<span style="color:#008a20"><?php echo ! empty( $last['installed'] ) ? esc_html__( 'uğurla quraşdırıldı', 'fixit' ) : esc_html__( 'paket yükləndi', 'fixit' ); ?></span>
			<?php else : ?>
				<span style="color:#b32d2e"><?php echo esc_html( $last['error'] ); ?></span>
			<?php endif; ?>
		</p>
	<?php endif; ?>

	<form method="post">
		<?php wp_nonce_field( 'fixit_pkg_test', 'fixit_pkg_nonce' ); ?>
		<p>
			<button type="submit" class="button"><?php esc_html_e( 'Paketi sına', 'fixit' ); ?></button>
			<span class="description"><?php esc_html_e( 'Son relizi yükləyib yoxlayır, amma quraşdırmır. Yeniləmə alınmırsa, nəticəni bizə göndərin.', 'fixit' ); ?></span>
		</p>
	</form>
	<?php
}
add_action( 'fixit_update_status_after', 'fixit_pkg_status_box' );
