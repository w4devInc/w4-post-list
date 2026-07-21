<?php
/**
 * Doc Template
 *
 * @package W4_Post_List
 */

?>
<h2>
	<?php esc_html_e( 'What is a Template?', 'w4-post-list' ); ?>
</h2>
<p>
	<?php esc_html_e( 'The template controls the HTML output of your list. It is plain HTML mixed with Template Tags that are replaced with dynamic information. Everything between a loop tag pair (for example [posts] and [/posts]) is repeated once per item, so wrapper elements like <ul> belong outside the loop tags and the per-item markup goes inside. The available loop tags and template tags depend on the list type you have selected.', 'w4-post-list' ); ?>
</p>

<h2>
	<?php esc_html_e( 'Examples', 'w4-post-list' ); ?>
</h2>
<h3>
	<?php esc_html_e( 'Post list', 'w4-post-list' ); ?>
</h3>
<p>
	<?php esc_html_e( 'Everything between [posts] and [/posts] repeats once for every post — the per-item wrapper makes that easy to see:', 'w4-post-list' ); ?>
</p>
<pre>[posts]
	&lt;div class=&quot;post-item&quot;&gt;
		&lt;a href=&quot;[post_permalink]&quot;&gt;[post_title]&lt;/a&gt;
	&lt;/div&gt;
[/posts]
[nav]</pre>


<h3>
	<?php esc_html_e( 'Post list (with excerpt limited to 20 words, post class on the post wrapper element)', 'w4-post-list' ); ?>
</h3>
<pre>[posts]
	&lt;div class=&quot;[post_class]&quot;&gt;
		&lt;h3&gt;&lt;a href=&quot;[post_permalink]&quot;&gt;[post_title]&lt;/a&gt;&lt;/h3&gt;
		&lt;p&gt;[post_excerpt wordlimit=&quot;20&quot;]&lt;/p&gt;
	&lt;/div&gt;
[/posts]</pre>

<h3>
	<?php esc_html_e( 'Post list grouped by year (set the "Group by" option to Year while using this)', 'w4-post-list' ); ?>
</h3>
<pre><code>&lt;ul&gt;
[groups]
	&lt;li&gt;
		&lt;a href=&quot;[group_url]&quot;&gt;[group_title]&lt;/a&gt;
		&lt;ol&gt;
		[posts]
			&lt;li&gt;&lt;a href=&quot;[post_permalink]&quot;&gt;[post_title]&lt;/a&gt;&lt;/li&gt;
		[/posts]
		&lt;/ol&gt;
	&lt;/li&gt;
[/groups]
&lt;/ul&gt;</code></pre>

<h3>
	<?php esc_html_e( 'Category list', 'w4-post-list' ); ?>
</h3>
<pre><code>&lt;ul&gt;
[terms]
	&lt;li&gt;&lt;a href=&quot;[term_link]&quot;&gt;[term_name]&lt;/a&gt;&lt;/li&gt;
[/terms]
&lt;/ul&gt;</code></pre>


<h3>
	<?php esc_html_e( 'Category + Post list', 'w4-post-list' ); ?>
</h3>
<pre><code>&lt;ul&gt;
[terms]
	&lt;li&gt;
		&lt;a href=&quot;[term_link]&quot;&gt;[term_name]&lt;/a&gt;
		&lt;ol&gt;
		[posts]
			&lt;li&gt;&lt;a href=&quot;[post_permalink]&quot;&gt;[post_title]&lt;/a&gt;&lt;/li&gt;
		[/posts]
		&lt;/ol&gt;
	&lt;/li&gt;
[/terms]
&lt;/ul&gt;</code></pre>

<h3>
	<?php esc_html_e( 'User list', 'w4-post-list' ); ?>
</h3>
<pre><code>&lt;ul&gt;
[users]
	&lt;li&gt;&lt;a href=&quot;[user_link]&quot;&gt;[user_name]&lt;/a&gt;&lt;/li&gt;
[/users]
&lt;/ul&gt;</code></pre>

<h3>
	<?php esc_html_e( 'User + Post list', 'w4-post-list' ); ?>
</h3>
<pre><code>&lt;ul&gt;
[users]
	&lt;li&gt;
		&lt;a href=&quot;[user_link]&quot;&gt;[user_name]&lt;/a&gt;
		&lt;ol&gt;
		[posts]
			&lt;li&gt;&lt;a href=&quot;[post_permalink]&quot;&gt;[post_title]&lt;/a&gt;&lt;/li&gt;
		[/posts]
		&lt;/ol&gt;
	&lt;/li&gt;
[/users]
&lt;/ul&gt;</code></pre>
