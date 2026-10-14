<?php
/**
 * Template Name: Fixit Remote — Məxfilik siyasəti
 *
 * Mobil tətbiqin məxfilik siyasəti. Google Play və App Store listinqində
 * və tətbiqin öz Ayarlar səhifəsində bu ünvan göstərilir — ona görə
 * ayrıca səhifədir və tema yenilənəndə özü yaranır (inc/demo-content.php).
 *
 * Mətn Fixit-Remote reposundakı mobile/store/privacy-policy.md ilə
 * eynidir; dəyişəndə ikisi də yenilənməlidir.
 *
 * @package FIXIT
 */

get_header();

$fixit_remote_url = fixit_page_url( 'template-remote.php' );
?>

<section class="page-hero">
	<div class="container">
		<span class="eyebrow"><?php fixit_icon( 'lock' ); ?><?php esc_html_e( 'Məxfilik', 'fixit' ); ?></span>
		<h1><?php esc_html_e( 'Fixit Remote — Məxfilik siyasəti', 'fixit' ); ?></h1>
		<p><?php esc_html_e( 'Mobil tətbiq (Android və iOS) haqqında. Son yenilənmə: 12 oktyabr 2026.', 'fixit' ); ?></p>

		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Naviqasiya', 'fixit' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ana səhifə', 'fixit' ); ?></a>
			<span>/</span>
			<?php if ( $fixit_remote_url ) : ?>
				<a href="<?php echo esc_url( $fixit_remote_url ); ?>"><?php esc_html_e( 'Uzaqdan qoşulma', 'fixit' ); ?></a>
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
			<h2><?php esc_html_e( 'Qısa cavab', 'fixit' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Reklam, analitika, çökmə hesabatı və izləmə vasitəsi yoxdur.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'Məlumat satılmır və heç bir üçüncü tərəfə verilmir.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'Tətbiq iki serverlə işləyir:', 'fixit' ); ?>
					<ul>
						<li><strong><?php esc_html_e( 'Fixit-in ID serveri', 'fixit' ); ?></strong> (id.fixit.az) — <?php esc_html_e( 'cihazlar arasında qoşulmanı qurmaq üçün. Orada yalnız bunun üçün lazım olan minimum saxlanılır; ekran görüntüsü saxlanılmır.', 'fixit' ); ?></li>
						<li><strong><?php esc_html_e( 'Şirkətinizin öz serveri', 'fixit' ); ?></strong> (<?php esc_html_e( 'istəyə görə', 'fixit' ); ?>) — <?php esc_html_e( 'komandalar, cihazlar və yazışma. Bu məlumat şirkətinizdə qalır, Fixit-in ona çıxışı yoxdur.', 'fixit' ); ?></li>
					</ul>
				</li>
			</ul>

			<h2><?php esc_html_e( '1. ID ilə qoşulma (id.fixit.az)', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Telefonunuzun ekranını paylaşmağı açdığınız zaman telefon ID serverində qeydiyyatdan keçir. Server bunları saxlayır:', 'fixit' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'tətbiqin yaratdığı təsadüfi cihaz kimliyi və 9 rəqəmli ID nömrəsi,', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'cihazın adı (telefonun adı və ya modeli) və əməliyyat sisteminin versiyası,', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'qoşulma parolunun hash-i — parolun özü serverə heç vaxt göndərilmir,', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'son əlaqə vaxtı.', 'fixit' ); ?></li>
			</ul>
			<p><?php esc_html_e( 'Hər qoşulma cəhdi təhlükəsizlik jurnalına yazılır: vaxt, ID nömrəsi, IP ünvanı və nəticə. Bu, parol təxmin etmə cəhdlərini aşkar edib dayandırmaq üçündür.', 'fixit' ); ?></p>
			<p><?php esc_html_e( 'Qoşulma zamanı ekran görüntüsü, toxunuş və klaviatura əmrləri server vasitəsilə real vaxtda qarşı tərəfə ötürülür. Server onları saxlamır. Bütün əlaqə şifrəli kanal (HTTPS/TLS) ilə gedir.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '2. Telefonun ekranını paylaşmaq', 'fixit' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Yalnız siz açanda işləyir. Bağlı olanda telefon ID serverinə qoşulmur.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'Kimsə qoşulmaq istəyəndə telefon hər dəfə sizdən icazə soruşur.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'Ekranı yazmağa Android-in öz icazə pəncərəsi ilə razılıq verirsiniz.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'Paylaşım davam etdiyi müddətdə bildiriş daim görünür; istənilən an dayandıra bilərsiniz.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'Qoşulan tərəf ekranınıza yalnız baxır — telefonu idarə edə bilməz.', 'fixit' ); ?></li>
			</ul>

			<h2><?php esc_html_e( '3. Başqa cihaza qoşulmaq', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Qoşulduğunuz kompüterin ekranı telefona gəlir, toxunuş və klaviatura əmrləriniz oraya gedir. Görüntü telefonda yazılmır və saxlanılmır.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '4. Şirkətinizin serveri', 'fixit' ); ?></h2>
			<p><?php esc_html_e( '"Kontaktlar" və yazışma bölmələri işlədiyiniz şirkətin serverinə qoşulur (ünvanı özünüz yazırsınız) və istifadəçi adı ilə parol tələb edir. Komandalar, əməkdaşların adları, cihazlar və mesajlar həmin serverdə saxlanılır. Bu məlumatın sahibi şirkətdir; Fixit-in ona çıxışı yoxdur. Onunla bağlı suallar üçün şirkətinizin administratoruna müraciət edin.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '5. Telefonda nə saxlanılır', 'fixit' ); ?></h2>
			<div class="compare__scroll">
				<table>
					<thead>
						<tr>
							<th><?php esc_html_e( 'Məlumat', 'fixit' ); ?></th>
							<th><?php esc_html_e( 'Harada', 'fixit' ); ?></th>
							<th><?php esc_html_e( 'Nə üçün', 'fixit' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr><td><?php esc_html_e( 'Server ünvanları və sertifikat izi', 'fixit' ); ?></td><td><?php esc_html_e( 'Tətbiqin öz yaddaşında', 'fixit' ); ?></td><td><?php esc_html_e( 'Hər dəfə yazmamaq və serverin dəyişdiyini tutmaq üçün', 'fixit' ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Sessiya açarı (12 saat)', 'fixit' ); ?></td><td><?php esc_html_e( 'Sistemin şifrəli anbarında', 'fixit' ); ?></td><td><?php esc_html_e( 'Hər açılışda yenidən daxil olmamaq üçün', 'fixit' ); ?></td></tr>
						<tr><td><?php esc_html_e( 'İstifadəçi adı', 'fixit' ); ?></td><td><?php esc_html_e( 'Tətbiqin öz yaddaşında', 'fixit' ); ?></td><td><?php esc_html_e( 'Giriş formasını doldurmaq üçün', 'fixit' ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Hesab parolu', 'fixit' ); ?></td><td><?php esc_html_e( 'Sistemin şifrəli anbarında', 'fixit' ); ?></td><td><?php esc_html_e( 'Yalnız "Parolu yadda saxla" seçilibsə', 'fixit' ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Qoşulduğunuz cihazların parolları', 'fixit' ); ?></td><td><?php esc_html_e( 'Sistemin şifrəli anbarında', 'fixit' ); ?></td><td><?php esc_html_e( 'Yalnız yadda saxlamağı seçdiyiniz cihazlar üçün', 'fixit' ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Telefonun öz ID-si və qoşulma parolu', 'fixit' ); ?></td><td><?php esc_html_e( 'Tətbiqin öz yaddaşında', 'fixit' ); ?></td><td><?php esc_html_e( 'Ekran paylaşımı üçün', 'fixit' ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Son qoşulmalar siyahısı', 'fixit' ); ?></td><td><?php esc_html_e( 'Tətbiqin öz yaddaşında', 'fixit' ); ?></td><td><?php esc_html_e( 'Tez yenidən qoşulmaq üçün', 'fixit' ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Görünüş ayarları', 'fixit' ); ?></td><td><?php esc_html_e( 'Tətbiqin öz yaddaşında', 'fixit' ); ?></td><td><?php esc_html_e( 'Mövzu və idarə rejimi', 'fixit' ); ?></td></tr>
					</tbody>
				</table>
			</div>
			<p><?php esc_html_e( 'Son qoşulmaları Ayarlar → "Qoşulma tarixçəsi" ilə, sertifikatı və saxlanmış parolu Ayarlar → "Serveri unut" ilə silmək olar. Tətbiqi silmək bunların hamısını telefondan silir.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '6. İcazələr', 'fixit' ); ?></h2>
			<ul>
				<li><strong><?php esc_html_e( 'İnternet', 'fixit' ); ?></strong> — <?php esc_html_e( 'serverlərə qoşulmaq üçün.', 'fixit' ); ?></li>
				<li><strong><?php esc_html_e( 'Şəbəkə vəziyyəti', 'fixit' ); ?></strong> — <?php esc_html_e( 'bağlantı olmadıqda düzgün mesaj göstərmək üçün.', 'fixit' ); ?></li>
				<li><strong><?php esc_html_e( 'Bildirişlər', 'fixit' ); ?></strong> — <?php esc_html_e( 'ekran paylaşımı açıq olduğunu göstərmək üçün.', 'fixit' ); ?></li>
				<li><strong><?php esc_html_e( 'Ekran paylaşımı xidməti', 'fixit' ); ?></strong> (Android: FOREGROUND_SERVICE_MEDIA_PROJECTION) — <?php esc_html_e( 'yalnız sizin başlatdığınız paylaşım müddətində ekranı göndərmək üçün.', 'fixit' ); ?></li>
			</ul>
			<p><?php esc_html_e( 'Tətbiq kamera, mikrofon, kontaktlar, məkan və fayllara müraciət etmir.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '7. Üçüncü tərəflər', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Yoxdur. Tətbiqdə reklam, analitika, çökmə hesabatı və izləmə vasitəsi istifadə olunmur.', 'fixit' ); ?></p>

			<h2 id="silme"><?php esc_html_e( '8. Məlumatın silinməsi', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Fixit Remote tətbiqinin ID serverindəki (id.fixit.az) məlumatınızın silinməsini belə istəyə bilərsiniz:', 'fixit' ); ?></p>
			<ol>
				<li><?php esc_html_e( 'Bu ünvana "Məlumatın silinməsi" mövzusu ilə yazın:', 'fixit' ); ?> <a href="mailto:info@fixit.az?subject=M%C9%99lumat%C4%B1n%20silinm%C9%99si">info@fixit.az</a></li>
				<li><?php esc_html_e( 'Məktubda telefonunuzun 9 rəqəmli ID nömrəsini göstərin — o, tətbiqin "Əsas" səhifəsində görünür.', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'Həmin ID-yə aid məlumat 30 gün ərzində silinir və nəticə barədə sizə cavab yazılır.', 'fixit' ); ?></li>
			</ol>
			<p><strong><?php esc_html_e( 'Silinir:', 'fixit' ); ?></strong> <?php esc_html_e( 'cihaz qeydi (cihaz kimliyi, ID nömrəsi, cihazın adı, əməliyyat sisteminin versiyası, parolun hash-i, son əlaqə vaxtı) və həmin ID-yə aid qoşulma jurnalı (vaxt, IP ünvanı, nəticə).', 'fixit' ); ?></p>
			<p><strong><?php esc_html_e( 'Qalmır:', 'fixit' ); ?></strong> <?php esc_html_e( 'ID serverində bu ID-yə aid heç nə saxlanılmır. Ekran görüntüsü heç vaxt saxlanılmır.', 'fixit' ); ?></p>
			<p><?php esc_html_e( 'Silmə istəyi olmadıqda cihaz qeydi və qoşulma jurnalı xidmətin işləməsi və təhlükəsizliyi üçün saxlanılır.', 'fixit' ); ?></p>
			<p><?php esc_html_e( 'Telefonda saxlananları tətbiqi silməklə özünüz silə bilərsiniz (bax: 5-ci bənd). Şirkət serverindəki hesab və mesajlar şirkətinizə məxsusdur — onların silinməsi üçün şirkətinizin administratoruna müraciət edin.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '9. Uşaqlar', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Tətbiq iş aləti kimi nəzərdə tutulub və uşaqlara ünvanlanmır.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( '10. Dəyişikliklər', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Bu siyasət dəyişəndə yeni mətn bu ünvanda dərc olunur və yuxarıdakı tarix yenilənir.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( 'Əlaqə', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Sual və müraciətlər üçün:', 'fixit' ); ?> <a href="mailto:info@fixit.az">info@fixit.az</a></p>

			<?php
			/*
			 * İngiliscə nüsxə yalnız azərbaycanca səhifədə göstərilir — Google Play
			 * yoxlayıcıları üçün (Play-dəki link bu ünvana gedir). İngilis və rus
			 * səhifələrində mətn onsuz da həmin dildədir.
			 */
			if ( fixit_is_default_lang() ) :
				?>
			<hr>

			<!-- ======================= EN ======================= -->
			<h2>Privacy Policy (English)</h2>
			<p><em>Last updated: 12 October 2026. This policy covers the Fixit Remote mobile app (Android and iOS).</em></p>

			<h3>Short answer</h3>
			<ul>
				<li>No ads, analytics, crash reporting or tracking.</li>
				<li>Data is never sold or given to third parties.</li>
				<li>The app works with two servers:
					<ul>
						<li><strong>Fixit's ID server</strong> (id.fixit.az) — to set up connections between devices. It keeps only the minimum needed for that; screen images are not stored.</li>
						<li><strong>Your company's own server</strong> (optional) — teams, devices and chat. That data stays with your company; Fixit has no access to it.</li>
					</ul>
				</li>
			</ul>

			<h3>1. Connecting by ID (id.fixit.az)</h3>
			<p>When you turn on screen sharing, the phone registers with the ID server, which stores: a random device identifier generated by the app and a 9-digit ID number; the device name (phone name or model) and OS version; a <strong>hash</strong> of the connection password (the password itself is never sent); and the time of last contact.</p>
			<p>Every connection attempt is logged for security: time, ID number, IP address and result. This is used to detect and stop password-guessing.</p>
			<p>During a session, screen images and touch/keyboard commands are relayed through the server in real time. The server does <strong>not</strong> store them. All traffic uses an encrypted channel (HTTPS/TLS).</p>

			<h3>2. Sharing your phone's screen</h3>
			<ul>
				<li>It runs only when you turn it on; otherwise the phone does not connect to the ID server.</li>
				<li>The phone asks for your permission every time someone wants to connect.</li>
				<li>You grant screen capture through Android's own system dialog.</li>
				<li>A notification stays visible for as long as sharing is on; you can stop it at any time.</li>
				<li>The other side can only <strong>view</strong> your screen — it cannot control your phone.</li>
			</ul>

			<h3>3. Connecting to another device</h3>
			<p>The remote computer's screen is shown on your phone and your touch and keyboard input is sent to it. The image is not recorded or stored on the phone.</p>

			<h3>4. Your company's server</h3>
			<p>The Contacts and chat sections connect to your employer's server (you enter its address) and require a username and password. Teams, employee names, devices and messages are stored on that server. That data belongs to your company; Fixit has no access to it. Contact your company's administrator about it.</p>

			<h3>5. What is stored on the phone</h3>
			<div class="compare__scroll">
				<table>
					<thead><tr><th>Data</th><th>Where</th><th>Why</th></tr></thead>
					<tbody>
						<tr><td>Server addresses and certificate fingerprint</td><td>App storage</td><td>So you don't retype them, and to detect if a server changes</td></tr>
						<tr><td>Session token (12 hours)</td><td>System secure storage</td><td>So you don't sign in on every launch</td></tr>
						<tr><td>Username</td><td>App storage</td><td>To pre-fill the sign-in form</td></tr>
						<tr><td>Account password</td><td>System secure storage</td><td>Only if "Remember password" is on</td></tr>
						<tr><td>Passwords of devices you connect to</td><td>System secure storage</td><td>Only for devices you chose to remember</td></tr>
						<tr><td>The phone's own ID and connection password</td><td>App storage</td><td>For screen sharing</td></tr>
						<tr><td>Recent connections</td><td>App storage</td><td>For quick reconnects</td></tr>
						<tr><td>Appearance settings</td><td>App storage</td><td>Theme and control mode</td></tr>
					</tbody>
				</table>
			</div>
			<p>Recent connections can be cleared in Settings → "Connection history"; the certificate and saved password via Settings → "Forget server". Uninstalling the app removes all of the above.</p>

			<h3>6. Permissions</h3>
			<ul>
				<li><strong>Internet</strong> — to reach the servers.</li>
				<li><strong>Network state</strong> — to show a correct message when offline.</li>
				<li><strong>Notifications</strong> — to show that screen sharing is on.</li>
				<li><strong>Screen sharing service</strong> (Android: FOREGROUND_SERVICE_MEDIA_PROJECTION) — to send the screen only while a share you started is running.</li>
			</ul>
			<p>The app does not access camera, microphone, contacts, location or files.</p>

			<h3>7. Third parties</h3>
			<p>None. No ads, no analytics, no crash reporting, no tracking.</p>

			<h3 id="delete">8. Data deletion</h3>
			<p>To request deletion of your data on the Fixit Remote ID server (id.fixit.az):</p>
			<ol>
				<li>Email <a href="mailto:info@fixit.az?subject=Data%20deletion">info@fixit.az</a> with the subject "Data deletion".</li>
				<li>Include your phone's 9-digit ID number — it is shown on the app's Home ("Əsas") screen.</li>
				<li>The data for that ID is deleted within 30 days and we reply to confirm.</li>
			</ol>
			<p><strong>Deleted:</strong> the device record (device identifier, ID number, device name, OS version, password hash, time of last contact) and the connection log entries for that ID (time, IP address, result).</p>
			<p><strong>Kept:</strong> nothing for that ID remains on the ID server. Screen images are never stored.</p>
			<p>Without a deletion request, the device record and connection log are kept to run the service and keep it secure.</p>
			<p>Data stored on the phone can be removed by uninstalling the app (see section 5). Accounts and messages on your company's server belong to your company — contact your company's administrator to have them deleted.</p>

			<h3>9. Children</h3>
			<p>The app is a workplace tool and is not directed at children.</p>

			<h3>10. Changes</h3>
			<p>When this policy changes, the new text is published at this address and the date above is updated.</p>

			<h3>Contact</h3>
			<p><a href="mailto:info@fixit.az">info@fixit.az</a></p>
			<?php endif; ?>

		</div>
	</div>
</section>

<?php
get_footer();
