# Manual QA checklist — list editor JS

Run before every release that touches `assets/js/list-editor.js` or the editor
form. The automated suite cannot cover browser behavior; this list can be done
in ~5 minutes on the docker dev site.

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
