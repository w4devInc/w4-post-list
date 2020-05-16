<?php
/**
 * Doc Template
 *
 * @package W4_Post_List
 */

?>
	<h2><?php _e( 'What is Template?', 'w4-post-list' ); ?></h2>
	<p><?php _e( 'Template handles the html output of your list. It is designed with Templace Tags to display dynamic information and HTML to manage layout. Availability of Template structure & tags varies based on the type of list you have selected, and it\'s parent element.', 'w4-post-list' ); ?></p>

	<h2><?php _e( 'Examples', 'w4-post-list' ); ?></h2>
	<h3><?php _e( 'Post List', 'w4-post-list' ); ?></h3>
	<pre>[posts]
	&lt;ul&gt;
		&lt;li&gt;&lt;a href=&quot;[post_link]&quot;&gt;[post_title]&lt;/a&gt;&lt;li&gt;
	&lt;/ul&gt;
[/posts]</pre>


	<h3><?php _e( 'Post list (with excerpt limited to 20 words, post class on post wrapper element)', 'w4-post-list' ); ?></h3>
	<pre>[posts]
	&lt;div class=&quot;[post_class]&quot;&gt;
		&lt;h3&gt;&lt;a href=&quot;[post_link]&quot;&gt;[post_title]&lt;/a&gt;&lt;/h3&gt;
		&lt;p&gt;[post_excerpt wordlimit=&quot;20&quot;]&lt;/p&gt;
	&lt;/div&gt;
[/posts]</pre>

	<h3><?php _e( 'Post list (grouped by year - chose <code>Group By</code> option to Year while using this).', 'w4-post-list' ); ?></h3>
	<pre><code>[groups]
&lt;ul&gt;
	&lt;li&gt;
		&lt;a href=&quot;[group_link]&quot;&gt;[group_name]&lt;/a&gt;
		[posts]
		&lt;ol&gt;
			&lt;li&gt;&lt;a href=&quot;[post_link]&quot;&gt;[post_title]&lt;/a&gt;&lt;li&gt;
		&lt;/ol&gt;
		[/posts]
	&lt;li&gt;
&lt;/ul&gt;
[/groups]</code></pre>

	<h3><?php _e( 'Category list', 'w4-post-list' ); ?></h3>
	<pre><code>[terms]
&lt;ul&gt;
	&lt;li&gt;&lt;a href=&quot;[term_link]&quot;&gt;[term_name]&lt;/a&gt;&lt;li&gt;
&lt;/ul&gt;
[/terms]</code></pre>


	<h3><?php _e( 'Category + Post list', 'w4-post-list' ); ?></h3>
	<pre><code>[terms]
&lt;ul&gt;
	&lt;li&gt;
		&lt;a href=&quot;[term_link]&quot;&gt;[term_name]&lt;/a&gt;
		[posts]
		&lt;ol&gt;
			&lt;li&gt;&lt;a href=&quot;[post_link]&quot;&gt;[post_title]&lt;/a&gt;&lt;li&gt;
		&lt;/ol&gt;
		[/posts]
	&lt;li&gt;
&lt;/ul&gt;
[/terms]</code></pre>

	<h3><?php _e( 'User list', 'w4-post-list' ); ?></h3>
	<pre><code>[users]
&lt;ul&gt;
	&lt;li&gt;&lt;a href=&quot;[user_link]&quot;&gt;[user_name]&lt;/a&gt;&lt;li&gt;
&lt;/ul&gt;
[/users]</code></pre>

	<h3><?php _e( 'User + Post list', 'w4-post-list' ); ?></h3>
	<pre><code>[users]
&lt;ul&gt;
	&lt;li&gt;
		&lt;a href=&quot;[user_link]&quot;&gt;[user_name]&lt;/a&gt;
		[posts]
		&lt;ol&gt;
			&lt;li&gt;&lt;a href=&quot;[post_link]&quot;&gt;[post_title]&lt;/a&gt;&lt;li&gt;
		&lt;/ol&gt;
		[/posts]
	&lt;li&gt;
&lt;/ul&gt;
[/users]</code></pre>
