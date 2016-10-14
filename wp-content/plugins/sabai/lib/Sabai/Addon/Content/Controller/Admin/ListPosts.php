<?php
class Sabai_Addon_Content_Controller_Admin_ListPosts extends Sabai_Addon_Form_Controller
{   
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        // Init variables
        $bundle = $this->_getBundle($context);
        $statuses = $this->Content_StatusOptions($bundle);
        $status = $context->getRequest()->asStr('status', '', array_keys($statuses));
        $default_sortable_headers = array('title' => 'post_title', 'published' => 'post_published', 'author' => 'post_user_id');
        $sortable_headers = $this->Filter('content_admin_posts_sortable_headers', array_keys($default_sortable_headers));
        $sort = $context->getRequest()->asStr('sort', 'published', $sortable_headers);
        $order = $context->getRequest()->asStr('order', 'DESC', array('ASC', 'DESC'));
        $content_keywords = $context->getRequest()->asStr('content_keywords', '');
        $limit = $context->getRequest()->asInt('limit', 20, $this->Filter('content_admin_posts_limit', array(20, 30, 50, 100, 200, 500)));
        $url_params = $this->Filter(
            'content_admin_posts_url_params',
            array('status' => $status, 'sort' => $sort, 'order' => $order, 'limit' => $limit, 'content_keywords' => $content_keywords),
            array($context, $bundle)
        );
        
        // Init form
        $form = array(
            'entities' => array(
                '#type' => 'tableselect',
                '#header' => array(
                    'title' => array('order' => 1, 'label' => __('Title', 'sabai')),
                    'author' => array('order' => 2, 'label' => __('Author', 'sabai')),
                    'views' => array('order' => 20, 'label' => '<i title="'. __('Views', 'sabai') .'" class="fa fa-lg fa-eye"></i>'),
                    'published' => array('order' => 50, 'label' => __('Date', 'sabai')),
                ),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
            ),
            '#bundle' => $bundle,
            '#status' => $status,
            '#filters' => array(),
        );
        
        // Set submit buttons
        $this->_submitButtons = $this->_getSubmitButtons($context, $status);

        // Set sortable headers
        $this->_makeTableSortable($context, $form['entities'], $sortable_headers, array('published'), $sort, $order, $url_params);

        // Init queries
        $query = $this->Entity_Query('content')
            ->propertyIs('post_entity_bundle_name', $bundle->name);
        $count_query = $this->Entity_Query('content')
            ->propertyIs('post_entity_bundle_name', $bundle->name)
            ->groupByProperty('post_status');
        
        // Showing posts for a specific child bundle?
        if ($context->child_bundle
            && ($parent_post_id = $context->getRequest()->asInt('content_parent'))
            && ($parent_post = $this->Entity_Entity('content', $parent_post_id, false))
            && $parent_post->getBundleName() === $context->bundle->name
        ) {
            $url_params['content_parent'] = $parent_post_id;
            $context->setInfo(sprintf(
                __('%s posted for %s: %s', 'sabai'),
                $this->Entity_BundleLabel($context->child_bundle, false),
                $this->Entity_BundleLabel($this->Entity_Bundle($parent_post), true),
                $parent_post->getTitle()
            ));
            $query->fieldIs('content_parent', $parent_post->getId());
            $count_query->fieldIs('content_parent', $parent_post->getId());
        // Showing posts referencing a specific post?
        } elseif (($reference_post_id = $context->getRequest()->asInt('content_reference'))
            && ($reference_post = $this->Entity_Entity('content', $reference_post_id, false))
        ) {
            $url_params['content_reference'] = $reference_post_id;
            $context->setInfo(sprintf(
                __('%s posted for %s: %s', 'sabai'),
                $this->Entity_BundleLabel($bundle, false),
                $this->Entity_BundleLabel($this->Entity_Bundle($reference_post), true),
                $reference_post->getTitle()
            ));
            $query->fieldIs('content_reference', $reference_post_id);
            $count_query->fieldIs('content_reference', $reference_post_id);
        }
        
        // Filter by status
        if ($status) {
            $query->propertyIs('post_status', $status);
        } else {
            $query->propertyIsNot('post_status', Sabai_Addon_Content::POST_STATUS_TRASHED);
        }
        
        // Filter by keywords
        if ($content_keywords) {
            $keywords = $this->Keywords($content_keywords);
            if (!empty($keywords[0])) {
                foreach ($keywords[0] as $keyword) {
                    $query->startCriteriaGroup('OR')
                        ->fieldContains('content_body', $keyword)
                        ->propertyContains('post_title', $keyword)
                        ->finishCriteriaGroup();
                }
            }
        }
        
        // Sort query
        if (isset($default_sortable_headers[$sort])) {
            $query->sortByProperty($default_sortable_headers[$sort], $order);
        }
        
        // Allow add-ons to filter query
        $this->Action('content_admin_posts_query', array($context, $bundle, $query, $count_query, $sort, $order));
        
        // Query with pagination
        $pager = $query->paginate($limit)->setCurrentPage($url_params[Sabai::$p] = $context->getRequest()->asInt(Sabai::$p, 1));
        
        // Add rows
        $filter_name_prefix = 'content_admin_posts_' . $bundle->type;
        $previewable = !empty($bundle->info['content_previewable']);
        foreach ($pager->getElements() as $entity) {
            $entity_path = $bundle->getAdminPath() . '/' . $entity->getId();
            $entity_title = $entity->getTitle();
            if (!strlen($entity_title)) {
                $entity_title = __('(no title)', 'sabai');
            } else {
                $entity_title = mb_strimwidth($entity_title, 0, 200, '...');
            }
            $links = array();
            if ($status === Sabai_Addon_Content::POST_STATUS_TRASHED) {
                $title = Sabai::h($entity_title);
                // Allow restore only if this post does not have a trashed parent post
                if (!$entity->getSingleFieldValue('content_trashed', 'parent_entity_id')) {
                    $links[] = $this->LinkToModal(__('Restore', 'sabai'), $this->Url($entity_path . '/restore', $url_params), array('width' => 470), array('title' => __('Restore this Post', 'sabai')));
                }
                $links[] = $this->LinkToModal(__('Delete Permanently', 'sabai'), $this->Url($entity_path . '/delete', $url_params), array('width' => 470), array('title' => __('Delete this Post', 'sabai')));
            } else {
                $title = $this->LinkTo($entity_title, $this->Url($entity_path));
                $links['edit'] = $this->LinkTo(__('Edit', 'sabai'), $this->Url($entity_path));
                $links['trash'] = $this->LinkToModal(__('Trash', 'sabai'), $this->Url($entity_path . '/trash', $url_params), array('width' => 470), array('title' => __('Trash this Post', 'sabai')));
                if ($entity->isPending()) {
                    if ($previewable) {
                        $links['preview'] = $this->LinkTo(__('Preview', 'sabai'), $this->Content_PreviewUrl($entity));
                    }
                } elseif ($entity->isDraft()) {
                    if ($previewable) {
                        $links['preview'] = $this->LinkTo(__('Preview', 'sabai'), $this->Content_PreviewUrl($entity));
                    }
                } else {
                    if (!isset($bundle->info['public']) || $bundle->info['public'] !== false) {
                        $links['view'] = $this->LinkTo(__('View', 'sabai'), $this->Entity_Url($entity));
                    }
                }
            }
            $links = $this->Filter($filter_name_prefix . '_links', $links, array($entity, $status));
            $label = '';
            if ((!$status && $entity->getStatus() !== Sabai_Addon_Content::POST_STATUS_PUBLISHED)
                || $status === Sabai_Addon_Content::POST_STATUS_TRASHED
            ) {
                $label = $this->Content_StatusLabel($entity);
            }
            $form['entities']['#options'][$entity->getId()] = array(
                'title' => $label . '<strong class="sabai-row-title">' . $title . '</strong> (ID: ' . $entity->getId() . ')<div class="sabai-row-action">' . $this->Menu($links) . '</div>',
                'published' => $this->getPlatform()->getHumanTimeDiff($entity->getTimestamp()),
                'author' => $this->UserIdentityLink($this->Entity_Author($entity)),
                'views' => $entity->getViews(),
                '#entity' => $entity,
                '#links' => $links,
            );
        }
        
        // Pass required url parameters as hidden values
        foreach ($url_params as $url_param_k => $url_param_v) {
            $form[$url_param_k] = array('#type' => 'hidden', '#value' => $url_param_v);
        }
        
        // Get count by status for status labels       
        $count = $count_query->count();
        $all_count = 0;
        $buttons = array();
        foreach ($statuses as $status_name => $status_title) {
            if (!empty($count[$status_name])) {
                $buttons[$status_name] = sprintf(__('%s (%d)', 'sabai'), $status_title, $count[$status_name]);
                if ($status_name !== Sabai_Addon_Content::POST_STATUS_TRASHED) {
                    $all_count += $count[$status_name];
                }
            }
        }
        $buttons[''] = sprintf(__('%s (%d)', 'sabai'), __('All', 'sabai'), $all_count);
        ksort($buttons);
        
        // Set template
        $context->addTemplate('content_admin_posts')
            ->setAttributes(array(
                'buttons' => $buttons,
                'status' => $status, 
                'current_bundle' => $bundle,
                'url_params' => $url_params,
                'pager' => $pager,
                'links' => $this->Filter('content_admin_posts_links', $this->_getLinks($context), array($this->_getBundle($context))),
            ));

        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if ($form->getClickedButtonName() === 'empty') {
            $trashed_posts = $this->_application->Entity_Query('content')
                ->propertyIs('post_entity_bundle_name', $this->_getBundle($context)->name)
                ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_TRASHED)
                ->fetch(); 
            $this->Content_DeletePosts($trashed_posts);
        } else {
            if (!empty($form->values['entities'])) {
                switch ($form->values['action']) {
                    case 'publish':
                        $this->Content_PublishPosts($form->values['entities']);
                        break;
                    case 'trash':
                        $this->Content_TrashPosts($form->values['entities'], Sabai_Addon_Content::TRASH_TYPE_OTHER, 'Administration');
                        break;
                    case 'restore':
                        $this->Content_RestorePosts($form->values['entities']);
                        break;
                    case 'delete':
                        $this->Content_DeletePosts($form->values['entities']);
                        break;
                }
            }
        }
        
        if (!$context->isError()) {
            $context->setSuccess($this->Url($context->getRoute(), $context->url_params));
        }
    }
    
    protected function _getSubmitButtons(Sabai_Context $context, $status)
    {
        $options = array(
            'action' => array(
                '#type' => 'select',
                '#options' => array(
                    '' => __('Bulk Actions', 'sabai'),
                ),
                '#weight' => 1,
            ),
            'apply' => array(
                '#value' => __('Apply', 'sabai'),
                '#btn_size' => 'sm',
                '#weight' => 10,
            ),
        );    
        switch ($status) {
            case 'published':
                $options['action']['#options'] += array(
                    'trash' => __('Move to Trash', 'sabai'),
                );
                break;
            case 'pending':
                $options['action']['#options'] += array(
                    'publish' => __('Publish', 'sabai'),
                    'trash' => __('Move to Trash', 'sabai'),
                );
                break;
            case 'trashed':
                $options['action']['#options'] += array(
                    'restore' => __('Restore', 'sabai'),
                    'delete' => __('Delete Permanently', 'sabai'),
                );
                $options['empty'] = array(
                    '#value' => __('Empty Trash', 'sabai'),
                    '#btn_size' => 'sm',
                    '#btn_type' => 'danger',
                    '#weight' => 50,
                );
                break;
            default:
                $options['action']['#options'] += array(
                    'publish' => __('Publish', 'sabai'),
                    'trash' => __('Move to Trash', 'sabai'),
                );
        }
        return $options;
    }
    
    /**
     * @return Sabai_Addon_Model_Bundle 
     */
    protected function _getBundle(Sabai_Context $context)
    {
        return $context->bundle;
    }
    
    protected function _getLinks(Sabai_Context $context)
    {
        $bundle = $this->_getBundle($context);
        return array(
			$this->LinkTo(
                sprintf(__('Add %s', 'sabai'), $this->Entity_BundleLabel($bundle, true)),
                $this->Url($bundle->getAdminPath() . '/add'),
                array('icon' => 'plus'),
                array('class' => 'sabai-btn sabai-btn-primary sabai-btn-sm')
            ),
        );
    }
}