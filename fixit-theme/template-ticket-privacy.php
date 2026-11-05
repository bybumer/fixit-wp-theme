<?php
/**
 * Template Name: FIXIT Ticket: Məxfilik siyasəti
 *
 * FIXIT Ticket Android tətbiqinin məxfilik siyasəti. Google Play listinqi
 * (Privacy policy və Delete account URL) bu ünvanı göstərir, ona görə
 * ayrıca səhifədir və tema yenilənəndə özü yaranır (inc/demo-content.php).
 *
 * Mətn Fixit-Ticket-Android reposundakı store/privacy-policy.md ilə
 * eynidir; dəyişəndə ikisi də yenilənməlidir.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$fixit_ticket_product = get_page_by_path( 'sorgu-sistemi', OBJECT, 'fixit_product' );
$fixit_ticket_url     = $fixit_ticket_product ? get_permalink( $fixit_ticket_product ) : '';
?>

<section class="page-hero">
	<div class="container">
		<span class="eyebrow"><?php fixit_icon( 'lock' ); ?><?php esc_html_e( 'Məxfilik', 'fixit' ); ?></span>
		<h1><?php esc_html_e( 'FIXIT Ticket: Məxfilik siyasəti', 'fixit' ); ?></h1>
		<p><?php esc_html_e( 'Android tətbiqi haqqında. Son yenilənmə: 25 oktyabr 2026.', 'fixit' ); ?></p>

		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Naviqasiya', 'fixit' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ana səhifə', 'fixit' ); ?></a>
			<span>/</span>
			<?php if ( $fixit_ticket_url ) : ?>
				<a href="<?php echo esc_url( $fixit_ticket_url ); ?>">FIXIT Ticket</a>
				<span>/</span>
			<?php endif; ?>
			<span><?php esc_html_e( 'Məxfilik siyasəti', 'fixit' ); ?></span>
		</nav>
	</div>
</section>

<section class="section">
	<div class="container">
		<div class="policy">

			<!-- ======================= AZ ======================= -->
			<h2><?php esc_html_e( 'Qısaca', 'fixit' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Reklam, analitika, çökmə hesabatı və izləmə vasitəsi yoxdur.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'Tətbiq yalnız şirkətinizin öz FIXIT Ticket serveri ilə işləyir. Ünvanı siz yazırsınız və məlumat o serverə gedir.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'FIXIT (tətbiqin tərtibatçısı) sizin hesabınızı, sorğularınızı və fayllarınızı görmür və saxlamır.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'Məlumat satılmır və heç bir üçüncü tərəfə verilmir.', 'fixit' ); ?></li>
			</ul>

			<h2><?php esc_html_e( '1. Kim nəyə cavabdehdir', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Server şirkətinizdə (və ya şirkətinizin seçdiyi hostinqdə) qurulur və onu şirkətiniz idarə edir. Hesabınızı, sorğuları və faylları şirkətinizin administratoru yaradır, saxlayır və silir. Bu məlumatla bağlı suallar üçün şirkətinizə müraciət edin. Tətbiq yalnız sizin cihazınızla şirkətin serveri arasında vasitədir.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '2. Tətbiq hansı məlumatla işləyir', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Şirkətinizin serverinə göndərilir:', 'fixit' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'e-poçt və parol (giriş anında; parol telefonda saxlanılmır), adınız, imzanız və dil seçiminiz;', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'yazdığınız sorğular, cavablar, daxili qeydlər, əlavə etdiyiniz fayllar və şəkillər, qeyd etdiyiniz vaxt;', 'fixit' ); ?></li>
				<li><?php esc_html_e( '«Cihazlar» siyahısı üçün tətbiqin yaratdığı təsadüfi cihaz kimliyi, telefonun modeli və Android versiyası.', 'fixit' ); ?></li>
			</ul>
			<p><?php esc_html_e( 'Telefonda saxlanılır:', 'fixit' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'serverlərin ünvanları və adları;', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'giriş tokenləri, Android Keystore açarı ilə şifrələnmiş;', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'tətbiq ayarları (tema, dil, bildiriş və fon yoxlaması seçimləri);', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'son yüklənən sorğu və tapşırıq siyahıları (şəbəkə olmayanda göstərmək üçün), ayrıca Android Keystore açarı ilə şifrələnmiş;', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'göndərilməmiş cavab, şərh və yeni sorğu qaralamaları, şifrələnmiş; 7 gündən sonra və çıxışda silinir, fayllar qaralamada saxlanılmır;', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'açdığınız faylların nüsxələri.', 'fixit' ); ?></li>
			</ul>
			<p><?php esc_html_e( 'Sorğuya bağlı kompüter üçün «FIXIT Remote-da aç» düyməsini göstərmək üçün tətbiq yalnız telefonda FIXIT Remote tətbiqinin quraşdırılıb-quraşdırılmadığını yoxlayır. Başqa tətbiqlərin siyahısına baxmır.', 'fixit' ); ?></p>
			<p><?php esc_html_e( 'Tətbiq kontaktlarınıza, yerləşmənizə, mikrofona və şəkil qalereyanıza çıxış istəmir. Şəkil çəkəndə telefonun öz kamera tətbiqi açılır, tətbiq yalnız çəkdiyiniz şəkli alır. Fayl əlavə edəndə yalnız seçdiyiniz fayl oxunur.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '3. Bildirişlər', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Bildiriş xidməti (Firebase və s.) işlədilmir. Tətbiq yeniliyi şirkətinizin serverindən özü soruşur: açıq olanda yarım dəqiqədən bir, bağlı olanda seçdiyiniz aralıqla (15, 30, 60 dəqiqə və ya heç vaxt).', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '4. Yeniləmələr', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Google Play-dən quraşdırılmış tətbiq yalnız Google Play vasitəsilə yenilənir. Birbaşa APK ilə quraşdırılmış versiya isə 6 saatdan bir GitHub-dakı açıq faylı (latest.json) oxuyur. Bu zaman GitHub yalnız standart şəbəkə məlumatını (IP ünvanı) görür, sizin hesab məlumatınız göndərilmir.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '5. Təhlükəsizlik', 'fixit' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Bütün əlaqə şifrəli kanal (HTTPS/TLS) ilə gedir, şifrəsiz qoşulma qadağandır.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'Tokenlər şifrəli saxlanılır, tətbiqin məlumatı bulud ehtiyat nüsxəsinə düşmür.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'İki mərhələli doğrulama dəstəklənir.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'İstəsəniz ekran görüntüsünü qadağan edə bilərsiniz.', 'fixit' ); ?></li>
			</ul>

			<h2 id="silme"><?php esc_html_e( '6. Məlumatın silinməsi və hesabın silinməsi', 'fixit' ); ?></h2>
			<p><strong><?php esc_html_e( 'Telefondakı məlumat.', 'fixit' ); ?></strong> <?php esc_html_e( 'Profildə «Çıxış» bu cihazdakı sessiyanı bağlayır və yerli məlumatı, o cümlədən göndərilməmiş qaralamaları silir. Tətbiqi silmək telefondakı hər şeyi silir.', 'fixit' ); ?></p>
			<p><strong><?php esc_html_e( 'Hesab və serverdəki məlumat.', 'fixit' ); ?></strong> <?php esc_html_e( 'Hesabınız şirkətinizin serverindədir, onu şirkətin administratoru silir:', 'fixit' ); ?></p>
			<ul>
				<li><strong><?php esc_html_e( 'Müştəri hesabı:', 'fixit' ); ?></strong> <?php esc_html_e( 'tətbiqdə Profil → Hesabın silinməsi → Sorğu göndər. Şirkətin dəstək xidmətinə silinmə sorğusu gedir, gedişini «Sorğularım» bölməsində görürsünüz. Tətbiqə daxil ola bilmirsinizsə, şirkətin dəstək e-poçtuna yazın.', 'fixit' ); ?></li>
				<li><strong><?php esc_html_e( 'İşçi hesabı:', 'fixit' ); ?></strong> <?php esc_html_e( 'işçi hesabını şirkət yaradır və silir. Silinmə üçün administratorunuza müraciət edin.', 'fixit' ); ?></li>
			</ul>
			<p><?php esc_html_e( 'Şirkətin qanuni saxlama öhdəlikləri varsa (məsələn, müraciətlərin tarixçəsi), bəzi qeydlər şirkətin qaydasına uyğun saxlanıla bilər. Bunu şirkətinizdən soruşun.', 'fixit' ); ?></p>
			<p><?php esc_html_e( 'Kimə müraciət edəcəyinizi bilmirsinizsə, bu ünvana yazın, sizi şirkətinizin administratoruna yönləndirək:', 'fixit' ); ?> <a href="mailto:info@fixit.az">info@fixit.az</a></p>

			<h2><?php esc_html_e( '7. Uşaqlar', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Tətbiq iş aləti kimi nəzərdə tutulub və uşaqlara ünvanlanmır.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '8. Dəyişikliklər', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Siyasət dəyişəndə bu səhifə yenilənir, yuxarıdakı tarix dəyişir.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( 'Əlaqə', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Sual və müraciətlər üçün:', 'fixit' ); ?> <a href="mailto:info@fixit.az">info@fixit.az</a></p>

			<?php
			/*
			 * İngiliscə nüsxə yalnız azərbaycanca səhifədə göstərilir: Google Play
			 * yoxlayıcıları üçün (Play-dəki link bu ünvana gedir). İngilis və rus
			 * səhifələrində mətn onsuz da həmin dildədir.
			 */
			if ( fixit_is_default_lang() ) :
				?>
			<hr>

			<!-- ======================= EN ======================= -->
			<h2>Privacy Policy (English)</h2>
			<p><em>Last updated: 25 October 2026. This policy covers the FIXIT Ticket Android app.</em></p>

			<h3>In short</h3>
			<ul>
				<li>No ads, analytics, crash reporting or tracking.</li>
				<li>The app works only with <strong>your company's own FIXIT Ticket server</strong>. You enter its address and your data goes to that server.</li>
				<li>FIXIT (the app developer) does not see or store your account, tickets or files.</li>
				<li>Data is never sold or shared with third parties.</li>
			</ul>

			<h3>1. Who is responsible</h3>
			<p>The server is installed by your company (or a hosting provider your company chose) and is operated by your company. Your account, tickets and files are created, stored and deleted by your company's administrator. For questions about this data, contact your company. The app is only the link between your device and your company's server.</p>

			<h3>2. Data the app handles</h3>
			<p>Sent to your company's server:</p>
			<ul>
				<li>email and password (at sign-in; the password is not stored on the phone), your name, signature and language;</li>
				<li>tickets, replies, internal notes, attached files and photos, and time entries you create;</li>
				<li>for the "Devices" list: a random device identifier generated by the app, the phone model and Android version.</li>
			</ul>
			<p>Stored on the phone:</p>
			<ul>
				<li>server addresses and names;</li>
				<li>sign-in tokens, encrypted with an Android Keystore key;</li>
				<li>app settings (theme, language, notification and background check options);</li>
				<li>the last loaded ticket and task lists (to show them offline), encrypted with a separate Android Keystore key;</li>
				<li>unsent reply, comment and new ticket drafts, encrypted; deleted after 7 days and when you sign out, files are not kept in drafts;</li>
				<li>copies of files you opened.</li>
			</ul>
			<p>To show the "Open in FIXIT Remote" button for a computer linked to a ticket, the app only checks whether the FIXIT Remote app is installed on the phone. It does not look at the list of other apps.</p>
			<p>The app does not ask for access to contacts, location, microphone or the photo gallery. Taking a photo opens the phone's own camera app; the app only receives the photo you take. When attaching a file, only the file you pick is read.</p>

			<h3>3. Notifications</h3>
			<p>No push service (such as Firebase) is used. The app asks your company's server for updates itself: every 30 seconds while open, and at the interval you choose while closed (15, 30, 60 minutes or never).</p>

			<h3>4. App updates</h3>
			<p>When installed from Google Play, the app is updated only through Google Play. The directly installed APK version reads a public file (latest.json) on GitHub every 6 hours. GitHub only sees standard network data (IP address); no account data is sent.</p>

			<h3>5. Security</h3>
			<ul>
				<li>All traffic is encrypted (HTTPS/TLS); unencrypted connections are blocked.</li>
				<li>Tokens are stored encrypted, and app data is excluded from cloud backups.</li>
				<li>Two-factor authentication is supported.</li>
				<li>Screenshot blocking can be turned on.</li>
			</ul>

			<h3 id="delete">6. Data deletion and account deletion</h3>
			<p><strong>Data on the phone.</strong> Sign out in Profile closes the session on this device and deletes local data, including unsent drafts. Uninstalling the app removes everything on the phone.</p>
			<p><strong>Account and server data.</strong> Your account lives on your company's server and is deleted by your company's administrator:</p>
			<ul>
				<li><strong>Customer account:</strong> in the app, go to Profile → Delete account → Send request. A deletion request is sent to the company's support team, and you can follow it under "My tickets". If you cannot sign in, email your company's support address.</li>
				<li><strong>Staff account:</strong> staff accounts are created and deleted by the company. Contact your administrator.</li>
			</ul>
			<p>If your company has legal retention obligations (for example, ticket history), some records may be kept according to the company's rules. Ask your company for details.</p>
			<p>If you are not sure whom to contact, email <a href="mailto:info@fixit.az">info@fixit.az</a> and we will direct you to your company's administrator.</p>

			<h3>7. Children</h3>
			<p>The app is a work tool and is not directed at children.</p>

			<h3>8. Changes</h3>
			<p>When this policy changes, this page is updated and the date above changes.</p>

			<h3>Contact</h3>
			<p><a href="mailto:info@fixit.az">info@fixit.az</a></p>
			<?php endif; ?>

		</div>
	</div>
</section>

<?php
get_footer();
