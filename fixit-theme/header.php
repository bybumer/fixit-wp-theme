<?php
/**
 * Saytın başlıq hissəsi.
 *
 * @package FIXIT
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Əsas məzmuna keç', 'fixit' ); ?></a>

<?php
$fixit_phone    = fixit_get( 'fixit_phone' );
$fixit_email    = fixit_get( 'fixit_email' );
$fixit_socials  = array(
	'facebook'  => fixit_get( 'fixit_facebook' ),
	'instagram' => fixit_get( 'fixit_instagram' ),
	'tiktok'    => fixit_get( 'fixit_tiktok' ),
	'linkedin'  => fixit_get( 'fixit_linkedin' ),
);
?>

<!-- Üst zolaq -->
<div class="topbar">
	<div class="container topbar__inner">
		<div class="topbar__contact">
			<?php if ( $fixit_phone ) : ?>
				<a href="tel:<?php echo esc_attr( fixit_tel( $fixit_phone ) ); ?>">
					<?php fixit_icon( 'phone' ); ?><?php echo esc_html( $fixit_phone ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $fixit_email ) : ?>
				<a href="mailto:<?php echo esc_attr( $fixit_email ); ?>">
					<?php fixit_icon( 'mail' ); ?><?php echo esc_html( $fixit_email ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="topbar__socials">
			<?php foreach ( $fixit_socials as $fixit_net => $fixit_url ) : ?>
				<?php if ( $fixit_url ) : ?>
					<a href="<?php echo esc_url( $fixit_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( ucfirst( $fixit_net ) ); ?>">
						<?php fixit_icon( $fixit_net ); ?>
					</a>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<!-- Başlıq -->
<header class="header" id="site-header">
	<div class="container nav">

		<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php if ( has_custom_logo() ) : ?>
				<?php
				/*
				 * the_custom_logo() şəkli öz <a> teqinə sarır. Bu blok artıq
				 * <a class="logo"> içindədir, iç-içə <a> isə etibarsız HTML-dir:
				 * brauzer daxili linki bayıra çıxarır, <img> .logo-dan kənara
				 * düşür və `.logo img{max-height:46px}` qaydası işləmir —
				 * loqo tam ölçüsündə görünür.
				 *
				 * Ona görə şəkli birbaşa çap edirik.
				 */
				$logo_light = (int) get_theme_mod( 'custom_logo' );
				$logo_dark  = (int) get_theme_mod( 'fixit_logo_dark' );

				// Qaranlıq rejim üçün ayrı loqo varsa, ikisi də çap olunur və
				// CSS hansının görünəcəyini seçir. Yoxdursa, tək loqo qalır.
				$light_class = $logo_dark ? 'logo__img logo__img--light' : 'logo__img';

				echo wp_get_attachment_image(
					$logo_light,
					'full',
					false,
					array(
						'class' => $light_class,
						'alt'   => get_bloginfo( 'name' ),
					)
				);

				if ( $logo_dark ) {
					echo wp_get_attachment_image(
						$logo_dark,
						'full',
						false,
						array(
							'class' => 'logo__img logo__img--dark',
							'alt'   => '',
							'aria-hidden' => 'true',
						)
					);
				}
				?>
			<?php else : ?>
				<span class="logo__mark"><?php fixit_icon( 'shield' ); ?></span>
				<span>
					FIXIT
					<small class="logo__sub"><?php esc_html_e( 'İKT Həlləri', 'fixit' ); ?></small>
				</span>
			<?php endif; ?>
		</a>

		<nav class="nav__desktop" aria-label="<?php esc_attr_e( 'Əsas menyu', 'fixit' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'nav__links',
					'depth'          => 2,
					'fallback_cb'    => 'fixit_menu_fallback',
				)
			);
			?>
		</nav>

		<div class="nav__actions">
			<button class="icon-btn theme-toggle" type="button" aria-label="<?php esc_attr_e( 'İşıqlı/qaranlıq rejim', 'fixit' ); ?>">
				<span class="i-sun"><?php fixit_icon( 'sun' ); ?></span>
				<span class="i-moon"><?php fixit_icon( 'moon' ); ?></span>
			</button>

			<a class="btn btn--primary nav__cta" href="<?php echo esc_url( fixit_page_url( 'template-contact.php' ) ); ?>">
				<?php fixit_icon( 'send' ); ?><?php esc_html_e( 'Müraciət et', 'fixit' ); ?>
			</a>

			<button class="icon-btn burger" type="button" aria-label="<?php esc_attr_e( 'Menyu', 'fixit' ); ?>" aria-expanded="false">
				<?php fixit_icon( 'menu' ); ?>
			</button>
		</div>
	</div>
</header>

<!-- Mobil menyu -->
<div class="mobile-menu" id="mobile-menu" aria-hidden="true">
	<div class="mobile-menu__top">
		<span class="logo">
			<span class="logo__mark"><?php fixit_icon( 'shield' ); ?></span>
			<span>FIXIT</span>
		</span>
		<button class="icon-btn mobile-close" type="button" aria-label="<?php esc_attr_e( 'Bağla', 'fixit' ); ?>">
			<?php fixit_icon( 'close' ); ?>
		</button>
	</div>

	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'mobile-menu__list',
			'depth'          => 2,
			'fallback_cb'    => 'fixit_menu_fallback',
		)
	);
	?>

	<div class="mobile-menu__foot">
		<a class="btn btn--primary btn--block" href="tel:<?php echo esc_attr( fixit_tel( $fixit_phone ) ); ?>">
			<?php fixit_icon( 'phone' ); ?><?php echo esc_html( $fixit_phone ); ?>
		</a>
		<a class="btn btn--ghost btn--block" href="mailto:<?php echo esc_attr( $fixit_email ); ?>">
			<?php fixit_icon( 'mail' ); ?><?php echo esc_html( $fixit_email ); ?>
		</a>
	</div>
</div>

<main id="main">
