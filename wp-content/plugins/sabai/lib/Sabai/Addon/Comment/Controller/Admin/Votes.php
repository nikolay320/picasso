<?php
class Sabai_Addon_Comment_Controller_Admin_Votes extends Sabai_Addon_Form_Controller
{
    protected $_tag = 'up';
    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        // Init form
        $form = array(
            'votes' => array(
                '#type' => 'tableselect',
                '#header' => $this->_getHeaders(),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
            )
        );
        // Set submit buttons
        $this->_submitButtons = $this->_getSubmitButtons($context);
        
        // Init variables
        $sortable_headers = array('value', 'created');
        $timestamp_headers = array('created');
        $sort = $context->getRequest()->asStr('sort', 'created', array_keys($sortable_headers));
        $order = $context->getRequest()->asStr('order', 'DESC', array('ASC', 'DESC'));
        
        // Set sortable headers
        $this->_makeTableSortable($context, $form['votes'], $sortable_headers, $timestamp_headers, $sort, $order);

        // Fetch votes
        $votes = $this->getModel('Vote')
            ->postId_is($context->comment->id)
            ->tag_is($this->_tag)
            ->fetch(0, 0, $sort, $order);
        
        // Add rows
        foreach ($votes as $vote) {            
            $form['votes']['#options'][$vote->id] = $this->_getVoteRow($context, $vote);
        }

        $form['sort'] = array(
            '#type' => 'hidden',
            '#value' => $sort,
        );
        $form['order'] = array(
            '#type' => 'hidden',
            '#value' => $order,
        );

        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        if (empty($form->values['votes'])) {
            $context->setSuccess($this->Url($context->getRoute()));
            return;
        }
        
        switch ($form->values['action']) {
            case 'delete':
                $this->getModel('Vote')->id_in($form->values['votes'])->tag_is($this->_tag)->fetch()->delete();
                $this->getModel()->commit();
                $context->comment->updateVoteStat($this->_tag);
                $context->comment->commit();
                break;
        }
        
        $context->setSuccess($this->Url($context->getRoute()));
    }
    
    protected function _getSubmitButtons(Sabai_Context $context)
    {
        return array(
            'action' => array(
                '#type' => 'select',
                '#options' => array(
                    '' => __('Bulk Actions', 'sabai'),
                    'delete' => __('Delete', 'sabai'),
                ),
            ),
            'apply' => array(
                '#value' => __('Apply', 'sabai'),
            ),
        );
    }
    
    protected function _getHeaders()
    {
        return array(
            'author' => __('User', 'sabai'),
            'created' => __('Voted at', 'sabai'),
            'value' => __('Score', 'sabai'),
        );
    }
    
    protected function _getVoteRow(Sabai_Context $context, Sabai_Addon_Comment_Model_Vote $vote)
    {
        return array(
            'created' => $this->getPlatform()->getHumanTimeDiff($vote->created),
            'author' => $this->UserIdentityLinkWithThumbnailSmall($vote->User),
            'value' => $vote->value,
        );
    }
}