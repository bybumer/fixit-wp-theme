<?php
/**
 * Ana səhifə.
 *
 * @package FIXIT
 */

get_header();

$fixit_services     = fixit_get_services( 6 );
$fixit_products     = fixit_get_products( 4 );
$fixit_reviews      = fixit_get_reviews( 3 );
$fixit_contact_url  = fixit_page_url( 'template-contact.php' );
$fixit_services_url = fixit_page_url( 'template-services.php' );
$fixit_products_url = fixit_page_url( 'template-products.php' );
$fixit_about_url    = fixit_page_url( 'template-about.php' );
$fixit_remote_url   = fixit_page_url( 'template-remote.php' );
$fixit_clients      = fixit_get_clients();
$fixit_phone        = fixit_get( 'fixit_phone' );
?>

<!-- ============================ HERO ============================ -->
<section class="hero">
	<div class="container hero__inner">

		<div>
			<span class="eyebrow"><?php fixit_icon( 'zap' ); ?><?php echo esc_html( fixit_get( 'fixit_year_since' ) ); ?><?php esc_html_e( '-ci ildən etibarən', 'fixit' ); ?></span>

			<h1><?php echo wp_kses_post( fixit_get( 'fixit_hero_title' ) ); ?></h1>

			<p class="lead"><?php echo wp_kses_post( fixit_get( 'fixit_hero_text' ) ); ?></p>

			<div class="hero__cta">
				<a class="btn btn--primary" href="<?php echo esc_url( $fixit_contact_url ); ?>">
					<?php fixit_icon( 'send' ); ?><?php esc_html_e( 'Pulsuz konsultasiya', 'fixit' ); ?>
				</a>
				<a class="btn btn--light" href="<?php echo esc_url( $fixit_services_url ); ?>">
					<?php esc_html_e( 'Xidmətlərə bax', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?>
				</a>
			</div>

			<div class="hero__stats">
				<div>
					<b><?php echo esc_html( fixit_get( 'fixit_stat_clients' ) ); ?></b>
					<span><?php esc_html_e( 'korporativ müştəri', 'fixit' ); ?></span>
				</div>
				<div>
					<b><?php echo esc_html( fixit_get( 'fixit_stat_project' ) ); ?></b>
					<span><?php esc_html_e( 'tamamlanmış layihə', 'fixit' ); ?></span>
				</div>
				<div>
					<b><?php echo esc_html( fixit_get( 'fixit_stat_years' ) ); ?></b>
					<span><?php esc_html_e( 'il təcrübə', 'fixit' ); ?></span>
				</div>
			</div>
		</div>

		<!-- Sağ kart -->
		<div class="hero-card reveal">
			<div class="hero-card__head">
				<span class="dot" aria-hidden="true"></span>
				<strong><?php esc_html_e( 'Xidmət istiqamətlərimiz', 'fixit' ); ?></strong>
				<small><?php esc_html_e( 'aktiv', 'fixit' ); ?></small>
			</div>

			<ul class="hero-list">
				<?php
				$fixit_hero_items = array(
					array( 'computer', __( 'Kompüter servisi', 'fixit' ), __( 'pulsuz analiz', 'fixit' ) ),
					array( 'camera', __( 'Video müşahidə', 'fixit' ), __( 'uzaqdan baxış', 'fixit' ) ),
					array( 'cloud', __( 'Bulud serverlər', 'fixit' ), __( 'yedəkli', 'fixit' ) ),
					array( 'phone', __( 'IP telefoniya', 'fixit' ), __( '3CX / Yeastar', 'fixit' ) ),
					array( 'support', __( 'İT dəstək', 'fixit' ), __( 'abunə', 'fixit' ) ),
				);
				foreach ( $fixit_hero_items as $fixit_item ) :
					?>
					<li>
						<span class="ico"><?php fixit_icon( $fixit_item[0] ); ?></span>
						<span><?php echo esc_html( $fixit_item[1] ); ?></span>
						<span class="tag"><?php echo esc_html( $fixit_item[2] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

	</div>
</section>

<!-- ==================== TƏRƏFDAŞ / TEXNOLOGİYA ==================== -->
<div class="marquee">
	<div class="marquee__label"><?php esc_html_e( 'İşlədiyimiz texnologiyalar və tərəfdaşlar', 'fixit' ); ?></div>
	<div class="marquee__track">
		<?php
		$fixit_brands = array( 'Hikvision', 'Dahua', '3CX', 'Yeastar', 'Kerio', 'MikroTik', 'Ubiquiti', 'Microsoft 365', 'Dell', 'HP', 'Cisco', 'VMware' );
		// İki dəfə yazılır ki, lent fasiləsiz görünsün.
		for ( $fixit_i = 0; $fixit_i < 2; $fixit_i++ ) {
			foreach ( $fixit_brands as $fixit_brand ) {
				echo '<span>' . esc_html( $fixit_brand ) . '</span>';
			}
		}
		?>
	</div>
</div>

<!-- ============================ MÜŞTƏRİLƏR ============================ -->
<?php if ( $fixit_clients ) : ?>
	<section class="section section--tight">
		<div class="container">
			<div class="section-head section-head--center reveal">
				<span class="eyebrow"><?php fixit_icon( 'users' ); ?><?php esc_html_e( 'Bizə etibar edirlər', 'fixit' ); ?></span>
				<h2><?php esc_html_e( 'Bizimlə işləyən şirkətlər', 'fixit' ); ?></h2>
			</div>

			<div class="clients reveal">
				<?php foreach ( $fixit_clients as $fixit_client ) : ?>
					<?php $fixit_logo = fixit_client_logo( $fixit_client ); ?>
					<div class="clients__item" title="<?php echo esc_attr( $fixit_client->post_title ); ?>">
						<?php if ( $fixit_logo ) : ?>
							<?php echo wp_kses_post( $fixit_logo ); ?>
						<?php else : ?>
							<span><?php echo esc_html( $fixit_client->post_title ); ?></span>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<!-- ============================ XİDMƏTLƏR ============================ -->
<section class="section">
	<div class="container">
		<div class="section-head section-head--center reveal">
			<span class="eyebrow"><?php esc_html_e( 'Xidmətlərimiz', 'fixit' ); ?></span>
			<h2><?php esc_html_e( 'Bir tərəfdaş — ', 'fixit' ); ?><span class="grad-text"><?php esc_html_e( 'bütün İKT ehtiyaclarınız', 'fixit' ); ?></span></h2>
			<p><?php esc_html_e( 'Kiçik və böyük şirkətlər üçün müasir informasiya-kommunikasiya texnologiyaları xidmətləri təqdim edirik.', 'fixit' ); ?></p>
		</div>

		<div class="grid grid--3">
			<?php foreach ( $fixit_services as $fixit_svc ) : ?>
				<?php
				$fixit_icon_key = get_post_meta( $fixit_svc->ID, '_fixit_icon', true );
				$fixit_short    = get_post_meta( $fixit_svc->ID, '_fixit_short', true );
				if ( ! $fixit_short ) {
					$fixit_short = wp_trim_words( $fixit_svc->post_content, 22 );
				}
				?>
				<a class="card reveal" href="<?php echo esc_url( $fixit_services_url . '#xidmet-' . $fixit_svc->ID ); ?>">
					<span class="card__icon"><?php fixit_icon( $fixit_icon_key ? $fixit_icon_key : 'shield' ); ?></span>
					<h3><?php echo esc_html( $fixit_svc->post_title ); ?></h3>
					<p><?php echo esc_html( $fixit_short ); ?></p>
					<span class="card__link"><?php esc_html_e( 'Ətraflı', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ============================ MƏHSULLAR ============================ -->
<?php if ( $fixit_products ) : ?>
	<section class="section section--dark">
		<div class="container">
			<div class="section-head section-head--center reveal">
				<span class="eyebrow"><?php fixit_icon( 'zap' ); ?><?php esc_html_e( 'Öz məhsullarımız', 'fixit' ); ?></span>
				<h2><?php esc_html_e( 'Sıfırdan özümüzün yazdığı', 'fixit' ); ?> <span style="background:linear-gradient(100deg,#6fd0ff,#7bf7d8);-webkit-background-clip:text;background-clip:text;color:transparent"><?php esc_html_e( 'proqram təminatı', 'fixit' ); ?></span></h2>
				<p><?php esc_html_e( 'Sizin öz serverinizdə işləyir, interfeysi Azərbaycan dilindədir və şirkətinizin iş axınına uyğunlaşdırıla bilir.', 'fixit' ); ?></p>
			</div>

			<div class="grid <?php echo count( $fixit_products ) > 2 ? 'grid--3' : 'grid--2'; ?>">
				<?php foreach ( $fixit_products as $fixit_prod ) : ?>
					<?php
					$fixit_p_icon  = get_post_meta( $fixit_prod->ID, '_fixit_icon', true );
					$fixit_p_short = get_post_meta( $fixit_prod->ID, '_fixit_short', true );
					$fixit_p_feat  = array_slice( fixit_meta_lines( $fixit_prod->ID, '_fixit_features' ), 0, 3 );

					if ( ! $fixit_p_short ) {
						$fixit_p_short = wp_trim_words( $fixit_prod->post_content, 26 );
					}
					?>
					<a class="card reveal" href="<?php echo esc_url( get_permalink( $fixit_prod ) ); ?>">
						<span class="badge"><?php fixit_icon( 'award' ); ?><?php esc_html_e( 'FIXIT məhsulu', 'fixit' ); ?></span>
						<span class="card__icon"><?php fixit_icon( $fixit_p_icon ? $fixit_p_icon : 'server' ); ?></span>
						<h3><?php echo esc_html( $fixit_prod->post_title ); ?></h3>
						<p><?php echo esc_html( $fixit_p_short ); ?></p>

						<?php if ( $fixit_p_feat ) : ?>
							<ul class="bullets" style="margin-top:16px">
								<?php foreach ( $fixit_p_feat as $fixit_pf ) : ?>
									<li><?php fixit_icon( 'check-c' ); ?><span><?php echo esc_html( $fixit_pf ); ?></span></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

						<span class="card__link"><?php esc_html_e( 'Ətraflı və demo', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>

			<div style="text-align:center;margin-top:34px">
				<a class="btn btn--white" href="<?php echo esc_url( $fixit_products_url ); ?>">
					<?php esc_html_e( 'Məhsullar və satış şərtləri', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?>
				</a>
			</div>
		</div>
	</section>
<?php endif; ?>

<!-- ============================ UZAQDAN DƏSTƏK ============================ -->
<section class="section">
	<div class="container">
		<div class="demo-box reveal">
			<div class="demo-box__text">
				<span class="eyebrow"><?php fixit_icon( 'zap' ); ?><?php esc_html_e( 'Pulsuz proqram', 'fixit' ); ?></span>
				<h2><?php esc_html_e( 'Uzaqdan qoşulma', 'fixit' ); ?> — <span class="grad-text"><?php esc_html_e( 'öz proqramımız', 'fixit' ); ?></span></h2>
				<p><?php esc_html_e( 'Olduğunuz yerdən başqa kompüterə qoşulun: yaxınınıza kömək edin, evdə qalan maşınınıza çıxın, iş yoldaşınızla eyni ekrana baxın. Qeydiyyat yoxdur, ödəniş yoxdur — qoşulmağa isə hər dəfə qarşı tərəf icazə verir.', 'fixit' ); ?></p>

				<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:22px;align-items:center">
					<?php if ( FIXIT_REMOTE_WINDOWS_READY ) : ?>
						<a class="btn btn--primary" href="<?php echo esc_url( FIXIT_REMOTE_WINDOWS ); ?>" rel="nofollow">
							<?php fixit_icon( 'download' ); ?><?php esc_html_e( 'Pulsuz yüklə', 'fixit' ); ?>
						</a>
					<?php else : ?>
						<span class="chip"><?php fixit_icon( 'clock' ); ?><?php esc_html_e( 'Tezliklə', 'fixit' ); ?></span>
					<?php endif; ?>

					<?php if ( $fixit_remote_url ) : ?>
						<a class="btn btn--ghost" href="<?php echo esc_url( $fixit_remote_url ); ?>">
							<?php esc_html_e( 'Necə işləyir', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<ol class="demo-steps">
				<li><?php esc_html_e( 'Hər iki tərəf proqramı yükləyir', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'Qarşı tərəfin nömrəsini yazırsınız', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'O, icazə verir — ekran açılır', 'fixit' ); ?></li>
			</ol>
		</div>
	</div>
</section>

<!-- ============================ NİYƏ BİZ ============================ -->
<section class="section section--soft">
	<div class="container split">

		<div class="reveal">
			<span class="eyebrow"><?php esc_html_e( 'Niyə FIXIT?', 'fixit' ); ?></span>
			<h2 style="font-size:clamp(1.75rem,3.4vw,2.5rem);margin:16px 0 14px">
				<?php esc_html_e( 'Texnologiyanı sizin üçün', 'fixit' ); ?> <span class="grad-text"><?php esc_html_e( 'sadə və dayanıqlı', 'fixit' ); ?></span> <?php esc_html_e( 'edirik', 'fixit' ); ?>
			</h2>
			<p style="color:var(--text-soft)">
				<?php esc_html_e( 'Beynəlxalq sertifikatlı mühəndislərdən ibarət komandamız problemi yaranmamışdan qabaq görməyə çalışır. Bizim üçün əsas göstərici — sizin sisteminizin fasiləsiz işləməsidir.', 'fixit' ); ?>
			</p>

			<ul class="feature-list">
				<?php
				$fixit_reasons = array(
					array( __( 'Sertifikatlı mühəndis komandası', 'fixit' ), __( 'Hər istiqamət üzrə ixtisaslaşmış, təcrübəli mütəxəssislər.', 'fixit' ) ),
					array( __( 'Operativ reaksiya', 'fixit' ), __( 'Uzaqdan dəstək dəqiqələr içində, yerində dəstək razılaşdırılmış müddətdə.', 'fixit' ) ),
					array( __( 'Şəffaf qiymət', 'fixit' ), __( 'Diaqnostika pulsuz, iş başlamazdan əvvəl dəqiq smeta təqdim olunur.', 'fixit' ) ),
					array( __( 'Vahid məsuliyyət', 'fixit' ), __( 'Kamera, server, mail, telefoniya — hamısı bir komandanın nəzarətində.', 'fixit' ) ),
				);
				foreach ( $fixit_reasons as $fixit_reason ) :
					?>
					<li>
						<span class="tick"><?php fixit_icon( 'check' ); ?></span>
						<div>
							<h4><?php echo esc_html( $fixit_reason[0] ); ?></h4>
							<p><?php echo esc_html( $fixit_reason[1] ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>

			<div style="margin-top:30px">
				<a class="btn btn--primary" href="<?php echo esc_url( $fixit_about_url ); ?>">
					<?php esc_html_e( 'Haqqımızda ətraflı', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?>
				</a>
			</div>
		</div>

		<div class="stat-grid reveal">
			<div class="stat">
				<b><?php echo esc_html( fixit_get( 'fixit_stat_clients' ) ); ?></b>
				<span><?php esc_html_e( 'əməkdaşlıq etdiyimiz şirkət', 'fixit' ); ?></span>
			</div>
			<div class="stat">
				<b><?php echo esc_html( fixit_get( 'fixit_stat_project' ) ); ?></b>
				<span><?php esc_html_e( 'uğurla tamamlanmış layihə', 'fixit' ); ?></span>
			</div>
			<div class="stat">
				<b><?php echo esc_html( fixit_get( 'fixit_stat_years' ) ); ?></b>
				<span><?php esc_html_e( 'il sahə təcrübəsi', 'fixit' ); ?></span>
			</div>
			<div class="stat">
				<b><?php echo esc_html( fixit_get( 'fixit_stat_support' ) ); ?></b>
				<span><?php esc_html_e( 'monitorinq və dəstək', 'fixit' ); ?></span>
			</div>
		</div>

	</div>
</section>

<!-- ============================ İŞ PROSESİ ============================ -->
<section class="section">
	<div class="container">
		<div class="section-head section-head--center reveal">
			<span class="eyebrow"><?php esc_html_e( 'İş prosesi', 'fixit' ); ?></span>
			<h2><?php esc_html_e( 'Necə işləyirik?', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'İlk müraciətdən daimi dəstəyə qədər — sadə və şəffaf dörd addım.', 'fixit' ); ?></p>
		</div>

		<div class="steps">
			<?php
			$fixit_steps = array(
				array( __( 'Müraciət', 'fixit' ), __( 'Zəng, mesaj və ya formu doldurursunuz. Ehtiyacınızı dinləyirik.', 'fixit' ) ),
				array( __( 'Analiz', 'fixit' ), __( 'Mövcud infrastruktur pulsuz yoxlanılır, problemlər müəyyən edilir.', 'fixit' ) ),
				array( __( 'Həll', 'fixit' ), __( 'Uyğun texniki həll və dəqiq smeta təqdim olunur, iş icra edilir.', 'fixit' ) ),
				array( __( 'Dəstək', 'fixit' ), __( 'Quraşdırmadan sonra da yanınızdayıq: monitorinq və müntəzəm baxış.', 'fixit' ) ),
			);
			$fixit_n     = 0;
			foreach ( $fixit_steps as $fixit_step ) :
				++$fixit_n;
				?>
				<div class="step reveal">
					<div class="step__num"><?php echo esc_html( sprintf( '%02d', $fixit_n ) ); ?></div>
					<h4><?php echo esc_html( $fixit_step[0] ); ?></h4>
					<p><?php echo esc_html( $fixit_step[1] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ============================ RƏYLƏR ============================ -->
<?php if ( $fixit_reviews ) : ?>
	<section class="section section--soft">
		<div class="container">
			<div class="section-head section-head--center reveal">
				<span class="eyebrow"><?php esc_html_e( 'Müştəri rəyləri', 'fixit' ); ?></span>
				<h2><?php esc_html_e( 'Bizə etibar edənlər', 'fixit' ); ?></h2>
			</div>

			<div class="grid <?php echo count( $fixit_reviews ) > 2 ? 'grid--3' : 'grid--2'; ?>">
				<?php foreach ( $fixit_reviews as $fixit_rev ) : ?>
					<?php
					$fixit_rev_author = get_post_meta( $fixit_rev->ID, '_fixit_author', true );
					$fixit_rev_city   = get_post_meta( $fixit_rev->ID, '_fixit_city', true );
					$fixit_rev_rating = (int) get_post_meta( $fixit_rev->ID, '_fixit_rating', true );
					$fixit_rev_author = $fixit_rev_author ? $fixit_rev_author : $fixit_rev->post_title;
					?>
					<div class="quote reveal">
						<?php echo fixit_stars( $fixit_rev_rating ? $fixit_rev_rating : 5 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<p>“<?php echo esc_html( wp_strip_all_tags( $fixit_rev->post_content ) ); ?>”</p>
						<div class="quote__who">
							<span class="avatar"><?php echo esc_html( mb_substr( $fixit_rev_author, 0, 1 ) ); ?></span>
							<span>
								<strong><?php echo esc_html( $fixit_rev_author ); ?></strong>
								<span><?php echo esc_html( $fixit_rev_city ); ?></span>
							</span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<!-- ============================ FAQ ============================ -->
<section class="section">
	<div class="container">
		<div class="section-head section-head--center reveal">
			<span class="eyebrow"><?php esc_html_e( 'Tez-tez verilən suallar', 'fixit' ); ?></span>
			<h2><?php esc_html_e( 'Ağlınıza gələn suallar', 'fixit' ); ?></h2>
		</div>

		<?php fixit_render_faq( fixit_faq_home() ); ?>
	</div>
</section>

<!-- ============================ CTA ============================ -->
<section class="section" style="padding-top:0">
	<div class="container">
		<div class="cta reveal">
			<div>
				<h2><?php esc_html_e( 'İnfrastrukturunuzu pulsuz yoxlayaq', 'fixit' ); ?></h2>
				<p><?php esc_html_e( 'Bir zəng və ya mesaj kifayətdir. Mütəxəssisimiz ehtiyacınızı dinləyib uyğun həlli təklif edəcək.', 'fixit' ); ?></p>
			</div>
			<div class="cta__actions">
				<a class="btn btn--white" href="tel:<?php echo esc_attr( fixit_tel( $fixit_phone ) ); ?>">
					<?php fixit_icon( 'phone' ); ?><?php echo esc_html( $fixit_phone ); ?>
				</a>
				<a class="btn btn--light" href="<?php echo esc_url( $fixit_contact_url ); ?>">
					<?php esc_html_e( 'Müraciət göndər', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
