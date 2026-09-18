<?php
/**
 * Saytın alt hissəsi.
 *
 * @package FIXIT
 */

$fixit_phone    = fixit_get( 'fixit_phone' );
$fixit_email    = fixit_get( 'fixit_email' );
$fixit_whatsapp = fixit_get( 'fixit_whatsapp' );
$fixit_socials  = array(
	'facebook'  => fixit_get( 'fixit_facebook' ),
	'instagram' => fixit_get( 'fixit_instagram' ),
	'tiktok'    => fixit_get( 'fixit_tiktok' ),
	'linkedin'  => fixit_get( 'fixit_linkedin' ),
);
$fixit_services = fixit_get_services( 6 );
?>
</main><!-- #main -->

<footer class="footer">
	<div class="container">
		<div class="footer__grid">

			<!-- Şirkət -->
			<div>
				<span class="logo">
					<span class="logo__mark"><?php fixit_icon( 'shield' ); ?></span>
					<span>
						FIXIT
						<small class="logo__sub"><?php esc_html_e( 'İKT Həlləri', 'fixit' ); ?></small>
					</span>
				</span>
				<p class="about">
					<?php esc_html_e( '2017-ci ildən etibarən informasiya texnologiyaları sahəsində peşəkar həllər təqdim edən etibarlı tərəfdaşınız.', 'fixit' ); ?>
				</p>

				<div class="socials">
					<?php foreach ( $fixit_socials as $fixit_net => $fixit_url ) : ?>
						<?php if ( $fixit_url ) : ?>
							<a href="<?php echo esc_url( $fixit_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( ucfirst( $fixit_net ) ); ?>">
								<?php fixit_icon( $fixit_net ); ?>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Xidmətlər -->
			<div>
				<h4><?php esc_html_e( 'Xidmətlər', 'fixit' ); ?></h4>
				<ul>
					<?php if ( $fixit_services ) : ?>
						<?php foreach ( $fixit_services as $fixit_svc ) : ?>
							<li>
								<a href="<?php echo esc_url( fixit_page_url( 'template-services.php' ) . '#xidmet-' . $fixit_svc->ID ); ?>">
									<?php echo esc_html( $fixit_svc->post_title ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			</div>

			<!-- Keçidlər -->
			<div>
				<h4><?php esc_html_e( 'Keçidlər', 'fixit' ); ?></h4>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ana səhifə', 'fixit' ); ?></a></li>
					<li><a href="<?php echo esc_url( fixit_page_url( 'template-services.php' ) ); ?>"><?php esc_html_e( 'Xidmətlər', 'fixit' ); ?></a></li>
					<li><a href="<?php echo esc_url( fixit_page_url( 'template-products.php' ) ); ?>"><?php esc_html_e( 'Məhsullar', 'fixit' ); ?></a></li>
					<li><a href="<?php echo esc_url( fixit_page_url( 'template-about.php' ) ); ?>"><?php esc_html_e( 'Haqqımızda', 'fixit' ); ?></a></li>
					<li><a href="<?php echo esc_url( fixit_page_url( 'template-contact.php' ) ); ?>"><?php esc_html_e( 'Əlaqə', 'fixit' ); ?></a></li>
				</ul>
			</div>

			<!-- Əlaqə -->
			<div>
				<h4><?php esc_html_e( 'Əlaqə', 'fixit' ); ?></h4>
				<ul class="footer__contact">
					<?php if ( $fixit_phone ) : ?>
						<li><?php fixit_icon( 'phone' ); ?><a href="tel:<?php echo esc_attr( fixit_tel( $fixit_phone ) ); ?>"><?php echo esc_html( $fixit_phone ); ?></a></li>
					<?php endif; ?>
					<?php if ( $fixit_email ) : ?>
						<li><?php fixit_icon( 'mail' ); ?><a href="mailto:<?php echo esc_attr( $fixit_email ); ?>"><?php echo esc_html( $fixit_email ); ?></a></li>
					<?php endif; ?>
					<li><?php fixit_icon( 'pin' ); ?><span><?php echo esc_html( fixit_get( 'fixit_address_main' ) ); ?></span></li>
					<li><?php fixit_icon( 'pin' ); ?><span><?php echo esc_html( fixit_get( 'fixit_address_br' ) ); ?></span></li>
					<li><?php fixit_icon( 'clock' ); ?><span><?php echo esc_html( fixit_get( 'fixit_hours_main' ) ); ?></span></li>
				</ul>
			</div>
		</div>

		<div class="footer__bottom">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> FIXIT. <?php esc_html_e( 'Bütün hüquqlar qorunur.', 'fixit' ); ?></span>
			<span><?php esc_html_e( 'Bakı · Cəlilabad', 'fixit' ); ?></span>
		</div>
	</div>
</footer>

<!-- Üzən düymələr -->
<div class="fab">
	<?php if ( $fixit_whatsapp ) : ?>
		<a class="fab__wa" href="https://wa.me/<?php echo esc_attr( fixit_tel( $fixit_whatsapp ) ); ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
			<?php fixit_icon( 'whatsapp' ); ?>
		</a>
	<?php endif; ?>
	<?php if ( $fixit_phone ) : ?>
		<a class="fab__call" href="tel:<?php echo esc_attr( fixit_tel( $fixit_phone ) ); ?>" aria-label="<?php esc_attr_e( 'Zəng et', 'fixit' ); ?>">
			<?php fixit_icon( 'phone' ); ?>
		</a>
	<?php endif; ?>
	<button class="to-top" type="button" aria-label="<?php esc_attr_e( 'Yuxarı qalx', 'fixit' ); ?>">
		<?php fixit_icon( 'arrow-up' ); ?>
	</button>
</div>

<?php wp_footer(); ?>
</body>
</html>
