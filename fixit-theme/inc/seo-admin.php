<?php
/**
 * Admin panel: SEO vəziyyətinin izlənməsi.
 *
 * Menyu: FIXIT SEO
 *
 * İki hissədən ibarətdir:
 *  1) Sayt səviyyəsində yoxlamalar (indeksləşmə, linklər, sitemap və s.)
 *  2) Hər səhifə üzrə cədvəl — başlıq/təsvir uzunluğu, indeksləşmə, bal
 *
 * Bütün yoxlamalar səhifə açılanda canlı hesablanır; heç bir xarici
 * xidmətə sorğu getmir və heç nə saxlanılmır.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   1. Menyu
   ============================================================ */
function fixit_seo_admin_menu() {
	add_menu_page(
		__( 'FIXIT SEO', 'fixit' ),
		__( 'FIXIT SEO', 'fixit' ),
		'edit_posts',
		'fixit-seo',
		'fixit_seo_admin_page',
		'dashicons-chart-area',
		19
	);
}
add_action( 'admin_menu', 'fixit_seo_admin_menu' );

/* ============================================================
   2. Sayt səviyyəsində yoxlamalar
   ============================================================ */

/**
 * Hər yoxlama: ad, vəziyyət (ok / warn / bad), izah, düzəliş linki.
 */
function fixit_seo_site_checks() {
	$checks = array();

	// Axtarış sistemlərinə görünürlük.
	$public   = (int) get_option( 'blog_public' );
	$checks[] = array(
		'label' => __( 'Axtarış sistemlərinə görünürlük', 'fixit' ),
		'state' => $public ? 'ok' : 'bad',
		'note'  => $public
			? __( 'Sayt indeksləşməyə açıqdır.', 'fixit' )
			: __( 'Sayt axtarış sistemlərindən gizlədilib! Google saytı tapa bilməyəcək.', 'fixit' ),
		'fix'   => admin_url( 'options-reading.php' ),
	);

	// Daimi linklər.
	$perma    = get_option( 'permalink_structure' );
	$checks[] = array(
		'label' => __( 'Daimi linklərin quruluşu', 'fixit' ),
		'state' => $perma ? 'ok' : 'bad',
		'note'  => $perma
			? __( 'Linklər oxunaqlıdır.', 'fixit' )
			: __( 'Linklər "?p=123" formasındadır. "Yazı adı" seçimi SEO üçün daha yaxşıdır.', 'fixit' ),
		'fix'   => admin_url( 'options-permalink.php' ),
	);

	// Sayt başlığı və şüarı.
	$name     = get_bloginfo( 'name' );
	$tagline  = get_bloginfo( 'description' );
	$default  = ( 'Just another WordPress site' === $tagline );
	$checks[] = array(
		'label' => __( 'Sayt adı və şüarı', 'fixit' ),
		'state' => ( $name && $tagline && ! $default ) ? 'ok' : 'warn',
		'note'  => ( $name && $tagline && ! $default )
			? sprintf( '%s — %s', $name, $tagline )
			: __( 'Şüar boşdur və ya standart mətndir. Şirkəti bir cümlə ilə təsvir edin.', 'fixit' ),
		'fix'   => admin_url( 'options-general.php' ),
	);

	// HTTPS.
	$https    = ( 0 === strpos( home_url(), 'https://' ) );
	$checks[] = array(
		'label' => __( 'Təhlükəsiz bağlantı (HTTPS)', 'fixit' ),
		'state' => $https ? 'ok' : 'bad',
		'note'  => $https
			? __( 'Sayt SSL üzərindən işləyir.', 'fixit' )
			: __( 'Sayt HTTP üzərindədir. Hostinqdən pulsuz SSL aktivləşdirin.', 'fixit' ),
		'fix'   => '',
	);

	// Sitemap.
	$sitemap  = function_exists( 'wp_sitemaps_get_server' ) && wp_sitemaps_get_server()->sitemaps_enabled();
	$checks[] = array(
		'label' => __( 'XML sitemap', 'fixit' ),
		'state' => $sitemap ? 'ok' : 'warn',
		'note'  => $sitemap
			? sprintf( __( 'Aktivdir: %s', 'fixit' ), home_url( '/wp-sitemap.xml' ) )
			: __( 'Sitemap söndürülüb. Google səhifələri gec tapa bilər.', 'fixit' ),
		'fix'   => $sitemap ? home_url( '/wp-sitemap.xml' ) : '',
	);

	// Sayt ikonu.
	$icon     = (int) get_option( 'site_icon' );
	$checks[] = array(
		'label' => __( 'Sayt ikonu (favicon)', 'fixit' ),
		'state' => $icon ? 'ok' : 'warn',
		'note'  => $icon
			? __( 'Təyin olunub.', 'fixit' )
			: __( 'Təyin olunmayıb. Brend tanınması üçün əlavə edin.', 'fixit' ),
		'fix'   => admin_url( 'customize.php' ),
	);

	// Loqo.
	$logo     = (int) get_theme_mod( 'custom_logo' );
	$checks[] = array(
		'label' => __( 'Loqo', 'fixit' ),
		'state' => $logo ? 'ok' : 'warn',
		'note'  => $logo
			? __( 'Yüklənib — sosial şəbəkə paylaşımlarında da işlədilir.', 'fixit' )
			: __( 'Yüklənməyib. Paylaşım şəkli və Google şirkət kartı üçün faydalıdır.', 'fixit' ),
		'fix'   => admin_url( 'customize.php' ),
	);

	// Əlaqə məlumatları (struktur məlumatlar üçün vacibdir).
	$phone    = fixit_get( 'fixit_phone' );
	$email    = fixit_get( 'fixit_email' );
	$checks[] = array(
		'label' => __( 'Əlaqə məlumatları', 'fixit' ),
		'state' => ( $phone && $email ) ? 'ok' : 'warn',
		'note'  => ( $phone && $email )
			? sprintf( '%s · %s', $phone, $email )
			: __( 'Telefon və ya e-poçt boşdur. Google şirkət kartında görünmür.', 'fixit' ),
		'fix'   => admin_url( 'customize.php' ),
	);

	// Məzmun.
	$svc      = count( fixit_get_services() );
	$prod     = count( fixit_get_products() );
	$checks[] = array(
		'label' => __( 'Xidmət və məhsul məzmunu', 'fixit' ),
		'state' => ( $svc && $prod ) ? 'ok' : 'warn',
		'note'  => sprintf(
			/* translators: 1: xidmət sayı, 2: məhsul sayı */
			__( '%1$d xidmət, %2$d məhsul dərc olunub.', 'fixit' ),
			$svc,
			$prod
		),
		'fix'   => admin_url( 'edit.php?post_type=fixit_service' ),
	);

	// SEO plagini.
	if ( fixit_seo_plugin_active() ) {
		$checks[] = array(
			'label' => __( 'SEO plagini', 'fixit' ),
			'state' => 'warn',
			'note'  => __( 'Xarici SEO plagini aktivdir — meta teqləri o idarə edir, temanın SEO sahələri söndürülüb.', 'fixit' ),
			'fix'   => '',
		);
	}

	return $checks;
}

/* ============================================================
   3. Səhifə səviyyəsində yoxlamalar
   ============================================================ */

/**
 * İndeksləşməli bütün məzmunu gətirir.
 */
function fixit_seo_content_list() {
	return get_posts(
		array(
			'post_type'      => array( 'page', 'post', 'fixit_service', 'fixit_product' ),
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'orderby'        => 'post_type',
			'order'          => 'ASC',
		)
	);
}

/**
 * Bir səhifənin SEO vəziyyətini hesablayır.
 *
 * @param WP_Post $post Yazı.
 * @return array
 */
function fixit_seo_analyze( $post ) {
	$seo_title = get_post_meta( $post->ID, '_fixit_seo_title', true );
	$seo_desc  = get_post_meta( $post->ID, '_fixit_seo_desc', true );
	$noindex   = get_post_meta( $post->ID, '_fixit_seo_noindex', true );

	// Faktiki başlıq: öz SEO başlığımız yoxdursa, yazının adı + sayt adı.
	$title = $seo_title ? $seo_title : $post->post_title . ' – ' . get_bloginfo( 'name' );

	// Faktiki təsvir — saytda hansı mətn çıxırsa, burada da o görünməlidir.
	$desc = $seo_desc;
	if ( ! $desc && (int) get_option( 'page_on_front' ) === $post->ID ) {
		$desc = wp_strip_all_tags( fixit_get( 'fixit_hero_text' ) );
	}
	if ( ! $desc ) {
		$desc = fixit_template_description( $post->ID );
	}
	if ( ! $desc && $post->post_excerpt ) {
		$desc = $post->post_excerpt;
	}
	if ( ! $desc && $post->post_content ) {
		$desc = wp_trim_words( wp_strip_all_tags( $post->post_content ), 28, '…' );
	}

	$title_len = mb_strlen( $title );
	$desc_len  = mb_strlen( (string) $desc );

	$issues = array();
	$passed = 0;
	$total  = 3;

	// 1) Başlıq uzunluğu.
	if ( $title_len >= FIXIT_TITLE_MIN && $title_len <= FIXIT_TITLE_MAX ) {
		++$passed;
	} elseif ( $title_len > FIXIT_TITLE_MAX ) {
		$issues[] = __( 'Başlıq uzundur — Google onu kəsəcək.', 'fixit' );
	} else {
		$issues[] = __( 'Başlıq qısadır — açar sözlər əlavə edin.', 'fixit' );
	}

	// 2) Təsvir.
	if ( ! $desc_len ) {
		$issues[] = __( 'Təsvir yoxdur — Google özü təsadüfi mətn seçəcək.', 'fixit' );
	} elseif ( $desc_len < FIXIT_DESC_MIN ) {
		$issues[] = __( 'Təsvir qısadır.', 'fixit' );
	} elseif ( $desc_len > FIXIT_DESC_MAX ) {
		$issues[] = __( 'Təsvir uzundur — kəsiləcək.', 'fixit' );
	} else {
		++$passed;
	}

	// 3) İndeksləşmə.
	if ( $noindex ) {
		$issues[] = __( 'Bu səhifə axtarışdan gizlədilib (noindex).', 'fixit' );
	} else {
		++$passed;
	}

	return array(
		'post'      => $post,
		'title'     => $title,
		'title_len' => $title_len,
		'desc'      => $desc,
		'desc_len'  => $desc_len,
		'noindex'   => (bool) $noindex,
		'issues'    => $issues,
		'score'     => (int) round( $passed / $total * 100 ),
		'custom'    => (bool) ( $seo_title || $seo_desc ),
	);
}

/* ============================================================
   4. Səhifənin özü
   ============================================================ */

function fixit_seo_admin_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$site_checks = fixit_seo_site_checks();
	$rows        = array();
	$score_sum   = 0;

	foreach ( fixit_seo_content_list() as $post ) {
		$row = fixit_seo_analyze( $post );

		$rows[]     = $row;
		$score_sum += $row['score'];
	}

	$avg      = $rows ? (int) round( $score_sum / count( $rows ) ) : 0;
	$problems = 0;
	foreach ( $rows as $row ) {
		if ( $row['issues'] ) {
			++$problems;
		}
	}

	$site_bad  = 0;
	$site_warn = 0;
	foreach ( $site_checks as $check ) {
		if ( 'bad' === $check['state'] ) {
			++$site_bad;
		} elseif ( 'warn' === $check['state'] ) {
			++$site_warn;
		}
	}
	?>
	<style>
		.fixit-seo-wrap{max-width:1180px}
		.fixit-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin:20px 0 28px}
		.fixit-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px 20px}
		.fixit-card b{display:block;font-size:30px;line-height:1.1;margin-bottom:4px}
		.fixit-card span{color:#646970;font-size:13px}
		.fixit-card.ok b{color:#008a20}
		.fixit-card.warn b{color:#b26200}
		.fixit-card.bad b{color:#b32d2e}
		.fixit-list{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:4px 20px;margin-bottom:28px}
		.fixit-list .row{display:flex;gap:14px;align-items:flex-start;padding:14px 0;border-bottom:1px solid #f0f0f1}
		.fixit-list .row:last-child{border-bottom:0}
		.fixit-dot{width:10px;height:10px;border-radius:50%;flex:none;margin-top:6px}
		.fixit-dot.ok{background:#008a20}
		.fixit-dot.warn{background:#dba617}
		.fixit-dot.bad{background:#d63638}
		.fixit-list strong{display:block}
		.fixit-list p{margin:2px 0 0;color:#646970;font-size:13px}
		.fixit-len{font-weight:600}
		.fixit-len.ok{color:#008a20}
		.fixit-len.warn{color:#b26200}
		.fixit-len.bad{color:#b32d2e}
		.fixit-issues{margin:4px 0 0;padding-left:16px;color:#b26200;font-size:12px}
		.fixit-preview{max-width:520px}
		.fixit-preview .t{color:#1a0dab;font-size:15px;line-height:1.3}
		.fixit-preview .u{color:#006621;font-size:12px}
		.fixit-preview .d{color:#545454;font-size:12.5px;line-height:1.45}
		.fixit-score{font-weight:700}
	</style>

	<div class="wrap fixit-seo-wrap">
		<h1><?php esc_html_e( 'FIXIT SEO', 'fixit' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Saytın axtarış sistemlərinə hazırlığı. Məlumat səhifə açılanda canlı hesablanır — heç bir xarici xidmətə sorğu getmir.', 'fixit' ); ?>
		</p>

		<!-- Ümumi göstəricilər -->
		<div class="fixit-cards">
			<div class="fixit-card <?php echo esc_attr( $avg >= 80 ? 'ok' : ( $avg >= 50 ? 'warn' : 'bad' ) ); ?>">
				<b><?php echo esc_html( $avg ); ?>%</b>
				<span><?php esc_html_e( 'orta SEO balı', 'fixit' ); ?></span>
			</div>
			<div class="fixit-card <?php echo esc_attr( $problems ? 'warn' : 'ok' ); ?>">
				<b><?php echo esc_html( $problems ); ?></b>
				<span><?php esc_html_e( 'diqqət tələb edən səhifə', 'fixit' ); ?></span>
			</div>
			<div class="fixit-card <?php echo esc_attr( $site_bad ? 'bad' : ( $site_warn ? 'warn' : 'ok' ) ); ?>">
				<b><?php echo esc_html( $site_bad + $site_warn ); ?></b>
				<span><?php esc_html_e( 'sayt ayarı problemi', 'fixit' ); ?></span>
			</div>
			<div class="fixit-card ok">
				<b><?php echo esc_html( count( $rows ) ); ?></b>
				<span><?php esc_html_e( 'indeksləşən səhifə', 'fixit' ); ?></span>
			</div>
		</div>

		<!-- Sayt ayarları -->
		<h2><?php esc_html_e( 'Sayt ayarları', 'fixit' ); ?></h2>
		<div class="fixit-list">
			<?php foreach ( $site_checks as $check ) : ?>
				<div class="row">
					<span class="fixit-dot <?php echo esc_attr( $check['state'] ); ?>"></span>
					<div style="flex:1">
						<strong><?php echo esc_html( $check['label'] ); ?></strong>
						<p><?php echo esc_html( $check['note'] ); ?></p>
					</div>
					<?php if ( $check['fix'] && 'ok' !== $check['state'] ) : ?>
						<a class="button button-small" href="<?php echo esc_url( $check['fix'] ); ?>">
							<?php esc_html_e( 'Düzəlt', 'fixit' ); ?>
						</a>
					<?php elseif ( $check['fix'] ) : ?>
						<a class="button button-small" href="<?php echo esc_url( $check['fix'] ); ?>" target="_blank" rel="noopener">
							<?php esc_html_e( 'Bax', 'fixit' ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Səhifələr -->
		<h2><?php esc_html_e( 'Səhifələr üzrə vəziyyət', 'fixit' ); ?></h2>
		<p class="description" style="margin-bottom:12px">
			<?php esc_html_e( 'Başlıq və təsviri dəyişmək üçün yazını açın — redaktə səhifəsində "SEO" qutusu var.', 'fixit' ); ?>
		</p>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th style="width:24%"><?php esc_html_e( 'Səhifə', 'fixit' ); ?></th>
					<th style="width:38%"><?php esc_html_e( 'Google-da necə görünür', 'fixit' ); ?></th>
					<th style="width:8%"><?php esc_html_e( 'Başlıq', 'fixit' ); ?></th>
					<th style="width:8%"><?php esc_html_e( 'Təsvir', 'fixit' ); ?></th>
					<th style="width:14%"><?php esc_html_e( 'Qeydlər', 'fixit' ); ?></th>
					<th style="width:8%"><?php esc_html_e( 'Bal', 'fixit' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<?php
					$type_obj  = get_post_type_object( $row['post']->post_type );
					$title_cls = ( $row['title_len'] >= FIXIT_TITLE_MIN && $row['title_len'] <= FIXIT_TITLE_MAX ) ? 'ok' : ( $row['title_len'] > FIXIT_TITLE_MAX ? 'bad' : 'warn' );
					$desc_cls  = ( $row['desc_len'] >= FIXIT_DESC_MIN && $row['desc_len'] <= FIXIT_DESC_MAX ) ? 'ok' : ( 0 === $row['desc_len'] ? 'bad' : 'warn' );
					?>
					<tr>
						<td>
							<strong><a href="<?php echo esc_url( get_edit_post_link( $row['post']->ID ) ); ?>"><?php echo esc_html( $row['post']->post_title ); ?></a></strong>
							<div style="color:#646970;font-size:12px">
								<?php echo esc_html( $type_obj ? $type_obj->labels->singular_name : $row['post']->post_type ); ?>
								<?php if ( $row['custom'] ) : ?>
									· <span style="color:#008a20"><?php esc_html_e( 'əl ilə yazılıb', 'fixit' ); ?></span>
								<?php endif; ?>
							</div>
						</td>
						<td>
							<div class="fixit-preview">
								<div class="t"><?php echo esc_html( $row['title'] ); ?></div>
								<div class="u"><?php echo esc_html( get_permalink( $row['post']->ID ) ); ?></div>
								<div class="d">
									<?php echo $row['desc'] ? esc_html( $row['desc'] ) : '<em style="color:#b32d2e">' . esc_html__( '(təsvir yoxdur)', 'fixit' ) . '</em>'; ?>
								</div>
							</div>
						</td>
						<td><span class="fixit-len <?php echo esc_attr( $title_cls ); ?>"><?php echo esc_html( $row['title_len'] ); ?></span></td>
						<td><span class="fixit-len <?php echo esc_attr( $desc_cls ); ?>"><?php echo esc_html( $row['desc_len'] ); ?></span></td>
						<td>
							<?php if ( $row['issues'] ) : ?>
								<ul class="fixit-issues">
									<?php foreach ( $row['issues'] as $issue ) : ?>
										<li><?php echo esc_html( $issue ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php else : ?>
								<span style="color:#008a20">✓ <?php esc_html_e( 'qaydasındadır', 'fixit' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<span class="fixit-score" style="color:<?php echo esc_attr( $row['score'] >= 80 ? '#008a20' : ( $row['score'] >= 50 ? '#b26200' : '#b32d2e' ) ); ?>">
								<?php echo esc_html( $row['score'] ); ?>%
							</span>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<!-- Faydalı linklər -->
		<h2 style="margin-top:34px"><?php esc_html_e( 'Növbəti addımlar', 'fixit' ); ?></h2>
		<div class="fixit-list">
			<div class="row">
				<span class="fixit-dot ok"></span>
				<div>
					<strong><?php esc_html_e( 'Google Search Console-a saytı əlavə edin', 'fixit' ); ?></strong>
					<p><?php esc_html_e( 'Saytın Google-da hansı sözlərlə tapıldığını, neçə kliklə gəldiyini və xətaları orada görürsünüz. Sitemap ünvanını da ora əlavə edin.', 'fixit' ); ?></p>
				</div>
				<a class="button button-small" href="https://search.google.com/search-console" target="_blank" rel="noopener"><?php esc_html_e( 'Aç', 'fixit' ); ?></a>
			</div>
			<div class="row">
				<span class="fixit-dot ok"></span>
				<div>
					<strong><?php esc_html_e( 'Struktur məlumatları yoxlayın', 'fixit' ); ?></strong>
					<p><?php esc_html_e( 'Tema şirkət kartı, naviqasiya zənciri və sual-cavab məlumatlarını avtomatik göndərir. Düzgün oxunduğunu Google-un test alətində yoxlaya bilərsiniz.', 'fixit' ); ?></p>
				</div>
				<a class="button button-small" href="https://search.google.com/test/rich-results?url=<?php echo rawurlencode( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Test et', 'fixit' ); ?></a>
			</div>
			<div class="row">
				<span class="fixit-dot ok"></span>
				<div>
					<strong><?php esc_html_e( 'Google Biznes Profili', 'fixit' ); ?></strong>
					<p><?php esc_html_e( 'Yerli axtarışda (xəritədə) görünmək üçün Bakı və Cəlilabad ofislərini qeydə alın — İT xidmətlərində ən çox müraciət oradan gəlir.', 'fixit' ); ?></p>
				</div>
				<a class="button button-small" href="https://business.google.com" target="_blank" rel="noopener"><?php esc_html_e( 'Aç', 'fixit' ); ?></a>
			</div>
		</div>
	</div>
	<?php
}
