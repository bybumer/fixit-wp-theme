<?php
/**
 * Template Name: Uzaqdan dəstək
 *
 * Müştəri bu səhifədən proqramı yükləyir və dəstəyə nömrə ilə şifrəni
 * deyir. Qeydiyyat, hesab yaratmaq və quraşdırma ayarları lazım deyil —
 * nə qədər az addım olsa, telefonda izah etmək bir o qədər asandır.
 *
 * @package FIXIT
 */

get_header();

$fixit_contact_url = fixit_page_url( 'template-contact.php' );
$fixit_remote_page = fixit_product_url( 'remote' );
$fixit_phone       = fixit_get( 'fixit_phone' );
?>

<!-- Səhifə başlığı -->
<section class="page-hero">
	<div class="container">
		<span class="eyebrow"><?php fixit_icon( 'support' ); ?><?php esc_html_e( 'Uzaqdan dəstək', 'fixit' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<p><?php esc_html_e( 'Proqramı yükləyin, açılan pəncərədəki nömrə və şifrəni mütəxəssisimizə deyin — kompüterinizə qoşulub köməklik göstərək. Quraşdırmadan sonra heç bir ayar etmək lazım deyil.', 'fixit' ); ?></p>

		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Naviqasiya', 'fixit' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ana səhifə', 'fixit' ); ?></a>
			<span>/</span>
			<span><?php the_title(); ?></span>
		</nav>
	</div>
</section>

<!-- ============================ YÜKLƏMƏ ============================ -->
<section class="section">
	<div class="container">

		<div class="grid grid--2">

			<article class="card reveal" style="cursor:default">
				<span class="card__icon"><?php fixit_icon( 'computer' ); ?></span>
				<h3><?php esc_html_e( 'Windows üçün', 'fixit' ); ?></h3>
				<p><?php esc_html_e( 'Windows 10 və 11 (64-bit). Fayl quraşdırılır və masaüstünüzdə nişan yaradır.', 'fixit' ); ?></p>

				<div style="margin-top:18px">
					<a class="btn btn--primary" href="<?php echo esc_url( FIXIT_REMOTE_WINDOWS ); ?>" rel="nofollow">
						<?php fixit_icon( 'download' ); ?><?php esc_html_e( 'Proqramı yüklə', 'fixit' ); ?>
					</a>
				</div>

				<ul class="bullets" style="margin-top:18px">
					<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'Quraşdırma bir neçə saniyə çəkir', 'fixit' ); ?></span></li>
					<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'Qeydiyyat və hesab lazım deyil', 'fixit' ); ?></span></li>
				</ul>
			</article>

			<article class="card reveal" style="cursor:default">
				<span class="card__icon"><?php fixit_icon( 'phone' ); ?></span>
				<h3><?php esc_html_e( 'Telefon üçün', 'fixit' ); ?></h3>

				<?php if ( FIXIT_REMOTE_ANDROID_READY ) : ?>
					<p><?php esc_html_e( 'Telefonunuzdan kompüterə qoşulmaq üçün tətbiq. Android 7 və yuxarı.', 'fixit' ); ?></p>

					<div style="margin-top:18px">
						<a class="btn btn--ghost" href="<?php echo esc_url( FIXIT_REMOTE_ANDROID ); ?>" rel="nofollow">
							<?php fixit_icon( 'download' ); ?><?php esc_html_e( 'Tətbiqi yüklə', 'fixit' ); ?>
						</a>
					</div>

					<ul class="bullets" style="margin-top:18px">
						<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'Kompüterin ekranını telefondan idarə edin', 'fixit' ); ?></span></li>
						<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'Android 7 və yuxarı', 'fixit' ); ?></span></li>
					</ul>
				<?php else : ?>
					<p><?php esc_html_e( 'Telefondan kompüterə qoşulmaq üçün tətbiq üzərində işləyirik. Hazır olanda buradan yükləmək mümkün olacaq.', 'fixit' ); ?></p>

					<div style="margin-top:18px;display:flex;gap:10px;flex-wrap:wrap;align-items:center">
						<span class="chip"><?php fixit_icon( 'clock' ); ?><?php esc_html_e( 'Tezliklə', 'fixit' ); ?></span>

						<a class="btn btn--ghost" href="<?php echo esc_url( $fixit_contact_url ); ?>">
							<?php esc_html_e( 'Hazır olanda xəbər verin', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?>
						</a>
					</div>

					<ul class="bullets" style="margin-top:18px">
						<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'Kompüterin ekranına telefondan baxmaq və idarə etmək', 'fixit' ); ?></span></li>
						<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'İndilik dəstək üçün Windows proqramı kifayətdir', 'fixit' ); ?></span></li>
					</ul>
				<?php endif; ?>
			</article>

		</div>
	</div>
</section>

<!-- ============================ NECƏ İŞLƏYİR ============================ -->
<section class="section section--soft">
	<div class="container">
		<div class="section-head section-head--center reveal">
			<span class="eyebrow"><?php esc_html_e( 'Üç addım', 'fixit' ); ?></span>
			<h2><?php esc_html_e( 'Qoşulma necə baş verir', 'fixit' ); ?></h2>
		</div>

		<div class="grid grid--3">
			<?php
			$fixit_steps = array(
				array(
					'icon'  => 'download',
					'title' => __( '1. Proqramı yükləyin', 'fixit' ),
					'text'  => __( 'Yüklənən faylı açın və "Bəli" deyin. Quraşdırmadan sonra proqram özü işə düşür, masaüstündə nişanı görünür.', 'fixit' ),
				),
				array(
					'icon'  => 'phone',
					'title' => __( '2. Nömrəni və şifrəni deyin', 'fixit' ),
					'text'  => __( 'Pəncərədə iki sətir var: ID və ŞİFRƏ. Onları telefonla mütəxəssisimizə oxuyun. Şifrəni istənilən vaxt dəyişə bilərsiniz.', 'fixit' ),
				),
				array(
					'icon'  => 'support',
					'title' => __( '3. Biz qoşuluruq', 'fixit' ),
					'text'  => __( 'Qoşulma zamanı ekranınızda zolaq görünür. İstədiyiniz an "Əlaqəni kəs" düyməsi ilə bağlantını dayandıra bilərsiniz.', 'fixit' ),
				),
			);

			foreach ( $fixit_steps as $fixit_step ) :
				?>
				<article class="card reveal" style="cursor:default">
					<span class="card__icon"><?php fixit_icon( $fixit_step['icon'] ); ?></span>
					<h3><?php echo esc_html( $fixit_step['title'] ); ?></h3>
					<p><?php echo esc_html( $fixit_step['text'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ============================ TƏHLÜKƏSİZLİK ============================ -->
<section class="section">
	<div class="container split">

		<div class="reveal">
			<span class="eyebrow"><?php fixit_icon( 'lock' ); ?><?php esc_html_e( 'Təhlükəsizlik', 'fixit' ); ?></span>
			<h2 style="font-size:clamp(1.6rem,3vw,2.2rem);margin:16px 0 14px">
				<?php esc_html_e( 'Nəzarət', 'fixit' ); ?> <span class="grad-text"><?php esc_html_e( 'sizdə qalır', 'fixit' ); ?></span>
			</h2>

			<ul class="feature-list">
				<?php
				$fixit_notes = array(
					array( __( 'Şifrəsiz qoşulmaq mümkün deyil', 'fixit' ), __( 'Şifrəni yalnız siz görürsünüz və istədiyiniz vaxt yenisini yarada bilirsiniz.', 'fixit' ) ),
					array( __( 'Qoşulma gizli ola bilmir', 'fixit' ), __( 'Sessiya boyu ekranınızda kimin qoşulduğunu göstərən zolaq durur.', 'fixit' ) ),
					array( __( 'Bağlantını siz kəsirsiniz', 'fixit' ), __( 'Bir düymə ilə sessiya dayanır; proqramı tamamilə dayandırmaq da mümkündür.', 'fixit' ) ),
					array( __( 'Proqram özü heç nə etmir', 'fixit' ), __( 'Siz nömrə və şifrəni deməyincə kompüterinizə qoşulmaq olmur.', 'fixit' ) ),
				);

				foreach ( $fixit_notes as $fixit_note ) :
					?>
					<li>
						<span class="ico"><?php fixit_icon( 'check' ); ?></span>
						<div>
							<strong><?php echo esc_html( $fixit_note[0] ); ?></strong>
							<span><?php echo esc_html( $fixit_note[1] ); ?></span>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="reveal">
			<div class="card" style="cursor:default">
				<span class="badge"><?php fixit_icon( 'server' ); ?><?php esc_html_e( 'Şirkətlər üçün', 'fixit' ); ?></span>
				<h3 style="margin-top:12px"><?php esc_html_e( 'Öz serverinizdə quraşdırma', 'fixit' ); ?></h3>
				<p><?php esc_html_e( 'Şirkətinizdə çox sayda kompüter varsa, sistemi tam sizin serverinizdə qura bilərik: bağlantı, icazələr və sessiya jurnalı şirkətdən kənara çıxmır.', 'fixit' ); ?></p>

				<ul class="bullets" style="margin-top:16px">
					<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'Kim hansı kompüterə qoşula bilər — mərkəzdən idarə', 'fixit' ); ?></span></li>
					<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'Hər sessiya jurnalda; CSV və PDF kimi yüklənir', 'fixit' ); ?></span></li>
					<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'İşçi kompüterlərində port açmaq lazım deyil', 'fixit' ); ?></span></li>
				</ul>

				<div style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap">
					<?php if ( $fixit_remote_page ) : ?>
						<a class="btn btn--ghost" href="<?php echo esc_url( $fixit_remote_page ); ?>">
							<?php esc_html_e( 'Məhsul haqqında', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?>
						</a>
					<?php endif; ?>

					<a class="btn btn--primary" href="<?php echo esc_url( $fixit_contact_url ); ?>">
						<?php fixit_icon( 'send' ); ?><?php esc_html_e( 'Bizə yazın', 'fixit' ); ?>
					</a>
				</div>
			</div>
		</div>

	</div>
</section>

<!-- ============================ KÖMƏK ============================ -->
<section class="section section--dark">
	<div class="container" style="text-align:center">
		<h2 style="margin-bottom:12px"><?php esc_html_e( 'Yükləmədə çətinlik var?', 'fixit' ); ?></h2>
		<p style="max-width:640px;margin:0 auto 24px;color:rgba(255,255,255,.78)">
			<?php esc_html_e( 'Zəng edin — birlikdə addım-addım keçək. Proqramı yükləmək və açmaq bir neçə dəqiqə çəkir.', 'fixit' ); ?>
		</p>

		<div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
			<?php if ( $fixit_phone ) : ?>
				<a class="btn btn--white" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $fixit_phone ) ); ?>">
					<?php fixit_icon( 'phone' ); ?><?php echo esc_html( $fixit_phone ); ?>
				</a>
			<?php endif; ?>

			<a class="btn btn--light" href="<?php echo esc_url( $fixit_contact_url ); ?>">
				<?php esc_html_e( 'Əlaqə formu', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?>
			</a>
		</div>
	</div>
</section>

<?php
get_footer();
