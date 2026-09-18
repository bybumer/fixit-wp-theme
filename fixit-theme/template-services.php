<?php
/**
 * Template Name: Xidmətlər səhifəsi
 *
 * @package FIXIT
 */

get_header();

$fixit_services    = fixit_get_services();
$fixit_contact_url = fixit_page_url( 'template-contact.php' );
?>

<!-- Səhifə başlığı -->
<section class="page-hero">
	<div class="container">
		<span class="eyebrow"><?php fixit_icon( 'shield' ); ?><?php esc_html_e( 'İKT Xidmətləri', 'fixit' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<p><?php esc_html_e( 'Kiçik və böyük şirkətlər üçün müasir informasiya-kommunikasiya texnologiyaları xidmətləri təqdim edirik — quraşdırmadan daimi dəstəyə qədər.', 'fixit' ); ?></p>

		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Naviqasiya', 'fixit' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ana səhifə', 'fixit' ); ?></a>
			<span>/</span>
			<span><?php the_title(); ?></span>
		</nav>
	</div>
</section>

<!-- Sürətli keçid -->
<?php if ( $fixit_services ) : ?>
	<div class="marquee" style="padding:16px 0">
		<div class="container" style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center">
			<?php foreach ( $fixit_services as $fixit_svc ) : ?>
				<a class="chip" href="#xidmet-<?php echo esc_attr( $fixit_svc->ID ); ?>"><?php echo esc_html( $fixit_svc->post_title ); ?></a>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>

<!-- Detallı xidmətlər -->
<section class="section">
	<div class="container" style="display:grid;gap:26px">
		<?php foreach ( $fixit_services as $fixit_svc ) : ?>
			<?php
			$fixit_icon_key = get_post_meta( $fixit_svc->ID, '_fixit_icon', true );
			$fixit_features = fixit_meta_lines( $fixit_svc->ID, '_fixit_features' );
			$fixit_chips    = get_post_meta( $fixit_svc->ID, '_fixit_chips', true );
			$fixit_chips    = $fixit_chips ? array_filter( array_map( 'trim', explode( ',', $fixit_chips ) ) ) : array();
			?>
			<article class="svc reveal" id="xidmet-<?php echo esc_attr( $fixit_svc->ID ); ?>">
				<div class="svc__icon"><?php fixit_icon( $fixit_icon_key ? $fixit_icon_key : 'shield' ); ?></div>

				<div>
					<h2><?php echo esc_html( $fixit_svc->post_title ); ?></h2>

					<?php if ( $fixit_svc->post_content ) : ?>
						<p><?php echo esc_html( wp_strip_all_tags( $fixit_svc->post_content ) ); ?></p>
					<?php endif; ?>

					<?php if ( $fixit_features ) : ?>
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
		<?php endforeach; ?>
	</div>
</section>

<!-- Öz proqram məhsullarımız -->
<?php $fixit_strip_products = fixit_get_products(); ?>
<?php if ( $fixit_strip_products ) : ?>
	<section class="section section--dark">
		<div class="container">
			<div class="section-head reveal" style="margin-bottom:0">
				<span class="eyebrow"><?php fixit_icon( 'award' ); ?><?php esc_html_e( 'Öz məhsullarımız', 'fixit' ); ?></span>
				<h2><?php esc_html_e( 'Xidmətlə yanaşı — öz proqram təminatımız', 'fixit' ); ?></h2>
				<p><?php esc_html_e( 'Komandamızın hazırladığı sistemlər sizin serverinizdə işləyir. Hər biri üçün onlayn demo mövcuddur.', 'fixit' ); ?></p>
			</div>

			<div class="prod-strip">
				<?php foreach ( $fixit_strip_products as $fixit_sp ) : ?>
					<?php $fixit_sp_icon = get_post_meta( $fixit_sp->ID, '_fixit_icon', true ); ?>
					<a class="reveal" href="<?php echo esc_url( get_permalink( $fixit_sp ) ); ?>">
						<span class="card__icon"><?php fixit_icon( $fixit_sp_icon ? $fixit_sp_icon : 'server' ); ?></span>
						<span>
							<strong><?php echo esc_html( $fixit_sp->post_title ); ?></strong>
							<small><?php esc_html_e( 'Ətraflı və demo →', 'fixit' ); ?></small>
						</span>
					</a>
				<?php endforeach; ?>
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
?>

<!-- CTA -->
<section class="section" style="padding-top:0">
	<div class="container">
		<div class="cta reveal">
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
