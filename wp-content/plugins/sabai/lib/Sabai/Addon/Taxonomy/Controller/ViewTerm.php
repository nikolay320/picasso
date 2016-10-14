<?php
class Sabai_Addon_Taxonomy_Controller_ViewTerm extends Sabai_Addon_Entity_Controller_ViewEntity
{    
    protected function _getEntity(Sabai_Context $context)
    {
        return $context->entity;
    }
}