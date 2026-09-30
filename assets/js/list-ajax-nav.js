/**
 * AJAX pagination for lists rendered with [nav ajax="1"], and the visitor
 * sort dropdown (form.w4pl-sort) of lists with visitor sorting enabled.
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

	// Browsers without these keep plain, full-page-reload pagination, and a
	// sort form with a visible submit button.
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
	 * The sort form applies itself on change, so its submit button is only
	 * for visitors without JavaScript.
	 */
	function hideSortButtons(root) {
		var buttons = root.querySelectorAll('form.w4pl-sort .w4pl-sort-submit');
		for (var i = 0; i < buttons.length; i++) {
			buttons[i].style.display = 'none';
		}
	}

	/**
	 * The URL a GET submit of `form` would load. The action is read as an
	 * attribute: a field named "action" would shadow the form.action property.
	 */
	function sortUrl(form) {
		var url = new URL(form.getAttribute('action') || window.location.href, window.location.href);
		url.search = new URLSearchParams(new FormData(form)).toString();
		url.hash = '';
		return url.toString();
	}

	/**
	 * Apply a sort form: swap the list over AJAX when the form asks for it,
	 * otherwise submit it as a normal GET.
	 */
	function applySort(form) {
		var wrapper = form.closest('[id^="w4pl-list-"]');

		var canSerialize = window.URL && window.URLSearchParams && window.FormData;

		if (form.getAttribute('data-ajax') !== '1' || !wrapper || !canSerialize) {
			// Via the prototype: a field named "submit" would shadow form.submit.
			HTMLFormElement.prototype.submit.call(form);
			return;
		}

		var select = form.querySelector('select');
		swapPage(wrapper, sortUrl(form), select ? select.id : '');
	}

	/**
	 * Replace the .w4pl-inner subtree of `wrapper` with the matching subtree of
	 * the document at `url`. Same contract as the old jQuery .load() call: only
	 * the inner subtree is swapped, and scripts in the response never run.
	 *
	 * `focusId` names an element to refocus after the swap, so a keyboard
	 * user who changed the sort dropdown does not lose their place.
	 */
	function swapPage(wrapper, url, focusId) {
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
			hideSortButtons(wrapper);
			clearLoading();

			if (focusId) {
				var focusTarget = wrapper.querySelector('[id="' + focusId + '"]');
				if (focusTarget) {
					focusTarget.focus();
				}
			}
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

	document.addEventListener('change', function (event) {
		var target = event.target;
		if (!target || target.tagName !== 'SELECT' || !target.closest) {
			return;
		}

		var form = target.closest('form.w4pl-sort');
		if (form) {
			applySort(form);
		}
	});

	// Enter on a focused control, or a button a theme made visible again.
	document.addEventListener('submit', function (event) {
		var form = event.target;
		if (!form || !form.matches || !form.matches('form.w4pl-sort') || form.getAttribute('data-ajax') !== '1') {
			return;
		}

		if (!form.closest('[id^="w4pl-list-"]') || !window.URL || !window.URLSearchParams || !window.FormData) {
			return;
		}

		event.preventDefault();
		applySort(form);
	});

	hideSortButtons(document);

	// A list rendered after the footer scripts prints this file before its
	// own markup, so look again once the whole document is in.
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			hideSortButtons(document);
		});
	}
})();
