<?php
class Sabai_Addon_Field_Widget_Phone extends Sabai_Addon_Field_Widget_Textfield
{
    protected function _fieldWidgetGetInfo()
    {
        $info = parent::_fieldWidgetGetInfo();
        $info['field_types'] = array($this->_name);
        return $info;
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        $form = parent::fieldWidgetGetSettingsForm($fieldType, $settings, $parents);
        return $form;
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        $form = parent::fieldWidgetGetForm($field, $settings, $bundle, $value, $entity, $parents, $admin);
        return $form;
    }
}