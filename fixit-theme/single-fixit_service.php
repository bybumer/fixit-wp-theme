<?php
/**
 * Xidmət səhifəsi: /service/<slug>/
 *
 * Bazadakı mətn, üstünlüklər və teqlər tərcümə ilə göstərilir, üstəlik iş
 * qaydası, sual-cavab (inc/faq.php → fixit_faq_service) və digər xidmətlər.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$fixit_svc          = get_queried_object();
$fixit_icon_key     = get_post_meta( $fixit_svc->ID, '_fixit_icon', true );
$fixit_features     = fixit_t_features( $fixit_svc );
$fixit_chips        = fixit_t_chips( $fixit_svc );
$fixit_short        = fixit_t_short( $fixit_svc );
$fixit_body         = trim( (string) fixit_t_content( $fixit_svc ) );
$fixit_services_url = fixit_page_url( 'template-services.php' );
$fixit_contact_url  = add_query_arg( 'product', rawurlencode( $fixit_svc->post_title ), fixit_page_url( 'template-contact.php' ) ) . '#contact-form';
$fixit_faqs         = fixit_faq_service( $fixit_svc->post_name );
$fixit_others       = array_filter(
	fixit_get_services(),
	function ( $s ) use ( $fixit_svc ) {
		return $s->ID !== $fixit_svc->ID;
	}
);
?>

<section class="page-hero">
	<div class="container">
		<span class="eyebrow"><?php fixit_icon( $fixit_icon_key ? $fixit_icon_key : 'shield' ); ?><?php esc_html_e( 'İKT Xidmətləri', 'fixit' ); ?></span>
		<h1><?php echo esc_html( fixit_t_title( $fixit_svc ) ); ?></h1>
		<?php if ( $fixit_short ) : ?>
			<p><?php echo esc_html( wp_strip_all_tags( $fixit_short ) ); ?></p>
		<?php endif; ?>

		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Naviqasiya', 'fixit' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ana səhifə', 'fixit' ); ?></a>
			<span>/</span>
			<a href="<?php echo esc_url( $fixit_services_url ); ?>"><?php esc_html_e( 'Xidmətlər', 'fixit' ); ?></a>
			<span>/</span>
			<span><?php echo esc_html( fixit_t_title( $fixit_svc ) ); ?></span>
		</nav>
	</div>
</section>

<section class="section">
	<div class="container">
		<article class="svc">
			<div class="svc__icon"><?php fixit_icon( $fixit_icon_key ? $fixit_icon_key : 'shield' ); ?></div>

			<div>
				<h2><?php esc_html_e( 'Xidmət haqqında', 'fixit' ); ?></h2>

				<?php if ( '' !== $fixit_body ) : ?>
					<div class="entry"><?php echo wp_kses_post( wpautop( $fixit_body ) ); ?></div>
				<?php endif; ?>

				<?php if ( $fixit_features ) : ?>
					<h3 class="svc__sub"><?php esc_html_e( 'Nələr daxildir', 'fixit' ); ?></h3>
					<ul class="bullets">
						<?php foreach ( $fixit_features as $fixit_feature ) : ?>
							<li><?php fixit_icon( 'check-c' ); ?><span><?php echo esc_html( $fixit_feature ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( $fixit_chips ) : ?>
					<div class="chips">
						<?php foreach ( $fixit_chips as $fixit_chip ) : ?>
							<span class="chip"><?php echo esc_html( $fixit_chip ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div style="margin-top:22px">
					<a class="btn btn--primary" href="<?php echo esc_url( $fixit_contact_url ); ?>">
						<?php fixit_icon( 'send' ); ?><?php esc_html_e( 'Bu xidmət üçün müraciət', 'fixit' ); ?>
					</a>
				</div>
			</div>
		</article>
	</div>
</section>

<section class="section section--soft">
	<div class="container">
		<div class="section-head section-head--center">
			<h2><?php esc_html_e( 'Necə işləyirik?', 'fixit' ); ?></h2>
		</div>

		<div class="steps">
			<?php
			$fixit_steps = array(
				array( __( 'Müraciət', 'fixit' ), __( 'Zəng, mesaj və ya formu doldurursunuz. Ehtiyacınızı dinləyirik.', 'fixit' ) ),
				array( __( 'Analiz', 'fixit' ), __( 'Mövcud infrastruktur pulsuz yoxlanılır, problemlər müəyyən edilir.', 'fixit' ) ),
				array( __( 'Həll', 'fixit' ), __( 'Uyğun texniki həll və dəqiq smeta təqdim olunur, iş icra edilir.', 'fixit' ) ),
				array( __( 'Dəstək', 'fixit' ), __( 'Quraşdırmadan sonra da yanınızdayıq: monitorinq və müntəzəm baxış.', 'fixit' ) ),
			);
			$fixit_n     = 0;
			foreach ( $fixit_steps as $fixit_step ) :
				++$fixit_n;
				?>
				<div class="step">
					<div class="step__num"><?php echo esc_html( sprintf( '%02d', $fixit_n ) ); ?></div>
					<h3><?php echo esc_html( $fixit_step[0] ); ?></h3>
					<p><?php echo esc_html( $fixit_step[1] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php if ( $fixit_faqs ) : ?>
	<section class="section">
		<div class="container">
			<div class="section-head section-head--center">
				<h2><?php esc_html_e( 'Tez-tez verilən suallar', 'fixit' ); ?></h2>
			</div>
			<?php fixit_render_faq( $fixit_faqs ); ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( $fixit_others ) : ?>
	<section class="section section--soft">
		<div class="container">
			<div class="section-head section-head--center">
				<h2><?php esc_html_e( 'Digər xidmətlərimiz', 'fixit' ); ?></h2>
			</div>

			<div class="grid grid--3">
				<?php foreach ( $fixit_others as $fixit_other ) : ?>
					<?php $fixit_other_icon = get_post_meta( $fixit_other->ID, '_fixit_icon', true ); ?>
					<a class="card" href="<?php echo esc_url( get_permalink( $fixit_other ) ); ?>">
						<span class="card__icon"><?php fixit_icon( $fixit_other_icon ? $fixit_other_icon : 'shield' ); ?></span>
						<h3><?php echo esc_html( fixit_t_title( $fixit_other ) ); ?></h3>
						<p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( fixit_t_short( $fixit_other ) ), 16, '…' ) ); ?></p>
						<span class="card__link"><?php esc_html_e( 'Ətraflı', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<section class="section">
	<div class="container">
		<div class="cta">
			<div>
				<h2><?php esc_html_e( 'Hansı xidmətin sizə uyğun olduğunu bilmirsiniz?', 'fixit' ); ?></h2>
				<p><?php esc_html_e( 'Ehtiyacınızı danışın — mütəxəssisimiz ən sərfəli və dayanıqlı həlli təklif etsin.', 'fixit' ); ?></p>
			</div>
			<div class="cta__actions">
				<a class="btn btn--white" href="tel:<?php echo esc_attr( fixit_tel( fixit_get( 'fixit_phone' ) ) ); ?>">
					<?php fixit_icon( 'phone' ); ?><?php echo esc_html( fixit_get( 'fixit_phone' ) ); ?>
				</a>
				<a class="btn btn--light" href="<?php echo esc_url( $fixit_contact_url ); ?>">
					<?php esc_html_e( 'Əlaqə formu', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
