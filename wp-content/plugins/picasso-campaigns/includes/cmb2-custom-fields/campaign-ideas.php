<?php
/**
 * CMB2 custom field type 'campaign_ideas'
 */
if (!function_exists('cmb2_render_campaign_ideas')) {
	function cmb2_render_campaign_ideas( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		$values = get_post_meta($field->object_id, $field_type_object->_name(), false);

		$ideas = pc_ideas();

		$html = '';
		$html .= '<select name="' . $field_type_object->_name() . '[]' . '" class="cmb2_select ' . $field_type_object->_name() . '" id="' . $field_type_object->_id() . '" multiple="multiple" style="width: 100%">';

		if ($ideas) {
			foreach ($ideas as $idea_id => $idea_title) {
				$selected = (in_array($idea_id, $values)) ? 'selected="selected"' : '';
				$html .= '<option value="' . $idea_id . '" ' . $selected . '>' . $idea_title . '</option>';
			}
		}

		$html .= '</select>';

		echo $html;
	}
	add_action( 'cmb2_render_campaign_ideas', 'cmb2_render_campaign_ideas', 10, 5 );
}