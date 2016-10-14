<?php
class Sabai_Addon_Taxonomy_Controller_Admin_ViewTerm extends Sabai_Controller
{    
    protected function _doExecute(Sabai_Context $context)
    {
        $url = $this->Entity_Url($context->entity);
        $context->setRedirect($url);
    }
}