<?php
class Sabai_Addon_Content_Controller_PreviewPost extends Sabai_Addon_Entity_Controller_ViewEntity
{    
    protected function _doExecute(Sabai_Context $context)
    {        
        parent::_doExecute($context);

        $context->clearTabs()
            ->addTemplate(isset($this->_template) ? $this->_template : $this->Entity_Bundle($context->entity)->type . '_single_preview');
    }
    
    protected function _getEntity(Sabai_Context $context)
    {
        return $context->entity;
    }
}