/**
 * FIXIT Pro — kuki razılığı zolağı.
 *
 * Seçim localStorage-da saxlanılır: fixit-consent = {"v":2,"a":0|1,"m":0|1,"t":<ms>}
 *   a — analitika (Google Analytics 4, Microsoft Clarity)
 *   m — reklam (Meta Pixel, LinkedIn Insight Tag)
 * 12 aydan köhnə seçim etibarsızdır, zolaq yenidən görünür (inc/consent.php).
 *
 * Razılıq verilən kimi window.fixitTrack.start(seçim) çağırılır
 * (inc/analytics.php): skriptlər yalnız bundan sonra yüklənir.
 * Razılıq geri götürüləndə səhifə yenilənir — yüklənmiş skripti
 * səhifədən çıxarmaq mümkün deyil, yeniləmədən sonra artıq yüklənmir.
 */
(function () {
	'use strict';

	var doc = document;
	var bar = doc.getElementById('fixit-consent');
	var api = window.fixitConsent || {};

	if (!bar || !api.read) return;

	var prefs = bar.querySelector('[data-consent-prefs]');
	var saveBtn = bar.querySelector('[data-consent="save"]');
	var settingsBtn = bar.querySelector('[data-consent="settings"]');

	function box(cat) {
		return bar.querySelector('[data-consent-cat="' + cat + '"]');
	}

	function store(choice) {
		try {
			localStorage.setItem(api.key, JSON.stringify({ v: 2, a: choice.a ? 1 : 0, m: choice.m ? 1 : 0, t: Date.now() }));
		} catch (e) {}
	}

	/*
	 * Razılıq geri götürüləndə kənar kukiləri silir (bacarılan qədər:
	 * hər iki domen səviyyəsində).
	 */
	function clearCookies(cat) {
		var names = cat === 'a'
			? /^(_ga|_gid|_gat|_clck|_clsk|CLID|MUID)/
			: /^(_fbp|_fbc|li_|lidc|bcookie|UserMatchHistory|AnalyticsSyncHistory|li_fat_id)/;
		var host = window.location.hostname;
		var domains = ['', host, '.' + host, '.' + host.split('.').slice(-2).join('.')];

		doc.cookie.split(';').forEach(function (part) {
			var name = part.split('=')[0].trim();
			if (!names.test(name)) return;
			domains.forEach(function (d) {
				doc.cookie = name + '=;path=/;max-age=0' + (d ? ';domain=' + d : '');
			});
		});
	}

	function apply(choice, previous) {
		store(choice);
		hide();

		var revoked = previous && ((previous.a && !choice.a) || (previous.m && !choice.m));
		if (revoked) {
			if (previous.a && !choice.a) clearCookies('a');
			if (previous.m && !choice.m) clearCookies('m');
			window.location.reload();
			return;
		}

		if (window.fixitTrack && window.fixitTrack.start) window.fixitTrack.start(choice);

		try {
			doc.dispatchEvent(new CustomEvent('fixit:consent', { detail: choice }));
		} catch (e) {}
	}

	function showPrefs(open) {
		if (prefs) prefs.hidden = !open;
		if (saveBtn) saveBtn.hidden = !open;
		if (settingsBtn) {
			settingsBtn.hidden = open;
			settingsBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
		}
	}

	function show(withPrefs) {
		var current = api.read();
		var a = box('a');
		var m = box('m');
		if (a) a.checked = !!(current && current.a);
		if (m) m.checked = !!(current && current.m);

		showPrefs(!!withPrefs);
		bar.hidden = false;
		doc.documentElement.classList.add('has-consent');
	}

	function hide() {
		bar.hidden = true;
		doc.documentElement.classList.remove('has-consent');
	}

	bar.addEventListener('click', function (e) {
		var btn = e.target.closest ? e.target.closest('[data-consent]') : null;
		if (!btn) return;

		var action = btn.getAttribute('data-consent');
		var previous = api.read();

		if (action === 'all') {
			apply({ a: true, m: true }, previous);
		} else if (action === 'necessary') {
			apply({ a: false, m: false }, previous);
		} else if (action === 'settings') {
			showPrefs(true);
			var first = box('a');
			if (first) first.focus();
		} else if (action === 'save') {
			var a = box('a');
			var m = box('m');
			apply({ a: !!(a && a.checked), m: !!(m && m.checked) }, previous);
		}
	});

	// Footerdəki «Kuki ayarları»: zolaq ayarlarla birlikdə açılır.
	doc.addEventListener('click', function (e) {
		var opener = e.target.closest ? e.target.closest('[data-consent-open]') : null;
		if (!opener) return;
		e.preventDefault();
		show(true);
	});

	if (!api.read()) show(false);
})();
