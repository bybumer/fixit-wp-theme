<?php
/**
 * İlk aktivləşdirmədə səhifələri, menyunu, xidmətləri və rəyləri yaradır.
 *
 * Yalnız bir dəfə işləyir. Təkrar işlətmək üçün:
 * Görünüş → temanı deaktiv/aktiv etmək kifayət etmir —
 * `fixit_demo_installed` opsiyasını silmək lazımdır.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Başlanğıc mətnlərin revizyası.
 * Mətni dəyişəndə bu rəqəmi artırın — toxunulmamış məzmun yenilənir.
 */
define( 'FIXIT_SEED_REV', 5 );

/**
 * Quraşdırmanı icra edir.
 *
 * Bütün yaradıcı funksiyalar təkrar-təhlükəsizdir: mövcud olanı ikinci dəfə
 * yaratmır. Ona görə bu funksiyanı istənilən vaxt yenidən çağırmaq olar.
 */
function fixit_run_setup() {
	fixit_create_pages();
	fixit_create_services();
	fixit_create_products();
	fixit_refresh_seeded_products();
	fixit_fix_encoded_slugs();
	fixit_create_reviews();
	fixit_create_menu();

	flush_rewrite_rules();
	update_option( 'fixit_demo_installed', FIXIT_VERSION );
	update_option( 'fixit_seed_rev', FIXIT_SEED_REV );
}

/**
 * İlk quraşdırma.
 *
 * `after_switch_theme` hadisəsinə güvənmirik: o, tema hələ yüklənməmiş
 * işləyir və keşləmə (Hostinger, LiteSpeed və s.) səbəbindən ümumiyyətlə
 * işə düşməyə bilər. Ona görə yoxlamanı `init`-də aparırıq — burada
 * xüsusi məzmun növləri artıq qeydiyyatdan keçib.
 *
 * `add_option()` mövcud opsiya üçün false qaytarır — bu, eyni anda gələn
 * iki sorğunun məzmunu iki dəfə yaratmasının qarşısını alır.
 */
function fixit_maybe_install() {
	$installed = get_option( 'fixit_demo_installed' );

	// Mətn revizyası tema versiyasından asılı olmadan da yenilənə bilməlidir.
	$seed_current = ( (int) get_option( 'fixit_seed_rev' ) === FIXIT_SEED_REV );

	if ( FIXIT_VERSION === $installed && $seed_current ) {
		return; // Hər şey yerindədir.
	}

	if ( ! $installed ) {
		// İlk quraşdırma — eyni anda gələn sorğular üçün atomik qıfıl.
		if ( ! add_option( 'fixit_demo_installed', 'running', '', false ) ) {
			return;
		}
	}

	// Köhnə versiyadan yenilənmə: yeni bölmələr (məs. Məhsullar) yaradılır.
	// Yaradıcı funksiyalar təkrar-təhlükəsizdir, mövcud məzmuna toxunmur.
	fixit_run_setup();
}
add_action( 'init', 'fixit_maybe_install', 20 );

/* ============================================================
   1. Səhifələr
   ============================================================ */
function fixit_create_pages() {
	$pages = array(
		'ana-sehife' => array(
			'title'    => 'Ana səhifə',
			'template' => '',
		),
		'xidmetler'  => array(
			'title'    => 'Xidmətlər',
			'template' => 'template-services.php',
		),
		'mehsullar'  => array(
			'title'    => 'Məhsullar',
			'template' => 'template-products.php',
		),
		'haqqimizda' => array(
			'title'    => 'Haqqımızda',
			'template' => 'template-about.php',
		),
		'uzaqdan-destek' => array(
			'title'    => 'Uzaqdan dəstək',
			'template' => 'template-remote.php',
		),
		'elaqe'      => array(
			'title'    => 'Əlaqə',
			'template' => 'template-contact.php',
		),
	);

	$ids = array();

	foreach ( $pages as $slug => $data ) {
		$existing = get_page_by_path( $slug );

		if ( $existing ) {
			$id = $existing->ID;
		} else {
			$id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $data['title'],
					'post_name'    => $slug,
					'post_content' => '',
				)
			);
		}

		if ( $id && ! is_wp_error( $id ) ) {
			if ( $data['template'] ) {
				update_post_meta( $id, '_wp_page_template', $data['template'] );
			}
			$ids[ $slug ] = $id;
		}
	}

	// Statik ana səhifəni təyin et.
	if ( isset( $ids['ana-sehife'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['ana-sehife'] );
	}

	update_option( 'fixit_page_ids', $ids );
}

/* ============================================================
   2. Xidmətlər
   ============================================================ */
function fixit_create_services() {
	if ( ! empty( fixit_get_services( 1 ) ) ) {
		return; // Artıq var.
	}

	$services = array(
		array(
			'title' => 'Kompüter Servisi',
			'icon'  => 'computer',
			'short' => 'Kompüter avadanlıqlarının diaqnostikası və təmiri sahəsində peşəkar xidmət göstəririk. Müştərilərimizin texnoloji problemlərini operativ həll edirik.',
			'chips' => 'Pulsuz diaqnostika, Notbuk, Desktop, Server',
			'feat'  => "Tam pulsuz və detallı ilkin analiz\nOriginal və zəmanətli ehtiyat hissələri\nQısa müddətdə təhvil\nProfilaktika və təmizləmə xidməti",
			'body'  => 'Kompüterlərin təmiri və servisi üzrə peşəkar komandamıza etibar edə bilərsiniz. Cihazınız tam pulsuz şəkildə detallı analiz edilir, problemin səbəbi və təklif olunan həll sizə aydın şəkildə izah olunur. Uyğun servis xidmətini qəbul etdikdən sonra qısa müddət ərzində cihazınız təhvil verilir.',
		),
		array(
			'title' => 'Video Müşahidə Sistemləri',
			'icon'  => 'camera',
			'short' => 'Təhlükəsizlik və nəzarət üçün müasir video müşahidə sistemləri təklif edirik. Hər bir həllimiz yüksək keyfiyyət və davamlılıq təmin edir.',
			'chips' => 'NVR, DVR, XVR, IP kamera, Uzaqdan baxış',
			'feat'  => "Obyektin yerində ölçülməsi və layihələndirilməsi\nNVR / DVR / XVR sistemlərinin quraşdırılması\nDünyanın istənilən yerindən canlı baxış\nArxiv müddətinin ehtiyaca uyğun planlanması",
			'body'  => 'Müxtəlif istehsalçılara və kateqoriyalara (NVR, DVR, XVR) aid kamera sistemlərinin layihələndirilməsi və quraşdırılması həyata keçirilir. Siz dünyanın istənilən yerindən kameralarınıza qoşulub canlı müşahidə apara, arxivə baxa və hadisə bildirişləri ala bilərsiniz.',
		),
		array(
			'title' => 'İT Dəstək Xidməti',
			'icon'  => 'support',
			'short' => 'Müəssisənizin İT infrastrukturu üçün hərtərəfli dəstək və həllər təqdim edirik. Komandamızla texnoloji prosesləriniz daha təhlükəsiz və effektiv olacaq.',
			'chips' => 'Outsource İT, Uzaqdan dəstək, Yerində dəstək, Aylıq audit',
			'feat'  => "Şirkətin İT infrastrukturunun tam yoxlanılması\nMövcud problemlərin sistemli şəkildə həlli\nUzaqdan və yerində dəstək\nAyda bir dəfə ümumi texniki yoxlanış\nİT xərclərinin analizi və optimallaşdırılması",
			'body'  => 'İT Dəstək xidmətimizdən yararlandıqda ilk öncə şirkətinizin infrastrukturu tam şəkildə yoxlanılır və o ana qədər yığılmış problemlər həll edilir. Sonrasında uzaqdan və yerində dəstək, ayda bir dəfə ümumi yoxlanış, həmçinin İT xərclərinin analizi və optimallaşdırılması təmin olunur.',
		),
		array(
			'title' => 'IP Telefoniya',
			'icon'  => 'phone',
			'short' => 'Səmərəli kommunikasiya üçün IP telefon sistemləri ilə ofis əlaqələrini asanlaşdırırıq. Həllərimizlə iş prosesiniz daha çevik və məhsuldar olacaq.',
			'chips' => '3CX, Yeastar, IVR, Konfrans zəngi',
			'feat'  => "Zənglərin mərkəzləşdirilmiş idarə olunması\nAvtomatlaşdırılmış cavab sistemləri (IVR)\nVideo konfrans və qrup zəngləri\nBulud və ya lokal server seçimi\nZəng qeydiyyatı və hesabatlar",
			'body'  => 'Biz müasir IP telefoniya həlləri ilə müəssisəniz üçün effektiv və sərfəli kommunikasiya sistemləri qururuq. Əsasən 3CX və Yeastar platformalarından istifadə edərək zəng idarəetməsi, avtomatlaşdırılmış cavab sistemləri, konfrans zəngləri və digər qabaqcıl funksiyaları təmin edirik. 3CX yüksək səviyyədə inteqrasiya olunan, bulud əsaslı və ya lokal serverlərdə işləyə bilən çevik platformadır; Yeastar həlləri isə kiçik və orta bizneslər üçün optimal seçimdir.',
		),
		array(
			'title' => 'Korporativ Mail Sistemləri',
			'icon'  => 'mail',
			'short' => 'Elektron poçt xidmətlərimiz ilə məlumat mübadiləsini təhlükəsiz və operativ təmin edirik. E-poçt sistemlərinizin tam idarəetmə dəstəyi ilə xidmət göstəririk.',
			'chips' => 'Kerio, Şəxsi domen, Spam filtri, Arxivləşdirmə',
			'feat'  => "Şirkət domeni üzərindən peşəkar e-poçt ünvanları\nMəlumatların şifrələnməsi və spam filtrasiyası\nMobil cihazlarla tam uyğunluq\nAvtomatik yedəkləmə və arxivləşdirmə",
			'body'  => 'Korporativ mail xidməti müəssisələrin işçiləri arasında effektiv və təhlükəsiz ünsiyyət qurulmasını təmin edən əsas vasitədir. Şirkət adından göndərilən peşəkar e-poçtlar müştərilər və tərəfdaşlarla etibarlı əlaqə yaradır. Kerio Mail Server kimi həllər mobil cihazlar və müxtəlif e-poçt müştəriləri ilə uyğunluq təmin edir, avtomatik yedəkləmə və arxivləşdirmə isə məlumat itkisi riskini minimuma endirir.',
		),
		array(
			'title' => 'Bulud Serverlər',
			'icon'  => 'cloud',
			'short' => 'Müasir texnologiyalarla təchiz edilmiş bulud serverlər təhlükəsiz məlumat saxlama imkanı təqdim edir. Yüksək sürətli və dayanıqlı xidmət təmin edilir.',
			'chips' => 'Miqyaslanma, Yedəkləmə, Uzaqdan idarəetmə',
			'feat'  => "Elastiklik: resursları artırıb-azaltmaq imkanı\nSərfəli həll: fiziki avadanlıq xərci yoxdur\nAvtomatik yedəkləmə və məlumat bərpası\nHər cihazdan təhlükəsiz uzaqdan giriş",
			'body'  => 'Bulud serverləri məlumatların saxlanması və idarə olunması üçün ən müasir texnologiyaları təklif edən həllərdir. Ənənəvi fiziki serverlərdən fərqli olaraq bulud serverləri internet üzərindən idarə olunur — bu da istifadəçilərə daha çox çeviklik və səmərəlilik qazandırır.',
		),
	);

	$order = 0;
	foreach ( $services as $svc ) {
		++$order;

		$id = wp_insert_post(
			array(
				'post_type'    => 'fixit_service',
				'post_status'  => 'publish',
				'post_title'   => $svc['title'],
				'post_content' => $svc['body'],
				'post_excerpt' => $svc['short'],
				'menu_order'   => $order,
			)
		);

		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_fixit_icon', $svc['icon'] );
			update_post_meta( $id, '_fixit_short', $svc['short'] );
			update_post_meta( $id, '_fixit_features', $svc['feat'] );
			update_post_meta( $id, '_fixit_chips', $svc['chips'] );
		}
	}
}

/* ============================================================
   3. Məhsullar (öz proqram təminatımız)
   ============================================================ */

/**
 * Məhsulların başlanğıc mətnləri.
 *
 * Mətn dəyişdikdə FIXIT_SEED_REV artırılır — onda admin paneldə
 * ƏL İLƏ redaktə edilməmiş məhsullar yeni mətnlə yenilənir.
 * Siz mətni admin paneldən dəyişsəniz, o məhsula bir daha toxunulmur.
 *
 * `slug` — səhifənin ünvanı: /mehsul/<slug>/ (inc/product-data.php ilə eyni olmalıdır).
 */
function fixit_product_seed() {
	return array(
		array(
			'title' => 'FIXIT Sorğu Sistemi',
			'slug'  => 'sorgu-sistemi',
			'order' => 1,
			'icon'  => 'ticket',
			'short' => 'Müraciətləri e-poçt, müştəri portalı və daxili kanallardan bir mərkəzdə toplayan helpdesk sistemi. Tam öz kodumuzdur, kənar sistemə bağlılıq yoxdur.',
			'chips' => 'On-premise, E-poçt inteqrasiyası, Müştəri portalı, Rol icazələri, Azərbaycan dilində',
			'feat'  => implode(
				"\n",
				array(
					'Ticket yaradılması, təyinatı və statuslar; rol və qrup əsaslı icazələr',
					'E-poçt inteqrasiyası: gələn məktub avtomatik ticket olur, agentin cavabı müştəriyə mail kimi gedir',
					'Yazışma zəncirinin düzgün tanınması',
					'Müştəri portalı: qeydiyyat, e-poçt təsdiqi, parol bərpası, öz müraciətlərini izləmə',
					'Təhlükəsiz giriş: sessiyalar server tərəfdə saxlanılır',
					'İnterfeys və bildirişlər tam Azərbaycan dilindədir',
				)
			),
			'body'  => 'Şirkətə gələn bütün müraciətlərin bir yerdə toplandığı sistemdir. Müştəri istəsə e-poçt yazır, istəsə portala daxil olur — hər iki halda müraciət eyni ticketə düşür. Sistem tamamilə bizim kodumuzdur, ona görə şirkətinizin iş axınına uyğun dəyişikliklər etmək mümkündür.',
		),
		array(
			'title' => 'Fixit Remote',
			'slug'  => 'remote',
			'order' => 2,
			'icon'  => 'computer',
			'short' => 'Şirkət kompüterlərinə uzaqdan qoşulma, dəstək və idarəetmə sistemi. Sizin serverinizdə işləyir — bağlantı və jurnal şirkətdən kənara çıxmır.',
			'chips' => 'On-premise, Windows agent, Mobil tətbiq, Çat, Fayl ötürmə, Sessiya jurnalı',
			'feat'  => implode(
				"\n",
				array(
					'Ekrana baxmaq və idarə etmək — siçan və klaviatura',
					'Sessiya zamanı yazışma və fayl ötürmə',
					'Agent serverə özü qoşulur — kompüterlərdə port açmaq lazım deyil',
					'İstifadəçidən icazə soruşmaq və ekranda gizlədilə bilməyən qoşulma zolağı',
					'Hər sessiya jurnala yazılır; CSV və PDF kimi yüklənir',
					'Telefondan idarəetmə üçün mobil tətbiq',
				)
			),
			'body'  => 'Şirkət kompüterlərinə uzaqdan qoşulmaq, istifadəçiyə kömək etmək və kimin hansı kompüterə qoşulduğunu nəzarətdə saxlamaq üçün sistemdir. Server sizin şirkətinizdə qurulur, agentlər isə ona özü qoşulur — işçi kompüterlərində heç bir port açmaq lazım gəlmir.',
		),
		array(
			'title' => 'FIXIT Şəbəkə Monitorinqi',
			'slug'  => 'sebeke-monitorinqi',
			'order' => 3,
			'icon'  => 'activity',
			'short' => 'Cihazları, interfeysləri və VPN tunellərini fasiləsiz izləyən, kəsintini siz bilməmişdən əvvəl xəbər verən monitorinq və analitika aləti.',
			'chips' => 'On-premise, SNMP, FortiGate, Telegram, WhatsApp, E-poçt',
			'feat'  => implode(
				"\n",
				array(
					'Cihaz, interfeys və VPN tunellərinin fasiləsiz izlənməsi',
					'Uptime statistikası, kəsintilərin vaxt xətti və ən problemli nöqtələrin hesabatı',
					'Modulyar analitika lövhəsi: hər istifadəçi özünə lazım olan blokları düzür',
					'Bildiriş kanalları: Telegram, e-poçt (SMTP), WhatsApp və masaüstü — hər hadisə ayrıca açılıb-bağlanır',
				)
			),
			'body'  => 'Şəbəkənizdə nəyin nə vaxt və nə qədər müddət dayandığını dəqiq göstərən alətdir. Kəsinti baş verən kimi seçdiyiniz kanala bildiriş gedir, hadisə tarixçəyə yazılır və uptime hesabatında görünür.',
		),
	);
}

/**
 * Bir məhsulun meta sahələrini yazır.
 */
function fixit_apply_product_meta( $id, $prod ) {
	update_post_meta( $id, '_fixit_icon', $prod['icon'] );
	update_post_meta( $id, '_fixit_short', $prod['short'] );
	update_post_meta( $id, '_fixit_features', $prod['feat'] );
	update_post_meta( $id, '_fixit_chips', $prod['chips'] );
	update_post_meta( $id, '_fixit_seeded', FIXIT_SEED_REV );
}

/**
 * Çatışmayan başlanğıc məhsulları yaradır.
 *
 * Hər məhsul YALNIZ BİR DƏFƏ yaradılır: yaradılanlar `fixit_seeded_products`
 * opsiyasında qeyd olunur. Məhsulu admin paneldən silsəniz, geri gəlmir.
 */
function fixit_create_products() {
	$created = (array) get_option( 'fixit_seeded_products', array() );

	// Səbətdəkilər də daxil — silinmiş məhsulu təkrar yaratmayaq.
	$existing = array();
	$all      = get_posts(
		array(
			'post_type'      => 'fixit_product',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ),
			'posts_per_page' => -1,
		)
	);
	foreach ( $all as $p ) {
		$existing[ $p->post_title ] = true;
	}

	foreach ( fixit_product_seed() as $prod ) {
		if ( in_array( $prod['title'], $created, true ) ) {
			continue;
		}

		if ( isset( $existing[ $prod['title'] ] ) ) {
			$created[] = $prod['title']; // Əvvəlki versiyalarda yaradılıb.
			continue;
		}

		$id = wp_insert_post(
			array(
				'post_type'    => 'fixit_product',
				'post_status'  => 'publish',
				'post_title'   => $prod['title'],
				'post_name'    => $prod['slug'],
				'post_content' => $prod['body'],
				'post_excerpt' => $prod['short'],
				'menu_order'   => $prod['order'],
			)
		);

		if ( $id && ! is_wp_error( $id ) ) {
			fixit_apply_product_meta( $id, $prod );
			$created[] = $prod['title'];
		}
	}

	update_option( 'fixit_seeded_products', $created, false );
}

/**
 * Başlanğıc məhsulları güncəl saxlayır.
 *
 * 1) Ünvan: WordPress "ə" hərfini ünvanda çevirə bilmir və
 *    `fixit-s%c9%99b%c9%99k%c9%99-...` kimi ünvan yaradır. Avtomatik yaranmış
 *    ünvan təmiz ünvanla əvəz olunur (köhnə ünvan yenisinə yönləndirilir).
 *    Sizin əl ilə yazdığınız ünvana toxunulmur.
 * 2) Mətn: admin paneldə redaktə edilməmiş məhsullar yeni mətnlə yenilənir.
 */
function fixit_refresh_seeded_products() {
	$seed = array();
	foreach ( fixit_product_seed() as $prod ) {
		$seed[ $prod['title'] ] = $prod;
	}

	foreach ( fixit_get_products() as $post ) {
		if ( ! isset( $seed[ $post->post_title ] ) ) {
			continue; // Bizim başlanğıc məhsulumuz deyil.
		}

		$prod = $seed[ $post->post_title ];

		// 1) Ünvan.
		$auto_slug = sanitize_title( $post->post_title );
		$is_auto   = ( $post->post_name === $auto_slug ) || ( false !== strpos( $post->post_name, '%' ) );

		if ( $is_auto && $post->post_name !== $prod['slug'] ) {
			wp_update_post(
				array(
					'ID'        => $post->ID,
					'post_name' => $prod['slug'],
				)
			);
		}

		// 2) Mətn.
		if ( get_post_meta( $post->ID, '_fixit_user_edited', true ) ) {
			continue; // Əl ilə redaktə olunub — toxunmuruq.
		}

		// Nişanı olmayanlar köhnə versiyada yaradılıb — onlar da yenilənir.
		$seeded = get_post_meta( $post->ID, '_fixit_seeded', true );
		if ( '' !== $seeded && (int) $seeded >= FIXIT_SEED_REV ) {
			continue; // Onsuz da güncəldir.
		}

		wp_update_post(
			array(
				'ID'           => $post->ID,
				'post_content' => $prod['body'],
				'post_excerpt' => $prod['short'],
				'menu_order'   => $prod['order'],
			)
		);

		fixit_apply_product_meta( $post->ID, $prod );
	}
}

/**
 * Menyu bəndlərinin sıra nömrələrini ağac qaydasında düzür.
 *
 * WordPress menyunu düz siyahı kimi saxlayır: alt bənd valideynindən SONRA
 * gəlməlidir. Əks halda admin paneldəki menyu redaktoru alt bəndi səhv yerdə
 * göstərir və yadda saxlayanda quruluş pozulur. Görünən sıra dəyişmir.
 *
 * @param int $menu_id Menyu.
 */
function fixit_menu_normalize_order( $menu_id ) {
	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! $items ) {
		return;
	}

	/*
	 * Diqqət: wp_get_nav_menu_items() $item->menu_order dəyərini öz sıra
	 * nömrəsi (1, 2, 3…) ilə ƏVƏZ EDİR — bazadakı həqiqi dəyəri yox.
	 * Müqayisə üçün həqiqi dəyəri bazadan oxuyuruq.
	 */
	$children = array();
	foreach ( $items as $item ) {
		$item->fixit_db_order = (int) get_post_field( 'menu_order', $item->ID );
		$children[ (int) $item->menu_item_parent ][] = $item;
	}

	foreach ( $children as $parent => $list ) {
		usort(
			$list,
			function ( $a, $b ) {
				return ( $a->fixit_db_order - $b->fixit_db_order ) ?: ( (int) $a->ID - (int) $b->ID );
			}
		);
		$children[ $parent ] = $list;
	}

	$seq  = 0;
	$walk = function ( $parent ) use ( &$walk, &$seq, $children ) {
		if ( empty( $children[ $parent ] ) ) {
			return;
		}
		foreach ( $children[ $parent ] as $item ) {
			++$seq;
			if ( $item->fixit_db_order !== $seq ) {
				wp_update_post(
					array(
						'ID'         => $item->ID,
						'menu_order' => $seq,
					)
				);
			}
			$walk( (int) $item->ID );
		}
	};

	$walk( 0 );
}

/**
 * Menyuda "Məhsullar" bəndinin altına hər məhsulu əlavə edir (açılan menyu).
 *
 * - "Məhsullar" bəndi menyudan silinibsə, heç nə edilmir.
 * - Hər məhsul YALNIZ BİR DƏFƏ əlavə olunur (`fixit_menu_products`):
 *   menyudan çıxardığınız bənd geri gəlmir.
 *
 * @param int   $menu_id  Menyu.
 * @param array $page_ids Səhifə ID-ləri (slug => ID).
 */
function fixit_menu_add_products( $menu_id, $page_ids ) {
	if ( empty( $page_ids['mehsullar'] ) ) {
		return;
	}

	$items  = wp_get_nav_menu_items( $menu_id );
	$items  = $items ? $items : array();
	$parent = 0;
	$have   = array();

	foreach ( $items as $item ) {
		if ( 'post_type' === $item->type && 'page' === $item->object && (int) $item->object_id === (int) $page_ids['mehsullar'] ) {
			$parent = (int) $item->ID;
		}
		if ( 'fixit_product' === $item->object ) {
			$have[] = (int) $item->object_id;
		}
	}

	if ( ! $parent ) {
		return;
	}

	$added = array_map( 'intval', (array) get_option( 'fixit_menu_products', array() ) );
	$pos   = 0;

	foreach ( fixit_get_products() as $prod ) {
		++$pos;

		if ( in_array( (int) $prod->ID, $have, true ) || in_array( (int) $prod->ID, $added, true ) ) {
			continue;
		}

		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-object-id' => $prod->ID,
				'menu-item-object'    => 'fixit_product',
				'menu-item-type'      => 'post_type',
				'menu-item-parent-id' => $parent,
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $pos,
			)
		);

		$added[] = (int) $prod->ID;
	}

	update_option( 'fixit_menu_products', $added, false );
}

/* ============================================================
   4. Müştəri rəyləri
   ============================================================ */
function fixit_create_reviews() {
	if ( ! empty( fixit_get_reviews( 1 ) ) ) {
		return;
	}

	$reviews = array(
		array(
			'text'   => 'Fixit-in xidmətləri mükəmməl idi. Kompüterlərin təmiri sürətli və keyfiyyətli oldu.',
			'author' => 'Elçin Məmmədov',
			'city'   => 'Cəlilabad',
		),
		array(
			'text'   => 'Fixit-in kameraların quraşdırılması xidməti çox peşəkardır. Təhlükəsizlik sistemimiz indi daha etibarlıdır. Hər kəsə tövsiyə edirəm!',
			'author' => 'Fərid Əhmədov',
			'city'   => 'Bakı',
		),
	);

	$order = 0;
	foreach ( $reviews as $rev ) {
		++$order;

		$id = wp_insert_post(
			array(
				'post_type'    => 'fixit_review',
				'post_status'  => 'publish',
				'post_title'   => $rev['author'],
				'post_content' => $rev['text'],
				'menu_order'   => $order,
			)
		);

		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_fixit_author', $rev['author'] );
			update_post_meta( $id, '_fixit_city', $rev['city'] );
			update_post_meta( $id, '_fixit_rating', 5 );
		}
	}
}

/* ============================================================
   5. Menyu
   ============================================================ */
function fixit_create_menu() {
	$menu_name = 'Əsas menyu';
	$menu      = wp_get_nav_menu_object( $menu_name );

	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $menu_name );
	} else {
		$menu_id = $menu->term_id;
	}

	if ( is_wp_error( $menu_id ) ) {
		return;
	}

	/*
	 * Menyunu tamamlayırıq: mövcud bəndlərə toxunmuruq, yalnız
	 * siyahıda olmayan səhifələri əlavə edirik. Beləliklə tema
	 * yeniləndikdə yeni səhifə (məs. Məhsullar) menyuya özü düşür,
	 * sizin əl ilə etdiyiniz dəyişikliklər isə itmir.
	 */
	$items    = wp_get_nav_menu_items( $menu_id );
	$items    = $items ? $items : array();
	$existing = array();

	foreach ( $items as $item ) {
		if ( 'post_type' === $item->type ) {
			$existing[] = (int) $item->object_id;
		}
	}

	$page_ids = get_option( 'fixit_page_ids', array() );
	$order    = array( 'ana-sehife', 'xidmetler', 'mehsullar', 'uzaqdan-destek', 'haqqimizda', 'elaqe' );
	$pos      = count( $items );

	foreach ( $order as $slug ) {
		if ( empty( $page_ids[ $slug ] ) || in_array( (int) $page_ids[ $slug ], $existing, true ) ) {
			continue;
		}
		++$pos;
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-object-id' => $page_ids[ $slug ],
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $pos,
			)
		);
	}

	/*
	 * Sıranı düzəltmək — YALNIZ menyu bizim yaratdığımız kimi qalıbsa.
	 * İçində əlavə etdiyiniz bənd varsa sıraya toxunmuruq ki, sizin
	 * düzülüşünüz pozulmasın.
	 */
	$items    = wp_get_nav_menu_items( $menu_id );
	$items    = $items ? $items : array();
	$our_ids  = array_map( 'intval', array_values( $page_ids ) );
	$is_clean = true;

	foreach ( $items as $item ) {
		if ( 'post_type' !== $item->type || ! in_array( (int) $item->object_id, $our_ids, true ) ) {
			$is_clean = false;
			break;
		}
	}

	if ( $is_clean ) {
		$pos = 0;
		foreach ( $order as $slug ) {
			if ( empty( $page_ids[ $slug ] ) ) {
				continue;
			}
			++$pos;
			foreach ( $items as $item ) {
				if ( (int) $item->object_id === (int) $page_ids[ $slug ] ) {
					wp_update_nav_menu_item(
						$menu_id,
						$item->ID,
						array(
							'menu-item-object-id' => $item->object_id,
							'menu-item-object'    => $item->object,
							'menu-item-type'      => $item->type,
							'menu-item-status'    => 'publish',
							'menu-item-position'  => $pos,
						)
					);
					break;
				}
			}
		}
	}

	// "Məhsullar" altında hər məhsul — başlıqda açılan menyu.
	fixit_menu_add_products( $menu_id, $page_ids );
	fixit_menu_normalize_order( $menu_id );

	$locations            = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/* ============================================================
   6. Admin: əl ilə yenidən qurma
   ============================================================ */

/**
 * Görünüş menyusuna "FIXIT quraşdırma" səhifəsi əlavə edir.
 */
function fixit_setup_menu() {
	add_theme_page(
		__( 'FIXIT quraşdırma', 'fixit' ),
		__( 'FIXIT quraşdırma', 'fixit' ),
		'edit_theme_options',
		'fixit-setup',
		'fixit_setup_page'
	);
}
add_action( 'admin_menu', 'fixit_setup_menu' );

/**
 * Çatışmayan məzmunu sayır.
 */
function fixit_missing_items() {
	$missing = array();

	if ( ! fixit_get_services( 1 ) ) {
		$missing[] = __( 'Xidmətlər', 'fixit' );
	}
	if ( ! fixit_get_products( 1 ) ) {
		$missing[] = __( 'Məhsullar', 'fixit' );
	}
	if ( ! fixit_get_reviews( 1 ) ) {
		$missing[] = __( 'Müştəri rəyləri', 'fixit' );
	}
	if ( ! get_page_by_path( 'xidmetler' ) || ! get_page_by_path( 'elaqe' ) || ! get_page_by_path( 'mehsullar' ) ) {
		$missing[] = __( 'Səhifələr', 'fixit' );
	}
	if ( ! has_nav_menu( 'primary' ) ) {
		$missing[] = __( 'Menyu', 'fixit' );
	}

	return $missing;
}

/**
 * Quraşdırma səhifəsi.
 */
function fixit_setup_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$done = false;

	if ( isset( $_POST['fixit_setup_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fixit_setup_nonce'] ) ), 'fixit_run_setup' ) ) {
		fixit_run_setup();
		$done = true;
	}

	$missing = fixit_missing_items();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'FIXIT quraşdırma', 'fixit' ); ?></h1>

		<?php if ( $done ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'Hazırdır. Çatışmayan məzmun yaradıldı.', 'fixit' ); ?></p></div>
		<?php endif; ?>

		<p>
			<?php esc_html_e( 'Bu düymə temanın başlanğıc məzmununu yaradır: səhifələr (Ana səhifə, Xidmətlər, Haqqımızda, Əlaqə), 6 xidmət, müştəri rəyləri və əsas menyu.', 'fixit' ); ?>
		</p>
		<p>
			<strong><?php esc_html_e( 'Təhlükəsizdir:', 'fixit' ); ?></strong>
			<?php esc_html_e( 'mövcud məzmun silinmir və təkrarlanmır — yalnız çatışmayanlar yaradılır.', 'fixit' ); ?>
		</p>

		<h2><?php esc_html_e( 'Hazırkı vəziyyət', 'fixit' ); ?></h2>
		<?php if ( $missing ) : ?>
			<p style="color:#b32d2e">
				<?php esc_html_e( 'Çatışmayan bölmələr:', 'fixit' ); ?>
				<strong><?php echo esc_html( implode( ', ', $missing ) ); ?></strong>
			</p>
		<?php else : ?>
			<p style="color:#008a20"><?php esc_html_e( 'Hər şey yerindədir.', 'fixit' ); ?></p>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'fixit_run_setup', 'fixit_setup_nonce' ); ?>
			<p>
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Başlanğıc məzmunu yarat', 'fixit' ); ?>
				</button>
			</p>
		</form>

		<?php
		// Digər modullar (məs. tema yeniləmələri) bura öz blokunu əlavə edir.
		do_action( 'fixit_setup_page_after' );
		?>
	</div>
	<?php
}

/**
 * Məzmun çatışmırsa admin paneldə xəbərdarlıq göstərir.
 */
function fixit_setup_notice() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( $screen && 'appearance_page_fixit-setup' === $screen->id ) {
		return;
	}

	if ( ! fixit_missing_items() ) {
		return;
	}
	?>
	<div class="notice notice-warning">
		<p>
			<strong>FIXIT:</strong>
			<?php esc_html_e( 'Saytın başlanğıc məzmunu tam yaradılmayıb (xidmətlər, səhifələr və ya menyu çatışmır).', 'fixit' ); ?>
			<a href="<?php echo esc_url( admin_url( 'themes.php?page=fixit-setup' ) ); ?>">
				<?php esc_html_e( 'Bir kliklə düzəlt', 'fixit' ); ?>
			</a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'fixit_setup_notice' );
