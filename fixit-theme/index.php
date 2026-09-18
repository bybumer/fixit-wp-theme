<?php
/**
 * Ümumi siyahı şablonu (blog, arxiv, axtarış nəticələri).
 *
 * @package FIXIT
 */

get_header();
?>

<section class="page-hero">
	<div class="container">
		<h1>
			<?php
			if ( is_search() ) {
				/* translators: %s: axtarış sorğusu */
				printf( esc_html__( 'Axtarış: %s', 'fixit' ), esc_html( get_search_query() ) );
			} elseif ( is_archive() ) {
				the_archive_title();
			} else {
				echo esc_html( get_the_title( get_option( 'page_for_posts' ) ) ? get_the_title( get_option( 'page_for_posts' ) ) : __( 'Bloq', 'fixit' ) );
			}
			?>
		</h1>

		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Naviqasiya', 'fixit' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ana səhifə', 'fixit' ); ?></a>
			<span>/</span>
			<span><?php esc_html_e( 'Yazılar', 'fixit' ); ?></span>
		</nav>
	</div>
</section>

<section class="section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="grid grid--3">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article class="card reveal">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="post-card__thumb" style="margin-bottom:16px">
								<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium_large' ); ?></a>
							</div>
						<?php endif; ?>

						<div class="post-meta"><?php echo esc_html( get_the_date() ); ?></div>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>
						<span class="card__link"><?php esc_html_e( 'Oxu', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?></span>
					</article>
					<?php
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination(
				array(
					'class'     => 'pagination',
					'mid_size'  => 2,
					'prev_text' => __( 'Əvvəlki', 'fixit' ),
					'next_text' => __( 'Növbəti', 'fixit' ),
				)
			);
			?>

		<?php else : ?>
			<div class="entry" style="text-align:center">
				<h2><?php esc_html_e( 'Nəticə tapılmadı', 'fixit' ); ?></h2>
				<p><?php esc_html_e( 'Başqa açar sözlə yenidən cəhd edin.', 'fixit' ); ?></p>
				<?php get_search_form(); ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
