<?php
class Sabai_Addon_Field_Type_Number extends Sabai_Addon_Field_Type_Value implements Sabai_Addon_Field_ISortable
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('Number', 'sabai'),
            'default_widget' => 'textfield',
            'default_renderer' => 'number',
            'default_settings' => array(
                'min' => null,
                'max' => null,
                'decimals' => 0,
                'prefix' => null,
                'suffix' => null,
            ),
        );
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {
        return array(
            '#element_validate' => array(array($this, 'validateMinMaxSettings')),
            'min' => array(
                '#type' => 'number',
                '#title' => __('Minimum', 'sabai'),
                '#description' => __('The minimum value allowed in this field.', 'sabai'),
                '#size' => 10,
                '#default_value' => $settings['min'],
                '#numeric' => true,
                '#step' => 0.01,
            ),
            'max' => array(
                '#type' => 'number',
                '#title' => __('Maximum', 'sabai'),
                '#description' => __('The maximum value allowed in this field.', 'sabai'),
                '#size' => 10,
                '#default_value' => $settings['max'],
                '#numeric' => true,
                '#step' => 0.01,
            ),
            'decimals' => array(
                '#type' => 'radios',
                '#title' => __('Decimals', 'sabai'),
                '#description' => __('The number of digits to the right of the decimal point.', 'sabai'),
                '#options' => array(0 => __('0 (no decimals)', 'sabai'), 1 => 1, 2 => 2),
                '#default_value' => $settings['decimals'],
                '#class' => 'sabai-form-inline',
            ),
            'prefix' => array(
                '#type' => 'textfield',
                '#title' => __('Field prefix', 'sabai'),
                '#description' => __('Example: $, #, -', 'sabai'),
                '#size' => 20,
                '#default_value' => $settings['prefix'],
                '#no_trim' => true,
            ),
            'suffix' => array(
                '#type' => 'textfield',
                '#title' => __('Field suffix', 'sabai'),
                '#description' => __('Example: km, %, g', 'sabai'),
                '#size' => 20,
                '#default_value' => $settings['suffix'],
                '#no_trim' => true,
            ),
        );
    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'value' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_DECIMAL,
                    'notnull' => true,
                    'length' => 18,
                    'scale' => 2,
                    'unsigned' => false,
                    'was' => 'value',
                    'default' => 0,
                ),
            ),
            'indexes' => array(
                'value' => array(
                    'fields' => array('value' => array('sorting' => 'ascending')),
                    'was' => 'value',
                ),
            ),
        );
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        $settings = $field->getFieldSettings();
        $ret = array();
        foreach ($values as $weight => $value) {
            if (!is_numeric($value)) continue;

            $ret[]['value'] = round($value, $settings['decimals']);
        }

        return $ret;
    }
    
    public function fieldSortableDoSort(Sabai_Addon_Field_IQuery $query, $fieldName, array $args = null)
    {
        $query->sortByField($fieldName, isset($args) && $args[0] === 'asc' ? 'ASC' : 'DESC');
    }
}