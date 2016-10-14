<?php
require_once dirname(__FILE__) . '/Feed.php';

class Sabai_Addon_Questions_Controller_UserFeed extends Sabai_Addon_Questions_Controller_Feed
{    
    protected function _createQuery(Sabai_Context $context)
    {
        return parent::_createQuery($context)->propertyIs('post_user_id', $context->identity->id);
    }
    
    protected function _getTitle(Sabai_Context $context)
    {
        return sprintf(
            __('Recent posts by %s', 'sabai-discuss'),
            $context->identity->name
        );
    }
        
    protected function _getDescription(Sabai_Context $context)
    {
        return sprintf(
            __('The most recent %d questions and answers by %s', 'sabai-discuss'),
            $this->_numItems,
            $context->identity->name
        );
    }
}