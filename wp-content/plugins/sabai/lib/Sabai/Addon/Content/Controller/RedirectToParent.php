<?php
class Sabai_Addon_Content_Controller_RedirectToParent extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        // Redirect to parent entities page
        $context->setRedirect($context->bundle->getPath(), Sabai_Context::REDIRECT_PERMANENT);
    }
}
