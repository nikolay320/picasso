<?php
class Sabai_Addon_Field_Type_Choice extends Sabai_Addon_Field_Type_Value implements Sabai_Addon_Field_ISortable
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('Choice', 'sabai'),
            'default_widget' => 'checkboxes',
            'default_renderer' => 'choice',
            'default_settings' => array(
                'options' => null,
            ),
        );
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {
        return array(
            'options' => array(
                '#type' => 'options',
                '#title' => __('Options', 'sabai'),
                '#default_value' => $settings['options'],
            ),
        );
    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'value' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'length' => 255,
                    'notnull' => true,
                    'was' => 'value',
                    'default' => '',
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
    
    public function fieldSortableDoSort(Sabai_Addon_Field_IQuery $query, $fieldName, array $args = null)
    {
        $query->sortByField($fieldName, isset($args) && $args[0] === 'asc' ? 'ASC' : 'DESC');
    }
}