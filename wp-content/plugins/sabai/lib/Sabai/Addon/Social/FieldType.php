<?php
class Sabai_Addon_Social_FieldType extends Sabai_Addon_Field_Type_AbstractType
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('Social Accounts', 'sabai'),
            'default_renderer' => 'social_accounts',
        );
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {

    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'accounts' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                    'notnull' => true,
                    'was' => 'accounts',
                ),
            ),
        );        
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values, array $currentValues = null)
    {
        $ret = array();
        foreach ($values as $value) {
            if (is_array($value)) {
                $ret[] = array('accounts' => serialize(array_filter($value)));
            }
        }
        return $ret;
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values, Sabai_Addon_Entity_IEntity $entity)
    {
        foreach ($values as $key => $value) {
            $values[$key] = (array)@unserialize($values[$key]['accounts']);
        }
    }
    
    public function fieldTypeOnExport(Sabai_Addon_Field_IField $field, array &$values)
    {
        foreach (array_keys($values) as $key) {
            foreach (array_keys($values[$key]) as $media) {
                $values[$key][$media] = $media . ':' . $values[$key][$media];
            }
            $values[$key] = implode('|', $values[$key]);
        }
    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {   
        foreach ($currentLoadedValue as $key => $value) {
            $currentLoadedValue[$key]['accounts'] = serialize((array)@$currentLoadedValue[$key]);
        }
        return $currentLoadedValue !== $valueToSave;
    }
}