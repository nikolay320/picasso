<?php
class Sabai_Addon_Taxonomy_Controller_Autocomplete extends Sabai_Addon_Entity_Controller_Autocomplete
{
    protected $_template = 'taxonomy_autocomplete';
    
    protected function _getBundle(Sabai_Context $context)
    {
        return $context->taxonomy_bundle;
    }
}