<?php
/**
 * Kuki razılığı.
 *
 * Saytın aşağısında zolaq: «Hamısını qəbul et», «Yalnız zəruri», «Ayarlar».
 * Seçim brauzerdə 12 ay saxlanılır (localStorage: fixit-consent), sonra
 * yenidən soruşulur. Footerdəki «Kuki ayarları» ilə istənilən vaxt dəyişir.
 *
 * Kateqoriyalar:
 * - zəruri   — dil seçimi, razılığın özü, təhlükəsizlik; söndürülmür
 * - analitika — Google Analytics 4, Microsoft Clarity
 * - reklam    — Meta Pixel, LinkedIn Insight Tag
 *
 * Razılıq verilməyən kateqoriyanın skripti ümumiyyətlə yüklənmir
 * (inc/analytics.php). Zolaq səhifəyə gizli yazılır, göstərilib-göstərilməməsi
 * brauzerdə qərara alınır — keşlənmiş səhifə hər ziyarətçidə düzgün işləyir.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Saytın məxfilik siyasəti səhifəsinin ünvanı.
 */
function fixit_privacy_url() {
	return fixit_page_url( 'template-privacy.php' );
}

/**
 * Razılıq bu səhifədə soruşulmalıdırmı (admin panel, önizləmə xaric).
 */
function fixit_consent_enabled() {
	return ! is_admin() && ! is_customize_preview();
}

/**
 * <head>: saxlanmış seçimi oxuyan kiçik funksiya.
 *
 * Analitika yükləyicisi (inc/analytics.php, prioritet 2) bundan istifadə edir:
 * razılıq verilməyibsə heç bir kənar skript yüklənmir.
 */
function fixit_consent_head() {
	if ( ! fixit_consent_enabled() ) {
		return;
	}
	?>
	<script>
	window.fixitConsent = window.fixitConsent || {};
	window.fixitConsent.key = 'fixit-consent';
	window.fixitConsent.maxAge = 31536000000;
	window.fixitConsent.read = function () {
		var raw = null;
		try { raw = window.localStorage.getItem('fixit-consent'); } catch (e) {}
		if (!raw || raw.charAt(0) !== '{') return null;
		try {
			var v = JSON.parse(raw);
			if (!v || v.v !== 2 || !v.t || (Date.now() - v.t) > window.fixitConsent.maxAge) return null;
			return { a: !!v.a, m: !!v.m, t: v.t };
		} catch (e) { return null; }
	};
	</script>
	<?php
}
add_action( 'wp_head', 'fixit_consent_head', 1 );

/**
 * Razılıq skripti.
 */
function fixit_consent_assets() {
	if ( ! fixit_consent_enabled() ) {
		return;
	}

	wp_enqueue_script(
		'fixit-consent',
		get_template_directory_uri() . '/assets/js/consent.js',
		array(),
		fixit_ver( 'assets/js/consent.js' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'fixit_consent_assets' );

/**
 * Zolaq (gizli yazılır, consent.js göstərir).
 */
function fixit_consent_banner() {
	if ( ! fixit_consent_enabled() ) {
		return;
	}

	$privacy = fixit_privacy_url();
	?>
	<div class="consent" id="fixit-consent" role="region" aria-label="<?php esc_attr_e( 'Kuki razılığı', 'fixit' ); ?>" hidden>
		<div class="consent__inner">
			<div class="consent__text">
				<p class="consent__title"><?php esc_html_e( 'Bu sayt kukilərdən istifadə edir', 'fixit' ); ?></p>
				<p>
					<?php esc_html_e( 'Zəruri kukilər saytın işləməsi üçündür: dil seçimi, razılığınız və təhlükəsizlik. Razılığınızla həm də saytın necə istifadə olunduğunu ölçən analitika və reklamlarımızın nəticəsini göstərən kukilər işləyir.', 'fixit' ); ?>
					<a href="<?php echo esc_url( $privacy ); ?>"><?php esc_html_e( 'Məxfilik siyasəti', 'fixit' ); ?></a>
				</p>
			</div>

			<div class="consent__prefs" data-consent-prefs hidden>
				<label class="consent__opt">
					<input type="checkbox" checked disabled>
					<span><b><?php esc_html_e( 'Zəruri', 'fixit' ); ?></b> <?php esc_html_e( 'Dil seçimi, razılığınızın yadda saxlanması, təhlükəsizlik. Bunlarsız sayt düzgün işləmir, söndürmək olmur.', 'fixit' ); ?></span>
				</label>
				<label class="consent__opt">
					<input type="checkbox" data-consent-cat="a">
					<span><b><?php esc_html_e( 'Analitika', 'fixit' ); ?></b> <?php esc_html_e( 'Hansı səhifələrə baxıldığını və saytın necə istifadə olunduğunu anonim ölçür (Google Analytics, Microsoft Clarity).', 'fixit' ); ?></span>
				</label>
				<label class="consent__opt">
					<input type="checkbox" data-consent-cat="m">
					<span><b><?php esc_html_e( 'Reklam', 'fixit' ); ?></b> <?php esc_html_e( 'Reklamlarımızın nəticəsini ölçür və onları maraqlı ola biləcək insanlara göstərməyə kömək edir (Meta, LinkedIn).', 'fixit' ); ?></span>
				</label>
			</div>

			<div class="consent__actions">
				<button type="button" class="btn btn--primary btn--sm" data-consent="all"><?php esc_html_e( 'Hamısını qəbul et', 'fixit' ); ?></button>
				<button type="button" class="btn btn--ghost btn--sm" data-consent="necessary"><?php esc_html_e( 'Yalnız zəruri', 'fixit' ); ?></button>
				<button type="button" class="btn btn--ghost btn--sm" data-consent="settings" aria-expanded="false"><?php esc_html_e( 'Ayarlar', 'fixit' ); ?></button>
				<button type="button" class="btn btn--primary btn--sm" data-consent="save" hidden><?php esc_html_e( 'Seçimi yadda saxla', 'fixit' ); ?></button>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'fixit_consent_banner', 5 );

/**
 * Footerdəki hüquqi keçidlər: məxfilik siyasəti və kuki ayarları.
 */
function fixit_footer_legal_links() {
	?>
	<nav class="footer__legal" aria-label="<?php esc_attr_e( 'Hüquqi məlumat', 'fixit' ); ?>">
		<a href="<?php echo esc_url( fixit_privacy_url() ); ?>"><?php esc_html_e( 'Məxfilik siyasəti', 'fixit' ); ?></a>
		<button type="button" class="footer__link-btn" data-consent-open><?php esc_html_e( 'Kuki ayarları', 'fixit' ); ?></button>
	</nav>
	<?php
}

/**
 * Formaların altındakı qeyd: məlumatın necə işləndiyi.
 */
function fixit_form_privacy_note() {
	printf(
		'<p class="form-note">%1$s <a href="%2$s">%3$s</a></p>',
		esc_html__( 'Məlumatlarınız yalnız bələdçini göndərmək və sizinlə əlaqə üçün istifadə olunur.', 'fixit' ),
		esc_url( fixit_privacy_url() ),
		esc_html__( 'Məxfilik siyasəti', 'fixit' )
	);
}
