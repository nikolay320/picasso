<?php
class Sabai_Addon_Directory_Controller_Admin_SearchSettings extends Sabai_Addon_System_Controller_Admin_Settings
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $config = $this->getAddon()->getConfig('search');
        $radius_options = array();
        foreach ($this->Filter('directory_distances', array(0, 1, 2, 3, 5, 10, 20, 50, 100)) as $distance) {
            $radius_options[$distance] = sprintf(__('%d km/mi', 'sabai-directory'), $distance);
        }
        if (isset($radius_options[0])) {
            $radius_options[0] = __('None', 'sabai-directory');
        }
        $form = array(
            'keyword' => array(
                '#title' => __('Keyword Search Settings', 'sabai-directory'),
                '#collapsed' => false,
                '#weight' => 1,
                'no_key' => array(
                    '#type' => 'checkbox',
                    '#default_value' => !empty($config['no_key']),
                    '#title' => __('Disable keyword search', 'sabai-directory'),
                ),
                'min_keyword_len' => array(
                    '#type' => 'number',
                    '#title' => __('Min. length of keywords in characters', 'sabai-directory'),
                    '#size' => 3,
                    '#default_value' => isset($config['min_keyword_len']) ? $config['min_keyword_len'] : 3,
                    '#integer' => true,
                    '#required' => true,
                    '#min_value' => 1,
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => false),
                        ), 
                    ),
                    '#display_unrequired' => true,
                ),
                'match' => array(
                    '#title' => __('Match any or all', 'sabai-directory'),
                    '#type' => 'select',
                    '#options' => array('any' => __('Match any', 'sabai-directory'), 'all' => __('Match all', 'sabai-directory')),
                    '#default_value' => $config['match'],
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => false),
                        ), 
                    ),
                ),
                'auto_suggest' => array(
                    '#type' => 'yesno',
                    '#default_value' => !empty($config['auto_suggest']),
                    '#title' => __('Enable auto suggestion', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => false),
                        ), 
                    ),
                ),
                'suggest_listing' => array(
                    '#type' => 'yesno',
                    '#default_value' => !empty($config['suggest_listing']),
                    '#title' => __('Auto suggest listings', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest"]' => array('value' => 1),
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_listing_jump' => array(
                    '#type' => 'yesno',
                    '#default_value' => !empty($config['suggest_listing_jump']),
                    '#title' => __('Redirect to suggested listing page when clicked', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest"]' => array('value' => 1),
                            'input[name="suggest_listing"]' => array('value' => 1),
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_listing_num' => array(
                    '#type' => 'number',
                    '#title' => __('Number of auto suggested listings to display', 'sabai-directory'),
                    '#size' => 4,
                    '#integer' => true,
                    '#default_value' => isset($config['suggest_listing_num']) ? $config['suggest_listing_num'] : 5,
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest"]' => array('value' => 1),
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => false),
                            'input[name="suggest_listing"]' => array('value' => 1),
                        ),
                    ),
                    '#max_value' => 100,
                ),
                'suggest_listing_header' => array(
                    '#type' => 'textfield',
                    '#default_value' => isset($config['suggest_listing_header']) ? $config['suggest_listing_header'] : '',
                    '#title' => __('Suggested listings header text', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest"]' => array('value' => 1),
                            'input[name="suggest_listing"]' => array('value' => 1),
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_listing_icon' => array(
                    '#type' => 'icon',
                    '#size' => 20,
                    '#default_value' => isset($config['suggest_listing_icon']) ? $config['suggest_listing_icon'] : 'file-text-o',
                    '#title' => __('Suggested listing icon', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest"]' => array('value' => 1),
                            'input[name="suggest_listing"]' => array('value' => 1),
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_cat' => array(
                    '#type' => 'yesno',
                    '#default_value' => !empty($config['suggest_cat']),
                     '#title' => __('Auto suggest categories', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest"]' => array('value' => 1),
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_cat_jump' => array(
                    '#type' => 'yesno',
                    '#default_value' => !empty($config['suggest_cat_jump']),
                    '#title' => __('Redirect to suggested category page when clicked', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest"]' => array('value' => 1),
                            'input[name="suggest_cat"]' => array('value' => 1),
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_cat_num' => array(
                    '#type' => 'number',
                    '#title' => __('Number of auto suggested categories to display', 'sabai-directory'),
                    '#size' => 4,
                    '#integer' => true,
                    '#default_value' => isset($config['suggest_cat_num']) ? $config['suggest_cat_num'] : 5,
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest"]' => array('value' => 1),
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => false),
                            'input[name="suggest_cat"]' => array('value' => 1),
                        ),
                    ),
                    '#max_value' => 100,
                ),
                'suggest_cat_header' => array(
                    '#type' => 'textfield',
                    '#default_value' => isset($config['suggest_cat_header']) ? $config['suggest_cat_header'] : $this->Entity_BundleLabel($this->getAddon()->getCategoryBundleName()),
                    '#title' => __('Suggested categories header text', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest"]' => array('value' => 1),
                            'input[name="suggest_cat"]' => array('value' => 1),
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_cat_icon' => array(
                    '#type' => 'icon',
                    '#size' => 20,
                    '#default_value' => isset($config['suggest_cat_icon']) ? $config['suggest_cat_icon'] : 'folder-open',
                    '#title' => __('Suggested category icon', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest"]' => array('value' => 1),
                            'input[name="suggest_cat"]' => array('value' => 1),
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
            ),
            'category' => array(
                '#title' => __('Category Search Settings', 'sabai-directory'),
                '#collapsed' => false,
                '#weight' => 3,
                'no_cat' => array(
                    '#type' => 'checkbox',
                    '#default_value' => !empty($config['no_cat']),
                    '#title' => __('Disable category search', 'sabai-directory'),
                ),
                'cat_depth' => array(
                    '#type' => 'number',
                    '#size' => 4,
                    '#title' => __('Category depth (0 for unlimited)', 'sabai-directory'),
                    '#default_value' => intval(@$config['cat_depth']),
                    '#integer' => true,
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_cat[]"]' => array('type' => 'checked', 'value' => false),
                        ), 
                    ),
                ),
                'cat_hide_empty' => array(
                    '#type' => 'yesno',
                    '#title' => __('Hide if no posts', 'sabai-directory'),
                    '#default_value' => !empty($config['cat_hide_empty']), 
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_cat[]"]' => array('type' => 'checked', 'value' => false),
                        ), 
                    ),
                ),
                'cat_hide_count' => array(
                    '#type' => 'yesno',
                    '#title' => __('Hide post count', 'sabai-directory'),
                    '#default_value' => !empty($config['cat_hide_count']), 
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_cat[]"]' => array('type' => 'checked', 'value' => false),
                        ), 
                    ),
                ),
            ),
            'filter' => array(
                '#title' => __('Search Filter Settings', 'sabai-directory'),
                '#collapsed' => false,
                '#weight' => 4,
                'no_filters' => array(
                    '#type' => 'checkbox',
                    '#title' => __('Disable filters', 'sabai-directory'),
                    '#default_value' => !empty($config['no_filters']),
                ),
                'show_filters' => array(
                    '#type' => 'yesno',
                    '#title' => __('Always show filters', 'sabai-directory'),
                    '#default_value' => !empty($config['show_filters']),
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_filters[]"]' => array('type' => 'checked', 'value' => false),
                        ), 
                    ),
                ),
                'filters_top' => array(
                    '#type' => 'yesno',
                    '#default_value' => !empty($config['filters_top']),
                    '#title' => __('Display filter form above search results', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_filters[]"]' => array('type' => 'checked', 'value' => false),
                        ), 
                    ),
                ),
                'filters_auto' => array(
                    '#type' => 'yesno',
                    '#default_value' => !empty($config['filters_auto']),
                    '#title' => __('Auto submit filter form when value(s) changed', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_filters[]"]' => array('type' => 'checked', 'value' => false),
                        ), 
                    ),
                ),
            ),
        );
        $searchable_fields = array();
        foreach ($this->Entity_Field($context->bundle->name) as $field) {
            if ($field->isCustomField() && in_array($field->getFieldType(), array('string', 'text', 'markdown_text', 'choice'))) {
                $searchable_fields[$field->getFieldName()] = $field->getFieldLabel();
            }
        }
        if (!empty($searchable_fields)) {
            $form['keyword']['fields'] = array(
                '#type' => 'checkboxes',
                '#class' => 'sabai-form-inline',
                '#title' => __('Custom fields to include in search', 'sabai-directory'),
                '#options' => $searchable_fields,
                '#default_value' => isset($config['fields']) ? $config['fields'] : null,
                '#states' => array(
                    'visible' => array(
                        'input[name="no_key[]"]' => array('type' => 'checked', 'value' => false),
                    ),
                ),
            );
        }
        
        if ($this->isAddonLoaded('GoogleMaps')) {
            $form['location'] = array(
                '#title' => __('Location Search Settings', 'sabai-directory'),
                '#collapsed' => false,
                '#weight' => 2,
                'no_loc' => array(
                    '#type' => 'checkbox',
                    '#default_value' => !empty($config['no_loc']),
                    '#title' => __('Disable location search', 'sabai-directory'),
                ),
                'radius' => array(
                    '#type' => 'select',
                    '#title' => __('Default search radius', 'sabai-directory'),
                    '#options' => $radius_options,
                    '#default_value' => isset($config['radius']) ? $config['radius'] : 0,
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ), 
                    ),
                ),
                'country' => $this->GoogleMaps_AutocompleteCountryFormField($config['country']) + array(
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ), 
                    ),
                ),
                'auto_suggest_loc' => array(
                    '#type' => 'yesno',
                    '#default_value' => !isset($config['auto_suggest_loc']) || !empty($config['auto_suggest_loc']),
                    '#title' => __('Enable auto suggestion', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ), 
                    ),
                ),
                'suggest_location' => array(
                    '#type' => 'yesno',
                    '#default_value' => !isset($config['suggest_location']) || !empty($config['suggest_location']),
                    '#title' => __('Auto suggest location addresses', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_location_header' => array(
                    '#type' => 'textfield',
                    '#default_value' => isset($config['suggest_location_header']) ? $config['suggest_location_header'] : '',
                    '#title' => __('Suggested location addresses header text', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="suggest_location"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_city' => array(
                    '#type' => 'yesno',
                    '#default_value' => !empty($config['suggest_city']),
                    '#title' => __('Auto suggest cities', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_city_num' => array(
                    '#type' => 'number',
                    '#title' => __('Number of auto suggested cities to display', 'sabai-directory'),
                    '#size' => 4,
                    '#max_value' => 100,
                    '#integer' => true,
                    '#default_value' => isset($config['suggest_city_num']) ? $config['suggest_city_num'] : 5,
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                            'input[name="suggest_city"]' => array('value' => 1),
                        ),
                    ),
                    '#max_value' => 100,
                ),
                'suggest_city_header' => array(
                    '#type' => 'textfield',
                    '#default_value' => isset($config['suggest_city_header']) ? $config['suggest_city_header'] : __('City', 'sabai-directory'),
                    '#title' => __('Suggested cities header text', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="suggest_city"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_city_icon' => array(
                    '#type' => 'icon',
                    '#size' => 20,
                    '#default_value' => isset($config['suggest_city_icon']) ? $config['suggest_city_icon'] : 'building',
                    '#title' => __('Suggested city icon', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="suggest_city"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_location_icon' => array(
                    '#type' => 'icon',
                    '#size' => 20,
                    '#default_value' => isset($config['suggest_location_icon']) ? $config['suggest_location_icon'] : 'map-marker',
                    '#title' => __('Suggested location address icon', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="suggest_location"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_state' => array(
                    '#type' => 'yesno',
                    '#default_value' => !empty($config['suggest_city']),
                    '#title' => __('Auto suggest states/provinces/regions', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_state_num' => array(
                    '#type' => 'number',
                    '#title' => __('Number of auto suggested states/provinces/regions to display', 'sabai-directory'),
                    '#size' => 4,
                    '#max_value' => 100,
                    '#integer' => true,
                    '#default_value' => isset($config['suggest_state_num']) ? $config['suggest_state_num'] : 5,
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                            'input[name="suggest_state"]' => array('value' => 1),
                        ),
                    ),
                    '#max_value' => 100,
                ),
                'suggest_state_header' => array(
                    '#type' => 'textfield',
                    '#default_value' => isset($config['suggest_state_header']) ? $config['suggest_state_header'] : __('State / Province / Region', 'sabai-directory'),
                    '#title' => __('Suggested states/provinces/regions header text', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="suggest_state"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_state_icon' => array(
                    '#type' => 'icon',
                    '#size' => 20,
                    '#default_value' => isset($config['suggest_state_icon']) ? $config['suggest_state_icon'] : 'map-marker',
                    '#title' => __('Suggested state/province/region icon', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="suggest_state"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_zip' => array(
                    '#type' => 'yesno',
                    '#default_value' => !empty($config['suggest_zip']),
                    '#title' => __('Auto suggest zip/postal codes', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_zip_num' => array(
                    '#type' => 'number',
                    '#title' => __('Number of auto suggested zip/postal codes to display', 'sabai-directory'),
                    '#size' => 4,
                    '#integer' => true,
                    '#max_value' => 100,
                    '#default_value' => isset($config['suggest_zip_num']) ? $config['suggest_zip_num'] : 5,
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                            'input[name="suggest_zip"]' => array('value' => 1),
                        ),
                    ),
                    '#max_value' => 100,
                ),
                'suggest_zip_header' => array(
                    '#type' => 'textfield',
                    '#default_value' => isset($config['suggest_zip_header']) ? $config['suggest_zip_header'] : __('Postal / Zip code', 'sabai-directory'),
                    '#title' => __('Suggested zip/postal codes header text', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="suggest_city"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_zip_icon' => array(
                    '#type' => 'icon',
                    '#size' => 20,
                    '#default_value' => isset($config['suggest_zip_icon']) ? $config['suggest_zip_icon'] : 'envelope',
                    '#title' => __('Suggested zip/postal code icon', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="suggest_zip"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_country' => array(
                    '#type' => 'yesno',
                    '#default_value' => !empty($config['suggest_country']),
                    '#title' => __('Auto suggest countries', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_country_num' => array(
                    '#type' => 'number',
                    '#title' => __('Number of auto suggested countries to display', 'sabai-directory'),
                    '#size' => 4,
                    '#integer' => true,
                    '#max_value' => 100,
                    '#default_value' => isset($config['suggest_country_num']) ? $config['suggest_country_num'] : 5,
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                            'input[name="suggest_country"]' => array('value' => 1),
                        ),
                    ),
                    '#max_value' => 100,
                ),
                'suggest_country_header' => array(
                    '#type' => 'textfield',
                    '#default_value' => isset($config['suggest_country_header']) ? $config['suggest_country_header'] : __('Country', 'sabai-directory'),
                    '#title' => __('Suggested countries header text', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="suggest_country"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
                'suggest_country_icon' => array(
                    '#type' => 'icon',
                    '#size' => 20,
                    '#default_value' => isset($config['suggest_country_icon']) ? $config['suggest_country_icon'] : 'map-marker',
                    '#title' => __('Suggested country icon', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest_loc"]' => array('value' => 1),
                            'input[name="suggest_country"]' => array('value' => 1),
                            'input[name="no_loc[]"]' => array('type' => 'checked', 'value' => false),
                        ),
                    ),
                ),
            );
        }
        
        return $form;
    }
    
    protected function _getSuccessUrl(Sabai_Context $context)
    {
        return $this->Url($context->getRoute());
    }
    
    protected function _saveConfig(Sabai_Context $context, array $values)
    {
        $values['form_type'] = 0;
        foreach (array('no_key' => 4, 'no_loc' => 2, 'no_cat' => 1) as $key =>$value) {
            if (isset($values[$key]) && empty($values[$key])) $values['form_type'] += $value;
        }
        
        $this->getAddon()->saveConfig(array('search' => $values));
    }
}