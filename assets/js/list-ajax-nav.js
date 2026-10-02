/**
 * AJAX pagination for lists rendered with [nav ajax="1"].
 *
 * Replaces the inline jQuery snippet that shipped through 2.x. Block themes do
 * not enqueue jQuery on the front end, so that snippet threw a ReferenceError
 * and pagination silently did nothing. This has no dependencies and needs no
 * per-list inline script: one delegated listener serves every list on the page.
 *
 * The page a list is on lives in the address bar, in the same page{list id}
 * parameter the plain links use, so Back, Forward and reload land on the page
 * the visitor was reading instead of page one.
 *
 * @package W4_Post_List
 * @author Shazzad Hossain Khan
 * @url https://w4dev.com/plugins/w4-post-list
**/
(function () {
	'use strict';

	// Browsers without these keep plain, full-page-reload pagination.
	if (!window.fetch || !window.DOMParser || !Element.prototype.closest) {
		return;
	}

	var LOADING_CLASS = 'w4pl-loading';
	var WRAPPER_PREFIX = 'w4pl-list-';

	// Monotonic token so a slow response can never overwrite a newer one.
	var requestId = 0;

	/*
	 * Each AJAX page gets its own history entry, as each page of a plain,
	 * full-reload list does. Never inside wp-admin, where a list is only a
	 * preview and the address bar belongs to the editor.
	 */
	var useHistory = !!(window.history && window.history.pushState) &&
		!(document.body && document.body.classList.contains('wp-admin'));

	// The query string the server rendered this document from.
	var initialSearch = window.location.search;

	/**
	 * The list wrapper owning a clicked pagination link, or null when the link
	 * is not inside an ajax-enabled W4 Post List navigation.
	 */
	function getWrapper(link) {
		var nav = link.closest('.navigation.ajax-navigation');
		if (!nav) {
			return null;
		}

		var wrapper = nav.closest('[id^="' + WRAPPER_PREFIX + '"]');
		if (!wrapper || !wrapper.id) {
			return null;
		}

		return wrapper;
	}

	/**
	 * The query parameter holding a list's page: page{list id}.
	 */
	function pageKey(wrapper) {
		return 'page' + wrapper.id.slice(WRAPPER_PREFIX.length);
	}

	/**
	 * A query-string name or value, decoded; returned as-is when malformed.
	 */
	function decode(part) {
		try {
			return decodeURIComponent(part.replace(/\+/g, ' '));
		} catch (e) {
			return part;
		}
	}

	/**
	 * The page number `key` holds in a query string, as a string. Anything
	 * but a plain whole number above one is page one. Every link the plugin
	 * renders is in that form; only a hand-edited URL is not.
	 */
	function readPage(search, key) {
		var pairs = search.replace(/^\?/, '').split('&');
		var page = '1';

		for (var i = 0; i < pairs.length; i++) {
			var at = pairs[i].indexOf('=');
			if (at < 0 || decode(pairs[i].slice(0, at)) !== key) {
				continue;
			}

			var value = decode(pairs[i].slice(at + 1));
			page = /^\d{1,9}$/.test(value) && parseInt(value, 10) > 1 ? String(parseInt(value, 10)) : '1';
		}

		return page;
	}

	/**
	 * `search` with `key` set to `page`, or removed for page one. Every other
	 * parameter is kept as written: the string is edited, not parsed and
	 * rebuilt, so nothing that belongs to another list, a plugin or a
	 * campaign link is re-encoded.
	 */
	function writePage(search, key, page) {
		var pairs = search.replace(/^\?/, '').split('&');
		var kept = [];

		for (var i = 0; i < pairs.length; i++) {
			if (pairs[i] && decode(pairs[i].split('=')[0]) !== key) {
				kept.push(pairs[i]);
			}
		}

		if (page !== '1') {
			kept.push(encodeURIComponent(key) + '=' + page);
		}

		return kept.length ? '?' + kept.join('&') : '';
	}

	/**
	 * The page `wrapper` is showing: what the last swap put there, else what
	 * the server rendered.
	 */
	function shownPage(wrapper, key) {
		return wrapper.w4plPage || readPage(initialSearch, key);
	}

	/**
	 * This document's URL without query string or hash, to fetch from. Built
	 * from protocol and host rather than used as a bare path: a path starting
	 * with "//" would otherwise be read as another site.
	 */
	function baseUrl() {
		var loc = window.location;

		return loc.protocol + '//' + loc.host + loc.pathname;
	}

	function clearLoading(wrapper) {
		wrapper.classList.remove(LOADING_CLASS);
		wrapper.removeAttribute('aria-busy');
	}

	/**
	 * Drop whatever request `wrapper` is waiting on; its response is ignored.
	 */
	function cancelRequest(wrapper) {
		wrapper.w4plRequest = ++requestId;
		wrapper.w4plPopTarget = null;
		clearLoading(wrapper);
	}

	/**
	 * Point every list's pagination links at the current URL, each with its
	 * own page number. Links are rendered from the URL of the request that
	 * produced them, so once one list pages over AJAX the others' links no
	 * longer know its page: following one (a list that reloads the page, a
	 * link opened in a new tab) would put the AJAX list back on page one.
	 */
	function syncLinks() {
		var links = document.querySelectorAll('[id^="' + WRAPPER_PREFIX + '"] .navigation a.page-numbers');
		var search = window.location.search;

		for (var i = 0; i < links.length; i++) {
			var wrapper = links[i].closest('[id^="' + WRAPPER_PREFIX + '"]');
			var key = pageKey(wrapper);
			var next = writePage(search, key, readPage(links[i].search, key));

			if (links[i].search !== next) {
				links[i].search = next;
			}
		}
	}

	/**
	 * Replace the .w4pl-inner subtree of `wrapper` with the matching subtree of
	 * the document at `url`. Same contract as the old jQuery .load() call: only
	 * the inner subtree is swapped, and scripts in the response never run.
	 *
	 * `state`, when given, is { key, page, push }: the list's page parameter,
	 * the page `url` holds, and whether to record it as a new history entry
	 * (a click) or not (Back/Forward, where the browser already moved).
	 */
	function swapPage(wrapper, url, state) {
		var token = ++requestId;
		wrapper.w4plRequest = token;

		// The page a Back/Forward request is fetching, while it is in flight.
		wrapper.w4plPopTarget = state && !state.push ? state.page : null;

		wrapper.classList.add(LOADING_CLASS);
		wrapper.setAttribute('aria-busy', 'true');

		// Set when the server answered but the answer is unusable.
		var unusable = false;

		window.fetch(url, {
			credentials: 'same-origin',
			headers: { 'X-Requested-With': 'XMLHttpRequest' }
		}).then(function (response) {
			if (!response.ok) {
				unusable = true;
				throw new Error('HTTP ' + response.status);
			}
			return response.text();
		}).then(function (html) {
			// A newer click on this list already won; drop this response.
			if (wrapper.w4plRequest !== token) {
				return;
			}

			var doc = new DOMParser().parseFromString(html, 'text/html');
			var fresh = doc.querySelector('[id="' + wrapper.id + '"] .w4pl-inner');

			if (!fresh) {
				unusable = true;
				throw new Error('missing fragment');
			}

			wrapper.innerHTML = fresh.outerHTML;
			wrapper.w4plPopTarget = null;
			clearLoading(wrapper);

			if (state) {
				wrapper.w4plPage = state.page;

				if (state.push) {
					pushPage(state.key, state.page);
				}

				syncLinks();
			}
		}).catch(function () {
			if (wrapper.w4plRequest !== token) {
				return;
			}

			wrapper.w4plPopTarget = null;
			clearLoading(wrapper);

			/*
			 * No answer at all: the visitor is offline, or is already on
			 * the way to another page - Firefox and Safari reject pending
			 * requests the moment a navigation starts, and loading a URL
			 * from here would cancel it and drag them back. Do nothing
			 * now, and let this list's links be ordinary links from here
			 * on, so a request that can never succeed is no dead end.
			 */
			if (!unusable) {
				wrapper.w4plNative = true;
				return;
			}

			// The server answered without the list: a normal page load
			// shows the visitor whatever it has to say.
			if (state && !state.push) {
				window.location.reload();
			} else {
				window.location.href = url;
			}
		});
	}

	/**
	 * Add a history entry for the current URL with one list's page changed.
	 *
	 * Built from the address bar as it is now, not as it was at click time:
	 * another list may have moved on while this response was in flight, and
	 * its page must stay in the URL.
	 */
	function pushPage(key, page) {
		var loc = window.location;
		var search = writePage(loc.search, key, page);

		if (search === loc.search) {
			return;
		}

		try {
			// The document's own URL up to the query string, so a URL with
			// credentials in it (user:pass@host) stays same-origin.
			window.history.pushState(null, '', document.URL.split(/[?#]/)[0] + search + loc.hash);
		} catch (e) {
			// Browsers cap pushState calls; the list still paged, only the
			// address bar is behind.
		}
	}

	document.addEventListener('click', function (event) {
		if (event.defaultPrevented || event.button !== 0) {
			return;
		}

		// Let the browser handle open-in-new-tab / new-window / download.
		if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
			return;
		}

		var target = event.target;
		if (!target || !target.closest) {
			return;
		}

		var link = target.closest('a.page-numbers');
		if (!link || !link.href) {
			return;
		}

		var wrapper = getWrapper(link);
		if (!wrapper || wrapper.w4plNative) {
			return;
		}

		event.preventDefault();

		if (!useHistory) {
			swapPage(wrapper, link.href);
			return;
		}

		/*
		 * Only the page number is taken from the link. The request goes to the
		 * current URL with that page set, so it carries whatever page every
		 * other list on this page is on, and it is the same URL the address
		 * bar ends up with: a reload renders what the swap showed.
		 */
		var key = pageKey(wrapper);
		var page = readPage(link.search, key);

		swapPage(wrapper, baseUrl() + writePage(window.location.search, key, page), {
			key: key,
			page: page,
			push: true
		});
	});

	// A page restored from the back/forward cache gets a clean start: a
	// request cut off by leaving it says nothing about the network now.
	window.addEventListener('pageshow', function (event) {
		if (!event.persisted) {
			return;
		}

		var wrappers = document.querySelectorAll('[id^="' + WRAPPER_PREFIX + '"]');
		for (var i = 0; i < wrappers.length; i++) {
			wrappers[i].w4plNative = false;
			clearLoading(wrappers[i]);
		}
	});

	/*
	 * Back / Forward between entries this script added. The browser has
	 * already changed the URL and fires popstate instead of loading it, so
	 * bring each AJAX list whose page no longer matches the URL in line.
	 * A popstate that changes no list's page (a #hash, another script's
	 * entry) is left alone.
	 *
	 * Back and Forward can arrive faster than the responses. A list still
	 * waiting on an earlier Back/Forward is judged by the page that request
	 * is fetching: if the URL has moved off it, the request is replaced, or
	 * just dropped when the list already shows what the URL now asks for.
	 */
	if (useHistory) {
		window.addEventListener('popstate', function () {
			var wrappers = document.querySelectorAll('[id^="' + WRAPPER_PREFIX + '"]');
			var loc = window.location;

			for (var i = 0; i < wrappers.length; i++) {
				var wrapper = wrappers[i];

				if (!wrapper.querySelector('.navigation.ajax-navigation')) {
					continue;
				}

				var key = pageKey(wrapper);
				var page = readPage(loc.search, key);
				var shown = shownPage(wrapper, key);

				if (wrapper.w4plPopTarget) {
					if (page === wrapper.w4plPopTarget) {
						continue;
					}

					if (page === shown) {
						cancelRequest(wrapper);
						continue;
					}
				} else if (page === shown) {
					continue;
				}

				swapPage(wrapper, baseUrl() + loc.search, {
					key: key,
					page: page,
					push: false
				});
			}

			syncLinks();
		});
	}
})();
