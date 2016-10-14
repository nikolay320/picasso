<?php
class Sabai_Addon_Content_Controller_Admin_EditChildPost extends Sabai_Addon_Content_Controller_Admin_EditPost
{    
    protected function _getBundle(Sabai_Context $context)
    {
        return $context->child_bundle;
    }
}