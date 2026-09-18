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
})();
