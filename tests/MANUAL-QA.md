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

## Visitor sorting (#17)

PHPUnit (`VisitorSortTest`) covers the server side: opt-in, the token
whitelist, the per-list `w4pl_sort_<ID>` parameter, the form markup and hidden
fields, and that pagination links carry the sort. The items below are the
browser half.

Fixture: two posts lists on one page, Items per page 3, four "Visitor sorting"
orders ticked. List A in a W4 Post List **block** with
`<ul>[posts]<li>[post_title]</li>[/posts]</ul>[nav type="plain" ajax="1"]`;
list B in a shortcode block with
`[sort label="Order"]<ul>[posts]<li>[post_title]</li>[/posts]</ul>[nav type="plain"]`.

> Executed 2026-10-01 on the feature branch (pimi-canvas block theme, no jQuery
> on the page, headless Chromium via the Playwright docker image). Boxes record
> that run.

- [x] With JS on, each list shows its dropdown **and** its "Sort" button (the button
      is the keyboard path, so it is never hidden)
- [x] List A: picking "Title: A to Z" with the mouse swaps the list in place (no
      navigation), the dropdown keeps focus and shows the new choice, list B is untouched
- [x] Keyboard on list A: an arrow key plus the `change` Windows fires with it does
      **not** reload the list; Enter in the dropdown applies it over AJAX, focus kept
- [x] List A, then click "2": still no navigation, page 2 continues A to Z, and it
      matches a fresh load of `?w4pl_sort_<A>=title-asc&page<A>=2`
- [x] List B: picking an order with the mouse does a normal GET to
      `?w4pl_sort_<B>=…`, the list is re-sorted, and its page links carry the sort
- [x] List B by keyboard: Tab to "Sort", Enter: normal GET with the new order
- [x] `[sort label="Order"]` places the dropdown where the tag is, with that label
- [x] JS off: choosing an order and pressing "Sort" loads the sorted list and keeps
      unrelated query parameters (`utm_source`)
- [x] No console errors
- [x] Editor: the "Visitor sorting" checkboxes show the saved state, and changing
      them survives Update

**Lists with "Maximum items" or "Offset"** never offer sorting, ticked or not.
Add list C (four orders ticked, Maximum items 5, Items per page 2, template
`[sort label="Order"]<ul>…</ul>[nav type="plain" ajax="1"]`) and list D (four
orders ticked, Offset 2) to the same page. Executed 2026-10-01, same setup.

- [x] Lists C and D show no dropdown, no "Sort" button and no `[sort]` text; view
      source has nothing sort-related inside either list
- [x] `?w4pl_sort_<C>=title-asc&w4pl_sort_<D>=title-asc`: both lists keep their
      configured order and still show no dropdown
- [x] From that URL, click "2" on list C: AJAX swap, same items as a fresh
      `?page<C>=2`, still no dropdown
- [x] Lists A and B on the same page still sort (AJAX and plain GET) and leave
      C and D untouched
- [x] JS off: list C has no form and keeps its order
- [x] Editor, list C: a note beside the "Visitor sorting" checkboxes says sorting is
      off because Maximum items or Offset is set; the boxes stay ticked and enabled,
      survive Update, and Update shows no sort warning
- [x] Editor, list C: clear Maximum items and Update: the note goes, and the
      front end shows the dropdown again without re-ticking anything
- [x] Editor, list A: no note

- [ ] Real Windows keyboard (not a replayed event): arrowing through a closed
      dropdown does not reload; Enter applies
- [ ] Classic theme (Twenty Twenty-One): repeat the two list A mouse items
- [ ] Live preview pane: the dropdown shows, disabled

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
