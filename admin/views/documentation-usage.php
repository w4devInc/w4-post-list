<div class="w4pl-usage">
    <h2><?php _e( 'Usage', 'w4pl' ); ?></h2>
    <div class="inside">
        <strong>Shortcode usage</strong>
        <p><?php 
        printf( __('Use shortcode %s with the list id to show a list on post/page content area', 'w4pl'), '<code>postlist</code>');
        _e( 'Ex:', 'w4pl'); ?> <code>[postlist 1]</code></p>
    
        <strong>PHP Function usage</strong>
        <p><?php 
        printf( __('Display list using %s function', 'w4pl'), '<code>do_shortcode</code>'); 
		?></p>
        <pre><code>&lt;?php</code><br /><code>echo do_shortcode( '[postlist id=1]' );</code><br /><code>?&gt;</code></pre>
    </div>
</div><!--postbox-->
