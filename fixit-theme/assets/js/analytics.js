/**
 * FIXIT Pro — analitika hadisələri və kuki razılığı.
 *
 * Yalnız "Analitika və reklam" bölməsində ən azı bir identifikator
 * yazılanda qoşulur (inc/analytics.php). Ayarlar window.fixitTrack-dadır.
 *
 * Hadisələr (GA4 adı → Meta adı):
 *   generate_lead        → Lead          əlaqə / demo forması uğurla göndərildi
 *   demo_open            → DemoOpen      saytdan canlı demoya keçid
 *   contact_click        → Contact       zəng, WhatsApp, e-poçt linki
 *   remote_free_download → RemoteFreeDownload  pulsuz Fixit Remote yükləməsi
 *   guide_open           → —             istifadəçi bələdçisinə keçid
 */
(function () {
	'use strict';

	var c = window.fixitTrack;
	var doc = document;

	if (!c) return;

	/*
	 * Hadisəni bütün qoşulu sistemlərə ötürür. Razılıq verilməyibsə
	 * kitabxanalar yoxdur və hadisə heç yerə getmir — bu, qəsdəndir
	 * (razılıq zolağı: assets/js/consent.js).
	 */
	function send(name, params, metaName, metaParams, metaStandard) {
		if (c.ga4 && window.gtag) window.gtag('event', name, params || {});
		if (c.meta && window.fbq && metaName) {
			window.fbq(metaStandard ? 'track' : 'trackCustom', metaName, metaParams || params || {});
		}
	}

	/* ---------------------------------------------
	   2. Formlar: müraciət (lead)
	   --------------------------------------------- */
	/*
	 * Form admin-post.php-yə gedir və səhifə ?fixit_msg=ok ilə qayıdır.
	 * Göndərmə anında forma növünü və mövzunu yadda saxlayırıq, qayıdanda
	 * hadisəni bir dəfə göndəririk. Qeyd silinir — səhifə yenilənəndə
	 * müraciət ikinci dəfə sayılmır.
	 */
	var LEAD_KEY = 'fixit-lead';

	doc.addEventListener('submit', function (e) {
		var form = e.target;
		if (!form || !form.querySelector) return;

		// Fixit Remote-un 14 günlük sınaq forması (inc/remote-trial.php).
		if (form.querySelector('input[name="action"][value="fixit_trial"]')) {
			try {
				sessionStorage.setItem(LEAD_KEY, JSON.stringify({ kind: 'trial', topic: 'Fixit Remote', size: '' }));
			} catch (err) {}
			return;
		}

		if (!form.querySelector('input[name="action"][value="fixit_contact"]')) return;

		var kind = form.querySelector('[name="fixit_kind"]');
		var topic = form.querySelector('[name="fixit_topic"]');
		var size = form.querySelector('[name="fixit_size"]');

		try {
			sessionStorage.setItem(LEAD_KEY, JSON.stringify({
				kind: kind && kind.value === 'demo' ? 'demo' : 'contact',
				topic: topic ? topic.value : '',
				size: size ? size.value : ''
			}));
		} catch (err) {}
	}, true);

	if (/[?&](fixit_msg=(ok|demo_ok)|fixit_trial=(ok|queued))(&|$)/.test(window.location.search)) {
		var lead = null;
		try {
			lead = JSON.parse(sessionStorage.getItem(LEAD_KEY) || 'null');
			sessionStorage.removeItem(LEAD_KEY);
		} catch (err) {}

		if (lead) {
			send(
				'generate_lead',
				{ form_type: lead.kind, topic: lead.topic || '-', company_size: lead.size || '-' },
				'Lead',
				{ content_name: lead.topic || '-', content_category: lead.kind },
				true
			);

			if (c.leadConv && window.lintrk) window.lintrk('track', { conversion_id: Number(c.leadConv) });
		}
	}

	/* ---------------------------------------------
	   3. Linklər: demo, zəng/WhatsApp, yükləmə, bələdçi
	   --------------------------------------------- */
	var demos = c.demos || {};

	doc.addEventListener('click', function (e) {
		var a = e.target.closest ? e.target.closest('a[href]') : null;
		if (!a) return;

		var url;
		try {
			url = new URL(a.getAttribute('href'), window.location.href);
		} catch (err) {
			return;
		}

		var host = (url.hostname || '').toLowerCase();

		if (url.protocol === 'tel:' || url.protocol === 'mailto:' || host === 'wa.me' || /(^|\.)whatsapp\.com$/.test(host)) {
			var method = url.protocol === 'tel:' ? 'phone' : (url.protocol === 'mailto:' ? 'email' : 'whatsapp');
			send('contact_click', { method: method }, 'Contact', { content_name: method }, true);
			return;
		}

		if (demos[host]) {
			send('demo_open', { product: demos[host], link_url: url.href }, 'DemoOpen', { content_name: demos[host] });
			return;
		}

		// Saytın öz /download/ ünvanı (köhnəsi /yukle/): son FixitRemote.exe-yə yönləndirir.
		if (a.hasAttribute('data-remote-download')) {
			send('remote_free_download', { platform: 'windows' }, 'RemoteFreeDownload', { content_name: 'windows' });
			return;
		}

		if (host === 'github.com' && /\/fixit-remote-releases\/releases\//.test(url.pathname)) {
			var platform = /\.apk$/i.test(url.pathname) ? 'android' : 'windows';
			send('remote_free_download', { platform: platform }, 'RemoteFreeDownload', { content_name: platform });
			return;
		}

		if (host === 'guide.fixit.az') {
			send('guide_open', { link_url: url.href });
		}
	});
})();
