<?php
/**
 * SEO auditi: saytın bütün səhifələrini üç dildə yoxlayır və problemləri çıxarır.
 *
 * Menyu: FIXIT → SEO auditi.
 *
 * Yoxlama brauzerdə gedir (assets/js/seo-audit.js): səhifələr girişsiz,
 * ziyarətçi və Google kimi açılır (LiteSpeed keşi və Cloudflare olduğu kimi),
 * HTML oxunur və 40-dan çox qayda ilə yoxlanılır: başlıq, təsvir, H1,
 * canonical, hreflang, html lang, Open Graph, struktur məlumat (yerli
 * biznes sxemi daxil), şəkillər, linklər, tərcümə olunmamış mətn, sürət,
 * robots.txt, sitemap, 404, hədəf açar sözlər.
 *
 * Server yalnız ünvanların siyahısını verir və nəticəni saxlayır (REST):
 *   GET  /wp-json/fixit/v1/seo-audit/plan
 *   POST /wp-json/fixit/v1/seo-audit/save
 *
 * Nəticə «Claude üçün hesabat» kimi kopyalanır: problemləri düzəltmək üçün.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menyu.
 */
function fixit_seo_audit_menu() {
	add_submenu_page( 'fixit', __( 'SEO auditi', 'fixit' ), __( 'SEO auditi', 'fixit' ), 'edit_posts', 'fixit-seo-audit', 'fixit_seo_audit_page' );
}
add_action( 'admin_menu', 'fixit_seo_audit_menu', 11 );

/**
 * Hədəf açar sözlər (dil üzrə). Standart siyahı başlanğıc üçündür, admin dəyişir.
 *
 * @return array<string,string[]>
 */
function fixit_seo_keywords() {
	$saved = get_option( 'fixit_seo_keywords' );
	if ( is_array( $saved ) ) {
		return $saved;
	}
	return array(
		'az' => array( 'İT xidmətləri Bakı', 'kompüter servisi', 'video müşahidə sistemləri', 'IP telefoniya', 'uzaqdan dəstək proqramı', 'helpdesk sistemi', 'şəbəkə monitorinqi' ),
		'en' => array( 'remote support software', 'self-hosted remote desktop', 'helpdesk ticket system', 'network monitoring tool', 'IT services Azerbaijan' ),
		'ru' => array( 'программа удаленного доступа', 'удаленная поддержка', 'система заявок', 'мониторинг сети', 'ИТ услуги Баку' ),
	);
}

/**
 * Yoxlanılacaq ünvanlar: dərc olunmuş səhifələr, məhsullar, xidmətlər — hər dildə.
 */
function fixit_seo_audit_plan() {
	$langs = array_keys( fixit_langs() );
	$posts = get_posts(
		array(
			'post_type'      => array( 'page', 'fixit_product', 'fixit_service' ),
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);

	$front = (int) get_option( 'page_on_front' );
	$urls  = array();
	foreach ( $posts as $post ) {
		$base = fixit_strip_lang_from_url( get_permalink( $post ) );
		foreach ( $langs as $lang ) {
			$urls[] = array(
				'url'   => fixit_add_lang_to_url( $base, $lang ),
				'lang'  => $lang,
				'type'  => $post->ID === $front ? 'front' : $post->post_type,
				'id'    => $post->ID,
				'title' => get_the_title( $post ),
				'tpl'   => (string) get_post_meta( $post->ID, '_wp_page_template', true ),
			);
		}
	}

	return array(
		'host'     => wp_parse_url( home_url( '/' ), PHP_URL_HOST ),
		'home'     => home_url( '/' ),
		'langs'    => $langs,
		'default'  => fixit_default_lang(),
		'urls'     => $urls,
		'robots'   => home_url( '/robots.txt' ),
		'sitemap'  => home_url( '/wp-sitemap.xml' ),
		'notfound' => home_url( '/seo-audit-404-' . strtolower( wp_generate_password( 8, false, false ) ) . '/' ),
		'keywords' => fixit_seo_keywords(),
		// Bu ünvanlar qəsdən yönləndirir (yükləmə, köhnə Azərbaycanca ünvanlar) — daxili link kimi problem sayılmır.
		'skip'     => array( '/download/', '/yukle/', '/trial-package/', '/sinaq-paketi/', '/wp-admin/', '/wp-login.php', '/feed/', '/wp-json/', '/comments/feed/', '/cdn-cgi/' ),
	);
}

/**
 * REST.
 */
function fixit_seo_audit_rest() {
	$can = function () {
		return current_user_can( 'edit_posts' );
	};

	register_rest_route(
		'fixit/v1',
		'/seo-audit/plan',
		array(
			'methods'             => 'GET',
			'permission_callback' => $can,
			'callback'            => function () {
				$r = rest_ensure_response( fixit_seo_audit_plan() );
				$r->header( 'Cache-Control', 'no-store' );
				return $r;
			},
		)
	);

	register_rest_route(
		'fixit/v1',
		'/seo-audit/save',
		array(
			'methods'             => 'POST',
			'permission_callback' => $can,
			'callback'            => function ( WP_REST_Request $req ) {
				$report = $req->get_json_params();
				if ( ! is_array( $report ) || ! isset( $report['summary'] ) ) {
					return new WP_REST_Response( array( 'error' => 'bad' ), 400 );
				}
				$json = wp_json_encode( $report );
				if ( strlen( $json ) > 900000 ) {
					return new WP_REST_Response( array( 'error' => 'too big' ), 413 );
				}
				update_option( 'fixit_seo_audit_report', $report, false );

				$sum     = $report['summary'];
				$last    = get_option( 'fixit_seo_audit_last' );
				$history = is_array( $last ) && ! empty( $last['history'] ) ? $last['history'] : array();
				$history[] = array(
					'time'  => time(),
					'score' => (int) $sum['score'],
				);
				update_option(
					'fixit_seo_audit_last',
					array(
						'time'     => time(),
						'score'    => (int) $sum['score'],
						'critical' => (int) $sum['critical'],
						'warning'  => (int) $sum['warning'],
						'notice'   => (int) $sum['notice'],
						'pages'    => (int) $sum['pages'],
						'history'  => array_slice( $history, -12 ),
					),
					false
				);
				return rest_ensure_response( array( 'ok' => true ) );
			},
		)
	);
}
add_action( 'rest_api_init', 'fixit_seo_audit_rest' );

/**
 * Açar sözləri yadda saxlayır.
 */
function fixit_seo_keywords_save() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'İcazə yoxdur.', 'fixit' ) );
	}
	check_admin_referer( 'fixit_seo_keywords' );

	$out = array();
	foreach ( array_keys( fixit_langs() ) as $lang ) {
		$raw   = isset( $_POST[ 'kw_' . $lang ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ 'kw_' . $lang ] ) ) : '';
		$lines = array_filter( array_map( 'trim', preg_split( '/\R/u', $raw ) ) );
		$out[ $lang ] = array_slice( array_values( array_unique( $lines ) ), 0, 30 );
	}
	update_option( 'fixit_seo_keywords', $out, false );

	wp_safe_redirect( admin_url( 'admin.php?page=fixit-seo-audit&saved=1#keywords' ) );
	exit;
}
add_action( 'admin_post_fixit_seo_keywords', 'fixit_seo_keywords_save' );

/**
 * Qaydalar: [ciddilik, başlıq, izah]. Ciddilik: critical / warning / notice.
 */
function fixit_seo_audit_rules() {
	return array(
		'http_error'          => array( 'critical', __( 'Səhifə xəta qaytarır', 'fixit' ), __( 'Səhifə 4xx və ya 5xx statusu ilə cavab verir: Google onu indeksləməyəcək.', 'fixit' ) ),
		'noindex'             => array( 'critical', __( 'Səhifə axtarışdan gizlədilib (noindex)', 'fixit' ), __( 'robots meta və ya X-Robots-Tag «noindex» deyir: səhifə Google-da görünməyəcək.', 'fixit' ) ),
		'title_missing'       => array( 'critical', __( 'Başlıq (title) yoxdur', 'fixit' ), __( 'Başlıq axtarış nəticəsində görünən əsas mətndir.', 'fixit' ) ),
		'h1_missing'          => array( 'critical', __( 'H1 başlığı yoxdur', 'fixit' ), __( 'Hər səhifədə bir H1 olmalıdır: axtarış sistemi səhifənin mövzusunu ondan anlayır.', 'fixit' ) ),
		'canonical_broken'    => array( 'critical', __( 'Canonical işləməyən səhifəni göstərir', 'fixit' ), __( 'Canonical ünvanı 200 qaytarmalıdır.', 'fixit' ) ),
		'hreflang_broken'     => array( 'critical', __( 'hreflang işləməyən ünvanı göstərir', 'fixit' ), __( 'Dil versiyasının ünvanı 200 qaytarmalıdır.', 'fixit' ) ),
		'broken_link'         => array( 'critical', __( 'İşləməyən daxili link', 'fixit' ), __( 'Link 404 və ya başqa xəta verən səhifəyə aparır.', 'fixit' ) ),
		'robots_block'        => array( 'critical', __( 'robots.txt bütün saytı bağlayır', 'fixit' ), __( '«Disallow: /» axtarış robotlarının saytı oxumasına mane olur.', 'fixit' ) ),
		'sitemap_missing'     => array( 'critical', __( 'Sitemap tapılmadı', 'fixit' ), __( 'Sitemap Google-a bütün səhifələri tez tapmağa kömək edir.', 'fixit' ) ),
		'title_short'         => array( 'warning', __( 'Başlıq çox qısadır (30 simvoldan az)', 'fixit' ), __( 'Başlığa əsas açar sözü və brendi əlavə edin.', 'fixit' ) ),
		'title_long'          => array( 'warning', __( 'Başlıq çox uzundur (65 simvoldan çox)', 'fixit' ), __( 'Google uzun başlığı kəsir. Vacib sözləri əvvələ yazın.', 'fixit' ) ),
		'title_duplicate'     => array( 'warning', __( 'Eyni başlıq bir neçə səhifədə', 'fixit' ), __( 'Hər səhifənin başlığı unikal olmalıdır.', 'fixit' ) ),
		'title_untranslated'  => array( 'warning', __( 'Başlıq tərcümə olunmayıb', 'fixit' ), __( 'EN və ya RU səhifəsinin başlığı AZ səhifəsi ilə eynidir.', 'fixit' ) ),
		'desc_missing'        => array( 'warning', __( 'Meta təsvir yoxdur', 'fixit' ), __( 'Google özü təsadüfi mətn seçəcək. 120-160 simvolluq təsvir yazın.', 'fixit' ) ),
		'desc_duplicate'      => array( 'warning', __( 'Eyni meta təsvir bir neçə səhifədə', 'fixit' ), __( 'Hər səhifənin təsviri unikal olmalıdır.', 'fixit' ) ),
		'h1_multiple'         => array( 'warning', __( 'Bir neçə H1', 'fixit' ), __( 'Səhifədə yalnız bir H1 olmalıdır.', 'fixit' ) ),
		'canonical_missing'   => array( 'warning', __( 'Canonical link yoxdur', 'fixit' ), __( 'Canonical eyni məzmunun bir neçə ünvanda indekslənməsinin qarşısını alır.', 'fixit' ) ),
		'canonical_other'     => array( 'warning', __( 'Canonical başqa səhifəni göstərir', 'fixit' ), __( 'Səhifə öz ünvanını canonical kimi göstərməlidir, yoxsa indekslənməyə bilər.', 'fixit' ) ),
		'lang_mismatch'       => array( 'warning', __( 'html lang ünvanın dilinə uyğun deyil', 'fixit' ), __( '/en/ səhifəsində lang="en", /ru/ səhifəsində lang="ru" olmalıdır.', 'fixit' ) ),
		'hreflang_missing'    => array( 'warning', __( 'hreflang yoxdur', 'fixit' ), __( 'Dil versiyaları bir-birinə hreflang ilə bağlanmalıdır: Google hər ölkədə düzgün dili göstərir.', 'fixit' ) ),
		'hreflang_incomplete' => array( 'warning', __( 'hreflang natamamdır', 'fixit' ), __( 'Hər səhifədə az, en, ru və x-default olmalıdır.', 'fixit' ) ),
		'hreflang_reciprocal' => array( 'warning', __( 'hreflang qarşılıqlı deyil', 'fixit' ), __( 'A səhifəsi B-ni göstərirsə, B də A-nı göstərməlidir, yoxsa Google bağlantını nəzərə almır.', 'fixit' ) ),
		'schema_invalid'      => array( 'warning', __( 'Struktur məlumatda xəta', 'fixit' ), __( 'JSON-LD oxunmur: Google onu nəzərə almayacaq.', 'fixit' ) ),
		'schema_product'      => array( 'warning', __( 'Məhsul səhifəsində məhsul sxemi yoxdur', 'fixit' ), __( 'SoftwareApplication və ya Product sxemi lazımdır.', 'fixit' ) ),
		'org_missing'         => array( 'warning', __( 'Ana səhifədə şirkət sxemi yoxdur', 'fixit' ), __( 'Organization və ya LocalBusiness sxemi brendin Google-da tanınmasına kömək edir.', 'fixit' ) ),
		'local_missing'       => array( 'warning', __( 'Yerli SEO: şirkət sxemində çatışmayan sahələr', 'fixit' ), __( 'Azərbaycanda (xəritədə) görünmək üçün sxemdə ünvan, telefon (+994), koordinat, iş saatları və sosial profillər olmalıdır.', 'fixit' ) ),
		'img_alt'             => array( 'warning', __( 'Şəkillərin alt mətni yoxdur', 'fixit' ), __( 'Alt mətn şəkil axtarışında və əlçatanlıqda vacibdir.', 'fixit' ) ),
		'img_heavy'           => array( 'warning', __( 'Ağır şəkillər (300 KB-dan çox)', 'fixit' ), __( 'Şəkli kiçildin və ya WebP-yə çevirin: səhifə tez açılsın.', 'fixit' ) ),
		'thin_content'        => array( 'warning', __( 'Mətn azdır (250 sözdən az)', 'fixit' ), __( 'Az mətnli səhifə çətin sıralanır. Faydalı məzmun əlavə edin.', 'fixit' ) ),
		'untranslated'        => array( 'warning', __( 'Tərcümə olunmamış mətn', 'fixit' ), __( 'EN və ya RU səhifəsində Azərbaycan hərfləri (ə, ğ, ı) olan sözlər var.', 'fixit' ) ),
		'mixed_content'       => array( 'warning', __( 'HTTPS səhifədə HTTP resursu', 'fixit' ), __( 'Brauzer belə resursu bloklaya bilər, Google bunu təhlükəsizlik problemi sayır.', 'fixit' ) ),
		'slow_page'           => array( 'warning', __( 'Səhifə gec cavab verir (2,5 saniyədən çox)', 'fixit' ), __( 'Sürət Google-un sıralama siqnalıdır. Keşi və hostinqi yoxlayın.', 'fixit' ) ),
		'robots_missing'      => array( 'warning', __( 'robots.txt tapılmadı', 'fixit' ), __( 'robots.txt axtarış robotlarına sitemap-in yerini göstərir.', 'fixit' ) ),
		'sitemap_bad'         => array( 'warning', __( 'Sitemap-də işləməyən və ya yönləndirilən ünvan', 'fixit' ), __( 'Sitemap-də yalnız 200 qaytaran son ünvanlar olmalıdır.', 'fixit' ) ),
		'soft_404'            => array( 'warning', __( 'Olmayan səhifə 404 qaytarmır', 'fixit' ), __( 'Olmayan ünvan 404 statusu verməlidir, yoxsa Google boş səhifələri indeksləyir.', 'fixit' ) ),
		'keyword_missing'     => array( 'warning', __( 'Açar söz heç bir səhifənin başlığında və ya H1-ində yoxdur', 'fixit' ), __( 'Bu axtarış üçün ayrıca səhifə və ya başlıq lazımdır, yoxsa üstə qalxmaq çətindir.', 'fixit' ) ),
		'page_redirect'       => array( 'notice', __( 'Siyahıdakı ünvan yönləndirilir', 'fixit' ), __( 'Səhifənin öz ünvanı başqa ünvana yönləndirilir.', 'fixit' ) ),
		'desc_short'          => array( 'notice', __( 'Meta təsvir qısadır (70 simvoldan az)', 'fixit' ), __( 'Təsvirdə nə təklif etdiyinizi və fərqinizi yazın.', 'fixit' ) ),
		'desc_long'           => array( 'notice', __( 'Meta təsvir uzundur (165 simvoldan çox)', 'fixit' ), __( 'Google uzun təsviri kəsir.', 'fixit' ) ),
		'heading_order'       => array( 'notice', __( 'Başlıq səviyyələri ardıcıl deyil', 'fixit' ), __( 'Məsələn, H2-dən sonra birbaşa H4 gəlir.', 'fixit' ) ),
		'og_missing'          => array( 'notice', __( 'Sosial şəbəkə məlumatı (Open Graph) natamamdır', 'fixit' ), __( 'og:title, og:description və og:image paylaşımda görünür.', 'fixit' ) ),
		'og_locale'           => array( 'notice', __( 'og:locale səhifənin dilinə uyğun deyil', 'fixit' ), __( 'AZ: az_AZ, EN: en_US, RU: ru_RU olmalıdır.', 'fixit' ) ),
		'schema_missing'      => array( 'notice', __( 'Struktur məlumat (JSON-LD) yoxdur', 'fixit' ), __( 'Struktur məlumat Google-da zəngin nəticə (ulduz, sual-cavab, naviqasiya) almağa kömək edir.', 'fixit' ) ),
		'schema_rich'         => array( 'notice', __( 'Məhsul sxemində reytinq və ya qiymət yoxdur', 'fixit' ), __( 'Google proqram üçün zəngin nəticəni yalnız offers və ya aggregateRating olanda göstərir.', 'fixit' ) ),
		'breadcrumb_missing'  => array( 'notice', __( 'Naviqasiya zənciri (BreadcrumbList) yoxdur', 'fixit' ), __( 'Google axtarış nəticəsində ünvanın yerinə səhifənin yolunu göstərir.', 'fixit' ) ),
		'website_lang'        => array( 'notice', __( 'WebSite sxeminin dili səhifənin dilinə uyğun deyil', 'fixit' ), __( 'inLanguage səhifənin dili olmalıdır.', 'fixit' ) ),
		'img_size'            => array( 'notice', __( 'Şəkillərin ölçüsü (width/height) göstərilməyib', 'fixit' ), __( 'Səhifə yüklənəndə məzmun sıçrayır (CLS).', 'fixit' ) ),
		'img_format'          => array( 'notice', __( 'Böyük şəkil WebP və ya AVIF deyil', 'fixit' ), __( 'PNG/JPEG şəkil 20 KB-dan böyükdür. WebP adətən 25-35% kiçik olur, səhifə tez açılır.', 'fixit' ) ),
		'heavy_html'          => array( 'notice', __( 'HTML çox böyükdür (350 KB-dan çox)', 'fixit' ), __( 'Böyük HTML gec yüklənir, xüsusən mobil şəbəkədə.', 'fixit' ) ),
		'blocking_js'         => array( 'notice', __( 'Səhifənin açılmasını ləngidən skriptlər', 'fixit' ), __( '<head>-də async və ya defer olmayan skriptlər.', 'fixit' ) ),
		'url_format'          => array( 'notice', __( 'Ünvanda böyük hərf və ya qeyri-latın simvol', 'fixit' ), __( 'Ünvan kiçik latın hərfləri və defislə olmalıdır.', 'fixit' ) ),
		'link_redirect'       => array( 'notice', __( 'Daxili link yönləndirməyə aparır', 'fixit' ), __( 'Linki birbaşa son ünvana dəyişin (məsələn, köhnə Azərbaycanca ünvanlar).', 'fixit' ) ),
		'orphan'              => array( 'notice', __( 'Heç bir səhifədən link verilməyən səhifə', 'fixit' ), __( 'Google belə səhifəni çətin tapır və az əhəmiyyətli sayır.', 'fixit' ) ),
		'robots_sitemap'      => array( 'notice', __( 'robots.txt-də Sitemap sətri yoxdur', 'fixit' ), __( 'Sitemap: https://.../wp-sitemap.xml sətrini əlavə edin.', 'fixit' ) ),
		'not_in_sitemap'      => array( 'notice', __( 'Səhifə sitemap-də yoxdur', 'fixit' ), __( 'İndekslənməli səhifə sitemap-də olmalıdır.', 'fixit' ) ),
	);
}

/**
 * Səhifə.
 */
function fixit_seo_audit_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$kw   = fixit_seo_keywords();
	$last = get_option( 'fixit_seo_audit_report' );

	wp_enqueue_script( 'fixit-seo-audit', get_template_directory_uri() . '/assets/js/seo-audit.js', array(), fixit_ver( 'assets/js/seo-audit.js' ), true );
	wp_localize_script(
		'fixit-seo-audit',
		'fixitSeoAudit',
		array(
			'plan'  => esc_url_raw( rest_url( 'fixit/v1/seo-audit/plan' ) ),
			'save'  => esc_url_raw( rest_url( 'fixit/v1/seo-audit/save' ) ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'last'  => is_array( $last ) ? $last : null,
			'rules' => fixit_seo_audit_rules(),
			's'     => array(
				'critical'   => __( 'Kritik', 'fixit' ),
				'warning'    => __( 'Xəbərdarlıq', 'fixit' ),
				'notice'     => __( 'Qeyd', 'fixit' ),
				'score'      => __( 'SEO balı', 'fixit' ),
				'pages'      => __( 'Səhifə', 'fixit' ),
				'duration'   => __( 'Müddət', 'fixit' ),
				'sec'        => __( 'san', 'fixit' ),
				'plan'       => __( 'Ünvanların siyahısı alınır…', 'fixit' ),
				'crawl'      => __( 'Səhifələr yoxlanılır', 'fixit' ),
				'links'      => __( 'Linklər və şəkillər yoxlanılır', 'fixit' ),
				'site'       => __( 'robots.txt, sitemap və 404 yoxlanılır', 'fixit' ),
				'done'       => __( 'Hazırdır', 'fixit' ),
				'saved'      => __( 'Nəticə yadda saxlanıldı.', 'fixit' ),
				'saveFail'   => __( 'Nəticə yadda saxlanılmadı', 'fixit' ),
				'failed'     => __( 'Audit dayandı', 'fixit' ),
				'noIssues'   => __( 'Problem tapılmadı.', 'fixit' ),
				'more'       => __( 'və daha %d', 'fixit' ),
				'copied'     => __( 'Hesabat kopyalandı. Onu Claude-a göndərin.', 'fixit' ),
				'url'        => __( 'Ünvan', 'fixit' ),
				'lang'       => __( 'Dil', 'fixit' ),
				'status'     => __( 'Status', 'fixit' ),
				'title'      => __( 'Başlıq', 'fixit' ),
				'desc'       => __( 'Təsvir', 'fixit' ),
				'words'      => __( 'Söz', 'fixit' ),
				'issues'     => __( 'Problem', 'fixit' ),
				'time'       => __( 'Cavab', 'fixit' ),
				'keyword'    => __( 'Açar söz', 'fixit' ),
				'bestPage'   => __( 'Ən uyğun səhifə', 'fixit' ),
				'where'      => __( 'Harada var', 'fixit' ),
				'none'       => __( 'heç bir səhifədə', 'fixit' ),
				'inTitle'    => __( 'başlıq', 'fixit' ),
				'inH1'       => __( 'H1', 'fixit' ),
				'inDesc'     => __( 'təsvir', 'fixit' ),
				'inBody'     => __( 'mətn', 'fixit' ),
				'inUrl'      => __( 'ünvan', 'fixit' ),
				'lastRun'    => __( 'Son audit', 'fixit' ),
				'reportHead' => __( 'FIXIT SEO auditi', 'fixit' ),
				'reportAsk'  => __( 'Bu problemləri düzəlt. Əvvəl kritikləri, sonra xəbərdarlıqları.', 'fixit' ),
			),
		)
	);
	?>
	<style>
		.fsa{max-width:1180px}
		.fsa-top{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin:14px 0}
		.fsa-progress{height:8px;background:#e0e0e0;border-radius:4px;overflow:hidden;margin:8px 0 4px;max-width:640px}
		.fsa-progress i{display:block;height:100%;width:0;background:#2271b1;transition:width .2s}
		.fsa-log{color:#50575e;font-size:12px;min-height:18px;max-width:900px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}
		.fsa-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin:16px 0}
		.fsa-card{background:#fff;border:1px solid #dcdcde;border-radius:4px;padding:12px 14px}
		.fsa-card b{display:block;font-size:26px;line-height:1.2}
		.fsa-card span{color:#50575e}
		.fsa-score b{color:#2271b1}
		.fsa-card.c b{color:#d63638}.fsa-card.w b{color:#b26200}.fsa-card.n b{color:#50575e}
		.fsa-tabs{margin-top:18px}
		.fsa-rule{background:#fff;border:1px solid #dcdcde;border-left:4px solid #8c8f94;border-radius:3px;margin:0 0 8px}
		.fsa-rule.critical{border-left-color:#d63638}.fsa-rule.warning{border-left-color:#dba617}
		.fsa-rule summary{cursor:pointer;padding:10px 12px;font-weight:600;display:flex;gap:10px;align-items:baseline}
		.fsa-rule summary em{font-style:normal;font-weight:400;color:#50575e;margin-left:auto;white-space:nowrap}
		.fsa-rule .hint{margin:0 12px 8px;color:#50575e}
		.fsa-rule ul{margin:0 12px 12px 30px;list-style:disc}
		.fsa-rule li{margin:2px 0;word-break:break-all}
		.fsa-rule li small{color:#50575e;word-break:normal}
		.fsa-sev{display:inline-block;font-size:11px;font-weight:600;padding:1px 6px;border-radius:2px;color:#fff;background:#8c8f94}
		.fsa-sev.critical{background:#d63638}.fsa-sev.warning{background:#b26200}
		.fsa table.widefat td{vertical-align:top;word-break:break-word}
		.fsa .bad{color:#d63638;font-weight:600}
		.fsa .ok{color:#00a32a}
		.fsa-kw textarea{width:100%;min-height:150px;font-family:inherit}
		.fsa-kw-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
		@media (max-width:900px){.fsa-kw-grid{grid-template-columns:1fr}}
	</style>
	<div class="wrap fsa">
		<h1><?php esc_html_e( 'SEO auditi', 'fixit' ); ?></h1>
		<?php if ( isset( $_GET['saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- yalnız bildiriş ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Açar sözlər yadda saxlanıldı. Auditi yenidən işlədin.', 'fixit' ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'Saytın bütün səhifələri üç dildə (AZ, EN, RU) ziyarətçi və Google kimi, girişsiz açılır və yoxlanılır: başlıq, təsvir, H1, canonical, hreflang, dil, sosial şəbəkə məlumatı, struktur məlumat (yerli biznes sxemi daxil), şəkillər, linklər, tərcümə olunmamış mətn, sürət, robots.txt, sitemap və hədəf açar sözlər. Həm Azərbaycan, həm qlobal axtarış üçün.', 'fixit' ); ?></p>
		<p><?php esc_html_e( 'Audit bitəndə «Claude üçün hesabatı kopyala» düyməsini basın və mətni Claude-a göndərin: problemləri birlikdə düzəldək.', 'fixit' ); ?></p>

		<div class="fsa-top">
			<button type="button" class="button button-primary button-hero" id="fsa-run"><?php esc_html_e( 'Auditi başlat', 'fixit' ); ?></button>
			<button type="button" class="button" id="fsa-copy" disabled><?php esc_html_e( 'Claude üçün hesabatı kopyala', 'fixit' ); ?></button>
			<button type="button" class="button" id="fsa-json" disabled><?php esc_html_e( 'JSON yüklə', 'fixit' ); ?></button>
		</div>
		<div class="fsa-progress" hidden><i></i></div>
		<div class="fsa-log" aria-live="polite"></div>

		<div id="fsa-result"></div>

		<h2 id="keywords" style="margin-top:34px"><?php esc_html_e( 'Hədəf açar sözlər', 'fixit' ); ?></h2>
		<p><?php esc_html_e( 'Müştərinin Google-da yazdığı sözlər: hər sətirdə bir. Audit hər söz üçün ən uyğun səhifəni tapır və sözün başlıqda, H1-də, təsvirdə və mətndə olub-olmadığını göstərir. Azərbaycan bazarı üçün AZ və RU, qlobal bazar üçün EN sözləri yazın.', 'fixit' ); ?></p>
		<form class="fsa-kw" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="fixit_seo_keywords">
			<?php wp_nonce_field( 'fixit_seo_keywords' ); ?>
			<div class="fsa-kw-grid">
				<?php foreach ( array_keys( fixit_langs() ) as $lang ) : ?>
					<label><strong><?php echo esc_html( strtoupper( $lang ) ); ?></strong>
						<textarea name="kw_<?php echo esc_attr( $lang ); ?>"><?php echo esc_textarea( implode( "\n", isset( $kw[ $lang ] ) ? (array) $kw[ $lang ] : array() ) ); ?></textarea>
					</label>
				<?php endforeach; ?>
			</div>
			<?php submit_button( __( 'Açar sözləri yadda saxla', 'fixit' ) ); ?>
		</form>
	</div>
	<?php
}
