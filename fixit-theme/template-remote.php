<?php
/**
 * Template Name: Uzaqdan qoşulma
 *
 * Bu səhifə PULSUZ proqramı təqdim edir: adam onu yükləyib başqa bir
 * kompüterə qoşulur — dostuna, valideyninə kömək edir, ya da öz
 * maşınına uzaqdan çıxır.
 *
 * Bizim öz dəstək xidmətimiz AYRI məsələdir və ən aşağıda yazılıb:
 * səhifə "bizə müraciət edin" deyə başlasa, proqramın öz faydası itər.
 *
 * @package FIXIT
 */

get_header();

$fixit_contact_url = fixit_page_url( 'template-contact.php' );
$fixit_remote_page = fixit_product_url( 'remote' );
$fixit_whatsapp    = fixit_get( 'fixit_whatsapp' );
$fixit_release     = fixit_remote_release();
?>

<!-- Səhifə başlığı -->
<section class="page-hero">
	<div class="container">
		<span class="eyebrow"><?php fixit_icon( 'zap' ); ?><?php esc_html_e( 'Şəxsi istifadə üçün pulsuz', 'fixit' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<p><?php esc_html_e( 'Olduğunuz yerdən lazım olan kompüterə qoşulun. Uzaqdakı ekranı görün, siçanı və klaviaturanı idarə edin — sanki onun qarşısında oturmusunuz.', 'fixit' ); ?></p>

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
				<p><?php esc_html_e( 'Windows 10 və 11 (64-bit). Quraşdırıldıqdan sonra proqram həm qoşulmaq, həm də qoşulmağa icazə vermək üçün hazırdır.', 'fixit' ); ?></p>

				<div style="margin-top:18px">
					<a class="btn btn--primary" href="<?php echo esc_url( FIXIT_REMOTE_WINDOWS ); ?>" rel="nofollow">
						<?php fixit_icon( 'download' ); ?><?php esc_html_e( 'Pulsuz yüklə', 'fixit' ); ?>
					</a>

					<?php if ( $fixit_release['version'] ) : ?>
						<p style="margin:10px 0 0;font-size:13px;color:var(--text-mute)">
							<?php
							printf(
								/* translators: 1: versiya, 2: faylın həcmi */
								esc_html__( 'Versiya %1$s · %2$s', 'fixit' ),
								esc_html( $fixit_release['version'] ),
								esc_html( $fixit_release['size'] ? size_format( $fixit_release['size'], 1 ) : '' )
							);
							?>
						</p>
					<?php endif; ?>
				</div>

				<ul class="bullets" style="margin-top:18px">
					<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'Qeydiyyat, hesab və ödəniş yoxdur', 'fixit' ); ?></span></li>
					<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'Quraşdırma bir neçə saniyə çəkir', 'fixit' ); ?></span></li>
				</ul>
			</article>

			<article class="card reveal" style="cursor:default">
				<span class="card__icon"><?php fixit_icon( 'phone' ); ?></span>
				<h3><?php esc_html_e( 'Telefon üçün', 'fixit' ); ?></h3>

				<?php if ( FIXIT_REMOTE_ANDROID_READY ) : ?>
					<p><?php esc_html_e( 'Telefondan kompüterə qoşulmaq üçün tətbiq. Android 7 və yuxarı.', 'fixit' ); ?></p>

					<div style="margin-top:18px">
						<a class="btn btn--ghost" href="<?php echo esc_url( FIXIT_REMOTE_ANDROID ); ?>" rel="nofollow">
							<?php fixit_icon( 'download' ); ?><?php esc_html_e( 'Tətbiqi yüklə', 'fixit' ); ?>
						</a>
					</div>
				<?php else : ?>
					<p><?php esc_html_e( 'Telefondan kompüterə qoşulmaq üçün tətbiq üzərində işləyirik. Hazır olanda buradan yükləmək mümkün olacaq.', 'fixit' ); ?></p>

					<div style="margin-top:18px;display:flex;gap:10px;flex-wrap:wrap;align-items:center">
						<span class="chip"><?php fixit_icon( 'clock' ); ?><?php esc_html_e( 'Tezliklə', 'fixit' ); ?></span>

						<a class="btn btn--ghost" href="<?php echo esc_url( $fixit_contact_url ); ?>">
							<?php esc_html_e( 'Hazır olanda xəbər verin', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?>
						</a>
					</div>
				<?php endif; ?>

				<ul class="bullets" style="margin-top:18px">
					<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'Kompüterin ekranına telefondan baxmaq və idarə etmək', 'fixit' ); ?></span></li>
					<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'İndilik Windows proqramı kifayətdir', 'fixit' ); ?></span></li>
				</ul>
			</article>

		</div>
	</div>
</section>

<!-- ============================ NƏYƏ YARAYIR ============================ -->
<section class="section section--soft">
	<div class="container">
		<div class="section-head section-head--center reveal">
			<span class="eyebrow"><?php esc_html_e( 'Nəyə yarayır', 'fixit' ); ?></span>
			<h2><?php esc_html_e( 'Məsafə problem olmaqdan çıxır', 'fixit' ); ?></h2>
		</div>

		<div class="grid grid--3">
			<?php
			$fixit_uses = array(
				array(
					'icon'  => 'support',
					'title' => __( 'Yaxınlarınıza kömək', 'fixit' ),
					'text'  => __( 'Valideyninizin və ya dostunuzun kompüterində nəyisə düzəltmək üçün yanına getməyə ehtiyac qalmır — ekranını görüb özünüz edirsiniz.', 'fixit' ),
				),
				array(
					'icon'  => 'network',
					'title' => __( 'Öz kompüterinizə çıxış', 'fixit' ),
					'text'  => __( 'Evdə və ya ofisdə qalan maşın əlinizin altında olur: sənədinizi götürün, proqramı işə salın, yarımçıq işi bitirin.', 'fixit' ),
				),
				array(
					'icon'  => 'users',
					'title' => __( 'Birlikdə işləmək', 'fixit' ),
					'text'  => __( 'Ekranı göstərin, izah edin, yazışın. Eyni ekrana baxmaq telefonla izah etməkdən qat-qat asandır.', 'fixit' ),
				),
			);

			foreach ( $fixit_uses as $fixit_use ) :
				?>
				<article class="card reveal" style="cursor:default">
					<span class="card__icon"><?php fixit_icon( $fixit_use['icon'] ); ?></span>
					<h3><?php echo esc_html( $fixit_use['title'] ); ?></h3>
					<p><?php echo esc_html( $fixit_use['text'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ============================ NECƏ İŞLƏYİR ============================ -->
<section class="section">
	<div class="container">
		<div class="section-head section-head--center reveal">
			<span class="eyebrow"><?php esc_html_e( 'Üç addım', 'fixit' ); ?></span>
			<h2><?php esc_html_e( 'Başqa kompüterə necə qoşulursunuz', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Proqram hər iki kompüterdə olmalıdır: birindən qoşulursunuz, o birində isə qoşulmağa icazə verilir.', 'fixit' ); ?></p>
		</div>

		<div class="grid grid--3">
			<?php
			$fixit_steps = array(
				array(
					'icon'  => 'download',
					'title' => __( '1. Hər iki tərəf yükləyir', 'fixit' ),
					'text'  => __( 'Proqramı açan kimi pəncərədə həmin kompüterin nömrəsi (ID) və şifrəsi görünür.', 'fixit' ),
				),
				array(
					'icon'  => 'search',
					'title' => __( '2. Nömrəni yazırsınız', 'fixit' ),
					'text'  => __( 'Qarşı tərəfin nömrəsini yazıb "Qoşul" basırsınız. Nömrəni o sizə necə rahatdırsa deyir — telefonla, mesajla.', 'fixit' ),
				),
				array(
					'icon'  => 'check-c',
					'title' => __( '3. O, icazə verir', 'fixit' ),
					'text'  => __( 'Qarşı tərəfin ekranında sual çıxır: icazə verir və ya rədd edir. İstəsə şifrəsini deyər — onda sual verilmir.', 'fixit' ),
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
<section class="section section--soft">
	<div class="container split">

		<div class="reveal">
			<span class="eyebrow"><?php fixit_icon( 'lock' ); ?><?php esc_html_e( 'Təhlükəsizlik', 'fixit' ); ?></span>
			<h2 style="font-size:clamp(1.6rem,3vw,2.2rem);margin:16px 0 14px">
				<?php esc_html_e( 'Kompüterinizə kim qoşulur —', 'fixit' ); ?> <span class="grad-text"><?php esc_html_e( 'qərar sizindir', 'fixit' ); ?></span>
			</h2>

			<ul class="feature-list">
				<?php
				$fixit_notes = array(
					array( __( 'İcazəsiz qoşulmaq olmur', 'fixit' ), __( 'Ya ekrandakı suala "icazə verirəm" deyirsiniz, ya da şifrənizi özünüz bildirirsiniz.', 'fixit' ) ),
					array( __( 'Qoşulma gizli ola bilmir', 'fixit' ), __( 'Sessiya boyu ekranınızda kimin qoşulduğunu göstərən zolaq durur.', 'fixit' ) ),
					array( __( 'Bağlantını istənilən an kəsirsiniz', 'fixit' ), __( 'Bir düymə ilə sessiya dayanır; proqramı tamamilə dayandırmaq da mümkündür.', 'fixit' ) ),
					array( __( 'Şifrə yalnız sizdədir', 'fixit' ), __( 'Şifrəni istədiyiniz vaxt yeniləyirsiniz — köhnəsini bilən bir daha qoşula bilmir.', 'fixit' ) ),
				);

				foreach ( $fixit_notes as $fixit_note ) :
					?>
					<li>
						<span class="tick"><?php fixit_icon( 'check' ); ?></span>
						<div>
							<h4><?php echo esc_html( $fixit_note[0] ); ?></h4>
							<p><?php echo esc_html( $fixit_note[1] ); ?></p>
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

<!-- ============================ BİZİM DƏSTƏK ============================ -->
<section class="section section--dark">
	<div class="container" style="text-align:center">
		<h2 style="margin-bottom:12px"><?php esc_html_e( 'Problemi mütəxəssis həll etsin?', 'fixit' ); ?></h2>
		<p style="max-width:680px;margin:0 auto 24px;color:rgba(255,255,255,.78)">
			<?php esc_html_e( 'Proqram pulsuzdur və hər kəs onu istədiyi kimi işlədə bilər. Kompüterinizdəki problemi özünüz həll etmək istəmirsinizsə — eyni proqramla bizim mütəxəssis qoşulub kömək edər. Bu, ayrıca xidmətdir.', 'fixit' ); ?>
		</p>

		<div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
			<?php if ( $fixit_whatsapp ) : ?>
				<?php
				// Hazır mətnlə açılır: müştəri nə yazacağını düşünməsin,
				// biz də söhbətin hansı səhifədən gəldiyini bilək.
				$fixit_wa_text = rawurlencode( __( 'Salam! Uzaqdan dəstək lazımdır.', 'fixit' ) );
				?>
				<a class="btn btn--white" href="https://wa.me/<?php echo esc_attr( fixit_tel( $fixit_whatsapp ) ); ?>?text=<?php echo esc_attr( $fixit_wa_text ); ?>" target="_blank" rel="noopener noreferrer">
					<?php fixit_icon( 'whatsapp' ); ?><?php esc_html_e( 'WhatsApp-dan yazın', 'fixit' ); ?>
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
