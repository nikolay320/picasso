<?php
class Sabai_Addon_Voting_Controller_Admin_Updown extends Sabai_Addon_Form_Controller
{
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
        
        // Submit via ajax if in modal window
        if ($context->getContainer() === '#sabai-modal') {
            $this->_ajaxOnSuccess = sprintf(
                'function (result, target, trigger) {target.find(".sabai-modal-content").slideUp(); SABAI.replace("#sabai-modal .sabai-modal-content", "%s", function(target){target.slideDown();}, true);}',
                $this->Url($context->getRoute())
            );
        }
        
        // Init variables
        $sortable_headers = $this->_getSortableHeaders();
        $timestamp_headers = $this->_getTimestampHeaders();
        $sort = $context->getRequest()->asStr('sort', $this->_getDefaultHeader(), array_keys($sortable_headers));
        $order = $context->getRequest()->asStr('order', 'DESC', array('ASC', 'DESC'));
        
        // Set sortable headers
        $this->_makeTableSortable($context, $form['votes'], $sortable_headers, $timestamp_headers, $sort, $order);

        // Fetch votes
        $votes = $this->getModel('Vote', 'Voting')
            ->entityId_is($context->entity->getId())
            ->tag_is($context->voting_tag)
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
        
        switch ($form->values['_action']) {
            case 'delete':
                $this->getModel('Vote', 'Voting')->id_in($form->values['votes'])->tag_is($context->voting_tag)->fetch()->delete();
                $this->getModel(null, 'Voting')->commit();
                $this->getAddon('Voting')->recalculateEntityVotes($context->entity, $context->voting_tag);
                break;
        }
        
        $context->setSuccess($this->Url($context->getRoute()));
    }
    
    protected function _getSubmitButtons(Sabai_Context $context)
    {
        return array(
            '_action' => array(
                '#type' => 'select',
                '#options' => array(
                    '' => __('Bulk Actions', 'sabai'),
                    'delete' => __('Delete', 'sabai'),
                ),
                '#weight' => 1,
            ),
            'apply' => array(
                '#value' => __('Apply', 'sabai'),
                '#btn_size' => 'small',
                '#weight' => 10,
            ),
        );
    }
    
    protected function _getHeaders()
    {
        return array(
            'author' => __('User', 'sabai'),
            'ip' => __('IP address', 'sabai'),
            'created' => __('Date', 'sabai'),
            'value' => __('Score', 'sabai'),
        );
    }
    
    protected function _getSortableHeaders()
    {
        return array('value', 'created');
    }
    
    protected function _getDefaultHeader()
    {
        return 'created';
    }
    
    protected function _getTimestampHeaders()
    {
        return array('created');
    }
    
    protected function _getVoteRow(Sabai_Context $context, Sabai_Addon_Voting_Model_Vote $vote)
    {
        return array(
            'created' => $this->getPlatform()->getHumanTimeDiff($vote->created),
            'author' => $this->UserIdentityLinkWithThumbnailSmall($vote->User),
            'value' => $vote->value,
            'ip' => $vote->ip,
        );
    }
}