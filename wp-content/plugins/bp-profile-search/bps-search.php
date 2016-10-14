<?php

add_action ('wp', 'bps_set_cookie');
function bps_set_cookie ()
{
	if (isset ($_REQUEST['bp_profile_search']))
		setcookie ('bps_request', serialize ($_REQUEST), 0, COOKIEPATH);
	else if (isset ($_COOKIE['bps_request']))
		setcookie ('bps_request', '', 0, COOKIEPATH);
}

function bps_get_request ()
{
	$request = array ();
	if (isset ($_REQUEST['bp_profile_search']))
		$request = $_REQUEST;
	else if (isset ($_COOKIE['bps_request']) && defined ('DOING_AJAX'))
		$request = unserialize (stripslashes ($_COOKIE['bps_request']));

	return apply_filters ('bps_request', $request);
}

function bps_active_form ()
{
	$request = bps_get_request ();
	return isset ($request['bp_profile_search'])? $request['bp_profile_search']: false;
}

function bps_text_search ()
{
	$request = bps_get_request ();
	return isset ($request['text_search'])? $request['text_search']: '';
}

function bps_minmax ($request, $id, $type)
{
	$min = (isset ($request["field_{$id}_min"]) && is_numeric (trim ($request["field_{$id}_min"])))? trim ($request["field_{$id}_min"]): '';
	$max = (isset ($request["field_{$id}_max"]) && is_numeric (trim ($request["field_{$id}_max"])))? trim ($request["field_{$id}_max"]): '';

	if ($type == 'datebox')
	{
		if (is_numeric ($min))  $min = (int)$min;
		if (is_numeric ($max))  $max = (int)$max;
	}

	return array ($min, $max);
}

function bps_search ()
{
	global $bp, $wpdb;

	$results = array ('users' => array (0), 'validated' => true);

	$F = bps_query_data ();
	foreach ($F->fields as $field)
	{
		$id = $field->id;
		$key = $field->code;
		if (isset ($field->value))  $value = $field->value;

		$field_type = $field->type;
		$field_type = apply_filters ('bps_field_query_type', $field_type, $field);
		$field_type = apply_filters ('bps_field_type_for_query', $field_type, $field);

		if (bps_custom_field ($field_type))
		{
			$found = apply_filters ('bps_field_query', array (), $field, $key, $value);
		}
		else
		{
			$sql = $wpdb->prepare ("SELECT user_id FROM {$bp->profile->table_name_data} WHERE field_id = %d ", $id);
			$sql = apply_filters ('bps_field_sql', $sql, $field);

			if ($field->filter == 'range')
			{
				if ($field_type == 'multiselectbox' || $field_type == 'checkbox')  continue;

				$min = isset ($field->min)? $field->min: '';
				$max = isset ($field->max)? $field->max: '';

				switch ($field_type)
				{
				case 'textbox':
				case 'number':
				case 'textarea':
				case 'selectbox':
				case 'radio':
					if ($min !== '')  $sql .= $wpdb->prepare ("AND value >= %f", $min);
					if ($max !== '')  $sql .= $wpdb->prepare ("AND value <= %f", $max);
					break;

				case 'datebox':
					$time = time ();
					$day = date ("j", $time);
					$month = date ("n", $time);
					$year = date ("Y", $time);
					$ymin = $year - (int)$max - 1;
					$ymax = $year - (int)$min;

					if ($max !== '')  $sql .= $wpdb->prepare ("AND DATE(value) > %s", "$ymin-$month-$day");
					if ($min !== '')  $sql .= $wpdb->prepare ("AND DATE(value) <= %s", "$ymax-$month-$day");
					break;
				}
			}
			else if ($field->filter == 'default')
			{
				if ($field_type == 'datebox')  continue;

				switch ($field_type)
				{
				case 'textbox':
				case 'textarea':
				case 'url':
					$value = str_replace ('&', '&amp;', $value);
					$escaped = '%'. bps_esc_like ($value). '%';
					switch (bps_text_search ())
					{
					default:	// contains
						$sql .= $wpdb->prepare ("AND value LIKE %s", $escaped);
						break;
					case 'ISLIKE':
						$value = str_replace ('\\\\%', '\\%', $value);
						$value = str_replace ('\\\\_', '\\_', $value);
						$sql .= $wpdb->prepare ("AND value LIKE %s", $value);
						break;
					case 'EQUAL':
						$sql .= $wpdb->prepare ("AND value = %s", $value);
						break;
					}
					break;

				case 'number':
					$sql .= $wpdb->prepare ("AND value = %d", $value);
					break;

				case 'selectbox':
				case 'radio':
					$values = (array)$value;
					$parts = array ();
					foreach ($values as $value)
					{
						$value = str_replace ('&', '&amp;', $value);
						$parts[] = $wpdb->prepare ("value = %s", $value);
					}
					$sql .= 'AND ('. implode (' OR ', $parts). ')';
					break;

				case 'multiselectbox':
				case 'checkbox':
					$values = (array)$value;
					$parts = array ();
					foreach ($values as $value)
					{
						$value = str_replace ('&', '&amp;', $value);
						$escaped = '%:"'. bps_esc_like ($value). '";%';
						$parts[] = $wpdb->prepare ("value LIKE %s", $escaped);
					}
					$match = apply_filters ('bps_field_checkbox_match_all', false, $id)? ' AND ': ' OR ';
					$sql .= 'AND ('. implode ($match, $parts). ')';
					break;
				}
			}

			$found = $wpdb->get_col ($sql);
		}

		$users = isset ($users)? array_intersect ($users, $found): $found;
		if (count ($users) == 0)  return $results;
	}

	if (isset ($users))
		$results['users'] = $users;
	else
		$results['validated'] = false;

	return $results;
}

add_action ('bp_ajax_querystring', 'bps_filter_members', 99, 2);
function bps_filter_members ($qs, $object)
{
	if ($object != 'members')  return $qs;
	if (bps_active_form () === false)  return $qs;

	$bps_results = bps_search ();
	if ($bps_results['validated'])
	{
		$args = wp_parse_args ($qs);
		$users = $bps_results['users'];

		if (isset ($args['include']))
		{
			$included = explode (',', $args['include']);
			$users = array_intersect ($users, $included);
			if (count ($users) == 0)  $users = array (0);
		}

		$users = apply_filters ('bps_filter_members', $users);
		$args['include'] = implode (',', $users);
		$qs = build_query ($args);
	}

	return $qs;
}

function bps_esc_like ($text)
{
    return addcslashes ($text, '_%\\');
}

function bps_query_data ()
{
	list ($x, $fields) = bps_get_fields ();

	$F = new stdClass;
	$F->fields = array ();

	foreach ($fields as $f)
		if (isset ($f->filter))  $F->fields[] = $f;

	return $F;
}

function bps_parse_request ($fields)
{
	$request = bps_get_request ();
	foreach ($request as $key => $value)
	{
		if ($value === '')  continue;

		$k = bps_match_key ($key, $fields);
		if ($k === false)  continue;

		$f = $fields[$k];
		$filter = substr ($key, strlen ($f->code));
		switch ($filter)
		{
		case '':
			$f->filter = 'default';
			$f->value = $value;
			$f->values = (array)$f->value;
			$f->min = $f->max = '';
			break;
		case '_min':
			if (!is_numeric ($value))  break;
			$f->filter = 'range';
			$f->min = $value;
			if ($f->type == 'datebox')  $f->min = (int)$f->min;
			if ($f->type == 'birthdate')  $f->min = (int)$f->min;
			if (!isset ($f->max))  $f->max = '';
			$f->value = '';
			$f->values = array ();
			break;
		case '_max':
			if (!is_numeric ($value))  break;
			$f->filter = 'range';
			$f->max = $value;
			if ($f->type == 'datebox')  $f->max = (int)$f->max;
			if ($f->type == 'birthdate')  $f->max = (int)$f->max;
			if (!isset ($f->min))  $f->min = '';
			$f->value = '';
			$f->values = array ();
			break;
		case '_label':
			$f->label = stripslashes ($value);
			break;
		}
	}

	return true;
}

function bps_match_key ($key, $fields)
{
	foreach ($fields as $k => $f)
		if ($key == $f->code || strpos ($key, $f->code. '_') === 0)  return $k;

	return false;
}
