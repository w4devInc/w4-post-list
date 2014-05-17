<?php
/**
 * Include Tinymce Button for Creating Post List on The Post
**/

add_action( 'init', 'w4pl_tinymce_add_button' );
function w4pl_tinymce_add_button(){
	if( ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' )) && get_user_option( 'rich_editing' ) == 'true'){
		add_filter( "mce_external_plugins", "w4pl_tinymce_custom_plugin" );
		add_filter( "mce_buttons", "w4pl_tinymce_register_button" );
	}
}


function w4pl_tinymce_register_button( $buttons ){
	array_push( $buttons, "|", "w4pl" );
	return $buttons;
}

function w4pl_tinymce_custom_plugin( $plugin_array ){
	$plugin_array['w4pl'] = W4PL_URL. 'tinymice/plugin.js';
	return $plugin_array;
}
?>