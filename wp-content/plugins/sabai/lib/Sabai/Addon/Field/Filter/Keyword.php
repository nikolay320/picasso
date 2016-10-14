<?php
class Sabai_Addon_Field_Filter_Keyword extends Sabai_Addon_Field_Filter_AbstractFilter
{
    protected function _fieldFilterGetInfo()
    {
        return array(
            'label' => __('Keyword', 'sabai'),
            'field_types' => array('string', 'text', 'markdown_text'),
            'default_settings' => array(
                'min_length' => 3,
                'size' => 'large',
                'type' => 'all',
            ),
        );
    }

    public function fieldFilterGetSettingsForm(Sabai_Addon_Field_IField $field, array $settings, array $parents = array())
    {
        return array(
            'min_length' => array(
                '#type' => 'number',
                '#title' => __('Min. length of keywords in characters', 'sabai'),
                '#size' => 4,
                '#default_value' => $settings['min_length'],
                '#integer' => true,
                '#required' => true,
                '#min_value' => 1,
            ),
            'size' => array(
                '#type' => 'select',
                '#title' => __('Field size', 'sabai'),
                '#options' => array(
                    'small' => __('Small', 'sabai'),
                    'medium' => __('Medium', 'sabai'),
                    'large' => __('Large (responsive)', 'sabai'),
                ),
                '#default_value' => $settings['size'],
            ),
            'type' => array(
                '#type' => 'radios',
                '#title' => __('Match any or all', 'sabai'),
                '#options' => array(
                    'any' => __('Match any', 'sabai'),
                    'all' => __('Match all', 'sabai'),
                ),
                '#default_value' => $settings['type'],
                '#class' => 'sabai-form-inline',
            ),
        );
    }
    
    public function fieldFilterGetForm(Sabai_Addon_Field_IField $field, $filterName, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $request = null, array $requests = null, $isSubmitOnChanage = true, array $parents = array())
    {
        $sizes = array('small' => 20, 'medium' => 50, 'large' => null);
        return array(
            '#class' => 'sabai-form-group',
            '#collapsible' => false,
            'keyword' => array(
                '#type' => 'textfield',
                '#field_prefix' => isset($settings['field_prefix']) && strlen($settings['field_prefix']) ? $settings['field_prefix'] : null,
                '#field_suffix' => isset($settings['field_suffix']) && strlen($settings['field_suffix']) ? $settings['field_suffix'] : null,
                '#size' => isset($settings['size']) && isset($sizes[$settings['size']]) ? $sizes[$settings['size']] : null,
            ),
            'type' => array(
                '#type' => 'radios',
                '#options' => array('all' => __('Match all', 'sabai'), 'any' => __('Match any', 'sabai')),
                '#default_value' => $settings['type'],
                '#class' => 'sabai-form-inline',
                '#pre_render' => array(array(array($this, 'preRenderCallback'), array($parents))),
            ),
        );
    }
    
    public function preRenderCallback(Sabai_Addon_Form_Form $form, &$element, $parents)
    {
        $value = $form->getValue($parents);
        // Prevent the filter form from being submitted if no keywords
        if (empty($value['keywords'])) {
            $element['#class'] .= ' sabai-field-filter-ignore';
        }
    }
    
    public function fieldFilterIsFilterable(Sabai_Addon_Field_IField $field, $filterName, array $settings, &$value, array $requests = null)
    {
        if (!isset($value['keyword'])
            || (!$value['keyword'] = trim((string)$value['keyword']))
        ) {
            return false;
        }
        
        $keywords = $this->_addon->getApplication()->Keywords($value['keyword'], $settings['min_length']);
        
        if (empty($keywords[0])) return false; // no valid keywords
        
        $value['keywords'] = $keywords[0];
        
        return true;
    }
    
    public function fieldFilterDoFilter(Sabai_Addon_Field_IQuery $query, Sabai_Addon_Field_IField $field, $filterName, array $settings, $value)
    {        
        switch (@$value['type']) {
            case 'any':
                if (count($value['keywords']) === 1) {
                    $query->addContainsCriteria($field, 'value', array_shift($value['keywords']));
                } else {
                    $query->startCriteriaGroup('OR');
                    foreach ($value['keywords'] as $keyword) {
                        $query->addContainsCriteria($field, 'value', $keyword);
                    } 
                    $query->finishCriteriaGroup();
                }
                break;
            default:
                foreach ($value['keywords'] as $keyword) {
                    $query->addContainsCriteria($field, 'value', $keyword);
                }
        }
    }
    
    public function fieldFilterGetPreview(Sabai_Addon_Field_IField $field, $filterName, array $settings)
    {
        $sizes = array('small' => 20, 'medium' => 50, 'large' => null);
        return sprintf(
            '<input type="text" disabled="disabled"%s /><br /><input type="radio" disabled="disabled"%s />%s <input type="radio" disabled="disabled"%s />%s',
            isset($settings['size']) && isset($sizes[$settings['size']]) ? sprintf(' size="%d"', $sizes[$settings['size']]) : '',
            $settings['type'] === 'all' ? ' checked="checked"' : '',
            __('Match all', 'sabai'),
            $settings['type'] === 'any' ? ' checked="checked"' : '',
            __('Match any', 'sabai')
        );
    }
}