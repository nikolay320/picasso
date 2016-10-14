<?php

if ((int)get_option('un_db_revision') < 3)
	add_action('init', 'un_do_db_upgrade', 11);

function un_do_db_upgrade(){
	global $un_default_options, $wp_roles, $wpdb;
	$icons = array('idea' => 'icon-lightbulb', 'question' => 'icon-question-sign', 'problem' => 'icon-exclamation-sign', 'praise' => 'icon-heart');
	$plural = array('idea' => __('Ideas', 'usernoise'), 'question' => __('Questions', 'usernoise'), 'problem' => __('Problems', 'usernoise'), 'praise' => __('Praises', 'usernoise'));
	$index = 0;
	$wpdb->un_termmeta = $wpdb->prefix . "un_termmeta";
	if($wpdb->get_var("SHOW TABLES LIKE '$wpdb->un_termmeta'") != $wpdb->un_termmeta) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = "CREATE TABLE `$wpdb->un_termmeta` (
			`meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`un_term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			`meta_key` varchar(255) DEFAULT NULL,
			`meta_value` longtext,
			PRIMARY KEY (`meta_id`),
			KEY `un_term_id` (`un_term_id`),
			KEY `meta_key` (`meta_key`)

		) DEFAULT CHARSET=" . $wpdb->charset . ";";
		dbDelta($sql);
	}

	foreach(array(
		'idea' => __('Idea', 'usernoise'), 'question' => __('Question', 'usernoise'), 'problem' => __('Problem', 'usernoise'),
		'praise' => __('Praise', 'usernoise')) as $type => $value){
		if (null == ($term = get_term_by('slug', $type, FEEDBACK_TYPE, ARRAY_A))){
			$term = wp_insert_term($value, FEEDBACK_TYPE, array('slug' => $type));
		}
		if (!is_wp_error($term)){
			if (null == un_get_term_meta($term['term_id'], 'icon')){
				un_add_term_meta($term['term_id'], 'icon', $icons[$type], true);
				un_add_term_meta($term['term_id'], 'plural', $plural[$type], true);
				un_add_term_meta($term['term_id'], 'position', $index, true);
			}	
		} else {
			var_dump($term);
			exit;
		}
		
		$index ++;
	}
	if ( ! isset( $wp_roles ) )
		$wp_roles = new WP_Roles();
	foreach(un_get_capable_roles() as $role)
		foreach(un_get_feedback_capabilities() as $cap)
			$wp_roles->add_cap($role, $cap);
	update_option('un_db_revision', 3);
}