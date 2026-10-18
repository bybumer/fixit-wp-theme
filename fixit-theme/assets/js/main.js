/**
 * FIXIT Pro — əsas skript.
 * Heç bir kitabxana (jQuery və s.) tələb etmir.
 */
(function () {
	'use strict';

	var doc = document;

	/* ---------------------------------------------
	   1. İşıqlı / qaranlıq rejim
	   --------------------------------------------- */
	var toggle = doc.querySelector('.theme-toggle');

	if (toggle) {
		toggle.addEventListener('click', function () {
			var current = doc.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
			var next = current === 'dark' ? 'light' : 'dark';

			doc.documentElement.setAttribute('data-theme', next);
			try {
				localStorage.setItem('fixit-theme', next);
			} catch (e) {}
		});
	}

	/* ---------------------------------------------
	   2. Mobil menyu
	   --------------------------------------------- */
	var burger = doc.querySelector('.burger');
	var menu = doc.getElementById('mobile-menu');
	var closeBtn = doc.querySelector('.mobile-close');

	function openMenu() {
		if (!menu) return;
		menu.classList.add('is-open');
		menu.setAttribute('aria-hidden', 'false');
		if (burger) burger.setAttribute('aria-expanded', 'true');
		doc.body.style.overflow = 'hidden';
	}

	function closeMenu() {
		if (!menu) return;
		menu.classList.remove('is-open');
		menu.setAttribute('aria-hidden', 'true');
		if (burger) burger.setAttribute('aria-expanded', 'false');
		doc.body.style.overflow = '';
	}

	if (burger) burger.addEventListener('click', openMenu);
	if (closeBtn) closeBtn.addEventListener('click', closeMenu);

	if (menu) {
		menu.addEventListener('click', function (e) {
			if (e.target.closest('a')) closeMenu();
		});
	}

	doc.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') closeMenu();
	});

	/* ---------------------------------------------
	   3. Başlığın "yapışma" effekti
	   --------------------------------------------- */
	var header = doc.getElementById('site-header');
	var toTop = doc.querySelector('.to-top');

	function onScroll() {
		var y = window.scrollY || window.pageYOffset;

		if (header) header.classList.toggle('is-stuck', y > 8);
		if (toTop) toTop.classList.toggle('is-visible', y > 500);
	}

	window.addEventListener('scroll', onScroll, { passive: true });
	onScroll();

	if (toTop) {
		toTop.addEventListener('click', function () {
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});
	}

	/* ---------------------------------------------
	   4. Scroll ilə görünmə animasiyası
	   --------------------------------------------- */
	/*
	 * Sadə və etibarlı üsul: hər sürüşmədə elementin ekranda olub-olmadığını
	 * yoxlayırıq. IntersectionObserver-dən fərqli olaraq burada "işə düşmədi"
	 * vəziyyəti mümkün deyil — səhifə hansı vəziyyətdə açılsa da nəticə eynidir.
	 */
	var pending = Array.prototype.slice.call(doc.querySelectorAll('.reveal'));
	var ticking = false;

	pending.forEach(function (el, i) {
		el.style.transitionDelay = Math.min(i % 4, 3) * 70 + 'ms';
	});

	function revealVisible() {
		ticking = false;

		var limit = window.innerHeight * 0.94;

		pending = pending.filter(function (el) {
			var box = el.getBoundingClientRect();

			// Elementin yuxarı kənarı ekrana girib (və ya element ekranı əhatə edir).
			if (box.top < limit && box.bottom > 0) {
				el.classList.add('is-in');
				return false;
			}

			return true;
		});
	}

	function queueReveal() {
		if (ticking || !pending.length) return;
		ticking = true;
		window.requestAnimationFrame(revealVisible);
	}

	window.addEventListener('scroll', queueReveal, { passive: true });
	window.addEventListener('resize', queueReveal, { passive: true });
	window.addEventListener('load', revealVisible);
	revealVisible();

	// Son təhlükəsizlik qatı: şəkillər gec yüklənib düzülüş sürüşərsə.
	setTimeout(revealVisible, 800);

	/* ---------------------------------------------
	   5. Formdan sonra bildirişə sürüşmə
	   --------------------------------------------- */
	if (window.location.search.indexOf('fixit_msg=') !== -1) {
		var alertBox = doc.querySelector('.alert');
		if (alertBox) {
			setTimeout(function () {
				alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
			}, 200);
		}
	}

	/* ---------------------------------------------
	   6. Ekran görüntüləri — şəkil böyüdücü
	   --------------------------------------------- */
	var galleries = doc.querySelectorAll('[data-lightbox]');

	if (galleries.length) {
		var lb = doc.createElement('div');
		lb.className = 'lightbox';
		lb.setAttribute('role', 'dialog');
		lb.setAttribute('aria-modal', 'true');
		lb.innerHTML =
			'<button type="button" class="lightbox__close" aria-label="Bağla">&times;</button>' +
			'<button type="button" class="lightbox__prev" aria-label="Əvvəlki">&#8249;</button>' +
			'<img alt="">' +
			'<button type="button" class="lightbox__next" aria-label="Növbəti">&#8250;</button>' +
			'<div class="lightbox__cap"></div>';
		doc.body.appendChild(lb);

		var lbImg = lb.querySelector('img');
		var lbCap = lb.querySelector('.lightbox__cap');
		var items = [];
		var current = 0;

		function show(i) {
			current = (i + items.length) % items.length;
			lbImg.src = items[current].href;
			lbCap.textContent = items[current].getAttribute('data-caption') || '';
			lb.querySelector('.lightbox__prev').style.display = items.length > 1 ? '' : 'none';
			lb.querySelector('.lightbox__next').style.display = items.length > 1 ? '' : 'none';
		}

		function open(list, i) {
			items = list;
			show(i);
			lb.classList.add('is-open');
			doc.body.style.overflow = 'hidden';
		}

		function close() {
			lb.classList.remove('is-open');
			lbImg.src = '';
			doc.body.style.overflow = '';
		}

		Array.prototype.forEach.call(galleries, function (g) {
			var links = Array.prototype.slice.call(g.querySelectorAll('a[href]'));
			links.forEach(function (a, i) {
				a.addEventListener('click', function (e) {
					e.preventDefault();
					open(links, i);
				});
			});
		});

		lb.addEventListener('click', function (e) {
			if (e.target === lb || e.target.classList.contains('lightbox__close')) close();
			if (e.target.classList.contains('lightbox__prev')) show(current - 1);
			if (e.target.classList.contains('lightbox__next')) show(current + 1);
		});

		doc.addEventListener('keydown', function (e) {
			if (!lb.classList.contains('is-open')) return;
			if (e.key === 'Escape') close();
			if (e.key === 'ArrowLeft') show(current - 1);
			if (e.key === 'ArrowRight') show(current + 1);
		});
	}

	/* ---------------------------------------------
	   6a. Ana səhifənin slaydı (məhsullar)
	   --------------------------------------------- */
	var slider = doc.querySelector('[data-hero-slider]');

	if (slider) {
		var slides = slider.querySelectorAll('.hero-slide');
		var dots = slider.querySelectorAll('[data-slide-to]');
		var at = 0;
		var timer = null;
		var paused = false;
		var still = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		var showSlide = function (i) {
			at = (i + slides.length) % slides.length;
			Array.prototype.forEach.call(slides, function (s, k) {
				var on = k === at;
				s.classList.toggle('is-active', on);
				// Gizli slaydın düymələrinə Tab ilə düşülməsin.
				if (on) {
					s.removeAttribute('inert');
					s.removeAttribute('aria-hidden');
				} else {
					s.setAttribute('inert', '');
					s.setAttribute('aria-hidden', 'true');
				}
			});
			Array.prototype.forEach.call(dots, function (d, k) {
				d.setAttribute('aria-current', k === at ? 'true' : 'false');
			});
		};

		// Avtomatik keçid: siçan üstündə və ya klaviatura içindədirsə dayanır.
		var schedule = function () {
			clearTimeout(timer);
			if (still || paused || doc.hidden || slides.length < 2) return;
			timer = setTimeout(function () {
				showSlide(at + 1);
				schedule();
			}, 7000);
		};

		var go = function (i) {
			showSlide(i);
			schedule();
		};

		slider.addEventListener('click', function (e) {
			var t = e.target.closest ? e.target.closest('[data-slide-to],[data-slide-prev],[data-slide-next]') : null;
			if (!t) return;
			if (t.hasAttribute('data-slide-prev')) go(at - 1);
			else if (t.hasAttribute('data-slide-next')) go(at + 1);
			else go(parseInt(t.getAttribute('data-slide-to'), 10) || 0);
		});

		slider.addEventListener('mouseenter', function () { paused = true; clearTimeout(timer); });
		slider.addEventListener('mouseleave', function () { paused = false; schedule(); });
		slider.addEventListener('focusin', function () { paused = true; clearTimeout(timer); });
		slider.addEventListener('focusout', function () { paused = false; schedule(); });
		doc.addEventListener('visibilitychange', schedule);

		// Telefonda barmaqla sürüşdürmək.
		var startX = null;
		slider.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
		slider.addEventListener('touchend', function (e) {
			if (startX === null) return;
			var dx = e.changedTouches[0].clientX - startX;
			startX = null;
			if (Math.abs(dx) > 45) go(at + (dx < 0 ? 1 : -1));
		});

		schedule();
	}

	/* ---------------------------------------------
	   6b. Fixit Remote yüklənməsi: sonra nə etməli
	   --------------------------------------------- */
	var dlHelp = doc.getElementById('dl-help');

	if (dlHelp) {
		var phone = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent) && !/Windows/i.test(navigator.userAgent);
		var lastFocus = null;

		var openHelp = function (view) {
			Array.prototype.forEach.call(dlHelp.querySelectorAll('[data-dl-view]'), function (v) {
				v.hidden = v.getAttribute('data-dl-view') !== view;
			});
			lastFocus = doc.activeElement;
			dlHelp.hidden = false;
			var close = dlHelp.querySelector('[data-dl-close]');
			if (close) close.focus();
		};

		var closeHelp = function () {
			dlHelp.hidden = true;
			if (lastFocus && lastFocus.focus) lastFocus.focus();
		};

		doc.addEventListener('click', function (e) {
			var a = e.target.closest ? e.target.closest('[data-remote-download]') : null;
			if (!a) return;

			// Telefonda yükləmənin mənası yoxdur: kompüterdə açılacaq ünvan.
			if (phone) {
				e.preventDefault();
				openHelp('mobile');
				return;
			}

			// Yükləmə öz yolu ilə gedir (səhifə yerində qalır), kömək üstə açılır.
			setTimeout(function () { openHelp('desktop'); }, 400);
		});

		dlHelp.addEventListener('click', function (e) {
			if (e.target === dlHelp || (e.target.closest && e.target.closest('[data-dl-close]'))) closeHelp();

			var copy = e.target.closest ? e.target.closest('[data-dl-copy]') : null;
			if (copy && navigator.clipboard) {
				navigator.clipboard.writeText(copy.getAttribute('data-dl-copy')).then(function () {
					copy.textContent = copy.getAttribute('data-copied');
				}).catch(function () {});
			}
		});

		doc.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !dlHelp.hidden) closeHelp();
		});
	}

	/* ---------------------------------------------
	   7. Dil: seçim yadda qalır, ilk ziyarətdə ölkəyə görə təklif
	   --------------------------------------------- */
	var L = window.fixitLang;

	if (L && L.cookie) {
		// İstifadəçi dili özü seçdi — seçimi bir il saxlayırıq.
		doc.addEventListener('click', function (e) {
			var a = e.target.closest ? e.target.closest('.lang-switch a') : null;
			if (!a || !a.dataset.lang) return;
			setLangCookie(a.dataset.lang);
		});

		/*
		 * İlk ziyarət: kuki yoxdursa və ünvanda dil göstərilməyibsə,
		 * ölkəyə görə uyğun dili soruşuruq. Yoxlama brauzerdə aparılır —
		 * səhifə keşi pozulmur. Axtarış robotları (Google JavaScript-i
		 * icra edir) yönləndirilmir: hər dili öz ünvanından indeksləyirlər.
		 */
		if (!hasLangCookie() && L.current === L.default && location.search.indexOf('nolang') === -1 && !isBot()) {
			try {
				fetch(L.api, { credentials: 'omit' })
					.then(function (r) { return r.json(); })
					.then(function (d) {
						var s = d && d.suggested;
						if (!s || !L.urls[s]) return;
						setLangCookie(s);
						if (s !== L.current) location.replace(L.urls[s]);
					})
					.catch(function () {});
			} catch (e) {}
		}
	}

	function isBot() {
		try {
			return new RegExp(L.bots || 'bot', 'i').test(navigator.userAgent || '');
		} catch (e) {
			return false;
		}
	}

	function hasLangCookie() {
		return doc.cookie.indexOf(L.cookie + '=') !== -1;
	}

	function setLangCookie(code) {
		try {
			doc.cookie = L.cookie + '=' + code + ';path=/;max-age=31536000;samesite=lax';
		} catch (e) {}
	}
})();
