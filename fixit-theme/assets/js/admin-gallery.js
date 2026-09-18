/**
 * Admin: məhsulun ekran görüntüləri qalereyası.
 * WordPress media seçicisi ilə şəkil əlavə etmək, silmək və sürüşdürərək sıralamaq.
 */
(function () {
	'use strict';

	var box = document.getElementById('fixit-gallery');
	var input = document.getElementById('fixit_gallery_ids');
	var add = document.getElementById('fixit-gallery-add');
	var l10n = window.fixitGallery || { title: 'Images', button: 'Add', remove: 'Remove' };
	var frame;
	var dragged;

	if (!box || !input || !add) return;

	function sync() {
		var ids = [];
		box.querySelectorAll('figure').forEach(function (f) {
			ids.push(f.dataset.id);
		});
		input.value = ids.join(',');
	}

	function makeFigure(id, src) {
		var f = document.createElement('figure');
		f.dataset.id = id;
		f.draggable = true;

		var img = document.createElement('img');
		img.src = src;
		img.alt = '';

		var btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'fixit-gallery-remove';
		btn.setAttribute('aria-label', l10n.remove);
		btn.textContent = '×';

		f.appendChild(img);
		f.appendChild(btn);
		return f;
	}

	// Silmək
	box.addEventListener('click', function (e) {
		if (e.target.classList.contains('fixit-gallery-remove')) {
			e.target.closest('figure').remove();
			sync();
		}
	});

	// Sürüşdürərək sıralamaq
	box.addEventListener('dragstart', function (e) {
		dragged = e.target.closest('figure');
	});
	box.addEventListener('dragover', function (e) {
		e.preventDefault();
		var over = e.target.closest('figure');
		if (!dragged || !over || over === dragged) return;
		var rect = over.getBoundingClientRect();
		var after = e.clientX > rect.left + rect.width / 2;
		box.insertBefore(dragged, after ? over.nextSibling : over);
	});
	box.addEventListener('drop', function (e) {
		e.preventDefault();
		dragged = null;
		sync();
	});

	// Əlavə etmək
	add.addEventListener('click', function () {
		if (!window.wp || !wp.media) return;

		if (!frame) {
			frame = wp.media({
				title: l10n.title,
				button: { text: l10n.button },
				library: { type: 'image' },
				multiple: true
			});

			frame.on('select', function () {
				frame.state().get('selection').each(function (att) {
					var a = att.toJSON();
					if (box.querySelector('figure[data-id="' + a.id + '"]')) return;
					var src = a.sizes && a.sizes.medium ? a.sizes.medium.url : a.url;
					box.appendChild(makeFigure(a.id, src));
				});
				sync();
			});
		}

		frame.open();
	});
})();
