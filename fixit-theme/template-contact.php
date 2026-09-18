<?php
/**
 * Template Name: Əlaqə səhifəsi
 *
 * @package FIXIT
 */

get_header();

$fixit_phone     = fixit_get( 'fixit_phone' );
$fixit_phone_alt = fixit_get( 'fixit_phone_alt' );
$fixit_email     = fixit_get( 'fixit_email' );
$fixit_whatsapp  = fixit_get( 'fixit_whatsapp' );
$fixit_map       = fixit_get( 'fixit_map_embed' );
$fixit_services  = fixit_get_services();
$fixit_products  = fixit_get_products();
?>

<section class="page-hero">
	<div class="container">
		<span class="eyebrow"><?php fixit_icon( 'phone' ); ?><?php esc_html_e( 'Əlaqə', 'fixit' ); ?></span>
		<h1><?php esc_html_e( 'Bizimlə əlaqə saxlayın', 'fixit' ); ?></h1>
		<p><?php esc_html_e( 'Peşəkar xidmətlərimiz üçün bizə yazın və ya zəng edin. Sizin məmnuniyyətiniz bizim prioritetimizdir.', 'fixit' ); ?></p>

		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Naviqasiya', 'fixit' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ana səhifə', 'fixit' ); ?></a>
			<span>/</span>
			<span><?php the_title(); ?></span>
		</nav>
	</div>
</section>

<section class="section">
	<div class="container contact-grid">

		<!-- Sol: məlumatlar -->
		<div class="reveal">
			<div class="info-card">
				<div class="info-row">
					<span class="ico"><?php fixit_icon( 'phone' ); ?></span>
					<div>
						<h4><?php esc_html_e( 'Telefon', 'fixit' ); ?></h4>
						<a href="tel:<?php echo esc_attr( fixit_tel( $fixit_phone ) ); ?>"><?php echo esc_html( $fixit_phone ); ?></a>
						<?php if ( $fixit_phone_alt ) : ?>
							<small><a href="tel:<?php echo esc_attr( fixit_tel( $fixit_phone_alt ) ); ?>"><?php echo esc_html( $fixit_phone_alt ); ?></a></small>
						<?php endif; ?>
					</div>
				</div>

				<div class="info-row">
					<span class="ico"><?php fixit_icon( 'mail' ); ?></span>
					<div>
						<h4><?php esc_html_e( 'E-poçt', 'fixit' ); ?></h4>
						<a href="mailto:<?php echo esc_attr( $fixit_email ); ?>"><?php echo esc_html( $fixit_email ); ?></a>
					</div>
				</div>

				<?php if ( $fixit_whatsapp ) : ?>
					<div class="info-row">
						<span class="ico"><?php fixit_icon( 'whatsapp' ); ?></span>
						<div>
							<h4>WhatsApp</h4>
							<a href="https://wa.me/<?php echo esc_attr( fixit_tel( $fixit_whatsapp ) ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Birbaşa yazın', 'fixit' ); ?>
							</a>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<div class="info-card">
				<h3><?php esc_html_e( 'Ofislər', 'fixit' ); ?></h3>

				<div class="info-row">
					<span class="ico"><?php fixit_icon( 'pin' ); ?></span>
					<div>
						<h4><?php esc_html_e( 'Baş ofis', 'fixit' ); ?></h4>
						<p><?php echo esc_html( fixit_get( 'fixit_address_main' ) ); ?></p>
						<small><?php fixit_icon( 'clock' ); ?> <?php echo esc_html( fixit_get( 'fixit_hours_main' ) ); ?></small>
					</div>
				</div>

				<div class="info-row">
					<span class="ico"><?php fixit_icon( 'pin' ); ?></span>
					<div>
						<h4><?php esc_html_e( 'Cəlilabad filialı', 'fixit' ); ?></h4>
						<p><?php echo esc_html( fixit_get( 'fixit_address_br' ) ); ?></p>
						<small><?php fixit_icon( 'clock' ); ?> <?php echo esc_html( fixit_get( 'fixit_hours_br' ) ); ?></small>
					</div>
				</div>
			</div>
		</div>

		<!-- Sağ: form -->
		<div class="form reveal" id="elaqe-form">
			<h2 style="font-size:1.5rem;margin-bottom:8px"><?php esc_html_e( 'Müraciət göndərin', 'fixit' ); ?></h2>
			<p style="color:var(--text-soft);margin-bottom:22px;font-size:.96rem">
				<?php esc_html_e( 'Formu doldurun — iş saatları ərzində sizinlə əlaqə saxlayaq.', 'fixit' ); ?>
			</p>

			<?php echo fixit_form_notice(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="fixit_contact">
				<?php wp_nonce_field( 'fixit_contact', 'fixit_contact_nonce' ); ?>

				<!-- Bot tələsi: istifadəçi görmür -->
				<div class="hp-field" aria-hidden="true">
					<label for="fixit_website"><?php esc_html_e( 'Sayt', 'fixit' ); ?></label>
					<input type="text" name="fixit_website" id="fixit_website" tabindex="-1" autocomplete="off">
				</div>

				<div class="field-2">
					<div class="field">
						<label for="fixit_name"><?php esc_html_e( 'Adınız', 'fixit' ); ?> *</label>
						<input type="text" name="fixit_name" id="fixit_name" required>
					</div>
					<div class="field">
						<label for="fixit_phone_f"><?php esc_html_e( 'Telefon', 'fixit' ); ?></label>
						<input type="tel" name="fixit_phone" id="fixit_phone_f" placeholder="+994 __ ___ __ __">
					</div>
				</div>

				<div class="field">
					<label for="fixit_email_f"><?php esc_html_e( 'E-poçt', 'fixit' ); ?> *</label>
					<input type="email" name="fixit_email" id="fixit_email_f" required>
				</div>

				<div class="field">
					<label for="fixit_topic"><?php esc_html_e( 'Mövzu', 'fixit' ); ?></label>
					<?php
					// Məhsul səhifəsindən "Demo təyin et" ilə gəlibsə mövzu öncədən seçilir.
					$fixit_preselect = isset( $_GET['mehsul'] ) ? sanitize_text_field( wp_unslash( $_GET['mehsul'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					?>
					<select name="fixit_topic" id="fixit_topic">
						<option value=""><?php esc_html_e( '— Mövzu seçin —', 'fixit' ); ?></option>

						<?php if ( $fixit_products ) : ?>
							<optgroup label="<?php esc_attr_e( 'Məhsullar (demo / təklif)', 'fixit' ); ?>">
								<?php foreach ( $fixit_products as $fixit_prod ) : ?>
									<option value="<?php echo esc_attr( $fixit_prod->post_title ); ?>" <?php selected( $fixit_preselect, $fixit_prod->post_title ); ?>>
										<?php echo esc_html( $fixit_prod->post_title ); ?>
									</option>
								<?php endforeach; ?>
							</optgroup>
						<?php endif; ?>

						<optgroup label="<?php esc_attr_e( 'Xidmətlər', 'fixit' ); ?>">
							<?php foreach ( $fixit_services as $fixit_svc ) : ?>
								<option value="<?php echo esc_attr( $fixit_svc->post_title ); ?>" <?php selected( $fixit_preselect, $fixit_svc->post_title ); ?>>
									<?php echo esc_html( $fixit_svc->post_title ); ?>
								</option>
							<?php endforeach; ?>
						</optgroup>

						<option value="Digər"><?php esc_html_e( 'Digər', 'fixit' ); ?></option>
					</select>
				</div>

				<div class="field">
					<label for="fixit_message"><?php esc_html_e( 'Mesajınız', 'fixit' ); ?> *</label>
					<textarea name="fixit_message" id="fixit_message" required placeholder="<?php esc_attr_e( 'Ehtiyacınızı qısa şəkildə yazın…', 'fixit' ); ?>"></textarea>
				</div>

				<button class="btn btn--primary btn--block" type="submit">
					<?php fixit_icon( 'send' ); ?><?php esc_html_e( 'Göndər', 'fixit' ); ?>
				</button>

				<p class="form-note"><?php esc_html_e( 'Məlumatlarınız yalnız müraciətinizə cavab vermək üçün istifadə olunur.', 'fixit' ); ?></p>
			</form>
		</div>

	</div>
</section>

<!-- Xəritə -->
<?php if ( $fixit_map ) : ?>
	<section class="section" style="padding-top:0">
		<div class="container">
			<div class="map-wrap reveal">
				<iframe
					src="<?php echo esc_url( $fixit_map ); ?>"
					title="<?php esc_attr_e( 'FIXIT ofisinin xəritədə yeri', 'fixit' ); ?>"
					loading="lazy"
					referrerpolicy="no-referrer-when-downgrade"
					allowfullscreen></iframe>
			</div>
		</div>
	</section>
<?php endif; ?>

<!-- Səhifə redaktorundan gələn əlavə məzmun -->
<?php
while ( have_posts() ) :
	the_post();
	$fixit_content = trim( get_the_content() );
	if ( $fixit_content ) :
		?>
		<section class="section section--soft">
			<div class="container entry"><?php the_content(); ?></div>
		</section>
		<?php
	endif;
endwhile;

get_footer();
