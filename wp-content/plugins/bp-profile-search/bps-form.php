<?php

add_action ('bp_before_directory_members_tabs', 'bps_add_form');
function bps_add_form ()
{
	global $post;

	$page = $post->ID;
	if ($page == 0)
	{
		$bp_pages = bp_core_get_directory_page_ids ();
		$page = $bp_pages['members'];
	}

	$page = bps_wpml_id ($page, 'default');
	$len = strlen ((string)$page);

	$args = array (
		'post_type' => 'bps_form',
		'orderby' => 'ID',
		'order' => 'ASC',
		'nopaging' => true,
		'meta_query' => array (
			array (
				'key' => 'bps_options',
				'value' => 's:9:"directory";s:3:"Yes";',
				'compare' => 'LIKE',
			),
			array (
				'key' => 'bps_options',
				'value' => "s:6:\"action\";s:$len:\"$page\";",
				'compare' => 'LIKE',
			),
		)
	);

	$args = apply_filters ('bps_form_order', $args);
	$posts = get_posts ($args);

	foreach ($posts as $post)
	{
		$meta = bps_meta ($post->ID);
		$template = $meta['template'];
		bps_display_form ($post->ID, $template, 'directory');
	}
}

add_action ('bps_display_form', 'bps_display_form', 10, 3);
function bps_display_form ($form, $template='', $location='')
{
	if (!function_exists ('bp_has_profile'))
	{
		printf ('<p class="bps_error">'. __('%s: The BuddyPress Extended Profiles component is not active.', 'bp-profile-search'). '</p>',
			'<strong>BP Profile Search '. BPS_VERSION. '</strong>');
		return false;
	}

	$meta = bps_meta ($form);
	if (empty ($meta['field_name']))
	{
		printf ('<p class="bps_error">'. __('%s: Form %d was not found, or has no fields.', 'bp-profile-search'). '</p>',
			'<strong>BP Profile Search '. BPS_VERSION. '</strong>', $form);
		return false;
	}

	if (empty ($template))  $template = bps_default_template ();
	bps_set_template_args ($form, $location);
	bps_call_template ($template);

	return true;
}

add_action ('bp_before_directory_members_content', 'bps_display_filters');
function bps_display_filters ()
{
	$form = bps_active_form ();
	if ($form === false)  return false;

	bps_set_template_args ($form, 'filters');
	bps_call_template ('members/bps-filters');

	return true;
}

function bps_set_wpml ($form, $code, $key, $value)
{
	if (!class_exists ('BPML_XProfile'))  return false;
	if (empty ($value))  return false;

	icl_register_string ('Profile Search', "form $form $code $key", $value);
}

function bps_wpml ($form, $id, $key, $value)
{
	if (!class_exists ('BPML_XProfile'))  return $value;
	if (empty ($value))  return $value;

	switch ($key)
	{
	case 'name':
		return icl_t ('Buddypress Multilingual', "profile field $id name", $value);
	case 'label':
		return icl_t ('Profile Search', "form $form field_$id label", $value);
	case 'description':
		return icl_t ('Buddypress Multilingual', "profile field $id description", $value);
	case 'comment':
		return icl_t ('Profile Search', "form $form field_$id comment", $value);
	case 'option':
		$option = bpml_sanitize_string_name ($value, 30);
		return icl_t ('Buddypress Multilingual', "profile field $id - option '$option' name", $value);
	}
}

function bps_wpml_id ($id, $lang='current')
{
	if (class_exists ('BPML_XProfile'))
	{
		global $sitepress;

		if ($lang == 'current')  $id = icl_object_id ($id, 'page', true);
		if ($lang == 'default')  $id = icl_object_id ($id, 'page', true, $sitepress->get_default_language ());
	}

	return $id;
}

add_shortcode ('bps_display', 'bps_show_form');
function bps_show_form ($attr, $content)
{
	ob_start ();

	if (isset ($attr['form']))
	{
		$template = isset ($attr['template'])? $attr['template']: '';
		bps_display_form ($attr['form'], $template, 'shortcode');
	}	

	return ob_get_clean ();
}

add_shortcode ('bps_directory', 'bps_show_directory');
function bps_show_directory ($attr, $content)
{
	ob_start ();

	if (!function_exists ('bp_has_profile'))
	{
		printf ('<p class="bps_error">'. __('%s: The BuddyPress Extended Profiles component is not active.', 'bp-profile-search'). '</p>',
			'<strong>BP Profile Search '. BPS_VERSION. '</strong>');
	}
	else
	{
		$template = isset ($attr['template'])? $attr['template']: 'members/index';

		$found = bp_get_template_part ($template);
		if (!$found)  printf ('<p class="bps_error">'. __('%s: The directory template "%s" was not found.', 'bp-profile-search'). '</p>',
			'<strong>BP Profile Search '. BPS_VERSION. '</strong>', $template);
	}

	return ob_get_clean ();
}

class bps_widget extends WP_Widget
{
	function __construct ()
	{
		$widget_ops = array ('description' => __('A Profile Search form.', 'bp-profile-search'));
		parent::__construct ('bps_widget', __('Profile Search', 'bp-profile-search'), $widget_ops);
	}

	function widget ($args, $instance)
	{
		extract ($args);
		$title = apply_filters ('widget_title', $instance['title']);
		$form = $instance['form'];
		$template = isset ($instance['template'])? $instance['template']: '';

		echo $before_widget;
		if ($title)
			echo $before_title. $title. $after_title;
		bps_display_form ($form, $template, 'widget');
		echo $after_widget;
	}

	function update ($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['form'] = $new_instance['form'];
		$instance['template'] = $new_instance['template'];
		return $instance;
	}

	function form ($instance)
	{
		$title = isset ($instance['title'])? $instance['title']: '';
		$form = isset ($instance['form'])? $instance['form']: '';
		$template = isset ($instance['template'])? $instance['template']: '';
?>
	<p>
		<label for="<?php echo $this->get_field_id ('title'); ?>"><?php _e('Title:', 'bp-profile-search'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id ('title'); ?>" name="<?php echo $this->get_field_name ('title'); ?>" type="text" value="<?php echo esc_attr ($title); ?>" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id ('form'); ?>"><?php _e('Form:', 'bp-profile-search'); ?></label>
<?php
		$posts = get_posts (array ('post_type' => 'bps_form', 'orderby' => 'ID', 'order' => 'ASC', 'nopaging' => true));
		if (count ($posts))
		{
			echo "<select class='widefat' id='{$this->get_field_id ('form')}' name='{$this->get_field_name ('form')}'>";
			foreach ($posts as $post)
			{
				$id = $post->ID;
				$name = !empty ($post->post_title)? $post->post_title: __('(no title)');
				echo "<option value='$id'";
				if ($id == $form)  echo " selected='selected'";
				echo ">$name &nbsp;</option>\n";
			}
			echo "</select>";
		}
		else
		{
			echo '<br/>';
			_e('You have not created any form yet.', 'bp-profile-search');
		}
?>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id ('template'); ?>"><?php _e('Template:', 'bp-profile-search'); ?></label>
<?php
		$templates = bps_templates ();
		echo "<select class='widefat' id='{$this->get_field_id ('template')}' name='{$this->get_field_name ('template')}'>";
		foreach ($templates as $option)
		{
			echo "<option value='$option'";
			if ($option == $template)  echo " selected='selected'";
			echo ">$option &nbsp;</option>\n";
		}
		echo "</select>";
?>
	</p>
<?php
	}
}

add_action ('widgets_init', 'bps_widget_init');
function bps_widget_init ()
{
	register_widget ('bps_widget');
}

function bps_escaped_form_data ()
{
	list ($form, $location) = bps_template_args ();

	$meta = bps_meta ($form);
	list ($x, $fields) = bps_get_fields ();

	$F = new stdClass;
	$F->id = $form;
	$F->location = $location;
	$F->header = $meta['header'];
	$F->toggle = ($meta['toggle'] == 'Enabled');
	$F->toggle_text = $meta['button'];
	if ($location == 'directory')
		$F->action = parse_url ($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	else
		$F->action = get_page_link (bps_wpml_id ($meta['action']));
	
	$F->method = $meta['method'];
	$F->fields = array ();

	foreach ($meta['field_name'] as $k => $id)
	{
		if (empty ($fields[$id]))  continue;

		$f = clone $fields[$id];
		if (isset ($meta['field_range'][$k]))  $f->display = 'range';
		if (empty ($f->display))
		{
			$f->display = apply_filters ('bps_field_type_for_filters', $f->type, $f);
			$f->display = apply_filters ('bps_field_type_for_search_form', $f->display, $f);
		}

		$f->label = $f->name;
		$custom_label = bps_wpml ($form, $f->id, 'label', $meta['field_label'][$k]);
		if (!empty ($custom_label))  $f->label = $custom_label;

		$custom_desc = bps_wpml ($form, $id, 'comment', $meta['field_desc'][$k]);
		if ($custom_desc == '-')
			$f->description = '';
		else if (!empty ($custom_desc))
			$f->description = $custom_desc;

		if ($form != bps_active_form () || !isset ($f->filter))
		{
			$f->min = $f->max = $f->value = '';
			$f->values = array ();
		}

		$f = apply_filters ('bps_field_data_for_filters', $f);
		$f = apply_filters ('bps_field_data_for_search_form', $f);
		$F->fields[] = $f;

		if (!empty ($custom_label))
			$F->fields[] = bps_set_hidden_field ($f->code. '_label', $custom_label);
	}

	$F->fields[] = bps_set_hidden_field ('text_search', $meta['searchmode']);
//	$F->fields[] = bps_set_hidden_field ('bp_profile_search', $form);

	$F = apply_filters ('bps_search_form_data', $F);

	$F->toggle_text = esc_attr ($F->toggle_text);
	foreach ($F->fields as $f)
	{
		if (!is_array ($f->value))  $f->value = esc_attr (stripslashes ($f->value));
		if ($f->display == 'hidden')  continue;

		$f->label = esc_attr ($f->label);
		$f->description = esc_attr ($f->description);
		foreach ($f->values as $k => $value)  $f->values[$k] = esc_attr (stripslashes ($value));
		$options = array ();
		foreach ($f->options as $key => $label)  $options[esc_attr ($key)] = esc_attr ($label);
		$f->options = $options;
	}

	return $F;
}

function bps_escaped_filters_data ()
{
	$F = new stdClass;
	$F->action = parse_url ($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$F->fields = array ();

	list ($x, $fields) = bps_get_fields ();
	foreach ($fields as $field)
	{
		if (!isset ($field->filter))  continue;

		$f = clone $field;
		if ($f->filter == 'range')  $f->display = 'range';
		if (empty ($f->display))
		{
			$f->display = apply_filters ('bps_field_type_for_filters', $f->type, $f);
			$f->display = apply_filters ('bps_field_type_for_search_form', $f->display, $f);
		}

		if (empty ($f->label))  $f->label = $f->name;

		$f = apply_filters ('bps_field_data_for_filters', $f);
		$f = apply_filters ('bps_field_data_for_search_form', $f);
		$F->fields[] = $f;
	}

	$F = apply_filters ('bps_filters_data', $F);

	foreach ($F->fields as $f)
	{
		$f->label = esc_attr ($f->label);
		if (!is_array ($f->value))  $f->value = esc_attr (stripslashes ($f->value));
		foreach ($f->values as $k => $value)  $f->values[$k] = stripslashes ($value);

		foreach ($f->options as $key => $label)  $f->options[$key] = esc_attr ($label);
	}

	return $F;
}

function bps_set_template_args ()
{
	$GLOBALS['bps_template_args'] = func_get_args ();
}

function bps_template_args ()
{
	return $GLOBALS['bps_template_args'];
}

function bps_call_template ($template)
{
	$version = BPS_VERSION;
	$args = implode (', ', bps_template_args ());

	echo "\n<!-- BP Profile Search $version $template ($args) -->\n";
	$found = bp_get_template_part ($template);
	if (!$found)  printf ('<p class="bps_error">'. __('%s: Template "%s" not found.', 'bp-profile-search'). '</p>',
		"<strong>BP Profile Search $version</strong>", $template);
	echo "\n<!-- BP Profile Search $version $template ($args) - end -->\n";

	return true;
}

function bps_set_hidden_field ($code, $value)
{
	$new = new stdClass;
	$new->display = 'hidden';
	$new->code = $code;
	$new->value = $value;

	return $new;
}
