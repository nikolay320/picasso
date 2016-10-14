<?php
class Sabai_Addon_WordPress_Controller_Admin_Pages extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        $context->addTemplate('wordpress_pages');
        $context->parent = $context->getRequest()->asInt('value');
    }
}