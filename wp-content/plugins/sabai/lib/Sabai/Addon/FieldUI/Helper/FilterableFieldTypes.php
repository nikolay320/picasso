<?php
class Sabai_Addon_FieldUI_Helper_FilterableFieldTypes extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Model_Bundle $bundle, $creatable = false)
    {
        $ret = array();
        $key = $creatable ? 'creatable_filters' : 'filters';
        foreach ($application->Field_Types() as $field_type_name => $field_type) {
            if (empty($field_type[$key])) continue;
           
            if (isset($field_type['entity_types'])
                && !in_array($bundle->entitytype_name, $field_type['entity_types'])
            ) {
                // the field type does not support the entity type of the current bundle
                continue;
            }
            if (isset($field_type['bundles'])
                && !in_array($bundle->name, $field_type['bundles'])
            ) {
                // the field type does not support the entity type of the current bundle
                continue;
            }
            $ret[$field_type_name] = $field_type;
        }
        return $ret;
    }
}