<?php
class Sabai_Addon_Taxonomy_Controller_TermFeed extends Sabai_Addon_Content_Controller_Feed
{    
    protected function _createQuery(Sabai_Context $context)
    {
        return parent::_createQuery($context)->fieldIs($context->taxonomy_bundle->type, $context->entity->getId());
    }
    
    protected function _getTitle(Sabai_Context $context)
    {
        return sprintf(__('Recent posts tagged %s', 'sabai'), $context->entity->getTitle());
    }
        
    protected function _getDescription(Sabai_Context $context)
    {
        return sprintf(
            __('The most recent %d posts tagged %s', 'sabai'),
            $this->_numItems,
            $context->entity->getTitle()
        );
    }
}