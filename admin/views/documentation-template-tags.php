<div class="w4pl-template-tags">
	<h2><?php _e( 'Template Tags', 'w4pl'); ?></h2>
	<div class="inside"><?php $shortcodes = apply_filters( 'w4pl/get_shortcodes', array() ); ?>
	<table id="shortcode_hint" cellpadding="0" cellspacing="0">
	<thead><tr><th class="tag_name">Tag</th><th style="text-align:left; padding-left:10px;" class="tag_desc"><?php _e( 'Details', 'w4pl'); ?></th></tr></thead><tbody><?php
	foreach( $shortcodes as $shortcode => $attr ){ $rc = isset($rc) && $rc == '' ? $rc = 'alt' : ''; ?>
		<tr class="<?php echo $rc; ?>">
		<th valign="top" class="tag_name"><code>[<?php echo $shortcode; ?>]</code></th>
		<td class="tag_desc"><?php echo $attr['desc']; ?></td>
		</tr>
	<?php } ?>
	</tbody></table>
	</div><!--inside-->
</div>