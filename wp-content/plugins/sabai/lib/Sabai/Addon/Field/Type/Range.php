<?php
class Sabai_Addon_Field_Type_Range extends Sabai_Addon_Field_Type_AbstractType
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('Range', 'sabai'),
            'default_widget' => 'range',
            'default_renderer' => 'range',
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
            '#element_validate' => array(array(array($this, 'validateMinMaxSettings'), array('decimals'))),
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
                'min' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_DECIMAL,
                    'notnull' => true,
                    'length' => 18,
                    'scale' => 2,
                    'unsigned' => false,
                    'was' => 'min',
                    'default' => 0,
                ),
                'max' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_DECIMAL,
                    'notnull' => true,
                    'length' => 18,
                    'scale' => 2,
                    'unsigned' => false,
                    'was' => 'max',
                    'default' => 0,
                ),
            ),
            'indexes' => array(
                'min' => array(
                    'fields' => array('min' => array('sorting' => 'ascending')),
                    'was' => 'min',
                ),
                'max' => array(
                    'fields' => array('max' => array('sorting' => 'ascending')),
                    'was' => 'max',
                ),
            ),
        );
    }   

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        $settings = $field->getFieldSettings();
        $ret = array();
        foreach ($values as $weight => $value) {
            if (!is_numeric(@$value['min']) || !is_numeric(@$value['max'])) continue;

            $ret[] = array(
                'min' => round($value['min'], $settings['decimals']),
                'max' => round($value['max'], $settings['decimals']),
            );
        }

        return $ret;
    }
}