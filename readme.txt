=== W4 Post List ===
Contributors: sajib1223
Tags: post list, user list, post grid, category list, shortcode
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.6.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Build lists the Query Loop can't: posts grouped by year, category or author, term lists and user directories — via block, shortcode or widget.


== Description ==

W4 Post List builds the lists that WordPress core still can't: posts **grouped** under year, month, category or author headings; category and term indexes; user directories; and combined Terms + Posts and Users + Posts lists. You control the output with a template of plain HTML and simple template tags, so the markup stays clean, semantic and completely yours.

Place any list anywhere with the **W4 Post List block**, the `[postlist id="123"]` shortcode, or the classic widget.

= What can you build? =

* **Year/month archives** — posts grouped under date headings
* **Category, tag or custom taxonomy indexes** — with or without each term's posts listed underneath
* **Author and user directories** — list users by role, with avatars, bios and each user's posts
* **Filtered post lists** — any post type, filtered by status, taxonomy terms, custom fields, dates, authors, parents and more
* **Media lists** — image and attachment lists by mime type

= Five list types =

* Posts
* Terms
* Users
* Terms + Posts
* Users + Posts

= Query options (Posts) =

* Post type, status and mime type
* Search keyword
* Include/exclude posts by ID, exclude the current post
* Filter by parent, author, taxonomy terms (tax query), custom fields (meta query) and dates (date query)
* Items per page, with multi-page pagination

= Group results by =

* Year, month, or month + year
* Category, tag or any custom taxonomy
* Author
* Parent

= Order results by =

* ID, title, slug, publish date, modified date, menu order, comment count, custom field value, or random

= Pagination =

* Next/previous links or numeric navigation, with optional AJAX page loading

= Full control over the output =

Output is template-driven: every list has an HTML template with template tags like `[post_title]`, `[post_permalink]`, `[featured_image]`, `[post_author_name]` and `[post_meta key="..."]`. Start from a preset and tweak it, or write your own markup for pixel-perfect control. If you want a drag-and-drop visual builder, this plugin isn't that — it keeps you close to your own HTML, which is exactly why themes and developers like it.

See the [full template tag reference](https://w4dev.com/docs/w4-post-list/faqs/what-are-the-available-template-tags/) and [live examples with copy-paste templates](https://w4dev.com/wp/w4-post-list-examples/).

= Lightweight, independently measured =

[WP Hive](https://wphive.com/plugins/w4-post-list/) independently measures W4 Post List at **19 KB memory usage and +0.05 s page-speed impact — better than 99% of the plugins they test**. No bundled frameworks, no frontend bloat.


== Installation ==

1. Install from Plugins → Add New (search for "W4 Post List"), or upload the zip to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins menu.
3. Find the **W4 Post List** menu (list icon) in your admin sidebar. Create a list, publish it, then place it on any page with the W4 Post List block or the `[postlist id="123"]` shortcode.


== Frequently Asked Questions ==

= How do I display a list? =

Three ways: add the **W4 Post List block** and pick your list; paste the shortcode `[postlist id="123"]` into any content; or add the W4 Post List widget to a widget area. Each list's ID and ready-made shortcode are shown in the Shortcode column on the All Lists screen.

= How do I list posts from a specific category? =

Create a Posts list, open the **Posts: Tax Query** section, and select your taxonomy and terms. You can combine multiple term filters, and filter by custom fields and dates the same way.

= Why does my list show nothing on the page? =

The usual causes, in order: the list is not **Published** yet; the shortcode ID doesn't match (check the Shortcode column on the All Lists screen); the template is missing its loop tags (a Posts list template needs `[posts]...[/posts]`, Terms needs `[terms]...[/terms]`, Users needs `[users]...[/users]`); or the query simply matched no items. Opening the list and re-checking the query options usually finds it.

= Can I display custom fields (including ACF)? =

Yes. Use `[post_meta key="your_field_key"]` inside the posts loop. Fields stored as plain values (text, numbers, URLs) work best; complex/serialized fields are output as stored.

= Can I group posts by year, category or author? =

Yes — that's this plugin's specialty. Set **Group by** to year, month, month + year, any taxonomy, author or parent, and the list renders grouped sections with headings. The core Query Loop block can't do this.

= Can I list users or build an author directory? =

Yes. Use the **Users** list type (filter by role, order by name/registration date) or **Users + Posts** to show each user with their latest posts. Template tags cover avatars, display names, bios and profile URLs.

= Does it work with the block editor and block themes? =

Yes. The W4 Post List block renders your list server-side with a live preview in the editor, and the shortcode works in classic editors, page builders and template files (`echo do_shortcode( '[postlist id="123"]' );`).

= Where is the documentation? =

In your admin: **W4 Post List → Documentation** (template tags reference, examples, usage). Online: [w4dev.com/docs/w4-post-list](https://w4dev.com/docs/w4-post-list/faqs/what-are-the-available-template-tags/).


== Screenshots ==

1. The list editor — choose a list type, then configure query, template and style options per section.
2. Front-end output — an AJAX-paginated post list, an image/media grid, and a year/month grouped archive.


== Changelog ==
= 2.6.0 =
* New: Automated test suite now gates every release - characterization snapshots freeze the rendered output of all five list types (including grouped lists and pagination) so updates can no longer break existing lists unnoticed.
* New: List options now carry a schema version with a lazy migration path, protecting saved lists during future upgrades.
* New: Opt-in usage counters (lists created/published) added to the existing Appsero telemetry. Nothing is collected without your explicit consent.
* Fix: PHP 8.2+ deprecation notices from the query classes (dynamic properties).
* Fix: Unit-suffixed dimensions in template tags (e.g. [post_thumbnail width="50px"]) caused a PHP notice and broken output on PHP 7.x - dimensions are now parsed forgivingly (50px, 50 and "50" all work).
* Dev: Docs-drift guard - documentation examples, default templates and presets are verified in CI against the registered template tags.
* Dev: Coding-standards nonce exclusion scoped to legacy files only; new endpoints are always linted for nonce verification.
= 2.5.9 =
* New: Added a WordPress Playground blueprint that powers the Live Preview button on the wordpress.org plugin page.
= 2.5.8 =
* Fix: Documentation examples used unregistered template tags ([post_link], [group_name], [group_link]) and invalid HTML, so copy-pasting them produced broken output. All examples now use registered tags with valid loop markup.
* New: "Getting Started" documentation tab with a step-by-step walkthrough, now the default tab.
* Fix: The Documentation page is now visible to everyone who can edit lists (previously required the delete_users capability, hiding it from Editors).
* Fix: The "Include users" and "Exclude users" fields were incorrectly described as taking term IDs; corrected, along with a typo cleanup across admin field descriptions.
* Fix: Added missing text domain to the Appsero opt-in notice string.
= 2.5.7 =
* Improved: Rewrote the wordpress.org listing for accuracy — removed the reference to the retired TinyMCE button, repositioned the description around grouped lists and user directories, expanded the FAQ from real support questions, updated screenshots and fixed typos.
* Improved: Plugin header description.
* Note: No functional changes in this release.
= 2.5.6 =
* New: Post status options now include all registered statuses, including custom ones (e.g. "Expired" from job listing plugins) - https://github.com/w4devInc/w4-post-list/issues/88.
* Tested up to WordPress 7.0.2.
= 2.5.5 =
* Tested up to WordPress 6.9.1.
= 2.5.4 =
* Fix: Sanitized media image width and height attributes - https://github.com/w4devInc/w4-post-list/issues/64.
= 2.5.3 =
* Fix: Fixed version number.
= 2.5.1 =
* Fix: Fixed textdomain issue.


[See changelog of all versions](https://raw.githubusercontent.com/w4devInc/w4-post-list/master/CHANGELOG.txt).

== Upgrade Notice ==
= 2.6.0 =
Adds automated release testing, options versioning and PHP 8.2 deprecation fixes. No changes to list output.
= 2.5.9 =
Adds a Live Preview blueprint for the wordpress.org plugin page. No functional changes.
= 2.5.8 =
Documentation fixes: examples now copy-paste correctly, new Getting Started guide, docs visible to Editors.
= 2.5.7 =
Listing and documentation accuracy update — no functional changes.

== Privacy Policy ==
W4 Post List uses [Appsero](https://appsero.com) SDK to collect some telemetry data upon user's confirmation. This helps us to troubleshoot problems faster & make product improvements.

Appsero SDK **does not gather any data by default.** The SDK only starts gathering basic telemetry data **when a user allows it via the admin notice**. We collect the data to ensure a great user experience for all our users.

Integrating Appsero SDK **DOES NOT IMMEDIATELY** start gathering data, **without confirmation from users in any case.**

Learn more about how [Appsero collects and uses this data](https://appsero.com/privacy-policy/).
