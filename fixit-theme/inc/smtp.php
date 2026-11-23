<?php
/**
 * E-poçt (SMTP).
 *
 * Saytın bütün məktubları (sınaq bələdçisi, bildirişlər, əlaqə forması) hostinqin
 * mail() funksiyası ilə yox, sizin poçt serverinizdən gedir: məktub spama düşmür,
 * göndərənin ünvanı şirkətin ünvanı olur.
 *
 * Ayarlar: FIXIT → E-poçt (SMTP). Parol bazada şifrələnmiş saxlanılır (açar
 * wp-config.php-dəki duzlardan alınır, baza tək ələ keçsə parol oxunmur). İstəsəniz
 * parolu wp-config.php-də verə bilərsiniz, onda bazaya heç yazılmır:
 *
 *     define( 'FIXIT_SMTP_PASSWORD', '...' );
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Şifrələmənin nonce uzunluğu (sodium secretbox). */
const FIXIT_SMTP_NONCE = 24;

/**
 * Hazır poçt xidmətləri (forma bunları doldurur).
 */
function fixit_smtp_presets() {
	return array(
		'hostinger' => array( 'Hostinger', 'smtp.hostinger.com', 465, 'ssl' ),
		'google'    => array( 'Google Workspace / Gmail', 'smtp.gmail.com', 587, 'tls' ),
		'microsoft' => array( 'Microsoft 365 / Outlook', 'smtp.office365.com', 587, 'tls' ),
		'yandex'    => array( 'Yandex', 'smtp.yandex.com', 465, 'ssl' ),
		'zoho'      => array( 'Zoho Mail', 'smtp.zoho.com', 465, 'ssl' ),
		'mailru'    => array( 'Mail.ru', 'smtp.mail.ru', 465, 'ssl' ),
	);
}

/**
 * Ayarlar (parolsuz).
 *
 * @return array{enabled:bool,host:string,port:int,secure:string,auth:bool,user:string,from:string,from_name:string}
 */
function fixit_smtp_settings() {
	$saved = get_option( 'fixit_smtp', array() );
	$s     = wp_parse_args(
		is_array( $saved ) ? $saved : array(),
		array(
			'enabled'   => false,
			'host'      => '',
			'port'      => 465,
			'secure'    => 'ssl',
			'auth'      => true,
			'user'      => '',
			'from'      => '',
			'from_name' => 'FIXIT',
		)
	);

	return array(
		'enabled'   => (bool) $s['enabled'],
		'host'      => preg_match( '/^[A-Za-z0-9.-]{1,253}$/', (string) $s['host'] ) ? (string) $s['host'] : '',
		'port'      => min( 65535, max( 1, (int) $s['port'] ) ),
		'secure'    => in_array( $s['secure'], array( 'ssl', 'tls', '' ), true ) ? $s['secure'] : 'ssl',
		'auth'      => (bool) $s['auth'],
		'user'      => (string) $s['user'],
		'from'      => is_email( $s['from'] ) ? (string) $s['from'] : '',
		'from_name' => (string) $s['from_name'],
	);
}

/**
 * Parolun şifrələnməsi üçün açar: wp-config.php-dəki duzlardan.
 */
function fixit_smtp_key() {
	return hash( 'sha256', wp_salt( 'auth' ) . '|fixit-smtp', true );
}

/**
 * Parol wp-config.php-də verilibmi.
 */
function fixit_smtp_password_in_config() {
	return defined( 'FIXIT_SMTP_PASSWORD' ) && '' !== (string) FIXIT_SMTP_PASSWORD;
}

/**
 * Parolu şifrələnmiş saxlayır (boş — silir).
 *
 * @param string $plain Parol.
 */
function fixit_smtp_store_password( $plain ) {
	if ( '' === $plain ) {
		delete_option( 'fixit_smtp_pass' );
		return;
	}

	$nonce = random_bytes( FIXIT_SMTP_NONCE );
	$box   = sodium_crypto_secretbox( $plain, $nonce, fixit_smtp_key() );
	update_option( 'fixit_smtp_pass', base64_encode( $nonce . $box ), false ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- şifrələnmiş parolun saxlanma forması
}

/**
 * Parol (wp-config.php və ya baza); yoxdursa və ya açılmırsa boş.
 */
function fixit_smtp_password() {
	if ( fixit_smtp_password_in_config() ) {
		return (string) FIXIT_SMTP_PASSWORD;
	}

	$raw = base64_decode( (string) get_option( 'fixit_smtp_pass', '' ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- şifrələnmiş parolun saxlanma forması
	if ( ! is_string( $raw ) || strlen( $raw ) <= FIXIT_SMTP_NONCE ) {
		return '';
	}

	$plain = sodium_crypto_secretbox_open(
		substr( $raw, FIXIT_SMTP_NONCE ),
		substr( $raw, 0, FIXIT_SMTP_NONCE ),
		fixit_smtp_key()
	);

	return is_string( $plain ) ? $plain : '';
}

/**
 * Parol yazılıbmı (açmadan).
 */
function fixit_smtp_has_password() {
	return fixit_smtp_password_in_config() || '' !== (string) get_option( 'fixit_smtp_pass', '' );
}

/**
 * SMTP işə salınıb və ayarlar tamdır.
 */
function fixit_smtp_ready() {
	$s = fixit_smtp_settings();
	return $s['enabled'] && '' !== $s['host'] && ( ! $s['auth'] || ( '' !== $s['user'] && fixit_smtp_has_password() ) );
}

/**
 * Göndərənin ünvanı: ayarda yazılıb, yoxdursa giriş adı (e-poçtdursa).
 */
function fixit_smtp_from() {
	$s = fixit_smtp_settings();
	if ( '' !== $s['from'] ) {
		return $s['from'];
	}
	return is_email( $s['user'] ) ? $s['user'] : '';
}

/**
 * PHPMailer-i SMTP ilə qurur.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $mailer Poçt.
 */
function fixit_smtp_phpmailer( $mailer ) {
	if ( ! fixit_smtp_ready() ) {
		return;
	}

	$s = fixit_smtp_settings();

	$mailer->isSMTP();
	$mailer->Host       = $s['host']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer
	$mailer->Port       = $s['port']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	$mailer->SMTPSecure = 'ssl' === $s['secure'] ? 'ssl' : ( 'tls' === $s['secure'] ? 'tls' : '' ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	$mailer->SMTPAutoTLS = '' !== $s['secure']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	$mailer->SMTPAuth   = $s['auth']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	$mailer->Timeout    = 15; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

	if ( $s['auth'] ) {
		$mailer->Username = $s['user']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$mailer->Password = fixit_smtp_password(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	// Poçt serverlərinin çoxu başqa ünvandan göndərməyə icazə vermir: göndərən həmişə bu qutudur.
	$from = fixit_smtp_from();
	if ( '' !== $from ) {
		$mailer->setFrom( $from, '' !== $s['from_name'] ? $s['from_name'] : get_bloginfo( 'name' ), false );
		$mailer->Sender = $from; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- qaytarılan məktublar bu ünvana
	}
}
add_action( 'phpmailer_init', 'fixit_smtp_phpmailer', 20 );

/**
 * Göndərən WordPress-in öz yoxlamasından əvvəl qoyulur (standart wordpress@... əvəzinə).
 *
 * @param string $from Ünvan.
 */
function fixit_smtp_mail_from( $from ) {
	$own = fixit_smtp_ready() ? fixit_smtp_from() : '';
	return '' !== $own ? $own : $from;
}
add_filter( 'wp_mail_from', 'fixit_smtp_mail_from', 20 );

/**
 * Göndərənin adı.
 *
 * @param string $name Ad.
 */
function fixit_smtp_mail_from_name( $name ) {
	$s = fixit_smtp_settings();
	return fixit_smtp_ready() && '' !== $s['from_name'] ? $s['from_name'] : $name;
}
add_filter( 'wp_mail_from_name', 'fixit_smtp_mail_from_name', 20 );

/**
 * Son xəta və son uğurlu göndərmə (admin səhifəsində görünür).
 *
 * @param WP_Error $error Xəta.
 */
function fixit_smtp_failed( $error ) {
	$data = $error->get_error_data();
	$to   = is_array( $data ) && ! empty( $data['to'] ) ? implode( ', ', (array) $data['to'] ) : '';

	update_option(
		'fixit_smtp_last_error',
		array(
			'time'    => time(),
			'message' => wp_strip_all_tags( $error->get_error_message() ),
			'to'      => $to,
			'smtp'    => fixit_smtp_ready(),
		),
		false
	);
}
add_action( 'wp_mail_failed', 'fixit_smtp_failed' );

/**
 * Son uğurlu göndərmə.
 */
function fixit_smtp_succeeded() {
	update_option( 'fixit_smtp_last_ok', time(), false );
}
add_action( 'wp_mail_succeeded', 'fixit_smtp_succeeded' );

/* ============================================================
   Admin: FIXIT → E-poçt (SMTP)
   ============================================================ */

/**
 * Menyu.
 */
function fixit_smtp_menu() {
	add_submenu_page( 'fixit', __( 'E-poçt (SMTP)', 'fixit' ), __( 'E-poçt (SMTP)', 'fixit' ), 'manage_options', 'fixit-smtp', 'fixit_smtp_page' );
}
add_action( 'admin_menu', 'fixit_smtp_menu' );

/**
 * Ayar səhifəsinin ünvanı.
 */
function fixit_smtp_page_url() {
	return admin_url( 'admin.php?page=fixit-smtp' );
}

/**
 * Vəziyyət bir sətirdə (başqa səhifələrdə də göstərilir).
 *
 * @return array{0:string,1:string} [ok|warn, mətn]
 */
function fixit_smtp_status() {
	$s = fixit_smtp_settings();
	if ( fixit_smtp_ready() ) {
		/* translators: 1: server, 2: göndərən */
		return array( 'ok', sprintf( __( 'Məktublar SMTP ilə gedir: %1$s, göndərən %2$s', 'fixit' ), $s['host'], fixit_smtp_from() ? fixit_smtp_from() : '—' ) );
	}
	if ( $s['enabled'] ) {
		return array( 'warn', __( 'SMTP yandırılıb, amma ayarlar tam deyil: məktublar hostinqin mail() funksiyası ilə gedir.', 'fixit' ) );
	}
	return array( 'warn', __( 'SMTP qurulmayıb: məktublar hostinqin mail() funksiyası ilə gedir və spama düşə bilər.', 'fixit' ) );
}

/**
 * Ayar səhifəsi.
 */
function fixit_smtp_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$s       = fixit_smtp_settings();
	$status  = fixit_smtp_status();
	$error   = get_option( 'fixit_smtp_last_error' );
	$last_ok = (int) get_option( 'fixit_smtp_last_ok' );
	$result  = get_transient( 'fixit_smtp_test_' . get_current_user_id() );
	if ( $result ) {
		delete_transient( 'fixit_smtp_test_' . get_current_user_id() );
	}
	$test_to = function_exists( 'fixit_trial_settings' ) ? fixit_trial_settings()['notify'] : get_option( 'admin_email' );
	?>
	<div class="wrap" style="max-width:900px">
		<h1><?php esc_html_e( 'E-poçt (SMTP)', 'fixit' ); ?></h1>
		<p><?php esc_html_e( 'Saytın bütün məktubları (sınaq bələdçisi, yeni müraciət bildirişləri, əlaqə forması) bu poçt qutusundan göndərilir. Göndərən şirkətin ünvanı olur, məktublar spama düşmür.', 'fixit' ); ?></p>

		<?php if ( isset( $_GET['saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- yalnız bildiriş ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Ayarlar yadda saxlanıldı.', 'fixit' ); ?></p></div>
		<?php endif; ?>
		<?php if ( is_array( $result ) ) : ?>
			<div class="notice notice-<?php echo $result['ok'] ? 'success' : 'error'; ?>"><p><?php echo esc_html( $result['text'] ); ?></p></div>
		<?php endif; ?>

		<div class="notice notice-<?php echo 'ok' === $status[0] ? 'success' : 'warning'; ?> inline" style="margin:16px 0">
			<p><strong><?php echo esc_html( $status[1] ); ?></strong></p>
			<?php if ( $last_ok ) : ?>
				<p>
					<?php
					/* translators: %s: tarix */
					echo esc_html( sprintf( __( 'Son uğurlu məktub: %s', 'fixit' ), wp_date( 'd.m.Y H:i', $last_ok ) ) );
					?>
				</p>
			<?php endif; ?>
			<?php if ( is_array( $error ) && ! empty( $error['time'] ) ) : ?>
				<p style="color:#b32d2e">
					<?php
					/* translators: 1: tarix, 2: alan, 3: xəta */
					echo esc_html( sprintf( __( 'Son xəta (%1$s, %2$s): %3$s', 'fixit' ), wp_date( 'd.m.Y H:i', (int) $error['time'] ), $error['to'] ? $error['to'] : '—', $error['message'] ) );
					?>
				</p>
			<?php endif; ?>
		</div>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="fixit_smtp_save">
			<?php wp_nonce_field( 'fixit_smtp_save' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'SMTP', 'fixit' ); ?></th>
					<td><label><input type="checkbox" name="enabled" value="1" <?php checked( $s['enabled'] ); ?>> <strong><?php esc_html_e( 'Məktubları bu poçt serverindən göndər', 'fixit' ); ?></strong></label></td>
				</tr>
				<tr>
					<th scope="row"><label for="fixit-smtp-preset"><?php esc_html_e( 'Poçt xidməti', 'fixit' ); ?></label></th>
					<td>
						<select id="fixit-smtp-preset">
							<option value=""><?php esc_html_e( 'Seçin, sahələr özü dolsun', 'fixit' ); ?></option>
							<?php foreach ( fixit_smtp_presets() as $key => $preset ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" data-host="<?php echo esc_attr( $preset[1] ); ?>" data-port="<?php echo esc_attr( $preset[2] ); ?>" data-secure="<?php echo esc_attr( $preset[3] ); ?>"><?php echo esc_html( $preset[0] ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Gmail və Google Workspace-də adi parol işləmir: hesabda iki mərhələli giriş açılmalı və «App password» yaradılmalıdır. Microsoft 365-də poçt qutusu üçün «Authenticated SMTP» açıq olmalıdır.', 'fixit' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="fixit-smtp-host"><?php esc_html_e( 'Server', 'fixit' ); ?></label></th>
					<td><input type="text" name="host" id="fixit-smtp-host" value="<?php echo esc_attr( $s['host'] ); ?>" class="regular-text" placeholder="smtp.hostinger.com" autocomplete="off"></td>
				</tr>
				<tr>
					<th scope="row"><label for="fixit-smtp-port"><?php esc_html_e( 'Port və şifrələmə', 'fixit' ); ?></label></th>
					<td>
						<input type="number" name="port" id="fixit-smtp-port" value="<?php echo esc_attr( $s['port'] ); ?>" min="1" max="65535" style="width:90px">
						<select name="secure" id="fixit-smtp-secure">
							<option value="ssl" <?php selected( $s['secure'], 'ssl' ); ?>>SSL (465)</option>
							<option value="tls" <?php selected( $s['secure'], 'tls' ); ?>>TLS / STARTTLS (587)</option>
							<option value="" <?php selected( $s['secure'], '' ); ?>><?php esc_html_e( 'Şifrələmə yoxdur (tövsiyə olunmur)', 'fixit' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Giriş', 'fixit' ); ?></th>
					<td>
						<label><input type="checkbox" name="auth" value="1" <?php checked( $s['auth'] ); ?>> <?php esc_html_e( 'Server istifadəçi adı və parol istəyir', 'fixit' ); ?></label>
						<p><input type="text" name="user" value="<?php echo esc_attr( $s['user'] ); ?>" class="regular-text" placeholder="info@fixit.az" autocomplete="off" aria-label="<?php esc_attr_e( 'İstifadəçi adı', 'fixit' ); ?>"></p>
						<?php if ( fixit_smtp_password_in_config() ) : ?>
							<p><input type="password" class="regular-text" value="" disabled placeholder="<?php esc_attr_e( 'wp-config.php-də verilib', 'fixit' ); ?>"></p>
						<?php else : ?>
							<p><input type="password" name="password" value="" class="regular-text" autocomplete="new-password" placeholder="<?php echo esc_attr( fixit_smtp_has_password() ? __( 'yazılıb, dəyişmək üçün yenisini yazın', 'fixit' ) : __( 'Parol', 'fixit' ) ); ?>" aria-label="<?php esc_attr_e( 'Parol', 'fixit' ); ?>"></p>
							<?php if ( fixit_smtp_has_password() ) : ?>
								<p><label><input type="checkbox" name="password_clear" value="1"> <?php esc_html_e( 'Parolu sil', 'fixit' ); ?></label></p>
							<?php endif; ?>
							<p class="description"><?php esc_html_e( 'Parol bazada şifrələnmiş saxlanılır və səhifədə göstərilmir.', 'fixit' ); ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="fixit-smtp-from"><?php esc_html_e( 'Göndərən', 'fixit' ); ?></label></th>
					<td>
						<input type="email" name="from" id="fixit-smtp-from" value="<?php echo esc_attr( $s['from'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'boş olsa istifadəçi adı', 'fixit' ); ?>">
						<input type="text" name="from_name" value="<?php echo esc_attr( $s['from_name'] ); ?>" class="regular-text" placeholder="FIXIT" aria-label="<?php esc_attr_e( 'Göndərənin adı', 'fixit' ); ?>" style="margin-top:6px">
						<p class="description"><?php esc_html_e( 'Adətən poçt qutusunun özü (məs. info@fixit.az). Başqa ünvan yazsanız, poçt xidmətində o ünvandan göndərməyə icazə olmalıdır.', 'fixit' ); ?></p>
					</td>
				</tr>
			</table>

			<?php submit_button( __( 'Yadda saxla', 'fixit' ) ); ?>
		</form>

		<h2><?php esc_html_e( 'Sınaq məktubu', 'fixit' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="fixit_smtp_test">
			<?php wp_nonce_field( 'fixit_smtp_test' ); ?>
			<p>
				<input type="email" name="to" value="<?php echo esc_attr( $test_to ); ?>" class="regular-text" required>
				<?php submit_button( __( 'Sınaq məktubu göndər', 'fixit' ), 'secondary', 'submit', false ); ?>
			</p>
			<p class="description"><?php esc_html_e( 'Əvvəlcə ayarları yadda saxlayın. Xəta olarsa, poçt serverinin cavabı burada görünəcək.', 'fixit' ); ?></p>
		</form>
	</div>
	<script>
	( function () {
		var preset = document.getElementById( 'fixit-smtp-preset' );
		if ( ! preset ) { return; }
		preset.addEventListener( 'change', function () {
			var opt = preset.options[ preset.selectedIndex ];
			if ( ! opt.value ) { return; }
			document.getElementById( 'fixit-smtp-host' ).value = opt.getAttribute( 'data-host' );
			document.getElementById( 'fixit-smtp-port' ).value = opt.getAttribute( 'data-port' );
			document.getElementById( 'fixit-smtp-secure' ).value = opt.getAttribute( 'data-secure' );
		} );
	} )();
	</script>
	<?php
}

/**
 * Ayarları yadda saxlayır.
 */
function fixit_smtp_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'İcazə yoxdur.', 'fixit' ) );
	}
	check_admin_referer( 'fixit_smtp_save' );

	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- aşağıda tiplə təmizlənir
	$host = isset( $_POST['host'] ) ? strtolower( trim( sanitize_text_field( wp_unslash( $_POST['host'] ) ) ) ) : '';
	$from = isset( $_POST['from'] ) ? sanitize_email( wp_unslash( $_POST['from'] ) ) : '';

	update_option(
		'fixit_smtp',
		array(
			'enabled'   => ! empty( $_POST['enabled'] ),
			'host'      => preg_replace( '/[^a-z0-9.-]/', '', $host ),
			'port'      => isset( $_POST['port'] ) ? absint( $_POST['port'] ) : 465,
			'secure'    => isset( $_POST['secure'] ) && in_array( $_POST['secure'], array( 'ssl', 'tls', '' ), true ) ? $_POST['secure'] : 'ssl',
			'auth'      => ! empty( $_POST['auth'] ),
			'user'      => isset( $_POST['user'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['user'] ) ) ) : '',
			'from'      => $from,
			'from_name' => isset( $_POST['from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['from_name'] ) ) : '',
		),
		false
	);

	if ( ! fixit_smtp_password_in_config() ) {
		if ( ! empty( $_POST['password_clear'] ) ) {
			fixit_smtp_store_password( '' );
		} elseif ( isset( $_POST['password'] ) && '' !== (string) $_POST['password'] ) {
			// Parol olduğu kimi saxlanılır (boşluq və xüsusi simvollar ola bilər), yalnız sətir sonları atılır.
			fixit_smtp_store_password( str_replace( array( "\r", "\n" ), '', (string) wp_unslash( $_POST['password'] ) ) );
		}
	}
	// phpcs:enable

	wp_safe_redirect( add_query_arg( 'saved', 1, fixit_smtp_page_url() ) );
	exit;
}
add_action( 'admin_post_fixit_smtp_save', 'fixit_smtp_save' );

/**
 * Sınaq məktubu.
 */
function fixit_smtp_test() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'İcazə yoxdur.', 'fixit' ) );
	}
	check_admin_referer( 'fixit_smtp_test' );

	$to    = isset( $_POST['to'] ) ? sanitize_email( wp_unslash( $_POST['to'] ) ) : '';
	$error = '';
	$catch = function ( $e ) use ( &$error ) {
		$error = wp_strip_all_tags( $e->get_error_message() );
	};

	if ( ! is_email( $to ) ) {
		$result = array(
			'ok'   => false,
			'text' => __( 'E-poçt ünvanı düzgün deyil.', 'fixit' ),
		);
	} else {
		add_action( 'wp_mail_failed', $catch );
		$sent = wp_mail(
			$to,
			'[fixit.az] ' . __( 'Sınaq məktubu', 'fixit' ),
			__( 'Bu, saytın poçt ayarlarını yoxlamaq üçün sınaq məktubudur. Məktubu aldınızsa, sayt məktubları düzgün göndərir.', 'fixit' ) . "\n\n" . home_url( '/' )
		);
		remove_action( 'wp_mail_failed', $catch );

		$via = fixit_smtp_ready() ? fixit_smtp_settings()['host'] : 'mail()';
		$result = $sent
			? array(
				'ok'   => true,
				/* translators: 1: alan, 2: server */
				'text' => sprintf( __( 'Məktub %1$s ünvanına göndərildi (%2$s). Gələnlər qutusunu, gəlməyibsə spamı yoxlayın.', 'fixit' ), $to, $via ),
			)
			: array(
				'ok'   => false,
				/* translators: 1: server, 2: xəta */
				'text' => sprintf( __( 'Məktub göndərilmədi (%1$s): %2$s', 'fixit' ), $via, $error ? $error : __( 'səbəb bilinmir', 'fixit' ) ),
			);
	}

	set_transient( 'fixit_smtp_test_' . get_current_user_id(), $result, 5 * MINUTE_IN_SECONDS );
	wp_safe_redirect( fixit_smtp_page_url() );
	exit;
}
add_action( 'admin_post_fixit_smtp_test', 'fixit_smtp_test' );
