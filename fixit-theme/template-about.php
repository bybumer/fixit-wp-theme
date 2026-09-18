<?php
/**
 * Template Name: Haqqımızda səhifəsi
 *
 * @package FIXIT
 */

get_header();

$fixit_contact_url = fixit_page_url( 'template-contact.php' );
?>

<section class="page-hero">
	<div class="container">
		<span class="eyebrow"><?php fixit_icon( 'users' ); ?><?php esc_html_e( 'Haqqımızda', 'fixit' ); ?></span>
		<h1><?php esc_html_e( 'Texnologiyada etibarlı tərəfdaşınız', 'fixit' ); ?></h1>
		<p><?php esc_html_e( '2017-ci ildən bəri Azərbaycanda müəssisələrin İT infrastrukturunu qururuq, qoruyuruq və inkişaf etdiririk.', 'fixit' ); ?></p>

		<nav class="crumbs" aria-label="<?php esc_attr_e( 'Naviqasiya', 'fixit' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ana səhifə', 'fixit' ); ?></a>
			<span>/</span>
			<span><?php the_title(); ?></span>
		</nav>
	</div>
</section>

<!-- Hekayə + statistika -->
<section class="section">
	<div class="container split">
		<div class="reveal">
			<span class="eyebrow"><?php esc_html_e( 'Kimik?', 'fixit' ); ?></span>
			<h2 style="font-size:clamp(1.7rem,3.2vw,2.4rem);margin:16px 0 18px">
				<?php esc_html_e( 'Sizin uğurunuz', 'fixit' ); ?> <span class="grad-text"><?php esc_html_e( 'bizim ən böyük nailiyyətimizdir', 'fixit' ); ?></span>
			</h2>

			<div style="display:grid;gap:16px;color:var(--text-soft)">
				<p><?php esc_html_e( 'FixIT olaraq, 2017-ci ildən etibarən informasiya texnologiyaları sahəsində peşəkar həllər təqdim edən etibarlı tərəfdaşıq. Təcrübəli və beynəlxalq sertifikatlı mühəndislərdən ibarət komandamız müştərilərimizin texnoloji tələblərini qarşılamaq üçün daim yeniliklər tətbiq edir və innovativ yanaşmalar təklif edir.', 'fixit' ); ?></p>
				<p><?php esc_html_e( 'Fəaliyyət göstərdiyimiz müddətdə 40-dan çox şirkətlə uğurla əməkdaşlıq etmiş, 30-dan çox layihəni yüksək keyfiyyətlə tamamlamışıq. Xidmətlərimiz arasında İT dəstək, təhlükəsizlik sistemləri, bulud həlləri, server idarəetməsi, video müşahidə sistemləri və bir çox digər texnoloji həllər yer alır.', 'fixit' ); ?></p>
				<p><?php esc_html_e( 'FixIT-in əsas məqsədi müştərilərimizə davamlı, təhlükəsiz və effektiv texnoloji həllər təqdim edərək onların biznesinin rəqabət üstünlüyünü artırmaqdır.', 'fixit' ); ?></p>
			</div>

			<div style="margin-top:30px;display:flex;gap:12px;flex-wrap:wrap">
				<a class="btn btn--primary" href="<?php echo esc_url( $fixit_contact_url ); ?>">
					<?php fixit_icon( 'send' ); ?><?php esc_html_e( 'Bizimlə əlaqə', 'fixit' ); ?>
				</a>
				<a class="btn btn--ghost" href="<?php echo esc_url( fixit_page_url( 'template-services.php' ) ); ?>">
					<?php esc_html_e( 'Xidmətlərimiz', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?>
				</a>
			</div>
		</div>

		<div class="stat-grid reveal">
			<div class="stat">
				<b><?php echo esc_html( fixit_get( 'fixit_year_since' ) ); ?></b>
				<span><?php esc_html_e( 'fəaliyyətə başlama ili', 'fixit' ); ?></span>
			</div>
			<div class="stat">
				<b><?php echo esc_html( fixit_get( 'fixit_stat_clients' ) ); ?></b>
				<span><?php esc_html_e( 'əməkdaşlıq etdiyimiz şirkət', 'fixit' ); ?></span>
			</div>
			<div class="stat">
				<b><?php echo esc_html( fixit_get( 'fixit_stat_project' ) ); ?></b>
				<span><?php esc_html_e( 'tamamlanmış layihə', 'fixit' ); ?></span>
			</div>
			<div class="stat">
				<b><?php echo esc_html( fixit_get( 'fixit_stat_support' ) ); ?></b>
				<span><?php esc_html_e( 'monitorinq və dəstək', 'fixit' ); ?></span>
			</div>
		</div>
	</div>
</section>

<!-- Dəyərlərimiz -->
<section class="section section--soft">
	<div class="container">
		<div class="section-head section-head--center reveal">
			<span class="eyebrow"><?php esc_html_e( 'Dəyərlərimiz', 'fixit' ); ?></span>
			<h2><?php esc_html_e( 'İşimizin əsasında duran prinsiplər', 'fixit' ); ?></h2>
		</div>

		<div class="grid grid--4">
			<?php
			$fixit_values = array(
				array( 'shield', __( 'Etibarlılıq', 'fixit' ), __( 'Verdiyimiz sözü vaxtında və tam yerinə yetiririk.', 'fixit' ) ),
				array( 'zap', __( 'Operativlik', 'fixit' ), __( 'Problem böyüməmiş müdaxilə edirik — dayanma vaxtı minimum olur.', 'fixit' ) ),
				array( 'lock', __( 'Təhlükəsizlik', 'fixit' ), __( 'Məlumatlarınızın qorunması hər layihədə birinci prioritetdir.', 'fixit' ) ),
				array( 'chart', __( 'Səmərəlilik', 'fixit' ), __( 'İT xərclərini analiz edib optimallaşdırırıq, artıq xərcə yol vermirik.', 'fixit' ) ),
			);
			foreach ( $fixit_values as $fixit_value ) :
				?>
				<div class="card reveal">
					<span class="card__icon"><?php fixit_icon( $fixit_value[0] ); ?></span>
					<h3><?php echo esc_html( $fixit_value[1] ); ?></h3>
					<p><?php echo esc_html( $fixit_value[2] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- Ofislər -->
<section class="section">
	<div class="container">
		<div class="section-head section-head--center reveal">
			<span class="eyebrow"><?php esc_html_e( 'Ofislərimiz', 'fixit' ); ?></span>
			<h2><?php esc_html_e( 'Bizi harada tapa bilərsiniz?', 'fixit' ); ?></h2>
		</div>

		<div class="grid grid--2">
			<div class="info-card reveal">
				<div class="info-row">
					<span class="ico"><?php fixit_icon( 'pin' ); ?></span>
					<div>
						<h4><?php esc_html_e( 'Baş ofis', 'fixit' ); ?></h4>
						<p><?php echo esc_html( fixit_get( 'fixit_address_main' ) ); ?></p>
						<small><?php echo esc_html( fixit_get( 'fixit_hours_main' ) ); ?></small>
					</div>
				</div>
			</div>

			<div class="info-card reveal">
				<div class="info-row">
					<span class="ico"><?php fixit_icon( 'pin' ); ?></span>
					<div>
						<h4><?php esc_html_e( 'Cəlilabad filialı', 'fixit' ); ?></h4>
						<p><?php echo esc_html( fixit_get( 'fixit_address_br' ) ); ?></p>
						<small><?php echo esc_html( fixit_get( 'fixit_hours_br' ) ); ?></small>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

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

<section class="section" style="padding-top:0">
	<div class="container">
		<div class="cta reveal">
			<div>
				<h2><?php esc_html_e( 'Komandamızla tanış olmaq istəyirsiniz?', 'fixit' ); ?></h2>
				<p><?php esc_html_e( 'Bir görüş təyin edək — infrastrukturunuzu birlikdə nəzərdən keçirək.', 'fixit' ); ?></p>
			</div>
			<div class="cta__actions">
				<a class="btn btn--white" href="<?php echo esc_url( $fixit_contact_url ); ?>">
					<?php fixit_icon( 'send' ); ?><?php esc_html_e( 'Əlaqə saxla', 'fixit' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
