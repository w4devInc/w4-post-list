# W4 Post List - WordPress Plugin

Display Posts (or any custom post type), Terms (or any custom taxonomy), Users (any role) on Content or Widget Areas by placing a shortcode. Select what to show and design how to show it. Using the plugin is really easy. You will find Tinymce button on post/page editor to quickly inset a list. Also, there's a separate page for creating or editing list.

## List Types
* Posts
* Terms
* Users
* Terms & Posts
* Users & Posts

Creating a list is just few steps. There are different sets option for different List Type, following options are available for List Type - `posts`.

### Posts
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

### Group Results by
* year
* month
* month year
* category, post tag or custom taxonomies
* authors
* parents

### Order Results by
* post id
* post title
* post name
* post publish date
* post modified date
* menu order
* approved comment count
* meta value
* or random

### Multi-Page Pagination by
* Next / Previous links
* Numeric navigation flat - Ex: 1, 2, 3.
* Numeric navigation showing in unordered list.
* Enable/Disable pagination by ajax


### Creating Template
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


Check all of the available shortcodes on [W4 Post List Plugin Page](http://w4dev.com/plugins/w4-post-list).


### Check Example
* [Football Team Showcase](http://w4dev.com/wp/w4-post-list#w4pl-list-07081c7dd0982d2f4a7de78ce2398e8b)
* [Posts in Table](http://w4dev.com/wp/w4-post-list#w4pl-list-a4aad4240af859f0fb3c8b2bfaf56806)
* [Simple Posts List](http://w4dev.com/wp/w4-post-list-examples#example-1)
* [Media List](http://w4dev.com/wp/w4-post-list-examples#example-2)
* [Year/Month Archive](http://w4dev.com/wp/w4-post-list-examples#example-3)
* [List of Categories](http://w4dev.com/wp/w4-post-list-examples#example-4)
* [List of Terms](http://w4dev.com/wp/w4-post-list-examples#example-5)


## Installation
1. Upload zip to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Find W4 post list menu under Posts Menu. Add and manage post list from there.
