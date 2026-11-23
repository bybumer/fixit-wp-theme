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
			__( 'Bəli. Ofisimiz Bakıdadır, regionlarda layihə əsaslı işləyir və uzaqdan dəstək veririk.', 'fixit' ),
		),
	);
}

/**
 * Məhsullar səhifəsindəki suallar.
 */
function fixit_faq_products() {
	return array(
		array(
			__( 'Qiymətlər necə hesablanır?', 'fixit' ),
			__( 'Fixit Remote idarə olunan cihazların sayına görə paketlərlə satılır, operator sayı məhdud deyil; paketlər məhsul səhifəsindədir. Sorğu Sistemi və Şəbəkə Monitorinqi üçün qiymət istifadəçi sayından, modullardan və quraşdırma həcmindən asılıdır: ehtiyacınızı öyrənib yazılı təklif hazırlayırıq.', 'fixit' ),
		),
		array(
			__( 'Məlumatlarımız harada saxlanılacaq?', 'fixit' ),
			__( 'Seçim sizindir. On-premise variantda proqram sizin serverinizdə quraşdırılır və məlumat şirkətinizin infrastrukturundan kənara çıxmır. Bulud variantında sistemi biz işə salırıq, serveri və yeniləmələri biz idarə edirik, siz sadəcə istifadə edirsiniz.', 'fixit' ),
		),
		array(
			__( 'Almazdan əvvəl sınaya bilərəmmi?', 'fixit' ),
			__( 'Bəli. Sorğu Sistemi üçün açıq onlayn demo var (demo.fixit.az): qeydiyyat yoxdur, Admin, Agent, Menecer və ya Müştəri kimi bir kliklə daxil olursunuz. Digər məhsullar üçün canlı demo təşkil edirik; razılaşma olarsa sınaq quraşdırması da mümkündür.', 'fixit' ),
		),
		array(
			__( 'Şirkətimizin iş axınına uyğunlaşdırıla bilərmi?', 'fixit' ),
			__( 'Bəli. Şirkətinizin iş axınını öyrənib lazım olan əlavə modulları və dəyişiklikləri hazırlayırıq.', 'fixit' ),
		),
		array(
			__( 'Quraşdırmadan sonra dəstək verilirmi?', 'fixit' ),
			__( 'Bəli. Komandanız üçün təlim keçirilir və texniki dəstək paketi təklif olunur; dəstək müddətində yeni versiyalar da təqdim edilir.', 'fixit' ),
		),
		array(
			__( 'Sorğu Sistemi mövcud e-poçt qutumuzla işləyirmi?', 'fixit' ),
			__( 'Bəli. Şirkət poçt qutunuz sistemə qoşulur: gələn məktublar avtomatik müraciətə çevrilir, agentin cavabı isə müştəriyə adi e-poçt kimi gedir.', 'fixit' ),
		),
		array(
			__( 'Sorğu Sistemi yalnız müştəri müraciətləri üçündür?', 'fixit' ),
			__( 'Xeyr. Sistemdə iki modul var: Ticket — müştəridən gələn müraciətlər, Task — komandanın daxili işləri (lövhə, checklist, son tarix, təkrarlanan işlər). İkisini birlikdə və ya ayrıca işlədə bilərsiniz.', 'fixit' ),
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

/**
 * Xidmət səhifəsinin sualları (xidmətin slug-una görə).
 *
 * Yeni xidmət əlavə olunanda bura onun da sualları yazılır, yoxsa səhifədə
 * sual bloku görünmür.
 *
 * @param string $slug Xidmətin slug-u.
 * @return array
 */
function fixit_faq_service( $slug ) {
	switch ( $slug ) {
		case 'komputer-servisi':
			return array(
				array(
					__( 'Diaqnostika üçün pul ödəməliyəm?', 'fixit' ),
					__( 'Xeyr, ilkin analiz pulsuzdur. Cihaza baxırıq, nasazlığın səbəbini və təmirin qiymətini deyirik. Razılıq verməsəniz, heç bir iş görülmür.', 'fixit' ),
				),
				array(
					__( 'Təmir nə qədər vaxt aparır?', 'fixit' ),
					__( 'Nasazlıqdan və lazım olan hissənin olub-olmamasından asılıdır. Müddəti analizdən sonra qiymətlə birlikdə bildiririk.', 'fixit' ),
				),
				array(
					__( 'Təmir zamanı fayllarım silinə bilər?', 'fixit' ),
					__( 'Sistemi yenidən qurmaq lazım gələrsə, bunu əvvəlcədən deyirik və vacib faylların nüsxəsini çıxarmağı təklif edirik. Razılığınız olmadan diskdəki məlumata toxunulmur.', 'fixit' ),
				),
				array(
					__( 'Ofisdəki bütün kompüterlərə baxırsınız?', 'fixit' ),
					__( 'Bəli. Şirkətin kompüterlərinə müntəzəm texniki baxış və gündəlik kömək İT dəstək xidmətinə daxildir.', 'fixit' ),
				),
			);

		case 'video-musahide-sistemleri':
			return array(
				array(
					__( 'Kameralara telefondan baxmaq olar?', 'fixit' ),
					__( 'Bəli. Quraşdırmadan sonra sistemi telefon tətbiqinə qoşuruq. Canlı görüntüyə, arxivə və hadisə bildirişlərinə internet olan istənilən yerdən baxa bilərsiniz.', 'fixit' ),
				),
				array(
					__( 'Arxiv neçə gün saxlanılır?', 'fixit' ),
					__( 'Kameraların sayından, görüntünün keyfiyyətindən və diskin həcmindən asılıdır. Layihədə ehtiyacınıza uyğun disk seçirik və neçə günlük arxiv alınacağını əvvəlcədən hesablayırıq.', 'fixit' ),
				),
				array(
					__( 'Köhnə kameralarımızı yeni sistemə qoşmaq olar?', 'fixit' ),
					__( 'Çox vaxt olar. Kameraların modelinə baxırıq: uyğun gələnləri yeni qeydiyyatçıya qoşuruq, uyğun gəlməyənlərin yerinə nə qoymağın sərfəli olduğunu deyirik.', 'fixit' ),
				),
				array(
					__( 'NVR, DVR və XVR nə ilə fərqlənir?', 'fixit' ),
					__( 'DVR analoq kameralarla, NVR IP kameralarla işləyir. XVR hər iki növü qəbul edir və analoq sistemi tədricən IP kameralara keçirmək istəyənlər üçün rahatdır.', 'fixit' ),
				),
			);

		case 'it-destek-xidmeti':
			return array(
				array(
					__( 'Bu xidmət hansı şirkətlər üçündür?', 'fixit' ),
					__( 'Ştatında İT mütəxəssisi olmayan və ya mövcud mütəxəssisinə kömək lazım olan şirkətlər üçün. Kompüterlər, şəbəkə, printerlər, poçt və serverlərlə bağlı gündəlik işləri üzərimizə götürürük.', 'fixit' ),
				),
				array(
					__( 'Problem yarananda necə müraciət edirik?', 'fixit' ),
					__( 'Zəng edirsiniz və ya yazırsınız. Uzaqdan həll oluna bilən işləri uzaqdan görürük, lazım olanda mütəxəssis ofisinizə gəlir.', 'fixit' ),
				),
				array(
					__( 'Qiymət necə müəyyən olunur?', 'fixit' ),
					__( 'Xidmət aylıq abunə ilə göstərilir. Əvvəlcə infrastrukturu yoxlayırıq, sonra kompüter və serverlərin sayına, işin həcminə görə aylıq təklif hazırlayırıq.', 'fixit' ),
				),
				array(
					__( 'Aylıq yoxlanışda nəyə baxırsınız?', 'fixit' ),
					__( 'Kompüterlərin, serverlərin, ehtiyat nüsxələrin və şəbəkə avadanlığının ümumi vəziyyətinə. Gördüyümüz riskləri və nəyi yaxşılaşdırmağın mümkün olduğunu sizinlə bölüşürük.', 'fixit' ),
				),
			);

		case 'ip-telefoniya':
			return array(
				array(
					__( '3CX və Yeastar arasında necə seçim edək?', 'fixit' ),
					__( '3CX geniş inteqrasiya imkanı olan çevik platformadır, buludda və ya öz serverinizdə işləyir. Yeastar kiçik və orta ofislər üçün hazır, sadə həlldir. Seçimi işçi sayına və zənglərin həcminə görə birlikdə edirik.', 'fixit' ),
				),
				array(
					__( 'İşçilər ofisdən kənarda da ofis nömrəsindən danışa bilər?', 'fixit' ),
					__( 'Bəli. Telefon tətbiqi və ya kompüter proqramı ilə işçi harada olsa da ofis xəttindən zəng edə və zəngləri qəbul edə bilər.', 'fixit' ),
				),
				array(
					__( 'Zənglər yazılır?', 'fixit' ),
					__( 'İstəsəniz, zənglərin qeydi açılır. Hesabatlarda kimin neçə zəng qəbul etdiyi və buraxılmış zənglər görünür.', 'fixit' ),
				),
				array(
					__( 'Mövcud analoq telefonlarımızı saxlamaq olar?', 'fixit' ),
					__( 'Bəli, adapter vasitəsilə analoq aparatları yeni sistemə qoşmaq olur. Hansılarını saxlamağın sərfəli olduğunu yerində baxıb deyirik.', 'fixit' ),
				),
			);

		case 'korporativ-mail-sistemleri':
			return array(
				array(
					__( 'Köhnə məktubları itirmədən keçmək olar?', 'fixit' ),
					__( 'Bəli. Köhnə qutulardakı məktubları yeni sistemə köçürürük. Keçidin vaxtını sizinlə razılaşdırırıq ki, iş yazışması yarımçıq qalmasın.', 'fixit' ),
				),
				array(
					__( 'Poçtu telefonda oxumaq olar?', 'fixit' ),
					__( 'Bəli. Sistem telefon və planşetlərlə, həmçinin Outlook kimi poçt proqramları ilə işləyir. Məktublar bütün cihazlarda eyni görünür.', 'fixit' ),
				),
				array(
					__( 'Spam və viruslardan necə qorunur?', 'fixit' ),
					__( 'Gələn məktublar spam və virus filtrindən keçir. Şübhəli məktublar ayrıca qovluğa düşür, istəsəniz onlara baxıb geri qaytara bilərsiniz.', 'fixit' ),
				),
				array(
					__( 'Ünvanlar şirkətin domeni ilə olacaq?', 'fixit' ),
					__( 'Bəli, ünvanlar ad@sirketiniz.az formasında olur. Domeniniz yoxdursa, onu almağa da kömək edirik.', 'fixit' ),
				),
			);

		case 'bulud-serverler':
			return array(
				array(
					__( 'Bulud server fiziki serverdən nə ilə fərqlənir?', 'fixit' ),
					__( 'Avadanlıq almaq və saxlamaq lazım gəlmir. Prosessor, yaddaş və disk ehtiyaca görə artırılır və ya azaldılır, server internet olan istənilən yerdən idarə olunur.', 'fixit' ),
				),
				array(
					__( 'Məlumatların ehtiyat nüsxəsi çıxarılır?', 'fixit' ),
					__( 'Bəli. Avtomatik yedəkləmə qurulur, lazım olanda məlumatı nüsxədən bərpa edirik.', 'fixit' ),
				),
				array(
					__( 'Mövcud serverimizi buluda köçürmək olar?', 'fixit' ),
					__( 'Bəli. Əvvəlcə serverdə işləyən proqramlara və məlumatın həcminə baxırıq, sonra köçürmənin planını və vaxtını sizinlə razılaşdırırıq.', 'fixit' ),
				),
				array(
					__( 'Bulud server hansı işlər üçün uyğundur?', 'fixit' ),
					__( 'Mühasibat və ERP proqramları, fayl serveri, sayt, poçt və uzaqdan iş üçün masaüstü serverləri. Sizə hansının sərfəli olduğunu ehtiyacınızı öyrəndikdən sonra deyirik.', 'fixit' ),
				),
			);
	}

	return array();
}
