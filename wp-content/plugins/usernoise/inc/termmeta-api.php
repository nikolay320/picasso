<?php
global $wpdb;
$wpdb->un_termmeta = $wpdb->prefix . "un_termmeta";

register_activation_hook(__FILE__, 'un_tma_activation_hook');
function un_tma_activation_hook(){
	global $wpdb;
	$wpdb->un_termmeta = $wpdb->prefix . "un_termmeta";
	$charset_collate = '';

	if ( ! empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	if ( ! empty($wpdb->collate) )
		$charset_collate .= " COLLATE $wpdb->collate";

	if($wpdb->get_var("SHOW TABLES LIKE '$wpdb->un_termmeta'") != $wpdb->un_termmeta) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = "CREATE TABLE `$wpdb->un_termmeta` (
			`meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			`meta_key` varchar(255) DEFAULT NULL,
			`meta_value` longtext,
			PRIMARY KEY (`meta_id`),
			KEY `term_id` (`term_id`),
			KEY `meta_key` (`meta_key`)
		) $charset_collate;";
		dbDelta($sql);
	}
}

function un_update_term_meta($term_id, $meta_key, $meta_value, $prev_value = ''){
	return update_metadata('un_term', $term_id, $meta_key, $meta_value, $prev_value);
}

function un_add_term_meta($term_id, $meta_key, $meta_value, $unique = false){
	return add_metadata('un_term', $term_id, $meta_key, $meta_value, $unique);
}

function un_delete_term_meta($term_id, $meta_key, $meta_value = '', $delete_all = false){
	return delete_metadata('un_term', $term_id, $meta_key, $meta_value, $delete_all);
}

function un_get_term_meta($term_id, $key, $single = true){
	return  get_metadata('un_term', $term_id, $key, $single);
}

add_filter('list_terms_exclusions', 'un_tma_filter_list_term_exclusions', 10, 2);

function un_tma_filter_list_term_exclusions($exclusions, $args){
	global $wpdb;
	if (isset($args['meta_compare']) && is_array($args['meta_compare'])) {
		foreach($args['meta_compare'] as $var){
			if ($var['value'] && in_array($var['operation'], array('>', '<', '>=', '<=', '='))) {
				$op = $var['operation'];
				$val = $var['value'];
				if (is_string($val)) $val = "'" . addslashes($val) . "'";
				if (in_array($op, array('>', '<', '>=', '<=', '='))) {
					$exclusions .= $wpdb->prepare(
						" AND t.term_id IN (
							SELECT tm.term_id FROM $wpdb->un_termmeta tm 
							WHERE tm.un_term_id = t.term_id AND meta_key = %s AND meta_value $op $val )", 
						$var['key']);
				}
			}
		}
	}
	return $exclusions;
}

add_filter('get_terms', 'un_tma_filter_get_terms', 10, 3);

function un_tma_filter_get_terms($terms, $taxonomies, $args){
	global $wpdb;
	if (isset($args['un_orderby_meta']) && $args['un_orderby_meta'] && count($terms)){
		$ids = array();
		foreach($terms as $term) $ids []= $term->term_id;
		$ids = implode(',', $ids);
		$ordered = $wpdb->get_col($wpdb->prepare("SELECT t.term_id FROM $wpdb->terms t LEFT OUTER JOIN $wpdb->un_termmeta tm ON t.term_id = tm.un_term_id AND (tm.meta_key = %s OR tm.meta_key IS NULL) WHERE t.term_id IN ($ids) ORDER BY CAST(tm.meta_value AS SIGNED) ASC", $args['un_orderby_meta']));
		$newterms = array();
		$termhash = array();
		foreach($terms as $term) $termhash[$term->term_id] = $term;
		foreach($ordered as $id) $newterms []= $termhash[$id];
		$terms = $newterms;
	}
	return $terms;
}