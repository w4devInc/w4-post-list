<?php
/**
 * Getting started section template
 *
 * @package W4_Post_List
 */

?>
<div class="w4pl-getting-started">
	<h2>
		<?php esc_html_e( 'Your first list in four steps', 'w4-post-list' ); ?>
	</h2>

	<h3>
		<?php esc_html_e( '1. Create a list', 'w4-post-list' ); ?>
	</h3>
	<p>
		<?php
		printf(
			/* translators: %s: link to the new list screen */
			esc_html__( 'Go to %s and give your list a title. Choose a List Type: Posts, Terms, Users, Terms + Posts or Users + Posts.', 'w4-post-list' ),
			'<a href="' . esc_url( admin_url( 'post-new.php?post_type=w4pl' ) ) . '">' . esc_html__( 'W4 Post List &rarr; Add New', 'w4-post-list' ) . '</a>'
		);
		?>
		<?php esc_html_e( 'Fresh installs include a draft "Example: Recent Posts" list — open it to see a working configuration you can learn from or adapt.', 'w4-post-list' ); ?>
	</p>

	<h3>
		<?php esc_html_e( '2. Choose what to show', 'w4-post-list' ); ?>
	</h3>
	<p>
		<?php esc_html_e( 'Open the section named after your list type (for example "Posts") and pick the post type, how many items to show, and the order. The other sections (Tax Query, Meta Query, Date Query) are optional filters — you can skip them entirely for your first list.', 'w4-post-list' ); ?>
	</p>

	<h3>
		<?php esc_html_e( '3. Publish', 'w4-post-list' ); ?>
	</h3>
	<p>
		<?php esc_html_e( 'A default template is applied automatically, so you do not need to touch the Template section to get output. Click Publish.', 'w4-post-list' ); ?>
	</p>

	<h3>
		<?php esc_html_e( '4. Place it on your site', 'w4-post-list' ); ?>
	</h3>
	<p>
		<?php esc_html_e( 'Three ways to display your list:', 'w4-post-list' ); ?>
	</p>
	<ul style="list-style: disc; padding-left: 20px;">
		<li>
			<?php esc_html_e( 'Block: add the "W4 Post List" block in the editor and pick your list.', 'w4-post-list' ); ?>
		</li>
		<li>
			<?php
			printf(
				/* translators: %s: example shortcode in code tag */
				esc_html__( 'Shortcode: paste %s into any content. Every list\'s ready-made shortcode is shown in the Shortcode column on the All Lists screen.', 'w4-post-list' ),
				'<code>[postlist id="123"]</code>'
			);
			?>
		</li>
		<li>
			<?php esc_html_e( 'Widget: add the W4 Post List widget to any widget area.', 'w4-post-list' ); ?>
		</li>
	</ul>

	<h2>
		<?php esc_html_e( 'Customizing the output', 'w4-post-list' ); ?>
	</h2>
	<p>
		<?php
		printf(
			/* translators: 1: example template tag, 2: example template tag, 3: link to the Template tab, 4: link to the Template Tags tab */
			esc_html__( 'Each list has an HTML template that controls its markup. Edit it in the Template section of the list editor using template tags like %1$s or %2$s — see the %3$s and %4$s tabs above for working examples and the full tag reference.', 'w4-post-list' ),
			'<code>[post_title]</code>',
			'<code>[post_permalink]</code>',
			'<a href="' . esc_url( add_query_arg( 'tab', 'template-examples' ) ) . '">' . esc_html__( 'Template', 'w4-post-list' ) . '</a>',
			'<a href="' . esc_url( add_query_arg( 'tab', 'template-tags' ) ) . '">' . esc_html__( 'Template Tags', 'w4-post-list' ) . '</a>'
		);
		?>
	</p>

	<h2>
		<?php esc_html_e( 'If your list shows nothing', 'w4-post-list' ); ?>
	</h2>
	<ul style="list-style: disc; padding-left: 20px;">
		<li><?php esc_html_e( 'Make sure the list is Published, not Draft.', 'w4-post-list' ); ?></li>
		<li><?php esc_html_e( 'Check the shortcode ID matches the list — copy it from the Shortcode column on the All Lists screen.', 'w4-post-list' ); ?></li>
		<li>
			<?php
			printf(
				/* translators: 1: posts loop tag, 2: terms loop tag, 3: users loop tag */
				esc_html__( 'If you edited the template, it must keep the loop tags for its list type: %1$s for posts, %2$s for terms, %3$s for users.', 'w4-post-list' ),
				'<code>[posts]&hellip;[/posts]</code>',
				'<code>[terms]&hellip;[/terms]</code>',
				'<code>[users]&hellip;[/users]</code>'
			);
			?>
		</li>
		<li><?php esc_html_e( 'Re-check the query options — the list may simply have matched no items.', 'w4-post-list' ); ?></li>
	</ul>
</div>
