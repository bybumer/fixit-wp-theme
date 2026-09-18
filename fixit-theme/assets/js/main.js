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
})();
