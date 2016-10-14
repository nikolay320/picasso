<?php
/*
Plugin Name: Sabai Edit to Buddypress Activity

Plugin URI: Sabai Edit to Buddypress Activity

Description: Sabai Edit to Buddypress Activity

Version: 1.0.4

Text Domain: sabai-edit

Domain Path: /languages

Author: phandung122

Author URI: http://www.upwork.com/o/profiles/users/_~016252273d5cf3683a/

License: 

Instruction:

After install and activate this plugin,
Go to Sabai Directory and Sabai Discuss to create these fields:
for each Articles, Reviews, Questions and Answers.
With these setting:
	TYPE: ON/OFF
	field name: 
		field_article_update_activity
		field_review_update_activity
		field_question_update_activity
		field_answer_update_activity
	label: Buddypress update activity
	hide label: check
	checkbox label: Buddypress update activity (or your language string)
	make this field checked by default: check
	display setting: UNCHECK
*/


/**
 * Include CSS file for Multi address.
 */

add_action( 'plugins_loaded', 'myplugin_load_textdomain' );
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function myplugin_load_textdomain() {
  load_plugin_textdomain( 'sabai-edit', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' ); 
} 
 
wp_enqueue_script( 'jquery', plugin_dir_url( __FILE__ ) . 'jquery-1.12.2.min.js');
wp_enqueue_script("sabai-edit", plugin_dir_url( __FILE__ ) . 'sabai_edit.js'); 
 
add_action('sabai_entity_update_content_directory_listing_entity_success', 'entity_update_content_directory_listing_entity_success', 10, 4);
function entity_update_content_directory_listing_entity_success($bundle, $entity, $oldEntity, $values) {	

	
	$entityValue = $entity->getFieldValues();
	$oldEntityValue = $oldEntity->getFieldValues();
	
	if(!$entityValue['field_article_update_activity'][0]) return;
	
	if(
	($entityValue['directory_category'][0]->_properties == $oldEntityValue['directory_category'][0]->_properties)
	&& ($entityValue['field_documentassociefiche'][0]['id'] == $oldEntityValue['field_documentassociefiche'][0]['id']) 
	&& ($entityValue['field_contexte'] == $oldEntityValue['field_contexte']) 
	&& ($entityValue['directory_photos'] == $oldEntityValue['directory_photos'])
	&& ($entityValue['field_videoarticle'] == $oldEntityValue['field_videoarticle'])
	&& ($entity->getTitle() == $oldEntity->getTitle()) 
	&& ($entity->getContent() == $oldEntity->getContent())	
	) return;
	
	if (!function_exists('bp_activity_add')
        ) return;
		$addonSlug = '/outils';
		$addonTitle = 'Articles';
		$directoryLink = get_site_url().$addonSlug;
		$link = get_site_url().$entity->getUrlPath($bundle);
        $user_id = $entity->getAuthorId();
        bp_activity_add(array(
            'user_id' => $user_id,
            'action' => sprintf(
                __('%s edited a listing to %s', 'sabai-edit'),
                bp_core_get_userlink($user_id),
                '<a href="'.$directoryLink.'">' . __('Articles', 'sabai-edit') . '</a>'
            ),
            //'content' => '<a href="'.$link.'">' . $entity->getTitle() . '</a><br>'.$entity->getContent(),
			'content' => implode(PHP_EOL, array( // PHP_EOL is converted to <br />
                '<a href="'.$link.'">' . $entity->getTitle() . '</a>', 
                $entity->getContent(),
            )),
            'primary_link' => $permalink,
            'type' => 'new_' . $bundle->type,
            'item_id' => $entity->getId(),
            'secondary_item_id' => false,
            'recorded_time' => bp_core_current_time(),
            'hide_sitewide' => false,
            'component' => 'sabai-directory',
        ));
		
}

add_action('sabai_entity_update_content_directory_listing_review_entity_success', 'entity_update_content_directory_listing_review_entity_success', 10, 4);
function entity_update_content_directory_listing_review_entity_success($bundle, $entity, $oldEntity, $values) {	
	
	$entityValue = $entity->getFieldValues();
	$oldEntityValue = $oldEntity->getFieldValues();
	
	if(!$entityValue['field_review_update_activity'][0]) return;
	
	if(
	//($entityValue['directory_category'][0]->_properties == $oldEntityValue['directory_category'][0]->_properties)
	($entityValue['field_fichiersarticles'][0]['id'] == $oldEntityValue['field_fichiersarticles'][0]['id']) 
	&& ($entityValue['content_body'] == $oldEntityValue['content_body']) 
	&& ($entityValue['directory_photos'] == $oldEntityValue['directory_photos'])
	&& ($entityValue['field_videoreparticel'] == $oldEntityValue['field_videoreparticel'])
	&& ($entity->getTitle() == $oldEntity->getTitle()) 
	&& ($entity->getContent() == $oldEntity->getContent())	
	) return;
	
	$parentEntity = $entityValue['content_parent'][0];
	$parentEntityId = $parentEntity->getId();
	$parentEntityTitle = $parentEntity->getTitle();
	
	$link = get_site_url().$entity->getUrlPath($bundle);
	$html_link = '<p><a href="'.$link.'">'.$parentEntityTitle.'</a></p>';
	
	
	if (!function_exists('bp_activity_add')
        ) return;
        
        
        $user_id = $entity->getAuthorId();
        bp_activity_add(array(
            'user_id' => $user_id,
            'action' => sprintf(
                __('%s edited a review for %s', 'sabai-edit'),
                bp_core_get_userlink($user_id),
                $html_link
            ),
            //'content' => '<p><a href="'.$link.'">' . $entity->getTitle() . '</a></p>'.'<p>'.$entity->getContent().'</p>',
			'content' => implode(PHP_EOL, array( // PHP_EOL is converted to <br />
                '<a href="'.$link.'">' . $entity->getTitle() . '</a>', 
                $entity->getContent(),
            )),
            
            'primary_link' => $link,
            'type' => 'new_' . $bundle->type,
            'item_id' => $parentEntityId,
            'secondary_item_id' => $entity->getId(),
            'recorded_time' => bp_core_current_time(),
            'hide_sitewide' => false,
            'component' => 'sabai-directory',
        ));
		
}

add_action('sabai_entity_update_content_questions_entity_success', 'entity_update_content_questions_entity_success', 10, 4);
function entity_update_content_questions_entity_success($bundle, $entity, $oldEntity, $values) {	
	
	$entityValue = $entity->getFieldValues();
	$oldEntityValue = $oldEntity->getFieldValues();
	
	if(!$entityValue['field_question_update_activity'][0]) return;
	
	if(
	($entityValue['questions_categories'][0]->_properties == $oldEntityValue['questions_categories'][0]->_properties)
	&& ($entityValue['field_documentassocie'][0]['id'] == $oldEntityValue['field_documentassocie'][0]['id']) 
	&& ($entityValue['content_body'] == $oldEntityValue['content_body']) 
	&& ($entityValue['field_images'] == $oldEntityValue['field_images'])
	&& ($entityValue['field_videoexterne'] == $oldEntityValue['field_videoexterne'])
	&& ($entity->getTitle() == $oldEntity->getTitle()) 
	&& ($entity->getContent() == $oldEntity->getContent())	
	) return;
	
	if (!function_exists('bp_activity_add')
        ) return;
		$addonSlug = '/questions';
		$addonTitle = 'List of questions';
		$questionLink = get_site_url().$addonSlug;
		$link = get_site_url().$entity->getUrlPath($bundle);
        $user_id = $entity->getAuthorId();
        bp_activity_add(array(
            'user_id' => $user_id,
            'action' => sprintf(
                __('%s edited a question to %s', 'sabai-edit'),
                bp_core_get_userlink($user_id),
                '<a href="'.$questionLink.'">' . __('List of questions', 'sabai-edit') . '</a>'
            ),
            //'content' => '<a href="'.$link.'">' . $entity->getTitle() . '</a><br>'.$entity->getContent(),
			'content' => implode(PHP_EOL, array( // PHP_EOL is converted to <br />
                '<a href="'.$link.'">' . $entity->getTitle() . '</a>', 
                $entity->getContent(),
            )),
            'primary_link' => $permalink,
            'type' => 'new_' . $bundle->type,
            'item_id' => $entity->getId(),
            'secondary_item_id' => false,
            'recorded_time' => bp_core_current_time(),
            'hide_sitewide' => false,
            'component' => 'sabai-discuss',
        ));
		
}

add_action('sabai_entity_update_content_questions_answers_entity_success', 'entity_update_content_questions_answers_entity_success', 10, 4);
function entity_update_content_questions_answers_entity_success($bundle, $entity, $oldEntity, $values) {	
	
	$entityValue = $entity->getFieldValues();
	$oldEntityValue = $oldEntity->getFieldValues();
	
	if(!$entityValue['field_answer_update_activity'][0]) return;
	
	if(
	//($entityValue['directory_category'][0]->_properties == $oldEntityValue['directory_category'][0]->_properties)
	($entityValue['field_doc_reponse'][0]['id'] == $oldEntityValue['field_doc_reponse'][0]['id']) 
	&& ($entityValue['content_body'] == $oldEntityValue['content_body']) 
	&& ($entityValue['field_imageresponse'] == $oldEntityValue['field_imageresponse'])
	&& ($entityValue['field_videoreponse'] == $oldEntityValue['field_videoreponse'])
	&& ($entity->getTitle() == $oldEntity->getTitle()) 
	&& ($entity->getContent() == $oldEntity->getContent())	
	) return;
	
	$parentEntity = $entityValue['content_parent'][0];
	$parentEntityId = $parentEntity->getId();
	$parentEntityTitle = $parentEntity->getTitle();
	
	$link = get_site_url().$entity->getUrlPath($bundle);
	$html_link = '<p><a href="'.$link.'">'.$parentEntityTitle.'</a></p>';
	
	
	if (!function_exists('bp_activity_add')
        ) return;
        
        $user_id = $entity->getAuthorId();
        bp_activity_add(array(
            'user_id' => $user_id,
            'action' => sprintf(
                __('%s edited an answer to question %s', 'sabai-edit'),
                bp_core_get_userlink($user_id),
                $html_link
            ),
            //'content' => '<p><a href="'.$link.'">' . $entity->getTitle() . '</a></p>'.'<p>'.$entity->getContent().'</p>',
			'content' => implode(PHP_EOL, array( // PHP_EOL is converted to <br />
                '<a href="'.$link.'">' . $entity->getTitle() . '</a>', 
                $entity->getContent(),
            )),
            
            'primary_link' => $link,
            'type' => 'new_' . $bundle->type,
            'item_id' => $parentEntityId,
            'secondary_item_id' => $entity->getId(),
            'recorded_time' => bp_core_current_time(),
            'hide_sitewide' => false,
            'component' => 'sabai-discuss',
        ));
	
}