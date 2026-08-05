# Manual QA checklist — browser JS

Run before every release that touches `assets/js/list-editor.js`, the editor
form, or `assets/js/list-ajax-nav.js`. The automated suite cannot cover browser
behavior; this list can be done in ~10 minutes on the docker dev site.

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

- [ ] Click "2": the items swap in place, no full page reload, no console errors
- [ ] View source: **no inline `<script>` inside the list**, and `list-ajax-nav.js`
      is present in the footer (not 404ing)
- [ ] Load a page with **no** ajax list on it: `list-ajax-nav.js` is absent entirely
      (also covered by PHPUnit — `AjaxNavTest::test_a_front_end_page_with_no_ajax_list_registers_but_does_not_enqueue_the_script`)
- [ ] List rendered **late**, after `wp_footer` priority 20 (e.g. a snippet plugin doing
      `add_action('wp_footer', fn() => echo do_shortcode('[postlist id="N"]'), 30)`):
      `list-ajax-nav.js` still ships, because the enqueue prints the tag directly once
      `wp_print_footer_scripts` has already run
- [ ] Loading state: `.w4pl-loading` is on `#w4pl-list-<ID>` during the fetch and
      cleared afterwards (add a temporary CSS rule to see it)
- [ ] Paginate twice in a row: the nav inside the swapped-in markup still works
      (the listener is delegated, so it survives the swap)
- [ ] **Two ajax lists on one page**: paging one leaves the other untouched, and
      `list-ajax-nav.js` is loaded exactly once
- [ ] Non-ajax list (`[nav type="plain"]`) still does a normal full-page navigation
- [ ] JS disabled: page links still work as plain hrefs (progressive enhancement)
- [ ] Ctrl/Cmd-click and middle-click a page link: opens in a new tab as normal
      (a deliberate improvement over the 2.x jQuery handler, which hijacked these)
- [ ] Network failure mid-fetch (devtools → offline, then click "2"): the loading
      class clears and the browser falls through to a normal page load — no dead
      end, no unhandled promise rejection
- [ ] Reload / deep-link `?page<ID>=2` in a fresh tab: the server renders page 2
- [ ] Page served from a full-page cache / CDN: first click still works (no nonce
      in the URL, so cached HTML is fine)
- [ ] Admin **Live preview** pane with an ajax-nav list: renders with no
      `jQuery is not defined` error in the console (the 2.x snippet threw there)

## Front-end asset footprint (M12)

Through 2.x, three admin stylesheets and two admin scripts were registered on
every front-end request. They were never enqueued there, so nothing should
change visually — but the admin side must be re-checked, because the same
method now runs on `admin_enqueue_scripts` only.

- [ ] View source on any front-end page: no `form.css`, `list-editor.css`,
      `admin-documentation.css`, `form.js` or `list-editor.js` tags
- [ ] Same page: **no `jquery.min.js` loaded on the plugin's account** on a block
      theme (a classic theme may still load it for its own reasons)
- [ ] Front end with an ajax list still looks and behaves exactly as before
- [ ] List editor screen (`w4pl` → edit): CSS/JS still load — form styling intact,
      CodeMirror, sortable rows, tag inserter and preview all still work
- [ ] Documentation page (Lists → Documentation): stylesheet still loads, page is
      not unstyled
- [ ] Block editor: insert/edit the W4 Post List block, list picker works and the
      server-side render preview shows the list

## CodeMirror

- [ ] Template, CSS and JS fields render as CodeMirror editors (line numbers, highlighting)
- [ ] Type in the Template editor, switch List Type (Posts → Terms): form refreshes and **editors re-initialize** (not blank textareas, not frozen)
- [ ] Values typed in the editors survive a list-type switch (serialize reads synced textareas)
- [ ] Save the list: template/CSS/JS persist exactly as typed
- [ ] Click a tag in the "Template Tags" panel: it inserts **at the cursor inside CodeMirror**

## AJAX refresh

- [ ] Change List Type: Publish button is truly disabled during refresh (attribute, not just style)
- [ ] Check 3 post-type checkboxes quickly: only ~one refresh fires (debounce)
- [ ] Simulate failure (devtools → offline, then change list type): spinner clears, error notice appears above the form, entered values still present, Publish re-enabled

## Layout

- [ ] Add 4+ Meta Query rows: pane grows, no overlap with the publish area
- [ ] Resize the window with a tall tab open: height recalculates

## Shortcode box

- [ ] "Display this list" box appears in the sidebar with the correct `[postlist id="N"]`
- [ ] Copy button copies; "Copied!" confirmation shows; works on a draft (with publish reminder)

## Live preview

- [ ] Preview button under the editor toggles the pane; first open renders the current (unsaved) settings
- [ ] Change list type or template, wait for the form refresh: an open preview refreshes itself
- [ ] Starter template + preview: pick a starter, preview shows the new layout with its CSS
- [ ] Preview of a list with an error (e.g. temporarily break the template) shows the error message in the status line, not a broken pane

## Validation & review prompt (M8)

- [ ] Save a list with "ten" in Items per page: saves fine, warning notice explains the coercion
- [ ] Save a template with a typo'd tag ([post_titel]): warning suggests [post_title]
- [ ] Switch list type with an incompatible template: inline warning appears with a working "Replace with the default template" button (into CodeMirror)
- [ ] Check "Any" post status: concrete statuses uncheck, and vice versa
- [ ] Quick-edit a list title: list options survive untouched
- [ ] (Time-gated) Review prompt appears on the Lists screen 7+ days after first publish; dismiss is permanent

## Error surfacing

- [ ] `[postlist id="99999"]` on a page: logged-in as editor → inline notice; logged-out → nothing
- [ ] New list: "No items text" is prefilled with "No items found."; existing lists unchanged
