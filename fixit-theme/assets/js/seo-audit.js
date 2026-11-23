/**
 * FIXIT — SEO auditi (admin: FIXIT → SEO auditi).
 *
 * Saytın bütün səhifələri girişsiz (credentials: omit) açılır — ziyarətçi və
 * Google kimi. HTML DOMParser ilə oxunur, qaydalar inc/seo-audit.php-dəki
 * fixit_seo_audit_rules() ilə eynidir. Nəticə serverdə saxlanılır və
 * «Claude üçün hesabat» kimi kopyalanır.
 */
(function () {
	'use strict';

	var D = window.fixitSeoAudit;
	if (!D) return;
	var S = D.s;
	var RULES = D.rules;
	var $ = function (s) { return document.querySelector(s); };
	var current = null;

	var OG_LOCALE = { az: ['az_az'], en: ['en_us', 'en_gb'], ru: ['ru_ru'] };
	var AZ_LETTERS = /[əƏğĞıİ]/;
	var BUSINESS = /^(Organization|Corporation|LocalBusiness|ProfessionalService|ComputerStore|ElectronicsStore|Store)$/;
	var SEV_W = { critical: 6, warning: 2.5, notice: 0.6 };

	/* ---------------- köməkçilər ---------------- */

	function norm(u, base) {
		try {
			var x = new URL(u, base || location.href);
			x.hash = '';
			x.hostname = x.hostname.toLowerCase();
			return x.href;
		} catch (e) {
			return '';
		}
	}

	function clean(t) { return (t || '').replace(/\s+/g, ' ').trim(); }

	function esc(t) {
		return String(t == null ? '' : t).replace(/[&<>"]/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
		});
	}

	function short(u) {
		try { var x = new URL(u); return decodeURI(x.pathname + x.search); } catch (e) { return u; }
	}

	function log(t) { $('.fsa-log').textContent = t; }

	function progress(done, total) {
		var bar = $('.fsa-progress');
		bar.hidden = false;
		bar.firstElementChild.style.width = (total ? Math.round(done / total * 100) : 0) + '%';
	}

	function timeout(ms) {
		if (window.AbortSignal && AbortSignal.timeout) return AbortSignal.timeout(ms);
		return undefined;
	}

	async function pool(items, n, fn) {
		var i = 0;
		var workers = [];
		for (var w = 0; w < n; w++) {
			workers.push((async function () {
				while (i < items.length) {
					var k = i++;
					await fn(items[k], k);
				}
			})());
		}
		await Promise.all(workers);
	}

	function wait(ms) { return new Promise(function (r) { setTimeout(r, ms); }); }

	// Hostinq bəzən çoxlu keşsiz sorğuya qısa müddət 500/503 qaytarır.
	// Belə cavab «qırıq link» deyil: fasilə verib iki dəfə yenidən yoxlayırıq.
	function transient(r) { return !r.status || r.status >= 500; }

	async function getPage(url, attempt) {
		attempt = attempt || 0;
		var res = await getPageOnce(url);
		if (transient(res) && attempt < 2) {
			await wait(1500 * (attempt + 1));
			return getPage(url, attempt + 1);
		}
		return res;
	}

	async function getPageOnce(url) {
		var t0 = performance.now();
		try {
			var r = await fetch(url, { credentials: 'omit', cache: 'no-store', redirect: 'follow', signal: timeout(25000) });
			var html = await r.text();
			return {
				status: r.status, finalUrl: r.url, redirected: r.redirected, ms: Math.round(performance.now() - t0),
				size: html.length, xrobots: (r.headers.get('x-robots-tag') || '').toLowerCase(),
				type: r.headers.get('content-type') || '', html: html
			};
		} catch (e) {
			return { status: 0, error: String(e && e.message || e), ms: Math.round(performance.now() - t0) };
		}
	}

	// Daxili link / şəkil: yönləndirməni izləmədən status.
	async function probe(url, method, attempt) {
		attempt = attempt || 0;
		var res = await probeOnce(url, method);
		if (transient(res) && attempt < 2) {
			await wait(1500 * (attempt + 1));
			return probe(url, method, attempt + 1);
		}
		return res;
	}

	async function probeOnce(url, method) {
		try {
			var r = await fetch(url, { method: method || 'HEAD', credentials: 'omit', cache: 'no-store', redirect: 'manual', signal: timeout(20000) });
			if (r.type === 'opaqueredirect') return { redirect: true, status: 301 };
			if (r.status === 405 && (method || 'HEAD') === 'HEAD') return probeOnce(url, 'GET');
			return { status: r.status, length: parseInt(r.headers.get('content-length') || '0', 10) || 0 };
		} catch (e) {
			return { status: 0, error: String(e && e.message || e) };
		}
	}

	/* ---------------- audit ---------------- */

	function Audit(plan) {
		this.plan = plan;
		this.pages = [];
		this.byUrl = {};
		this.issues = {};
		this.links = {};    // hədəf → [mənbələr]
		this.images = {};   // şəkil → [səhifələr]
		this.extra = {};    // əlavə yoxlanılacaq ünvanlar: url → səbəb
		this.t0 = Date.now();
	}

	Audit.prototype.add = function (rule, url, detail) {
		if (!RULES[rule]) return;
		var list = this.issues[rule] || (this.issues[rule] = []);
		for (var i = 0; i < list.length; i++) {
			if (list[i].url === url && list[i].detail === (detail || '')) return;
		}
		list.push({ url: url, detail: detail || '' });
	};

	Audit.prototype.skipped = function (u) {
		var path;
		try { path = new URL(u).pathname; } catch (e) { return true; }
		path = path.replace(/^\/(en|ru)(\/|$)/, '/');
		return this.plan.skip.some(function (p) { return path.indexOf(p) === 0; }) || /\.(pdf|zip|exe|msi|apk|xml|txt|ico)$/i.test(path);
	};

	Audit.prototype.analyze = function (item, res) {
		var plan = this.plan;
		var self = this;
		var url = item.url;
		var p = {
			url: url, lang: item.lang, type: item.type, id: item.id, status: res.status, ms: res.ms,
			size: res.size || 0, redirected: !!res.redirected, finalUrl: res.finalUrl || url
		};
		this.pages.push(p);
		this.byUrl[norm(url)] = p;
		if (p.redirected) this.byUrl[norm(p.finalUrl)] = p;

		if (!res.status || res.status >= 400) {
			this.add('http_error', url, res.status ? 'HTTP ' + res.status : (res.error || 'fetch'));
			return;
		}
		if (res.redirected && norm(res.finalUrl) !== norm(url)) this.add('page_redirect', url, '→ ' + res.finalUrl);
		if (res.ms > 2500) this.add('slow_page', url, (res.ms / 1000).toFixed(1) + ' s');
		if (res.size > 350000) this.add('heavy_html', url, Math.round(res.size / 1024) + ' KB');

		var path = new URL(url).pathname;
		if (/[A-Z]/.test(path) || /%[0-9A-F]{2}/i.test(path)) this.add('url_format', url, decodeURI(path));

		var doc = new DOMParser().parseFromString(res.html, 'text/html');
		var q = function (s) { return doc.querySelector(s); };
		var attr = function (s, a) { var e = q(s); return e ? (e.getAttribute(a) || '') : ''; };

		// Başlıq və təsvir
		p.title = clean(q('title') ? q('title').textContent : '');
		p.desc = clean(attr('meta[name="description"]', 'content'));
		if (!p.title) this.add('title_missing', url);
		else if (p.title.length < 30) this.add('title_short', url, p.title.length + ': ' + p.title);
		else if (p.title.length > 65) this.add('title_long', url, p.title.length + ': ' + p.title);
		if (!p.desc) this.add('desc_missing', url);
		else if (p.desc.length < 70) this.add('desc_short', url, p.desc.length + ': ' + p.desc);
		else if (p.desc.length > 165) this.add('desc_long', url, String(p.desc.length));

		// İndeksləşmə
		var robots = (attr('meta[name="robots"]', 'content') + ' ' + res.xrobots).toLowerCase();
		p.noindex = /noindex/.test(robots);
		if (p.noindex) this.add('noindex', url, clean(robots));

		// Başlıq quruluşu
		p.h1 = Array.prototype.map.call(doc.querySelectorAll('h1'), function (h) { return clean(h.textContent); });
		if (!p.h1.length) this.add('h1_missing', url);
		else if (p.h1.length > 1) this.add('h1_multiple', url, p.h1.length + ': ' + p.h1.join(' | ').slice(0, 160));
		var prev = 0;
		Array.prototype.some.call(doc.querySelectorAll('main h1, main h2, main h3, main h4, main h5, main h6'), function (h) {
			var lv = +h.tagName[1];
			if (prev && lv > prev + 1) { self.add('heading_order', url, 'H' + prev + ' → H' + lv + ': ' + clean(h.textContent).slice(0, 60)); return true; }
			prev = lv;
			return false;
		});

		// Canonical
		var canon = attr('link[rel="canonical"]', 'href');
		p.canonical = canon;
		if (!canon) this.add('canonical_missing', url);
		else if (norm(canon, url) !== norm(p.finalUrl)) {
			this.add('canonical_other', url, '→ ' + canon);
			this.extra[norm(canon, url)] = this.extra[norm(canon, url)] || [];
			this.extra[norm(canon, url)].push({ kind: 'canonical', src: url });
		}

		// Dil
		p.htmlLang = (doc.documentElement.getAttribute('lang') || '').toLowerCase();
		if (p.htmlLang.split('-')[0] !== item.lang) this.add('lang_mismatch', url, 'lang="' + p.htmlLang + '"');

		// hreflang
		p.alts = {};
		Array.prototype.forEach.call(doc.querySelectorAll('link[rel="alternate"][hreflang]'), function (l) {
			var code = (l.getAttribute('hreflang') || '').toLowerCase();
			code = code === 'x-default' ? code : code.split('-')[0];
			p.alts[code] = norm(l.getAttribute('href'), url);
		});
		var codes = Object.keys(p.alts);
		if (!codes.length) this.add('hreflang_missing', url);
		else {
			var need = plan.langs.concat(['x-default']).filter(function (c) { return !p.alts[c]; });
			if (p.alts[item.lang] && p.alts[item.lang] !== norm(p.finalUrl)) need.push(item.lang + ' ≠ self');
			if (need.length) this.add('hreflang_incomplete', url, need.join(', '));
		}

		// Open Graph
		var og = {};
		Array.prototype.forEach.call(doc.querySelectorAll('meta[property^="og:"]'), function (m) { og[m.getAttribute('property')] = m.getAttribute('content') || ''; });
		var ogMiss = ['og:title', 'og:description', 'og:image'].filter(function (k) { return !og[k]; });
		if (!q('meta[name="twitter:card"]')) ogMiss.push('twitter:card');
		if (ogMiss.length) this.add('og_missing', url, ogMiss.join(', '));
		if (og['og:locale'] && OG_LOCALE[item.lang] && OG_LOCALE[item.lang].indexOf(og['og:locale'].toLowerCase()) === -1) {
			this.add('og_locale', url, og['og:locale'] + ' (' + item.lang + ')');
		}

		// Struktur məlumat
		var types = [];
		var objs = [];
		var walk = function (o) {
			if (!o || typeof o !== 'object') return;
			if (Array.isArray(o)) { o.forEach(walk); return; }
			if (o['@type']) {
				(Array.isArray(o['@type']) ? o['@type'] : [o['@type']]).forEach(function (t) { types.push(String(t)); });
				objs.push(o);
			}
			Object.keys(o).forEach(function (k) { if (k !== '@context' && typeof o[k] === 'object') walk(o[k]); });
		};
		var ldErrors = 0;
		Array.prototype.forEach.call(doc.querySelectorAll('script[type="application/ld+json"]'), function (s) {
			try { walk(JSON.parse(s.textContent)); } catch (e) { ldErrors++; }
		});
		p.schema = types.filter(function (t, i) { return types.indexOf(t) === i; });
		if (ldErrors) this.add('schema_invalid', url, ldErrors + ' JSON-LD');
		if (!types.length && !ldErrors) this.add('schema_missing', url);
		if (item.type === 'fixit_product') {
			var apps = objs.filter(function (o) { var t = [].concat(o['@type']); return t.indexOf('SoftwareApplication') > -1 || t.indexOf('Product') > -1; });
			if (!apps.length) this.add('schema_product', url);
			else if (!apps.some(function (o) { return o.offers || o.aggregateRating; })) this.add('schema_rich', url, 'offers / aggregateRating');
		}
		if (item.type !== 'front' && types.indexOf('BreadcrumbList') === -1) this.add('breadcrumb_missing', url);
		var site = objs.filter(function (o) { return [].concat(o['@type']).indexOf('WebSite') > -1; })[0];
		if (site && site.inLanguage && String(site.inLanguage).toLowerCase().split('-')[0] !== item.lang) {
			this.add('website_lang', url, 'inLanguage: ' + site.inLanguage);
		}
		if (item.type === 'front') {
			var biz = objs.filter(function (o) { return [].concat(o['@type']).some(function (t) { return BUSINESS.test(t); }); })[0];
			if (!biz) this.add('org_missing', url);
			else if (item.lang === plan['default']) {
				var miss = [];
				var addr = [].concat(biz.address || []);
				if (!addr.length) miss.push('address');
				else if (!addr.some(function (a) { return a && /^(az|aze|azerbaijan)$/i.test(String(a.addressCountry || '')); })) miss.push('address.addressCountry=AZ');
				if (!biz.telephone) miss.push('telephone');
				else if (String(biz.telephone).replace(/\s/g, '').indexOf('+994') !== 0) miss.push('telephone +994');
				if (!biz.geo) miss.push('geo');
				if (!biz.openingHoursSpecification && !biz.openingHours) miss.push('openingHours');
				if (!biz.sameAs || ![].concat(biz.sameAs).length) miss.push('sameAs');
				if (!biz.logo && !biz.image) miss.push('logo');
				if (!biz.areaServed) miss.push('areaServed');
				if (miss.length) this.add('local_missing', url, miss.join(', '));
			}
		}

		// Şəkillər
		var noAlt = 0, noSize = 0;
		Array.prototype.forEach.call(doc.querySelectorAll('img'), function (img) {
			if (img.closest('#wpadminbar')) return;
			var src = img.getAttribute('src') || img.getAttribute('data-src') || '';
			if (!src || /^data:/.test(src)) return;
			if (!img.hasAttribute('alt')) noAlt++;
			if (!img.hasAttribute('width') || !img.hasAttribute('height')) noSize++;
			var abs = norm(src, url);
			if (abs && new URL(abs).hostname === plan.host) {
				if (!/\.svg$/i.test(abs)) (self.images[abs] = self.images[abs] || []).push(url);
			}
		});
		if (noAlt) this.add('img_alt', url, String(noAlt));
		if (noSize) this.add('img_size', url, String(noSize));

		// Qarışıq məzmun və skriptlər
		if (location.protocol === 'https:') {
			var mixed = Array.prototype.filter.call(doc.querySelectorAll('img[src],script[src],iframe[src],source[src],link[rel="stylesheet"][href]'), function (e) {
				return /^http:\/\//i.test(e.getAttribute('src') || e.getAttribute('href') || '');
			});
			if (mixed.length) this.add('mixed_content', url, mixed.length + ': ' + (mixed[0].getAttribute('src') || mixed[0].getAttribute('href')));
		}
		var blocking = Array.prototype.filter.call(doc.querySelectorAll('head script[src]'), function (s) {
			return !s.async && !s.defer && !s.hasAttribute('async') && !s.hasAttribute('defer') && s.getAttribute('type') !== 'module';
		});
		if (blocking.length) this.add('blocking_js', url, String(blocking.length));

		// Mətn
		var main = (q('main') || doc.body).cloneNode(true);
		Array.prototype.forEach.call(main.querySelectorAll('script,style,noscript,template,svg,header,footer,nav,form,.lang-switch,.screen-reader-text,[hidden],[aria-hidden="true"]'), function (e) { e.remove(); });
		var text = clean(main.textContent);
		var words = text.split(' ').filter(function (w) { return /\p{L}/u.test(w); });
		p.words = words.length;
		p.text = text.toLocaleLowerCase(item.lang).slice(0, 30000);
		// Əlaqə səhifəsinin qısa olması normaldır, Google onu buna görə aşağı salmır.
		if (p.words < 250 && !p.noindex && item.tpl !== 'template-contact.php') this.add('thin_content', url, p.words + ' / 250');
		if (item.lang !== plan['default'] && item.lang !== 'az') {
			var az = words.filter(function (w) { return AZ_LETTERS.test(w); });
			if (az.length >= 5 && az.length / Math.max(1, words.length) > 0.015) {
				var uniq = az.map(function (w) { return w.replace(/[^\p{L}-]/gu, ''); }).filter(function (w, i, a) { return w && a.indexOf(w) === i; });
				this.add('untranslated', url, az.length + ': ' + uniq.slice(0, 8).join(', '));
			}
		}

		// Linklər
		p.out = [];
		Array.prototype.forEach.call(doc.querySelectorAll('a[href]'), function (a) {
			var href = a.getAttribute('href') || '';
			if (/^(mailto:|tel:|javascript:|#)/i.test(href)) return;
			var abs = norm(href, url);
			if (!abs) return;
			var u = new URL(abs);
			if (u.hostname !== plan.host || self.skipped(abs)) return;
			if (abs === norm(p.finalUrl)) return;
			p.out.push(abs);
			(self.links[abs] = self.links[abs] || []).push(url);
		});
	};

	Audit.prototype.afterCrawl = async function () {
		var self = this;
		var plan = this.plan;
		var ok = this.pages.filter(function (p) { return p.status === 200; });

		// Təkrarlanan başlıq / təsvir (eyni dildə)
		['title', 'desc'].forEach(function (f) {
			var seen = {};
			ok.forEach(function (p) {
				if (!p[f]) return;
				var k = p.lang + '|' + p[f].toLowerCase();
				(seen[k] = seen[k] || []).push(p);
			});
			Object.keys(seen).forEach(function (k) {
				if (seen[k].length < 2) return;
				seen[k].forEach(function (p) {
					var others = seen[k].filter(function (o) { return o !== p; }).map(function (o) { return short(o.url); });
					self.add(f === 'title' ? 'title_duplicate' : 'desc_duplicate', p.url, '= ' + others.join(', '));
				});
			});
		});

		// Tərcümə olunmamış başlıq
		var byId = {};
		ok.forEach(function (p) { (byId[p.id] = byId[p.id] || {})[p.lang] = p; });
		Object.keys(byId).forEach(function (id) {
			var base = byId[id][plan['default']];
			if (!base || !base.title) return;
			Object.keys(byId[id]).forEach(function (l) {
				var p = byId[id][l];
				if (l !== plan['default'] && p.title && p.title === base.title && p.title.length > 12) self.add('title_untranslated', p.url, p.title);
			});
		});

		// hreflang qarşılıqlılığı
		ok.forEach(function (p) {
			Object.keys(p.alts || {}).forEach(function (code) {
				if (code === 'x-default' || code === p.lang) return;
				var target = p.alts[code];
				var t = self.byUrl[target];
				if (!t) { (self.extra[target] = self.extra[target] || []).push({ kind: 'hreflang', src: p.url }); return; }
				if (t.status !== 200) { self.add('hreflang_broken', p.url, code + ' → ' + target + ' (HTTP ' + t.status + ')'); return; }
				if (t.alts && t.alts[p.lang] !== norm(p.finalUrl)) self.add('hreflang_reciprocal', p.url, code + ': ' + short(target) + ' ↛ ' + p.lang);
			});
		});

		// Gələn linklər: yetim səhifələr
		var inbound = {};
		this.pages.forEach(function (p) { (p.out || []).forEach(function (t) { if (t !== norm(p.url)) inbound[t] = true; }); });
		ok.forEach(function (p) {
			if (p.type !== 'front' && !p.noindex && !inbound[norm(p.url)] && !inbound[norm(p.finalUrl)]) self.add('orphan', p.url);
		});

		// Linklərin, canonical və hreflang hədəflərinin statusu
		log(S.links);
		var targets = Object.keys(this.links).concat(Object.keys(this.extra)).filter(function (u, i, a) { return a.indexOf(u) === i; });
		var unknown = targets.filter(function (u) { return !self.byUrl[u]; });
		var status = {};
		var done = 0;
		await pool(unknown.slice(0, 250), 3, async function (u) {
			status[u] = await probe(u);
			progress(++done, unknown.length);
		});
		targets.forEach(function (u) {
			var known = self.byUrl[u];
			var st = known ? { status: known.status, redirect: known.redirected && norm(known.finalUrl) !== u } : status[u];
			if (!st) return;
			var bad = !st.redirect && (st.status === 0 || st.status >= 400);
			(self.links[u] || []).forEach(function (src) {
				if (bad) self.add('broken_link', src, '→ ' + u + ' (HTTP ' + (st.status || '?') + ')');
				else if (st.redirect) self.add('link_redirect', src, '→ ' + short(u));
			});
			(self.extra[u] || []).forEach(function (x) {
				if (bad && x.kind === 'canonical') self.add('canonical_broken', x.src, '→ ' + u + ' (HTTP ' + (st.status || '?') + ')');
				if (bad && x.kind === 'hreflang') self.add('hreflang_broken', x.src, '→ ' + u + ' (HTTP ' + (st.status || '?') + ')');
			});
		});

		// Şəkillərin çəkisi
		var imgs = Object.keys(this.images).slice(0, 150);
		done = 0;
		await pool(imgs, 4, async function (u) {
			var r = await probe(u);
			progress(++done, imgs.length);
			// WebP-yə keçmək yalnız 20 KB-dan böyük PNG/JPEG üçün mənalıdır.
			if (r.length > 20000 && /\.(png|jpe?g)$/i.test(new URL(u).pathname)) {
				self.images[u].filter(function (v, i, a) { return a.indexOf(v) === i; }).forEach(function (pg) {
					self.add('img_format', pg, short(u) + ' (' + Math.round(r.length / 1024) + ' KB)');
				});
			}
			if (r.length > 300000) {
				self.images[u].filter(function (v, i, a) { return a.indexOf(v) === i; }).forEach(function (pg) {
					self.add('img_heavy', pg, short(u) + ' (' + Math.round(r.length / 1024) + ' KB)');
				});
			}
		});

		// robots.txt, sitemap, 404
		log(S.site);
		var robots = await getPage(plan.robots);
		if (robots.status !== 200) this.add('robots_missing', plan.robots, 'HTTP ' + robots.status);
		else {
			var txt = robots.html || '';
			var groups = txt.split(/^\s*user-agent\s*:/im);
			groups.forEach(function (g) {
				var head = g.split('\n')[0].trim();
				if (head === '*' && /^\s*disallow\s*:\s*\/\s*$/im.test(g)) self.add('robots_block', plan.robots);
			});
			if (!/^\s*sitemap\s*:/im.test(txt)) this.add('robots_sitemap', plan.robots);
		}

		var sm = await getPage(plan.sitemap);
		var inMap = {};
		if (sm.status !== 200) this.add('sitemap_missing', plan.sitemap, 'HTTP ' + sm.status);
		else {
			var locs = function (xml) {
				var x = new DOMParser().parseFromString(xml, 'application/xml');
				return Array.prototype.map.call(x.getElementsByTagName('loc'), function (l) { return norm(clean(l.textContent)); });
			};
			var subs = locs(sm.html);
			for (var i = 0; i < subs.length && i < 40; i++) {
				var r = await getPage(subs[i]);
				if (r.status === 200) locs(r.html).forEach(function (u) { inMap[u] = true; });
			}
			ok.forEach(function (p) {
				if (!p.noindex && !inMap[norm(p.url)]) self.add('not_in_sitemap', p.url);
			});
			var mapOnly = Object.keys(inMap).filter(function (u) { return !self.byUrl[u]; }).slice(0, 120);
			await pool(mapOnly, 4, async function (u) {
				var r2 = await probe(u);
				if (r2.redirect || !r2.status || r2.status >= 400) self.add('sitemap_bad', plan.sitemap, u + ' (' + (r2.redirect ? '301' : 'HTTP ' + r2.status) + ')');
			});
			ok.forEach(function (p) {
				if (inMap[norm(p.url)] && p.redirected) self.add('sitemap_bad', plan.sitemap, p.url + ' → ' + p.finalUrl);
			});
		}

		var nf = await probe(plan.notfound, 'GET');
		if (nf.status === 200) this.add('soft_404', plan.notfound, 'HTTP 200');

		// Açar sözlər
		this.keywords = [];
		plan.langs.forEach(function (lang) {
			var kws = (plan.keywords && plan.keywords[lang]) || [];
			var pages = ok.filter(function (p) { return p.lang === lang && !p.noindex; });
			var home = pages.filter(function (p) { return p.type === 'front'; })[0];
			// Söz formaları: «Bakı» → «Bakıda», «удаленная» → «удалённая».
			// Açar sözün hər sözü mətndəki hansısa sözün əvvəli olmalıdır.
			var fold = function (t) { return String(t || '').toLocaleLowerCase(lang).replace(/ё/g, 'е'); };
			var hit = function (hay, k) {
				hay = fold(hay);
				if (!hay) return false;
				if (hay.indexOf(k) > -1) return true;
				var words = hay.split(/[^\p{L}\p{N}]+/u).filter(Boolean);
				return k.split(/[^\p{L}\p{N}]+/u).filter(Boolean).every(function (part) {
					return words.some(function (w) { return w.indexOf(part) === 0; });
				});
			};
			kws.forEach(function (kw) {
				var k = fold(clean(kw));
				if (!k) return;
				var best = null;
				pages.forEach(function (p) {
					var where = [];
					var score = 0;
					var path = '';
					try { path = decodeURIComponent(new URL(p.url).pathname).replace(/[-/]/g, ' '); } catch (e) {}
					if (hit(p.title, k)) { where.push(S.inTitle); score += 4; }
					if (hit((p.h1 || []).join(' '), k)) { where.push(S.inH1); score += 3; }
					if (hit(p.desc, k)) { where.push(S.inDesc); score += 1; }
					if (hit(path, k)) { where.push(S.inUrl); score += 1; }
					if (hit(p.text, k)) { where.push(S.inBody); score += 1; }
					if (score && (!best || score > best.score)) best = { url: p.url, where: where, score: score };
				});
				self.keywords.push({ lang: lang, keyword: kw, url: best ? best.url : '', where: best ? best.where : [], score: best ? best.score : 0 });
				if (!best || best.score < 3) self.add('keyword_missing', home ? home.url : plan.home, lang.toUpperCase() + ': ' + kw);
			});
		});
	};

	Audit.prototype.report = function () {
		var self = this;
		var issues = Object.keys(this.issues).map(function (id) {
			return { id: id, severity: RULES[id][0], title: RULES[id][1], hint: RULES[id][2], items: self.issues[id] };
		});
		var order = { critical: 0, warning: 1, notice: 2 };
		issues.sort(function (a, b) { return order[a.severity] - order[b.severity] || b.items.length - a.items.length; });

		var count = { critical: 0, warning: 0, notice: 0 };
		var penalty = 0;
		issues.forEach(function (i) {
			count[i.severity] += i.items.length;
			penalty += SEV_W[i.severity] * Math.min(3, 1 + Math.log2(i.items.length));
		});

		var perPage = {};
		issues.forEach(function (i) { i.items.forEach(function (it) { perPage[it.url] = (perPage[it.url] || 0) + 1; }); });

		return {
			time: Math.round(Date.now() / 1000),
			host: this.plan.host,
			summary: {
				score: Math.max(0, Math.round(100 - penalty)),
				pages: this.pages.length,
				critical: count.critical, warning: count.warning, notice: count.notice,
				duration: Math.round((Date.now() - this.t0) / 1000)
			},
			issues: issues.map(function (i) { return { id: i.id, severity: i.severity, title: i.title, hint: i.hint, items: i.items.slice(0, 80), total: i.items.length }; }),
			pages: this.pages.map(function (p) {
				return {
					url: p.url, lang: p.lang, status: p.status, ms: p.ms, title: p.title || '', titleLen: (p.title || '').length,
					descLen: (p.desc || '').length, h1: (p.h1 || []).length, words: p.words || 0, schema: p.schema || [], issues: perPage[p.url] || 0
				};
			}),
			keywords: this.keywords || []
		};
	};

	/* ---------------- göstərmə ---------------- */

	function render(r) {
		current = r;
		$('#fsa-copy').disabled = false;
		$('#fsa-json').disabled = false;
		var s = r.summary;
		var when = new Date(r.time * 1000).toLocaleString();
		var h = '';
		h += '<p><strong>' + esc(S.lastRun) + ':</strong> ' + esc(when) + '</p>';
		h += '<div class="fsa-cards">';
		h += '<div class="fsa-card fsa-score"><b>' + s.score + '/100</b><span>' + esc(S.score) + '</span></div>';
		h += '<div class="fsa-card"><b>' + s.pages + '</b><span>' + esc(S.pages) + '</span></div>';
		h += '<div class="fsa-card c"><b>' + s.critical + '</b><span>' + esc(S.critical) + '</span></div>';
		h += '<div class="fsa-card w"><b>' + s.warning + '</b><span>' + esc(S.warning) + '</span></div>';
		h += '<div class="fsa-card n"><b>' + s.notice + '</b><span>' + esc(S.notice) + '</span></div>';
		h += '<div class="fsa-card"><b>' + s.duration + ' ' + esc(S.sec) + '</b><span>' + esc(S.duration) + '</span></div>';
		h += '</div>';

		if (!r.issues.length) h += '<p>' + esc(S.noIssues) + '</p>';
		r.issues.forEach(function (i) {
			h += '<details class="fsa-rule ' + i.severity + '"' + (i.severity === 'critical' ? ' open' : '') + '>';
			h += '<summary><span class="fsa-sev ' + i.severity + '">' + esc(S[i.severity]) + '</span>' + esc(i.title) + '<em>' + i.total + '</em></summary>';
			h += '<p class="hint">' + esc(i.hint) + ' <code>' + esc(i.id) + '</code></p><ul>';
			i.items.forEach(function (it) {
				h += '<li><a href="' + esc(it.url) + '" target="_blank" rel="noopener">' + esc(short(it.url)) + '</a>' + (it.detail ? ' <small>' + esc(it.detail) + '</small>' : '') + '</li>';
			});
			if (i.total > i.items.length) h += '<li>' + esc(S.more.replace('%d', i.total - i.items.length)) + '</li>';
			h += '</ul></details>';
		});

		if (r.keywords && r.keywords.length) {
			h += '<h2>' + esc(S.keyword) + '</h2><table class="widefat striped"><thead><tr><th>' + esc(S.lang) + '</th><th>' + esc(S.keyword) + '</th><th>' + esc(S.bestPage) + '</th><th>' + esc(S.where) + '</th></tr></thead><tbody>';
			r.keywords.forEach(function (k) {
				var weak = k.score < 3;
				h += '<tr><td>' + esc(k.lang.toUpperCase()) + '</td><td>' + esc(k.keyword) + '</td><td>' + (k.url ? '<a href="' + esc(k.url) + '" target="_blank" rel="noopener">' + esc(short(k.url)) + '</a>' : '<span class="bad">' + esc(S.none) + '</span>') + '</td><td class="' + (weak ? 'bad' : 'ok') + '">' + esc((k.where || []).join(', ') || '—') + '</td></tr>';
			});
			h += '</tbody></table>';
		}

		h += '<h2>' + esc(S.pages) + '</h2><table class="widefat striped"><thead><tr><th>' + esc(S.url) + '</th><th>' + esc(S.lang) + '</th><th>' + esc(S.status) + '</th><th>' + esc(S.time) + '</th><th>' + esc(S.title) + '</th><th>' + esc(S.desc) + '</th><th>H1</th><th>' + esc(S.words) + '</th><th>' + esc(S.issues) + '</th></tr></thead><tbody>';
		(r.pages || []).slice().sort(function (a, b) { return b.issues - a.issues; }).forEach(function (p) {
			h += '<tr><td><a href="' + esc(p.url) + '" target="_blank" rel="noopener">' + esc(short(p.url)) + '</a></td><td>' + esc(p.lang) + '</td>';
			h += '<td class="' + (p.status === 200 ? '' : 'bad') + '">' + p.status + '</td><td>' + (p.ms / 1000).toFixed(1) + ' s</td>';
			h += '<td class="' + (p.titleLen < 30 || p.titleLen > 65 ? 'bad' : '') + '">' + p.titleLen + '</td>';
			h += '<td class="' + (p.descLen < 70 || p.descLen > 165 ? 'bad' : '') + '">' + p.descLen + '</td>';
			h += '<td class="' + (p.h1 !== 1 ? 'bad' : '') + '">' + p.h1 + '</td><td>' + p.words + '</td><td>' + p.issues + '</td></tr>';
		});
		h += '</tbody></table>';
		$('#fsa-result').innerHTML = h;
	}

	function markdown(r) {
		var s = r.summary;
		var out = [];
		out.push('# ' + S.reportHead + ': ' + r.host + ' (' + new Date(r.time * 1000).toLocaleString() + ')');
		out.push(S.score + ': ' + s.score + '/100 · ' + S.pages + ': ' + s.pages + ' · ' + S.critical + ': ' + s.critical + ' · ' + S.warning + ': ' + s.warning + ' · ' + S.notice + ': ' + s.notice);
		out.push('');
		out.push(S.reportAsk);
		['critical', 'warning', 'notice'].forEach(function (sev) {
			var list = r.issues.filter(function (i) { return i.severity === sev; });
			if (!list.length) return;
			out.push('');
			out.push('## ' + S[sev]);
			list.forEach(function (i) {
				out.push('');
				out.push('### ' + i.title + ' [' + i.id + '] · ' + i.total);
				out.push(i.hint);
				i.items.slice(0, sev === 'notice' ? 15 : 40).forEach(function (it) {
					out.push('- ' + it.url + (it.detail ? ' · ' + it.detail : ''));
				});
				var shown = Math.min(i.items.length, sev === 'notice' ? 15 : 40);
				if (i.total > shown) out.push('- ' + S.more.replace('%d', i.total - shown));
			});
		});
		if (r.keywords && r.keywords.length) {
			out.push('');
			out.push('## ' + S.keyword);
			r.keywords.forEach(function (k) {
				out.push('- ' + k.lang.toUpperCase() + ' «' + k.keyword + '» → ' + (k.url || S.none) + (k.where && k.where.length ? ' (' + k.where.join(', ') + ')' : ''));
			});
		}
		return out.join('\n');
	}

	async function run() {
		var btn = $('#fsa-run');
		btn.disabled = true;
		try {
			log(S.plan);
			progress(0, 1);
			var pr = await fetch(D.plan, { credentials: 'same-origin', headers: { 'X-WP-Nonce': D.nonce } });
			if (!pr.ok) throw new Error('plan HTTP ' + pr.status);
			var plan = await pr.json();
			var a = new Audit(plan);
			var done = 0;
			await pool(plan.urls, 4, async function (item) {
				var res = await getPage(item.url);
				a.analyze(item, res);
				res.html = null;
				progress(++done, plan.urls.length);
				log(S.crawl + ' ' + done + ' / ' + plan.urls.length + ': ' + short(item.url));
			});
			await a.afterCrawl();
			var r = a.report();
			render(r);
			progress(1, 1);
			log(S.done);
			var sv = await fetch(D.save, { method: 'POST', credentials: 'same-origin', headers: { 'X-WP-Nonce': D.nonce, 'Content-Type': 'application/json' }, body: JSON.stringify(r) });
			log(sv.ok ? S.done + '. ' + S.saved : S.saveFail + ' (HTTP ' + sv.status + ')');
		} catch (e) {
			log(S.failed + ': ' + (e && e.message || e));
		} finally {
			btn.disabled = false;
		}
	}

	$('#fsa-run').addEventListener('click', run);
	$('#fsa-copy').addEventListener('click', function () {
		if (!current) return;
		var md = markdown(current);
		var done = function () { log(S.copied); };
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(md).then(done, function () { fallbackCopy(md); done(); });
		} else { fallbackCopy(md); done(); }
	});
	$('#fsa-json').addEventListener('click', function () {
		if (!current) return;
		var blob = new Blob([JSON.stringify(current, null, 1)], { type: 'application/json' });
		var a = document.createElement('a');
		a.href = URL.createObjectURL(blob);
		a.download = 'fixit-seo-audit-' + new Date(current.time * 1000).toISOString().slice(0, 10) + '.json';
		document.body.appendChild(a);
		a.click();
		setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 500);
	});

	function fallbackCopy(text) {
		var t = document.createElement('textarea');
		t.value = text;
		t.style.position = 'fixed';
		t.style.opacity = '0';
		document.body.appendChild(t);
		t.select();
		try { document.execCommand('copy'); } catch (e) {}
		t.remove();
	}

	if (D.last) render(D.last);
})();
