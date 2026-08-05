/**
 * AJAX pagination for lists rendered with [nav ajax="1"].
 *
 * Replaces the inline jQuery snippet that shipped through 2.x. Block themes do
 * not enqueue jQuery on the front end, so that snippet threw a ReferenceError
 * and pagination silently did nothing. This has no dependencies and needs no
 * per-list inline script: one delegated listener serves every list on the page.
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

	// Monotonic token so a slow response can never overwrite a newer one.
	var requestId = 0;

	/**
	 * The list wrapper owning a clicked pagination link, or null when the link
	 * is not inside an ajax-enabled W4 Post List navigation.
	 */
	function getWrapper(link) {
		var nav = link.closest('.navigation.ajax-navigation');
		if (!nav) {
			return null;
		}

		var wrapper = nav.closest('[id^="w4pl-list-"]');
		if (!wrapper || !wrapper.id) {
			return null;
		}

		return wrapper;
	}

	/**
	 * Replace the .w4pl-inner subtree of `wrapper` with the matching subtree of
	 * the document at `url`. Same contract as the old jQuery .load() call: only
	 * the inner subtree is swapped, and scripts in the response never run.
	 */
	function swapPage(wrapper, url) {
		var token = ++requestId;
		wrapper.w4plRequest = token;

		wrapper.classList.add(LOADING_CLASS);
		wrapper.setAttribute('aria-busy', 'true');

		var clearLoading = function () {
			wrapper.classList.remove(LOADING_CLASS);
			wrapper.removeAttribute('aria-busy');
		};

		window.fetch(url, {
			credentials: 'same-origin',
			headers: { 'X-Requested-With': 'XMLHttpRequest' }
		}).then(function (response) {
			if (!response.ok) {
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
				throw new Error('missing fragment');
			}

			wrapper.innerHTML = fresh.outerHTML;
			clearLoading();
		}).catch(function () {
			if (wrapper.w4plRequest !== token) {
				return;
			}

			clearLoading();

			// Never dead-end the visitor: fall back to a normal page load.
			window.location.href = url;
		});
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
		if (!wrapper) {
			return;
		}

		event.preventDefault();
		swapPage(wrapper, link.href);
	});
})();
