<?php
class Sabai_Addon_Field_Type_User extends Sabai_Addon_Field_Type_AbstractType
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => _x('User', 'field type', 'sabai'),
            'default_widget' => 'user_select',
            'default_renderer' => 'user',
            'default_settings' => array(),
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
        $ret = $user_ids = array();
        foreach ($values as $weight => $value) {
            if (is_array($value)) {  // autocomplete field widget
                foreach ($value as $user_id) {
                    if (!is_numeric($user_id)) {
                        continue;
                    }
                    $user_ids[$user_id] = $user_id;
                            
                }
            } elseif (is_numeric($value)) {
                $user_ids[$value] = $value;
            }
        }
        foreach ($user_ids as $user_id) {
            $ret[]['value'] = $user_id;
        }
        return $ret;
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values, Sabai_Addon_Entity_IEntity $entity)
    {
        $users = array();
        foreach ($values as $key => $value) {
            $users[$value['value']] = $key;
        }
        foreach ($this->_addon->getApplication()->UserIdentities(array_keys($users)) as $identity) {
            if (!$identity->id) {
                continue;
            }
            $key = $users[$identity->id];
            $values[$key] = $identity;
            unset($users[$identity->id]);
        }
        // Remove values that were not found
        foreach ($users as $key) {
            unset($values[$key]);
        }
        // Re-order as it was saved
        ksort($values);
    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {
        $current = $new = array();
        foreach ($currentLoadedValue as $identity) {
            $current[] = (int)$identity->id;
        }
        foreach ($valueToSave as $value) {
            $new[] = $value['value'];
        }
        return $current !== $new;
    }
    
    public function fieldTypeOnExport(Sabai_Addon_Field_IField $field, array &$values)
    {
        foreach (array_keys($values) as $key) {
            $values[$key] = $values[$key]->id;
        }
    }
}