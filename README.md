# W4 Post List — WordPress Plugin

Build the lists WordPress core still can't: posts **grouped** under year, month, category or author headings; category and term indexes; user directories; and combined Terms + Posts and Users + Posts lists. Output is controlled by a template of plain HTML and simple template tags, so the markup stays clean, semantic and completely yours.

Place any list anywhere with the **W4 Post List block**, the `[postlist id="123"]` shortcode, or the classic widget.

**Plugin page:** [wordpress.org/plugins/w4-post-list](https://wordpress.org/plugins/w4-post-list/)

## What can you build?

* **Year/month archives** — posts grouped under date headings
* **Category, tag or custom taxonomy indexes** — with or without each term's posts listed underneath
* **Author and user directories** — list users by role, with avatars, bios and each user's posts
* **Filtered post lists** — any post type, filtered by status, taxonomy terms, custom fields (meta query), dates (date query), authors, parents and more
* **Media lists** — image and attachment lists by mime type

## List types

* Posts
* Terms
* Users
* Terms + Posts
* Users + Posts

## Features

* **Grouping** — by year, month, month + year, any taxonomy, author or parent (the core Query Loop block can't do this)
* **Ordering** — by ID, title, slug, publish/modified date, menu order, comment count, custom field value, or random
* **Pagination** — next/previous links or numeric navigation, with optional AJAX page loading
* **Template-driven output** — HTML plus template tags like `[post_title]`, `[post_permalink]`, `[featured_image]`, `[post_meta key="..."]`; start from a preset or write your own markup
* **Lightweight** — [independently measured by WP Hive](https://wphive.com/plugins/w4-post-list/) at ~19 KB memory and +0.05 s page-speed impact, better than 99% of tested plugins

Full [template tag reference](https://w4dev.com/docs/w4-post-list/faqs/what-are-the-available-template-tags/) and [live examples with copy-paste templates](https://w4dev.com/wp/w4-post-list-examples/).

## Installation

1. Install from Plugins → Add New (search for "W4 Post List"), or upload the zip to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins menu.
3. Find the **W4 Post List** menu (list icon) in your admin sidebar. Create a list, publish it, then place it with the block or the `[postlist id="123"]` shortcode.

## Development

```bash
npm install && composer install   # install dependencies

npm run start-block               # block editor dev (hot reload)
npm run build-block               # build block assets

npm run build-plugin              # full build: i18n → version sync → LESS → minify
npm run zip-plugin                # distribution zip

./vendor/bin/phpcs                # WordPress Coding Standards check
```

Version is managed in `package.json` (source of truth) and synced into PHP files by `npm run build-plugin`.

## License

GPLv3 — see [LICENSE](LICENSE).
