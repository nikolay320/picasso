<?php
interface Sabai_Addon_Field_IType
{
    public function fieldTypeGetInfo($key = null);
    /**
     * Returns the settings form for the field type
     * @return array
     */
    public function fieldTypeGetSettingsForm(array $settings, array $parents = array());
    /**
     * Returns the database schema for the field type
     * @return array
     */
    public function fieldTypeGetSchema(array $settings);
    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values);
    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values, Sabai_Addon_Entity_IEntity $entity);
}