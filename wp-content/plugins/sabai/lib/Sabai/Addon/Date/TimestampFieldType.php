<?php
class Sabai_Addon_Date_TimestampFieldType extends Sabai_Addon_Field_Type_AbstractType implements Sabai_Addon_Field_ISortable
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('Date', 'sabai'),
            'default_widget' => 'date_datepicker',
            'default_renderer' => 'date_timestamp',
            'default_settings' => array(
                'date_range' => false,
                'date_range_min' => null,
                'date_range_max' => null,
                'enable_time' => true,
            ),
            'sorts' => array(
                array(),
                array('args' => array('desc'), 'label' => __('%s (desc)', 'sabai'))
            ),
        );
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {
        return array(
            '#element_validate' => array(array($this, 'validateSettings')),
            'enable_time' => array(
                '#type' => 'checkbox',
                '#title' => __('Enable time (hour and minute)', 'sabai'),
                '#description' => __('Check this option to allow the user to enter the time (hour and minute) as well as the date.', 'sabai'),
                '#default_value' => !empty($settings['enable_time']),
            ),
            'date_range' => array(
                '#type' => 'checkbox',
                '#title' => __('Restrict dates', 'sabai'),
                '#description' => __('Check this option to set the range of allowed dates for this field.', 'sabai'),
                '#default_value' => !empty($settings['date_range']),
                '#class' => 'sabai-form-field-no-margin',
            ),
            'date_range_min' => array(
                '#type' => 'date_datepicker',
                '#field_prefix' => __('Minimum date:', 'sabai'),
                '#default_value' => $settings['date_range_min'],
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[date_range][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                            'type' => 'checked',
                            'value' => true,
                        ),
                    ),
                ),
            ),
            'date_range_max' => array(
                '#type' => 'date_datepicker',
                '#field_prefix' => __('Maximum date:', 'sabai'),
                '#default_value' => $settings['date_range_max'],
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[date_range][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                            'type' => 'checked',
                            'value' => true,
                        ),
                    ),
                ),
            ),
        );
    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'value' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'notnull' => true,
                    'unsigned' => false,
                    'length' => 20,
                    'default' => 0,
                ),
            ),
            'indexes' => array(
                'value' => array(
                    'fields' => array('value' => array('sorting' => 'ascending')),
                ),
            ),
        );
    }
    
    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        $ret = array();
        foreach ($values as $weight => $value) {
            if (!is_numeric($value)
                && (!$value = strtotime($value))
            ) {
                continue;
            } else {
                $value = intval($value);
            }
            $ret[]['value'] = $value;
        }

        return $ret;
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values, Sabai_Addon_Entity_IEntity $entity)
    {
        foreach ($values as $key => $value) {
            $values[$key] = $value['value'];
        }
    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {   
        $new = array();
        foreach ($valueToSave as $value) {
            $new[] = $value['value'];
        }
        return $currentLoadedValue !== $new;
    }

    public function validateSettings($form, &$value, $element)
    {
        if (empty($value['date_range'])) return;
        
        if (!empty($value['date_range_min']) && !empty($value['date_range_max'])) {
            if ($value['date_range_min'] >= $value['date_range_max']) {
                $form->setError(__('The first date must be later than the second date.', 'sabai'), $element['#name']);
            }
        }
    }
    
    public function fieldSortableDoSort(Sabai_Addon_Field_IQuery $query, $fieldName, array $args = null)
    {
        $query->sortByField($fieldName, isset($args) && $args[0] === 'desc' ? 'DESC' : 'ASC');
    }
}