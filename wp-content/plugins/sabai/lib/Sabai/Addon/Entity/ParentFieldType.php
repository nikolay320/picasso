<?php
abstract class Sabai_Addon_Entity_ParentFieldType extends Sabai_Addon_Field_Type_AbstractType
{
    protected $_entityType;

    public function __construct(Sabai_Addon $addon, $name, $entityType)
    {
        parent::__construct($addon, $name);
        $this->_entityType = $entityType;
    }

    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => 'Parent Entity',
            'entity_types' => array($this->_entityType),
            'creatable' => false,
            'editable' => false,
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
            ),
        );
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values, array $currentValues = null)
    {
        $ret = array();
        foreach ($values as $weight => $value) {
            if (strlen((string)$value) === 0) continue;

            $ret[]['value'] = $value;
        }
        return $ret;   
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values, Sabai_Addon_Entity_IEntity $entity)
    {
        $entities = array();
        foreach ($values as $key => $value) {
            $entities[$value['value']] = $key;
        }
        $values = array();
        foreach ($this->_addon->getApplication()
            ->Entity_TypeImpl('content')
            ->entityTypeGetEntitiesByIds(array_keys($entities))
        as $entity) {
            $key = $entities[$entity->getId()];
            $values[$key] = $entity;
        }
        ksort($values); // re-order as it was saved
    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {
        $current = $new = array();
        foreach ($currentLoadedValue as $value) {
            $current[] = (int)$value->getId();
        }
        foreach ($valueToSave as $value) {
            $new[] = (int)$value['value'];
        }
        return $current !== $new;
    }
}