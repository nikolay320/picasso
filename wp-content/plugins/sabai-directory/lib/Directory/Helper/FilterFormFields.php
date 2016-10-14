<?php
class Sabai_Addon_Directory_Helper_FilterFormFields extends Sabai_Helper
{
    public function help(Sabai $application, array $form, array $allowedFields)
    {
        foreach (array('directory_contact', 'directory_social') as $field_name) {
            if (!isset($form[$field_name][0])) continue;
                
            foreach (array_keys($form[$field_name][0]) as $_field_name) {
                if (!in_array($field_name . '_' . $_field_name, $allowedFields)) {
                    unset($form[$field_name][0][$_field_name]);
                }
            }
                    
            if (empty($form[$field_name][0])) {
                unset($form[$field_name]);
            }
        }
        if (!in_array('content_body', $allowedFields)) {
            unset($form['content_body']);
        }
            
        foreach (array_keys($form) as $field_name) {
            if (strpos($field_name, 'field_') === 0
                || (is_array($form[$field_name]) && isset($form[$field_name]['#type']) && $form[$field_name]['#type'] === 'sectionbreak')
            ) {
                if (!in_array($field_name, $allowedFields)) {
                    unset($form[$field_name]);
                }
            }
        }
        
        return $form;
    }
}