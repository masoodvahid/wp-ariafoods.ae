/* Aria Foods Modern — no dependencies. */
(function () {
	'use strict';
	var d = document, root = d.documentElement, body = d.body;
	var $ = function (s, c) { return (c || d).querySelector(s); };
	var $$ = function (s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); };

	/* ---------- Header: drawer + submenus (bound once; header is not swapped) ---------- */
	var menuBtn = $('.menu-btn');
	function closeMenu() {
		body.classList.remove('nav-open');
		if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
	}
	if (menuBtn) {
		menuBtn.addEventListener('click', function () {
			var open = body.classList.toggle('nav-open');
			menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}
	d.addEventListener('click', function (e) {
		var t = e.target.closest('.sub-toggle');
		if (!t) return;
		var sub = d.getElementById(t.getAttribute('aria-controls'));
		var open = t.getAttribute('aria-expanded') !== 'true';
		$$('.sub-toggle[aria-expanded=true]').forEach(function (o) {
			if (o !== t) { o.setAttribute('aria-expanded', 'false'); var s = d.getElementById(o.getAttribute('aria-controls')); if (s) s.classList.remove('is-open'); }
		});
		t.setAttribute('aria-expanded', open ? 'true' : 'false');
		if (sub) sub.classList.toggle('is-open', open);
	});
	d.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') return;
		$$('.sub.is-open').forEach(function (s) { s.classList.remove('is-open'); });
		$$('.sub-toggle[aria-expanded=true]').forEach(function (t) { t.setAttribute('aria-expanded', 'false'); });
		if (body.classList.contains('nav-open')) { closeMenu(); menuBtn.focus(); }
	});

	/* ---------- Colour theme: light by default, dark or system only when chosen ---------- */
	var root = d.documentElement, pick = $('.theme-pick');
	function themeMark() {
		var cur = root.getAttribute('data-theme') || 'light';
		$$('[data-set-theme]').forEach(function (b) { b.setAttribute('aria-checked', b.getAttribute('data-set-theme') === cur ? 'true' : 'false'); });
		var dark = cur === 'dark' || (cur === 'system' && window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches);
		var m = $('meta[name=theme-color]'); if (m) m.setAttribute('content', dark ? '#0d0b0a' : '#1c1512');
	}
	function themeMenu(open) {
		if (!pick) return;
		pick.classList.toggle('is-open', open);
		var b = $('.theme-btn', pick); if (b) b.setAttribute('aria-expanded', open ? 'true' : 'false');
	}
	d.addEventListener('click', function (e) {
		var s = e.target.closest('[data-set-theme]');
		if (s) {
			var t = s.getAttribute('data-set-theme');
			root.classList.add('theme-anim');
			root.setAttribute('data-theme', t);
			try { localStorage.setItem('aria-theme', t); } catch (err) {}
			themeMark(); themeMenu(false);
			setTimeout(function () { root.classList.remove('theme-anim'); }, 400);
			return;
		}
		if (e.target.closest('.theme-btn')) { themeMenu(!pick.classList.contains('is-open')); return; }
		if (pick && !e.target.closest('.theme-pick')) themeMenu(false);
	});
	d.addEventListener('keydown', function (e) { if (e.key === 'Escape') themeMenu(false); });
	if (window.matchMedia) { var mq = matchMedia('(prefers-color-scheme: dark)'); if (mq.addEventListener) mq.addEventListener('change', themeMark); }
	themeMark();

	/* ---------- Quote pop-up (floating button) ---------- */
	var qm = $('#quote-modal'), qmTimer = null, qmLast = null;
	function qmOpen(product, port) {
		if (qm && port) { var pf = qm.querySelector('input[name=port]'); if (pf && !pf.value) pf.value = port; }
		if (!qm || typeof qm.showModal !== 'function') return false;
		clearTimeout(qmTimer);
		qmLast = document.activeElement;
		if (product) {
			var sel = qm.querySelector('select[name=product]');
			if (sel) { for (var i = 0; i < sel.options.length; i++) { if (sel.options[i].text === product || sel.options[i].value === product) { sel.selectedIndex = i; break; } } }
		}
		if (!qm.open) qm.showModal();
		document.documentElement.classList.add('qmodal-on');
		requestAnimationFrame(function () { requestAnimationFrame(function () { qm.classList.add('is-open'); }); });
		var first = qm.querySelector('input:not([type=hidden]):not([tabindex="-1"])');
		if (first) setTimeout(function () { first.focus({ preventScroll: true }); }, 60);
		return true;
	}
	function qmClose() {
		if (!qm || !qm.open) return;
		qm.classList.remove('is-open');
		document.documentElement.classList.remove('qmodal-on');
		qmTimer = setTimeout(function () { qm.close(); if (qmLast && qmLast.focus) qmLast.focus({ preventScroll: true }); }, 320);
	}
	d.addEventListener('click', function (e) {
		var o = e.target.closest('[data-quote-open]');
		if (o && !e.metaKey && !e.ctrlKey && !e.shiftKey) {
			if (body.classList.contains('nav-open') && typeof closeMenu === 'function') closeMenu();
			if (qmOpen(o.getAttribute('data-product') || (d.body.classList.contains('single-product') ? (($('.prod-title') || $('h1') || {}).textContent || '').trim() : ''), o.getAttribute('data-port'))) e.preventDefault();
			return;
		}
		if (e.target.closest('[data-quote-close]') || e.target === qm) qmClose();
	});
	if (qm) qm.addEventListener('cancel', function (e) { e.preventDefault(); qmClose(); });

	/* ---------- Page modules (re-run after every instant navigation) ---------- */
	function initTabs(scope) {
		$$('[data-tabs]', scope).forEach(function (box) {
			var tabs = $$('[role=tab]', box);
			function select(tab, focus) {
				tabs.forEach(function (t) {
					var on = t === tab;
					t.setAttribute('aria-selected', on ? 'true' : 'false');
					t.tabIndex = on ? 0 : -1;
					var p = d.getElementById(t.getAttribute('aria-controls'));
					if (p) p.hidden = !on;
				});
				if (focus) tab.focus();
			}
			tabs.forEach(function (t, i) {
				t.addEventListener('click', function () { select(t); });
				t.addEventListener('keydown', function (e) {
					var n = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
					if (!n) return;
					e.preventDefault();
					select(tabs[(i + n + tabs.length) % tabs.length], true);
				});
			});
		});
	}

	function initFilters(scope) {
		$$('[data-filter]', scope).forEach(function (group) {
			var grid = $(group.getAttribute('data-filter'));
			if (!grid) return;
			$$('button', group).forEach(function (b) {
				b.addEventListener('click', function () {
					var kind = b.getAttribute('data-kind');
					$$('button', group).forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
					$$('.pcard', grid).forEach(function (c) { c.hidden = kind !== 'all' && c.getAttribute('data-kind') !== kind; });
				});
			});
		});
	}

	function initYouTube(scope) {
		$$('div.yt[data-yt]', scope).forEach(function (div) {
			var id = div.getAttribute('data-yt');
			var b = d.createElement('button');
			b.type = 'button'; b.className = 'yt'; b.setAttribute('data-yt', id); b.setAttribute('aria-label', 'Play video');
			b.innerHTML = '<img src="https://i.ytimg.com/vi/' + id + '/hqdefault.jpg" alt="" loading="lazy" width="480" height="360"><svg class="yt-play" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13l11-6.5-11-6.5Z" fill="currentColor"/></svg>';
			div.replaceWith(b);
		});
	}
	d.addEventListener('click', function (e) {
		var b = e.target.closest('button.yt[data-yt]');
		if (!b) return;
		var f = d.createElement('iframe');
		f.src = 'https://www.youtube-nocookie.com/embed/' + b.getAttribute('data-yt') + '?autoplay=1&rel=0&playsinline=1';
		f.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
		f.allowFullscreen = true;
		f.title = b.getAttribute('aria-label') || 'Video';
		b.replaceChildren(f);
		b.removeAttribute('data-yt');
	});

	/* Links that preselect a product in the quote form */
	d.addEventListener('click', function (e) {
		var a = e.target.closest('a[data-product]');
		if (!a) return;
		var form = $('#form-quote');
		if (!form) return;
		var sel = form.querySelector('select[name=product]');
		var want = a.getAttribute('data-product').toLowerCase();
		if (sel) {
			for (var i = 0; i < sel.options.length; i++) {
				if (sel.options[i].text.toLowerCase() === want) { sel.selectedIndex = i; break; }
			}
		}
		setTimeout(function () { var f = form.querySelector('input[name=name]'); if (f) f.focus({ preventScroll: true }); }, 450);
	});

	/* ---------- AJAX forms ---------- */
	function initForms(scope) {
		$$('form[data-ajax-form]', scope).forEach(function (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				var status = form.querySelector('.form-status');
				var btn = form.querySelector('[type=submit]');
				var bad = null;
				$$('[required]', form).forEach(function (f) {
					var ok = f.value.trim() !== '' && (f.type !== 'email' || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(f.value.trim()));
					f.setAttribute('aria-invalid', ok ? 'false' : 'true');
					if (!ok && !bad) bad = f;
				});
				if (bad) {
					status.hidden = false; status.classList.add('is-error');
					status.textContent = 'Please complete the highlighted fields.';
					bad.focus();
					return;
				}
				var page = form.querySelector('input[name=page]');
				if (page) page.value = location.href;
				btn.setAttribute('aria-busy', 'true');
				fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'X-Requested-With': 'fetch' }, credentials: 'same-origin' })
					.then(function (r) { return r.json(); })
					.then(function (j) {
						status.hidden = false;
						status.classList.toggle('is-error', !j.success);
						status.textContent = (j.data && j.data.message) || (j.success ? 'Sent.' : 'Something went wrong.');
						if (j.success) { form.classList.add('is-sent'); form.reset(); }
					})
					.catch(function () {
						status.hidden = false; status.classList.add('is-error');
						status.textContent = 'The request did not go through. Check your connection, or message us on WhatsApp.';
					})
					.then(function () { btn.removeAttribute('aria-busy'); status.focus && status.setAttribute('tabindex', '-1'); status.focus(); });
			});
			$$('input,select,textarea', form).forEach(function (f) {
				f.addEventListener('input', function () { if (f.getAttribute('aria-invalid') === 'true' && f.value.trim()) f.setAttribute('aria-invalid', 'false'); });
			});
		});
	}

	/* ---------- Load more (articles) ---------- */
	function initLoadMore(scope) {
		$$('[data-load-more]', scope).forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				var grid = $(btn.getAttribute('data-load-more'));
				btn.setAttribute('aria-busy', 'true');
				fetch(btn.href, { credentials: 'same-origin' }).then(function (r) { return r.text(); }).then(function (html) {
					var doc = new DOMParser().parseFromString(html, 'text/html');
					var items = $$(btn.getAttribute('data-load-more') + ' > *', doc);
					var first = null;
					items.forEach(function (it) { var n = d.importNode(it, true); grid.appendChild(n); if (!first) first = n; });
					var next = doc.querySelector('[data-load-more]');
					if (next) { btn.href = next.href; btn.removeAttribute('aria-busy'); } else { btn.parentNode.remove(); }
					var link = first && first.querySelector('a'); if (link) link.focus({ preventScroll: true });
					history.replaceState(history.state, '', location.href);
				}).catch(function () { location.href = btn.href; });
			});
		});
	}

	/* ---------- Product gallery + lightbox ---------- */
	function initGallery(scope) {
		$$('[data-gallery]', scope).forEach(function (g) {
			var main = $('.gallery-main', g), img = main && main.querySelector('img');
			var thumbs = $$('.gallery-thumbs button', g), dlg = $('dialog.lightbox', g), idx = 0;
			var items = thumbs.length ? thumbs : $$('[data-full]', g);
			if (!img) return;
			function show(i) {
				var t = thumbs[i]; if (!t) return;
				idx = i;
				img.src = t.dataset.src; img.srcset = t.dataset.srcset || ''; img.alt = t.dataset.alt || '';
				thumbs.forEach(function (b) { b.toggleAttribute('aria-current', b === t); if (b === t) b.setAttribute('aria-current', 'true'); });
			}
			function lb(i) {
				if (!dlg || !items[i]) return;
				idx = i;
				var im = dlg.querySelector('img');
				im.src = items[i].dataset.full || img.currentSrc; im.alt = items[i].dataset.alt || img.alt;
			}
			thumbs.forEach(function (b, i) { b.addEventListener('click', function () { show(i); }); });
			main.addEventListener('click', function () {
				if (!dlg || !dlg.showModal) return;
				lb(idx); dlg.showModal();
			});
			if (dlg) {
				var step = function (n) { var i = (idx + n + items.length) % items.length; lb(i); if (thumbs.length) show(i); };
				var prev = $('.lb-prev', dlg), next = $('.lb-next', dlg);
				if (prev) prev.addEventListener('click', function () { step(-1); });
				if (next) next.addEventListener('click', function () { step(1); });
				$('.lb-close', dlg).addEventListener('click', function () { dlg.close(); });
				dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.close(); });
				dlg.addEventListener('keydown', function (e) { if (e.key === 'ArrowRight') step(1); if (e.key === 'ArrowLeft') step(-1); });
				var x0 = null;
				dlg.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
				dlg.addEventListener('touchend', function (e) { if (x0 === null) return; var dx = e.changedTouches[0].clientX - x0; if (Math.abs(dx) > 40 && items.length > 1) step(dx < 0 ? 1 : -1); x0 = null; });
			}
		});
	}

	function initPage(scope) {
		initTabs(scope); initFilters(scope); initYouTube(scope); initForms(scope); initLoadMore(scope); initGallery(scope);
	}
	initPage(d);

	/* ---------- Instant navigation: prefetch on intent, swap <main> ---------- */
	if (!window.fetch || !window.DOMParser || !history.pushState || body.classList.contains('admin-bar')) return;
	var cache = new Map(), progress = $('.progress'), current = null;
	var skip = /\/(wp-admin|wp-login\.php|gate|wp-json|feed|my-account)\b|\.(pdf|jpe?g|png|webp|gif|zip|xml|txt|mp4)$/i;

	function eligible(a) {
		if (!a || !a.href || a.target || a.hasAttribute('download') || a.hasAttribute('data-no-pjax') || a.hasAttribute('data-load-more')) return false;
		var u = new URL(a.href, location.href);
		if (u.origin !== location.origin || skip.test(u.pathname) || u.search.indexOf('aria-inquiry') > -1) return false;
		if (u.pathname === location.pathname && u.search === location.search && u.hash) return false;
		return u;
	}
	function key(u) { return u.origin + u.pathname + u.search; }
	function load(u) {
		var k = key(u), hit = cache.get(k);
		if (hit && Date.now() - hit.t < 90000) return hit.p;
		var p = fetch(k, { credentials: 'same-origin', headers: { 'X-Aria-Nav': '1' } }).then(function (r) {
			var type = r.headers.get('content-type') || '';
			if (!r.ok && r.status !== 404) throw new Error(r.status);
			if (type.indexOf('text/html') < 0) throw new Error('type');
			return r.text().then(function (html) { return { html: html, url: r.url }; });
		});
		p.catch(function () { cache.delete(k); });
		cache.set(k, { p: p, t: Date.now() });
		if (cache.size > 24) cache.delete(cache.keys().next().value);
		return p;
	}
	function prefetch(e) {
		var a = e.target.closest && e.target.closest('a');
		var u = eligible(a);
		if (!u || key(u) === key(new URL(location.href))) return;
		if (navigator.connection && (navigator.connection.saveData || /2g/.test(navigator.connection.effectiveType || ''))) return;
		clearTimeout(a._pf);
		a._pf = setTimeout(function () { load(u); }, e.type === 'touchstart' ? 0 : 65);
	}
	d.addEventListener('mouseover', prefetch, { passive: true });
	d.addEventListener('touchstart', prefetch, { passive: true });
	d.addEventListener('focusin', prefetch);

	function swapHead(doc) {
		d.title = doc.title;
		['meta[name=description]', 'link[rel=canonical]', 'meta[property="og:url"]', 'meta[property="og:title"]', 'meta[property="og:description"]', 'meta[property="og:image"]', 'meta[name=robots]', 'script[type="application/ld+json"]'].forEach(function (sel) {
			var a = d.head.querySelector(sel), b = doc.head.querySelector(sel);
			if (a && b) a.replaceWith(d.importNode(b, true));
			else if (a && !b) a.remove();
			else if (!a && b) d.head.appendChild(d.importNode(b, true));
		});
	}
	function go(u, push, scrollY) {
		var token = current = {};
		body.classList.add('is-loading');
		if (progress) { progress.classList.add('is-on'); progress.style.width = '35%'; }
		closeMenu();
		$$('.sub.is-open').forEach(function (s) { s.classList.remove('is-open'); });
		load(u).then(function (res) {
			if (token !== current) return;
			var doc = new DOMParser().parseFromString(res.html, 'text/html');
			var main = doc.getElementById('main'), nav = doc.getElementById('site-nav');
			if (!main) throw new Error('no main');
			var finalUrl = new URL(res.url || u.href);
			finalUrl.hash = u.hash;
			if (push) history.pushState({ y: 0 }, '', finalUrl.href);
			swapHead(doc);
			body.className = doc.body.className;
			$('#main').replaceWith(d.importNode(main, true));
			if (nav) $('#site-nav').innerHTML = nav.innerHTML;
			initPage($('#main'));
			if (u.hash && d.getElementById(u.hash.slice(1))) d.getElementById(u.hash.slice(1)).scrollIntoView();
			else window.scrollTo(0, scrollY || 0);
			$('#main').focus({ preventScroll: true });
			if (progress) { progress.style.width = '100%'; setTimeout(function () { progress.classList.remove('is-on'); progress.style.width = '0'; }, 250); }
			body.classList.remove('is-loading');
			if (typeof window.gtag === 'function') window.gtag('event', 'page_view', { page_location: location.href, page_title: d.title });
		}).catch(function () { location.href = u.href; });
	}
	d.addEventListener('click', function (e) {
		if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
		var a = e.target.closest('a');
		var u = eligible(a);
		if (!u) return;
		e.preventDefault();
		history.replaceState({ y: window.scrollY }, '');
		go(u, true, 0);
	});
	window.addEventListener('popstate', function (e) {
		go(new URL(location.href), false, e.state && e.state.y || 0);
	});
	history.scrollRestoration = 'manual';
})();

/* Certificate viewer: open certificate images in a light box instead of a new tab. */
(function(){
	if(typeof HTMLDialogElement!=='function'){return;}
	var dlg=null,img=null;
	document.addEventListener('click',function(e){
		var a=e.target.closest&&e.target.closest('a[data-cert-view]');
		if(!a||e.ctrlKey||e.metaKey||e.shiftKey){return;}
		e.preventDefault();
		if(!dlg){
			dlg=document.createElement('dialog');dlg.className='cview';
			dlg.innerHTML='<button type="button" class="cview-close" aria-label="Close">&times;</button><img alt="">';
			document.body.appendChild(dlg);img=dlg.querySelector('img');
			dlg.addEventListener('click',function(ev){if(ev.target===dlg||ev.target.classList.contains('cview-close')){dlg.close();}});
		}
		var t=a.querySelector('img');img.src=a.href;img.alt=t?t.alt:'';
		dlg.showModal();
	});
})();

