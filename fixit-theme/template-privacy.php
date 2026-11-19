<?php
/**
 * Template Name: Məxfilik siyasəti (sayt)
 *
 * fixit.az saytının ümumi məxfilik siyasəti: formalar, sınaq müraciəti,
 * kukilər, kənar xidmətlər, saxlama müddəti, hüquqlar.
 * Mobil tətbiqlərin öz siyasətləri ayrıca səhifələrdədir
 * (template-remote-privacy.php, template-ticket-privacy.php).
 *
 * Kukilər cədvəli inc/consent.php və inc/analytics.php ilə uyğun olmalıdır:
 * yeni kuki və ya xidmət əlavə olunanda bu səhifə də yenilənir.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$fixit_email   = fixit_get( 'fixit_email' );
$fixit_phone   = fixit_get( 'fixit_phone' );
$fixit_remote  = fixit_page_url( 'template-remote-privacy.php' );
$fixit_ticket  = fixit_page_url( 'template-ticket-privacy.php' );
$fixit_cookies = array(
	array( 'fixit_lang', 'fixit.az', __( 'Zəruri', 'fixit' ), __( 'Seçdiyiniz dili yadda saxlayır', 'fixit' ), __( '1 il', 'fixit' ) ),
	array( 'fixit-consent', 'fixit.az', __( 'Zəruri', 'fixit' ), __( 'Kuki seçiminizi yadda saxlayır (brauzerin yaddaşında)', 'fixit' ), __( '12 ay', 'fixit' ) ),
	array( 'fixit-theme', 'fixit.az', __( 'Zəruri', 'fixit' ), __( 'İşıqlı və ya tünd görünüş seçiminiz (brauzerin yaddaşında)', 'fixit' ), __( 'silinənə qədər', 'fixit' ) ),
	array( '__cf_bm, cf_clearance', 'Cloudflare', __( 'Zəruri', 'fixit' ), __( 'Saytı botlardan və hücumlardan qoruyur', 'fixit' ), __( '30 dəqiqə - 1 il', 'fixit' ) ),
	array( '_ga, _ga_*', 'Google Analytics', __( 'Analitika', 'fixit' ), __( 'Ziyarətləri anonim saymaq üçün; yalnız razılıqla', 'fixit' ), __( '2 il', 'fixit' ) ),
	array( '_clck, _clsk', 'Microsoft Clarity', __( 'Analitika', 'fixit' ), __( 'Səhifənin necə istifadə olunduğunu ölçür; yalnız razılıqla', 'fixit' ), __( '1 gün - 1 il', 'fixit' ) ),
	array( '_fbp', 'Meta', __( 'Reklam', 'fixit' ), __( 'Reklamın nəticəsini ölçür; yalnız razılıqla', 'fixit' ), __( '3 ay', 'fixit' ) ),
	array( 'li_*, bcookie, lidc', 'LinkedIn', __( 'Reklam', 'fixit' ), __( 'Reklamın nəticəsini ölçür; yalnız razılıqla', 'fixit' ), __( '1 gün - 1 il', 'fixit' ) ),
);
?>

<section class="page-hero">
	<div class="container">
		<span class="eyebrow"><?php fixit_icon( 'lock' ); ?><?php esc_html_e( 'Məxfilik', 'fixit' ); ?></span>
		<h1><?php esc_html_e( 'Məxfilik siyasəti', 'fixit' ); ?></h1>
		<p><?php esc_html_e( 'fixit.az saytında hansı məlumatların toplandığı, nə üçün işləndiyi və necə qorunduğu. Son yenilənmə: 19 noyabr 2026.', 'fixit' ); ?></p>

		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Naviqasiya', 'fixit' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ana səhifə', 'fixit' ); ?></a>
			<span>/</span>
			<span><?php esc_html_e( 'Məxfilik siyasəti', 'fixit' ); ?></span>
		</nav>
	</div>
</section>

<section class="section">
	<div class="container">
		<div class="policy">

			<h2><?php esc_html_e( 'Qısaca', 'fixit' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Formalarda yazdığınız məlumatı yalnız müraciətinizə cavab vermək üçün işlədirik.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'Məlumatı satmırıq və reklam üçün başqalarına vermirik.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'Analitika və reklam kukiləri yalnız razılıq versəniz işləyir. Seçiminizi istənilən vaxt dəyişə bilərsiniz.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'Proqramların yükləmə sayını hesablayırıq, amma IP ünvanınızı saxlamırıq.', 'fixit' ); ?></li>
			</ul>

			<h2><?php esc_html_e( '1. Məlumatı kim işləyir', 'fixit' ); ?></h2>
			<p>
				<?php esc_html_e( 'Sayt FIXIT-ə məxsusdur (Bakı və Cəlilabad, Azərbaycan). Fərdi məlumatlarınızın işlənməsi ilə bağlı bütün suallar üçün:', 'fixit' ); ?>
				<a href="mailto:<?php echo esc_attr( $fixit_email ); ?>"><?php echo esc_html( $fixit_email ); ?></a>,
				<a href="tel:<?php echo esc_attr( fixit_tel( $fixit_phone ) ); ?>"><?php echo esc_html( $fixit_phone ); ?></a>.
			</p>
			<p><?php esc_html_e( 'Məlumatları Azərbaycan Respublikasının «Fərdi məlumatlar haqqında» Qanununa uyğun işləyirik.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '2. Hansı məlumatları toplayırıq', 'fixit' ); ?></h2>

			<h3><?php esc_html_e( 'Əlaqə və demo formaları', 'fixit' ); ?></h3>
			<p><?php esc_html_e( 'Ad, e-poçt, telefon, şirkətin adı, mövzu və mesajınız. Məlumat saytın idarəetmə panelində saxlanılır və bizim e-poçtumuza göndərilir. Məqsəd: sualınıza cavab vermək, demo girişi göndərmək, təklif hazırlamaq.', 'fixit' ); ?></p>

			<h3><?php esc_html_e( 'Fixit Remote 14 günlük sınaq müraciəti', 'fixit' ); ?></h3>
			<p><?php esc_html_e( 'Ad və soyad, şirkətin adı, telefon, e-poçt və saytın hansı dildə açıldığı. Bu məlumatla sizə qurulum bələdçisini, qiymətləri və şəxsi yükləmə linkini göndəririk, sonra sınaqla bağlı sizinlə əlaqə saxlayırıq. Linkin nə vaxt və neçə dəfə istifadə olunduğunu qeyd edirik ki, qurulumda kömək lazım olub-olmadığını bilək. IP ünvanı saxlanılmır.', 'fixit' ); ?></p>

			<h3><?php esc_html_e( 'Yükləmə sayğacı', 'fixit' ); ?></h3>
			<p><?php esc_html_e( 'Fixit Remote proqramı saytdan yüklənəndə yalnız gün və saytdakı hansı düymənin basıldığı sayılır. IP ünvanı, brauzer və ya cihaz haqqında məlumat saxlanılmır.', 'fixit' ); ?></p>

			<h3><?php esc_html_e( 'Onlayn demolar', 'fixit' ); ?></h3>
			<p><?php esc_html_e( 'demo.fixit.az və remote-demo.fixit.az ünvanlarında hansı rolun seçildiyi və hansı bölmələrə baxıldığı anonim sayılır. Demoda yazdığınız məlumat nümunədir və müntəzəm silinir. IP ünvanları saxlanılmır.', 'fixit' ); ?></p>

			<h3><?php esc_html_e( 'Texniki məlumat və təhlükəsizlik', 'fixit' ); ?></h3>
			<p><?php esc_html_e( 'Sayt açılanda brauzeriniz IP ünvanını və brauzerin növünü serverə göndərir. Bunlardan saytı hücumlardan qorumaq üçün istifadə olunur: məsələn, formadan çox sayda göndərmə və ya səhv parol cəhdləri məhdudlaşdırılır. Bunun üçün IP ünvanının şifrələnmiş izi ən çox bir saat saxlanılır. Hostinq provayderi və Cloudflare öz jurnallarını öz qaydaları ilə saxlayır.', 'fixit' ); ?></p>
			<p><?php esc_html_e( 'Saytın dilini təklif etmək üçün ölkəniz Cloudflare-in verdiyi ölkə koduna görə müəyyən olunur. Bu məlumat saxlanılmır.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '3. Kukilər və brauzerin yaddaşı', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Zəruri kukilər saytın işləməsi üçündür və razılıq tələb etmir. Analitika və reklam kukiləri yalnız saytın aşağısındakı zolaqda razılıq verdikdən sonra yazılır. Razılıq verməsəniz, onların skriptləri ümumiyyətlə yüklənmir.', 'fixit' ); ?></p>
			<div class="compare"><div class="compare__scroll">
				<table>
					<thead>
						<tr>
							<th><?php esc_html_e( 'Ad', 'fixit' ); ?></th>
							<th><?php esc_html_e( 'Kimindir', 'fixit' ); ?></th>
							<th><?php esc_html_e( 'Növ', 'fixit' ); ?></th>
							<th><?php esc_html_e( 'Nə üçün', 'fixit' ); ?></th>
							<th><?php esc_html_e( 'Müddət', 'fixit' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $fixit_cookies as $fixit_row ) : ?>
							<tr>
								<td><code><?php echo esc_html( $fixit_row[0] ); ?></code></td>
								<td><?php echo esc_html( $fixit_row[1] ); ?></td>
								<td><?php echo esc_html( $fixit_row[2] ); ?></td>
								<td><?php echo esc_html( $fixit_row[3] ); ?></td>
								<td><?php echo esc_html( $fixit_row[4] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div></div>
			<p>
				<?php esc_html_e( 'Seçiminizi dəyişmək üçün:', 'fixit' ); ?>
				<button type="button" class="link-btn" data-consent-open><?php esc_html_e( 'Kuki ayarları', 'fixit' ); ?></button>.
				<?php esc_html_e( 'Razılığı geri götürəndə analitika və reklam kukiləri silinir.', 'fixit' ); ?>
			</p>

			<h2><?php esc_html_e( '4. Kənar xidmətlər', 'fixit' ); ?></h2>
			<ul>
				<li><strong>Hostinger</strong>: <?php esc_html_e( 'saytın hostinqi və serveri; formaların məlumatı burada saxlanılır.', 'fixit' ); ?></li>
				<li><strong>Cloudflare</strong>: <?php esc_html_e( 'saytın sürəti və hücumlardan qorunması; bütün sorğular onun şəbəkəsindən keçir.', 'fixit' ); ?></li>
				<li><strong>Google Fonts</strong>: <?php esc_html_e( 'saytın şrifti Google serverindən yüklənir, bu zaman IP ünvanınız Google-a çatır.', 'fixit' ); ?></li>
				<li><strong>Google Maps</strong>: <?php esc_html_e( 'əlaqə səhifəsindəki xəritə yalnız «Xəritəni göstər» düyməsini basanda yüklənir. Bundan sonra Google öz kukilərini yaza bilər.', 'fixit' ); ?></li>
				<li><strong>GitHub</strong>: <?php esc_html_e( 'proqramların yükləmə faylları GitHub serverlərindən verilir.', 'fixit' ); ?></li>
				<li><strong>Google Analytics, Microsoft Clarity, Meta, LinkedIn</strong>: <?php esc_html_e( 'yalnız razılıq versəniz və yalnız ölçmə üçün.', 'fixit' ); ?></li>
			</ul>
			<p><?php esc_html_e( 'Bu xidmətlərin serverləri Azərbaycandan kənarda yerləşə bilər. Hər biri ilə yalnız xidmət üçün lazım olan qədər məlumat paylaşılır.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '5. Nə qədər saxlayırıq', 'fixit' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Müraciətlər və sınaq müraciətləri: əməkdaşlıq davam etdiyi müddətdə və ondan sonra ən çox 3 il. Silinməsini istəsəniz, daha tez silirik.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'Şəxsi yükləmə linki: göndərildiyi gündən 14 gün işləyir, sonra etibarsız olur.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'Yükləmə və demo sayğacları: gün üzrə rəqəmlər, ən çox 400 gün.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'Təhlükəsizlik üçün IP izləri: ən çox 1 saat.', 'fixit' ); ?></li>
			</ul>

			<h2><?php esc_html_e( '6. Hüquqlarınız', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Haqqınızda hansı məlumatın saxlandığını öyrənmək, onu düzəltmək, silinməsini tələb etmək, razılığınızı geri götürmək və məlumatın işlənməsinə etiraz etmək hüququnuz var. Bunun üçün yuxarıdakı e-poçta yazın. Kimliyinizi təsdiqlədikdən sonra müraciətinizi qanunla müəyyən edilmiş müddətdə yerinə yetiririk.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '7. Məlumatın qorunması', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Sayt yalnız HTTPS ilə işləyir. İdarəetmə panelinə giriş məhduddur, səhv parol cəhdləri IP üzrə bloklanır. Proqram paketlərinin şəxsi linki təsadüfi açarla yaradılır və müddətlidir.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '8. Uşaqlar', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Sayt şirkətlər və mütəxəssislər üçündür. 16 yaşdan kiçik şəxslərdən bilərəkdən məlumat toplamırıq.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '9. Proqramlarımızın məxfilik siyasəti', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Mobil tətbiqlərin və proqramların öz siyasətləri var:', 'fixit' ); ?></p>
			<ul>
				<li><a href="<?php echo esc_url( $fixit_remote ); ?>"><?php esc_html_e( 'Fixit Remote mobil tətbiqi', 'fixit' ); ?></a></li>
				<li><a href="<?php echo esc_url( $fixit_ticket ); ?>"><?php esc_html_e( 'FIXIT Ticket mobil tətbiqi', 'fixit' ); ?></a></li>
			</ul>
			<p><?php esc_html_e( 'Şirkətinizin serverində qurulan sistemlərdə (Fixit Remote, Sorğu Sistemi) məlumat sizin serverinizdə qalır və onu siz idarə edirsiniz.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '10. Dəyişikliklər', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Siyasət dəyişəndə yeni mətn bu səhifədə dərc olunur və yuxarıdakı tarix yenilənir. Əhəmiyyətli dəyişikliklərdə kuki razılığı yenidən soruşula bilər.', 'fixit' ); ?></p>
		</div>
	</div>
</section>

<?php
get_footer();
