<?php
require_once dirname(__FILE__) . '/AllListings.php';
class Sabai_Addon_Directory_Controller_Map extends Sabai_Addon_Directory_Controller_AllListings
{    
    protected function _getCustomSettings(Sabai_Context $context)
    {
        $settings = array(
            'address' => isset($context->address) ? $context->address : '',
            'hide_searchbox' => true,
            'hide_nav' => true,
            'hide_pager' => true,
            'featured_only' => !empty($context->featured_only),
            'view' => 'map',
            'views' => array('map'),
        );
        if (isset($context->num)) {
            if ($context->num == 0) {
                $this->_paginate = false;
            } else {
                $settings['perpage'] = $context->num;
            }
        }
        if (isset($context->sort)) {
            $settings['sort'] = $context->sort;
        }
        if (isset($context->zoom)) {
            $settings['zoom'] = (int)$context->zoom;
        }
        if (isset($context->is_mile)) {
            $settings['is_mile'] = (bool)$context->is_mile;
        }
        return $settings;
    }
    
    protected function _getDefaultSettings(Sabai_Context $context)
    {
        // override map settings
        $settings = parent::_getDefaultSettings($context);
        $settings['map']['no_header'] = true;
        $settings['map']['list_show'] = false;
        if (isset($context->height)) {
            $settings['map']['height'] = $context->height;
        }
        if (isset($context->type)) {
            $settings['map']['type'] = $context->type;
        }
        if (isset($context->style)) {
            $settings['map']['style'] = $context->style;
        }
        if (isset($context->marker_clusters) && $context->marker_clusters == 0) {
            $settings['map']['options']['marker_clusters'] = false;
        }
        // Alwayd hide filters
        $settings['search']['no_filters'] = true;
        
        return $settings;
    }
}