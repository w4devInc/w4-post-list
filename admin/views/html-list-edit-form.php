<?php
/**
 * List edit form template
 *
 * @package W4_Post_List
 */

// Contains list settings.
if ( ! isset( $options ) ) {
	$options = array();
}

$fields = array();

// Root wrapper.
$fields['before_list_options'] = array(
	'position' => '0',
	'html'     => '<div id="w4pl_list_options" class="w4pl-list-editor">',
);
$fields['id']                  = array(
	'position'    => '1.1',
	'option_name' => 'id',
	'name'        => 'w4pl[id]',
	'type'        => 'hidden',
);
$fields['tab_id']              = array(
	'position'    => '1.2',
	'option_name' => 'tab_id',
	'name'        => 'w4pl[tab_id]',
	'type'        => 'hidden',
);

/* Field Group - List Type */
$fields['before_field_group_type'] = array(
	'position' => '2',
	'html'     => '<div id="w4pl_field_group_type" class="w4pl_field_group">
						<div class="w4pl_group_title">' . __( 'List Type', 'w4-post-list' ) . '</div>
						<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">',
);
$fields['list_type']               = array(
	'position'    => '3',
	'option_name' => 'list_type',
	'name'        => 'w4pl[list_type]',
	'label'       => __( 'List Type', 'w4-post-list' ),
	'type'        => 'radio',
	'option'      => W4PL_Config::list_type_options(),
	'input_class' => 'w4pl_onchange_lfr',
);
$fields['after_field_group_type']  = array(
	'position' => '4',
	'html'     => '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_type-->',
);

/* Field Group - Template */
$fields['before_field_group_template'] = array(
	'position' => '150',
	'html'     => '<div id="w4pl_field_group_template" class="w4pl_field_group">
		<div class="w4pl_group_title">' . __( 'Template', 'w4-post-list' ) . '</div>
		<div class="w4pl_group_fields"><div class="w4pl_group_fields_wrap">',
);

$template_html = '
<div class="wffw wffwi_w4pl_template wffwt_textarea">
	<p style="margin-top:0px;">
		<a href="#" class="button w4pl_toggler" data-target="#w4pl_template_examples">' . __( 'Template Example', 'w4-post-list' ) . '</a>
		<a href="#" class="button w4pl_toggler" data-target="#w4pl_template_buttons">' . __( 'Shortcodes', 'w4-post-list' ) . '</a>
	</p>
	<div id="w4pl_template_examples" class="csshide">'
	. "<pre style='width:auto'>\n[groups]\n\t[group_title]\n\t[posts]\n\t\t[post_title]\n\t[/posts]\n[/groups]\n[nav]</pre>"
	. '<br />without group, a simple template should be like -'
	. "<pre style='width:auto'>[posts]\n\t[post_title]\n[/posts]\n[nav]</pre>"
	. '</div>';


$shortcodes       = w4pl_get_shortcodes();
$shortcode_groups = array();
foreach ( $shortcodes as $shortcode => $attr ) {
	$group = $attr['group'];
	if ( ! isset( $shortcode_groups[ $group ] ) || ! is_array( $shortcode_groups[ $group ] ) ) {
		$shortcode_groups[ $group ] = array();
	}

	$shortcode_groups[ $group ][] = $shortcode;
}

$template_html .= '<div id="w4pl_template_buttons" class="csshide">';
foreach ( $shortcode_groups as $shortcode_group => $scodes ) {
	$template_html .= sprintf( ' <div class="w4pl_button_group"><span class="w4pl_button_group_title">%1$s</span>', $shortcode_group );
	foreach ( $scodes as $shortcode ) {
		$attr = $shortcodes[ $shortcode ];
		if ( isset( $attr['code'] ) ) {
			$code = $attr['code'];
		} else {
			$code = '[' . $shortcode . ']';
		}
		$template_html .= sprintf( ' <a href="#%1$s" data-code="%2$s">%1$s</a>', $shortcode, esc_attr( $code ) );
	}
	$template_html .= '</div>';
}
$template_html .= '</div>';

/*
$template_html .= '
<div class="wfflw wfflwi_w4pl_template wfflwt_textarea">
	<label for="w4pl_template" class="wffl wffli_w4pl_template wfflt_textarea">Template</label>
</div>';
*/
if ( ! isset( $options['template'] ) ) {
	$options['template'] = '';
}
$template_html .= w4pl_form_child_field_html(
	array(
		'id'          => 'w4pl_template',
		'name'        => 'w4pl[template]',
		'input_class' => 'wff wffi_w4pl_template wfft_textarea widefat',
		'type'        => 'textarea',
		'value'       => $options['template'],
	)
);
$template_html .= '</div>';

$fields['template1'] = array(
	'position' => '155',
	'html'     => $template_html,
);

$fields['after_field_group_template'] = array(
	'position' => '160',
	'html'     => '</div><!--.w4pl_group_fields_wrap--></div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_type-->',
);

$fields['after_list_options'] = array(
	'position' => '999',
	'type'     => 'html',
	'html'     => '</div><!--#w4pl_list_options-->',
);

// W4PL_Utils::p( $fields );.

$form_args = array(
	'no_form'      => true,
	'button_after' => false,
);

// let helper class extend/modify this class.
$fields = apply_filters( 'w4pl/list_edit_form_fields', $fields, $options );

// order by position.
uasort( $fields, array( 'W4PL_Utils', 'order_by_position' ) );

$output = w4pl_form_fields( $fields, $options, $form_args );

// Filter the output.
$output = apply_filters( 'w4pl/list_edit_form_html', $output, $fields, $options );

echo $output;
