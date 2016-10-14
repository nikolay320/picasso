<?php
abstract class Sabai_Addon_Entity_ChildCountFieldType extends Sabai_Addon_Field_Type_AbstractType implements Sabai_Addon_Field_ISortable
{
    protected $_entityTypes;

    public function __construct(Sabai_Addon $addon, $name, $entityType)
    {
        parent::__construct($addon, $name);
        $this->_entityType = $entityType;
    }

    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => 'Child Entity Count',
            'entity_types' => array($this->_entityType),
            'creatable' => false,
        );
    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'value' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'notnull' => true,
                    'unsigned' => true,
                    'was' => 'value',
                    'default' => 0,
                ),
                'child_bundle_name' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'notnull' => true,
                    'length' => 40,
                    'was' => 'child_bundle_name',
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

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values, array $currentValues = null)
    {
        $ret = array();
        foreach ($values as $value) {
            if (!is_array($value)) {
                $ret[] = false; // delete
            } else {
                $ret[] = $value;
            }
        }
        return $ret;
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values, Sabai_Addon_Entity_IEntity $entity)
    {
        $new_values = array();
        foreach ($values as $value) {
            // Index by child bundle name for easier access to counts
            $new_values[0][$value['child_bundle_name']] = (int)$value['value'];
        }
        $values = $new_values;
    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {
        $current = $new = array();
        if (!empty($currentLoadedValue[0])) {
            foreach ($currentLoadedValue[0] as $child_bundle_name => $value) {
                $current[] = array('value' => $value, 'child_bundle_name' => $child_bundle_name);
            }
        }
        foreach ($valueToSave as $value) {
            $new[] = array('value' => (int)$value['value'], 'child_bundle_name' => $value['child_bundle_name']);
        }
        return $current !== $new;
    }
    
    public function fieldSortableDoSort(Sabai_Addon_Field_IQuery $query, $fieldName, array $args = null)
    {
        if (!isset($args['child_bundle_name'])) return;
        
        $query->fieldIs($fieldName, $args['child_bundle_name'], 'child_bundle_name')
            ->sortByField($fieldName, isset($args[0]) && $args[0] === 'asc' ? 'ASC' : 'DESC', 'value');
    }
}