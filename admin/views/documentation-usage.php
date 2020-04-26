<div class="w4pl-usage">
    <h2><?php _e( 'Usage', 'w4pl' ); ?></h2>
    <div class="inside">
        <strong><?php _e( 'Shortcode usage', 'w4pl' ) ?></strong>
        <p>
			<?php
		        printf(
					/* translators: %s: postlist wrapper in code html tag */
					__( 'Use shortcode %s with the list id to show a list on post/page content area', 'w4pl' ),
					'<code>postlist</code>'
				);
			?>
	        <?php _e( 'Ex:', 'w4pl'); ?> <code>[postlist 1]</code>
		</p>

        <strong><?php _e( 'PHP function usage', 'w4pl' ); ?>:</strong>
        <p>
			<?php
		        printf(
					/* translators: %s: php function name */
					__( 'Display list using %s function', 'w4pl' ),
					'<code>do_shortcode</code>'
				);
			?>
		</p>
        <pre><code>&lt;?php</code><br /><code>echo do_shortcode( '[postlist id=1]' );</code><br /><code>?&gt;</code></pre>
    </div>
</div><!--postbox-->
