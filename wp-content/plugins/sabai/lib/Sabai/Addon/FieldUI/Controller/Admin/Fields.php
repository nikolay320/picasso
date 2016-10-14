<?php
class Sabai_Addon_FieldUI_Controller_Admin_Fields extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        $bundle = $context->child_bundle ? $context->child_bundle : ($context->taxonomy_bundle ? $context->taxonomy_bundle : $context->bundle);
        $admin_fields_path = $bundle->getAdminPath() . '/fields';
        $field_types = array();
        foreach ($this->Field_Types() as $field_type_name => $field_type) {
            if (empty($field_type['widgets']) || !$field_type['editable']) {
                continue;
            }
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
            $field_types[$field_type_name] = $field_type;
        }

        $fields = array();
        foreach ($bundle->Fields->with('FieldConfig') as $field) {            
            if (isset($fields[$field->getFieldName()])) {
                $field->markRemoved();
                $commit = true;
            } else {
                if ($field->getFieldType() === 'markdown_text') {
                    $field->setFieldType('text');
                    $commit = true;
                }
                $fields[$field->getFieldName()] = $field;
            }
        }
        if (!empty($commit)) {
            // Remove duplicated fields
            $this->getModel(null, 'Entity')->commit();
        }
        uasort($fields, array(__CLASS__, '_sortByWeight'));
        
        $existing_fields = array();
        foreach ($this->getModel('FieldConfig', 'Entity')->name_startsWith('field_')->fetch()->with('Fields') as $existing_field_config) {
            foreach ($existing_field_config->Fields as $existing_field) {
                if (isset($existing_fields[$existing_field_config->type][$existing_field_config->name])) continue;
                
                $existing_fields[$existing_field_config->type][$existing_field_config->name] = $existing_field;
            }
        }
        
        $context->addTemplate('fieldui_admin_fields')
            ->setAttributes(array(
                'fields' => $this->Filter('fieldui_admin_fields', $fields, array($bundle)),
                'field_types' => $field_types,
                'form_submit_path' => $this->Url($admin_fields_path . '/submit'),
                'form_edit_field_url' => $this->Url($admin_fields_path . '/edit'),
                'form_create_field_url' => $this->Url($admin_fields_path . '/create'),
                'existing_fields' => $existing_fields,
                'hidden_existing_fields' => array(),
            ));
        
        $this->Action('fieldui_admin_fields');
    }

    
    private static function _sortByWeight($a, $b)
    {
        return $a->getFieldWeight() < $b->getFieldWeight() ? -1 : 1;
    }
}