<?php
/**
 * Tək yazı şablonu.
 *
 * @package FIXIT
 */

get_header();
?>

<section class="page-hero">
	<div class="container">
		<h1><?php the_title(); ?></h1>
		<p><?php echo esc_html( get_the_date() ); ?></p>
	</div>
</section>

<section class="section">
	<div class="container entry">
		<?php
		while ( have_posts() ) :
			the_post();

			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'large' );
			}

			the_content();

			wp_link_pages(
				array(
					'before' => '<div class="pagination">',
					'after'  => '</div>',
				)
			);

			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</div>
</section>

<?php
get_footer();
