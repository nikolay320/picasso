<?php
class Sabai_Addon_Voting extends Sabai_Addon
    implements Sabai_Addon_System_IMainRouter,
               Sabai_Addon_System_IAdminRouter,
               Sabai_Addon_Field_ITypes,
               Sabai_Addon_Field_IFilters,
               Sabai_Addon_Field_IRenderers,
               Sabai_Addon_Form_IFields
{
    const VERSION = '1.3.28', PACKAGE = 'sabai';
    const FLAG_VALUE_SPAM = 5, FLAG_VALUE_OFFENSIVE = 6, FLAG_VALUE_OFFTOPIC = 2, FLAG_VALUE_OTHER = 0;
    
    public function systemGetMainRoutes()
    {
        $routes = array();
        foreach ($this->_application->getModel('FieldConfig', 'Entity')->type_in($this->fieldGetTypeNames())->fetch()->with('Fields', 'Bundle') as $field_config) {                
            foreach ($field_config->Fields as $field) {
                if (!$field->Bundle) continue;

                $base_path = empty($field->Bundle->info['permalink_path'])
                    ? $field->Bundle->getPath() . '/:entity_id'
                    : $field->Bundle->info['permalink_path'] . '/:slug';
                if (!isset($routes[$base_path . '/vote'])) {
                    $routes[$base_path . '/vote'] = array();
                }
                $field_settings = $field->getFieldSettings();
                $tag = $field_settings['tag'];
                $routes[$base_path . '/vote/' . $tag] = array(
                    'controller' => 'VoteEntity',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'data' => array(
                        'tag' => $tag,
                        'check_perms' => !empty($field_config->settings['require_vote_permissions']),
                        'check_own' => !empty($field_config->settings['vote_own_permission_label']),
                        'allow_anonymous' => !empty($field_config->settings['allow_anonymous']),
                    ),
                    'callback_path' => 'vote_entity',
                    'access_callback' => true,
                );
                $routes[$base_path . '/vote/' . $tag . '/form'] = array(
                    'controller' => 'VoteEntityForm',
                    'callback_path' => 'vote_entity_form',
                    'title_callback' => true,
                );
                
                switch ($tag) {
                    case 'flag':
                        $routes[$base_path . '/voting/flags/ignore'] = array(
                            'controller' => 'IgnoreFlags',
                            'callback_path' => 'ignore_flags',
                            'access_callback' => true,
                            'title_callback' => true,
                        );
                        break;
                }
            }
        }

        return $routes;
    }

    public function systemOnAccessMainRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'vote_entity':                
                $context->voting_tag = $route['data']['tag'];
                if ($route['data']['check_perms']) {
                    if ($this->_application->getUser()->isAnonymous() && empty($route['data']['allow_anonymous'])) {
                        $context->setUnauthorizedError($this->_application->Entity_Url($context->entity));
                        return false;
                    }
                    // Check permission
                    if (!$this->_application->HasPermission($context->entity->getBundleName() . '_voting_' . $context->voting_tag)) {
                        $context->setError(__('You do not have the permission to perform this action.', 'sabai'));
                        return false;
                    }
                    if ($route['data']['check_own']) {
                        // Require additional permission to vote for own post
                        if ($context->entity->getAuthorId() === $this->_application->getUser()->id
                            && !$this->_application->HasPermission($context->entity->getBundleName() . '_voting_own_' . $context->voting_tag)
                        ) {
                            $context->setError(__('You do not have the permission to perform this action.', 'sabai'));
                            return false;
                        }
                    }
                } else {
                    // Do not allow anonymous users to vote if permission is not being checked
                    if ($this->_application->getUser()->isAnonymous()) {
                        $context->setUnauthorizedError($this->_application->Entity_Url($context->entity));
                        return false;
                    }
                }
                return true;
            case 'ignore_flags':
                // Require content moderation permission
                return $this->_application->HasPermission($context->entity->getBundleName() . '_manage');
        }
    }

    public function systemGetMainRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'vote_entity_form':
                return sprintf(__('Vote for "%s"', 'sabai'), $context->entity->getTitle());
            case 'ignore_flags':
                return __('Ignore Flags', 'sabai');
        }
    }
    
    public function systemGetAdminRoutes()
    {
        $routes = array();
        foreach ($this->_application->getModel('FieldConfig', 'Entity')->type_in($this->fieldGetTypeNames())->fetch()->with('Fields', 'Bundle') as $field_config) {                
            foreach ($field_config->Fields as $field) {
                if (!$field->Bundle
                 //   || !in_array($field->Bundle->entitytype_name, array('content', 'taxonomy'))
                ) continue;

                $tag = substr($field->getFieldName(), strlen('voting_'));
                $routes[$field->Bundle->getAdminPath() . '/:entity_id/voting_' . $tag] = array(
                    'controller' => ucfirst($tag),
                    'callback_path' => 'votes',
                    'access_callback' => true,
                    'title' => (string)$field,
                    'title_callback' => true,
                    'ajax' => 1,
                    'type' => Sabai::ROUTE_TAB,
                    'data' => array(
                        'tag' => $tag,
                    ),
                    'weight' => 5,
                );
            }
        }

        return $routes;
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'votes':        
                $context->voting_tag = $route['data']['tag'];
                return true;
        }
    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'votes':
                if ($titleType !== Sabai::ROUTE_TITLE_TAB) {
                    return $title;
                }
                if (in_array($context->voting_tag, array('rating', 'default'))) {
                    $count = @$context->entity->voting_rating['']['count'];
                } else {
                    $count = $context->entity->getSingleFieldValue('voting_' . $context->voting_tag, 'count');
                }
                return empty($count) ? $title : sprintf(__('%s (%d)', 'sabai'), $title, $count);
        }
    }

    public function fieldGetTypeNames()
    {
        return array('voting_default', 'voting_updown', 'voting_rating', 'voting_favorite', 'voting_flag', 'voting_helpful');
    }

    public function fieldGetType($name)
    {
        return new Sabai_Addon_Voting_FieldType($this, $name);
    }

    public function fieldGetFilterNames()
    {
        return array('voting_rating');
    }

    public function fieldGetFilter($name)
    {
        switch ($name) {
            case 'voting_rating':
                return new Sabai_Addon_Voting_RatingFieldFilter($this, $name);
        }
    }
    
    public function fieldGetRendererNames()
    {
        return array('voting_rating');
    }

    public function fieldGetRenderer($name)
    {
        switch ($name) {
            case 'voting_rating':
                return new Sabai_Addon_Voting_RatingFieldRenderer($this, $name);
        }
    }

    public function onContentPermissionsFilter(&$permissions, $bundle)
    {
        foreach ($this->_application->Voting_TagSettings() as $tag => $settings) {
            if (empty($bundle->info['voting_' . $tag])
                || !isset($settings['require_vote_permissions'])
                || $settings['require_vote_permissions'] === false
            ) {
                continue;
            }
            $guest_allowed = !empty($settings['allow_anonymous']);
            $permissions['voting_' . $tag] = array($this->_application->Translate($settings['vote_permission_label']), '', $guest_allowed);
            if (!empty($settings['vote_own_permission_label'])) {
                $permissions['voting_own_' . $tag] = array($this->_application->Translate($settings['vote_own_permission_label']), '', $guest_allowed);
            }
            if (!empty($settings['require_vote_down_permission'])) {
                $permissions['voting_down_' . $tag] = array($this->_application->Translate($settings['vote_down_permission_label']), '', $guest_allowed);
            }
        }
    }
    
    public function onContentDefaultPermissionsFilter(&$permissions, $bundle)
    {
        foreach ($this->_application->Voting_TagSettings() as $tag => $settings) {
            if (empty($bundle->info['voting_' . $tag])
                || !isset($settings['require_vote_permissions'])
                || $settings['require_vote_permissions'] === false
            ) {
                continue;
            }
            $permissions[] = 'voting_' . $tag;
            $permissions[] = 'voting_down_' . $tag;
        }
    }
    
    public function onEntityCreateBundlesSuccess($entityType, $bundles)
    {
        $reload = false;
        foreach ($bundles as $bundle) {
            if (!empty($bundle->info['voting_updown'])) {
                if ($this->_createVotingUpdownEntityField($bundle)) {
                    $reload = true;
                }
            }
            if (!empty($bundle->info['voting_helpful'])) {
                if ($this->_createVotingHelpfulEntityField($bundle)) {
                    $reload = true;
                }
            }
            if (!empty($bundle->info['voting_rating'])) {
                if ($this->_createVotingRatingEntityField($bundle)) {
                    $reload = true;
                }
            }
            if (!empty($bundle->info['voting_favorite'])) {
                if ($this->_createVotingFavoriteEntityField($bundle)) {
                    $reload = true;
                }
            }
            if (!empty($bundle->info['voting_flag'])) {
                if ($this->_createVotingFlagEntityField($bundle)) {
                    $reload = true;
                }
            }
        }
        if ($reload) {
            // Reload system routing tables to reflect changes
            $this->_application->getAddon('System')->reloadRoutes($this)->reloadRoutes($this, true);
        }
    }
    
    private function _createVotingUpdownEntityField(Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        return $this->_application->getAddon('Entity')->createEntityField(
            $bundle,
            'voting_updown',
            array(
                'type' => 'voting_updown',
                'label' => isset($bundle->info['voting_updown']['label']) ? $bundle->info['voting_updown']['label'] : __('Votes', 'sabai'),
                'settings' => array(
                    'tag' => 'updown',
                    'min' => -1,
                    'max' => 1,
                    'step' => 1,
                    'allow_empty' => false,
                    'allow_anonymous' => true,
                    'require_vote_permissions' => true,
                    'require_vote_down_permission' => true,
                    'vote_permission_label' => $this->_application->_t(_n_noop('Vote up %s', 'Vote up %s', 'sabai'), 'sabai'),
                    'vote_own_permission_label' => $this->_application->_t(_n_noop('Vote up own %s', 'Vote up own %s', 'sabai'), 'sabai'),
                    'vote_down_permission_label' => $this->_application->_t(_n_noop('Vote down %s', 'Vote down %s', 'sabai'), 'sabai'),
                ),
                'weight' => 99,
                'max_num_items' => 1, // Only 1 entry per entity should be created
            ),
            Sabai_Addon_Entity::FIELD_REALM_ALL,
            true
        );
    }
    
    private function _createVotingHelpfulEntityField(Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        return $this->_application->getAddon('Entity')->createEntityField(
            $bundle,
            'voting_helpful',
            array(
                'type' => 'voting_helpful',
                'label' => isset($bundle->info['voting_helpful']['label']) ? $bundle->info['voting_helpful']['label'] : __('Votes', 'sabai'),
                'settings' => array(
                    'tag' => 'helpful',
                    'min' => 0,
                    'max' => 1,
                    'step' => 1,
                    'allow_empty' => true,
                    'allow_anonymous' => true,
                    'require_vote_permissions' => true,
                    'require_vote_down_permission' => false,
                    'vote_permission_label' => $this->_application->_t(_n_noop('Vote %s helpful', 'Vote %s helpful', 'sabai'), 'sabai'),
                    'vote_own_permission_label' => $this->_application->_t(_n_noop('Vote own %s helpful', 'Vote own %s helpful', 'sabai'), 'sabai'),
                ),
                'weight' => 99,
                'max_num_items' => 1, // Only 1 entry per entity should be created
            ),
            Sabai_Addon_Entity::FIELD_REALM_ALL,
            true
        );
    }
    
    private function _createVotingRatingEntityField(Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        return $this->_application->getAddon('Entity')->createEntityField(
            $bundle,
            'voting_rating',
            array(
                'type' => 'voting_rating',
                'label' => isset($bundle->info['voting_rating']['label']) ? $bundle->info['voting_rating']['label'] : __('Rating', 'sabai'),
                'settings' => array(
                    'tag' => 'rating',
                    'min' => 0,
                    'max' => 5,
                    'step' => 0.1,
                    'allow_empty' => true,
                    'allow_multiple' => true,
                    'require_vote_permissions' => true,
                    'require_vote_down_permission' => false,
                    'vote_permission_label' => $this->_application->_t(_n_noop('Rate %s', 'Rate %s', 'sabai'), 'sabai'),
                    'vote_own_permission_label' => $this->_application->_t(_n_noop('Rate own %s', 'Rate own %s', 'sabai'), 'sabai'),
                ),
                'weight' => 99,
                'max_num_items' => 0,
                'filter' => array(
                    'type' => 'voting_rating',
                    'name' => 'voting_rating',
                    'title' => __('Rating', 'sabai'),
                    'row' => @$bundle->info['voting_rating']['filter']['row'],
                    'col' => @$bundle->info['voting_rating']['filter']['col'],
                    'weight' => @$bundle->info['voting_rating']['filter']['weight'],
                    'settings' => @$bundle->info['voting_rating']['filter']['settings'],
                ),
                'view' => array(
                    'default' => 'default',
                    'summary' => 'summary,'
                ),
            ),
            Sabai_Addon_Entity::FIELD_REALM_ALL
        );
    }
    
    private function _createVotingFavoriteEntityField(Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        return $this->_application->getAddon('Entity')->createEntityField(
            $bundle,
            'voting_favorite',
            array(
                'type' => 'voting_favorite',
                'label' => isset($bundle->info['voting_favorite']['label']) ? $bundle->info['voting_favorite']['label'] : __('Favorites', 'sabai'),
                'settings' => array(
                    'tag' => 'favorite',
                    'min' => 1,
                    'max' => 1,
                    'step' => 1,
                    'allow_empty' => false,
                    'require_vote_permissions' => false,
                    'require_vote_down_permission' => false,
                ),
                'weight' => 99,
                'max_num_items' => 1, // Only 1 entry per entity should be created
            ),
            Sabai_Addon_Entity::FIELD_REALM_ALL
        );
    }
    
    private function _createVotingFlagEntityField(Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        return $this->_application->getAddon('Entity')->createEntityField(
            $bundle,
            'voting_flag',
            array(
                'type' => 'voting_flag',
                'label' => isset($bundle->info['voting_flag']['label']) ? $bundle->info['voting_flag']['label'] : __('Flags', 'sabai'),
                'settings' => array(
                    'tag' => 'flag',
                    'min' => self::FLAG_VALUE_OTHER,
                    'max' => self::FLAG_VALUE_OFFENSIVE,
                    'step' => 1,
                    'allow_empty' => true,
                    'require_vote_permissions' => true,
                    'require_vote_down_permission' => false,
                    'vote_permission_label' => $this->_application->_t(_n_noop('Flag %s', 'Flag %s', 'sabai'), 'sabai'),
                    'form_title' => __('Reason for flagging', 'sabai'),
                    'form_options' => $this->_application->Voting_FlagOptions(),
                    'form_other_option' => self::FLAG_VALUE_OTHER,
                    'form_default_value' => self::FLAG_VALUE_SPAM,
                    'form_redo_msg' => __('You have already flagged this %s. Press the button to redo flagging.', 'sabai'),
                    'form_redo_btn' => __('Redo Flagging', 'sabai'),
                    'form_submit_btn' => __('Flag %s', 'sabai'),
                    'form_success_msg' => __('Thanks, we will take a look at it.', 'sabai'),
                ),
                'weight' => 99,
                'max_num_items' => 1, // Only 1 entry per entity should be created
            ),
            Sabai_Addon_Entity::FIELD_REALM_ALL,
            true
        );
    }
    
    public function onEntityUpdateBundlesSuccess($entityType, $bundles)
    {
        $this->onEntityCreateBundlesSuccess($entityType, $bundles);
    }
    
    public function onEntityDeleteBundlesSuccess($entityType, $bundles)
    {
        $criteria = $this->getModel()->createCriteria('Vote')->bundleId_in(array_keys($bundles));
        $this->getModel()->getGateway('Vote')->deleteByCriteria($criteria);
    }
    
    public function onEntityCreateEntity($bundle, &$values)
    {
        // We need to create an empty entry so that order by sql query returns 
        // results in the correct order.

        if (!empty($bundle->info['voting_updown'])) {
            if (!isset($values['voting_updown'])) {
                $values['voting_updown'] = array();
            }
        }
    }
    
    public function onEntityRenderEntities($bundle, $entities, $displayMode)
    {
        if (!in_array($bundle->entitytype_name, array('content', 'wppost'))) {
            return;
        }
        
        if ($displayMode === 'full') {   
            $votes = $this->getModel()->getGateway('Vote')->getVotes($bundle->entitytype_name, array_keys($entities), $this->_application->getUser()->id);
            foreach ($votes as $tag => $_votes) {
                foreach ($_votes as $entity_id => $value) {
                    $entities[$entity_id]->data['voting_' . $tag . '_voted'] = $value;
                }
            }
        } elseif ($displayMode === 'favorited') {
            if ($this->_application->getUser()->isAnonymous()) {
                return;
            }
            $votes = $this->getModel()->getGateway('Vote')->getVotes($bundle->entitytype_name, array_keys($entities), $this->_application->getUser()->id, array('favorite'));
            if (!empty($votes['favorite'])) {
                foreach ($votes['favorite'] as $entity_id => $value) {
                    $entities[$entity_id]->data['voting_favorite_voted'] = $value;
                }
            }
        } elseif ($displayMode === 'flagged') {
            if ($this->_application->getUser()->isAnonymous()) {
                return;
            }
            foreach ($this->getModel('Vote')->tag_is('flag')->entityId_in(array_keys($entities))->fetch()->with('User') as $flag) {
                $entities[$flag->entity_id]->data['voting_flags'][] = $flag;
            }
        }
    }
    
    public function getFieldByTag($tag)
    {
        return $this->_application->getModel('FieldConfig', 'Entity')->name_is('voting_' . $tag)->fetchOne();
    }
    
    public function onEntityRenderHtml(Sabai_Addon_Entity_Model_Bundle $bundle, Sabai_Addon_Entity_IEntity $entity, $displayMode, $id, &$classes, &$links, &$buttons)
    {
        if (!in_array($bundle->entitytype_name, array('content', 'wppost'))
            || $displayMode === 'preview'
        ) {
            return;
        }
         
        if (!empty($bundle->info['voting_helpful']['button_enable'])) {
            if ($displayMode === 'full'
                && $this->_application->HasPermission($entity->getBundleName() . '_voting_helpful')
            ) {
                if (empty($entity->data['voting_helpful_voted'])) {
                    $title = sprintf(isset($bundle->info['voting_helpful']['button_on_title']) ? $bundle->info['voting_helpful']['button_on_title'] : __('Vote for this %s', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true));
                } else {
                    $title = sprintf(isset($bundle->info['voting_helpful']['button_off_title']) ? $bundle->info['voting_helpful']['button_off_title'] : __('Unvote for this %s', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true));
                }
                $buttons['links']['voting_helpful'] = $this->_application->Voting_RenderVoteLink($entity, array(
                    'label' => isset($bundle->info['voting_helpful']['button_label']) ? $bundle->info['voting_helpful']['button_label'] : __('Vote', 'sabai'),
                    'title' => $title,
                    'active' => !empty($entity->data['voting_helpful_voted']),
                    'icon' => isset($bundle->info['voting_helpful']['icon']) ? $bundle->info['voting_helpful']['icon'] : 'thumbs-up',
                ));
            }
        }
        
        if (!empty($bundle->info['voting_favorite']['button_enable'])) {
            if ($displayMode === 'full') {
                if (empty($entity->data['voting_favorite_voted'])) {
                    $title = sprintf(isset($bundle->info['voting_favorite']['button_on_title']) ? $bundle->info['voting_favorite']['button_on_title'] : __('Bookmark this %s', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true));
                } else {
                    $title= sprintf(isset($bundle->info['voting_favorite']['button_off_title']) ? $bundle->info['voting_favorite']['button_off_title'] : __('Unbookmark this %s', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true));
                }
                $buttons['links']['voting_favorite'] = $this->_application->Voting_RenderVoteLink($entity, array(
                    'tag' => 'favorite',
                    'label' => isset($bundle->info['voting_favorite']['button_label']) ? $bundle->info['voting_favorite']['button_label'] : __('Bookmark', 'sabai'),
                    'title' => $title,
                    'active' => !empty($entity->data['voting_favorite_voted']),
                    'icon' => isset($bundle->info['voting_favorite']['icon']) ? $bundle->info['voting_favorite']['icon'] : 'bookmark',
                ));
            } elseif ($displayMode === 'favorited') {
                if (!empty($entity->data['voting_favorite_voted'])) {
                    $buttons['links']['voting_favorite'] = $this->_application->Voting_RenderVoteLink($entity, array(
                        'tag' => 'favorite',
                        'label' => isset($bundle->info['voting_favorite']['button_label']) ? $bundle->info['voting_favorite']['button_label'] : __('Bookmark', 'sabai'),
                        'title' => sprintf(isset($bundle->info['voting_favorite']['button_off_title']) ? $bundle->info['voting_favorite']['button_off_title'] : __('Unbookmark this %s', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true)),
                        'active' => !empty($entity->data['voting_favorite_voted']),
                        'icon' => isset($bundle->info['voting_favorite']['icon']) ? $bundle->info['voting_favorite']['icon'] : 'bookmark',
                    ));
                }
            }
        }
        
        if ($this->_application->getUser()->isAnonymous()) {
            return;
        }
        
        if (!empty($bundle->info['voting_flag'])) {
            if ($displayMode === 'full'
                && $this->_application->HasPermission($entity->getBundleName() . '_voting_flag')
            ) {
                $title = sprintf(isset($bundle->info['voting_flag']['button_title']) ? $bundle->info['voting_flag']['button_title'] : __('Flag this %s', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true));
                $links['voting_flag'] = $this->_application->LinkToModal(
                    isset($bundle->info['voting_flag']['button_label']) ? $bundle->info['voting_flag']['button_label'] : __('Flag', 'sabai'),
                    $this->_application->Entity_Url($entity, '/vote/flag/form', array('update_target_id' => $id)),
                    array('width' => 470, 'icon' => 'flag', 'active' => !empty($entity->data['voting_flag_voted'])),
                    array('class' => 'sabai-voting-btn-flag', 'title' => $title)
                );
            }
        
            if (($flag_count = $entity->getSingleFieldValue('voting_flag', 'count'))
                && $this->_application->HasPermission($entity->getBundleName() . '_manage')
            ) {
                // Let the moderators know that this content has been flagged
                $classes[] = 'sabai-voting-content-flagged';
                $entity->data['entity_labels']['voting_flagged'] = array(
                    'label' => __('Flagged', 'sabai'),
                    'title' => $title = sprintf(__('This post has %d flags', 'sabai'), $flag_count),
                    'icon' => 'flag',
                );
                $entity->data['entity_icons']['voting_flagged'] = array(
                    'title' => $title,
                    'icon' => 'flag',
                );
            }
        }
    }
    
    public function recalculateEntityVotes(Sabai_Addon_Entity_IEntity $entity, $tag, $update = true)
    {
        // Calculate results
        $results = $this->getModel()->getGateway('Vote')
            ->getResults($entity->getType(), $entity->getId(), $tag);

        // Field values for the entity
        $values = array();
        foreach ($results as $name => $result) {
            $values[] = array(
                'name' => $name,
                'average' => $result['sum'] && $result['count'] ? round($result['sum'] / $result['count'], 2) : 0.00,
            ) + $result;
        }

        if ($update) {
            // Update voting fields of the entity
            $this->_application->Entity_Save(
                $entity,
                array('voting_' . $tag => $values),
                array('entity_field_max_num_values' => array('voting_' . $tag => 0)) // for backward compat with v1.2 where max_num was set to 1
            );
        }
        
        return $results;
    }
    
    public function deleteEntityVotes($entityId, $tag, $commit = true)
    {
        if ($entityId instanceof Sabai_Addon_Entity_IEntity) {
            $entityId = $entityId->getId();
        }
        $this->getModel('Vote')
            ->entityId_in((array)$entityId)
            ->tag_is($tag)
            ->fetch()
            ->delete($commit);
    }

    public function onFormBuildContentAdminListPosts(&$form, &$storage)
    {
        $this->_onFormBuildContentAdminListPosts($form);
    }
    
    public function onFormBuildContentAdminListChildPosts(&$form, &$storage)
    {
        $this->_onFormBuildContentAdminListPosts($form);
    }
    
    private function _onFormBuildContentAdminListPosts(&$form)
    {
        $has_voting = false;
        if (!empty($form['#bundle']->info['voting_updown'])) {
            $voting_updown = $has_voting = true;
        }
        if (!empty($form['#bundle']->info['voting_favorite'])) {
            $voting_favorite = $has_voting = true;
        }
        if (!empty($form['#bundle']->info['voting_flag'])) {
            $voting_flag = $has_voting = true;
        }
        if (!empty($form['#bundle']->info['voting_rating'])) {
            $voting_rating = $has_voting = true;
        }
        
        if (!$has_voting) return;

        if (!empty($voting_updown)) {
            $title = isset($form['#bundle']->info['voting_updown']['title']) ? $form['#bundle']->info['voting_updown']['title'] : __('Votes', 'sabai');
            $form['entities']['#header']['vote'] = array(
                'order' => 30,
                'label' => '<i title="'. Sabai::h($title) .'" class="fa fa-lg fa-thumbs-up"></i>',
            );
            $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME]['action']['#options']['clear_votes'] = sprintf(__('Clear %s', 'sabai'), $title);
        }
        if (!empty($voting_flag)) {
            $title = isset($form['#bundle']->info['voting_flag']['title']) ? $form['#bundle']->info['voting_flag']['title'] : __('Flags', 'sabai');
            $form['entities']['#header']['flag'] = array(
                'order' => 32,
                'label' => '<i title="'. Sabai::h($title) .'" class="fa fa-lg fa-flag"></i>',
            );
            $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME]['action']['#options']['clear_flags'] = sprintf(__('Clear %s', 'sabai'), $title);
            
            $form['#filters']['voting_flag'] = array(
                'default_option_label' => __('Flagged/Unflagged', 'sabai'),
                'options' => array(1 => __('Flagged', 'sabai'), 2 => __('Unflagged', 'sabai')),
                'order' => 50,
            );     
        }
        if (!empty($voting_favorite)) {
            $title = isset($form['#bundle']->info['voting_favorite']['title']) ? $form['#bundle']->info['voting_favorite']['title'] : __('Favorites', 'sabai');
            $form['entities']['#header']['favorite'] = array(
                'order' => 31,
                'label' => isset($form['#bundle']->info['voting_favorite']['icon'])
                    ? '<i title="'. Sabai::h($title) .'" class="fa fa-lg fa-' . $form['#bundle']->info['voting_favorite']['icon'] .'"></i>' 
                    : '<i title="'. Sabai::h($title) .'" class="fa fa-lg fa-bookmark"></i>',
            );
            $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME]['action']['#options']['clear_favorites'] = sprintf(__('Clear %s', 'sabai'), $title);
        }
        if (!empty($voting_rating)) {
            $title = isset($form['#bundle']->info['voting_rating']['title']) ? $form['#bundle']->info['voting_rating']['title'] : __('Ratings', 'sabai');
            $form['entities']['#header']['rating'] = array(
                'order' => 33,
                'label' => '<i title="'. Sabai::h($title) .'" class="fa fa-lg fa-star"></i>',
            );
            $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME]['action']['#options']['clear_ratings'] = sprintf(__('Clear %s', 'sabai'), $title);
        }
        if (empty($form['entities']['#options'])) {
            return;
        }
        foreach ($form['entities']['#options'] as $entity_id => $data) {
            $entity = $data['#entity'];
            $entity_path = $form['#bundle']->getAdminPath() . '/' . $entity->getId();
            if (!empty($voting_updown)) {
                $form['entities']['#options'][$entity_id]['vote'] = ($vote_count = $entity->getSingleFieldValue('voting_updown', 'count'))
                    ? $this->_application->LinkTo(
                          sprintf('%d (%d)', $vote_count, ($vote_sum = $entity->getSingleFieldValue('voting_updown', 'sum'))),
                          $this->_application->Url($entity_path . '/voting_updown'),
                          array(),
                          array('title' => sprintf(_n('%d vote (score: %d)', '%d votes (score: %d)', $vote_count, 'sabai'), $vote_count, $vote_sum))
                       )
                    : '0 (0)';
            }
            if (!empty($voting_favorite)) {
                $form['entities']['#options'][$entity_id]['favorite'] = ($favorite_count = $entity->getSingleFieldValue('voting_favorite', 'count'))
                    ? $this->_application->LinkTo(
                          $favorite_count,
                          $this->_application->Url($entity_path . '/voting_favorite'),
                          array(),
                          array('title' => sprintf(_n('%d favorite', '%d favorites', $favorite_count, 'sabai'), $favorite_count))
                       )
                    : 0;
            }
            if (!empty($voting_flag)) {
                $form['entities']['#options'][$entity_id]['flag'] = ($flag_count = $entity->getSingleFieldValue('voting_flag', 'count'))
                    ? $this->_application->LinkTo(
                          sprintf('%d (%d)', $flag_count, ($flag_sum = $entity->getSingleFieldValue('voting_flag', 'sum'))),
                          $this->_application->Url($entity_path . '/voting_flag'),
                          array(),
                          array('title' => sprintf(_n('%d flag (spam score: %d)', '%d flags (spam score: %d)', $flag_count, 'sabai'), $flag_count, $flag_sum))
                      )
                    : '0 (0)';
            }
            if (!empty($voting_rating)) {
                $form['entities']['#options'][$entity_id]['rating'] = ($rating_count = $entity->getSingleFieldValue('voting_rating', 'count', ''))
                    ? $this->_application->LinkTo(
                          sprintf('%d (%.2f)', $rating_count, ($rating_avg = $entity->getSingleFieldValue('voting_rating', 'average', ''))),
                          $this->_application->Url($entity_path . '/voting_rating'),
                          array(),
                          array('title' => sprintf(__('%.2f out of 5 stars', 'sabai'), $rating_avg))
                       )
                    : 0;
            }
        }

        $form['#submit'][0][] = array($this, 'updateEntities');
    }
    
    public function updateEntities(Sabai_Addon_Form_Form $form)
    {
        if (!empty($form->values['entities'])) {
            switch ($form->values['action']) {
                case 'clear_flags':
                    $this->_application->Voting_DeleteVotes($form->values['entities'], 'flag');
                    break;
                case 'clear_votes':
                    $this->_application->Voting_DeleteVotes($form->values['entities'], 'updown');
                    break;
                case 'clear_favorites':
                    $this->_application->Voting_DeleteVotes($form->values['entities'], 'favorite');
                    break;
                case 'clear_ratings':
                    $this->_application->Voting_DeleteVotes($form->values['entities'], 'rating');
                    break;
            }
        }
    }
    
    public function onContentPostsTrashed($entities)
    {
        // Clear flags of trashed posts
        $this->_application->Voting_DeleteVotes(array_keys($entities), 'flag');
    }
    
    public function onCommentFlaggedAsSpam($comment, $entity)
    {
        $vote_comment = sprintf(
            __('Comment posted by %s on %s has been marked as spam (spam score: %d, flag count: %d)', 'sabai'),
            $comment->User->name,
            $this->_application->DateTime($comment->published_at),
            $comment->flag_sum,
            $comment->flag_count
        );
        $this->_application->Voting_CastVote($entity, 'flag', self::FLAG_VALUE_OTHER, array('comment' => $vote_comment, 'system' => true));
    }
    
    public function formGetFieldTypes()
    {
        return array('voting_rateit');
    }

    public function formGetField($type)
    {
        return new Sabai_Addon_Voting_RatingFormField($this, $type);
    }
    
    public function onContentAdminPostsUrlParamsFilter(&$urlParams, $context, $bundle)
    {
        if (!empty($bundle->info['voting_flag'])) {
            if ($voting_flag = $context->getRequest()->asInt('voting_flag')){
                $urlParams['voting_flag'] = $voting_flag;
            }
        }
    }
    
    public function onContentAdminPostsQuery($context, $bundle, $query, $countQuery, $sort, $order)
    {
        if (!empty($bundle->info['voting_flag'])) {
            if ($voting_flag = $context->getRequest()->asInt('voting_flag')){
                switch ($voting_flag) {
                    case 1:
                        $query->fieldIsGreaterThan('voting_flag', 0, 'count');
                        $countQuery->fieldIsGreaterThan('voting_flag', 0, 'count');
                    break;
                    case 2:
                        $query->fieldIsNull('voting_flag', 'count');
                        $countQuery->fieldIsNull('voting_flag', 'count');
                    break;
                }
            }
        }
    }
    
    
    public function onSabaiPlatformWordPressAdminInit()
    {
        foreach ($this->_application->getModel('FieldConfig', 'Entity')->type_in($this->fieldGetTypeNames())->fetch()->with('Fields', 'Bundle') as $field_config) {                
            foreach ($field_config->Fields as $field) {
                if (!$field->Bundle) continue;
                add_filter('manage_' . $field->Bundle->name . '_posts_columns', array($this, 'managePostsColumnsFilter'), 12);
                add_filter('manage_edit-' . $field->Bundle->name . '_sortable_columns', array($this, 'manageEditSortableColumnsFilter'), 12);
                add_action('manage_posts_custom_column', array($this, 'managePostsCustomColumn'), 12, 2);
                add_filter('posts_clauses', array($this, 'postsClausesFilter'), 12, 2);
            }
        }
    }
    
    public function postsClausesFilter($clauses, $query)
    {
        global $pagenow, $wpdb;
        if ($pagenow !== 'edit.php') return $clauses;
        
        if (!empty($_GET['orderby']) && in_array($_GET['orderby'], array('sabai_voting_updown', 'sabai_voting_favorite', 'sabai_voting_flag', 'sabai_voting_rating'))) {
            if ($_GET['orderby'] !== 'sabai_voting_flag' || empty($sabai_voting_flag_joined)) {
                $clauses['join'] .= sprintf(' LEFT JOIN %1$ssabai_entity_field_%3$s %2$s ON %1$sposts.ID = %2$s.entity_id', $wpdb->prefix, $_GET['orderby'], substr($_GET['orderby'], 6));
            }
            $clauses['orderby'] = $_GET['orderby'] . '.count ' . ($_GET['order'] === 'asc' ? 'ASC' : 'DESC');
        }
        
        return $clauses;
    }
    
    public function managePostsCustomColumn($column, $post_id)
    {
        switch ($column) {
            case 'sabai_voting_updown':
                if (($entity = $this->_application->Entity_Entity('wppost', $post_id))
                    && ($vote_count = $entity->getSingleFieldValue('voting_updown', 'count'))
                ) {
                    echo $this->_application->LinkToModal(
                        sprintf('%d (%d)', $vote_count, ($vote_sum = $entity->getSingleFieldValue('voting_updown', 'sum'))),
                        $this->_application->AdminUrl($this->_application->Entity_Bundle($entity)->getAdminPath() . '/' . $entity->getId() . '/voting_updown'),
                        array(),
                        array('title' => sprintf(_n('%d vote (score: %d)', '%d votes (score: %d)', $vote_count, 'sabai'), $vote_count, $vote_sum), 'data-modal-title' => __('Votes', 'sabai'))
                    );
                } else {
                    echo '0 (0)';
                }
                break;
            case 'sabai_voting_favorite':
                if (($entity = $this->_application->Entity_Entity('wppost', $post_id))
                    && ($favorite_count = $entity->getSingleFieldValue('voting_favorite', 'count'))
                ) {
                    echo $this->_application->LinkToModal(
                        $favorite_count,
                        $this->_application->AdminUrl($this->_application->Entity_Bundle($entity)->getAdminPath() . '/' . $entity->getId() . '/voting_favorite'),
                        array(),
                        array('title' => sprintf(_n('%d favorite', '%d favorites', $favorite_count, 'sabai'), $favorite_count), 'data-modal-title' => __('Favorites', 'sabai'))
                    );
                } else {
                    echo 0;
                }
                break;
            case 'sabai_voting_flag':
                if (($entity = $this->_application->Entity_Entity('wppost', $post_id))
                    && ($flag_count = $entity->getSingleFieldValue('voting_flag', 'count'))
                ) {
                    echo $this->_application->LinkToModal(
                        sprintf('%d (%d)', $flag_count, ($flag_sum = $entity->getSingleFieldValue('voting_flag', 'sum'))),
                        $this->_application->AdminUrl($this->_application->Entity_Bundle($entity)->getAdminPath() . '/' . $entity->getId() . '/voting_flag'),
                        array(),
                        array('title' => sprintf(_n('%d flag (spam score: %d)', '%d flags (spam score: %d)', $flag_count, 'sabai'), $flag_count, $flag_sum), 'data-modal-title' => __('Flags', 'sabai'))
                    );
                } else {
                    echo '0 (0)';
                }
                break;
            case 'sabai_voting_rating':
                if (($entity = $this->_application->Entity_Entity('wppost', $post_id))
                    && ($rating_count = $entity->getSingleFieldValue('voting_rating', 'count', ''))
                ) {
                    echo $this->_application->LinkToModal(
                        sprintf('%d (%.2f)', $rating_count, ($rating_avg = $entity->getSingleFieldValue('voting_rating', 'average', ''))),
                        $this->_application->AdminUrl($this->_application->Entity_Bundle($entity)->getAdminPath() . '/' . $entity->getId() . '/voting_rating'),
                        array(),
                        array('title' => sprintf(__('%.2f out of 5 stars', 'sabai'), $rating_avg), 'data-modal-title' => __('Ratings', 'sabai'))
                    );
                } else {
                    echo 0;
                }
                break;
        }
    }
	
    public function managePostsColumnsFilter($columns)
    {
        global $typenow;
        if (!$bundle = $this->_application->Entity_Bundle($typenow)) return $columns;
        
        if (!empty($bundle->info['voting_updown'])) {
            $columns['sabai_voting_updown'] = '<i class="fa fa-thumbs-up" title="' . __('Votes', 'sabai') . '"></i>';
        }
        if (!empty($bundle->info['voting_favorite'])) {
            $columns['sabai_voting_favorite'] = '<i class="fa fa-bookmark" title="' . __('Bookmarks', 'sabai') . '"></i>';
        }
        if (!empty($bundle->info['voting_flag'])) {
            $columns['sabai_voting_flag'] = '<i class="fa fa-flag" title="' . __('Flags', 'sabai') . '"></i>';
        }
        if (!empty($bundle->info['voting_rating'])) {
            $columns['sabai_voting_rating'] = '<i class="fa fa-star" title="' . __('Ratings', 'sabai') . '"></i>';
        }
        // Move date column to last
        $date_label = $columns['date'];
        unset($columns['date']);
        $columns['date'] = $date_label;

        return $columns;
    }
    
    public function manageEditSortableColumnsFilter($columns)
    {
        global $typenow;
        if ((!$bundle = $this->_application->Entity_Bundle($typenow))) return $columns;
        
        if (!empty($bundle->info['voting_updown'])) {
            $columns['sabai_voting_updown'] = 'sabai_voting_updown';
        }
        if (!empty($bundle->info['voting_favorite'])) {
            $columns['sabai_voting_favorite'] = 'sabai_voting_favorite';
        }
        if (!empty($bundle->info['voting_flag'])) {
            $columns['sabai_voting_flag'] = 'sabai_voting_flag';
        }
        if (!empty($bundle->info['voting_rating'])) {
            $columns['sabai_voting_rating'] = 'sabai_voting_rating';
        }

        return $columns;
    }
}