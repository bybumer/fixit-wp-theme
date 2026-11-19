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
				__( 'Öz serverinizdə və ya buludda', 'fixit' ),
				__( 'Azərbaycan · English · Русский', 'fixit' ),
				__( 'Android tətbiqi', 'fixit' ),
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
							__( 'Cavab müştəriyə yazdığı kanalla gedir: e-poçt, Telegram və ya portal', 'fixit' ),
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
					__( 'Ctrl+K ilə vahid axtarış', 'fixit' ),
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
				array( '01-sorgular.webp', __( 'Siyahı və sorğu yan-yana: işdən işə keçmək üçün geri qayıtmaq lazım deyil', 'fixit' ) ),
				array( '02-sorgu.webp', __( 'Sorğunun bütün konteksti bir ekranda: müştərinin digər sorğuları və bir kliklə «Həll olundu»', 'fixit' ) ),
				array( '03-axtaris.webp', __( 'Ctrl+K: sorğu, tapşırıq və müştərini bir pəncərədən tapın', 'fixit' ) ),
				array( '04-dashboard.webp', __( 'Hər işçinin öz paneli: vidcetlər, qrafiklər və yük bölgüsü', 'fixit' ) ),
				array( '05-tapsiriqlar.webp', __( 'Tapşırıq lövhəsi: gecikən iş dərhal seçilir', 'fixit' ) ),
				array( '06-gantt.webp', __( 'Gantt qrafiki: tapşırıqların müddəti və asılılıqları', 'fixit' ) ),
				array( '07-vaxt-hesabati.webp', __( 'Vaxt hesabatı: şirkət və agent üzrə, fakturaya düşən vaxt ayrıca', 'fixit' ) ),
				array( '08-hesabatlar.webp', __( 'Həll müddəti: orta, median, 90% və həftəlik dinamika', 'fixit' ) ),
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
						__( 'Cavab müştəriyə yazdığı kanalla qayıdır: məktuba məktubla, Telegram-a Telegram-da', 'fixit' ),
						__( 'Müştəri portalı: qeydiyyat, e-poçt təsdiqi, parol bərpası', 'fixit' ),
						__( 'Telegram botu: server internetdən görünməsə də işləyir', 'fixit' ),
						__( 'Göndərənin e-poçt domeninə görə sorğu doğru şirkətə bağlanır', 'fixit' ),
					),
				),
				array(
					'icon'  => 'ticket',
					'title' => __( 'Gündəlik iş', 'fixit' ),
					'items' => array(
						__( 'Siyahıda bir klik sorğunu sağ paneldə açır, ↑ ↓ ilə işdən işə keçirsiniz', 'fixit' ),
						__( 'Ctrl+K ilə hər yerdən axtarış; süzgəcləri saxlayıb komanda ilə paylaşmaq', 'fixit' ),
						__( 'Daxili qeyd sarı fonla seçilir, səhvən müştəriyə getmir', 'fixit' ),
						__( 'Həmkarınız eyni sorğuya yazanda göndərmədən əvvəl xəbərdarlıq', 'fixit' ),
						__( 'Toplu əməliyyatlar, «İzlə» və təkrar sorğuların birləşdirilməsi', 'fixit' ),
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
						__( '«SLA yaxınlaşır»: son müddətə 3 saatdan az qalan sorğular bir yerdə', 'fixit' ),
						__( '«Əgər-onda» qaydaları: sorğunu doğru komandaya ötürmək, gözləyəni bağlamaq', 'fixit' ),
					),
				),
				array(
					'icon'  => 'chart',
					'title' => __( 'Vaxt uçotu və hesabat', 'fixit' ),
					'items' => array(
						__( 'Hər ticket üzrə sərf olunan vaxtın qeydi', 'fixit' ),
						__( 'Aylıq hesabat: şirkət və agent üzrə', 'fixit' ),
						__( 'Fakturaya düşən vaxt ayrıca göstərilir', 'fixit' ),
						__( '14 hazır hesabat: həll müddəti, iş yükü, timesheet, Gantt; hər biri bir kliklə dashboard-a', 'fixit' ),
					),
				),
				array(
					'icon'  => 'users',
					'title' => __( 'İdarəetmə', 'fixit' ),
					'items' => array(
						__( 'Windows (Active Directory) hesabı ilə giriş, rol AD qrupundan gəlir', 'fixit' ),
						__( 'Rollar və icazələr: ayrı-ayrı seçilən hüquqlar', 'fixit' ),
						__( 'Komandalar və görünürlük: kim hansı sorğu və tapşırıqları görür', 'fixit' ),
						__( 'Hazır cavablar: mətn, status və teq bir kliklə', 'fixit' ),
					),
				),
				array(
					'icon'  => 'server',
					'title' => __( 'Sistem', 'fixit' ),
					'items' => array(
						__( 'İnterfeysdən ehtiyat nüsxə: baza və fayllar bir arxivdə', 'fixit' ),
						__( 'Android tətbiqi: sahədə cavab, şəkil və vaxt uçotu, bildirişlər öz serverinizdən', 'fixit' ),
						__( 'FIXIT Remote ilə: sorğudan istifadəçinin kompüterinə bir kliklə qoşulma', 'fixit' ),
						__( 'Mövcud helpdesk sistemindən köçürmə (məs. Zammad)', 'fixit' ),
					),
				),
			),

			'flow_title' => __( 'Müraciət necə işlənir', 'fixit' ),
			'flow'       => array(
				array( 'mail', __( 'Müraciət gəlir', 'fixit' ), __( 'e-poçt, portal, Telegram və ya veb forma', 'fixit' ) ),
				array( 'ticket', __( 'Ticket yaranır', 'fixit' ), __( 'nömrə alır, doğru şirkətə və komandaya düşür', 'fixit' ) ),
				array( 'users', __( 'Komanda işləyir', 'fixit' ), __( 'təyinat, SLA, lazım olsa — tapşırıq', 'fixit' ) ),
				array( 'send', __( 'Cavab gedir', 'fixit' ), __( 'müştəri cavabı yazdığı kanalda alır', 'fixit' ) ),
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
				__( 'Proqram və verilənlər bazası öz serverinizdə və ya bizim buludda', 'fixit' ),
				__( 'İki mərhələli giriş (2FA) — autentifikator tətbiqi ilə', 'fixit' ),
				__( 'Parol təxmininə qarşı qoruma: səhv cəhdlərdə IP müvəqqəti bloklanır', 'fixit' ),
				__( 'Hərəkətsiz qalan brauzerdən avtomatik çıxış, müddəti admin seçir', 'fixit' ),
				__( 'Mail qəbulu siyasəti: icazəli domenlər və qara siyahı', 'fixit' ),
				__( 'Girişlər və ayar dəyişiklikləri audit jurnalına, sorğudakı hər dəyişiklik isə onun tarixçəsinə yazılır', 'fixit' ),
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
				array(
					__( 'Mobil tətbiq varmı?', 'fixit' ),
					__( 'Bəli, FIXIT Ticket Android tətbiqi. Mütəxəssis sahədə sorğuya cavab yazır, şəkil əlavə edir, vaxtı qeyd edir. Tətbiq birbaşa şirkətinizin serverinə qoşulur, bildirişlər də oradan gəlir.', 'fixit' ),
				),
			),
		),

		/* ======================================================
		   Fixit Remote
		   ====================================================== */
		'remote'        => array(
			'tagline'  => __( 'Uzaqdan dəstək və kompüter parkının idarəsi: öz serverinizdə və ya bizim buludda', 'fixit' ),
			'badges'   => array(
				__( 'Öz serverinizdə və ya buludda', 'fixit' ),
				__( 'Windows və Linux', 'fixit' ),
				__( 'Operator sayı məhdud deyil', 'fixit' ),
				__( 'Tam jurnal', 'fixit' ),
			),

			/*
			 * Ekran görüntüləri: assets/img/products/remote/, remote-demo.fixit.az-dan
			 * çəkilib (tools/remote-ekranlar.py). Birincisi həm də başlıqdakı şəkildir.
			 */
			'screens'  => array(
				array( '01-ekran.webp', __( 'Ekran pəncərəsi: idarə, qısayollar, yazışma, ekran şəkli və video yazısı', 'fixit' ) ),
				array( '02-cihazlar.webp', __( 'Bütün kompüterlər bir siyahıda: sahib, vəziyyət, agent versiyası və bir kliklə qoşulma', 'fixit' ) ),
				array( '03-idare-lovhesi.webp', __( 'İdarə lövhəsi: onlayn cihazlar, sessiyalar, xəbərdarlıqlar və serverin vəziyyəti', 'fixit' ) ),
				array( '04-bildirisler.webp', __( 'Ekran bildirişləri: kompüterlərə elan və ya sahib məlumatı forması, cavabların hesabatı', 'fixit' ) ),
				array( '05-istifadeciler.webp', __( 'İstifadəçilər: rol, cihaz əhatəsi, iki mərhələli giriş və əməliyyat icazələri', 'fixit' ) ),
				array( '06-yazisma.webp', __( 'Yazışma: ekrana qoşulmadan işçi ilə çat', 'fixit' ) ),
				array( '07-sessiyalar.webp', __( 'Sessiyalar: kim kimə, nə qədər qoşulub; CSV və PDF', 'fixit' ) ),
				array( '08-komandalar.webp', __( 'Komandalar: kim işdədir, hansı kompüterdə', 'fixit' ) ),
			),

			'problems' => array(
				array(
					'icon'  => 'lock',
					'title' => __( 'Ekran başqasının serverindən keçir', 'fixit' ),
					'text'  => __( 'Adi uzaqdan giriş proqramlarında əlaqə xarici şirkətin serverləri üzərindən qurulur. Fixit Remote-u öz serverinizdə qurduqda ekran, fayllar və jurnal şirkətdən çıxmır; istəsəniz bizim buludda da işlədə bilərsiniz.', 'fixit' ),
				),
				array(
					'icon'  => 'network',
					'title' => __( 'Port açmaq, VLAN problemi', 'fixit' ),
					'text'  => __( 'Agent serverə özü qoşulur: işçi kompüterlərində port açmaq lazım deyil, fərqli VLAN-lar problem yaratmır.', 'fixit' ),
				),
				array(
					'icon'  => 'wrench',
					'title' => __( 'Hər kompüterə ayrıca gedirsiniz', 'fixit' ),
					'text'  => __( 'Skript, proqram və yeniləmə yüzlərlə kompüterə bir dəfəyə gedir, nəticə hər kompüter üçün ayrıca görünür.', 'fixit' ),
				),
				array(
					'icon'  => 'search',
					'title' => __( 'Kim kimə qoşulub, bilinmir', 'fixit' ),
					'text'  => __( 'Hər sessiya, fayl ötürmə və əmr jurnala yazılır; CSV və PDF kimi yüklənir.', 'fixit' ),
				),
			),

			'groups'   => array(
				array(
					'icon'  => 'computer',
					'title' => __( 'Uzaqdan dəstək', 'fixit' ),
					'items' => array(
						__( 'Ekrana baxmaq və idarə etmək: çoxmonitor, AZ / RU / EN klaviatura düzümü', 'fixit' ),
						__( 'Tam idarə və ya yalnız baxış; kilid ekranı və admin pəncərələri ilə işləyir', 'fixit' ),
						__( 'Fayl ötürmə və mübadilə buferi ekranı dayandırmadan', 'fixit' ),
						__( 'Bir sessiyaya bir neçə operator, sessiyanın video yazısı', 'fixit' ),
						__( 'Əlaqə qısa müddətə kəsiləndə sessiya özü bərpa olunur', 'fixit' ),
					),
				),
				array(
					'icon'  => 'server',
					'title' => __( 'Cihazlar və inventar', 'fixit' ),
					'items' => array(
						__( 'Bütün kompüterlər bir siyahıda: sahib, IP, əməliyyat sistemi, agent versiyası', 'fixit' ),
						__( 'Avadanlıq və proqram inventarı: disklər, şəbəkə, quraşdırılmış proqramlar', 'fixit' ),
						__( 'Cihaz profili: sağlamlıq, proqramlar, sessiyalar və jurnal bir yerdə', 'fixit' ),
						__( 'Qruplar, teqlər, toplu əməliyyatlar; minlərlə kompüterlə sürətli işləyir', 'fixit' ),
					),
				),
				array(
					'icon'  => 'wrench',
					'title' => __( 'System Manager və skriptlər', 'fixit' ),
					'items' => array(
						__( 'Ekrana qoşulmadan proseslər, xidmətlər, sistem jurnalı və aktiv istifadəçilər', 'fixit' ),
						__( 'PowerShell, CMD və Bash skript kitabxanası, versiyalar və risk yoxlaması', 'fixit' ),
						__( 'Dörd göz qaydası: istəsəniz skript ikinci şəxs təsdiqləməmiş işə düşmür', 'fixit' ),
						__( 'İşlər cədvəllə və qruplar üzrə; nəticə hər kompüter üçün ayrıca', 'fixit' ),
					),
				),
				array(
					'icon'  => 'download',
					'title' => __( 'Proqram yayımı və yeniləmələr', 'fixit' ),
					'items' => array(
						__( 'MSI və EXE kataloqu, imza və hash yoxlaması; Linux paketləri', 'fixit' ),
						__( 'Dalğalarla yayım: vaxt pəncərəsi, xəta həddi, təsdiq və geri qaytarma', 'fixit' ),
						__( 'Windows Update və Linux (apt, dnf) yeniləmələrinin mərkəzdən quraşdırılması', 'fixit' ),
						__( 'Hansı proqramın hansı kompüterdə olduğu bir siyahıda', 'fixit' ),
					),
				),
				array(
					'icon'  => 'activity',
					'title' => __( 'Monitorinq və xəbərdarlıqlar', 'fixit' ),
					'items' => array(
						__( 'Kompüterlərin sağlamlığı: yük, disk, şəbəkə, yenidən başlatma ehtiyacı', 'fixit' ),
						__( 'Qaydalar: şərt, müddət və ciddilik; problem davam edəndə xəbərdarlıq', 'fixit' ),
						__( 'Xəbərdarlıq mərkəzi: təsdiq, həll və tarixçə', 'fixit' ),
						__( 'Uzun müddət qoşulmayan və köhnə agentli kompüterlər ayrıca görünür', 'fixit' ),
					),
				),
				array(
					'icon'  => 'users',
					'title' => __( 'Komandalar və icazələr', 'fixit' ),
					'items' => array(
						__( 'Rollar: administrator, menecer, operator, yalnız baxış, rəhbər', 'fixit' ),
						__( 'Əməliyyat icazələri: kim qoşula, fayl ötürə, skript işlədə bilər', 'fixit' ),
						__( 'Qruplar: kim hansı kompüterlərə çatır', 'fixit' ),
						__( 'Şöbə, komanda və əməkdaş; kim işdədir, rəhbər bir baxışda görür', 'fixit' ),
					),
				),
				array(
					'icon'  => 'network',
					'title' => __( 'Giriş və inteqrasiyalar', 'fixit' ),
					'items' => array(
						__( 'Active Directory / LDAP və SSO: Entra ID, Okta, Keycloak, Google', 'fixit' ),
						__( 'FIXIT Sorğu Sistemi: sorğudan kompüterə bir kliklə, agentdən yeni sorğu', 'fixit' ),
						__( 'API və webhooklar: öz sistemlərinizə qoşmaq üçün', 'fixit' ),
						__( 'Ekran bildirişləri: kompüterlərə elan və sahib məlumatı forması', 'fixit' ),
					),
				),
				array(
					'icon'  => 'database',
					'title' => __( 'Server və baxım', 'fixit' ),
					'items' => array(
						__( 'Linux serverdə bir əmrlə quraşdırma: Ubuntu, Debian, RHEL ailəsi', 'fixit' ),
						__( 'Gündəlik ehtiyat nüsxə, bir əmrlə bərpa, lazım olsa təmiz maşında', 'fixit' ),
						__( 'Windows MSI-si GPO, Intune və ya SCCM ilə səssiz yayılır', 'fixit' ),
						__( 'Server internetsiz də tam işləyir: panel kənar xidmətə müraciət etmir', 'fixit' ),
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
			),

			'flow_title' => __( 'Necə işləyir', 'fixit' ),
			'flow'       => array(
				array( 'computer', __( 'Agent', 'fixit' ), __( 'Windows və Linux kompüterdə, serverə özü qoşulur', 'fixit' ) ),
				array( 'server', __( 'Fixit Remote server', 'fixit' ), __( 'sizin şəbəkənizdə: cihazlar, icazələr, jurnal', 'fixit' ) ),
				array( 'users', __( 'Operator', 'fixit' ), __( 'Client proqramı və ya telefondan işləyir', 'fixit' ) ),
				array( 'shield', __( 'İstifadəçi', 'fixit' ), __( 'icazə verir və qoşulmanı ekranda görür', 'fixit' ) ),
			),

			// Açıq demo: remote-demo.fixit.az (Fixit Remote repo-sunda demo-server/).
			// Giriş səhifəsinə aparır - rol orada bir kliklə seçilir və ?lang=
			// orada işləyir (kök ünvan girişə yönləndirəndə dil itərdi).
			'demo'     => array(
				'mode'  => 'open',
				'url'   => 'https://remote-demo.fixit.az/login.html',
				'text'  => __( 'Demoda nümunə şirkətin serveri açılır: 39 kompüter, komandalar, son 30 günün sessiyaları və jurnal. Qeydiyyat yoxdur, rolu seçib dərhal baxa bilərsiniz.', 'fixit' ),
				'note'  => __( 'Demoda dəyişikliklər saxlanmır və real kompüter yoxdur: "Qoşul" düyməsi nümunə ekran açır. Məlumat hər gecə yenilənir.', 'fixit' ),
				'steps' => array(
					__( 'Demonu açın, qeydiyyat tələb olunmur', 'fixit' ),
					__( 'Rol seçin: Administrator, Menecer, Operator və ya Rəhbər', 'fixit' ),
					__( 'Kompüterlərə, sessiyalara və jurnala baxın, qoşulmağı sınayın', 'fixit' ),
				),
				'roles' => array(
					array( 'shield', __( 'Administrator', 'fixit' ), __( 'cihazlar, istifadəçilər və ayarlar', 'fixit' ) ),
					array( 'users', __( 'Menecer', 'fixit' ), __( 'helpdesk hesabları və qoşulma', 'fixit' ) ),
					array( 'support', __( 'Operator', 'fixit' ), __( 'yalnız öz qruplarının kompüterləri', 'fixit' ) ),
					array( 'chart', __( 'Rəhbər', 'fixit' ), __( 'komandalar və kim işdədir', 'fixit' ) ),
				),
			),

			'security' => array(
				__( 'Qoşulmazdan əvvəl istifadəçidən icazə soruşulur; 30 saniyə cavab yoxdursa rədd', 'fixit' ),
				__( 'Ekranda gizlədilə bilməyən «qoşulma var» zolağı', 'fixit' ),
				__( 'Agent və Client serverin sertifikat izinə bağlıdır: araya girən başqa server qəbul olunmur', 'fixit' ),
				__( 'İki mərhələli giriş, SSO və domen hesabı ilə giriş', 'fixit' ),
				__( 'Skriptlər və proqram yayımı üçün ikinci şəxsin təsdiqi tələb oluna bilər', 'fixit' ),
				__( 'İstifadəçi yerində olmayanda mərkəzdən idarə olunan qoşulma parolu; hər istifadə jurnala yazılır', 'fixit' ),
				__( 'Səhv parol cəhdlərində IP bloklanır, hesab yox', 'fixit' ),
			),

			'faq'      => array(
				array(
					__( 'AnyDesk və ya TeamViewer-dən əsas fərqi nədir?', 'fixit' ),
					__( 'Fixit Remote serveri sizin şəbəkənizdə işləyir, bağlantı xarici xidmətlərdən keçmir. Uzaqdan dəstəkdən əlavə kompüter parkını da idarə edir: inventar, skriptlər, proqram yayımı, monitorinq. Kimin hansı kompüterə qoşula biləcəyini mərkəzdən təyin edirsiniz, hər sessiyanı jurnalda görürsünüz.', 'fixit' ),
				),
				array(
					__( 'Qiymət necə hesablanır?', 'fixit' ),
					__( 'İdarə olunan kompüterlərin sayına görə. Operator sayı məhdud deyil, bütün imkanlar hər paketdədir. Paketlər bu səhifənin «Qiymətlər» bölməsindədir, 14 günlük pulsuz sınaq var. Lisenziya müqavilə ilə rəsmiləşdirilir, ödəniş bank köçürməsi ilə.', 'fixit' ),
				),
				array(
					__( 'İşçi kompüterlərində port açmaq lazımdırmı?', 'fixit' ),
					__( 'Xeyr. Agent serverə özü qoşulur. Kompüterlər fərqli VLAN və ya alt şəbəkələrdə ola bilər, yalnız server əlçatan olmalıdır.', 'fixit' ),
				),
				array(
					__( 'İşçi bilmədən kompüterinə qoşulmaq olarmı?', 'fixit' ),
					__( 'Xeyr. Qoşulma zamanı ekranda gizlədilə bilməyən zolaq görünür. İcazə sorğusu açıqdırsa, istifadəçi təsdiq etmədən sessiya başlamır. Qoşulma parolu istifadə olunanda da bu, çatda və jurnalda qeyd olunur.', 'fixit' ),
				),
				array(
					__( 'Hansı əməliyyat sistemlərində işləyir?', 'fixit' ),
					__( 'Agent Windows və Linux kompüterlərində işləyir. Server Linux-da qurulur: Ubuntu, Debian və ya RHEL ailəsi. Operator Windows üçün Client proqramı və ya mobil tətbiqlə işləyir.', 'fixit' ),
				),
				array(
					__( 'Sınaqdan sonra nə olur?', 'fixit' ),
					__( 'Lisenziya açarını paneldə yazırsınız və eyni server işləməyə davam edir: kompüterlər, istifadəçilər və jurnal olduğu kimi qalır. Lisenziya yazılmasa, sınaq bitəndə panel bağlanır, kompüterlər isə serverə qoşulu qalır.', 'fixit' ),
				),
			),
		),

		/* ======================================================
		   FIXIT Şəbəkə Monitorinqi
		   ====================================================== */
		'sebeke-monitorinqi' => array(
			'tagline'  => __( 'Kəsintini müştəridən əvvəl siz bilin', 'fixit' ),
			'badges'   => array(
				__( 'Öz serverinizdə və ya buludda', 'fixit' ),
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
				__( 'Proqram və məlumat öz serverinizdə və ya bizim buludda', 'fixit' ),
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
