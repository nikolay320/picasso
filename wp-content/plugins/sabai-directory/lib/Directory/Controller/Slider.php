<?php
require_once dirname(__FILE__) . '/AllListings.php';
class Sabai_Addon_Directory_Controller_Slider extends Sabai_Addon_Directory_Controller_AllListings
{
    protected $_template = 'directory_listings_slider', $_perPage = 10;
    
    protected function _getCustomSettings(Sabai_Context $context)
    {
        $settings = array(
            'hide_nav' => !empty($context->hide_nav),
            'featured_only' => !empty($context->featured_only),
            'carousel' => array(
                'pause' => isset($context->slider_speed) ? (int)$context->slider_speed : 4000,
                'controls' => isset($context->slider_controls) ? (bool)$context->slider_controls : true,
                'mode' => isset($context->slider_mode) && in_array($context->slider_mode, array('vertical', 'fade')) ? $context->slider_mode : 'horizontal',
                'auto' => !isset($context->slider_auto) || !empty($context->slider_auto),
                'pager' => !isset($context->slider_pager) || !empty($context->slider_pager),
            ),
        );
        if (!empty($context->photo_only)) {
            $settings['photo_only'] = true;
            $settings['carousel']['captions'] = !isset($context->slider_captions) || !empty($context->slider_captions);
            $settings['carousel']['slideMargin'] = isset($context->slider_slide_margin) ? (int)$context->slider_slide_margin : 10;
            // Set defaults by photo size
            switch ($settings['photo_size'] = @$context->photo_size) {
                case 'medium':
                    $settings['carousel']['slideWidth'] = $this->getAddon('File')->getConfig('image_medium_width');
                    $settings['carousel']['maxSlides'] = 3;
                    break;
                case 'thumbnail':
                    $settings['carousel']['slideWidth'] = $this->getAddon('File')->getConfig('thumbnail_width');
                    $context->slider_slide_height = $this->getAddon('File')->getConfig('thumbnail_height');
                    $settings['carousel']['minSlides'] = 2;
                    $settings['carousel']['maxSlides'] = 10;
                    break;
                case 'original':
                    break;
                default:
                    $settings['carousel']['slideWidth'] = $this->getAddon('File')->getConfig('image_large_width');
                    $settings['photo_size'] = 'large';
            }
            // Allow override
            if (isset($context->slider_slide_width)) {
                $settings['carousel']['slideWidth'] = (int)$context->slider_slide_width;
            }
            if (isset($context->slider_min_slides)) {
                $settings['carousel']['minSlides'] = (int)$context->slider_min_slides;
            }
            if (isset($context->slider_max_slides)) {
                $settings['carousel']['maxSlides'] = (int)$context->slider_max_slides;
            }
        }
        
        // Add CSS
        $css = $viewport_css = array();
        if (isset($context->slider_height)) {
            $viewport_css[] = 'height: ' . (int)$context->slider_height . 'px !important;';
        }
        if (isset($context->slider_slide_height)) {
            $css[] = sprintf('%s .sabai-item {height: %dpx}', $context->getContainer(), $context->slider_slide_height);
        }
        if (!empty($context->slider_border)) {
            $viewport_css[] = '-moz-box-shadow: 0 0 5px #ccc; -webkit-box-shadow: 0 0 5px #ccc; box-shadow: 0 0 5px #ccc; border: 5px solid #fff;';
        }
        if (!empty($viewport_css)) {
            $css[] = sprintf('%s .bx-viewport {%s}', $context->getContainer(), implode(' ', $viewport_css));
        }
        if (!empty($css)) {
            $this->getPlatform()->addCss(implode(PHP_EOL, $css), $context->getContainer() . '-sabai-directory-slider');
        }
        
        return parent::_getCustomSettings($context) + $settings;
    }
    
    protected function _createListingsQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $query = parent::_createListingsQuery($context, $bundle);
        if (!empty($context->with_photo_only)) {
            $query->fieldIs('content_children_count', 'directory_listing_photo', 'child_bundle_name')
                ->fieldIsGreaterThan('content_children_count', 0);
        }
        return $query;
    }
}