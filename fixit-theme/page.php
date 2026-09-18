<?php
/**
 * Standart səhifə şablonu.
 *
 * @package FIXIT
 */

get_header();
?>

<section class="page-hero">
	<div class="container">
		<h1><?php the_title(); ?></h1>
		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Naviqasiya', 'fixit' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ana səhifə', 'fixit' ); ?></a>
			<span>/</span>
			<span><?php the_title(); ?></span>
		</nav>
	</div>
</section>

<section class="section">
	<div class="container entry">
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();

			wp_link_pages(
				array(
					'before' => '<div class="pagination">',
					'after'  => '</div>',
				)
			);
		endwhile;
		?>
	</div>
</section>

<?php
get_footer();
