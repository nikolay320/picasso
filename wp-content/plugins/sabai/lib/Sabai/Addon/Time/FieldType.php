<?php
class Sabai_Addon_Time_FieldType extends Sabai_Addon_Field_Type_AbstractType implements Sabai_Addon_Field_ISortable
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('Time', 'sabai'),
            'default_widget' => 'time_time',
            'default_renderer' => 'time_time',
            'default_settings' => array(
                'enable_day' => false,
                'enable_end' => false,
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
            'enable_day' => array(
                '#type' => 'checkbox',
                '#title' => __('Enable day of week', 'sabai'),
                '#default_value' => !empty($settings['enable_day']),
            ),
            'enable_end' => array(
                '#type' => 'checkbox',
                '#title' => __('Enable end time', 'sabai'),
                '#default_value' => !empty($settings['enable_end']),
            ),
        );
    }
    
    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values, Sabai_Addon_Entity_IEntity $entity){}

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'start' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'notnull' => true,
                    'length' => 8,
                    'was' => 'start',
                    'default' => '0',
                ),
                'end' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'notnull' => true,
                    'length' => 8,
                    'was' => 'end',
                    'default' => '0',
                ),
                'day' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'notnull' => true,
                    'length' => 1,
                    'was' => 'day',
                    'default' => '0',
                ),
            ),
            'indexes' => array(
                'start' => array(
                    'fields' => array('start' => array('sorting' => 'ascending')),
                ),
                'end' => array(
                    'fields' => array('end' => array('sorting' => 'ascending')),
                ),
                'day' => array(
                    'fields' => array('day' => array('sorting' => 'ascending')),
                ),
            ),
        );
    }
    
    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        $ret = array();
        foreach ($values as $weight => $value) {
            if (is_array($value)) {
                $value += array('start' => 0, 'end' => null, 'day' => 0);
            } else {
                if (!is_numeric($value)) {
                    continue;
                } 
                $value = array('start' => $value, 'end' => null, 'day' => 0);
            }
            
            $value['day'] = intval($value['day']);
            if ($value['day'] > 7 && $value['day'] % 7) {
                $value['day'] = $value['day'] % 7;
            }
            $value['start'] = intval($value['start']) % 86400;
            if (isset($value['end'])) {
                $value['end'] = intval($value['end']) % 86400;
                if ($value['end'] < $value['start']) {
                    $value['end'] += 86400;
                }
            } else {
                $value['end'] = $value['start'];
            }
            $ret[] = $value;
        }
        return $ret;
    }
    
    public function fieldSortableDoSort(Sabai_Addon_Field_IQuery $query, $fieldName, array $args = null)
    {
        $query->sortByField($fieldName, isset($args) && $args[0] === 'desc' ? 'DESC' : 'ASC', 'start');
    }
}
