<?php
/**
 * Template Name: Fixit Remote — Məxfilik siyasəti
 *
 * Mobil tətbiqin məxfilik siyasəti. Google Play və App Store listinqində
 * ictimai URL kimi göstərilir — ona görə ayrıca səhifədir və tema
 * yenilənəndə özü yaranır (inc/demo-content.php).
 *
 * Mətn mobile/store/privacy-policy.md ilə eynidir; dəyişəndə ikisi də
 * yenilənməlidir.
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
		<p><?php esc_html_e( 'Mobil tətbiq (Android və iOS) haqqında. Son yenilənmə: 12 sentyabr 2026.', 'fixit' ); ?></p>

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
			<p><?php esc_html_e( 'Tətbiq heç bir məlumat toplamır, heç kimə göndərmir və heç bir analitika sistemi ilə işləmir. Bütün məlumat işlədiyiniz şirkətin öz serverində qalır — biz ona çıxış əldə etmirik.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( 'Tətbiq nə göstərir', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Tətbiq yalnız sizin şirkətinizin serverinə qoşulur (ünvanı özünüz yazırsınız) və oradan gələn məlumatı ekranda göstərir:', 'fixit' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'əməkdaşın adı və soyadı,', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'şöbə və komanda adı,', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'iş telefonu (əgər şirkət onu yazıbsa),', 'fixit' ); ?></li>
				<li><?php esc_html_e( 'kompüterin həmin anda açıq olub-olmaması və son əlaqə vaxtı.', 'fixit' ); ?></li>
			</ul>
			<p><?php esc_html_e( 'Bu məlumatın sahibi şirkətdir. Tətbiq onu yalnız göstərir — saxlamır və başqa yerə ötürmür.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( 'Telefonda nə saxlanılır', 'fixit' ); ?></h2>
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
						<tr><td><?php esc_html_e( 'Server ünvanı', 'fixit' ); ?></td><td><?php esc_html_e( 'Tətbiqin öz yaddaşında', 'fixit' ); ?></td><td><?php esc_html_e( 'Hər dəfə yazmamaq üçün', 'fixit' ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Serverin sertifikat izi', 'fixit' ); ?></td><td><?php esc_html_e( 'Tətbiqin öz yaddaşında', 'fixit' ); ?></td><td><?php esc_html_e( 'Serverin dəyişdiyini tutmaq üçün', 'fixit' ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Sessiya açarı', 'fixit' ); ?></td><td><?php esc_html_e( 'Sistemin şifrəli anbarında', 'fixit' ); ?></td><td><?php esc_html_e( 'Hər açılışda yenidən giriş olmasın deyə', 'fixit' ); ?></td></tr>
					</tbody>
				</table>
			</div>
			<p><strong><?php esc_html_e( 'Parolunuz saxlanılmır.', 'fixit' ); ?></strong> <?php esc_html_e( 'Sessiya açarının müddəti 12 saatdır və şirkətin administratoru hesabı bağladıqda dərhal etibarsız olur. Tətbiqi silmək bu məlumatların hamısını telefondan silir.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( 'İcazələr', 'fixit' ); ?></h2>
			<ul>
				<li><strong><?php esc_html_e( 'İnternet', 'fixit' ); ?></strong> — <?php esc_html_e( 'yalnız sizin yazdığınız server ünvanına qoşulmaq üçün.', 'fixit' ); ?></li>
				<li><strong><?php esc_html_e( 'Şəbəkə vəziyyəti', 'fixit' ); ?></strong> — <?php esc_html_e( 'bağlantı olmadıqda düzgün mesaj göstərmək üçün.', 'fixit' ); ?></li>
				<li><strong><?php esc_html_e( 'Zəng', 'fixit' ); ?></strong> <?php esc_html_e( 'düyməsi telefonun öz zəng ekranını açır — tətbiq özü zəng etmir və zəng jurnalınızı oxumur.', 'fixit' ); ?></li>
			</ul>
			<p><?php esc_html_e( 'Tətbiq kamera, mikrofon, kontaktlar, məkan və fayllara müraciət etmir.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( 'Üçüncü tərəflər', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Yoxdur. Tətbiqdə reklam, analitika, çökmə hesabatı və izləmə vasitəsi istifadə olunmur. Məlumat yalnız telefonla şirkətin serveri arasında hərəkət edir.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( 'Uşaqlar', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Tətbiq iş yeri üçündür və uşaqlara ünvanlanmır.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( 'Əlaqə', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Sual və müraciətlər üçün:', 'fixit' ); ?> <a href="mailto:info@fixit.az">info@fixit.az</a></p>

			<hr>

			<!-- ======================= EN ======================= -->
			<h2>Privacy Policy (English)</h2>
			<p><em>Last updated: 12 September 2026. This policy covers the Fixit Remote mobile app (Android and iOS).</em></p>

			<h3>Short answer</h3>
			<p>The app collects nothing, sends nothing to us, and uses no analytics. All data stays on your company's own server; we have no access to it.</p>

			<h3>What the app shows</h3>
			<p>The app connects only to your company's server (you enter its address) and displays what that server returns: employee name, department and team, work phone number, whether their computer is currently on, and the time of last contact. That data belongs to your company. The app only displays it — it neither stores nor forwards it.</p>

			<h3>What is stored on the phone</h3>
			<div class="compare__scroll">
				<table>
					<thead><tr><th>Data</th><th>Where</th><th>Why</th></tr></thead>
					<tbody>
						<tr><td>Server address</td><td>App storage</td><td>So you don't retype it</td></tr>
						<tr><td>Server certificate fingerprint</td><td>App storage</td><td>To detect if the server changes</td></tr>
						<tr><td>Session token</td><td>System secure storage</td><td>So you don't sign in on every launch</td></tr>
					</tbody>
				</table>
			</div>
			<p><strong>Your password is not stored.</strong> The session lasts 12 hours and becomes invalid immediately if your administrator disables the account. Uninstalling the app removes all of the above.</p>

			<h3>Permissions</h3>
			<ul>
				<li><strong>Internet</strong> — solely to reach the server address you entered.</li>
				<li><strong>Network state</strong> — to show a correct message when there is no connection.</li>
				<li>The <strong>Call</strong> button opens the phone's own dialler; the app does not place calls and does not read your call log.</li>
			</ul>
			<p>The app does not access camera, microphone, contacts, location or files.</p>

			<h3>Third parties</h3>
			<p>None. No ads, no analytics, no crash reporting, no tracking.</p>

			<h3>Children</h3>
			<p>The app is a workplace tool and is not directed at children.</p>

			<h3>Contact</h3>
			<p><a href="mailto:info@fixit.az">info@fixit.az</a></p>

		</div>
	</div>
</section>

<?php
get_footer();
