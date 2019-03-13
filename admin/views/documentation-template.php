<div class="w4pl-template">
	<h2><?php _e( 'Template', 'w4pl'); ?></h2>
	<div class="inside">

	<p><?php _e( 'Template renders output of a list. Template can be designed with templace tags and HTML. Find few examples below.', 'w4pl' ); ?></p>


	<h3><?php _e('Example'); ?></strong>: <?php _e( 'Simple Unordered Post List', 'w4pl' ); ?></h3>
	<pre><code>[posts]
&lt;ul&gt;
&lt;li&gt;&lt;a href=&quot;[post_link]&quot;&gt;[post_title]&lt;/a&gt;&lt;li&gt;
&lt;/ul&gt;
[/posts]</code></pre>


	<h3><?php _e('Example'); ?></strong>: <?php _e( 'Post list having excerpt limited to 20 words, and using post class on post wrapper element', 'w4pl' ); ?></h3>
	<pre><code>[posts]
&lt;div class=&quot;[post_class]&quot;&gt;
&lt;h3&gt;&lt;a href=&quot;[post_link]&quot;&gt;[post_title]&lt;/a&gt;&lt;/h3&gt;
&lt;p&gt;[post_excerpt wordlimit=&quot;20&quot;]&lt;/p&gt;
&lt;/div&gt;
[/posts]</code></pre>

	<h3><?php _e('Example'); ?>: <?php _e( 'Post list Group by Year (chose <em>Group By</em> option to Year while using this).', 'w4pl' ); ?></h3>
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



	<h3><?php _e('Example'); ?>: <?php _e( 'A Simple Unordered Category list', 'w4pl' ); ?></h3>
	<pre><code>[terms]
&lt;ul&gt;
&lt;li&gt;&lt;a href=&quot;[term_link]&quot;&gt;[term_name]&lt;/a&gt;&lt;li&gt;
&lt;/ul&gt;
[/terms]</code></pre>


	<h3><?php _e('Example'); ?>: <?php _e( 'Category Post list', 'w4pl' ); ?></h3>
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


	<h3><?php _e('Example'); ?>: <?php _e( 'A Simple Unordered Users list', 'w4pl' ); ?></h3>
	<pre><code>[users]
&lt;ul&gt;
&lt;li&gt;&lt;a href=&quot;[user_link]&quot;&gt;[user_name]&lt;/a&gt;&lt;li&gt;
&lt;/ul&gt;
[/users]</code></pre>


	<h3><?php _e('Example'); ?>: <?php _e( 'Users Post list', 'w4pl' ); ?></h3>
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

	</div><!--inside-->
</div>