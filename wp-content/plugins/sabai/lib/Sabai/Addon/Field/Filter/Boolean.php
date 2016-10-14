<?php
class Sabai_Addon_Field_Filter_Boolean extends Sabai_Addon_Field_Filter_AbstractFilter
{
    protected $_filterColumn = 'value', $_trueValue = true, $_inverse = false, $_nullOnly = false;
    
    protected function _fieldFilterGetInfo()
    {
        return array(
            'label' => __('ON/OFF', 'sabai'),
            'field_types' => array('boolean'),
            'default_settings' => array(
                'type' => 'checkbox',
                'inline' => true,
                'labels' => array(
                    'on' => __('Checked', 'sabai'),
                    'off' => __('Unchecked', 'sabai'),
                ),
                'checkbox_label' => null,
            ),
        );
    }

    public function fieldFilterGetSettingsForm(Sabai_Addon_Field_IField $field, array $settings, array $parents = array())
    {
        return array(
            'type' => array(
                '#title' => __('Form element type', 'sabai'),
                '#type' => 'radios',
                '#options' => array('checkbox' => __('Single checkbox', 'sabai'), 'radios' => __('Radio buttons', 'sabai'), 'select' => __('Select list', 'sabai')),
                '#default_value' => $settings['type'],
                '#required' => true,
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
            'labels' => array(
                '#title' => __('Option labels', 'sabai'),
                '#class' => 'sabai-form-inline',
                '#collapsible' => false,
                'on' => array(
                    '#type' => 'textfield',
                    '#size' => 10,
                    '#default_value' => $settings['labels']['on'],
                    '#field_suffix' => '&nbsp;/&nbsp;',
                    '#placeholder' => __('Checked', 'sabai'),
                ),
                'off' => array(
                    '#type' => 'textfield',
                    '#size' => 10,
                    '#default_value' => $settings['labels']['off'],
                    '#placeholder' => __('Unchecked', 'sabai'),
                ),
                '#states' => array(
                    'invisible' => array(
                        sprintf('input[name="%s[type]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 'checkbox'),
                    ),
                ),
            ),
            'checkbox_label' => array(
                '#type' => 'textfield',
                '#title' => __('Checkbox label', 'sabai'),
                '#description' => __('Enter the label displayed next to the checkbox.', 'sabai'),
                '#default_value' => $settings['checkbox_label'],
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[type]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 'checkbox'),
                    ),
                ),
            ),
        );
    }
    
    public function fieldFilterGetForm(Sabai_Addon_Field_IField $field, $filterName, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $request = null, array $requests = null, $isSubmitOnChanage = true, array $parents = array())
    {
        switch ($settings['type']) {
            case 'radios':
                return array(
                    '#type' => 'radios',
                    '#options' => array(
                        1 => $settings['labels']['on'],
                        0 => $settings['labels']['off'],
                        '' => _x('Any', 'option', 'sabai'),
                    ),
                    '#class' => $settings['inline'] ? 'sabai-form-inline' : null,
                    '#default_value' => '',
                );
            case 'select':
                return array(
                    '#type' => 'select',
                    '#options' => array(
                        '' => _x('Any', 'option', 'sabai'),
                        1 => $settings['labels']['on'],
                        0 => $settings['labels']['off'],
                    ),
                );
            case 'checkbox':
            default:
                return array(
                    '#type' => 'checkbox',
                    '#on_value' => 1,
                    '#off_value' => '',
                    '#on_label' => $settings['checkbox_label'],
                );
        }
    }
    
    public function fieldFilterIsFilterable(Sabai_Addon_Field_IField $field, $filterName, array $settings, &$value, array $requests = null)
    {
        return is_numeric($value);
    }
    
    public function fieldFilterDoFilter(Sabai_Addon_Field_IQuery $query, Sabai_Addon_Field_IField $field, $filterName, array $settings, $value)
    {
        $value = (bool)$value;
        if ($this->_inverse) {
            $value = !$value;
        }
        if (!$value) {
            if ($this->_nullOnly) {
                $query->addIsNullCriteria($field, $this->_filterColumn);
            } else {
                $query->startCriteriaGroup('OR')
                    ->addIsNullCriteria($field, $this->_filterColumn);
                if (is_array($this->_trueValue)) {
                    $query->addNotInCriteria($field, $this->_filterColumn, $this->_trueValue);
                } else {
                    $query->addIsNotCriteria($field, $this->_filterColumn, $this->_trueValue);
                }
                $query->finishCriteriaGroup();
            }
        } else {
            if ($this->_nullOnly) {
                $query->addIsNotNullCriteria($field, $this->_filterColumn);
            } else {
                if (is_array($this->_trueValue)) {
                    $query->addInCriteria($field, $this->_filterColumn, $this->_trueValue);
                } else {
                    $query->addIsCriteria($field, $this->_filterColumn, $this->_trueValue);
                }
            }
        }
    }
    
    public function fieldFilterGetPreview(Sabai_Addon_Field_IField $field, $filterName, array $settings)
    {
        switch ($settings['type']) {
            case 'radios':
                $ret = array(
                    '<input type="radio" disabled="disabled" />' . Sabai::h($settings['labels']['on']),
                    '<input type="radio" disabled="disabled" />' . Sabai::h($settings['labels']['off']),
                    '<input type="radio" disabled="disabled" checked="checked" />' . _x('Any', 'option', 'sabai'),
                );
                if ($settings['inline']) {
                    return implode('&nbsp;&nbsp;', $ret);
                }
                return implode('<br />', $ret);
            case 'select':
                return '<select disabled="disabled"><option>' . _x('Any', 'option', 'sabai') . '</option></select>';
            case 'checkbox':
                return sprintf('<input type="checkbox" disabled="disabled" /> %s', Sabai::h($settings['checkbox_label']));
        }
    }
}