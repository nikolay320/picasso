<?php
class Sabai_Addon_Directory_Controller_Listing extends Sabai_Controller
{ 
    protected function _doExecute(Sabai_Context $context)
    {
        switch ($context->view) {
            case 'summary':
                $template = 'directory_listing_single_summary';
                break;
            default:
                $template = 'directory_listing_single_full';
        }
        $context->addTemplate($template)->setAttributes(current($this->Entity_Render($context->entity)));
    }
}