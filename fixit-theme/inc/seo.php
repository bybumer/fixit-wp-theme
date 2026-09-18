<?php
/**
 * SEO — meta teqlər, Open Graph və struktur məlumatlar (Schema.org).
 *
 * Hər səhifə üçün SEO başlığı və təsviri admin paneldən yazıla bilər
 * (yazı redaktə səhifəsindəki "SEO" qutusu).
 *
 * Yoast / RankMath kimi plagin quraşdırsanız, təkrarın qarşısını almaq üçün
 * temanın meta teqləri avtomatik söndürülür.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tövsiyə olunan uzunluqlar (admin paneldəki yoxlamalar da bunu işlədir).
 */
define( 'FIXIT_TITLE_MIN', 30 );
define( 'FIXIT_TITLE_MAX', 60 );
define( 'FIXIT_DESC_MIN', 70 );
define( 'FIXIT_DESC_MAX', 160 );

/**
 * SEO plagini aktivdirsə temanın meta teqləri söndürülür.
 */
function fixit_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || class_exists( 'All_in_One_SEO_Pack' );
}

/* ============================================================
   1. Yazı/səhifə üçün SEO qutusu
   ============================================================ */

/**
 * SEO qutusunun göstəriləcəyi məzmun növləri.
 */
function fixit_seo_post_types() {
	return array( 'page', 'post', 'fixit_service', 'fixit_product' );
}

function fixit_seo_add_meta_box() {
	if ( fixit_seo_plugin_active() ) {
		return;
	}

	foreach ( fixit_seo_post_types() as $type ) {
		add_meta_box(
			'fixit_seo_box',
			__( 'SEO — axtarış sistemlərində görünüş', 'fixit' ),
			'fixit_seo_meta_box',
			$type,
			'normal',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'fixit_seo_add_meta_box' );

/**
 * SEO qutusu.
 */
function fixit_seo_meta_box( $post ) {
	wp_nonce_field( 'fixit_save_seo', 'fixit_seo_nonce' );

	$title   = get_post_meta( $post->ID, '_fixit_seo_title', true );
	$desc    = get_post_meta( $post->ID, '_fixit_seo_desc', true );
	$noindex = get_post_meta( $post->ID, '_fixit_seo_noindex', true );
	?>
	<style>
		.fixit-seo label{display:block;font-weight:600;margin:14px 0 5px}
		.fixit-seo input[type=text],.fixit-seo textarea{width:100%;max-width:680px}
		.fixit-seo textarea{min-height:72px}
		.fixit-seo .hint{color:#666;font-size:12px;margin-top:4px}
		.fixit-seo .count{font-weight:600}
		.fixit-seo .ok{color:#008a20}
		.fixit-seo .warn{color:#b26200}
		.fixit-seo .bad{color:#b32d2e}
	</style>

	<div class="fixit-seo">
		<label for="fixit_seo_title"><?php esc_html_e( 'SEO başlığı', 'fixit' ); ?></label>
		<input type="text" id="fixit_seo_title" name="fixit_seo_title" value="<?php echo esc_attr( $title ); ?>"
			data-min="<?php echo esc_attr( FIXIT_TITLE_MIN ); ?>" data-max="<?php echo esc_attr( FIXIT_TITLE_MAX ); ?>">
		<p class="hint">
			<?php esc_html_e( 'Google-da görünən başlıq. Boş buraxsanız yazının adı işlədilir.', 'fixit' ); ?>
			<?php printf( esc_html__( 'Tövsiyə: %1$d–%2$d simvol.', 'fixit' ), (int) FIXIT_TITLE_MIN, (int) FIXIT_TITLE_MAX ); ?>
			<span class="count" data-for="fixit_seo_title"></span>
		</p>

		<label for="fixit_seo_desc"><?php esc_html_e( 'SEO təsviri', 'fixit' ); ?></label>
		<textarea id="fixit_seo_desc" name="fixit_seo_desc"
			data-min="<?php echo esc_attr( FIXIT_DESC_MIN ); ?>" data-max="<?php echo esc_attr( FIXIT_DESC_MAX ); ?>"><?php echo esc_textarea( $desc ); ?></textarea>
		<p class="hint">
			<?php esc_html_e( 'Başlığın altındakı izah mətni. Səhifənin nə haqqında olduğunu bir cümlə ilə yazın.', 'fixit' ); ?>
			<?php printf( esc_html__( 'Tövsiyə: %1$d–%2$d simvol.', 'fixit' ), (int) FIXIT_DESC_MIN, (int) FIXIT_DESC_MAX ); ?>
			<span class="count" data-for="fixit_seo_desc"></span>
		</p>

		<label style="display:flex;align-items:center;gap:8px;font-weight:600;margin-top:18px">
			<input type="checkbox" name="fixit_seo_noindex" value="1" <?php checked( $noindex, '1' ); ?>>
			<?php esc_html_e( 'Bu səhifə axtarış nəticələrində göstərilməsin (noindex)', 'fixit' ); ?>
		</label>
	</div>

	<script>
	( function () {
		function upd( el ) {
			var out = document.querySelector( '.count[data-for="' + el.id + '"]' );
			if ( ! out ) { return; }
			var n = el.value.length,
				min = parseInt( el.dataset.min, 10 ),
				max = parseInt( el.dataset.max, 10 ),
				cls = 'ok';
			if ( n === 0 ) { cls = 'warn'; }
			else if ( n < min || n > max ) { cls = 'bad'; }
			out.className = 'count ' + cls;
			out.textContent = n + ' / ' + max;
		}
		[ 'fixit_seo_title', 'fixit_seo_desc' ].forEach( function ( id ) {
			var el = document.getElementById( id );
			if ( ! el ) { return; }
			el.addEventListener( 'input', function () { upd( el ); } );
			upd( el );
		} );
	} )();
	</script>
	<?php
}

/**
 * SEO sahələrini yadda saxlayır.
 */
function fixit_save_seo( $post_id ) {
	if ( ! isset( $_POST['fixit_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fixit_seo_nonce'] ) ), 'fixit_save_seo' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, '_fixit_seo_title', isset( $_POST['fixit_seo_title'] ) ? sanitize_text_field( wp_unslash( $_POST['fixit_seo_title'] ) ) : '' );
	update_post_meta( $post_id, '_fixit_seo_desc', isset( $_POST['fixit_seo_desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['fixit_seo_desc'] ) ) : '' );
	update_post_meta( $post_id, '_fixit_seo_noindex', isset( $_POST['fixit_seo_noindex'] ) ? '1' : '' );
}
add_action( 'save_post', 'fixit_save_seo' );

/* ============================================================
   2. Başlıq və təsvir
   ============================================================ */

/**
 * Şablona görə standart təsvir — səhifə boş olsa da təsvirsiz qalmasın.
 *
 * @param int $post_id Səhifə ID-si.
 */
function fixit_template_description( $post_id ) {
	$template = get_post_meta( $post_id, '_wp_page_template', true );

	switch ( $template ) {
		case 'template-services.php':
			return __( 'Kompüter servisi, video müşahidə, IP telefoniya, korporativ mail, bulud serverlər və İT dəstək — şirkətiniz üçün tam İKT xidmətləri.', 'fixit' );

		case 'template-products.php':
			return __( 'Sorğu Sistemi, Fixit Remote və Şəbəkə Monitorinqi — öz komandamızın hazırladığı, sizin serverinizdə işləyən proqram təminatı. Onlayn demo mövcuddur.', 'fixit' );

		case 'template-about.php':
			return __( '2017-ci ildən İT sahəsində fəaliyyət göstəririk: 40-dan çox şirkətlə əməkdaşlıq, 30-dan çox tamamlanmış layihə. Bakı və Cəlilabadda xidmətinizdəyik.', 'fixit' );

		case 'template-contact.php':
			return sprintf(
				/* translators: %s: telefon nömrəsi */
				__( 'FIXIT ilə əlaqə: %s. Bakı və Cəlilabad ofisləri, iş saatları və müraciət formu. İKT xidmətləri üçün bizə yazın.', 'fixit' ),
				fixit_get( 'fixit_phone' )
			);
	}

	return '';
}

/**
 * Səhifə üçün meta təsvir.
 */
function fixit_meta_description() {
	if ( is_singular() ) {
		$id     = get_queried_object_id();
		$custom = get_post_meta( $id, '_fixit_seo_desc', true );

		if ( $custom ) {
			return $custom;
		}

		if ( is_front_page() ) {
			return wp_strip_all_tags( fixit_get( 'fixit_hero_text' ) );
		}

		$from_template = fixit_template_description( $id );
		if ( $from_template ) {
			return $from_template;
		}

		$excerpt = get_the_excerpt( $id );
		if ( $excerpt ) {
			return wp_strip_all_tags( $excerpt );
		}

		$content = get_post_field( 'post_content', $id );
		if ( $content ) {
			return wp_trim_words( wp_strip_all_tags( $content ), 28, '…' );
		}
	}

	if ( is_front_page() || is_home() ) {
		return wp_strip_all_tags( fixit_get( 'fixit_hero_text' ) );
	}

	$tagline = get_bloginfo( 'description' );
	return $tagline ? $tagline : wp_strip_all_tags( fixit_get( 'fixit_hero_text' ) );
}

/**
 * Öz SEO başlığımız varsa onu işlədirik.
 */
function fixit_document_title( $parts ) {
	if ( fixit_seo_plugin_active() || ! is_singular() ) {
		return $parts;
	}

	$custom = get_post_meta( get_queried_object_id(), '_fixit_seo_title', true );

	if ( $custom ) {
		$parts['title'] = $custom;
		unset( $parts['tagline'] );
	}

	return $parts;
}
add_filter( 'document_title_parts', 'fixit_document_title' );

/* ============================================================
   3. Robots
   ============================================================ */

/**
 * Axtarış robotları üçün göstərişlər.
 */
function fixit_robots( $robots ) {
	// Zəngin nəticələrdə böyük şəkil və mətn parçası göstərilə bilsin.
	// Dəyərlər mətn olmalıdır — rəqəm verilsə WordPress onları dəyərsiz yazır.
	$robots['max-image-preview'] = 'large';
	$robots['max-snippet']       = '-1';
	$robots['max-video-preview'] = '-1';

	// Axtarış nəticələri və 404 indeksləşdirilməsin.
	if ( is_search() || is_404() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'] );
	}

	// Səhifə üçün əl ilə "noindex" seçilibsə.
	if ( is_singular() && get_post_meta( get_queried_object_id(), '_fixit_seo_noindex', true ) ) {
		$robots['noindex'] = true;
		unset( $robots['index'] );
	}

	return $robots;
}
add_filter( 'wp_robots', 'fixit_robots' );

/* ============================================================
   4. Meta teqlər
   ============================================================ */

/**
 * Şriftlərə əvvəlcədən qoşulma — səhifə bir az tez açılır.
 */
function fixit_resource_hints( $hints, $relation ) {
	if ( 'preconnect' === $relation ) {
		$hints[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $hints;
}
add_filter( 'wp_resource_hints', 'fixit_resource_hints', 10, 2 );

/**
 * Paylaşım üçün standart şəkil.
 */
function fixit_share_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		return get_the_post_thumbnail_url( null, 'full' );
	}

	if ( is_singular( 'fixit_product' ) && function_exists( 'fixit_product_gallery_ids' ) ) {
		$gallery = fixit_product_gallery_ids( get_queried_object_id() );
		if ( $gallery ) {
			return wp_get_attachment_image_url( $gallery[0], 'full' );
		}
	}

	$logo_id = get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		return wp_get_attachment_image_url( $logo_id, 'full' );
	}

	$icon_id = get_option( 'site_icon' );
	if ( $icon_id ) {
		return wp_get_attachment_image_url( $icon_id, 'full' );
	}

	return '';
}

/**
 * <head> içinə meta teqlər.
 */
function fixit_seo_meta() {
	if ( fixit_seo_plugin_active() ) {
		return;
	}

	$desc  = fixit_meta_description();
	$title = wp_get_document_title();
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( array(), $GLOBALS['wp']->request ) );
	$image = fixit_share_image();
	?>
	<meta name="description" content="<?php echo esc_attr( $desc ); ?>">
	<meta property="og:type" content="<?php echo is_singular() && ! is_front_page() ? 'article' : 'website'; ?>">
	<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
	<meta property="og:description" content="<?php echo esc_attr( $desc ); ?>">
	<meta property="og:url" content="<?php echo esc_url( $url ); ?>">
	<meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
	<meta property="og:locale" content="az_AZ">
	<?php if ( $image ) : ?>
		<meta property="og:image" content="<?php echo esc_url( $image ); ?>">
		<meta name="twitter:image" content="<?php echo esc_url( $image ); ?>">
	<?php endif; ?>
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
	<meta name="twitter:description" content="<?php echo esc_attr( $desc ); ?>">
	<?php
	// Qeyd: canonical linki WordPress özü əlavə edir (rel_canonical), təkrarlamırıq.
}
add_action( 'wp_head', 'fixit_seo_meta', 2 );

/* ============================================================
   5. Struktur məlumatlar (Schema.org)
   ============================================================ */

/**
 * Şirkət kartı — Google-da biznes məlumatları üçün.
 */
function fixit_schema_organization() {
	$socials = array_values(
		array_filter(
			array(
				fixit_get( 'fixit_facebook' ),
				fixit_get( 'fixit_instagram' ),
				fixit_get( 'fixit_tiktok' ),
				fixit_get( 'fixit_linkedin' ),
			)
		)
	);

	$schema = array(
		'@context'     => 'https://schema.org',
		'@type'        => 'ProfessionalService',
		'@id'          => home_url( '/#organization' ),
		'name'         => get_bloginfo( 'name' ),
		'description'  => wp_strip_all_tags( fixit_get( 'fixit_hero_text' ) ),
		'url'          => home_url( '/' ),
		'telephone'    => fixit_tel( fixit_get( 'fixit_phone' ) ),
		'email'        => fixit_get( 'fixit_email' ),
		'foundingDate' => fixit_get( 'fixit_year_since' ),
		'areaServed'   => array( 'AZ' ),
		'address'      => array(
			array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => fixit_get( 'fixit_address_main' ),
				'addressLocality' => 'Bakı',
				'addressCountry'  => 'AZ',
			),
			array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => fixit_get( 'fixit_address_br' ),
				'addressLocality' => 'Cəlilabad',
				'addressCountry'  => 'AZ',
			),
		),
		'sameAs'       => $socials,
	);

	$logo = fixit_share_image();
	if ( $logo ) {
		$schema['logo']  = $logo;
		$schema['image'] = $logo;
	}

	// Xidmətlər və məhsullar kataloqu.
	$items = array();

	foreach ( fixit_get_services() as $svc ) {
		$items[] = array(
			'@type'       => 'Offer',
			'itemOffered' => array(
				'@type' => 'Service',
				'name'  => $svc->post_title,
			),
		);
	}

	foreach ( fixit_get_products() as $prod ) {
		$items[] = array(
			'@type'       => 'Offer',
			'itemOffered' => array(
				'@type'               => 'SoftwareApplication',
				'name'                => $prod->post_title,
				'description'         => wp_strip_all_tags( get_post_meta( $prod->ID, '_fixit_short', true ) ),
				'applicationCategory' => 'BusinessApplication',
				'operatingSystem'     => 'Web',
			),
		);
	}

	if ( $items ) {
		$schema['hasOfferCatalog'] = array(
			'@type'           => 'OfferCatalog',
			'name'            => __( 'İKT xidmətləri və məhsulları', 'fixit' ),
			'itemListElement' => $items,
		);
	}

	return $schema;
}

/**
 * Sayt kartı — ad və axtarış imkanı.
 */
function fixit_schema_website() {
	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'WebSite',
		'@id'             => home_url( '/#website' ),
		'url'             => home_url( '/' ),
		'name'            => get_bloginfo( 'name' ),
		'inLanguage'      => 'az-AZ',
		'publisher'       => array( '@id' => home_url( '/#organization' ) ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);
}

/**
 * Naviqasiya zənciri — Google nəticələrində yol göstərir.
 */
function fixit_schema_breadcrumb() {
	if ( is_front_page() || ! is_singular() ) {
		return null;
	}

	$trail = array(
		array( __( 'Ana səhifə', 'fixit' ), home_url( '/' ) ),
	);

	// Məhsul səhifəsi: Ana səhifə → Məhsullar → Məhsul.
	if ( is_singular( 'fixit_product' ) ) {
		$trail[] = array( __( 'Məhsullar', 'fixit' ), fixit_page_url( 'template-products.php' ) );
	}

	$trail[] = array( get_the_title(), get_permalink() );

	$items = array();
	foreach ( $trail as $i => $crumb ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $crumb[0],
			'item'     => $crumb[1],
		);
	}

	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);
}

/**
 * Məhsul səhifəsi — proqram kartı.
 */
function fixit_schema_software() {
	if ( ! is_singular( 'fixit_product' ) ) {
		return null;
	}

	$post   = get_queried_object();
	$schema = array(
		'@context'            => 'https://schema.org',
		'@type'               => 'SoftwareApplication',
		'name'                => $post->post_title,
		'description'         => wp_strip_all_tags( get_post_meta( $post->ID, '_fixit_short', true ) ),
		'url'                 => get_permalink( $post ),
		'applicationCategory' => 'BusinessApplication',
		'operatingSystem'     => 'Web',
		'publisher'           => array( '@id' => home_url( '/#organization' ) ),
	);

	$gallery = function_exists( 'fixit_product_gallery_ids' ) ? fixit_product_gallery_ids( $post->ID ) : array();
	if ( $gallery ) {
		$schema['screenshot'] = wp_get_attachment_image_url( $gallery[0], 'full' );
	}

	return $schema;
}

/**
 * Səhifədə göstərilən suallar — zəngin nəticə üçün.
 */
function fixit_schema_faq() {
	if ( empty( $GLOBALS['fixit_faq_shown'] ) ) {
		return null;
	}

	$items = array();

	foreach ( $GLOBALS['fixit_faq_shown'] as $faq ) {
		$items[] = array(
			'@type'          => 'Question',
			'name'           => $faq[0],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $faq[1],
			),
		);
	}

	return array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $items,
	);
}

/**
 * Bütün struktur məlumatları səhifənin sonunda yazır.
 *
 * Alt hissədə yazılır ki, səhifədə hansı sualların göstərildiyi artıq bəlli olsun.
 */
function fixit_output_schema() {
	$blocks = array(
		fixit_schema_organization(),
		fixit_schema_website(),
		fixit_schema_breadcrumb(),
		fixit_schema_software(),
		fixit_schema_faq(),
	);

	foreach ( $blocks as $block ) {
		if ( ! $block ) {
			continue;
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $block, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "</script>\n";
	}
}
add_action( 'wp_footer', 'fixit_output_schema', 20 );
