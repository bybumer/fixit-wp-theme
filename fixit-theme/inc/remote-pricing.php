<?php
/**
 * Fixit Remote: qiymət cədvəli.
 *
 * Model (bazarın RMM tərəfi belə satır, Fixit Remote-un lisenziyası da
 * yalnız agent sayını məhdudlaşdırır):
 * - qiymət idarə olunan kompüterin (agentin) sayına görədir, operator sayı
 *   və eyni anda sessiya sayı məhdud deyil;
 * - paketlər: 25, 50, 100, 250, 500 kompüter; kompüter başına qiymət həcm
 *   artdıqca düşür; ödəniş illikdir, aylıq rəqəm yalnız müqayisə üçündür;
 * - paketdən artıq kompüter 10-luq əlavə ilə həmin paketin qiymətinə alınır,
 *   ona görə qiymət paketlər arasında sıçrayış etmir (101 kompüter 100-dən
 *   ucuz olmur); kalkulyator ən sərfəli variantı özü seçir;
 * - bütün imkanlar hər paketdədir, paketlər dəstək səviyyəsi ilə fərqlənir.
 *
 * Rəqəmlər admin paneldə dəyişir: Fixit Remote → Qiymətlər və sınaq.
 * Qiymətlər saytda yalnız «Qiymətləri saytda göstər» seçiləndən sonra
 * görünür; o vaxta qədər paketlər «qiymət sorğu ilə» kimi göstərilir.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paketlərin adları (tərcümə olunur).
 */
function fixit_pricing_tier_names() {
	return array(
		'start'      => __( 'Start', 'fixit' ),
		'biznes'     => __( 'Biznes', 'fixit' ),
		'pesekar'    => __( 'Peşəkar', 'fixit' ),
		'korporativ' => __( 'Korporativ', 'fixit' ),
		'muessise'   => __( 'Müəssisə', 'fixit' ),
	);
}

/**
 * Hər paketin əvvəlkindən əlavə verdiyi (dəstək səviyyəsi).
 */
function fixit_pricing_tier_extras() {
	return array(
		'start'      => array(
			__( 'E-poçt ilə dəstək, iş günləri', 'fixit' ),
		),
		'biznes'     => array(
			__( 'E-poçt və telefonla dəstək', 'fixit' ),
			__( 'Uzaqdan quraşdırma köməyi', 'fixit' ),
		),
		'pesekar'    => array(
			__( 'Quraşdırma və SSO / LDAP qoşulması mütəxəssisimizlə', 'fixit' ),
			__( 'İT komandanız üçün onlayn təlim', 'fixit' ),
			__( 'Prioritet dəstək', 'fixit' ),
		),
		'korporativ' => array(
			__( 'Tam quraşdırma, ehtiyat nüsxə və bərpa planı', 'fixit' ),
			__( 'Filial serverləri üçün ayrıca lisenziya açarları', 'fixit' ),
		),
		'muessise'   => array(
			__( 'Şəxsi menecer və rüblük texniki baxış', 'fixit' ),
			__( 'Yazılı SLA: cavab və həll müddətləri', 'fixit' ),
		),
	);
}

/**
 * Defolt rəqəmlər (sahib təsdiq edənə qədər saytda görünmür).
 */
function fixit_pricing_defaults() {
	return array(
		'show'   => false,
		'multi2' => 10,
		'multi3' => 15,
		'tiers'  => array(
			array(
				'key'   => 'start',
				'max'   => 25,
				'price' => 3.5,
			),
			array(
				'key'   => 'biznes',
				'max'   => 50,
				'price' => 3.25,
			),
			array(
				'key'   => 'pesekar',
				'max'   => 100,
				'price' => 3.0,
			),
			array(
				'key'   => 'korporativ',
				'max'   => 250,
				'price' => 2.5,
			),
			array(
				'key'   => 'muessise',
				'max'   => 500,
				'price' => 2.0,
			),
		),
	);
}

/**
 * Qüvvədə olan qiymətlər (admin ayarı + defolt).
 *
 * @return array{show:bool,multi2:int,multi3:int,currency:string,step:int,tiers:array}
 */
function fixit_pricing() {
	$defaults = fixit_pricing_defaults();
	$saved    = get_option( 'fixit_remote_pricing', array() );
	$saved    = is_array( $saved ) ? $saved : array();
	$data     = wp_parse_args( $saved, $defaults );

	$tiers = array();
	$prev  = 0;
	foreach ( $defaults['tiers'] as $i => $def ) {
		$row   = isset( $data['tiers'][ $i ] ) && is_array( $data['tiers'][ $i ] ) ? $data['tiers'][ $i ] : array();
		$max   = max( $prev + 1, (int) ( isset( $row['max'] ) ? $row['max'] : $def['max'] ) );
		$price = max( 0, round( (float) ( isset( $row['price'] ) ? $row['price'] : $def['price'] ), 2 ) );

		$tiers[] = array(
			'key'   => $def['key'],
			'max'   => $max,
			'price' => $price,
			'year'  => round( $max * $price * 12, 2 ),
		);
		$prev = $max;
	}

	return array(
		'show'     => (bool) $data['show'],
		'multi2'   => min( 50, max( 0, (int) $data['multi2'] ) ),
		'multi3'   => min( 50, max( 0, (int) $data['multi3'] ) ),
		'currency' => 'AZN',
		'step'     => 10,
		'tiers'    => $tiers,
	);
}

/**
 * Rəqəmin ayırıcıları saytın dilinə görə.
 *
 * WordPress-in öz formatı admin dilindən asılıdır (canlıda başqa dil ola
 * bilər), ona görə burada açıq verilir: AZ və RU — "1 050,50", EN — "1,050.50".
 *
 * @return array{0:string,1:string} [onluq, minlik]
 */
function fixit_pricing_separators() {
	$lang = function_exists( 'fixit_lang' ) ? fixit_lang() : 'az';
	return 'en' === $lang ? array( '.', ',' ) : array( ',', "\u{00A0}" );
}

/**
 * Pul məbləği: "1 050 AZN", "3,50 AZN".
 *
 * @param float $amount Məbləğ.
 */
function fixit_pricing_money( $amount ) {
	$decimals = ( abs( $amount - round( $amount ) ) < 0.005 ) ? 0 : 2;
	list( $dec, $thousands ) = fixit_pricing_separators();
	return number_format( (float) $amount, $decimals, $dec, $thousands ) . "\u{00A0}AZN";
}

/**
 * Agent sayı üçün ən sərfəli variant (sayt kalkulyatoru ilə eyni hesab).
 *
 * @param int $agents Kompüter sayı.
 * @return array{tier:array,extra:int,year:float}|null null — fərdi təklif.
 */
function fixit_pricing_quote( $agents ) {
	$p    = fixit_pricing();
	$last = end( $p['tiers'] );
	if ( $agents > $last['max'] ) {
		return null;
	}

	$best = null;
	foreach ( $p['tiers'] as $tier ) {
		$extra = max( 0, (int) ceil( ( $agents - $tier['max'] ) / $p['step'] ) * $p['step'] );
		$year  = round( ( $tier['max'] + $extra ) * $tier['price'] * 12, 2 );
		if ( null === $best || $year < $best['year'] ) {
			$best = array(
				'tier'  => $tier,
				'extra' => $extra,
				'year'  => $year,
			);
		}
	}
	return $best;
}

/**
 * Bütün paketlərə daxil olanlar.
 */
function fixit_pricing_includes() {
	return array(
		__( 'Bütün imkanlar hər paketdə: uzaqdan dəstək, inventar, skriptlər, proqram yayımı, monitorinq, SSO və API', 'fixit' ),
		__( 'Operator sayı məhdud deyil, eyni anda istənilən qədər sessiya', 'fixit' ),
		__( 'Server sizdə qurulur, məlumat şirkətdən çıxmır', 'fixit' ),
		__( 'Lisenziya müddətində bütün yeni versiyalar', 'fixit' ),
		__( 'Windows və Linux kompüterləri eyni qaydada sayılır', 'fixit' ),
		__( 'Kompüter silinəndə və ya bloklananda yeri boşalır', 'fixit' ),
	);
}

/**
 * Qiymət bölməsi (Fixit Remote məhsul səhifəsində).
 */
function fixit_pricing_section() {
	$p      = fixit_pricing();
	$names  = fixit_pricing_tier_names();
	$extras = fixit_pricing_tier_extras();
	$last   = end( $p['tiers'] );
	$prev   = '';
	?>
	<section class="section section--soft" id="qiymetler">
		<div class="container">
			<div class="section-head section-head--center reveal">
				<span class="eyebrow"><?php fixit_icon( 'tag' ); ?><?php esc_html_e( 'Qiymətlər', 'fixit' ); ?></span>
				<h2><?php esc_html_e( 'Kompüter sayına görə ödəyirsiniz, operator sayına görə yox', 'fixit' ); ?></h2>
				<p><?php esc_html_e( 'Qiymət idarə olunan kompüterlərin sayından asılıdır. Komandanızın hamısı işləyə bilər, bütün imkanlar hər paketdədir. Ödəniş illikdir.', 'fixit' ); ?></p>
			</div>

			<div class="price-grid reveal">
				<?php foreach ( $p['tiers'] as $i => $tier ) : ?>
					<?php
					$featured = ( 'pesekar' === $tier['key'] );
					$name     = isset( $names[ $tier['key'] ] ) ? $names[ $tier['key'] ] : $tier['key'];
					?>
					<div class="price-card<?php echo $featured ? ' price-card--featured' : ''; ?>">
						<?php if ( $featured ) : ?>
							<span class="price-card__badge"><?php esc_html_e( 'Ən çox seçilən', 'fixit' ); ?></span>
						<?php endif; ?>
						<h3><?php echo esc_html( $name ); ?></h3>
						<p class="price-card__range">
							<?php
							/* translators: %s: kompüter sayı */
							echo esc_html( sprintf( __( '%s kompüterə qədər', 'fixit' ), number_format_i18n( $tier['max'] ) ) );
							?>
						</p>

						<?php if ( $p['show'] ) : ?>
							<p class="price-card__price">
								<b><?php echo esc_html( fixit_pricing_money( $tier['year'] ) ); ?></b>
								<span><?php esc_html_e( 'ildə', 'fixit' ); ?></span>
							</p>
							<p class="price-card__unit">
								<?php
								/* translators: %s: qiymət */
								echo esc_html( sprintf( __( 'ayda kompüter başına %s', 'fixit' ), fixit_pricing_money( $tier['price'] ) ) );
								?>
							</p>

							<ul class="price-card__list">
								<?php if ( $prev ) : ?>
									<li class="price-card__inherit">
										<?php
										/* translators: %s: əvvəlki paketin adı */
										echo esc_html( sprintf( __( '%s paketindəki hər şey', 'fixit' ), $prev ) );
										?>
									</li>
								<?php endif; ?>
								<?php foreach ( isset( $extras[ $tier['key'] ] ) ? $extras[ $tier['key'] ] : array() as $line ) : ?>
									<li><?php fixit_icon( 'check' ); ?><span><?php echo esc_html( $line ); ?></span></li>
								<?php endforeach; ?>
								<?php if ( $tier !== $last ) : ?>
									<li><?php fixit_icon( 'check' ); ?><span>
										<?php
										/* translators: 1: say, 2: qiymət */
										echo esc_html( sprintf( __( 'Hər əlavə %1$s kompüter: ildə %2$s', 'fixit' ), $p['step'], fixit_pricing_money( $p['step'] * $tier['price'] * 12 ) ) );
										?>
									</span></li>
								<?php endif; ?>
							</ul>
						<?php else : ?>
							<p class="price-card__price price-card__price--ask">
								<b><?php esc_html_e( 'Qiymət sorğu ilə', 'fixit' ); ?></b>
								<span><?php esc_html_e( 'təklifi sınaqla birlikdə göndəririk', 'fixit' ); ?></span>
							</p>
						<?php endif; ?>

						<a class="btn <?php echo $featured ? 'btn--primary' : 'btn--ghost'; ?> btn--block" href="#sinaq"><?php esc_html_e( '14 gün pulsuz sınayın', 'fixit' ); ?></a>
					</div>
					<?php $prev = $name; ?>
				<?php endforeach; ?>

				<div class="price-card price-card--custom">
					<h3><?php esc_html_e( 'Fərdi təklif', 'fixit' ); ?></h3>
					<p class="price-card__range">
						<?php
						/* translators: %s: kompüter sayı */
						echo esc_html( sprintf( __( '%s kompüterdən çox', 'fixit' ), number_format_i18n( $last['max'] ) ) );
						?>
					</p>
					<p class="price-card__price price-card__price--ask">
						<b><?php esc_html_e( 'Böyük parklar üçün', 'fixit' ); ?></b>
						<span><?php esc_html_e( 'həcmə görə qiymət, çoxillik müqavilə, bir neçə filial', 'fixit' ); ?></span>
					</p>
					<a class="btn btn--ghost btn--block" href="<?php echo esc_url( add_query_arg( 'mehsul', 'Fixit Remote', fixit_page_url( 'template-contact.php' ) ) ); ?>"><?php esc_html_e( 'Bizimlə əlaqə', 'fixit' ); ?></a>
				</div>
			</div>

			<?php if ( $p['show'] ) : ?>
				<div class="price-calc reveal" data-price-calc
					data-tiers="<?php echo esc_attr( wp_json_encode( $p['tiers'] ) ); ?>"
					data-names="<?php echo esc_attr( wp_json_encode( $names ) ); ?>"
					data-step="<?php echo esc_attr( $p['step'] ); ?>"
					data-sep="<?php echo esc_attr( wp_json_encode( fixit_pricing_separators() ) ); ?>"
					data-tpl="<?php echo esc_attr__( '{n} kompüter: {tier} paketi{extra}. İldə {year}, ayda təxminən {month}.', 'fixit' ); ?>"
					data-tpl-extra="<?php echo esc_attr__( ' və {extra} əlavə kompüter', 'fixit' ); ?>"
					data-tpl-custom="<?php echo esc_attr__( '{n} kompüter üçün fərdi təklif hazırlayırıq: bizimlə əlaqə saxlayın.', 'fixit' ); ?>">
					<label for="price-calc-n"><?php esc_html_e( 'Neçə kompüteriniz var?', 'fixit' ); ?></label>
					<div class="price-calc__row">
						<input type="range" id="price-calc-range" min="1" max="<?php echo esc_attr( $last['max'] + 100 ); ?>" value="100" aria-label="<?php esc_attr_e( 'Kompüter sayı', 'fixit' ); ?>">
						<input type="number" id="price-calc-n" min="1" max="100000" value="100" inputmode="numeric">
					</div>
					<p class="price-calc__out" aria-live="polite"></p>
				</div>
			<?php endif; ?>

			<ul class="price-includes reveal">
				<?php foreach ( fixit_pricing_includes() as $line ) : ?>
					<li><?php fixit_icon( 'check' ); ?><span><?php echo esc_html( $line ); ?></span></li>
				<?php endforeach; ?>
			</ul>

			<?php if ( $p['show'] && ( $p['multi2'] || $p['multi3'] ) ) : ?>
				<p class="price-foot">
					<?php
					/* translators: 1: 2 illik endirim, 2: 3 illik endirim */
					echo esc_html( sprintf( __( '2 illik müqavilədə %1$s%%, 3 illikdə %2$s%% endirim.', 'fixit' ), number_format_i18n( $p['multi2'] ), number_format_i18n( $p['multi3'] ) ) );
					?>
				</p>
			<?php endif; ?>

			<div class="price-license reveal">
				<div>
					<h3><?php esc_html_e( 'Lisenziya almaq istəyirsiniz?', 'fixit' ); ?></h3>
					<p><?php esc_html_e( 'Lisenziya müqavilə əsasında rəsmiləşdirilir, ödəniş bank köçürməsi ilə. Bizə yazın: kompüter sayınıza görə müqaviləni və hesab-fakturanı hazırlayırıq, ödənişdən sonra lisenziya açarını göndəririk.', 'fixit' ); ?></p>
				</div>
				<a class="btn btn--primary" href="<?php echo esc_url( add_query_arg( 'mehsul', 'Fixit Remote', fixit_page_url( 'template-contact.php' ) ) . '#elaqe-form' ); ?>"><?php esc_html_e( 'Lisenziya üçün müraciət', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?></a>
			</div>
		</div>
	</section>
	<?php
}
