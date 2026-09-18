<?php
/**
 * Məhsulun detallı səhifəsi: /mehsul/<slug>/
 *
 * Qısa təsvir, əsas xüsusiyyətlər, demo və ekran görüntüləri admin paneldən,
 * zəngin bölmələr (problem, xüsusiyyət qrupları, iş prinsipi, təhlükəsizlik,
 * suallar) isə inc/product-data.php faylından gəlir.
 *
 * @package FIXIT
 */

get_header();

while ( have_posts() ) :
	the_post();

	$fixit_post     = get_post();
	$fixit_detail   = fixit_product_detail( $fixit_post );
	$fixit_icon_key = get_post_meta( $fixit_post->ID, '_fixit_icon', true );
	$fixit_short    = get_post_meta( $fixit_post->ID, '_fixit_short', true );
	$fixit_features = fixit_meta_lines( $fixit_post->ID, '_fixit_features' );
	$fixit_chips    = get_post_meta( $fixit_post->ID, '_fixit_chips', true );
	$fixit_chips    = $fixit_chips ? array_filter( array_map( 'trim', explode( ',', $fixit_chips ) ) ) : array();
	$fixit_gallery  = fixit_product_gallery_ids( $fixit_post->ID );
	$fixit_demo     = fixit_product_demo( $fixit_post->ID );

	$fixit_contact_url  = fixit_page_url( 'template-contact.php' );
	$fixit_products_url = fixit_page_url( 'template-products.php' );
	$fixit_offer_url    = add_query_arg( 'mehsul', rawurlencode( $fixit_post->post_title ), $fixit_contact_url ) . '#elaqe-form';

	$fixit_tagline = ! empty( $fixit_detail['tagline'] ) ? $fixit_detail['tagline'] : $fixit_short;
	$fixit_badges  = ! empty( $fixit_detail['badges'] ) ? $fixit_detail['badges'] : array();
	$fixit_flow    = ! empty( $fixit_detail['flow'] ) ? $fixit_detail['flow'] : array();

	// Detallı qruplar yoxdursa, admin paneldəki əsas xüsusiyyətlər bir qrup kimi göstərilir.
	$fixit_groups = ! empty( $fixit_detail['groups'] ) ? $fixit_detail['groups'] : array();
	if ( ! $fixit_groups && $fixit_features ) {
		$fixit_groups = array(
			array(
				'icon'  => $fixit_icon_key ? $fixit_icon_key : 'check-c',
				'title' => __( 'Əsas imkanlar', 'fixit' ),
				'items' => $fixit_features,
			),
		);
	}

	$fixit_show_demo = ( 'off' !== $fixit_demo['mode'] ) && ( 'open' !== $fixit_demo['mode'] || $fixit_demo['url'] );
	?>

	<!-- ============================ HERO ============================ -->
	<section class="prod-hero">
		<div class="container prod-hero__inner">

			<div>
				<nav class="crumbs" aria-label="<?php esc_attr_e( 'Naviqasiya', 'fixit' ); ?>" style="margin:0 0 22px">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ana səhifə', 'fixit' ); ?></a>
					<span>/</span>
					<a href="<?php echo esc_url( $fixit_products_url ); ?>"><?php esc_html_e( 'Məhsullar', 'fixit' ); ?></a>
					<span>/</span>
					<span><?php the_title(); ?></span>
				</nav>

				<span class="eyebrow"><?php fixit_icon( 'award' ); ?><?php esc_html_e( 'FIXIT məhsulu', 'fixit' ); ?></span>

				<h1><?php the_title(); ?></h1>

				<?php if ( $fixit_tagline ) : ?>
					<p class="prod-hero__tagline"><?php echo esc_html( $fixit_tagline ); ?></p>
				<?php endif; ?>

				<?php if ( $fixit_post->post_content ) : ?>
					<p class="lead"><?php echo esc_html( wp_strip_all_tags( $fixit_post->post_content ) ); ?></p>
				<?php endif; ?>

				<?php if ( $fixit_badges ) : ?>
					<ul class="prod-badges">
						<?php foreach ( $fixit_badges as $fixit_badge ) : ?>
							<li><?php fixit_icon( 'check' ); ?><?php echo esc_html( $fixit_badge ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<div class="hero__cta">
					<?php if ( $fixit_show_demo ) : ?>
						<a class="btn btn--primary" href="#demo">
							<?php fixit_icon( 'zap' ); ?><?php esc_html_e( 'Demonu sına', 'fixit' ); ?>
						</a>
					<?php endif; ?>
					<a class="btn btn--light" href="<?php echo esc_url( $fixit_offer_url ); ?>">
						<?php esc_html_e( 'Təklif istə', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?>
					</a>
				</div>
			</div>

			<!-- Sağ tərəf: ilk ekran görüntüsü, yoxdursa iş prinsipi -->
			<div class="prod-hero__visual">
				<?php if ( $fixit_gallery ) : ?>
					<figure class="shot shot--hero">
						<span class="shot__bar" aria-hidden="true"><i></i><i></i><i></i></span>
						<?php
						echo wp_get_attachment_image(
							$fixit_gallery[0],
							'large',
							false,
							array(
								'alt'     => get_the_title(),
								'loading' => 'eager',
							)
						);
						?>
					</figure>
				<?php else : ?>
					<div class="hero-card">
						<div class="hero-card__head">
							<span class="prod-hero__mark"><?php fixit_icon( $fixit_icon_key ? $fixit_icon_key : 'server' ); ?></span>
							<strong><?php the_title(); ?></strong>
						</div>
						<ul class="hero-list">
							<?php
							$fixit_hero_rows = $fixit_flow ? $fixit_flow : array_map(
								function ( $f ) {
									return array( 'check-c', $f, '' );
								},
								array_slice( $fixit_features, 0, 5 )
							);
							foreach ( $fixit_hero_rows as $fixit_row ) :
								?>
								<li>
									<span class="ico"><?php fixit_icon( $fixit_row[0] ); ?></span>
									<span>
										<?php echo esc_html( $fixit_row[1] ); ?>
										<?php if ( ! empty( $fixit_row[2] ) ) : ?>
											<small class="hero-list__sub"><?php echo esc_html( $fixit_row[2] ); ?></small>
										<?php endif; ?>
									</span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>

		</div>
	</section>

	<!-- ============================ TEQLƏR ============================ -->
	<?php if ( $fixit_chips ) : ?>
		<div class="marquee" style="padding:16px 0">
			<div class="container" style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center">
				<?php foreach ( $fixit_chips as $fixit_chip ) : ?>
					<span class="chip"><?php echo esc_html( $fixit_chip ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<!-- ============================ PROBLEM → HƏLL ============================ -->
	<?php if ( ! empty( $fixit_detail['problems'] ) ) : ?>
		<section class="section">
			<div class="container">
				<div class="section-head section-head--center reveal">
					<span class="eyebrow"><?php esc_html_e( 'Nəyi həll edir', 'fixit' ); ?></span>
					<h2><?php esc_html_e( 'Tanış gəlir?', 'fixit' ); ?></h2>
				</div>

				<div class="grid <?php echo count( $fixit_detail['problems'] ) > 2 ? 'grid--3' : 'grid--2'; ?>">
					<?php foreach ( $fixit_detail['problems'] as $fixit_problem ) : ?>
						<div class="card reveal">
							<span class="card__icon"><?php fixit_icon( $fixit_problem['icon'] ); ?></span>
							<h3><?php echo esc_html( $fixit_problem['title'] ); ?></h3>
							<p><?php echo esc_html( $fixit_problem['text'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ============================ İMKANLAR ============================ -->
	<?php if ( $fixit_groups ) : ?>
		<section class="section section--soft">
			<div class="container">
				<div class="section-head section-head--center reveal">
					<span class="eyebrow"><?php esc_html_e( 'İmkanlar', 'fixit' ); ?></span>
					<h2><?php esc_html_e( 'Nələr edə bilirsiniz', 'fixit' ); ?></h2>
				</div>

				<div class="grid <?php echo count( $fixit_groups ) > 2 ? 'grid--3' : 'grid--2'; ?>">
					<?php foreach ( $fixit_groups as $fixit_group ) : ?>
						<div class="card feature-group reveal">
							<span class="card__icon"><?php fixit_icon( $fixit_group['icon'] ); ?></span>
							<h3><?php echo esc_html( $fixit_group['title'] ); ?></h3>
							<ul class="bullets">
								<?php foreach ( $fixit_group['items'] as $fixit_item ) : ?>
									<li><?php fixit_icon( 'check-c' ); ?><span><?php echo esc_html( $fixit_item ); ?></span></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ============================ EKRAN GÖRÜNTÜLƏRİ ============================ -->
	<?php if ( $fixit_gallery ) : ?>
		<section class="section">
			<div class="container">
				<div class="section-head section-head--center reveal">
					<span class="eyebrow"><?php esc_html_e( 'Ekran görüntüləri', 'fixit' ); ?></span>
					<h2><?php esc_html_e( 'Necə görünür', 'fixit' ); ?></h2>
				</div>

				<div class="gallery" data-lightbox>
					<?php foreach ( $fixit_gallery as $fixit_img_id ) : ?>
						<?php
						$fixit_full    = wp_get_attachment_image_url( $fixit_img_id, 'full' );
						$fixit_caption = wp_get_attachment_caption( $fixit_img_id );
						?>
						<figure class="shot reveal">
							<span class="shot__bar" aria-hidden="true"><i></i><i></i><i></i></span>
							<a href="<?php echo esc_url( $fixit_full ); ?>" data-caption="<?php echo esc_attr( $fixit_caption ); ?>">
								<?php echo wp_get_attachment_image( $fixit_img_id, 'large', false, array( 'alt' => $fixit_caption ? $fixit_caption : get_the_title() ) ); ?>
							</a>
							<?php if ( $fixit_caption ) : ?>
								<figcaption><?php echo esc_html( $fixit_caption ); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ============================ İŞ PRİNSİPİ ============================ -->
	<?php if ( $fixit_flow ) : ?>
		<section class="section <?php echo $fixit_gallery ? 'section--soft' : ''; ?>">
			<div class="container">
				<div class="section-head section-head--center reveal">
					<span class="eyebrow"><?php esc_html_e( 'İş prinsipi', 'fixit' ); ?></span>
					<h2><?php echo esc_html( ! empty( $fixit_detail['flow_title'] ) ? $fixit_detail['flow_title'] : __( 'Necə işləyir', 'fixit' ) ); ?></h2>
				</div>

				<ol class="flow reveal" style="--flow-count:<?php echo (int) count( $fixit_flow ); ?>">
					<?php foreach ( $fixit_flow as $fixit_step ) : ?>
						<li class="flow__step">
							<span class="flow__icon"><?php fixit_icon( $fixit_step[0] ); ?></span>
							<strong><?php echo esc_html( $fixit_step[1] ); ?></strong>
							<span><?php echo esc_html( $fixit_step[2] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
	<?php endif; ?>

	<!-- ============================ TƏHLÜKƏSİZLİK ============================ -->
	<?php if ( ! empty( $fixit_detail['security'] ) ) : ?>
		<section class="section section--dark">
			<div class="container split">
				<div class="reveal">
					<span class="eyebrow"><?php fixit_icon( 'shield' ); ?><?php esc_html_e( 'Təhlükəsizlik', 'fixit' ); ?></span>
					<h2 style="font-size:clamp(1.6rem,3vw,2.3rem);margin:16px 0 14px">
						<?php esc_html_e( 'Məlumatınız', 'fixit' ); ?>
						<span style="background:linear-gradient(100deg,#6fd0ff,#7bf7d8);-webkit-background-clip:text;background-clip:text;color:transparent"><?php esc_html_e( 'sizdə qalır', 'fixit' ); ?></span>
					</h2>
					<p style="color:#c4d8f0">
						<?php esc_html_e( 'Proqram sizin öz serverinizdə quraşdırılır. Kənar bulud xidmətindən asılılıq yoxdur.', 'fixit' ); ?>
					</p>
				</div>

				<ul class="bullets reveal" style="gap:14px">
					<?php foreach ( $fixit_detail['security'] as $fixit_sec ) : ?>
						<li><?php fixit_icon( 'shield' ); ?><span><?php echo esc_html( $fixit_sec ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<!-- ============================ DEMO ============================ -->
	<?php if ( $fixit_show_demo ) : ?>
		<section class="section" id="demo">
			<div class="container">
				<div class="demo-box reveal">

					<div class="demo-box__text">
						<span class="eyebrow"><?php fixit_icon( 'zap' ); ?><?php esc_html_e( 'Onlayn demo', 'fixit' ); ?></span>
						<h2><?php esc_html_e( 'Özünüz sınayın', 'fixit' ); ?></h2>

						<?php if ( 'open' === $fixit_demo['mode'] ) : ?>
							<p><?php esc_html_e( 'Demo nüsxəsi nümunə məlumatlarla doldurulub. Brauzerdə açın və sərbəst yoxlayın — heç nə quraşdırmaq lazım deyil.', 'fixit' ); ?></p>
						<?php else : ?>
							<p><?php esc_html_e( 'Formu doldurun — sizə şəxsi demo girişi göndərək. Nümunə məlumatlarla doldurulmuş sistemdə sərbəst işləyə bilərsiniz; istəsəniz mütəxəssisimiz canlı da göstərər.', 'fixit' ); ?></p>
						<?php endif; ?>

						<ol class="demo-steps">
							<?php if ( 'open' === $fixit_demo['mode'] ) : ?>
								<li><?php esc_html_e( 'Demonu açın', 'fixit' ); ?></li>
								<li><?php esc_html_e( 'Nümunə məlumatlarla sərbəst işləyin', 'fixit' ); ?></li>
								<li><?php esc_html_e( 'Sualınız olsa — bizə yazın', 'fixit' ); ?></li>
							<?php else : ?>
								<li><?php esc_html_e( 'Formu doldurun', 'fixit' ); ?></li>
								<li><?php esc_html_e( 'E-poçtunuza demo girişi gəlir', 'fixit' ); ?></li>
								<li><?php esc_html_e( 'Brauzerdə açın və sınayın', 'fixit' ); ?></li>
							<?php endif; ?>
						</ol>
					</div>

					<div class="demo-box__action">
						<?php if ( 'open' === $fixit_demo['mode'] ) : ?>
							<a class="btn btn--primary btn--block" href="<?php echo esc_url( $fixit_demo['url'] ); ?>" target="_blank" rel="noopener">
								<?php fixit_icon( 'zap' ); ?><?php esc_html_e( 'Demonu aç', 'fixit' ); ?>
							</a>
							<?php if ( $fixit_demo['note'] ) : ?>
								<p class="form-note"><?php echo esc_html( $fixit_demo['note'] ); ?></p>
							<?php endif; ?>
						<?php else : ?>
							<?php echo fixit_form_notice(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

							<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="demo-form">
								<input type="hidden" name="action" value="fixit_contact">
								<input type="hidden" name="fixit_kind" value="demo">
								<input type="hidden" name="fixit_anchor" value="demo">
								<input type="hidden" name="fixit_topic" value="<?php echo esc_attr( $fixit_post->post_title ); ?>">
								<?php wp_nonce_field( 'fixit_contact', 'fixit_contact_nonce' ); ?>

								<div class="hp-field" aria-hidden="true">
									<label for="fixit_website_d"><?php esc_html_e( 'Sayt', 'fixit' ); ?></label>
									<input type="text" name="fixit_website" id="fixit_website_d" tabindex="-1" autocomplete="off">
								</div>

								<div class="field-2">
									<div class="field">
										<label for="fixit_name_d"><?php esc_html_e( 'Adınız', 'fixit' ); ?> *</label>
										<input type="text" name="fixit_name" id="fixit_name_d" required autocomplete="name">
									</div>
									<div class="field">
										<label for="fixit_company_d"><?php esc_html_e( 'Şirkət', 'fixit' ); ?></label>
										<input type="text" name="fixit_company" id="fixit_company_d" autocomplete="organization">
									</div>
								</div>

								<div class="field-2">
									<div class="field">
										<label for="fixit_email_d"><?php esc_html_e( 'E-poçt', 'fixit' ); ?> *</label>
										<input type="email" name="fixit_email" id="fixit_email_d" required autocomplete="email">
									</div>
									<div class="field">
										<label for="fixit_phone_d"><?php esc_html_e( 'Telefon', 'fixit' ); ?></label>
										<input type="tel" name="fixit_phone" id="fixit_phone_d" autocomplete="tel" placeholder="+994 __ ___ __ __">
									</div>
								</div>

								<button class="btn btn--primary btn--block" type="submit">
									<?php fixit_icon( 'send' ); ?><?php esc_html_e( 'Demo girişi istə', 'fixit' ); ?>
								</button>
								<p class="form-note"><?php esc_html_e( 'Məlumatlarınız yalnız demo girişi göndərmək və sizinlə əlaqə üçün istifadə olunur.', 'fixit' ); ?></p>
							</form>
						<?php endif; ?>
					</div>

				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ============================ SUALLAR ============================ -->
	<?php if ( ! empty( $fixit_detail['faq'] ) ) : ?>
		<section class="section section--soft">
			<div class="container">
				<div class="section-head section-head--center reveal">
					<span class="eyebrow"><?php esc_html_e( 'Suallar', 'fixit' ); ?></span>
					<h2><?php esc_html_e( 'Tez-tez soruşulanlar', 'fixit' ); ?></h2>
				</div>
				<?php fixit_render_faq( $fixit_detail['faq'] ); ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- ============================ DİGƏR MƏHSULLAR ============================ -->
	<?php
	$fixit_others = array_filter(
		fixit_get_products(),
		function ( $p ) use ( $fixit_post ) {
			return $p->ID !== $fixit_post->ID;
		}
	);
	?>
	<?php if ( $fixit_others ) : ?>
		<section class="section">
			<div class="container">
				<div class="section-head section-head--center reveal">
					<span class="eyebrow"><?php esc_html_e( 'Digər məhsullarımız', 'fixit' ); ?></span>
					<h2><?php esc_html_e( 'Bunlar da maraqlı ola bilər', 'fixit' ); ?></h2>
				</div>

				<div class="grid <?php echo count( $fixit_others ) > 2 ? 'grid--3' : 'grid--2'; ?>">
					<?php foreach ( $fixit_others as $fixit_other ) : ?>
						<?php $fixit_o_icon = get_post_meta( $fixit_other->ID, '_fixit_icon', true ); ?>
						<a class="card reveal" href="<?php echo esc_url( get_permalink( $fixit_other ) ); ?>">
							<span class="card__icon"><?php fixit_icon( $fixit_o_icon ? $fixit_o_icon : 'server' ); ?></span>
							<h3><?php echo esc_html( $fixit_other->post_title ); ?></h3>
							<p><?php echo esc_html( get_post_meta( $fixit_other->ID, '_fixit_short', true ) ); ?></p>
							<span class="card__link"><?php esc_html_e( 'Ətraflı', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ============================ CTA ============================ -->
	<section class="section" style="padding-top:0">
		<div class="container">
			<div class="cta reveal">
				<div>
					<h2><?php esc_html_e( 'Şirkətinizə uyğun təklif hazırlayaq', 'fixit' ); ?></h2>
					<p><?php esc_html_e( 'İstifadəçi sayınızı və ehtiyacınızı deyin — konkret və şəffaf təklif göndərək.', 'fixit' ); ?></p>
				</div>
				<div class="cta__actions">
					<a class="btn btn--white" href="<?php echo esc_url( $fixit_offer_url ); ?>">
						<?php fixit_icon( 'send' ); ?><?php esc_html_e( 'Təklif istə', 'fixit' ); ?>
					</a>
					<a class="btn btn--light" href="tel:<?php echo esc_attr( fixit_tel( fixit_get( 'fixit_phone' ) ) ); ?>">
						<?php fixit_icon( 'phone' ); ?><?php echo esc_html( fixit_get( 'fixit_phone' ) ); ?>
					</a>
				</div>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_footer();
