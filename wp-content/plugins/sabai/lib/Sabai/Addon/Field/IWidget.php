<?php
interface Sabai_Addon_Field_IWidget
{
    public function fieldWidgetGetInfo($key = null);
    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array());
    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false);
    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $settings, array $parents = array());
    // public function fieldWidgetSetDefaultValue(Sabai_Addon_Field_IField $field, array $settings, array &$form, $defaultValue);
}