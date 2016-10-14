<?php
class Sabai_Addon_Comment_Controller_Admin_ListComments extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        // Init form
        $form = array(
            'comments' => array(
                '#type' => 'tableselect',
                '#header' => array(
                    'comment' => __('Comment', 'sabai'),
                    'vote_count' => __('Votes', 'sabai'),
                    'flag_count' => __('Flags', 'sabai'),
                    'author' => __('Author', 'sabai'),
                    'published_at' => __('Date', 'sabai'),
                    'links' => '',
                ),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
            ),
        );
        // Set submit buttons
        $this->_submitButtons = $this->_getSubmitButtons($context);
        
        // Init variables
        $filters = array('all', 'published', 'hidden');
        $filter = $context->getRequest()->asStr('filter', 'all', $filters);
        $sortable_headers = array('vote_count', 'flag_count', 'published_at');
        $sort = $context->getRequest()->asStr('sort', 'published_at', array_keys($sortable_headers));
        $order = $context->getRequest()->asStr('order', 'DESC', array('ASC', 'DESC'));
        $url_params = array('filter' => $filter, 'sort' => $sort, 'order' => $order);
        
        // Set sortable headers
        $this->_makeTableSortable($context, $form['comments'], $sortable_headers, array('published_at'), $sort, $order, $url_params);

        // Init criteria
        $criteria = $this->getModel()->createCriteria('Post')
            ->entityId_is($context->entity->getId());
        
        // Filter query
        switch ($filter) {
            case 'hidden':
                $criteria->status_is(Sabai_Addon_Comment::POST_STATUS_HIDDEN);
                break;
            case 'published':
                $criteria->status_isNot(Sabai_Addon_Comment::POST_STATUS_HIDDEN);
        }
        
        // Query with pagination
        $pager = $this->getModel('Post')
            ->paginateByCriteria($criteria, 20, $sort, $order)
            ->setCurrentPage($url_params[Sabai::$p] = $context->getRequest()->asInt(Sabai::$p, 1));
        
        // Add rows
        foreach ($pager->getElements()->with('User') as $comment) {
            $comment_path = $context->getRoute() . $comment->id;
            $links = array(
                $this->LinkTo(__('Edit', 'sabai'), $this->Url($comment_path)),
                $this->LinkTo(sprintf(__('View %s', 'sabai'), $this->Entity_BundleLabel($context->entity)), $this->Entity_Url($context->entity)),
            );
            $form['comments']['#options'][$comment->id] = array(
                'comment' => $this->Summarize($comment->body_html, 200),
                'published_at' => $this->getPlatform()->getHumanTimeDiff($comment->published_at),
                'author' => $this->UserIdentityLinkWithThumbnailSmall($comment->User),
                'vote_count' => $comment->vote_count
                    ? $this->LinkToModal(
                          $comment->vote_count,
                          $this->Url($comment_path . '/votes'),
                          array('width' => 470),
                          array('title' => sprintf(_n('%d vote', '%d votes', $comment->vote_count, 'sabai'), $comment->vote_count))
                      )
                    : 0,
                'flag_count' => $comment->flag_count
                    ? $this->LinkToModal(
                          sprintf('%d (%d)', $comment->flag_count, $comment->flag_sum),
                          $this->Url($comment_path . '/flags'),
                          array('width' => 470),
                          array('title' => sprintf(_n('%d flag (spam score: %d)', '%d flags (spam score: %d)', $comment->flag_count, 'sabai'), $comment->flag_count, $comment->flag_sum))
                      )
                    : 0,
                'links' => $this->Menu($links),
                '#comment' => $comment,
            );
            if ($comment->isHidden()) {
                $form['comments']['#row_attributes'][$comment->id]['@row']['class'] = 'sabai-active';
            }
        }
        
        foreach ($url_params as $url_param_k => $url_param_v) {
            $form[$url_param_k] = array('#type' => 'hidden', '#value' => $url_param_v);
        }
        
        // Get count by status for filter labels       
        $count = $this->getModel()->getGateway('Post')->getCountByStatus($context->entity->getId());
        $all_count = array_sum($count);
        $hidden_count = isset($count[Sabai_Addon_Comment::POST_STATUS_HIDDEN]) ? $count[Sabai_Addon_Comment::POST_STATUS_HIDDEN] : 0;
        $published_count = $all_count - $hidden_count;
        
        // Set template
        $context->addTemplate('comment_admin_comments')
            ->setAttributes(array(
                'filters' => array(
                    'all' => $all_count ? sprintf(__('All (%d)', 'sabai'), $all_count) : __('All', 'sabai'),
                    'published' => $published_count ? sprintf(__('Published (%d)', 'sabai'), $published_count) : __('Published', 'sabai'),
                    'hidden' => $hidden_count ? sprintf(__('Hidden (%d)', 'sabai'), $hidden_count) : __('Hidden', 'sabai'),
                ),
                'filter' => $filter, 
                'url_params' => $url_params,
                'pager' => $pager,
            ));

        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (!empty($form->values['comments'])) {
            switch ($form->values['action']) {
                case 'publish':
                    $this->_updateStatus($context, $form->values['comments'], Sabai_Addon_Comment::POST_STATUS_PUBLISHED);
                    break;
                case 'hide':
                    $this->_updateStatus($context, $form->values['comments'], Sabai_Addon_Comment::POST_STATUS_HIDDEN);
                    break;
                case 'delete':
                    $this->_delete($context, $form->values['comments']);
                    break;
            }
        }
        
        $context->setSuccessUrl($this->Url($context->getRoute(), array('filter' => $context->filter, 'sort' => $form->values['sort'], 'order' => $form->values['order'])));
    }
    
    protected function _updateStatus(Sabai_Context $context, $ids, $status)
    {
        $comments = $this->getModel('Post')->fetchByIds($ids);
        foreach ($comments as $comment) {
            if ($comment->status == $status) {
                continue; // no status change
            }
            $comment->status = $status;
        }
        $this->getModel()->commit();
        // Notify
        foreach ($comments as $comment) {
            $this->Action('comment_delete_comment_success', array($comment));
        }
    }
    
    protected function _delete(Sabai_Context $context, $ids)
    {
        foreach ($this->getModel('Post')->fetchByIds($ids) as $comment) {
            $comment->markRemoved();
        }
        $this->getModel()->commit();
    }
    
    protected function _getSubmitButtons(Sabai_Context $context)
    {
        return array(
            'action' => array(
                '#type' => 'select',
                '#options' => array(
                    '' => __('Bulk Actions', 'sabai'),
                    'publish' => __('Mark as published', 'sabai'),
                    'hide' => __('Mark as hidden', 'sabai'),
                    'delete' => __('Delete', 'sabai'),
                ),
            ),
            'apply' => array(
                '#value' => __('Apply', 'sabai'),
            ),
        );
    }
}