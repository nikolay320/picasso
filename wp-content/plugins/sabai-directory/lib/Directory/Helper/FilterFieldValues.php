<?php
class Sabai_Addon_Directory_Helper_FilterFieldValues extends Sabai_Helper
{
    public function help(Sabai $application, array $fieldValues, array $allowedFields)
    {
        foreach (array('directory_contact', 'directory_social') as $field_name) {
            if (!isset($fieldValues[$field_name][0])) continue;
                
            foreach (array_keys($fieldValues[$field_name][0]) as $_field_name) {
                if (!in_array($field_name . '_' . $_field_name, $allowedFields)) {
                    unset($fieldValues[$field_name][0][$_field_name]);
                }
            }
        }
        if (!in_array('content_body', $allowedFields)) {
            unset($fieldValues['content_body']);
        }
        // Limit custom fields
        foreach (array_keys($fieldValues) as $field_name) {
            if (strpos($field_name, 'field_') === 0
                && strpos($field_name, 'field_meta_') !== 0
                && !in_array($field_name, $allowedFields)
            ) {
                unset($fieldValues[$field_name]);
            }
        }
        
        return $fieldValues;
    }
}