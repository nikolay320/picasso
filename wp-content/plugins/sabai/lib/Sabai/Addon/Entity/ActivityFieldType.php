<?php
abstract class Sabai_Addon_Entity_ActivityFieldType extends Sabai_Addon_Field_Type_AbstractType implements Sabai_Addon_Field_ISortable
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
            'label' => 'Activity',
            'entity_types' => array($this->_entityType),
            'creatable' => false,
        );
    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'active_at' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'notnull' => true,
                    'unsigned' => true,
                    'was' => 'active_at',
                    'default' => 0,
                ),
                'edited_at' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'notnull' => true,
                    'unsigned' => true,
                    'was' => 'edited_at',
                    'default' => 0,
                ),
            ),
            'indexes' => array(
                'active_at' => array(
                    'fields' => array('active_at' => array('sorting' => 'ascending')),
                    'was' => 'active_at',
                ),
                'edited_at' => array(
                    'fields' => array('edited_at' => array('sorting' => 'ascending')),
                    'was' => 'edited_at',
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
    
    public function fieldSortableDoSort(Sabai_Addon_Field_IQuery $query, $fieldName, array $args = null)
    {
        $query->sortByField($fieldName, isset($args) && $args[0] === 'asc' ? 'ASC' : 'DESC', 'active_at');
    }
}