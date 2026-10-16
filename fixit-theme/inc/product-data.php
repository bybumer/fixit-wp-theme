<?php
/**
 * Məhsul səhifələrinin detallı məzmunu.
 *
 * Qısa təsvir, əsas xüsusiyyətlər, teqlər, demo ünvanı və ekran görüntüləri
 * admin paneldən (Məhsullar) idarə olunur. Bu fayl isə səhifənin daha zəngin
 * bölmələrini saxlayır: problem → həll, modullar, xüsusiyyət qrupları,
 * hazır ekran görüntüləri, iş prinsipi, demo, təhlükəsizlik və suallar.
 *
 * Açar — məhsulun ünvanı (slug): /mehsul/<slug>/
 *
 * Qayda: burada yalnız məhsulda HƏQİQƏTƏN olan imkanlar yazılır.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bütün məhsulların detallı məzmunu.
 *
 * @return array
 */
function fixit_product_details() {
	return array(

		/* ======================================================
		   FIXIT Sorğu Sistemi
		   ====================================================== */
		'sorgu-sistemi' => array(
			'tagline'  => __( 'Müştəri müraciətləri və komandanın daxili işləri — bir platformada', 'fixit' ),
			'badges'   => array(
				__( 'Ticket + Task', 'fixit' ),
				__( 'Öz serverinizdə', 'fixit' ),
				__( 'Azərbaycan · English · Русский', 'fixit' ),
				__( 'Avtomatik yenilənmə', 'fixit' ),
			),

			'intro'    => __( 'İki modul, bir sistem: kənardan gələn müraciətlər (Ticket) və komandanın öz işləri (Task) eyni mühərrik üzərində — eyni axtarış, hesabat, vaxt uçotu. Hər ikisini birlikdə və ya ayrıca işlədə bilərsiniz.', 'fixit' ),

			/*
			 * Ticket və Task yan-yana. Məhsulun əsas fərqi məhz budur: iki ayrı
			 * alət yox, eyni mühərrikdə iki iş növü. Ona görə səhifədə FAQ-da
			 * gizlənmir, ayrıca bölmə kimi göstərilir.
			 */
			'modules'  => array(
				'title'  => __( 'İki modul — bir platforma', 'fixit' ),
				'lead'   => __( 'Ticket kənardan gələn müraciət üçündür, Task komandanın öz işi üçün. Hər ikisi eyni mühərrikdə işləyir: birini və ya ikisini birlikdə açırsınız.', 'fixit' ),
				'items'  => array(
					array(
						'icon'   => 'ticket',
						'name'   => 'Ticket',
						'label'  => __( 'Müştəri müraciətləri', 'fixit' ),
						'text'   => __( 'Müştəri yazır, komanda cavab verir, SLA sayğacı işləyir.', 'fixit' ),
						'points' => array(
							__( 'E-poçt, müştəri portalı, Telegram və veb forma', 'fixit' ),
							__( 'SLA iş saatına görə; müddət keçəndə eskalasiya', 'fixit' ),
							__( 'Cavab müştəriyə adi e-poçt kimi gedir', 'fixit' ),
							__( 'Müştəri öz müraciətini portalda izləyir', 'fixit' ),
						),
					),
					array(
						'icon'   => 'task',
						'name'   => 'Task',
						'label'  => __( 'Komandanın daxili işləri', 'fixit' ),
						'text'   => __( 'Kim, nəyi, nə vaxta qədər edir — hamısı lövhədə görünür.', 'fixit' ),
						'points' => array(
							__( 'Lövhə: Ediləcək → İcrada → Hazır', 'fixit' ),
							__( 'Checklist, alt tapşırıqlar və asılılıqlar', 'fixit' ),
							__( 'Son tarix və planlanan vaxt', 'fixit' ),
							__( 'Təkrarlanan işlər özü yaranır', 'fixit' ),
						),
					),
				),
				'shared' => array(
					__( 'Müraciətdən bir kliklə tapşırıq', 'fixit' ),
					__( 'Vahid axtarış', 'fixit' ),
					__( 'Dashboard və hesabatlar', 'fixit' ),
					__( 'Vaxt uçotu', 'fixit' ),
					__( 'Rollar və icazələr', 'fixit' ),
					__( 'Audit jurnalı', 'fixit' ),
				),
			),

			/*
			 * Ekran görüntüləri temanın içindədir (assets/img/products/<slug>/):
			 * yeni saytda da hazır gəlir, media kitabxanasına yükləmək lazım
			 * deyil. Admin paneldə qalereya doldurulsa, o üstün tutulur.
			 * Şəkillər demo.fixit.az-dan çəkilib (tools/demo-ekranlar.py).
			 */
			'screens'  => array(
				array( '01-sorgular.webp', __( 'Sorğular: hazır görünüşlər, süzgəc və axtarış', 'fixit' ) ),
				array( '02-sorgu.webp', __( 'Müraciət: müştəri ilə yazışma, daxili qeyd və vaxt uçotu', 'fixit' ) ),
				array( '03-lovhe.webp', __( 'Ticket lövhəsi: statusu sürüşdürməklə dəyişin', 'fixit' ) ),
				array( '04-tapsiriqlar.webp', __( 'Task lövhəsi: gecikən iş dərhal seçilir', 'fixit' ) ),
				array( '05-vaxt-hesabati.webp', __( 'Vaxt hesabatı: şirkət və agent üzrə, fakturaya düşən vaxt ayrıca', 'fixit' ) ),
				array( '06-hesabatlar.webp', __( 'Hesabatlar: iş yükü, həll müddəti, SLA və status keçidləri', 'fixit' ) ),
			),

			'problems' => array(
				array(
					'icon'  => 'mail',
					'title' => __( 'Müraciətlər dağınıqdır', 'fixit' ),
					'text'  => __( 'Kimisi e-poçt yazır, kimisi Telegram-dan, kimisi zəng edir. Nəticədə kimin nəyə cavab verdiyi bəlli olmur.', 'fixit' ),
				),
				array(
					'icon'  => 'task',
					'title' => __( 'Daxili işlər ayrı yerdədir', 'fixit' ),
					'text'  => __( 'Komandanın tapşırıqları Excel-də, mesajlarda, ağılda qalır. Müştəri işi ilə daxili iş iki ayrı alətdədir.', 'fixit' ),
				),
				array(
					'icon'  => 'chart',
					'title' => __( 'Görülən iş görünmür', 'fixit' ),
					'text'  => __( 'Ay sonunda kimin nəyə, hansı şirkətə nə qədər vaxt sərf etdiyini hesablamaq saatlar aparır.', 'fixit' ),
				),
			),

			'groups'   => array(
				array(
					'icon'  => 'mail',
					'title' => __( 'Müraciət kanalları', 'fixit' ),
					'items' => array(
						__( 'E-poçt: şirkət poçt qutusuna gələn məktub avtomatik ticket olur', 'fixit' ),
						__( 'Agentin cavabı müştəriyə adi e-poçt kimi gedir', 'fixit' ),
						__( 'Müştəri portalı: qeydiyyat, e-poçt təsdiqi, parol bərpası', 'fixit' ),
						__( 'Telegram və saytınızdakı veb forma', 'fixit' ),
						__( 'Göndərənin domeninə görə müraciət avtomatik doğru qrupa düşür', 'fixit' ),
					),
				),
				array(
					'icon'  => 'ticket',
					'title' => __( 'Gündəlik iş', 'fixit' ),
					'items' => array(
						__( 'Təyinat, statuslar və prioritetlər; bir ticket-ə bir neçə agent', 'fixit' ),
						__( 'Kanban lövhə — statusu sürüşdürməklə dəyişmək', 'fixit' ),
						__( 'Sol paneldə hazır görünüşlər — lazım olan siyahı bir kliklə', 'fixit' ),
						__( 'Axtarış: nömrə, mövzu, müştəri və mesaj mətni üzrə', 'fixit' ),
						__( 'Layihələr — şirkətdaxili şöbələr üçün ayrıca axın', 'fixit' ),
					),
				),
				// Task-ın imkanları yuxarıdakı 'modules' bölməsindədir - burada
				// təkrarlanmır, qruplar da 3×2 tor kimi düz düzülür.
				array(
					'icon'  => 'clock',
					'title' => __( 'SLA və nəzarət', 'fixit' ),
					'items' => array(
						__( 'SLA iş saatına görə hesablanır — gecə və həftəsonu sayılmır', 'fixit' ),
						__( 'Müddət keçəndə məsul şəxsə eskalasiya məktubu', 'fixit' ),
						__( '"Vaxtı keçib" siyahısı — gecikən heç nə gözdən qaçmır', 'fixit' ),
						__( 'Yeni müraciət gələn kimi bildiriş', 'fixit' ),
					),
				),
				array(
					'icon'  => 'chart',
					'title' => __( 'Vaxt uçotu və hesabat', 'fixit' ),
					'items' => array(
						__( 'Hər ticket üzrə sərf olunan vaxtın qeydi', 'fixit' ),
						__( 'Aylıq hesabat: şirkət və agent üzrə', 'fixit' ),
						__( 'Fakturaya düşən vaxt ayrıca göstərilir', 'fixit' ),
						__( 'Analitika və süzgəclər', 'fixit' ),
					),
				),
				array(
					'icon'  => 'users',
					'title' => __( 'İdarəetmə', 'fixit' ),
					'items' => array(
						__( 'Rollar və icazələr — ayrı-ayrı seçilən hüquqlar', 'fixit' ),
						__( 'Qruplar və görünürlük: kim hansı müraciətləri görür', 'fixit' ),
						__( 'Avtomatik məktub şablonları və agent imzası', 'fixit' ),
						__( 'Audit jurnalı — kim nə vaxt nə edib', 'fixit' ),
					),
				),
				array(
					'icon'  => 'server',
					'title' => __( 'Sistem', 'fixit' ),
					'items' => array(
						__( 'İnterfeysdən ehtiyat nüsxə — baza və fayllar bir arxivdə', 'fixit' ),
						__( 'Yeni versiyalar interfeysdən, avtomatik quraşdırılır', 'fixit' ),
						__( 'Mövcud helpdesk sistemindən köçürmə (məs. Zammad)', 'fixit' ),
						__( 'Mobil və planşetdə tam işləyir, işıqlı/qaranlıq rejim', 'fixit' ),
					),
				),
			),

			'flow_title' => __( 'Müraciət necə işlənir', 'fixit' ),
			'flow'       => array(
				array( 'mail', __( 'Müraciət gəlir', 'fixit' ), __( 'e-poçt, portal, Telegram və ya veb forma', 'fixit' ) ),
				array( 'ticket', __( 'Ticket yaranır', 'fixit' ), __( 'nömrə alır, doğru qrupa düşür', 'fixit' ) ),
				array( 'users', __( 'Komanda işləyir', 'fixit' ), __( 'təyinat, SLA, lazım olsa — tapşırıq', 'fixit' ) ),
				array( 'send', __( 'Cavab gedir', 'fixit' ), __( 'müştəri adi e-poçt alır', 'fixit' ) ),
				array( 'chart', __( 'Hesabat', 'fixit' ), __( 'ay sonu şirkət və agent üzrə', 'fixit' ) ),
			),


			// İstifadəçi bələdçisi. Hələlik yalnız Azərbaycanca - EN/RU səhifələrdə
			// düymənin mətni bunu deyir (tools/translations/04-mehsul-detallari.json).
			'guide'    => 'https://guide.fixit.az/',

			// Demo düyməsinin defoltu - admin paneldə dəyişmək olar (bax product-demo.php).
			// Addımlar demo.fixit.az-ın giriş səhifəsini təsvir edir: orada
			// qeydiyyat yoxdur, rollar bir kliklə seçilir.
			'demo'     => array(
				'mode'     => 'open',
				'url'      => 'https://demo.fixit.az/portal',
				'text'     => __( 'Demo 250 nümunə sorğu və 250 tapşırıqla doldurulub. Qeydiyyat yoxdur — istədiyiniz rolu seçib dərhal işə başlayın.', 'fixit' ),
				'note'     => __( 'Hər giriş 40 dəqiqəlik ayrıca sessiyadır: dəyişikliklərinizi yalnız siz görürsünüz, sessiya bitəndə silinir.', 'fixit' ),
				// Əvvəlki defolt qeydlər. Köhnə admin forması qeydi xanaya özü doldururdu
				// və məhsulu yadda saxlayanda o bazaya düşürdü - belə qeyd admin yazısı
				// sayılmır, yuxarıdakı (saytın dilinə tərcümə olunan) qeyd göstərilir.
				'note_was' => array( 'Demo məlumatları hər gecə sıfırlanır.' ),
				'steps'    => array(
					__( 'Demonu açın — qeydiyyat tələb olunmur', 'fixit' ),
					__( 'Rol seçin: Admin, Agent, Menecer və ya Müştəri', 'fixit' ),
					__( 'Sorğu və tapşırıqlarla sərbəst işləyin', 'fixit' ),
				),
				// Demonun giriş səhifəsindəki düymələrlə eyni: ziyarətçi orada
				// nə görəcəyini əvvəlcədən bilir.
				'roles'    => array(
					array( 'shield', __( 'Admin', 'fixit' ), __( 'sistemin tam görünüşü', 'fixit' ) ),
					array( 'ticket', __( 'Agent', 'fixit' ), __( 'sorğulara cavab, tapşırıqlar', 'fixit' ) ),
					array( 'chart', __( 'Menecer', 'fixit' ), __( 'hesabatlar və komanda yükü', 'fixit' ) ),
					array( 'users', __( 'Müştəri', 'fixit' ), __( 'portal: müraciət və izləmə', 'fixit' ) ),
				),
			),

			'security' => array(
				__( 'Proqram və verilənlər bazası sizin serverinizdədir', 'fixit' ),
				__( 'İki mərhələli giriş (2FA) — autentifikator tətbiqi ilə', 'fixit' ),
				__( 'Parol təxmininə qarşı qoruma: səhv cəhdlərdə IP müvəqqəti bloklanır', 'fixit' ),
				__( 'Sessiyalar server tərəfdə saxlanılır', 'fixit' ),
				__( 'Mail qəbulu siyasəti: icazəli domenlər və qara siyahı', 'fixit' ),
				__( 'Hər əməliyyat audit jurnalına yazılır', 'fixit' ),
			),

			'faq'      => array(
				array(
					__( 'Sistemin istifadəçi bələdçisi varmı?', 'fixit' ),
					__( 'Bəli, guide.fixit.az ünvanında. Orada girişdən tutmuş sorğulara cavab, tapşırıqlar, hesabatlar, administrator ayarları və quraşdırmaya qədər hər şey addım-addım, ekran görüntüləri ilə yazılıb. Sistemin içində menyudakı «Bələdçi» düyməsi də olduğunuz səhifəyə uyğun bölməni açır.', 'fixit' ),
				),
				array(
					__( 'Mövcud e-poçt qutumuzla işləyəcəkmi?', 'fixit' ),
					__( 'Bəli. Şirkət poçt qutunuz sistemə qoşulur: gələn məktublar avtomatik ticket olur, agentin cavabı isə müştəriyə adi e-poçt kimi gedir. Müştəri heç nə quraşdırmır.', 'fixit' ),
				),
				array(
					__( 'Başqa helpdesk sistemindən keçə bilərikmi?', 'fixit' ),
					__( 'Bəli. Köhnə müraciətləri tarixçəsi ilə birlikdə köçürürük — məsələn Zammad-dan köçürmə təcrübəmiz var.', 'fixit' ),
				),
				array(
					__( 'SLA necə hesablanır?', 'fixit' ),
					__( 'İş saatlarına və iş günlərinə görə. "8 saat" deyəndə gecə və həftəsonu sayılmır. Müddət keçəndə məsul şəxsə məktub gedir və müraciət "Vaxtı keçib" siyahısına düşür.', 'fixit' ),
				),
				array(
					__( 'Ticket və Task fərqi nədir?', 'fixit' ),
					__( 'Ticket — kənardan, müştəridən gələn müraciətdir: cavab ona e-poçt və ya Telegram ilə gedir, SLA sayğacı işləyir. Task — komandanın öz daxili işidir: son tarix, checklist, asılılıq. Hər ikisi eyni sistemdədir; bir müraciətdən bir kliklə tapşırıq yarada bilərsiniz. Yalnız biri və ya hər ikisi açıq ola bilər.', 'fixit' ),
				),
				array(
					__( 'İnterfeys hansı dillərdədir?', 'fixit' ),
					__( 'Azərbaycan, ingilis və rus dillərində. Hər istifadəçi öz dilini seçir.', 'fixit' ),
				),
			),
		),

		/* ======================================================
		   Fixit Remote
		   ====================================================== */
		'remote'        => array(
			'tagline'  => __( 'Şirkət kompüterlərinə uzaqdan qoşulma — öz serverinizdə', 'fixit' ),
			'badges'   => array(
				__( 'Öz serverinizdə', 'fixit' ),
				__( 'Mobil tətbiq', 'fixit' ),
				__( 'Tam jurnal', 'fixit' ),
			),

			'problems' => array(
				array(
					'icon'  => 'lock',
					'title' => __( 'Ekran başqasının serverindən keçir', 'fixit' ),
					'text'  => __( 'Adi uzaqdan giriş proqramlarında əlaqə kənar şirkətin serverləri üzərindən qurulur. Fixit Remote-da hər şey sizin serverinizdə qalır.', 'fixit' ),
				),
				array(
					'icon'  => 'network',
					'title' => __( 'Port açmaq, VLAN problemi', 'fixit' ),
					'text'  => __( 'Agent serverə özü qoşulur — işçi kompüterlərində heç bir port açmaq lazım deyil, fərqli VLAN-lar problem yaratmır.', 'fixit' ),
				),
				array(
					'icon'  => 'search',
					'title' => __( 'Kim kimə qoşulub — bilinmir', 'fixit' ),
					'text'  => __( 'Hər sessiya, hər qoşulma cəhdi və hər hadisə jurnala yazılır; CSV və PDF kimi yüklənir.', 'fixit' ),
				),
			),

			'groups'   => array(
				array(
					'icon'  => 'computer',
					'title' => __( 'Uzaqdan qoşulma', 'fixit' ),
					'items' => array(
						__( 'Ekrana baxmaq və idarə etmək — siçan və klaviatura', 'fixit' ),
						__( 'Sessiya ayrı pəncərədə açılır, panel yerində qalır', 'fixit' ),
						__( 'Sessiya zamanı yazışma (çat)', 'fixit' ),
						__( 'Fayl ötürmə — ekran axınını dayandırmadan', 'fixit' ),
					),
				),
				array(
					'icon'  => 'server',
					'title' => __( 'Cihazlar', 'fixit' ),
					'items' => array(
						__( 'Bütün kompüterlər bir siyahıda, onlayn/oflayn vəziyyəti ilə', 'fixit' ),
						__( 'Axtarış: ad, ID, istifadəçi və IP üzrə', 'fixit' ),
						__( 'Yeni cihazı təsdiqləmək və ya bloklamaq', 'fixit' ),
						__( 'Bir kliklə qoşulma', 'fixit' ),
					),
				),
				array(
					'icon'  => 'users',
					'title' => __( 'Komandalar və icazələr', 'fixit' ),
					'items' => array(
						__( 'Şöbə → komanda → əməkdaş quruluşu', 'fixit' ),
						__( 'Kim işə başlayıb — kompüterin açıq olub-olmaması', 'fixit' ),
						__( 'Qruplar: kim hansı cihazlara qoşula bilər', 'fixit' ),
						__( 'Sistem bölmələri yalnız administrator üçün', 'fixit' ),
					),
				),
				array(
					'icon'  => 'phone',
					'title' => __( 'Mobil tətbiq', 'fixit' ),
					'items' => array(
						__( 'Telefondan kompüteri idarə etmək', 'fixit' ),
						__( 'Siçan jestləri, klaviatura, xüsusi düymələr və böyütmə', 'fixit' ),
						__( 'Kontaktlar: şöbə, komanda, əməkdaş və bir toxunuşla zəng', 'fixit' ),
					),
				),
				array(
					'icon'  => 'activity',
					'title' => __( 'Jurnal və hesabat', 'fixit' ),
					'items' => array(
						__( 'Sessiyalar: kim, hansı cihaza, nə vaxt, nə qədər, nə qədər trafik', 'fixit' ),
						__( 'Sistem logu — hadisə növünə görə süzgəc', 'fixit' ),
						__( 'Logları CSV və PDF kimi yükləmək', 'fixit' ),
					),
				),
				array(
					'icon'  => 'wrench',
					'title' => __( 'Quraşdırma və baxım', 'fixit' ),
					'items' => array(
						__( 'Server və agent üçün hazır quraşdırıcı (MSI)', 'fixit' ),
						__( 'İşçi kompüterinə əvvəlcədən heç nə quraşdırmaq lazım deyil', 'fixit' ),
						__( 'Server özü yenilənir və agentlərə yeni versiyanı özü paylayır', 'fixit' ),
						__( 'Ehtiyat nüsxə və köhnə qeydlərin təmizlənməsi paneldən', 'fixit' ),
					),
				),
			),

			'flow_title' => __( 'Necə işləyir', 'fixit' ),
			'flow'       => array(
				array( 'computer', __( 'Agent', 'fixit' ), __( 'işçi kompüterində, serverə özü qoşulur', 'fixit' ) ),
				array( 'server', __( 'Fixit Remote server', 'fixit' ), __( 'sizin serverinizdə — cihazlar, icazələr, jurnal', 'fixit' ) ),
				array( 'users', __( 'Operator', 'fixit' ), __( 'brauzerdən və ya telefondan qoşulur', 'fixit' ) ),
				array( 'shield', __( 'İstifadəçi', 'fixit' ), __( 'icazə verir və qoşulmanı ekranda görür', 'fixit' ) ),
			),

			'security' => array(
				__( 'Qoşulmazdan əvvəl istifadəçidən icazə soruşulur; 30 saniyə cavab yoxdursa — rədd', 'fixit' ),
				__( 'Ekranda gizlədilə bilməyən "qoşulma var" zolağı', 'fixit' ),
				__( 'İstifadəçi yerində olmayanda — mərkəzdən idarə olunan qoşulma parolu; hər istifadə jurnala yazılır', 'fixit' ),
				__( 'Şifrələnmiş bağlantı (TLS) və sertifikat yoxlaması', 'fixit' ),
				__( 'Parollar heç yerdə açıq saxlanılmır', 'fixit' ),
				__( 'Səhv parol cəhdlərində IP müvəqqəti bloklanır', 'fixit' ),
			),

			'faq'      => array(
				array(
					__( 'AnyDesk və ya Supremo-dan əsas fərqi nədir?', 'fixit' ),
					__( 'Fixit Remote sizin öz serverinizdə işləyir: bağlantı, jurnal və icazələr şirkətinizin daxilində qalır. Kimin hansı kompüterə qoşula biləcəyini mərkəzdən idarə edir, hər sessiyanı jurnalda görürsünüz.', 'fixit' ),
				),
				array(
					__( 'İşçi kompüterlərində port açmaq lazımdırmı?', 'fixit' ),
					__( 'Xeyr. Agent serverə özü qoşulur. Kompüterlər fərqli VLAN və ya alt şəbəkələrdə ola bilər — yalnız server əlçatan olmalıdır.', 'fixit' ),
				),
				array(
					__( 'İşçi bilmədən kompüterinə qoşulmaq olarmı?', 'fixit' ),
					__( 'Xeyr. Qoşulma zamanı ekranda gizlədilə bilməyən zolaq görünür. İcazə sorğusu açıqdırsa, istifadəçi təsdiq etmədən sessiya başlamır. Qoşulma parolu istifadə olunanda da bu, çatda və jurnalda qeyd olunur.', 'fixit' ),
				),
				array(
					__( 'Hansı əməliyyat sistemlərində işləyir?', 'fixit' ),
					__( 'Agent Windows kompüterlərində işləyir. Operator brauzerdən və ya mobil tətbiqdən qoşulur.', 'fixit' ),
				),
			),
		),

		/* ======================================================
		   FIXIT Şəbəkə Monitorinqi
		   ====================================================== */
		'sebeke-monitorinqi' => array(
			'tagline'  => __( 'Kəsintini müştəridən əvvəl siz bilin', 'fixit' ),
			'badges'   => array(
				__( 'Öz serverinizdə', 'fixit' ),
				__( 'Telegram · WhatsApp · E-poçt', 'fixit' ),
			),

			'problems' => array(
				array(
					'icon'  => 'activity',
					'title' => __( 'Kəsinti gec bilinir', 'fixit' ),
					'text'  => __( 'Cihaz və ya tunel düşəndə bunu çox vaxt istifadəçilərin zəngindən öyrənirsiniz.', 'fixit' ),
				),
				array(
					'icon'  => 'chart',
					'title' => __( 'Tarixçə yoxdur', 'fixit' ),
					'text'  => __( 'Hansı nöqtənin nə qədər dayandığını sübut etmək üçün rəqəm lazımdır.', 'fixit' ),
				),
			),

			'groups'   => array(
				array(
					'icon'  => 'network',
					'title' => __( 'İzləmə', 'fixit' ),
					'items' => array(
						__( 'Cihaz, interfeys və VPN tunellərinin fasiləsiz izlənməsi', 'fixit' ),
						__( 'Uptime statistikası və kəsintilərin vaxt xətti', 'fixit' ),
						__( 'Ən problemli nöqtələrin hesabatı', 'fixit' ),
					),
				),
				array(
					'icon'  => 'gauge',
					'title' => __( 'Analitika lövhəsi', 'fixit' ),
					'items' => array(
						__( 'Modulyar bloklar — hər istifadəçi özünə lazım olanı düzür', 'fixit' ),
						__( 'Canlı interfeys trafiki', 'fixit' ),
					),
				),
				array(
					'icon'  => 'send',
					'title' => __( 'Bildirişlər', 'fixit' ),
					'items' => array(
						__( 'Telegram, e-poçt (SMTP), WhatsApp və masaüstü', 'fixit' ),
						__( 'Hər hadisə ayrıca açılıb-bağlanır', 'fixit' ),
						__( 'Kanal ayarlarını "Sınaq göndər" ilə dərhal yoxlamaq', 'fixit' ),
					),
				),
			),

			'flow_title' => __( 'Necə işləyir', 'fixit' ),
			'flow'       => array(
				array( 'network', __( 'Şəbəkə', 'fixit' ), __( 'cihazlar, interfeyslər, tunellər', 'fixit' ) ),
				array( 'server', __( 'Monitorinq', 'fixit' ), __( 'fasiləsiz yoxlama, tarixçə', 'fixit' ) ),
				array( 'send', __( 'Bildiriş', 'fixit' ), __( 'seçdiyiniz kanala dərhal', 'fixit' ) ),
			),

			'security' => array(
				__( 'Proqram və məlumat sizin serverinizdədir', 'fixit' ),
				__( 'Parol və tokenlər brauzerə göndərilmir', 'fixit' ),
			),

			'faq'      => array(),
		),
	);
}

/**
 * Bir məhsulun detallı məzmununu qaytarır.
 *
 * @param WP_Post $post Məhsul.
 * @return array Boş massiv — detallı məzmun yoxdursa.
 */
function fixit_product_detail( $post ) {
	$all = fixit_product_details();
	return isset( $all[ $post->post_name ] ) ? $all[ $post->post_name ] : array();
}

/**
 * Temanın içindəki hazır ekran görüntüləri.
 *
 * Hər şəklin yanında "-800" adlı kiçik nüsxəsi var: mobil ekran və kartlar
 * 1600 px-lik faylı yükləməsin. Fayl yoxdursa həmin sətir buraxılır.
 *
 * @param WP_Post $post Məhsul.
 * @return array[] Hər biri: src, small, caption, width, height.
 */
function fixit_product_screens( $post ) {
	$detail = fixit_product_detail( $post );
	if ( empty( $detail['screens'] ) ) {
		return array();
	}

	$dir     = '/assets/img/products/' . $post->post_name . '/';
	$screens = array();

	foreach ( $detail['screens'] as $screen ) {
		$file = basename( $screen[0] );
		if ( ! file_exists( get_template_directory() . $dir . $file ) ) {
			continue;
		}

		$small = preg_replace( '/(\.\w+)$/', '-800$1', $file );

		$screens[] = array(
			'src'     => get_template_directory_uri() . $dir . $file,
			'small'   => file_exists( get_template_directory() . $dir . $small ) ? get_template_directory_uri() . $dir . $small : '',
			'caption' => $screen[1],
			'width'   => 1600,
			'height'  => 1000,
		);
	}

	return $screens;
}

/**
 * Hazır ekran görüntüsünün <img> teqi.
 *
 * @param array  $screen  fixit_product_screens() sətri.
 * @param string $sizes   Brauzerə ipucu: şəkil səhifədə nə enində görünür.
 * @param bool   $eager   Birinci ekranda görünən şəkil gecikmədən yüklənsin.
 * @return string
 */
function fixit_product_screen_img( $screen, $sizes = '(max-width: 900px) 100vw, 50vw', $eager = false ) {
	$srcset = $screen['small']
		? sprintf( '%s 800w, %s 1600w', esc_url( $screen['small'] ), esc_url( $screen['src'] ) )
		: '';

	return sprintf(
		'<img src="%1$s"%2$s alt="%3$s" width="%4$d" height="%5$d" loading="%6$s" decoding="async">',
		esc_url( $screen['src'] ),
		$srcset ? sprintf( ' srcset="%s" sizes="%s"', esc_attr( $srcset ), esc_attr( $sizes ) ) : '',
		esc_attr( $screen['caption'] ),
		(int) $screen['width'],
		(int) $screen['height'],
		$eager ? 'eager' : 'lazy'
	);
}
