=== W4 Post List ===
Contributors: sajib1223
Tags: post, post list, custom post list, custom post type, widget, shortcode, media
Requires at least: 5.2
Tested up to: 5.5
Requires PHP: 5.6
Stable tag: 2.3.6
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

W4 Post List lets you create a list of posts, terms, users or a combined one. Decorate output using shortcodes. It's just easy and fun.


== Description ==

Display Posts (any custom post type), Terms (any custom taxonomy), Users (any role) on Content or Widget Areas by placing a shortcode. Select what to show and design how to show it. Using the plugin is really easy. You will find Tinymce button on post/page editor to quickly inset a list. Also, there's a separate page for creating or editing list.

= List Types =
* Posts
* Terms
* Users
* Terms & Posts
* Users & Posts

Creating a list is just few steps. There are different sets option for different List Type, following options are available for List Type - `posts`.
= Posts =
* post type
* post mime type
* post status
* post search keyword
* include post by ids
* exclude post by ids
* exclude current post
* posts per page - while using pagination
* post by parent ids
* post by author ids
* post by terms ( tax_query )
* post by meta ( meta_query )
* post by year, month, day ( date_query )

= Group Results by =
* year
* month
* month year
* category, post tag or custom taxonomies
* authors
* parents

= Order Results by =
* post id
* post title
* post name
* post publish date
* post modified date
* menu order
* approved comment count
* meta value
* or random

= Multi-Page Pagination by =
* Next / Previous links
* Numeric navigation flat - Ex: 1, 2, 3.
* Numeric navigation showing in unordered list.
* Enable/Disable pagination by ajax


= To Create Template =
Templates are designed using Shortcodes. You can create a simple list just showing post title and linked to the post page, or you can display complex list using any of the information relating to post. Some of the available shortcodes are -

* post thumbnail
* post categories
* post tags
* post custom taxonomy terms
* post author name / links / avatar
* post publish time
* post modified time
* post excerpt
* post content
* post meta value (multiple times, with multiple meta keys)
* media thumbnail


Check all of the [available shortcodes](https://w4dev.com/docs/w4-post-list/faqs/what-are-the-available-template-tags/) here.


= Check Example =
* [Simple Posts List](https://w4dev.com/wp/w4-post-list-examples/#example-1)
* [Media List](https://w4dev.com/wp/w4-post-list-examples/#example-2)
* [Year/Month Archive](https://w4dev.com/wp/w4-post-list-examples/#example-3)
* [List of Categories](https://w4dev.com/wp/w4-post-list-examples/#example-4)
* [List of Terms](https://w4dev.com/wp/w4-post-list-examples/#example-5)


== Installation ==

1. Upload zip file to the `/wp-content/plugins/` directory
2. Activate the plugin through the `Plugins` menu in WordPress
3. You will find W4 post list menu below Posts Menu. Create / manage your list from there.


== Frequently Asked Questions ==

= How to display a list =

Each list have a unique id. Display a list by using `[postlist id="LIST_ID"]`.


== Screenshots ==

1. Settings Panel
2. Preview 1
3. Preview 2

== Changelog ==
= 2.3.5 =
* Updated: WordPress version compatibility meta.
= 2.3.4 =
* Fix: Replace depreciated jQuery method live.
* Updated: Appsero client library updated to 1.1.11.
= 2.3.3 =
* Fix: Using 0 (zero) for current author posts were not working.
= 2.3.2 =
* Fix: `strip_shortcodes` attribute of [post_excerpt] template tag were not working.
= 2.3.1 =
* New: Added new attribute `strip_shortcodes` to [post_excerpt] template tag.
* Fix: [post_thumbnail] template tag were missing return attribute. We propose you to use `output` rather than `return`, for now `return` were kept as fallback.
= 2.2.0 =
* New: Add a list to content editor using Gutenberg block editor.

[See changelog of all versions](https://raw.githubusercontent.com/w4devInc/w4-post-list/master/CHANGELOG.txt).

== Upgrade Notice ==
= 2.3.4 =
* Updated plugin to make it compatible with WordPress 5.5. WordPress 5.5 has updated jQuery version to 1.12.4 and removed jQuery migrate that was used to support depreciated jquery functions.
= 2.3.3 =
* Fix: Using 0 (zero) for current author posts were not working.
= 2.3.2 =
* Fix: `strip_shortcodes` attribute of [post_excerpt] template tag were not working.
= 2.3.1 =
* New: Added new attribute `strip_shortcodes` to [post_excerpt] template tag.
* Fix: [post_thumbnail] template tag were missing return attribute. We propose you to use `output` rather than `return`, for now `return` were kept as fallback.
= 2.3.0 =
* Improvement: Codebase has been upgraded to follow WordPress coding standards.

== Privacy Policy ==
W4 Post List uses [Appsero](https://appsero.com) SDK to collect some telemetry data upon user's confirmation. This helps us to troubleshoot problems faster & make product improvements.

Appsero SDK **does not gather any data by default.** The SDK only starts gathering basic telemetry data **when a user allows it via the admin notice**. We collect the data to ensure a great user experience for all our users.

Integrating Appsero SDK **DOES NOT IMMEDIATELY** start gathering data, **without confirmation from users in any case.**

Learn more about how [Appsero collects and uses this data](https://appsero.com/privacy-policy/).
