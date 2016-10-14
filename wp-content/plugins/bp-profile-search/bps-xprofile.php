<?php

add_filter ('bps_fields_setup', 'bps_anyfield_setup', 99);
function bps_anyfield_setup ($fields)
{
	$f = new stdClass;

	$f->group = __('Other', 'bp-profile-search');
	$f->id = 'any';
	$f->code = 'field_any';
	$f->name = __('Any field', 'bp-profile-search');
	$f->description = __('Search any BP Profile Field', 'bp-profile-search');
	$f->type = 'anyfield';
	$f->display = 'textbox';
	$f->options = array ();
	$f->format = 'text';
	$f->search = 'bps_anyfield_search';

	$fields[] = $f;
	return $fields;
}

add_filter ('bps_field_validation', 'bps_anyfield_validation', 10, 2);
function bps_anyfield_validation ($settings, $field)
{
	list ($value, $description, $range) = $settings;
	if ($field->type == 'anyfield')  $range = false;

	return array ($value, $description, $range);
}

add_filter ('bps_field_query', 'bps_anyfield_query', 10, 4);
function bps_anyfield_query ($results, $field, $key, $value)
{
	global $bp, $wpdb;

	if ($field->type == 'anyfield')
	{
		$value = str_replace ('&', '&amp;', $value);
		$escaped = '%'. bps_esc_like ($value). '%';
		$sql = $wpdb->prepare ("SELECT DISTINCT user_id FROM {$bp->profile->table_name_data} WHERE value LIKE %s", $escaped);
		$results = $wpdb->get_col ($sql);
	}

	return $results;
}
