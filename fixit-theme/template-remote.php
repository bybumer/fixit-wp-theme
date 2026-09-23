<?php
/**
 * Template Name: Uzaqdan qoşulma
 *
 * Səhifə İKİ AUDİTORİYAYA baxır və ona görə iki hissəyə bölünüb:
 *
 *   · Fərdi — proqram pulsuzdur, adam onu yükləyib dostuna, valideyninə
 *     qoşulur. Burada ən vacibi yükləmə düyməsidir.
 *   · Biznes — şirkət sistemi öz serverində qurur: icazələr, jurnal,
 *     kütləvi quraşdırma. Burada ən vacibi bizimlə əlaqədir.
 *
 * İkisi bir mətnə yığılsaydı, hər iki tərəf özünə aid olmayan şeyi
 * oxumalı olardı.
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
		<span class="eyebrow"><?php fixit_icon( 'computer' ); ?><?php esc_html_e( 'Uzaqdan qoşulma', 'fixit' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<p><?php esc_html_e( 'Olduğunuz yerdən lazım olan kompüterə qoşulun. Uzaqdakı ekranı görün, siçanı və klaviaturanı idarə edin — sanki onun qarşısında oturmusunuz.', 'fixit' ); ?></p>

		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Naviqasiya', 'fixit' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ana səhifə', 'fixit' ); ?></a>
			<span>/</span>
			<span><?php the_title(); ?></span>
		</nav>
	</div>
</section>

<!-- Hansı hissə kimə aiddir -->
<div class="marquee" style="padding:16px 0">
	<div class="container" style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center">
		<a class="chip" href="#ferdi"><?php esc_html_e( 'Fərdi istifadə — pulsuz', 'fixit' ); ?></a>
		<a class="chip" href="#biznes"><?php esc_html_e( 'Biznes — öz serverinizdə', 'fixit' ); ?></a>
	</div>
</div>

<!-- ============================ FƏRDİ ============================ -->
<section class="section" id="ferdi">
	<div class="container">

		<div class="section-head reveal">
			<span class="eyebrow"><?php fixit_icon( 'zap' ); ?><?php esc_html_e( 'Fərdi istifadə', 'fixit' ); ?></span>
			<h2><?php esc_html_e( 'Pulsuz yükləyin və qoşulun', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Yaxınınıza kömək edin, evdə qalan kompüterinizə çıxın, iş yoldaşınızla eyni ekrana baxın. Qeydiyyat yoxdur, abunə yoxdur, məhdudiyyət yoxdur.', 'fixit' ); ?></p>
		</div>

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
					<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'İstədiyiniz vaxt tamamilə dayandıra bilərsiniz', 'fixit' ); ?></span></li>
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

<!-- Fərdi: üç addım -->
<section class="section section--soft">
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
					'text'  => __( 'Qarşı tərəfin ekranında sual çıxır: yalnız baxmağa, yoxsa idarə etməyə icazə verir. İstəsə şifrəsini deyər — onda sual verilmir.', 'fixit' ),
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

<!-- Fərdi: təhlükəsizlik -->
<section class="section">
	<div class="container split">

		<div class="reveal">
			<span class="eyebrow"><?php fixit_icon( 'lock' ); ?><?php esc_html_e( 'Təhlükəsizlik', 'fixit' ); ?></span>
			<h2 style="font-size:clamp(1.6rem,3vw,2.2rem);margin:16px 0 14px">
				<?php esc_html_e( 'Kompüterinizə kim qoşulur —', 'fixit' ); ?> <span class="grad-text"><?php esc_html_e( 'qərar sizindir', 'fixit' ); ?></span>
			</h2>

			<ul class="feature-list">
				<?php
				$fixit_notes = array(
					array( __( 'İcazəsiz qoşulmaq olmur', 'fixit' ), __( 'Ya ekrandakı suala cavab verirsiniz, ya da şifrənizi özünüz bildirirsiniz.', 'fixit' ) ),
					array( __( 'Baxış və idarəetmə ayrıdır', 'fixit' ), __( '"Yalnız baxsın" deyəndə qarşı tərəf ekranı görür, amma klaviatura və siçana toxuna bilmir.', 'fixit' ) ),
					array( __( 'Qoşulma gizli ola bilmir', 'fixit' ), __( 'Sessiya boyu ekranınızda kimin qoşulduğunu göstərən zolaq durur.', 'fixit' ) ),
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
				<span class="card__icon"><?php fixit_icon( 'users' ); ?></span>
				<h3><?php esc_html_e( 'Nəyə yarayır', 'fixit' ); ?></h3>

				<ul class="bullets" style="margin-top:14px">
					<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'Valideyninizin kompüterində problemi yanına getmədən düzəldin', 'fixit' ); ?></span></li>
					<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'Evdə qalan maşından sənədinizi götürün', 'fixit' ); ?></span></li>
					<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'İş yoldaşınıza ekranı göstərib izah edin', 'fixit' ); ?></span></li>
					<li><?php fixit_icon( 'check-c' ); ?><span><?php esc_html_e( 'Qoşulma zamanı yazışın — telefonla danışmaq şərt deyil', 'fixit' ); ?></span></li>
				</ul>
			</div>
		</div>

	</div>
</section>

<!-- ============================ BİZNES ============================ -->
<section class="section section--dark" id="biznes">
	<div class="container">

		<div class="section-head reveal">
			<span class="eyebrow"><?php fixit_icon( 'server' ); ?><?php esc_html_e( 'Biznes üçün', 'fixit' ); ?></span>
			<h2><?php esc_html_e( 'Şirkət versiyası —', 'fixit' ); ?> <span style="background:linear-gradient(100deg,#6fd0ff,#7bf7d8);-webkit-background-clip:text;background-clip:text;color:transparent"><?php esc_html_e( 'öz serverinizdə', 'fixit' ); ?></span></h2>
			<p style="color:rgba(255,255,255,.78)"><?php esc_html_e( 'Şirkətdə uzaqdan dəstək başqa cür qurulur: kimin hansı kompüterə qoşulduğu bilinməlidir, qoşulma kənar şirkətin serverindən keçməməlidir, hər sessiya jurnalda qalmalıdır.', 'fixit' ); ?></p>
		</div>

		<div class="grid grid--3">
			<?php
			$fixit_business = array(
				array(
					'icon'  => 'lock',
					'title' => __( 'Məlumat şirkətdən çıxmır', 'fixit' ),
					'text'  => __( 'Server sizin infrastrukturunuzda qurulur. Ekran görüntüsü, fayllar, yazışma və jurnal kənar buluda getmir.', 'fixit' ),
				),
				array(
					'icon'  => 'users',
					'title' => __( 'Kim kimə qoşula bilər', 'fixit' ),
					'text'  => __( 'İstifadəçilər, rollar və qruplar. Hər operatorun hansı kompüterlərə çıxışı olduğu mərkəzdən təyin olunur.', 'fixit' ),
				),
				array(
					'icon'  => 'chart',
					'title' => __( 'Tam jurnal və hesabat', 'fixit' ),
					'text'  => __( 'Hər sessiya, hər qoşulma cəhdi, fayl köçürmə və uzaqdan əmr yazılır. CSV və PDF kimi yüklənir.', 'fixit' ),
				),
				array(
					'icon'  => 'network',
					'title' => __( 'Port açmaq lazım deyil', 'fixit' ),
					'text'  => __( 'Agent serverə özü qoşulur. İşçi kompüterlərində heç bir port açılmır, fərqli VLAN-lar problem yaratmır.', 'fixit' ),
				),
				array(
					'icon'  => 'shield',
					'title' => __( 'Girişin qorunması', 'fixit' ),
					'text'  => __( 'Panelə giriş iki mərhələli doğrulama ilə, agentlər isə sertifikat barmaq izi ilə yoxlanılır; hər yeni kompüter admin təsdiqindən keçir.', 'fixit' ),
				),
				array(
					'icon'  => 'wrench',
					'title' => __( 'Kütləvi quraşdırma', 'fixit' ),
					'text'  => __( 'MSI quraşdırıcısı parametrlərlə sakitcə quraşdırılır, agentlər sonra özləri yenilənir — hər maşına ayrıca getmək lazım gəlmir.', 'fixit' ),
				),
			);

			foreach ( $fixit_business as $fixit_item ) :
				?>
				<article class="card reveal" style="cursor:default">
					<span class="card__icon"><?php fixit_icon( $fixit_item['icon'] ); ?></span>
					<h3><?php echo esc_html( $fixit_item['title'] ); ?></h3>
					<p><?php echo esc_html( $fixit_item['text'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>

		<div style="text-align:center;margin-top:34px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
			<a class="btn btn--white" href="<?php echo esc_url( $fixit_contact_url ); ?>">
				<?php fixit_icon( 'send' ); ?><?php esc_html_e( 'Təklif istəyin', 'fixit' ); ?>
			</a>

			<?php if ( $fixit_remote_page ) : ?>
				<a class="btn btn--light" href="<?php echo esc_url( $fixit_remote_page ); ?>">
					<?php esc_html_e( 'Məhsul haqqında ətraflı', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>

<!-- Biznes: fərqlər cədvəli -->
<section class="section section--soft">
	<div class="container">
		<div class="section-head section-head--center reveal">
			<span class="eyebrow"><?php esc_html_e( 'Müqayisə', 'fixit' ); ?></span>
			<h2><?php esc_html_e( 'Hansı variant sizə uyğundur', 'fixit' ); ?></h2>
		</div>

		<div class="compare reveal">
			<div class="compare__scroll">
				<table>
					<thead>
						<tr>
							<th><?php esc_html_e( 'Xüsusiyyət', 'fixit' ); ?></th>
							<th><?php esc_html_e( 'Fərdi (pulsuz)', 'fixit' ); ?></th>
							<th><?php esc_html_e( 'Biznes', 'fixit' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$fixit_rows = array(
							array( __( 'Ekrana baxmaq və idarə etmək', 'fixit' ), __( 'var', 'fixit' ), __( 'var', 'fixit' ) ),
							array( __( 'Yazışma və fayl ötürmə', 'fixit' ), __( 'var', 'fixit' ), __( 'var', 'fixit' ) ),
							array( __( 'Qoşulma üsulu', 'fixit' ), __( 'nömrə və şifrə', 'fixit' ), __( 'nömrə, şifrə və ya mərkəzi icazə', 'fixit' ) ),
							array( __( 'Server', 'fixit' ), __( 'bizim mərkəzi server', 'fixit' ), __( 'sizin öz serveriniz', 'fixit' ) ),
							array( __( 'İstifadəçilər və icazələr', 'fixit' ), __( 'yoxdur', 'fixit' ), __( 'rollar, qruplar, cihaz təsdiqi', 'fixit' ) ),
							array( __( 'Sessiya jurnalı', 'fixit' ), __( 'yalnız cihazın öz siyahısı', 'fixit' ), __( 'tam jurnal, CSV və PDF', 'fixit' ) ),
							array( __( 'Kütləvi quraşdırma', 'fixit' ), __( 'əl ilə', 'fixit' ), __( 'MSI ilə sakit quraşdırma', 'fixit' ) ),
							array( __( 'Dəstək', 'fixit' ), __( 'ictimai', 'fixit' ), __( 'müqavilə ilə', 'fixit' ) ),
						);

						foreach ( $fixit_rows as $fixit_row ) :
							?>
							<tr>
								<td><b><?php echo esc_html( $fixit_row[0] ); ?></b></td>
								<td><?php echo esc_html( $fixit_row[1] ); ?></td>
								<td><?php echo esc_html( $fixit_row[2] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</section>

<!-- ============================ BİZİM DƏSTƏK ============================ -->
<section class="section">
	<div class="container" style="text-align:center">
		<h2 style="margin-bottom:12px"><?php esc_html_e( 'Problemi mütəxəssis həll etsin?', 'fixit' ); ?></h2>
		<p style="max-width:680px;margin:0 auto 24px;color:var(--text-soft)">
			<?php esc_html_e( 'Proqram pulsuzdur və hər kəs onu istədiyi kimi işlədə bilər. Kompüterinizdəki problemi özünüz həll etmək istəmirsinizsə — eyni proqramla bizim mütəxəssis qoşulub kömək edər. Bu, ayrıca xidmətdir.', 'fixit' ); ?>
		</p>

		<div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
			<?php if ( $fixit_whatsapp ) : ?>
				<?php
				// Hazır mətnlə açılır: müştəri nə yazacağını düşünməsin,
				// biz də söhbətin hansı səhifədən gəldiyini bilək.
				$fixit_wa_text = rawurlencode( __( 'Salam! Uzaqdan dəstək lazımdır.', 'fixit' ) );
				?>
				<a class="btn btn--primary" href="https://wa.me/<?php echo esc_attr( fixit_tel( $fixit_whatsapp ) ); ?>?text=<?php echo esc_attr( $fixit_wa_text ); ?>" target="_blank" rel="noopener noreferrer">
					<?php fixit_icon( 'whatsapp' ); ?><?php esc_html_e( 'WhatsApp-dan yazın', 'fixit' ); ?>
				</a>
			<?php endif; ?>

			<a class="btn btn--ghost" href="<?php echo esc_url( $fixit_contact_url ); ?>">
				<?php esc_html_e( 'Əlaqə formu', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?>
			</a>
		</div>
	</div>
</section>

<?php
get_footer();
