<?php
/**
 * Tez-tez verilən suallar.
 *
 * Suallar burada bir yerdə saxlanılır: həm səhifədə göstərilir,
 * həm də Google üçün FAQ struktur məlumatına çevrilir (zəngin nəticə şansı).
 *
 * Sual əlavə etmək üçün aşağıdakı massivə bir sətir yazmaq kifayətdir.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ana səhifədəki suallar.
 */
function fixit_faq_home() {
	return array(
		array(
			__( 'Diaqnostika həqiqətən pulsuzdur?', 'fixit' ),
			__( 'Bəli. Kompüter və noutbuklar üçün ilkin detallı analiz tam pulsuzdur. Analizdən sonra problem, təklif olunan həll və dəqiq qiymət sizə bildirilir — razılığınız olmadan heç bir iş görülmür.', 'fixit' ),
		),
		array(
			__( 'İT dəstək xidməti hansı formada təqdim olunur?', 'fixit' ),
			__( 'Aylıq abunə formasında. Buraya infrastrukturun ilkin tam yoxlanılması, mövcud problemlərin həlli, uzaqdan və yerində dəstək, ayda bir dəfə ümumi baxış, həmçinin İT xərclərinin analizi və optimallaşdırılması daxildir.', 'fixit' ),
		),
		array(
			__( 'Kameralara uzaqdan baxa biləcəyəm?', 'fixit' ),
			__( 'Bəli. Quraşdırdığımız sistemlərdə dünyanın istənilən nöqtəsindən telefon və ya kompüter vasitəsilə canlı baxış və arxivə giriş mümkündür.', 'fixit' ),
		),
		array(
			__( 'Hansı IP telefoniya platformaları ilə işləyirsiniz?', 'fixit' ),
			__( 'Əsasən 3CX və Yeastar. Hər iki platforma bulud və ya lokal serverdə qurula bilər; IVR, konfrans zəngləri, zəng qeydiyyatı və hesabatlar dəstəklənir.', 'fixit' ),
		),
		array(
			__( 'Bakıdan kənarda xidmət göstərirsiniz?', 'fixit' ),
			__( 'Bəli. Baş ofisimiz Bakıda, filialımız isə Cəlilabaddadır. Regionlarda layihə əsaslı iş və uzaqdan dəstək təmin edirik.', 'fixit' ),
		),
	);
}

/**
 * Məhsullar səhifəsindəki suallar.
 */
function fixit_faq_products() {
	return array(
		array(
			__( 'Qiymət niyə saytda göstərilmir?', 'fixit' ),
			__( 'Çünki qiymət istifadəçi sayından, lazım olan modullardan və quraşdırma həcmindən asılıdır. Sabit siyahı vermək əvəzinə ehtiyacınızı öyrənib dəqiq və şəffaf təklif hazırlayırıq.', 'fixit' ),
		),
		array(
			__( 'Məlumatlarımız harada saxlanılacaq?', 'fixit' ),
			__( 'Sizin öz serverinizdə. Hər iki məhsul on-premise modeldə təqdim olunur — məlumat şirkətinizin infrastrukturundan kənara çıxmır.', 'fixit' ),
		),
		array(
			__( 'Almazdan əvvəl sınaya bilərəmmi?', 'fixit' ),
			__( 'Bəli. Əvvəlcə canlı demo təşkil edirik, razılaşma olarsa sınaq quraşdırması da mümkündür.', 'fixit' ),
		),
		array(
			__( 'Şirkətimizin iş axınına uyğunlaşdırıla bilərmi?', 'fixit' ),
			__( 'Bəli. Hər iki məhsul tamamilə bizim kodumuzdur, kənar sistemə bağlılıq yoxdur. Ona görə əlavə modul və dəyişikliklər mümkündür.', 'fixit' ),
		),
		array(
			__( 'Quraşdırmadan sonra dəstək verilirmi?', 'fixit' ),
			__( 'Bəli. Komandanız üçün təlim keçirilir və texniki dəstək paketi təklif olunur; dəstək müddətində yeni versiyalar da təqdim edilir.', 'fixit' ),
		),
		array(
			__( 'Sorğu Sistemi mövcud e-poçt qutumuzla işləyirmi?', 'fixit' ),
			__( 'Bəli. Şirkət poçt qutunuz sistemə qoşulur: gələn məktublar avtomatik müraciətə çevrilir, agentin cavabı isə müştəriyə adi e-poçt kimi gedir.', 'fixit' ),
		),
	);
}

/**
 * Sual blokunu ekrana çıxarır və eyni zamanda schema üçün qeyd edir.
 *
 * @param array $faqs Sual-cavab massivi.
 */
function fixit_render_faq( $faqs ) {
	if ( ! $faqs ) {
		return;
	}

	// Səhifənin sonunda FAQ schema yazıla bilsin deyə saxlayırıq.
	$GLOBALS['fixit_faq_shown'] = $faqs;

	echo '<div class="faq">';
	foreach ( $faqs as $faq ) {
		printf(
			'<details><summary>%s</summary><p>%s</p></details>',
			esc_html( $faq[0] ),
			esc_html( $faq[1] )
		);
	}
	echo '</div>';
}
