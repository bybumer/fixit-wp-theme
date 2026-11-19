<?php
/**
 * Fixit Remote: qiymət cədvəli.
 *
 * Model (bazarın RMM tərəfi belə satır, Fixit Remote-un lisenziyası da
 * yalnız agent sayını məhdudlaşdırır):
 * - qiymət idarə olunan kompüterin (agentin) sayına görədir, operator sayı
 *   və eyni anda sessiya sayı məhdud deyil;
 * - paketlər: 25, 50, 100, 250, 500 kompüter; kompüter başına qiymət həcm
 *   artdıqca düşür;
 * - ödəniş aylıq və ya illik ola bilər: aylıq məbləğ = kompüter sayı ×
 *   kompüter başına aylıq qiymət; illik məbləğ = aylıq × (12 − hədiyyə ay).
 *   Hədiyyə ay admin paneldə seçilir (standart 0: illik = 12 ay);
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
 * Paket kimin üçündür (kartda adın altında).
 */
function fixit_pricing_tier_audience() {
	return array(
		'start'      => __( 'Kiçik ofis üçün', 'fixit' ),
		'biznes'     => __( 'Böyüyən komanda üçün', 'fixit' ),
		'pesekar'    => __( 'Orta şirkət üçün', 'fixit' ),
		'korporativ' => __( 'Filialları olan şirkət üçün', 'fixit' ),
		'muessise'   => __( 'Böyük təşkilat üçün', 'fixit' ),
	);
}

/**
 * Paketlərin fərqləri (müqayisə cədvəli).
 *
 * Hər sətir ya hər paket üçün öz mətnini verir ('cells'), ya da göstərilən
 * paketdən ('from') başlayaraq yuxarı paketlərdə var.
 */
function fixit_pricing_compare() {
	$priority = __( 'Prioritet', 'fixit' );
	$full     = __( 'Tam quraşdırma', 'fixit' );

	return array(
		array(
			'label' => __( 'Dəstək', 'fixit' ),
			'cells' => array(
				'start'      => __( 'E-poçt, iş günləri', 'fixit' ),
				'biznes'     => __( 'E-poçt və telefon', 'fixit' ),
				'pesekar'    => $priority,
				'korporativ' => $priority,
				'muessise'   => $priority,
			),
		),
		array(
			'label' => __( 'Quraşdırma', 'fixit' ),
			'cells' => array(
				'start'      => __( 'Bələdçi ilə', 'fixit' ),
				'biznes'     => __( 'Uzaqdan kömək', 'fixit' ),
				'pesekar'    => __( 'Mütəxəssisimizlə', 'fixit' ),
				'korporativ' => $full,
				'muessise'   => $full,
			),
		),
		array(
			'label' => __( 'SSO / LDAP qoşulması mütəxəssisimizlə', 'fixit' ),
			'from'  => 'pesekar',
		),
		array(
			'label' => __( 'İT komandanız üçün onlayn təlim', 'fixit' ),
			'from'  => 'pesekar',
		),
		array(
			'label' => __( 'Ehtiyat nüsxə və bərpa planı', 'fixit' ),
			'from'  => 'korporativ',
		),
		array(
			'label' => __( 'Filial serverləri üçün ayrıca lisenziya açarları', 'fixit' ),
			'from'  => 'korporativ',
		),
		array(
			'label' => __( 'Şəxsi menecer və rüblük texniki baxış', 'fixit' ),
			'from'  => 'muessise',
		),
		array(
			'label' => __( 'Yazılı SLA: cavab və həll müddətləri', 'fixit' ),
			'from'  => 'muessise',
		),
	);
}

/**
 * Defolt rəqəmlər (sahib təsdiq edənə qədər saytda görünmür).
 */
function fixit_pricing_defaults() {
	return array(
		'show'   => false,
		'gift'   => 0,
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
 * @return array{show:bool,gift:int,months:int,save:int,multi2:int,multi3:int,currency:string,step:int,tiers:array}
 */
function fixit_pricing() {
	$defaults = fixit_pricing_defaults();
	$saved    = get_option( 'fixit_remote_pricing', array() );
	$saved    = is_array( $saved ) ? $saved : array();
	$data     = wp_parse_args( $saved, $defaults );

	$gift   = min( 3, max( 0, (int) $data['gift'] ) );
	$months = 12 - $gift;

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
			'month' => round( $max * $price, 2 ),
			'year'  => round( $max * $price * $months, 2 ),
		);
		$prev = $max;
	}

	return array(
		'show'     => (bool) $data['show'],
		'gift'     => $gift,
		'months'   => $months,
		'save'     => (int) round( $gift / 12 * 100 ),
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
 * @return array{tier:array,extra:int,month:float,year:float}|null null — fərdi təklif.
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
		$month = round( ( $tier['max'] + $extra ) * $tier['price'], 2 );
		if ( null === $best || $month < $best['month'] ) {
			$best = array(
				'tier'  => $tier,
				'extra' => $extra,
				'month' => $month,
				'year'  => round( $month * $p['months'], 2 ),
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
 * Məbləğ, aylıq və illik görünüşü ilə (seçim JS ilə dəyişir, JS olmasa aylıq).
 *
 * @param float  $month Aylıq məbləğ.
 * @param float  $year  İllik məbləğ.
 * @param string $class Əlavə sinif.
 */
function fixit_pricing_amount( $month, $year, $class = '' ) {
	?>
	<span class="price-amount <?php echo esc_attr( $class ); ?>">
		<span data-bill="month"><b><?php echo esc_html( fixit_pricing_money( $month ) ); ?></b><small><?php esc_html_e( 'ayda', 'fixit' ); ?></small></span>
		<span data-bill="year"><b><?php echo esc_html( fixit_pricing_money( $year ) ); ?></b><small><?php esc_html_e( 'ildə', 'fixit' ); ?></small></span>
	</span>
	<?php
}

/**
 * Qiymət bölməsi (Fixit Remote məhsul səhifəsində).
 */
function fixit_pricing_section() {
	$p        = fixit_pricing();
	$names    = fixit_pricing_tier_names();
	$audience = fixit_pricing_tier_audience();
	$last     = end( $p['tiers'] );
	$contact  = add_query_arg( 'mehsul', 'Fixit Remote', fixit_page_url( 'template-contact.php' ) );
	$keys     = wp_list_pluck( $p['tiers'], 'key' );
	/* translators: %s: faiz */
	$save = $p['save'] ? sprintf( __( '%s%% qənaət', 'fixit' ), number_format_i18n( $p['save'] ) ) : '';
	?>
	<section class="section section--soft price-section" id="qiymetler" data-billing="month">
		<div class="container">
			<div class="section-head section-head--center reveal">
				<span class="eyebrow"><?php fixit_icon( 'tag' ); ?><?php esc_html_e( 'Qiymətlər', 'fixit' ); ?></span>
				<h2><?php esc_html_e( 'Kompüter sayına görə ödəyirsiniz, operator sayına görə yox', 'fixit' ); ?></h2>
				<p><?php esc_html_e( 'Qiymət idarə olunan kompüterlərin sayından asılıdır. Komandanızın hamısı işləyə bilər, bütün imkanlar hər paketdədir. Aylıq və ya illik ödəyə bilərsiniz.', 'fixit' ); ?></p>
			</div>

			<?php if ( $p['show'] ) : ?>
				<div class="bill-toggle" role="group" aria-label="<?php esc_attr_e( 'Ödəniş dövrü', 'fixit' ); ?>" hidden>
					<button type="button" data-bill-set="month" aria-pressed="true"><?php esc_html_e( 'Aylıq ödəniş', 'fixit' ); ?></button>
					<button type="button" data-bill-set="year" aria-pressed="false"><?php esc_html_e( 'İllik ödəniş', 'fixit' ); ?><?php if ( $save ) : ?><span class="bill-toggle__save"><?php echo esc_html( $save ); ?></span><?php endif; ?></button>
				</div>
			<?php endif; ?>

			<div class="price-grid reveal">
				<?php foreach ( $p['tiers'] as $tier ) : ?>
					<?php $featured = ( 'pesekar' === $tier['key'] ); ?>
					<article class="price-card<?php echo $featured ? ' price-card--featured' : ''; ?>">
						<?php if ( $featured ) : ?>
							<span class="price-card__badge"><?php esc_html_e( 'Ən çox seçilən', 'fixit' ); ?></span>
						<?php endif; ?>

						<header class="price-card__head">
							<h3><?php echo esc_html( isset( $names[ $tier['key'] ] ) ? $names[ $tier['key'] ] : $tier['key'] ); ?></h3>
							<p><?php echo esc_html( isset( $audience[ $tier['key'] ] ) ? $audience[ $tier['key'] ] : '' ); ?></p>
						</header>

						<div class="price-card__price">
							<?php if ( $p['show'] ) : ?>
								<?php fixit_pricing_amount( $tier['month'], $tier['year'] ); ?>
								<p class="price-card__note">
									<span data-bill="month">
										<?php
										/* translators: %s: qiymət */
										echo esc_html( sprintf( __( 'kompüter başına %s', 'fixit' ), fixit_pricing_money( $tier['price'] ) ) );
										?>
									</span>
									<span data-bill="year">
										<?php
										/* translators: 1: aylıq məbləğ, 2: ödənilən ay sayı */
										echo esc_html( sprintf( __( '%1$s × %2$d ay', 'fixit' ), fixit_pricing_money( $tier['month'] ), $p['months'] ) );
										?>
										<?php if ( $save ) : ?>
											<em class="price-save"><?php echo esc_html( $save ); ?></em>
										<?php endif; ?>
									</span>
								</p>
							<?php else : ?>
								<span class="price-amount price-amount--ask"><b><?php esc_html_e( 'Qiymət sorğu ilə', 'fixit' ); ?></b></span>
								<p class="price-card__note"><?php esc_html_e( 'təklifi sınaqla birlikdə göndəririk', 'fixit' ); ?></p>
							<?php endif; ?>
						</div>

						<p class="price-card__range">
							<?php fixit_icon( 'computer' ); ?>
							<span>
								<?php
								/* translators: %s: kompüter sayı */
								echo esc_html( sprintf( __( '%s kompüterə qədər', 'fixit' ), number_format_i18n( $tier['max'] ) ) );
								?>
							</span>
						</p>

						<a class="btn <?php echo $featured ? 'btn--primary' : 'btn--ghost'; ?> btn--block" href="#sinaq"><?php esc_html_e( '14 gün pulsuz sınayın', 'fixit' ); ?></a>
					</article>
				<?php endforeach; ?>
			</div>

			<div class="price-custom reveal">
				<span class="price-custom__icon"><?php fixit_icon( 'users' ); ?></span>
				<p>
					<strong><?php esc_html_e( 'Fərdi təklif', 'fixit' ); ?></strong>
					<span>
						<?php
						/* translators: %s: kompüter sayı */
						echo esc_html( sprintf( __( '%s kompüterdən çox', 'fixit' ), number_format_i18n( $last['max'] ) ) . ': ' . __( 'həcmə görə qiymət, çoxillik müqavilə, bir neçə filial', 'fixit' ) );
						?>
					</span>
				</p>
				<a class="btn btn--ghost" href="<?php echo esc_url( $contact ); ?>"><?php esc_html_e( 'Bizimlə əlaqə', 'fixit' ); ?></a>
			</div>

			<?php if ( $p['show'] ) : ?>
				<div class="price-calc reveal" data-price-calc
					data-tiers="<?php echo esc_attr( wp_json_encode( $p['tiers'] ) ); ?>"
					data-names="<?php echo esc_attr( wp_json_encode( $names ) ); ?>"
					data-step="<?php echo esc_attr( $p['step'] ); ?>"
					data-months="<?php echo esc_attr( $p['months'] ); ?>"
					data-sep="<?php echo esc_attr( wp_json_encode( fixit_pricing_separators() ) ); ?>"
					data-per-month="<?php esc_attr_e( 'ayda', 'fixit' ); ?>"
					data-per-year="<?php esc_attr_e( 'ildə', 'fixit' ); ?>"
					data-tpl-tier="<?php echo esc_attr__( '{tier} paketi{extra}', 'fixit' ); ?>"
					data-tpl-extra="<?php echo esc_attr__( ' və {extra} əlavə kompüter', 'fixit' ); ?>"
					data-tpl-alt-month="<?php echo esc_attr__( 'Aylıq ödənişdə: {v}', 'fixit' ); ?>"
					data-tpl-alt-year="<?php echo esc_attr__( 'İllik ödənişdə: {v}', 'fixit' ); ?>"
					data-tpl-custom-title="<?php esc_attr_e( 'Fərdi təklif', 'fixit' ); ?>"
					data-tpl-custom="<?php echo esc_attr__( '{n} kompüter üçün fərdi təklif hazırlayırıq: bizimlə əlaqə saxlayın.', 'fixit' ); ?>">
					<div class="price-calc__input">
						<label for="price-calc-n"><?php esc_html_e( 'Neçə kompüteriniz var?', 'fixit' ); ?></label>
						<div class="price-calc__row">
							<input type="range" id="price-calc-range" min="1" max="<?php echo esc_attr( $last['max'] + 100 ); ?>" value="100" aria-label="<?php esc_attr_e( 'Kompüter sayı', 'fixit' ); ?>">
							<input type="number" id="price-calc-n" min="1" max="100000" value="100" inputmode="numeric">
						</div>
					</div>
					<div class="price-calc__out" aria-live="polite">
						<span class="price-calc__label"><?php esc_html_e( 'Sizə uyğun paket', 'fixit' ); ?></span>
						<span class="price-calc__tier" data-calc="tier"></span>
						<span class="price-calc__big" data-calc="big"></span>
						<span class="price-calc__alt" data-calc="alt"></span>
					</div>
				</div>
			<?php endif; ?>

			<div class="price-compare reveal">
				<div class="price-compare__all">
					<h3><?php esc_html_e( 'Hər paketə daxildir', 'fixit' ); ?></h3>
					<ul class="price-includes">
						<?php foreach ( fixit_pricing_includes() as $line ) : ?>
							<li><?php fixit_icon( 'check' ); ?><span><?php echo esc_html( $line ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<h3 class="price-compare__title"><?php esc_html_e( 'Paketlər nə ilə fərqlənir', 'fixit' ); ?></h3>
				<div class="price-table-wrap" tabindex="0" role="region" aria-label="<?php esc_attr_e( 'Paketlər nə ilə fərqlənir', 'fixit' ); ?>">
					<table class="price-table">
						<thead>
							<tr>
								<td></td>
								<?php foreach ( $p['tiers'] as $tier ) : ?>
									<th scope="col"<?php echo 'pesekar' === $tier['key'] ? ' class="is-featured"' : ''; ?>><?php echo esc_html( $names[ $tier['key'] ] ); ?></th>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tbody>
							<tr>
								<th scope="row"><?php esc_html_e( 'Kompüterə qədər', 'fixit' ); ?></th>
								<?php foreach ( $p['tiers'] as $tier ) : ?>
									<td<?php echo 'pesekar' === $tier['key'] ? ' class="is-featured"' : ''; ?>><b><?php echo esc_html( number_format_i18n( $tier['max'] ) ); ?></b></td>
								<?php endforeach; ?>
							</tr>
							<?php if ( $p['show'] ) : ?>
								<tr>
									<th scope="row"><?php esc_html_e( 'Qiymət', 'fixit' ); ?></th>
									<?php foreach ( $p['tiers'] as $tier ) : ?>
										<td<?php echo 'pesekar' === $tier['key'] ? ' class="is-featured"' : ''; ?>><?php fixit_pricing_amount( $tier['month'], $tier['year'], 'price-amount--sm' ); ?></td>
									<?php endforeach; ?>
								</tr>
								<tr>
									<th scope="row">
										<?php
										/* translators: %s: say */
										echo esc_html( sprintf( __( 'Hər əlavə %s kompüter', 'fixit' ), $p['step'] ) );
										?>
									</th>
									<?php foreach ( $p['tiers'] as $tier ) : ?>
										<td<?php echo 'pesekar' === $tier['key'] ? ' class="is-featured"' : ''; ?>><?php fixit_pricing_amount( $p['step'] * $tier['price'], $p['step'] * $tier['price'] * $p['months'], 'price-amount--sm' ); ?></td>
									<?php endforeach; ?>
								</tr>
							<?php endif; ?>
							<?php foreach ( fixit_pricing_compare() as $row ) : ?>
								<tr>
									<th scope="row"><?php echo esc_html( $row['label'] ); ?></th>
									<?php
									$on = false;
									foreach ( $keys as $key ) :
										$on = $on || ( isset( $row['from'] ) && $row['from'] === $key );
										?>
										<td<?php echo 'pesekar' === $key ? ' class="is-featured"' : ''; ?>>
											<?php if ( isset( $row['cells'] ) ) : ?>
												<?php echo esc_html( isset( $row['cells'][ $key ] ) ? $row['cells'][ $key ] : '' ); ?>
											<?php elseif ( $on ) : ?>
												<span class="price-yes"><?php fixit_icon( 'check' ); ?><span class="screen-reader-text"><?php esc_html_e( 'Var', 'fixit' ); ?></span></span>
											<?php else : ?>
												<span class="price-no" aria-hidden="true"></span><span class="screen-reader-text"><?php esc_html_e( 'Yoxdur', 'fixit' ); ?></span>
											<?php endif; ?>
										</td>
									<?php endforeach; ?>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<?php if ( $p['show'] && ( $p['multi2'] || $p['multi3'] ) ) : ?>
					<p class="price-foot">
						<?php
						/* translators: 1: 2 illik endirim, 2: 3 illik endirim */
						echo esc_html( sprintf( __( '2 illik müqavilədə %1$s%%, 3 illikdə %2$s%% endirim.', 'fixit' ), number_format_i18n( $p['multi2'] ), number_format_i18n( $p['multi3'] ) ) );
						?>
					</p>
				<?php endif; ?>
			</div>

			<div class="price-license reveal">
				<div>
					<h3><?php esc_html_e( 'Lisenziya almaq istəyirsiniz?', 'fixit' ); ?></h3>
					<p><?php esc_html_e( 'Lisenziya müqavilə əsasında rəsmiləşdirilir, ödəniş bank köçürməsi ilə. Bizə yazın: kompüter sayınıza görə müqaviləni və hesab-fakturanı hazırlayırıq, ödənişdən sonra lisenziya açarını göndəririk.', 'fixit' ); ?></p>
				</div>
				<a class="btn btn--primary" href="<?php echo esc_url( $contact . '#elaqe-form' ); ?>"><?php esc_html_e( 'Lisenziya üçün müraciət', 'fixit' ); ?><?php fixit_icon( 'arrow-r' ); ?></a>
			</div>
		</div>
	</section>
	<?php
}
