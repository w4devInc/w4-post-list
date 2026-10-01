# Manual QA checklist — browser JS

Run before every release that touches `assets/js/list-editor.js`, the editor
form, or `assets/js/list-ajax-nav.js`. The automated suite cannot cover browser
behavior; this list can be done in ~10 minutes on the docker dev site.

> **Executed 2026-09-11 against 3.0.5** (WP 7.1, Twenty Twenty-Five, real docker
> dev site, headless Chromium 143). First execution since the 3.0.0 rewrite.
> **43 of 44 passed. One real defect found: see the CodeMirror section.**
> Boxes below record that run; re-verify them for the next release.

## Front-end AJAX pagination (M12)

PHPUnit covers the server side only — that no inline jQuery is emitted, that the
`w4pl-ajax-nav` handle registers and enqueues exactly when a rendered list uses
`[nav ajax="1"]`, and that the wrapper markup carries the hooks the script keys
off. **Everything below is browser-only and is not covered by any test.**

Fixture: a posts list, Items per page 2 (so 3+ pages), template
`<ul>[posts]<li>[post_title]</li>[/posts]</ul>[nav type="plain" ajax="1"]`.

Test on a **block theme (Twenty Twenty-Five)** first — block themes do not
enqueue jQuery on the front end, which is the bug this replaces — then repeat
the first three items on a classic theme (Twenty Twenty-One).

- [x] Click "2": the items swap in place, no full page reload, no console errors
- [x] View source: **no inline `<script>` inside the list**, and `list-ajax-nav.js`
      is present in the footer (not 404ing)
- [x] Load a page with **no** ajax list on it: `list-ajax-nav.js` is absent entirely
      (also covered by PHPUnit — `AjaxNavTest::test_a_front_end_page_with_no_ajax_list_registers_but_does_not_enqueue_the_script`)
- [x] List rendered **late**, after `wp_footer` priority 20 (e.g. a snippet plugin doing
      `add_action('wp_footer', fn() => echo do_shortcode('[postlist id="N"]'), 30)`):
      `list-ajax-nav.js` still ships, because the enqueue prints the tag directly once
      `wp_print_footer_scripts` has already run
- [x] Loading state: `.w4pl-loading` is on `#w4pl-list-<ID>` during the fetch and
      cleared afterwards (add a temporary CSS rule to see it)
- [x] Paginate twice in a row: the nav inside the swapped-in markup still works
      (the listener is delegated, so it survives the swap)
- [x] **Two ajax lists on one page**: paging one leaves the other untouched, and
      `list-ajax-nav.js` is loaded exactly once
- [x] Non-ajax list (`[nav type="plain"]`) still does a normal full-page navigation
- [x] JS disabled: page links still work as plain hrefs (progressive enhancement)
- [x] Ctrl/Cmd-click and middle-click a page link: opens in a new tab as normal
      (a deliberate improvement over the 2.x jQuery handler, which hijacked these)
- [x] Network failure mid-fetch (devtools → offline, then click "2"): the loading
      class clears and the browser falls through to a normal page load — no dead
      end, no unhandled promise rejection
- [x] Reload / deep-link `?page<ID>=2` in a fresh tab: the server renders page 2
- [x] Page served from a full-page cache / CDN: first click still works (no nonce
      in the URL, so cached HTML is fine)
- [x] Admin **Live preview** pane with an ajax-nav list: renders with no
      `jQuery is not defined` error in the console (the 2.x snippet threw there)

## AJAX pagination: page kept in the address bar

The script records each AJAX page in the address bar as `page<ID>=N` (one
history entry per page, like the plain links), so Back, Forward and reload land
on the page the visitor was reading. PHPUnit (`AjaxNavTest`) covers the server
half: the URL alone decides the page, each list reads only its own parameter,
links to page one carry no parameter, and a junk value renders page one.
**Everything below is browser-only.**

Fixture: one page with list A (`[nav type="plain" ajax="1"]`), list B
(`[nav ajax="1"]`, the Previous/Next style) and list C (`[nav type="plain"]`),
Items per page 3, each item linking to its post
(`<li><a href="[post_permalink]">[post_title]</a></li>`).

> Executed 2026-10-01 on the fix branch (WP 7.1.2, pimi-canvas block theme, no
> jQuery on the page, headless Chromium 143 via the Playwright docker image).
> Boxes record that run.

- [x] List A: click "2", then "3": items swap in place, the address bar reads
      `?page<A>=2` then `?page<A>=3`, no full reload
- [x] Back, Back, Forward, Forward: pages 2, 1, 2, 3 swap in place; on page 1 the
      address bar has no `page<A>` left
- [x] **On page 3, open a post, press Back: the list is on page 3** (the bug this
      fixes; it used to be page 1)
- [x] Back again after that: page 2, then page 1, then Forward to page 2. Each
      shows the right items. (On a block theme the step to page 1 is a full
      reload: core's Interactivity API reloads history entries left by an earlier
      document. The page is still right, because the URL carries it.)
- [x] Reload on page 3: still page 3
- [x] Two lists: A to 2, B to 3 with "Next": the address bar holds both
      (`?page<A>=2&page<B>=3`); Back moves only B; reload restores both
- [x] B's "Previous" down to page 1 and A's "1" link: each drops only its own
      parameter; with both on page 1 the address bar is clean
- [x] Start from `?utm_source=qa&x%5B%5D=1&q=a+b#frag`: paging adds `page<A>=2`
      before the `#frag` and leaves the rest byte-for-byte; back on page 1 the
      original URL is restored exactly
- [x] On `?page<A>=2`, click an in-page `#anchor` link, press Back: no request,
      the list does not move
- [x] Back then Forward (and Back, Back) faster than the responses arrive (throttle
      the network): the list ends on the page the address bar shows, with no
      loading state left behind
- [x] List C (not ajax): a page link is a normal full-page navigation, as before
- [x] After list A pages to 3, list C's links carry `page<A>=3`: paging C reloads
      the page with A still on 3. Before A pages, C's links are untouched
- [x] After list A pages to 3, Ctrl/Cmd-click "Next" on list B: the new tab has
      A on 3 and B on 2
- [x] Network failure on a page click: the browser loads `?page<A>=2` normally
- [x] Ctrl/Cmd-click a page link: new tab on that page, this tab untouched
- [x] JS disabled: page links and the "1" link work as plain hrefs
- [x] No console errors
- [ ] Return from a post restored from the **back/forward cache** (a real desktop
      or mobile browser; headless Chromium never used it in the run above): the
      list is on the page it was left on
- [ ] Safari and Firefox: the first three items
- [ ] Classic theme (Twenty Twenty-One): the first three items; every Back and
      Forward step should swap in place there

## Front-end asset footprint (M12)

Through 2.x, three admin stylesheets and two admin scripts were registered on
every front-end request. They were never enqueued there, so nothing should
change visually — but the admin side must be re-checked, because the same
method now runs on `admin_enqueue_scripts` only.

- [x] View source on any front-end page: no `form.css`, `list-editor.css`,
      `admin-documentation.css`, `form.js` or `list-editor.js` tags
- [x] Same page: **no `jquery.min.js` loaded on the plugin's account** on a block
      theme (a classic theme may still load it for its own reasons)
- [x] Front end with an ajax list still looks and behaves exactly as before
- [x] List editor screen (`w4pl` → edit): CSS/JS still load — form styling intact,
      CodeMirror, sortable rows, tag inserter and preview all still work
- [x] Documentation page (Lists → Documentation): stylesheet still loads, page is
      not unstyled
- [x] Block editor: insert/edit the W4 Post List block, list picker works and the
      server-side render preview shows the list

## CodeMirror

- [x] Template, CSS and JS fields render as CodeMirror editors (line numbers, highlighting)
      *(CSS and JS live in the collapsed "Style" tab; they refresh correctly when it is opened)*
- [x] Type in the Template editor, switch List Type (Posts → Terms): form refreshes and **editors re-initialize** (not blank textareas, not frozen)
- [x] Values typed in the editors survive a list-type switch (serialize reads synced textareas)
- [ ] Save the list: template/CSS/JS persist exactly as typed
      **FAILS as of 3.0.5 — every backslash is stripped on save.** `content:"\f101"`
      saves as `content:"f101"`; a JS regex `/\d+\s\w/` saves as `/d+sw/`. Cause:
      `admin/class-admin-lists-metaboxes.php:183` calls `stripslashes_deep()` and then
      passes the result to `update_post_meta()`, which unslashes again. Note the live
      preview uses `wp_unslash()` correctly, so the preview renders the value you typed
      and only the *saved* copy is corrupted.
- [x] Click a tag in the "Template Tags" panel: it inserts **at the cursor inside CodeMirror**

## AJAX refresh

- [x] Change List Type: Publish button is truly disabled during refresh (attribute, not just style)
- [x] Check 3 post-type checkboxes quickly: only ~one refresh fires (debounce)
- [x] Simulate failure (devtools → offline, then change list type): spinner clears, error notice appears above the form, entered values still present, Publish re-enabled

## Layout

- [x] Add 4+ Meta Query rows: pane grows, no overlap with the publish area
- [x] Resize the window with a tall tab open: height recalculates

## Shortcode box

- [x] "Display this list" box appears in the sidebar with the correct `[postlist id="N"]`
- [x] Copy button copies; "Copied!" confirmation shows; works on a draft (with publish reminder)

## Live preview

- [x] Preview button under the editor toggles the pane; first open renders the current (unsaved) settings
- [x] Change list type or template, wait for the form refresh: an open preview refreshes itself
- [x] Starter template + preview: pick a starter, preview shows the new layout with its CSS
- [x] Preview of a list with an error (e.g. temporarily break the template) shows the error message in the status line, not a broken pane

## Validation & review prompt (M8)

- [x] Save a list with "ten" in Items per page: saves fine, warning notice explains the coercion
- [x] Save a template with a typo'd tag ([post_titel]): warning suggests [post_title]
- [x] Switch list type with an incompatible template: inline warning appears with a working "Replace with the default template" button (into CodeMirror)
- [x] Check "Any" post status: concrete statuses uncheck, and vice versa
- [x] Quick-edit a list title: list options survive untouched
- [x] (Time-gated) Review prompt appears on the Lists screen 7+ days after first publish; dismiss is permanent

## Error surfacing

- [x] `[postlist id="99999"]` on a page: logged-in as editor → inline notice; logged-out → nothing
- [x] New list: "No items text" is prefilled with "No items found."; existing lists unchanged
