<?php
class Sabai_Addon_Directory_Controller_Admin_MapSettings extends Sabai_Addon_System_Controller_Admin_Settings
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $config = $this->getAddon()->getConfig('map');
        return array(
            'disable' => array(
                '#type' => 'checkbox',
                '#default_value' => $this->isAddonLoaded('GoogleMaps') ? !empty($config['disable']) : false,
                '#title' => __('Disable map', 'sabai-directory'),
                '#disabled' => !$this->isAddonLoaded('GoogleMaps'),
            ),
            'basic' => array(
                '#title' => __('Basic Settings', 'sabai-directory'),
                '#collapsed' => false,
                '#states' => array(
                    'visible' => array(
                        'input[name="disable[]"]' => array('type' => 'checked', 'value' => false),
                    ), 
                ),
                'type' => array(
                    '#type' => 'select',
                    '#title' => __('Default map type', 'sabai-directory'),
                    '#options' => $this->GoogleMaps_Types(),
                    '#default_value' => $config['type'],
                ),
                'height' => array(
                    '#type' => 'number',
                    '#title' => __('Map height', 'sabai-directory'),
                    '#default_value' => $config['height'],
                    '#size' => 5,
                    '#integer' => true,
                    '#required' => array($this, 'isFieldRequired'),
                    '#field_suffix' => 'px',
                    '#display_unrequired' => true,
                ),
                'style' => array(
                    '#type' => 'select',
                    '#options' => array('' => __('Default style', 'sabai-directory')) + $this->GoogleMaps_Style(),
                    '#title' => __('Google map style', 'sabai-directory'),
                    '#default_value' => $config['style'],
                ),
                'distance_mode' => array(
                    '#type' => 'radios',
                    '#title' => __('Distance mode', 'sabai-directory'),
                    '#options' => array('km' => __('Kilometers', 'sabai-directory'), 'mil' => __('Miles', 'sabai-directory')),
                    '#default_value' => isset($config['distance_mode']) ? $config['distance_mode'] : 'km',
                    '#class' => 'sabai-form-inline',
                ),
                'options' => array(
                    '#tree' => true,
                    'default_lat' => array(
                        '#type' => 'textfield',
                        '#size' => 15,
                        '#maxlength' => 9,
                        '#title' => __('Default latitude', 'sabai-directory'),
                        '#default_value' => isset($config['options']['default_lat']) ? $config['options']['default_lat'] : 40.69847,
                        '#regex' => '/^-?([1-8]?[1-9]|[1-9]?0)\.{1}\d{1,5}/',
                        '#numeric' => true,
                        '#required' => array($this, 'isFieldRequired'),
                        '#display_unrequired' => true,
                    ),
                    'default_lng' => array(
                        '#type' => 'textfield',
                        '#size' => 15,
                        '#maxlength' => 10,
                        '#title' => __('Default longitude', 'sabai-directory'),
                        '#default_value' => isset($config['options']['default_lng']) ? $config['options']['default_lng'] : -73.95144,
                        '#regex' => '/^-?((([1]?[0-7][0-9]|[1-9]?[0-9])\.{1}\d{1,6}$)|[1]?[1-8][0]\.{1}0{1,6}$)/',
                        '#numeric' => true,
                        '#required' => array($this, 'isFieldRequired'),
                        '#display_unrequired' => true,
                    ),
                    'scrollwheel' => array(
                        '#type' => 'yesno',
                        '#default_value' => !empty($config['options']['scrollwheel']),
                        '#title' => __('Enable scrollwheel zooming on the map', 'sabai-directory'),
                    ),
                    'infobox_width' => array(
                        '#type' => 'number',
                        '#size' => 4,
                        '#integer' => true,
                        '#field_suffix' => 'px',
                        '#min_value' => 1,
                        '#default_value' => isset($config['options']['infobox_width']) ? $config['options']['infobox_width'] : 250,
                        '#title' => __('Map infobox width', 'sabai-directory'),
                    ),
                ),
            ),
            'list_view' => array(
                '#title' => __('List View Settings', 'sabai-directory'),
                '#collapsed' => false,
                '#states' => array(
                    'visible' => array(
                        'input[name="disable[]"]' => array('type' => 'checked', 'value' => false),
                    ), 
                ),
                'list_show' => array(
                    '#type' => 'yesno',
                    '#title' => __('Display small map', 'sabai-directory'),
                    '#default_value' => $config['list_show'],
                ),
                'list_scroll' => array(
                    '#type' => 'yesno',
                    '#title' => __('Show a scroll bar when there are many listings to display', 'sabai-directory'),
                    '#default_value' => !empty($config['list_scroll']),
                    '#states' => array(
                        'visible' => array(
                            'input[name="list_show"]' => array('value' => 1),
                        ), 
                    ),
                ),
                'list_height' => array(
                    '#type' => 'number',
                    '#title' => __('Map height', 'sabai-directory'),
                    '#default_value' => $config['list_height'],
                    '#size' => 5,
                    '#integer' => true,
                    '#required' => array($this, 'isListHeightFieldRequired'),
                    '#field_suffix' => 'px',
                    '#states' => array(
                        'visible' => array(
                            'input[name="list_show"]' => array('value' => 1),
                        ), 
                    ),
                    '#display_unrequired' => true,
                ),
                'span' => array(
                    '#type' => 'radios',
                    '#title' => __('Map width', 'sabai-directory'),
                    '#class' => 'sabai-form-inline',
                    '#description' => __('The horizontal display ratio (12 being 100% wide) of the map in List view', 'sabai-directory'),
                    '#options' => array(4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8),
                    '#default_value' => isset($config['span']) ? $config['span'] : 5,
                    '#states' => array(
                        'visible' => array(
                            'input[name="list_show"]' => array('value' => 1),
                        ), 
                    ),
                ),
                'list_infobox' => array(
                    '#type' => 'radios',
                    '#title' => __('Map infobox', 'sabai-directory'),
                    '#options' => array(
                        'hover' => __('Display map infobox on the map when hover overing listing titles or clicking map markers'),
                        'marker' => __('Display map infobox on the map when clicking map markers'),
                        '' => __('Never display map infobox on the map'),
                    ),
                    '#default_value' => isset($config['list_infobox']) ? $config['list_infobox'] : 'hover',
                    '#states' => array(
                        'visible' => array(
                            'input[name="list_show"]' => array('value' => 1),
                        ), 
                    ),
                ),
            ),
            'marker' => array(
                '#title' => __('Map Marker Settings', 'sabai-directory'),
                '#states' => array(
                    'visible' => array(
                        'input[name="disable[]"]' => array('type' => 'checked', 'value' => false),
                    ), 
                ),
                '_icon' => array(
                    'icon' => array(
                        '#type' => 'url',
                        '#title' => __('Custom marker icon URL', 'sabai-directory'),
                        '#default_value' => $config['icon'],
                    ),
                    '_options' => array(
                        '#class' => 'sabai-form-inline',
                        '#collapsible' => false,
                        '#title' => __('Custom marker icon size', 'sabai-directory'),
                        '#tree' => true,
                        'marker_width' => array(
                            '#type' => 'number',
                            '#size' => 4,
                            '#integer' => true,
                            '#field_suffix' => 'x',
                            '#default_value' => isset($config['options']['marker_width']) ? $config['options']['marker_width'] : 0,
                        ),
                        'marker_height' => array(
                            '#type' => 'number',
                            '#size' => 4,
                            '#integer' => true,
                            '#field_suffix' => 'px',
                            '#default_value' => isset($config['options']['marker_height']) ? $config['options']['marker_height'] : 0,
                        ),
                    ),
                ),
                'options' => array(
                    '#tree' => true,            
                    'marker_clusters' => array(
                        '#type' => 'yesno',
                        '#default_value' => !empty($config['options']['marker_clusters']),
                        '#title' => __('Enable marker clusters', 'sabai-directory'),
                    ),
                    'marker_cluster_imgurl' => array(
                        '#type' => 'url',
                        '#default_value' => @$config['options']['marker_cluster_imgurl'],
                        '#title' => __('Custom marker cluster image directory URL', 'sabai-directory'),
                        '#description' => sprintf(__('Default: %s', 'sabai-directory'), 'http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclusterer/images'),
                        '#states' => array(
                            'visible' => array(
                                'input[name="options[marker_clusters]"]' => array('value' => 1),
                            ), 
                        ),
                    ),
                ),
            ),
            'other' => array(
                '#title' => __('Other Settings', 'sabai-directory'),
                '#states' => array(
                    'visible' => array(
                        'input[name="disable[]"]' => array('type' => 'checked', 'value' => false),
                    ), 
                ),
                'listing_default_zoom' => array(
                    '#type' => 'select',
                    '#title' => __('Default zoom level for single listing', 'sabai-directory'),
                    '#options' => array_combine(range(0, 19), range(0, 19)),
                    '#default_value' => isset($config['listing_default_zoom']) ? $config['listing_default_zoom'] : 15,
                ),
                'map_show_all' => array(
                    '#type' => 'yesno',
                    '#title' => __('Show all listings in Map view', 'sabai-directory'),
                    '#default_value' => !empty($config['map_show_all']),
                ),
                'options' => array(
                    '#tree' => true,
                    'circle' => array(
                        'draw' => array(
                            '#type' => 'yesno',
                            '#title' => __('Draw radius search circle'),
                            '#default_value' => !empty($config['options']['circle']['draw']),
                        ),
                        'stroke_color' => array(
                            '#type' => 'textfield',
                            '#title' => __('Radius search circle stroke color'),
                            '#default_value' => isset($config['options']['circle']['stroke_color']) ? $config['options']['circle']['stroke_color'] : '#9999ff',
                            '#states' => array(
                                'visible' => array(
                                    'input[name="options[circle][draw]"]' => array('value' => 1),
                                ),
                            ),
                            '#size' => 8,
                            '#max_length' => 7,
                            '#min_length' => 4,
                            '#regex' => '/^#[a-f0-9]{3}([a-f0-9]{3})?$/i',
                            '#attributes' => array('placeholder' => '#9999ff'),
                        ),
                        'fill_color' => array(
                            '#type' => 'textfield',
                            '#title' => __('Radius search circle fill color'),
                            '#default_value' => isset($config['options']['circle']['fill_color']) ? $config['options']['circle']['fill_color'] : '#9999ff',
                            '#states' => array(
                                'visible' => array(
                                    'input[name="options[circle][draw]"]' => array('value' => 1),
                                ),
                            ),
                            '#size' => 8,
                            '#max_length' => 7,
                            '#min_length' => 4,
                            '#regex' => '/^#[a-f0-9]{3}([a-f0-9]{3})?$/i',
                            '#attributes' => array('placeholder' => '#9999ff'),
                        ),
                    ),
                ),
            ),
        );
    }
    
    public function isFieldRequired($form)
    {
        return empty($form->values['disable']);
    }
    
    public function isListHeightFieldRequired($form)
    {
        return $this->isFieldRequired($form) && !empty($form->values['list_show']);
    }
    
    protected function _getSuccessUrl(Sabai_Context $context)
    {
        return $this->Url($context->getRoute());
    }
    
    protected function _saveConfig(Sabai_Context $context, array $values)
    {
        $values['options'] += $values['_options'];
        unset($values['_options']);
        $this->getAddon()->saveConfig(array('map' => $values));
    }
}