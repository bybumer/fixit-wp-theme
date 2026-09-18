<?php
/**
 * 404 — səhifə tapılmadı.
 *
 * @package FIXIT
 */

get_header();
?>

<section class="page-hero">
	<div class="container">
		<span class="eyebrow"><?php esc_html_e( 'Xəta 404', 'fixit' ); ?></span>
		<h1><?php esc_html_e( 'Belə bir səhifə tapılmadı', 'fixit' ); ?></h1>
		<p><?php esc_html_e( 'Axtardığınız səhifə silinmiş və ya ünvanı dəyişmiş ola bilər.', 'fixit' ); ?></p>
	</div>
</section>

<section class="section">
	<div class="container" style="text-align:center">
		<div style="max-width:520px;margin-inline:auto">
			<?php get_search_form(); ?>

			<div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:26px">
				<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php esc_html_e( 'Ana səhifəyə qayıt', 'fixit' ); ?>
				</a>
				<a class="btn btn--ghost" href="<?php echo esc_url( fixit_page_url( 'template-contact.php' ) ); ?>">
					<?php esc_html_e( 'Bizimlə əlaqə', 'fixit' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
