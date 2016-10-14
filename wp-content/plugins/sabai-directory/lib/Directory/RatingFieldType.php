<?php
class Sabai_Addon_Directory_RatingFieldType extends Sabai_Addon_Field_Type_AbstractType implements Sabai_Addon_Field_ISortable
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => 'Listing Rating',
            'creatable' => false,
            'default_settings' => array(),
            'default_renderer' => $this->_name,
            'default_widget' => $this->_name,
        );
    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'value' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_DECIMAL,
                    'notnull' => true,
                    'length' => 5,
                    'scale' => 2,
                    'unsigned' => true,
                    'was' => 'value',
                    'default' => 0,
                ),
                'name' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'notnull' => true,
                    'length' => 50,
                    'was' => 'name',
                    'default' => '',
                ),
            ),
            'indexes' => array(
                'value' => array(
                    'fields' => array('value' => array('sorting' => 'ascending')),
                    'was' => 'value',
                ),
                'name_value' => array(
                    'fields' => array('name' => array('sorting' => 'ascending'), 'value' => array('sorting' => 'ascending')),
                    'was' => 'name_value',
                ),
            ),
        );        
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values, array $currentValues = null)
    {
        $value = array_shift($values);
        if (!is_array($value)) {
            // For versions before 1.3.0
            return array(array('name' => '', 'value' => (float)$value));
        }
        foreach ($value as $name => $rating) {
            if (!is_numeric($rating)) {
                unset($value[$name]);
                continue;
            }
            $rating = (float)$rating;
            if ($rating >= 0 && $rating <= 5) {
                $ret[$name] = array('name' => $name, 'value' => $rating);
            }
        }
        if (!isset($ret[''])) {
            $ret[''] = array('name' => '', 'value' => ($count = count($value)) ? round(array_sum($value) / $count, 1) : 0);
        }
        ksort($ret);
        return array_values($ret);
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values, Sabai_Addon_Entity_IEntity $entity)
    {
        $new_values = array();
        foreach ($values as $key => $value) {
            $new_values[$value['name']] = $value['value'];
        }
        ksort($new_values);
        $values = $new_values;
    }
    
    public function fieldTypeOnExport(Sabai_Addon_Field_IField $field, array &$values)
    {
        foreach (array_keys($values) as $name) {
            $values[$name] = $name . ':' . $values[$name];
        }
    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {   
        $current = array();
        foreach ($currentLoadedValue as $name => $value) {
            $current[] = array('name' => $name, 'value' => $value);
        }
        return $current !== $valueToSave;
    }
    
    public function fieldSortableDoSort(Sabai_Addon_Field_IQuery $query, $fieldName, array $args = null)
    {        
        $query->fieldIs($fieldName, '', 'name')
            ->sortByField($fieldName, isset($args) && $args[0] === 'asc' ? 'ASC' : 'DESC', 'value');
    }
}