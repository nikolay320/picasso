<?php
class Sabai_Addon_Directory_ClaimFieldType extends Sabai_Addon_Field_Type_AbstractType implements Sabai_Addon_Field_ISortable
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => 'Listing Claim',
            'creatable' => false,
            'editable' => false,
            'default_renderer' => $this->_name,
            'default_settings' => array(),
        );
    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'claimed_by' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'notnull' => true,
                    'unsigned' => true,
                    'was' => 'claimed_by',
                    'default' => 0,
                ),
                'claimed_at' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'notnull' => true,
                    'unsigned' => true,
                    'was' => 'claimed_at',
                    'default' => 0,
                ),
                'expires_at' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'notnull' => true,
                    'unsigned' => true,
                    'was' => 'expires_at',
                    'default' => 0,
                ),
            ),
            'indexes' => array(
                'claimed_by' => array(
                    'fields' => array(
                        'claimed_by' => array('sorting' => 'ascending'),
                    ),
                    'was' => 'claimed_by',
                ),
                'expires_at' => array(
                    'fields' => array(
                        'expires_at' => array('sorting' => 'ascending'),
                    ),
                    'was' => 'expires_at',
                ),
            ),
        );
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values, array $currentValues = null)
    {
        $ret = array();
        if (!empty($currentValues)) {
            $current_values = array();
            foreach ($currentValues as $current_value) {
                $current_values[$current_value['claimed_by']] = $current_value;
            }
        }
        foreach ($values as $value) {
            if (!is_array($value) || empty($value['claimed_by'])) {
                continue;
            }
            if (empty($value['claimed_at'])) { // may be empty on renewal, for example
                $value['claimed_at'] = isset($current_values[$value['claimed_by']]) && !empty($current_values[$value['claimed_by']]['claimed_at'])
                    ? $current_values[$value['claimed_by']]['claimed_at']
                    : time();   
            }
            $ret[$value['claimed_by']] = $value;
        }
        return array_values($ret); // re-index array
    }
    
    public function fieldSortableDoSort(Sabai_Addon_Field_IQuery $query, $fieldName, array $args = null)
    {        
        $query->sortByField($fieldName, isset($args) && $args[0] === 'asc' ? 'ASC' : 'DESC', 'claimed_at');
    }
}