<?php
/**
 * Bazadakı məzmunun hazır tərcümələri.
 *
 * Buradakı mətnlər tema yeniləndikdə saytdakı məhsul, xidmət və rəylərə
 * yazılır (admin paneldə əl ilə dəyişdirilənlərə toxunulmur).
 *
 * Açar — Azərbaycanca başlıq.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Səhifə, menyu və başlıq tərcümələri.
 *
 * @return array AZ mətn => tərcümə (hazırkı dildə).
 */
function fixit_title_translations() {
	$lang = fixit_lang();

	$map = array(
		// Səhifələr və menyu.
		'Ana səhifə'                    => array( 'en' => 'Home', 'ru' => 'Главная' ),
		'Xidmətlər'                     => array( 'en' => 'Services', 'ru' => 'Услуги' ),
		'Məhsullar'                     => array( 'en' => 'Products', 'ru' => 'Продукты' ),
		'Haqqımızda'                    => array( 'en' => 'About us', 'ru' => 'О нас' ),
		'Əlaqə'                         => array( 'en' => 'Contact', 'ru' => 'Контакты' ),
		'Uzaqdan qoşulma'               => array( 'en' => 'Remote access', 'ru' => 'Удалённый доступ' ),
		'Uzaqdan dəstək'                => array( 'en' => 'Remote support', 'ru' => 'Удалённая поддержка' ),
		'Fixit Remote — Məxfilik siyasəti' => array( 'en' => 'Fixit Remote — Privacy policy', 'ru' => 'Fixit Remote — Политика конфиденциальности' ),

		// Məhsullar.
		'FIXIT Sorğu Sistemi'           => array( 'en' => 'FIXIT Helpdesk', 'ru' => 'FIXIT Служба заявок' ),
		'Fixit Remote'                  => array( 'en' => 'Fixit Remote', 'ru' => 'Fixit Remote' ),
		'FIXIT Şəbəkə Monitorinqi'      => array( 'en' => 'FIXIT Network Monitoring', 'ru' => 'FIXIT Мониторинг сети' ),

		// Xidmətlər.
		'Kompüter Servisi'              => array( 'en' => 'Computer Service', 'ru' => 'Компьютерный сервис' ),
		'Video Müşahidə Sistemləri'     => array( 'en' => 'Video Surveillance Systems', 'ru' => 'Системы видеонаблюдения' ),
		'İT Dəstək Xidməti'             => array( 'en' => 'IT Support', 'ru' => 'IT-поддержка' ),
		'IP Telefoniya'                 => array( 'en' => 'IP Telephony', 'ru' => 'IP-телефония' ),
		'Korporativ Mail Sistemləri'    => array( 'en' => 'Corporate Mail Systems', 'ru' => 'Корпоративная почта' ),
		'Bulud Serverlər'               => array( 'en' => 'Cloud Servers', 'ru' => 'Облачные серверы' ),

		// Fərdiləşdir panelinin standart mətnləri.
		'Biznesiniz üçün <span>etibarlı İKT</span> infrastrukturu' => array(
			'en' => 'Reliable <span>ICT infrastructure</span> for your business',
			'ru' => 'Надёжная <span>ИКТ-инфраструктура</span> для вашего бизнеса',
		),
		'Kompüter servisi, video müşahidə, bulud serverlər, korporativ mail və IP telefoniya — bütün texnoloji ehtiyaclarınız üçün vahid tərəfdaş.' => array(
			'en' => 'Computer service, video surveillance, cloud servers, corporate mail and IP telephony — one partner for all your technology needs.',
			'ru' => 'Компьютерный сервис, видеонаблюдение, облачные серверы, корпоративная почта и IP-телефония — один партнёр для всех технологических задач.',
		),
		'Bakı, Nərimanov ray.'                   => array( 'en' => 'Baku, Narimanov district', 'ru' => 'Баку, Наримановский район' ),
		'Həftənin 5 günü, 09:00 – 18:00'         => array( 'en' => '5 days a week, 09:00 – 18:00', 'ru' => '5 дней в неделю, 09:00 – 18:00' ),
		'Cəlilabad ray., Heydər Əliyev pr. 284A' => array( 'en' => 'Jalilabad district, 284A Heydar Aliyev Ave.', 'ru' => 'Джалилабадский район, пр. Гейдара Алиева, 284A' ),
		'Həftənin 7 günü, 09:00 – 20:00'         => array( 'en' => '7 days a week, 09:00 – 20:00', 'ru' => '7 дней в неделю, 09:00 – 20:00' ),

		// Sayt adı və şüarı (Ayarlar → Ümumi).
		'FIXIT — Peşəkar İKT Xidmətləri'         => array( 'en' => 'FIXIT — Professional ICT Services', 'ru' => 'FIXIT — Профессиональные ИКТ-услуги' ),
		'Fixit – Informasiya Texnologiyalari'    => array( 'en' => 'Fixit – Information Technology', 'ru' => 'Fixit – Информационные технологии' ),
		'Fixit - Informasiya Texnologiyalari'    => array( 'en' => 'Fixit - Information Technology', 'ru' => 'Fixit - Информационные технологии' ),

		// Şəhərlər (rəylərdə).
		'Bakı'                          => array( 'en' => 'Baku', 'ru' => 'Баку' ),
		'Cəlilabad'                     => array( 'en' => 'Jalilabad', 'ru' => 'Джалилабад' ),
	);

	$out = array();
	foreach ( $map as $az => $translations ) {
		if ( isset( $translations[ $lang ] ) ) {
			$out[ $az ] = $translations[ $lang ];
		}
	}

	return $out;
}

/**
 * Məhsul, xidmət və rəylərin tərcümələri.
 *
 * @return array AZ başlıq => dil => sahələr.
 */
function fixit_content_translations() {
	return array(

		/* ==================== MƏHSULLAR ==================== */

		'FIXIT Sorğu Sistemi' => array(
			'en' => array(
				'title'    => 'FIXIT Helpdesk',
				'short'    => 'Customer requests (Ticket) and your team\'s internal work (Task) on one platform: email, portal, Telegram, SLA, board and time tracking. On your own server.',
				'chips'    => 'Ticket + Task, On-premise, Email and Telegram, Customer portal, SLA, Kanban board, Time tracking, Multilingual',
				'features' => implode(
					"\n",
					array(
						'Ticket: requests from email, the customer portal, Telegram and the web form in one place',
						'Task: your team\'s internal work — board, checklists, dependencies, recurring tasks',
						'SLA counted in business hours, time tracking and monthly reports',
						'Turn a request into a task in one click — the two stay linked',
						'Role and group based permissions, two-step login, audit log',
						'Interface in Azerbaijani, English and Russian',
					)
				),
				'body'     => 'Customer requests (Ticket) and your team\'s own work (Task) live in the same system: the customer writes by email, through the portal or Telegram, your team answers, splits the work into tasks and records the time spent. The system is entirely our own code, so it can be shaped around the way your company works.',
			),
			'ru' => array(
				'title'    => 'FIXIT Служба заявок',
				'short'    => 'Обращения клиентов (Ticket) и внутренние задачи команды (Task) на одной платформе: почта, портал, Telegram, SLA, доска и учёт времени. На вашем сервере.',
				'chips'    => 'Ticket + Task, On-premise, Почта и Telegram, Клиентский портал, SLA, Канбан-доска, Учёт времени, Многоязычный',
				'features' => implode(
					"\n",
					array(
						'Ticket: обращения с почты, клиентского портала, Telegram и веб-формы в одном месте',
						'Task: внутренние задачи команды — доска, чек-листы, зависимости, повторяющиеся работы',
						'SLA по рабочим часам, учёт времени и ежемесячный отчёт',
						'Из обращения — задача в один клик, связь между ними сохраняется',
						'Права по ролям и группам, двухэтапный вход, журнал аудита',
						'Интерфейс на азербайджанском, английском и русском',
					)
				),
				'body'     => 'Обращения клиентов (Ticket) и собственные задачи команды (Task) находятся в одной системе: клиент пишет по почте, через портал или Telegram, команда отвечает, распределяет работу в виде задач и фиксирует затраченное время. Система полностью написана нами, поэтому её можно подстроить под рабочие процессы вашей компании.',
			),
		),

		'Fixit Remote' => array(
			'en' => array(
				'title'    => 'Fixit Remote',
				'short'    => 'Remote access, support and management for company computers. Runs on your own server — the connection and the logs never leave your company.',
				'chips'    => 'On-premise, Windows agent, Mobile app, Chat, File transfer, Session log',
				'features' => implode(
					"\n",
					array(
						'View and control the screen — mouse and keyboard',
						'Chat and file transfer during the session',
						'The agent connects to the server itself — no ports to open on the computers',
						'Asks the user for permission; an on-screen bar that cannot be hidden',
						'Every session is logged; export as CSV and PDF',
						'Mobile app for controlling a computer from your phone',
					)
				),
				'body'     => 'A system for connecting to company computers remotely, helping users and keeping track of who connected to which machine. The server is installed inside your company and the agents connect to it themselves — no ports need to be opened on employee computers.',
			),
			'ru' => array(
				'title'    => 'Fixit Remote',
				'short'    => 'Удалённое подключение, поддержка и управление компьютерами компании. Работает на вашем сервере — подключение и журналы не покидают компанию.',
				'chips'    => 'On-premise, Windows-агент, Мобильное приложение, Чат, Передача файлов, Журнал сессий',
				'features' => implode(
					"\n",
					array(
						'Просмотр и управление экраном — мышь и клавиатура',
						'Чат и передача файлов во время сессии',
						'Агент сам подключается к серверу — порты на компьютерах открывать не нужно',
						'Запрос разрешения у пользователя и полоса подключения, которую нельзя скрыть',
						'Каждая сессия записывается в журнал; выгрузка в CSV и PDF',
						'Мобильное приложение для управления с телефона',
					)
				),
				'body'     => 'Система для удалённого подключения к компьютерам компании, помощи пользователям и контроля того, кто и к какой машине подключался. Сервер устанавливается внутри вашей компании, а агенты подключаются к нему сами — открывать порты на рабочих компьютерах не требуется.',
			),
		),

		'FIXIT Şəbəkə Monitorinqi' => array(
			'en' => array(
				'title'    => 'FIXIT Network Monitoring',
				'short'    => 'A monitoring and analytics tool that watches devices, interfaces and VPN tunnels non-stop and warns you about an outage before you notice it.',
				'chips'    => 'On-premise, SNMP, FortiGate, Telegram, WhatsApp, Email',
				'features' => implode(
					"\n",
					array(
						'Continuous monitoring of devices, interfaces and VPN tunnels',
						'Uptime statistics, an outage timeline and a report on the weakest points',
						'Modular analytics board: every user arranges the blocks they need',
						'Notification channels: Telegram, email (SMTP), WhatsApp and desktop — each event can be switched on separately',
					)
				),
				'body'     => 'A tool that shows exactly what stopped working in your network, when and for how long. As soon as an outage happens a notification goes to the channel you chose, the event is written to the history and appears in the uptime report.',
			),
			'ru' => array(
				'title'    => 'FIXIT Мониторинг сети',
				'short'    => 'Инструмент мониторинга и аналитики: непрерывно следит за устройствами, интерфейсами и VPN-туннелями и сообщает о сбое раньше, чем вы его заметите.',
				'chips'    => 'On-premise, SNMP, FortiGate, Telegram, WhatsApp, Почта',
				'features' => implode(
					"\n",
					array(
						'Непрерывный контроль устройств, интерфейсов и VPN-туннелей',
						'Статистика доступности, лента сбоев и отчёт по самым проблемным точкам',
						'Модульная панель аналитики: каждый пользователь собирает нужные ему блоки',
						'Каналы уведомлений: Telegram, почта (SMTP), WhatsApp и рабочий стол — каждое событие включается отдельно',
					)
				),
				'body'     => 'Инструмент, который точно показывает, что в вашей сети остановилось, когда и на какое время. Как только происходит сбой, уведомление уходит в выбранный канал, событие записывается в историю и попадает в отчёт о доступности.',
			),
		),

		/* ==================== XİDMƏTLƏR ==================== */

		'Kompüter Servisi' => array(
			'en' => array(
				'title'    => 'Computer Service',
				'short'    => 'We provide professional diagnostics and repair of computer equipment, solving our customers\' technical problems quickly.',
				'chips'    => 'Free diagnostics, Laptop, Desktop, Server',
				'features' => implode(
					"\n",
					array(
						'Completely free and detailed initial analysis',
						'Original spare parts with warranty',
						'Short turnaround time',
						'Preventive maintenance and cleaning',
					)
				),
				'body'     => 'You can rely on our experienced team for computer repair and service. Your device is analysed in detail completely free of charge, and the cause of the problem together with the proposed solution is explained to you clearly. Once you accept the quote, your device is returned within a short time.',
			),
			'ru' => array(
				'title'    => 'Компьютерный сервис',
				'short'    => 'Профессиональная диагностика и ремонт компьютерной техники — оперативно решаем технические проблемы наших клиентов.',
				'chips'    => 'Бесплатная диагностика, Ноутбук, Десктоп, Сервер',
				'features' => implode(
					"\n",
					array(
						'Полностью бесплатный и подробный первичный анализ',
						'Оригинальные запчасти с гарантией',
						'Короткие сроки выполнения',
						'Профилактика и чистка',
					)
				),
				'body'     => 'В ремонте и обслуживании компьютеров можно довериться нашей опытной команде. Устройство проходит подробный анализ полностью бесплатно, причина неисправности и предлагаемое решение объясняются понятным языком. После согласования работы устройство возвращается в короткий срок.',
			),
		),

		'Video Müşahidə Sistemləri' => array(
			'en' => array(
				'title'    => 'Video Surveillance Systems',
				'short'    => 'We offer modern video surveillance systems for security and monitoring. Every solution is built for quality and durability.',
				'chips'    => 'NVR, DVR, XVR, IP camera, Remote viewing',
				'features' => implode(
					"\n",
					array(
						'On-site survey and system design',
						'Installation of NVR / DVR / XVR systems',
						'Live viewing from anywhere in the world',
						'Archive retention planned to fit your needs',
					)
				),
				'body'     => 'We design and install camera systems from various manufacturers and categories (NVR, DVR, XVR). You can connect to your cameras from anywhere in the world, watch live, review the archive and receive event alerts.',
			),
			'ru' => array(
				'title'    => 'Системы видеонаблюдения',
				'short'    => 'Предлагаем современные системы видеонаблюдения для безопасности и контроля. Каждое решение рассчитано на качество и надёжность.',
				'chips'    => 'NVR, DVR, XVR, IP-камера, Удалённый просмотр',
				'features' => implode(
					"\n",
					array(
						'Выезд на объект и проектирование системы',
						'Установка систем NVR / DVR / XVR',
						'Живой просмотр из любой точки мира',
						'Срок хранения архива подбирается под ваши задачи',
					)
				),
				'body'     => 'Мы проектируем и устанавливаем системы камер разных производителей и категорий (NVR, DVR, XVR). Вы можете подключаться к своим камерам из любой точки мира, смотреть онлайн, просматривать архив и получать уведомления о событиях.',
			),
		),

		'İT Dəstək Xidməti' => array(
			'en' => array(
				'title'    => 'IT Support',
				'short'    => 'We provide complete support and solutions for your company\'s IT infrastructure, making your technology safer and more effective.',
				'chips'    => 'Outsourced IT, Remote support, On-site support, Monthly audit',
				'features' => implode(
					"\n",
					array(
						'A full review of the company\'s IT infrastructure',
						'Existing problems solved in a systematic way',
						'Remote and on-site support',
						'A general technical check once a month',
						'Analysis and optimisation of IT costs',
					)
				),
				'body'     => 'When you use our IT support service, your company\'s infrastructure is reviewed in full first and the problems accumulated until then are fixed. After that you get remote and on-site support, a general check once a month, plus analysis and optimisation of your IT costs.',
			),
			'ru' => array(
				'title'    => 'IT-поддержка',
				'short'    => 'Всесторонняя поддержка и решения для IT-инфраструктуры вашей компании — технологические процессы становятся безопаснее и эффективнее.',
				'chips'    => 'IT на аутсорсе, Удалённая поддержка, Выезд на место, Ежемесячный аудит',
				'features' => implode(
					"\n",
					array(
						'Полная проверка IT-инфраструктуры компании',
						'Системное решение накопившихся проблем',
						'Удалённая поддержка и выезд на место',
						'Общая техническая проверка раз в месяц',
						'Анализ и оптимизация IT-расходов',
					)
				),
				'body'     => 'При подключении услуги IT-поддержки мы сначала полностью проверяем инфраструктуру компании и устраняем накопившиеся проблемы. Далее обеспечиваем удалённую поддержку и выезд на место, ежемесячную общую проверку, а также анализ и оптимизацию IT-расходов.',
			),
		),

		'IP Telefoniya' => array(
			'en' => array(
				'title'    => 'IP Telephony',
				'short'    => 'We make office communication easier with IP phone systems, so your work becomes more flexible and productive.',
				'chips'    => '3CX, Yeastar, IVR, Conference call',
				'features' => implode(
					"\n",
					array(
						'Centralised call management',
						'Automated answering systems (IVR)',
						'Video conferences and group calls',
						'Cloud or on-premise server, your choice',
						'Call recording and reports',
					)
				),
				'body'     => 'We build effective and affordable communication systems with modern IP telephony. Working mainly with the 3CX and Yeastar platforms, we provide call management, automated answering, conference calls and other advanced features. 3CX is a flexible platform that integrates deeply and can run in the cloud or on a local server; Yeastar is the optimal choice for small and medium businesses.',
			),
			'ru' => array(
				'title'    => 'IP-телефония',
				'short'    => 'Упрощаем офисную связь с помощью IP-телефонии — работа становится гибче и продуктивнее.',
				'chips'    => '3CX, Yeastar, IVR, Конференц-связь',
				'features' => implode(
					"\n",
					array(
						'Централизованное управление звонками',
						'Автоматические системы ответа (IVR)',
						'Видеоконференции и групповые звонки',
						'Облако или локальный сервер — на ваш выбор',
						'Запись звонков и отчёты',
					)
				),
				'body'     => 'Мы строим эффективные и выгодные системы связи на базе современной IP-телефонии. В основном используем платформы 3CX и Yeastar: управление звонками, автоматические системы ответа, конференц-связь и другие возможности. 3CX — гибкая платформа с глубокой интеграцией, работает в облаке или на локальном сервере; Yeastar — оптимальный выбор для малого и среднего бизнеса.',
			),
		),

		'Korporativ Mail Sistemləri' => array(
			'en' => array(
				'title'    => 'Corporate Mail Systems',
				'short'    => 'Our email services keep your communication secure and fast, with full management support for your mail systems.',
				'chips'    => 'Kerio, Own domain, Spam filter, Archiving',
				'features' => implode(
					"\n",
					array(
						'Professional email addresses on your company domain',
						'Encryption of data and spam filtering',
						'Full compatibility with mobile devices',
						'Automatic backup and archiving',
					)
				),
				'body'     => 'Corporate mail is the main tool for effective and secure communication between a company\'s employees. Professional emails sent from the company name build reliable contact with customers and partners. Solutions such as Kerio Mail Server work with mobile devices and different email clients, while automatic backup and archiving reduce the risk of data loss to a minimum.',
			),
			'ru' => array(
				'title'    => 'Корпоративная почта',
				'short'    => 'Наши почтовые решения делают обмен информацией безопасным и оперативным, с полной поддержкой управления почтовыми системами.',
				'chips'    => 'Kerio, Свой домен, Спам-фильтр, Архивирование',
				'features' => implode(
					"\n",
					array(
						'Профессиональные адреса на домене компании',
						'Шифрование данных и фильтрация спама',
						'Полная совместимость с мобильными устройствами',
						'Автоматическое резервное копирование и архивирование',
					)
				),
				'body'     => 'Корпоративная почта — основной инструмент эффективного и безопасного общения между сотрудниками компании. Письма от имени компании формируют надёжную связь с клиентами и партнёрами. Решения вроде Kerio Mail Server совместимы с мобильными устройствами и разными почтовыми клиентами, а автоматическое резервное копирование и архивирование сводят риск потери данных к минимуму.',
			),
		),

		'Bulud Serverlər' => array(
			'en' => array(
				'title'    => 'Cloud Servers',
				'short'    => 'Cloud servers built on modern technology give you secure data storage with fast and dependable service.',
				'chips'    => 'Scaling, Backup, Remote management',
				'features' => implode(
					"\n",
					array(
						'Flexibility: resources can be scaled up or down',
						'Cost effective: no physical hardware expense',
						'Automatic backup and data recovery',
						'Secure remote access from any device',
					)
				),
				'body'     => 'Cloud servers offer the most modern technology for storing and managing data. Unlike traditional physical servers they are managed over the internet, which gives you far more flexibility and efficiency.',
			),
			'ru' => array(
				'title'    => 'Облачные серверы',
				'short'    => 'Облачные серверы на современных технологиях обеспечивают безопасное хранение данных, высокую скорость и стабильность.',
				'chips'    => 'Масштабирование, Резервные копии, Удалённое управление',
				'features' => implode(
					"\n",
					array(
						'Гибкость: ресурсы можно увеличивать и уменьшать',
						'Выгодно: нет расходов на физическое оборудование',
						'Автоматические резервные копии и восстановление данных',
						'Безопасный удалённый доступ с любого устройства',
					)
				),
				'body'     => 'Облачные серверы предлагают самые современные технологии хранения и управления данными. В отличие от традиционных физических серверов они управляются через интернет, что даёт заметно больше гибкости и эффективности.',
			),
		),

		/* ==================== RƏYLƏR ==================== */

		'Elçin Məmmədov' => array(
			'en' => array(
				'body' => 'Fixit\'s service was excellent. The computer repair was fast and done well.',
			),
			'ru' => array(
				'body' => 'Обслуживание в Fixit было отличным. Ремонт компьютеров сделали быстро и качественно.',
			),
		),

		'Fərid Əhmədov' => array(
			'en' => array(
				'body' => 'Fixit\'s camera installation service is very professional. Our security system is far more reliable now. I recommend them to everyone!',
			),
			'ru' => array(
				'body' => 'Установка камер в Fixit выполнена очень профессионально. Наша система безопасности стала надёжнее. Рекомендую всем!',
			),
		),
	);
}
