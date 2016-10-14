<?php
class Sabai_Addon_Entity_Helper_CustomFields extends Sabai_Addon_Entity_Helper_Fields
{
    protected function _isValidField(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $fieldName, array $exclude = null)
    {
        return strpos($fieldName, 'field_') === 0 && ($field = parent::_isValidField($application, $entity, $fieldName, $exclude)) ? $field : false;
    }
}