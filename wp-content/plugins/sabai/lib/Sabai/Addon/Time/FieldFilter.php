<?php
class Sabai_Addon_Time_FieldFilter extends Sabai_Addon_Field_Filter_AbstractFilter
{
    protected function _fieldFilterGetInfo()
    {
        return array(
            'label' => __('Time', 'sabai'),
            'field_types' => array('time_time'),
            'default_settings' => array(),
        );
    }
    
    public function fieldFilterGetForm(Sabai_Addon_Field_IField $field, $filterName, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $request = null, array $requests = null, $isSubmitOnChanage = true, array $parents = array())
    {
        $ret = array('#collapsible' => false, '#class' => 'sabai-form-inline');
        $field_settings = $field->getFieldSettings();
        if (!empty($field_settings['enable_day'])) {
            $ret['day'] = array(
                '#type' => 'select',
                '#options' => $this->_addon->getApplication()->Time_Days(true),
            );
        }
        foreach (range(0, 84600, 3600) as $time) {
            $options[$time] = date('h:i A', $time);
        }
        $ret['start'] = array(
            '#type' => 'select',
            '#options' => array('' => '') + $options,
        );
        return $ret;
    }
    
    public function fieldFilterIsFilterable(Sabai_Addon_Field_IField $field, $filterName, array $settings, &$value, array $requests = null)
    {
        $field_settings = $field->getFieldSettings();        
        if (empty($field_settings['enable_day'])) {
            return strlen(@$value['start']) > 0;
        }

        return !empty($value['day']) || strlen(@$value['start']) > 0;
    }
    
    public function fieldFilterDoFilter(Sabai_Addon_Field_IQuery $query, Sabai_Addon_Field_IField $field, $filterName, array $settings, $value)
    {
        $field_settings = $field->getFieldSettings();        
        if (empty($field_settings['enable_day'])) {
            $value['start'] -= $this->_addon->getApplication()->getPlatform()->getGMTOffset() * 3600;
            if ($value['start'] < 0) {
                $value['start'] += 86400;
            }
            $query->addIsOrSmallerThanCriteria($field, 'start', $value['start'])
                ->addIsOrGreaterThanCriteria($field, 'end', $value['start']);
            return;
        }
        if (!empty($value['day'])) {
            $query->addIsCriteria($field, 'day', $value['day']);
        }
        if (strlen(@$value['start'])) {
            $value['start'] -= $this->_addon->getApplication()->getPlatform()->getGMTOffset() * 3600;
            if ($value['start'] < 0) {
                $value['start'] += 86400;
            }
            $query->addIsOrSmallerThanCriteria($field, 'start', $value['start'])
                ->addIsOrGreaterThanCriteria($field, 'end', $value['start']);
        }
    }
    
    public function fieldFilterGetPreview(Sabai_Addon_Field_IField $field, $filterName, array $settings)
    {
        $ret = array();
        $field_settings = $field->getFieldSettings(); 
        if (!empty($field_settings['enable_day'])) {
            $ret[] = '<select disabled="disabled"><option></option></select>';
        }
        $ret[] = '<input type="text" disabled="disabled" size="6" placeholder="HH:MM" />';
        return implode(PHP_EOL, $ret);
    }
}
