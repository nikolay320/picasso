<?php
class Sabai_Addon_WordPress_Controller_Admin_Permalink extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        if (!$context->getRequest()->isPostMethod()
            || !$context->getRequest()->isAjax()
            || (!$entity_id = $context->getRequest()->asInt('post_id'))
            || (false === $slug = $context->getRequest()->asStr('new_slug', false))
            || !strlen($slug)
            || (!$entity_type = $context->getRequest()->asStr('entity_type', 'content', array('content', 'taxonomy')))
        ) {
            $context->setBadRequestError();
            return;
        }

        // Check request token
        if (!$this->_checkToken($context, 'wordpress_permalink', true, 'samplepermalinknonce')) return;
        
        if (!$entity = $this->Entity_Entity($entity_type, $entity_id, false)) {
            $context->setBadRequestError();
            return;
        }
        
        $slug_property = $entity_type === 'taxonomy' ? 'taxonomy_term_name' : 'content_post_slug';
        $context->entity = $this->Entity_Save($entity, array($slug_property => $slug));
        
        $context->addTemplate('wordpress_permalink');
    } 
}