<?php
/**
 * CMB2 custom field type 'users_with_avatar'
 */
if (!function_exists('cmb2_render_users_with_avatar')) {
	function cmb2_render_users_with_avatar( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		$values = (array) $field_type_object->field->value();

		$users = get_users();

	    $html = '';
	    $html .= '<select name="' . $field_type_object->_name() . '[]' . '" class="select2-users-with-avatar" id="' . $field_type_object->_id() . '" multiple="multiple" style="width: 100%">';
	    $html .= '<option></option>';

	    if ($users) {
	    	foreach ($users as $user) {
	    		$user_id = $user->ID;
	    		$display_name = $user->data->display_name;
	    		$placeholder = __('Search user', IDEAS_LOCALE);
	    		$member_type = function_exists('bp_get_member_type') ? bp_get_member_type($user_id) : '';
	    		$avatar_link = pi_get_avatar($user_id);
	    		$selected = (in_array($user_id, $values)) ? 'selected="selected"' : '';

	    		$html .= '<option value="' . $user_id . '" ' . $selected . ' data-member-type="' . $member_type . '" data-avatar="' . $avatar_link . '">' . $display_name . '</option>';
	    	}
	    }

	    $html .= '</select>';

	    $html .= "
	    <style type='text/css'>
	    	.cmb-type-users-with-avatar .select2-container .select2-choice {
	    		height: 60px;
			    line-height: 55px;
	    	}
	    	.cmb-type-users-with-avatar .select2-container .select2-choice .select2-arrow b {
	    		background-position: 0 15px;
	    	}
	    	.select2-search-choice img,
	    	.select2-result img,
	    	.cmb-type-users-with-avatar .select2-choice img {
	    		vertical-align: middle;
	    	}
	    	.cmb-type-users-with-avatar .select2-container-multi .select2-search-choice-close {
	    		top: 10px;
	    	}
	    </style>
	    <script type='text/javascript'>
	    	jQuery(document).ready(function($) {
	    		if (jQuery().select2) {
	    		    function format(state) {
	    		        var originalOption = state.element,
	    		            member_type = '';

	    		        if ($(originalOption).data('member-type')) {
	    		            member_type = ' (' + $(originalOption).data('member-type') + ')';
	    		        }
	    		        return '<span><img src=' + $(originalOption).data('avatar') + ' width=32 height=32 /> ' + state.text + member_type + '</span>';
	    		    }

	    		    $('#" . $field_type_object->_id() . "').select2({
	    		        placeholder: '" . $placeholder . "',
	    		        formatResult: format,
	    		        formatSelection: format,
	    		    });
	    		}
	    	});
	    </script>
	    ";

	    echo $html;
	}
	add_action( 'cmb2_render_users_with_avatar', 'cmb2_render_users_with_avatar', 10, 5 );
}