<?php
class Sabai_Addon_Questions_Controller_Admin_SearchSettings extends Sabai_Addon_System_Controller_Admin_Settings
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $config = $this->getAddon()->getConfig('search');
        $form = array(
            'keyword' => array(
                '#title' => __('Keyword Search Settings', 'sabai-discuss'),
                '#collapsed' => false,
                'no_key' => array(
                    '#type' => 'checkbox',
                    '#default_value' => !empty($config['no_key']),
                    '#title' => __('Disable keyword search', 'sabai-discuss'),
                ),
                'min_keyword_len' => array(
                    '#type' => 'number',
                    '#title' => __('Min. length of keywords in characters', 'sabai-discuss'),
                    '#size' => 3,
                    '#default_value' => isset($config['min_keyword_len']) ? $config['min_keyword_len'] : 3,
                    '#integer' => true,
                    '#required' => true,
                    '#min_value' => 1,
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => 0),
                        ), 
                    ),
                ),
                'match' => array(
                    '#title' => __('Match any or all', 'sabai-discuss'),
                    '#type' => 'select',
                    '#options' => array('any' => __('Match any', 'sabai-discuss'), 'all' => __('Match all', 'sabai-discuss')),
                    '#default_value' => $config['match'],
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => 0),
                        ), 
                    ),
                ),
                'auto_suggest' => array(
                    '#type' => 'yesno',
                    '#default_value' => !empty($config['auto_suggest']),
                    '#title' => __('Enable auto suggestion', 'sabai-discuss'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => 0),
                        ),
                    ),
                ),
                'suggest_question' => array(
                    '#type' => 'yesno',
                    '#default_value' => !empty($config['suggest_question']),
                    '#title' => __('Auto suggest questions', 'sabai-discuss'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => 0),
                            'input[name="auto_suggest"]' => array('value' => 1),
                        ),
                    ),
                ),
                'suggest_question_jump' => array(
                    '#type' => 'yesno',
                    '#default_value' => !empty($config['suggest_question_jump']),
                    '#title' => __('Redirect to suggested question page when clicked', 'sabai-discuss'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => 0),
                            'input[name="auto_suggest"]' => array('value' => 1),
                            'input[name="suggest_question"]' => array('value' => 1),
                        ),
                    ),
                ),
                'suggest_question_num' => array(
                    '#type' => 'number',
                    '#title' => __('Number of auto suggested questions to display', 'sabai-discuss'),
                    '#size' => 4,
                    '#integer' => true,
                    '#default_value' => isset($config['suggest_question_num']) ? $config['suggest_question_num'] : 5,
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_suggest"]' => array('value' => 1),
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => false),
                            'input[name="suggest_question"]' => array('value' => 1),
                        ),
                    ),
                    '#max_value' => 100,
                ),
                'suggest_cat' => array(
                    '#type' => 'yesno',
                    '#default_value' => !empty($config['suggest_cat']),
                    '#title' => __('Auto suggest categories', 'sabai-discuss'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => 0),
                            'input[name="auto_suggest"]' => array('value' => 1),
                        ),
                    ),
                ),
                'suggest_cat_jump' => array(
                    '#type' => 'yesno',
                    '#default_value' => !empty($config['suggest_cat_jump']),
                    '#title' => __('Redirect to suggested category page when clicked', 'sabai-discuss'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => 0),
                            'input[name="auto_suggest"]' => array('value' => 1),
                            'input[name="suggest_cat"]' => array('value' => 1),
                        ),
                    ),
                ),
                'suggest_cat_num' => array(
                    '#type' => 'number',
                    '#title' => __('Number of auto suggested categories to display', 'sabai-discuss'),
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
                'suggest_cat_icon' => array(
                    '#type' => 'icon',
                    '#size' => 20,
                    '#default_value' => isset($config['suggest_cat_icon']) ? $config['suggest_cat_icon'] : 'folder-open',
                    '#title' => __('Suggested category icon', 'sabai-discuss'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_key[]"]' => array('type' => 'checked', 'value' => 0),
                            'input[name="auto_suggest"]' => array('value' => 1),
                            'input[name="suggest_cat"]' => array('value' => 1),
                        ),
                    ),
                ),
            ),
            'category' => array(
                '#title' => __('Category Search Settings', 'sabai-discuss'),
                '#collapsed' => false,
                'no_cat' => array(
                    '#type' => 'checkbox',
                    '#default_value' => !empty($config['no_cat']),
                    '#title' => __('Disable category search', 'sabai-discuss'),
                ),
                'cat_depth' => array(
                    '#type' => 'number',
                    '#size' => 4,
                    '#title' => __('Category depth (0 for unlimited)', 'sabai-discuss'),
                    '#default_value' => intval(@$config['cat_depth']),
                    '#integer' => true,
                ),
                'cat_hide_empty' => array(
                    '#type' => 'yesno',
                    '#title' => __('Hide if no posts', 'sabai-discuss'),
                    '#default_value' => !empty($config['cat_hide_empty']), 
                ),
                'cat_hide_count' => array(
                    '#type' => 'yesno',
                    '#title' => __('Hide post count', 'sabai-discuss'),
                    '#default_value' => !empty($config['cat_hide_count']), 
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_cat[]"]' => array('type' => 'checked', 'value' => false),
                        ), 
                    ),
                ),
            ),
            'filter' => array(
                '#title' => __('Search Filter Settings', 'sabai-discuss'),
                '#collapsed' => false,
                'no_filters' => array(
                    '#type' => 'checkbox',
                    '#title' => __('Disable filters', 'sabai-discuss'),
                    '#default_value' => !empty($config['no_filters']),
                ),
                'show_filters' => array(
                    '#type' => 'yesno',
                    '#title' => __('Always show filters', 'sabai-discuss'),
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
                    '#title' => __('Display filter form above search results', 'sabai-discuss'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_filters[]"]' => array('type' => 'checked', 'value' => 0),
                        ), 
                    ),
                ),
                'filters_auto' => array(
                    '#type' => 'yesno',
                    '#default_value' => !empty($config['filters_auto']),
                    '#title' => __('Auto submit filter form when value(s) changed', 'sabai-discuss'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="no_filters[]"]' => array('type' => 'checked', 'value' => 0),
                        ), 
                    ),
                ),
            ),
        );
        $searchable_fields = array();
        foreach ($this->Entity_Field($context->bundle->name) as $field) {
            if ($field->isCustomField() && in_array($field->getFieldType(), array('string', 'text', 'markdown_text'))) {
                $searchable_fields[$field->getFieldName()] = $field->getFieldLabel();
            }
        }
        if (!empty($searchable_fields)) {
            $form['keyword']['fields'] = array(
                '#type' => 'checkboxes',
                '#class' => 'sabai-form-inline',
                '#title' => __('Custom fields to include in search', 'sabai-discuss'),
                '#options' => $searchable_fields,
                '#default_value' => isset($config['fields']) ? $config['fields'] : null,
                '#states' => array(
                    'visible' => array(
                        'input[name="no_key[]"]' => array('type' => 'checked', 'value' => 0),
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
        foreach (array('no_key' => 2, 'no_cat' => 1) as $key =>$value) {
            if (empty($values[$key])) $values['form_type'] += $value;
        }
        $this->getAddon()->saveConfig(array('search' => $values));
    }
}