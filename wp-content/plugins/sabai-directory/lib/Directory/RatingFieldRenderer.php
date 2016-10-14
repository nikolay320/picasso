<?php
class Sabai_Addon_Directory_RatingFieldRenderer extends Sabai_Addon_Field_Renderer_AbstractRenderer
{    
    protected function _fieldRendererGetInfo()
    {
        return array(
            'field_types' => array($this->_name),
            'default_settings' => array(
                'sub_ratings' => array(
                    'display' => true,
                    'prefix' => '[ ',
                    'suffix' => ' ]',
                    'separator' => ' | '
                )
            ),
            'separatable' => false,
        );
    }
    
    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array())
    {
        return array(
            'sub_ratings' => array(
                '#class' => 'sabai-form-group',
                'display' => array(
                    '#type' => 'checkbox',
                    '#title' => __('Show sub-ratings if any', 'sabai-directory'),
                    '#default_value' => !empty($settings['sub_ratings']['display']),
                ),
                'separator' => array(
                    '#type' => 'textfield',
                    '#size' => 5,
                    '#default_value' => $settings['sub_ratings']['separator'],
                    '#field_prefix' => __('Rating separator:', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            sprintf('input[name="%s[sub_ratings][display][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('type' => 'checked', 'value' => true),
                        ),
                    ),
                    '#no_trim' => true,
                ),
                'prefix' => array(
                    '#type' => 'textfield',
                    '#size' => 5,
                    '#default_value' => $settings['sub_ratings']['prefix'],
                    '#field_prefix' => __('Prefix text:', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            sprintf('input[name="%s[sub_ratings][display][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('type' => 'checked', 'value' => true),
                        ),
                    ),
                    '#no_trim' => true,
                ),
                'suffix' => array(
                    '#type' => 'textfield',
                    '#size' => 5,
                    '#default_value' => $settings['sub_ratings']['suffix'],
                    '#field_prefix' => __('Suffix text:', 'sabai-directory'),
                    '#states' => array(
                        'visible' => array(
                            sprintf('input[name="%s[sub_ratings][display][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('type' => 'checked', 'value' => true),
                        ),
                    ),
                    '#no_trim' => true,
                ),
            ),
        );
    }

    public function fieldRendererRenderField(Sabai_Addon_Field_IField $field, array $settings, array $values, Sabai_Addon_Entity_IEntity $entity)
    {
        $ret = array();
        $ret[] = $this->_addon->getApplication()->Voting_RenderRating($values['']);
        $ret[] = sprintf('<span class="sabai-voting-rating-average" itemprop="ratingValue">%s</span>', number_format($values[''], 1));
        if (!empty($settings['sub_ratings']['display'])) {
            $ret[] = sprintf('<span class="sabai-directory-ratings">%s</span>', $this->_getReviewRatings($settings['sub_ratings'], $entity));
        }
        return implode('', $ret);
    }
    
    protected function _getReviewRatings(array $settings, $entity)
    {
        if (!$field = $this->_addon->getApplication()->Entity_Field($entity, 'directory_rating')) {
            return '';
        }
        $criteria = $this->_getValidCriteria($field->getFieldWidgetSettings());
        if (empty($criteria)) {
            return '';
        }
        
        $ret = array();
        $ratings = $entity->getFieldValue('directory_rating');
        foreach ($criteria as $slug => $label) {
            if (!isset($ratings[$slug])) continue;
            
            $ret[$slug] = sprintf('<span>%s</span> <strong>%.2f</strong>', $label, $ratings[$slug]);
        }
        
        return empty($ret) ? '' : $settings['prefix'] . implode($settings['separator'], $ret) . $settings['suffix'];
    }
    
    protected function _getValidCriteria(array $settings)
    {
        if (!isset($settings['criterion']['options'])) {
            return array();
        }
        $criteria = $settings['criterion']['options'];
        $default = (array)@$settings['criterion']['default'];
        foreach (array_keys($criteria) as $option) {
            if (!in_array($option, $default)) {
                unset($criteria[$option]);
            }
        }
        return $criteria;
    }
}
