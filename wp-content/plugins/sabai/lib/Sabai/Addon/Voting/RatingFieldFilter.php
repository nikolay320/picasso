<?php
class Sabai_Addon_Voting_RatingFieldFilter extends Sabai_Addon_Field_Filter_AbstractFilter
{
    protected $_nameColumn = 'name', $_valueColumn = 'average', $_nameColumnValue = '';
    
    protected function _fieldFilterGetInfo()
    {
        return array(
            'label' => __('Rating', 'sabai'),
            'field_types' => array('voting_rating'),
            'default_label' => __('Rating', 'sabai'),
            'default_settings' => array(
                'type' => 'radios',
                'inline' => false,
            ),
        );
    }

    public function fieldFilterGetSettingsForm(Sabai_Addon_Field_IField $field, array $settings, array $parents = array())
    {
        return array(
            'type' => array(
                '#title' => __('Form element type', 'sabai'),
                '#type' => 'radios',
                '#options' => array('radios' => __('Radio buttons', 'sabai'), 'select' => __('Select list', 'sabai')),
                '#default_value' => $settings['type'],
                '#class' => 'sabai-form-inline',
            ),
            'inline' => array(
                '#type' => 'checkbox',
                '#title' => __('Display inline', 'sabai'),
                '#description' => __('Check this to align all options on the same line.', 'sabai'),
                '#default_value' => $settings['inline'],
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[type]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 'radios'),
                    ),
                ),
            ),
        );
    }
    
    public function fieldFilterGetForm(Sabai_Addon_Field_IField $field, $filterName, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $request = null, array $requests = null, $isSubmitOnChanage = true, array $parents = array())
    {
        return array(
            '#type' => $settings['type'] === 'radios' ? 'radios' : 'select',
            '#options' => $this->_addon->getApplication()->Voting_RatingOptions($settings['type']),
            '#title_no_escape' => true,
            '#class' => $settings['inline'] ? 'sabai-form-inline' : null,
            '#empty_value' => 0,
        );
    }
    
    public function fieldFilterIsFilterable(Sabai_Addon_Field_IField $field, $filterName, array $settings, &$value, array $requests = null)
    {
        return !empty($value);
    }
    
    public function fieldFilterDoFilter(Sabai_Addon_Field_IQuery $query, Sabai_Addon_Field_IField $field, $filterName, array $settings, $value)
    {
        $query->addIsCriteria($field, $this->_nameColumn, $this->_nameColumnValue)
            ->addIsOrGreaterThanCriteria($field, $this->_valueColumn, (int)$value);
    }
    
    public function fieldFilterGetPreview(Sabai_Addon_Field_IField $field, $filterName, array $settings)
    {
        if ($settings['type'] === 'select') {
            return '<select disabled="disabled"><option>' . _x('Any', 'option', 'sabai') . '</option></select>';
        }
        $ret = array();
        foreach ($this->_addon->getApplication()->Voting_RatingOptions($settings['type']) as $value => $label) {
            $ret[] = sprintf('<input type="radio" disabled="disabled"%s />%s', $value === 0 ? ' checked="checked"' : '', $label);
        }
        return implode($settings['inline'] ? '&nbsp;&nbsp;' : '<br />', $ret);
    }
}