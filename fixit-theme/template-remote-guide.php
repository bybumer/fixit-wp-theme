<?php
/**
 * Template Name: Fixit Remote — Server qurulumu
 *
 * Serverin 14 günlük sınaq üçün qurulması addım-addım. Eyni addımlar
 * sınaq məktubunda da gedir (inc/remote-trial.php → fixit_trial_guide_steps).
 * Server paketi burada YOXDUR: o, yalnız sınaq formasından sonra şəxsi
 * linklə verilir.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$fixit_remote_url = function_exists( 'fixit_product_url' ) ? fixit_product_url( 'remote' ) : home_url( '/' );
?>

<section class="page-hero">
	<div class="container">
		<span class="eyebrow"><?php fixit_icon( 'server' ); ?><?php esc_html_e( 'Qurulum bələdçisi', 'fixit' ); ?></span>
		<h1><?php esc_html_e( 'Fixit Remote serverinin qurulması', 'fixit' ); ?></h1>
		<p><?php esc_html_e( 'Server sizin avadanlığınızda qurulur. Bir Linux server, bir əmr və bir neçə dəqiqə: sonra kompüterləri qoşursunuz.', 'fixit' ); ?></p>

		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Naviqasiya', 'fixit' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ana səhifə', 'fixit' ); ?></a>
			<span>/</span>
			<a href="<?php echo esc_url( $fixit_remote_url ); ?>">Fixit Remote</a>
			<span>/</span>
			<span><?php esc_html_e( 'Qurulum bələdçisi', 'fixit' ); ?></span>
		</nav>
	</div>
</section>

<section class="section">
	<div class="container">
		<div class="policy">
			<div class="alert alert--ok" style="margin-bottom:26px">
				<?php esc_html_e( 'Server paketini sınaq formasını dolduranda e-poçtunuza göndərdiyimiz şəxsi linklə yükləyirsiniz.', 'fixit' ); ?>
				<a href="<?php echo esc_url( $fixit_remote_url . '#sinaq' ); ?>"><?php esc_html_e( 'Linki almaq', 'fixit' ); ?></a>
			</div>

			<ol class="guide-steps">
				<?php foreach ( fixit_trial_guide_steps() as $fixit_step ) : ?>
					<li>
						<h3><?php echo esc_html( $fixit_step['title'] ); ?></h3>
						<p><?php echo esc_html( $fixit_step['text'] ); ?></p>
						<?php if ( ! empty( $fixit_step['code'] ) ) : ?>
							<pre class="guide-code"><?php echo esc_html( $fixit_step['code'] ); ?></pre>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>

			<h2><?php esc_html_e( 'Problem olarsa', 'fixit' ); ?></h2>
			<p><?php esc_html_e( 'Xidmət cavab verirmi və baza, disk qaydasındadırmı:', 'fixit' ); ?></p>
			<pre class="guide-code">curl -k https://127.0.0.1:8443/api/health/ready
journalctl -u fixit-remote-server -n 100 --no-pager</pre>
			<p><?php esc_html_e( 'Quraşdırmanın hər addımı jurnala yazılır:', 'fixit' ); ?></p>
			<pre class="guide-code">sudo less /var/log/fixit-remote-kur.log</pre>
			<p><?php esc_html_e( 'Bizə göndərmək üçün diaqnostika paketi (şəxsi məlumat və açarlar gizlədilir):', 'fixit' ); ?></p>
			<pre class="guide-code">sudo bash /opt/fixit-remote/diaq.sh</pre>
			<p><?php esc_html_e( 'Agent qoşulmursa, kompüterdən serverin 8443 portuna çıxış olduğunu və serverin saatının düzgün olduğunu yoxlayın. Qurulum zamanı sertifikat izi və qurulum kodu yalnız serverdə saxlanılır, kənara getmir.', 'fixit' ); ?></p>

			<h2><?php esc_html_e( 'Kömək lazımdır?', 'fixit' ); ?></h2>
			<p>
				<?php esc_html_e( 'Mütəxəssisimiz qurulumda uzaqdan kömək edə bilər. Zəng edin və ya məktuba cavab yazın:', 'fixit' ); ?>
				<a href="tel:<?php echo esc_attr( fixit_tel( fixit_get( 'fixit_phone' ) ) ); ?>"><?php echo esc_html( fixit_get( 'fixit_phone' ) ); ?></a>
			</p>
		</div>
	</div>
</section>

<?php
get_footer();
