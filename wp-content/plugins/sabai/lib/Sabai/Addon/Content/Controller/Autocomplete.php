<?php
class Sabai_Addon_Content_Controller_Autocomplete extends Sabai_Addon_Entity_Controller_Autocomplete
{
    protected function _getBundle(Sabai_Context $context)
    {
        return $context->child_bundle ? $context->child_bundle : $context->bundle;;
    }
}