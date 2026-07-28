<?php
/**
 * Built-in starter template library. Returned by W4PL_Starter_Templates.
 *
 * Generated content; strings use real tabs/newlines. Labels/descriptions
 * are translated at registry load.
 *
 * @package W4_Post_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'simple-list'          => array(
		'label'       => __( 'Simple list', 'w4-post-list' ),
		'description' => __( 'A plain bulleted list of post titles you can click, with page navigation at the bottom.', 'w4-post-list' ),
		'list_type'   => 'posts',
		'template'    => '<div class="w4pl-simple-list">
	<ul class="simple-list-items">
		[posts]
		<li class="post-item">
			<a class="post-title" href="[post_permalink]">[post_title]</a>
		</li>
		[/posts]
	</ul>
	[nav]
</div>',
		'css'         => '#w4pl-list-[listid] .simple-list-items {
	margin: 0;
	padding-left: 1.25em;
}
#w4pl-list-[listid] .post-item {
	margin-bottom: 0.5em;
}',
		'thumbnail'   => '',
		'options'     => array(
			'posts_per_page' => 10,
			'orderby'        => 'date',
			'order'          => 'DESC',
		),
	),
	'thumbnail-cards'      => array(
		'label'       => __( 'Thumbnail cards', 'w4-post-list' ),
		'description' => __( 'A grid of cards showing each post\'s picture, title, and a short preview, the grid adapts to the available width, one card per row on phones.', 'w4-post-list' ),
		'list_type'   => 'posts',
		'template'    => '<div class="w4pl-cards">
	[posts]
	<div class="post-item">
		<a class="post-thumb" href="[post_permalink]">
			[featured_image size="medium"]
		</a>
		<h3 class="post-title">
			<a href="[post_permalink]">[post_title]</a>
		</h3>
		<div class="post-excerpt">
			[post_excerpt wordlimit="15"]
		</div>
	</div>
	[/posts]
</div>
[nav]',
		'css'         => '#w4pl-list-[listid] .w4pl-cards {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
	gap: 1.5em;
}
#w4pl-list-[listid] .post-item {
	display: flex;
	flex-direction: column;
	border: 1px solid #e5e5e5;
	border-radius: 6px;
	overflow: hidden;
}
#w4pl-list-[listid] .post-thumb {
	display: block;
	line-height: 0;
}
#w4pl-list-[listid] .post-thumb img {
	width: 100%;
	height: auto;
}
#w4pl-list-[listid] .post-title {
	margin: 0.75em 1em 0.25em;
	font-size: 1.1em;
}
#w4pl-list-[listid] .post-title a {
	text-decoration: none;
}
#w4pl-list-[listid] .post-excerpt {
	margin: 0 1em 1em;
	font-size: 0.9em;
	color: #666;
}',
		'thumbnail'   => '',
		'options'     => array(
			'posts_per_page' => 9,
		),
	),
	'title-date-list'      => array(
		'label'       => __( 'Title & date list', 'w4-post-list' ),
		'description' => __( 'A clean archive-style list where each post title sits on the left and its publish date on the right, separated by thin divider lines.', 'w4-post-list' ),
		'list_type'   => 'posts',
		'template'    => '<div class="w4pl-title-date-list">
	[posts]
	<div class="post-item">
		<a class="post-title" href="[post_permalink]">[post_title]</a>
		<span class="post-date">[post_date format="M j, Y"]</span>
	</div>
	[/posts]
</div>
[nav]',
		'css'         => '#w4pl-list-[listid] .w4pl-title-date-list .post-item {
	display: flex;
	align-items: baseline;
	justify-content: space-between;
	gap: 1em;
	padding: 0.6em 0;
	border-bottom: 1px solid #e5e5e5;
}
#w4pl-list-[listid] .w4pl-title-date-list .post-item:last-of-type {
	border-bottom: none;
}
#w4pl-list-[listid] .w4pl-title-date-list .post-title {
	text-decoration: none;
}
#w4pl-list-[listid] .w4pl-title-date-list .post-title:hover {
	text-decoration: underline;
}
#w4pl-list-[listid] .w4pl-title-date-list .post-date {
	white-space: nowrap;
	color: #777;
	font-size: 0.875em;
}',
		'thumbnail'   => '',
		'options'     => array(
			'posts_per_page' => 10,
			'orderby'        => 'date',
			'order'          => 'DESC',
		),
	),
	'year-archive'         => array(
		'label'       => __( 'Year archive', 'w4-post-list' ),
		'description' => __( 'A classic blog archive that groups your posts under big year headings, with each year showing a simple list of clickable post titles and dates.', 'w4-post-list' ),
		'list_type'   => 'posts',
		'template'    => '<div class="w4pl-year-archive">
	[groups]
	<section class="group-item">
		<h3 class="group-title">[group_title]</h3>
		<ul class="archive-posts">
			[posts]
			<li class="post-item">
				<a class="post-title" href="[post_permalink]">[post_title]</a>
				<span class="post-date">[post_date format="F j"]</span>
			</li>
			[/posts]
		</ul>
	</section>
	[/groups]
	[nav]
</div>',
		'css'         => '#w4pl-list-[listid] .w4pl-year-archive {
	display: flex;
	flex-direction: column;
	gap: 2em;
}
#w4pl-list-[listid] .group-title {
	margin: 0 0 0.5em;
	padding-bottom: 0.3em;
	border-bottom: 1px solid #e0e0e0;
}
#w4pl-list-[listid] .archive-posts {
	list-style: none;
	margin: 0;
	padding: 0;
	display: flex;
	flex-direction: column;
	gap: 0.5em;
}
#w4pl-list-[listid] .post-item {
	display: flex;
	justify-content: space-between;
	align-items: baseline;
	gap: 1em;
}
#w4pl-list-[listid] .post-date {
	color: #888;
	font-size: 0.875em;
	white-space: nowrap;
}',
		'thumbnail'   => '',
		'options'     => array(
			'groupby'        => 'year',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'posts_per_page' => 20,
		),
	),
	'category-index'       => array(
		'label'       => __( 'Category index', 'w4-post-list' ),
		'description' => __( 'A neat multi-column index of your categories, each shown as a clickable name with a small badge indicating how many posts it contains.', 'w4-post-list' ),
		'list_type'   => 'terms',
		'template'    => '<ul class="w4pl-category-index">
	[terms]
	<li class="term-item">
		<a class="term-name" href="[term_link]">[term_name]</a>
		<span class="term-count">[term_count]</span>
	</li>
	[/terms]
</ul>',
		'css'         => '#w4pl-list-[listid] .w4pl-category-index {
	list-style: none;
	margin: 0;
	padding: 0;
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
	gap: 0.5em 1.5em;
}
#w4pl-list-[listid] .term-item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 0.5em;
	padding: 0.35em 0;
	border-bottom: 1px solid #eee;
}
#w4pl-list-[listid] .term-name {
	text-decoration: none;
}
#w4pl-list-[listid] .term-name:hover {
	text-decoration: underline;
}
#w4pl-list-[listid] .term-count {
	font-size: 0.8em;
	background: #f2f2f2;
	color: #666;
	border-radius: 999px;
	padding: 0.1em 0.6em;
}',
		'thumbnail'   => '',
		'options'     => array(
			'terms_taxonomy' => 'category',
			'terms_orderby'  => 'name',
			'terms_order'    => 'ASC',
		),
	),
	'category-posts-index' => array(
		'label'       => __( 'Category index with posts', 'w4-post-list' ),
		'description' => __( 'An organized index where each category appears as a bold heading with its posts listed underneath as clickable links.', 'w4-post-list' ),
		'list_type'   => 'terms.posts',
		'template'    => '<div class="w4pl-category-index">
	[terms]
	<section class="term-item">
		<h3 class="term-name">
			<a href="[term_link]">[term_name]</a>
		</h3>
		<ul class="term-posts">
			[posts]
			<li class="post-item">
				<a class="post-title" href="[post_permalink]">[post_title]</a>
			</li>
			[/posts]
		</ul>
	</section>
	[/terms]
</div>',
		'css'         => '#w4pl-list-[listid] .w4pl-category-index {
	display: flex;
	flex-direction: column;
	gap: 1.5em;
}
#w4pl-list-[listid] .term-item {
	padding-bottom: 1em;
	border-bottom: 1px solid #e5e5e5;
}
#w4pl-list-[listid] .term-name {
	margin: 0 0 0.5em;
}
#w4pl-list-[listid] .term-name a {
	text-decoration: none;
}
#w4pl-list-[listid] .term-posts {
	margin: 0;
	padding-left: 1.25em;
}
#w4pl-list-[listid] .post-item {
	margin: 0.25em 0;
}',
		'thumbnail'   => '',
		'options'     => array(
			'terms_taxonomy' => 'category',
			'terms_orderby'  => 'name',
			'terms_order'    => 'ASC',
			'limit'          => 5,
			'orderby'        => 'title',
			'order'          => 'ASC',
		),
	),
	'user-directory'       => array(
		'label'       => __( 'User directory cards', 'w4-post-list' ),
		'description' => __( 'A member directory shown as a grid of centered cards, each with the person\'s photo, their name linked to their posts, and a short bio.', 'w4-post-list' ),
		'list_type'   => 'users',
		'template'    => '<div class="w4pl-user-directory">
	[users]
	<div class="user-item">
		<div class="user-avatar">[user_avatar]</div>
		<h3 class="user-name">
			<a href="[user_link]">[user_name]</a>
		</h3>
		<p class="user-bio">[user_bio]</p>
	</div>
	[/users]
</div>',
		'css'         => '#w4pl-list-[listid] .w4pl-user-directory {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
	gap: 1.25em;
}
#w4pl-list-[listid] .user-item {
	text-align: center;
	padding: 1.5em 1em;
	border: 1px solid #e5e5e5;
	border-radius: 8px;
}
#w4pl-list-[listid] .user-avatar img {
	border-radius: 50%;
	max-width: 100%;
	height: auto;
}
#w4pl-list-[listid] .user-name {
	margin: 0.75em 0 0.25em;
}
#w4pl-list-[listid] .user-name a {
	text-decoration: none;
}
#w4pl-list-[listid] .user-bio {
	margin: 0;
	font-size: 0.9em;
	color: #666;
}',
		'thumbnail'   => '',
		'options'     => array(
			'users_orderby' => 'display_name',
			'users_order'   => 'ASC',
		),
	),
	'authors-posts'        => array(
		'label'       => __( 'Author sections', 'w4-post-list' ),
		'description' => __( 'Each author appears as a heading row with their photo and name, followed by a short list of their latest posts you can click to read.', 'w4-post-list' ),
		'list_type'   => 'users.posts',
		'template'    => '<div class="w4pl-author-sections">
	[users]
	<section class="user-item">
		<div class="author-heading">
			<span class="user-avatar">[user_avatar]</span>
			<h3 class="user-name">
				<a href="[user_link]">[user_name]</a>
			</h3>
		</div>
		<ul class="author-posts">
			[posts]
			<li class="post-item">
				<a class="post-title" href="[post_permalink]">[post_title]</a>
				<span class="post-date">[post_date format="M j, Y"]</span>
			</li>
			[/posts]
		</ul>
	</section>
	[/users]
</div>',
		'css'         => '#w4pl-list-[listid] .w4pl-author-sections {
	display: flex;
	flex-direction: column;
	gap: 2em;
}
#w4pl-list-[listid] .author-heading {
	display: flex;
	align-items: center;
	gap: 0.75em;
	padding-bottom: 0.5em;
	border-bottom: 1px solid #e5e5e5;
}
#w4pl-list-[listid] .user-avatar img {
	border-radius: 50%;
	width: 48px;
	height: 48px;
}
#w4pl-list-[listid] .user-name {
	margin: 0;
}
#w4pl-list-[listid] .author-posts {
	list-style: none;
	margin: 0.75em 0 0;
	padding: 0;
	display: flex;
	flex-direction: column;
	gap: 0.5em;
}
#w4pl-list-[listid] .post-item {
	display: flex;
	align-items: baseline;
	justify-content: space-between;
	gap: 1em;
}
#w4pl-list-[listid] .post-date {
	color: #888;
	font-size: 0.875em;
	white-space: nowrap;
}',
		'thumbnail'   => '',
		'options'     => array(
			'limit'         => 5,
			'orderby'       => 'date',
			'order'         => 'DESC',
			'users_orderby' => 'display_name',
			'users_order'   => 'ASC',
		),
	),
);
