<?php
/**
 * @package W4 Post List
 */

// load WP, go down till 6 dirs...
$_abspath = "../../";
for ( $i = 0; $i < 3; $i++ ){
	$_abspath .= "../";
	if( file_exists( $_abspath . 'wp-load.php' )){
		require_once( $_abspath . 'wp-load.php' );
		break;
	}
}
unset( $_abspath );


if( !defined('ABSPATH') )
	die( '<p>Someting went wrong. We could not connect to the server.</p>' );

?>
<?php
header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<title>W4 Post List</title>
	<script type="text/javascript">
		var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>'
	</script>
	<?php do_action( 'wp_enqueue_scripts' ); ?>
	<?php do_action('w4pl/list_options_print_scripts'); ?>
	<?php wp_print_styles( array('w4pl_jquery_ui_custom', 'w4pl_colorpicker') ); ?>

	<?php 
	// if direct request, hide tinymce
	if( isset($_SERVER['HTTP_REFERER']) ){ wp_print_scripts( array('w4pl_tinymce_popup') ); } ?>

<script type="text/javascript">
var win = window.dialogArguments || opener || parent || top;
if ( win && win.tinymce && win.tinymce.isMac ) {
	document.body.className = document.body.className.replace(/windows/, 'macos');
}

(function($){
	$(document).ready(function(){
		if ( win && win.tinymce ){
			var selection = win.tinymce.activeEditor.selection.getContent({format:'text'});
			if( selection ){
				$.post( ajaxurl, {'action': 'w4pl_list_options_template', 'selection': selection }, function(r){
					if( r != '' ){
						$("#w4pl_list_options_template").html(r);
					}
				});
			}
		}
		$("#w4pl_list_options").submit(function(){
			var form = $(this);
			var errors = [];
			var data = form.serialize();

			$.post( ajaxurl, data, function(r){
				if( typeof(tinyMCEPopup) === 'object' ){
					tinyMCEPopup.execCommand( 'mceReplaceContent', false, r );
					tinyMCEPopup.close();
				}
				else{
					console.log(r);
				}
			});
			return false;
		})
	});
	function w4pl_get_shortcode( url, callback ){
		$.post( ajaxurl,{'action': 'w4pl_get_shortcode', 'url': url}, callback);
	}
})(jQuery);
</script>
<style>
#w4pl_list_options{padding:10px; max-width:820px; margin:0 auto;}
#submit{
	background-color:#0b4a6d;
	border:1px dashed #85a5b6;
	font-family:Arial, Helvetica, sans-serif;
	color:#FFF;
	font-weight:700;
	cursor:pointer;
	margin:20px 0 50px;
	padding:10px 20px;
	}
</style>
<?php wp_admin_css( 'wp-admin', true ); ?>
</head>
<body class="wp-core-ui">
<div class="wrap">
<form id="w4pl_list_options">
	<input type="hidden" name="action" value="w4pl_get_shortcode" />
	<div id="w4pl_list_options_template">
		<?php do_action( 'w4pl/list_options_template', array() ); ?>
	</div>
	<p style="text-align:right;"><input type="submit" id="submit" value="Insert" /></p>
</form>
</div>
</body>
</html>