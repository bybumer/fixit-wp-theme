<?php
/**
 * Template Name: Məhsullar səhifəsi
 *
 * Öz proqram təminatımızın (Sorğu Sistemi və Şəbəkə Monitorinqi)
 * təqdimatı və satış şərtləri.
 *
 * @package FIXIT
 */

get_header();

$fixit_products    = fixit_get_products();
$fixit_contact_url = fixit_page_url( 'template-contact.php' );
$fixit_phone       = fixit_get( 'fixit_phone' );
?>

<!-- Səhifə başlığı -->
<section class="page-hero">
	<div class="container">
		<span class="eyebrow"><?php fixit_icon( 'zap' ); ?><?php esc_html_e( 'Öz məhsullarımız', 'fixit' ); ?></span>
		<h1><?php esc_html_e( 'Bizim hazırladığımız proqram təminatı', 'fixit' ); ?></h1>
		<p><?php esc_html_e( 'Xidmətlərimizlə yanaşı, öz komandamızın sıfırdan yazdığı iki məhsulu təqdim edirik. Hər ikisi Azərbaycan dilindədir, sizin öz serverinizdə işləyir və şirkətinizin iş axınına uyğunlaşdırıla bilir.', 'fixit' ); ?></p>

		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Naviqasiya', 'fixit' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ana səhifə', 'fixit' ); ?></a>
			<span>/</span>
			<span><?php the_title(); ?></span>
		</nav>
	</div>
</section>

<!-- Əsas üstünlüklər zolağı -->
<div class="marquee" style="padding:22px 0">
	<div class="container">
		<div class="grid grid--4" style="gap:16px">
			<?php
			$fixit_pillars = array(
				array( 'lock', __( 'Məlumat sizdə qalır', 'fixit' ), __( 'Öz serverinizdə işləyir', 'fixit' ) ),
				array( 'wrench', __( 'Fərdiləşdirilə bilir', 'fixit' ), __( 'Mənbə kod bizimdir', 'fixit' ) ),
				array( 'users', __( 'Azərbaycan dilində', 'fixit' ), __( 'İnterfeys və dəstək', 'fixit' ) ),
				array( 'support', __( 'Quraşdırma daxil', 'fixit' ), __( 'Təlim və texniki dəstək', 'fixit' ) ),
			);
			foreach ( $fixit_pillars as $fixit_pillar ) :
				?>
				<div style="display:flex;gap:12px;align-items:center">
					<span class="card__icon" style="width:42px;height:42px;border-radius:12px;margin:0;flex:none">
						<?php fixit_icon( $fixit_pillar[0] ); ?>
					</span>
					<span>
						<strong style="display:block;font-size:.95rem"><?php echo esc_html( $fixit_pillar[1] ); ?></strong>
						<small style="color:var(--text-mute);font-size:.85rem"><?php echo esc_html( $fixit_pillar[2] ); ?></small>
					</span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<!-- Məhsullar -->
<section class="section">
	<div class="container" style="display:grid;gap:26px">
		<?php foreach ( $fixit_products as $fixit_prod ) : ?>
			<?php
			$fixit_icon_key = get_post_meta( $fixit_prod->ID, '_fixit_icon', true );
			$fixit_features = fixit_meta_lines( $fixit_prod->ID, '_fixit_features' );
			$fixit_chips    = get_post_meta( $fixit_prod->ID, '_fixit_chips', true );
			$fixit_chips    = $fixit_chips ? array_filter( array_map( 'trim', explode( ',', $fixit_chips ) ) ) : array();
			?>
			<article class="svc reveal" id="mehsul-<?php echo esc_attr( $fixit_prod->ID ); ?>">
				<div class="svc__icon"><?php fixit_icon( $fixit_icon_key ? $fixit_icon_key : 'server' ); ?></div>

				<div>
					<h2><?php echo esc_html( $fixit_prod->post_title ); ?></h2>

					<?php if ( $fixit_prod->post_content ) : ?>
						<p><?php echo esc_html( wp_strip_all_tags( $fixit_prod->post_content ) ); ?></p>
					<?php endif; ?>

					<?php if ( $fixit_features ) : ?>
						<ul class="bullets">
							<?php foreach ( $fixit_features as $fixit_feature ) : ?>
								<li><?php fixit_icon( 'check-c' ); ?><span><?php echo esc_html( $fixit_feature ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( $fixit_chips ) : ?>
						<div class="chips">
							<?php foreach ( $fixit_chips as $fixit_chip ) : ?>
								<span class="chip"><?php echo esc_html( $fixit_chip ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div style="margin-top:22px;display:flex;gap:12px;flex-wrap:wrap">
						<a class="btn btn--primary" href="<?php echo esc_url( add_query_arg( 'mehsul', rawurlencode( $fixit_prod->post_title ), $fixit_contact_url ) ); ?>#elaqe-form">
							<?php fixit_icon( 'send' ); ?><?php esc_html_e( 'Demo təyin et', 'fixit' ); ?>
						</a>
						<a class="btn btn--ghost" href="tel:<?php echo esc_attr( fixit_tel( $fixit_phone ) ); ?>">
							<?php fixit_icon( 'phone' ); ?><?php esc_html_e( 'Danışaq', 'fixit' ); ?>
						</a>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>

<!-- Satış şərtləri -->
<section class="section section--soft">
	<div class="container split">
		<div class="reveal">
			<span class="eyebrow"><?php fixit_icon( 'award' ); ?><?php esc_html_e( 'Satış şərtləri', 'fixit' ); ?></span>
			<h2 style="font-size:clamp(1.6rem,3vw,2.3rem);margin:16px 0 16px">
				<?php esc_html_e( 'Lisenziya', 'fixit' ); ?> <span class="grad-text"><?php esc_html_e( 'öz serverinizdə', 'fixit' ); ?></span>
			</h2>
			<p style="color:var(--text-soft)">
				<?php esc_html_e( 'Hər iki məhsul on-premise modeldə təqdim olunur: proqram sizin öz serverinizdə quraşdırılır və bütün məlumat şirkətinizin daxilində qalır. Kənar bulud xidmətindən asılılıq yoxdur.', 'fixit' ); ?>
			</p>

			<ul class="feature-list">
				<?php
				$fixit_terms = array(
					array( 'server', __( 'Öz infrastrukturunuzda', 'fixit' ), __( 'Proqram sizin serverinizə quraşdırılır; məlumat bazası da sizdə saxlanılır.', 'fixit' ) ),
					array( 'users', __( 'Lisenziya ehtiyaca görə', 'fixit' ), __( 'Qiymət istifadəçi sayı və lazım olan modullara görə hesablanır — hər şirkət üçün ayrıca təklif hazırlayırıq.', 'fixit' ) ),
					array( 'wrench', __( 'Quraşdırma və köçürmə', 'fixit' ), __( 'Sistemin qurulması, mövcud məlumatların köçürülməsi və ilkin ayarlar bizim üzərimizdədir.', 'fixit' ) ),
					array( 'support', __( 'Təlim və dəstək', 'fixit' ), __( 'Komandanız üçün təlim keçirilir, sonrasında texniki dəstək paketi təklif olunur.', 'fixit' ) ),
					array( 'zap', __( 'Yeniliklər', 'fixit' ), __( 'Məhsullar daim inkişaf etdirilir; yeni versiyalar dəstək müddəti ərzində təqdim olunur.', 'fixit' ) ),
				);
				foreach ( $fixit_terms as $fixit_term ) :
					?>
					<li>
						<span class="tick"><?php fixit_icon( $fixit_term[0] ); ?></span>
						<div>
							<h4><?php echo esc_html( $fixit_term[1] ); ?></h4>
							<p><?php echo esc_html( $fixit_term[2] ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="reveal">
			<div class="form">
				<span class="eyebrow"><?php esc_html_e( 'Qiymət', 'fixit' ); ?></span>
				<h3 style="font-size:1.5rem;margin:14px 0 12px"><?php esc_html_e( 'Təklif fərdi hazırlanır', 'fixit' ); ?></h3>
				<p style="color:var(--text-soft);font-size:.97rem">
					<?php esc_html_e( 'Sabit qiymət siyahısı vermirik, çünki hər şirkətin istifadəçi sayı, modul ehtiyacı və mövcud infrastrukturu fərqlidir. Qısa bir söhbətdən sonra sizə konkret və şəffaf təklif təqdim edirik.', 'fixit' ); ?>
				</p>

				<ul class="bullets" style="margin-top:20px">
					<?php
					$fixit_included = array(
						__( 'Canlı demo — məhsulu real işdə görürsünüz', 'fixit' ),
						__( 'Ehtiyacların birgə təhlili', 'fixit' ),
						__( 'Yazılı və detallı kommersiya təklifi', 'fixit' ),
						__( 'Sınaq quraşdırması imkanı', 'fixit' ),
					);
					foreach ( $fixit_included as $fixit_inc ) :
						?>
						<li><?php fixit_icon( 'check-c' ); ?><span><?php echo esc_html( $fixit_inc ); ?></span></li>
					<?php endforeach; ?>
				</ul>

				<div style="margin-top:26px;display:grid;gap:10px">
					<a class="btn btn--primary btn--block" href="<?php echo esc_url( $fixit_contact_url ); ?>#elaqe-form">
						<?php fixit_icon( 'send' ); ?><?php esc_html_e( 'Təklif istə', 'fixit' ); ?>
					</a>
					<a class="btn btn--ghost btn--block" href="tel:<?php echo esc_attr( fixit_tel( $fixit_phone ) ); ?>">
						<?php fixit_icon( 'phone' ); ?><?php echo esc_html( $fixit_phone ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Necə başlayırıq -->
<section class="section">
	<div class="container">
		<div class="section-head section-head--center reveal">
			<span class="eyebrow"><?php esc_html_e( 'Proses', 'fixit' ); ?></span>
			<h2><?php esc_html_e( 'Demodan istifadəyə qədər', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Qərar verməzdən əvvəl məhsulu real işdə görürsünüz — sonra addım-addım irəliləyirik.', 'fixit' ); ?></p>
		</div>

		<div class="steps">
			<?php
			$fixit_buy_steps = array(
				array( __( 'Demo', 'fixit' ), __( 'Məhsulu canlı göstəririk, suallarınızı cavablandırırıq.', 'fixit' ) ),
				array( __( 'Təhlil və təklif', 'fixit' ), __( 'Ehtiyaclarınızı öyrənib yazılı kommersiya təklifi hazırlayırıq.', 'fixit' ) ),
				array( __( 'Quraşdırma', 'fixit' ), __( 'Sistemi serverinizə qurur, məlumatları köçürür, ayarlayırıq.', 'fixit' ) ),
				array( __( 'Təlim və dəstək', 'fixit' ), __( 'Komandanıza istifadəni öyrədir, sonra da yanınızda oluruq.', 'fixit' ) ),
			);
			$fixit_bn        = 0;
			foreach ( $fixit_buy_steps as $fixit_bs ) :
				++$fixit_bn;
				?>
				<div class="step reveal">
					<div class="step__num"><?php echo esc_html( sprintf( '%02d', $fixit_bn ) ); ?></div>
					<h4><?php echo esc_html( $fixit_bs[0] ); ?></h4>
					<p><?php echo esc_html( $fixit_bs[1] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- Suallar -->
<section class="section section--soft">
	<div class="container">
		<div class="section-head section-head--center reveal">
			<span class="eyebrow"><?php esc_html_e( 'Məhsullar haqqında', 'fixit' ); ?></span>
			<h2><?php esc_html_e( 'Tez-tez verilən suallar', 'fixit' ); ?></h2>
		</div>

		<?php fixit_render_faq( fixit_faq_products() ); ?>
	</div>
</section>

<!-- Səhifə redaktorundan gələn əlavə məzmun -->
<?php
while ( have_posts() ) :
	the_post();
	$fixit_content = trim( get_the_content() );
	if ( $fixit_content ) :
		?>
		<section class="section">
			<div class="container entry"><?php the_content(); ?></div>
		</section>
		<?php
	endif;
endwhile;
?>

<!-- CTA -->
<section class="section" style="padding-top:0">
	<div class="container">
		<div class="cta reveal">
			<div>
				<h2><?php esc_html_e( 'Məhsulu canlı görmək istəyirsiniz?', 'fixit' ); ?></h2>
				<p><?php esc_html_e( 'Sizə uyğun vaxtda demo təşkil edək — 20-30 dəqiqə kifayətdir.', 'fixit' ); ?></p>
			</div>
			<div class="cta__actions">
				<a class="btn btn--white" href="<?php echo esc_url( $fixit_contact_url ); ?>#elaqe-form">
					<?php fixit_icon( 'send' ); ?><?php esc_html_e( 'Demo təyin et', 'fixit' ); ?>
				</a>
				<a class="btn btn--light" href="tel:<?php echo esc_attr( fixit_tel( $fixit_phone ) ); ?>">
					<?php fixit_icon( 'phone' ); ?><?php echo esc_html( $fixit_phone ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
