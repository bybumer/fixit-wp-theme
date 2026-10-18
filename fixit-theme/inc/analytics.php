<?php
/**
 * Analitika və reklam pikselləri — plaginsiz.
 *
 * Google Analytics 4, Meta Pixel, LinkedIn Insight Tag və Microsoft
 * Clarity. İdentifikatorlar Görünüş → Fərdiləşdir → FIXIT tema ayarları →
 * "Analitika və reklam" bölməsində yazılır; boş sahənin kodu səhifəyə
 * düşmür. Heç biri doldurulmayıbsa sayt əvvəlki kimi işləyir.
 *
 * Hadisələr (müraciət, demo açılışı, zəng və s.) assets/js/analytics.js-dədir,
 * kuki razılığı zolağı footer.php-dədir.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ayar sahələri: açar => [ad, izah, format].
 *
 * Format yoxlanılır — sahəyə təsadüfən bütün kod parçası yapışdırılsa,
 * səhifəyə heç nə yazılmır (yalnız identifikator qəbul olunur).
 */
function fixit_tracking_fields() {
	return array(
		'fixit_ga4_id'           => array(
			__( 'Google Analytics 4 — Measurement ID', 'fixit' ),
			__( 'G-XXXXXXXXXX formatında. GA4 → Admin → Data streams → Web.', 'fixit' ),
			'/^G-[A-Z0-9]{4,16}$/',
		),
		'fixit_meta_pixel_id'    => array(
			__( 'Meta Pixel ID', 'fixit' ),
			__( 'Yalnız rəqəmlər. Meta Events Manager → Data sources → Pixel.', 'fixit' ),
			'/^\d{8,20}$/',
		),
		'fixit_linkedin_partner' => array(
			__( 'LinkedIn Insight Tag — Partner ID', 'fixit' ),
			__( 'Yalnız rəqəmlər. Campaign Manager → Analyze → Insight Tag.', 'fixit' ),
			'/^\d{3,12}$/',
		),
		'fixit_linkedin_lead'    => array(
			__( 'LinkedIn — müraciət konversiyasının ID-si (istəyə görə)', 'fixit' ),
			__( 'Campaign Manager → Conversions → "Lead" növlü konversiya yaradın, onun ID-sini yazın. Form göndəriləndə bu konversiya sayılır.', 'fixit' ),
			'/^\d{3,12}$/',
		),
		'fixit_clarity_id'       => array(
			__( 'Microsoft Clarity — Project ID', 'fixit' ),
			__( 'clarity.microsoft.com → Settings → Overview → Project ID.', 'fixit' ),
			'/^[a-z0-9]{6,20}$/',
		),
	);
}

/**
 * Sahənin təmizlənmiş dəyəri: format uyğun deyilsə boş sətir.
 *
 * @param string $key Ayarın açarı.
 * @return string
 */
function fixit_tracking_id( $key ) {
	$fields = fixit_tracking_fields();
	$value  = trim( (string) get_theme_mod( $key, '' ) );

	if ( 'fixit_clarity_id' === $key ) {
		$value = strtolower( $value );
	} elseif ( 'fixit_ga4_id' === $key ) {
		$value = strtoupper( $value );
	}

	return ( isset( $fields[ $key ] ) && preg_match( $fields[ $key ][2], $value ) ) ? $value : '';
}

/**
 * Customizer: "Analitika və reklam" bölməsi.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function fixit_tracking_customize( $wp_customize ) {
	$wp_customize->add_section(
		'fixit_tracking',
		array(
			'title'       => __( 'Analitika və reklam', 'fixit' ),
			'panel'       => 'fixit_panel',
			'description' => __( 'Boş qalan sahənin kodu sayta əlavə olunmur. Sayta admin kimi daxil olanda kodlar işləmir — öz baxışlarınız statistikaya düşmür.', 'fixit' ),
		)
	);

	foreach ( fixit_tracking_fields() as $key => $field ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'       => $field[0],
				'description' => $field[1],
				'section'     => 'fixit_tracking',
				'type'        => 'text',
			)
		);
	}

	$wp_customize->add_setting(
		'fixit_tracking_consent',
		array(
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'fixit_tracking_consent',
		array(
			'label'       => __( 'Kodlar yalnız ziyarətçinin razılığından sonra işləsin', 'fixit' ),
			'description' => __( 'Açıq olanda saytın aşağısında kuki razılığı zolağı görünür və analitika yalnız “Qəbul edirəm” basıldıqdan sonra yüklənir. Türkiyə və Aİ auditoriyasına reklam veriləndə açılması tövsiyə olunur.', 'fixit' ),
			'section'     => 'fixit_tracking',
			'type'        => 'checkbox',
		)
	);
}
add_action( 'customize_register', 'fixit_tracking_customize', 20 );

/**
 * Səhifəyə ötürülən ayarlar. Heç bir identifikator yoxdursa boş massiv.
 *
 * @return array
 */
function fixit_tracking_config() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$config = array(
		'ga4'      => fixit_tracking_id( 'fixit_ga4_id' ),
		'meta'     => fixit_tracking_id( 'fixit_meta_pixel_id' ),
		'linkedin' => fixit_tracking_id( 'fixit_linkedin_partner' ),
		'clarity'  => fixit_tracking_id( 'fixit_clarity_id' ),
	);

	if ( ! array_filter( $config ) ) {
		$cached = array();
		return $cached;
	}

	$config['leadConv'] = fixit_tracking_id( 'fixit_linkedin_lead' );
	$config['ask']      = (bool) get_theme_mod( 'fixit_tracking_consent', false );
	$config['demos']    = fixit_tracking_demo_hosts();

	$cached = $config;
	return $cached;
}

/**
 * Demo serverlərinin ünvanı => məhsulun qısa adı.
 *
 * Saytdan demoya keçid "demo_open" hadisəsi kimi sayılır; məhsul adı
 * hesabatda hansı demonun açıldığını göstərir.
 *
 * @return array
 */
function fixit_tracking_demo_hosts() {
	$hosts = array();

	if ( ! function_exists( 'fixit_get_products' ) || ! function_exists( 'fixit_product_demo' ) ) {
		return $hosts;
	}

	foreach ( fixit_get_products() as $prod ) {
		$demo = fixit_product_demo( $prod->ID );
		$host = $demo['url'] ? (string) wp_parse_url( $demo['url'], PHP_URL_HOST ) : '';

		if ( $host ) {
			$hosts[ strtolower( $host ) ] = $prod->post_name;
		}
	}

	return $hosts;
}

/**
 * Kodlar bu səhifədə işləməlidirmi?
 *
 * Redaktorlar və adminlər sayılmır: öz baxışlarınız və sınaq formalarınız
 * statistikanı pozmasın. Fərdiləşdirmə önizləməsində də işləmir.
 */
function fixit_tracking_active() {
	if ( is_admin() || is_customize_preview() ) {
		return false;
	}

	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		return false;
	}

	return (bool) fixit_tracking_config();
}

/**
 * <head>: identifikatorlar və yükləyici.
 *
 * Kitabxanalar birbaşa <script src> ilə yox, yükləyici ilə qoşulur: razılıq
 * tələb olunanda "Qəbul edirəm" basılana qədər heç bir kənar skript
 * yüklənmir və kuki yazılmır.
 */
function fixit_tracking_head() {
	if ( ! fixit_tracking_active() ) {
		return;
	}
	?>
	<script>
	window.fixitTrack = <?php echo wp_json_encode( fixit_tracking_config() ); ?>;
	(function (w, d, c) {
		var started = false;

		function load(src) {
			var s = d.createElement('script');
			s.async = true;
			s.src = src;
			d.head.appendChild(s);
		}

		c.start = function () {
			if (started) return;
			started = true;

			if (c.ga4) {
				w.dataLayer = w.dataLayer || [];
				w.gtag = function () { w.dataLayer.push(arguments); };
				w.gtag('js', new Date());
				w.gtag('config', c.ga4);
				load('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(c.ga4));
			}

			if (c.meta && !w.fbq) {
				var f = w.fbq = function () {
					if (f.callMethod) { f.callMethod.apply(f, arguments); } else { f.queue.push(arguments); }
				};
				if (!w._fbq) w._fbq = f;
				f.push = f;
				f.loaded = true;
				f.version = '2.0';
				f.queue = [];
				load('https://connect.facebook.net/en_US/fbevents.js');
				f('init', c.meta);
				f('track', 'PageView');
			}

			if (c.linkedin) {
				w._linkedin_partner_id = c.linkedin;
				w._linkedin_data_partner_ids = w._linkedin_data_partner_ids || [];
				w._linkedin_data_partner_ids.push(c.linkedin);
				if (!w.lintrk) {
					w.lintrk = function (a, b) { w.lintrk.q.push([a, b]); };
					w.lintrk.q = [];
				}
				load('https://snap.licdn.com/li.lms-analytics/insight.min.js');
			}

			if (c.clarity) {
				w.clarity = w.clarity || function () { (w.clarity.q = w.clarity.q || []).push(arguments); };
				load('https://www.clarity.ms/tag/' + encodeURIComponent(c.clarity));
			}
		};

		var choice = null;
		try { choice = w.localStorage.getItem('fixit-consent'); } catch (e) {}
		if (!c.ask || choice === 'granted') c.start();
	})(window, document, window.fixitTrack);
	</script>
	<?php
}
add_action( 'wp_head', 'fixit_tracking_head', 2 );

/**
 * Hadisələr skripti — yalnız kodlar işləyəndə qoşulur.
 */
function fixit_tracking_assets() {
	if ( ! fixit_tracking_active() ) {
		return;
	}

	wp_enqueue_script(
		'fixit-analytics',
		get_template_directory_uri() . '/assets/js/analytics.js',
		array( 'fixit-main' ),
		fixit_ver( 'assets/js/analytics.js' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'fixit_tracking_assets', 20 );
