<?php

function pmxe_pmxe_before_export($export_id)
{
	$export = new PMXE_Export_Record();
	$export->getById($export_id);	
	
	if ( ! $export->isEmpty() )
	{				
		if ( ! $export->options['export_only_new_stuff'] )
		{
			$postList = new PMXE_Post_List();
			$missingPosts = $postList->getBy(array('export_id' => $export_id, 'iteration !=' => --$export->iteration));
			$missing_ids = array();
			if ( ! $missingPosts->isEmpty() ): 
					
				foreach ($missingPosts as $missingPost) 
				{			
					$missing_ids[] = $missingPost['post_id'];												
				}

			endif;	

			if ( ! empty($missing_ids))
			{
				global $wpdb;
				// Delete records form pmxe_posts
				$sql = "DELETE FROM " . PMXE_Plugin::getInstance()->getTablePrefix() . "posts WHERE post_id IN (" . implode(',', $missing_ids) . ") AND export_id = %d";
				$wpdb->query( 
					$wpdb->prepare($sql, $export->id)
				);
			}
		}	

		if ( empty($export->parent_id) )
		{
			delete_option( 'wp_all_export_queue_' . $export->id );		
		}

		// create an additional exports for order's related stuff
		if ( ! empty($export->options['cpt']) and class_exists('WooCommerce') and in_array('shop_order', $export->options['cpt']) and PMXE_Export_Record::is_bundle_supported($export->options))
		{
			$post_types = array('product', 'shop_coupon', 'shop_customer');

			$parent_export_options = $export->options;

			foreach ($post_types as $post_type) 
			{
				$post = array(
					'cpt' 			=> ($post_type == 'product') ? array('product', 'product_variation') : array($post_type),
					'export_to' 	=> $export->options['export_to'],
					'export_type' 	=> 'specific',
					'wp_query' 		=> '',
					'filter_rules_hierarhy' => '',
					'product_matching_mode' => 'strict',
					'wp_query_selector' 	=> 'wp_query',	
					'is_loaded_template'    => ''				
				);				

				$auto_generate = XmlCsvExport::auto_genetate_export_fields($post, false);

				$options = $post + $auto_generate + PMXE_Plugin::get_default_import_options();
				// do not generate imports for child exports
				$options['is_generate_import'] = 0;				

				$child_export = new PMXE_Export_Record();
				$child_export->getBy(array(
					'parent_id' 		 => $export_id, 
					'export_post_type' 	 => $post_type
				));

				$child_export->set(array(					
					'parent_id'     	 => $export_id, 					
					'export_post_type'   => $post_type,
					'options'       	 => $options,
					'friendly_name'      => $export->friendly_name . ' - ' . ucwords(str_replace("_", " ", $post_type)),
					'registered_on'      => date('Y-m-d H:i:s'),
					'last_activity'      => date('Y-m-d H:i:s')
				))->save();		

				PMXE_Wpallimport::generateImportTemplate( $child_export );								
			}
			
			$errors = new WP_Error();
			$engine = new XmlExportEngine($parent_export_options, $errors);	
			$engine->init_additional_data();	
			$engine->init_available_data();			
		}	
	}		
}