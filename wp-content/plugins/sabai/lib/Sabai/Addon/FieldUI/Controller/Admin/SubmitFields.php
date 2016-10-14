<?php
class Sabai_Addon_FieldUI_Controller_Admin_SubmitFields extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        // Check request token
        if (!$this->_checkToken($context, 'fieldui_admin_submit_fields', true)) return;

        $bundle = $context->child_bundle ? $context->child_bundle : ($context->taxonomy_bundle ? $context->taxonomy_bundle : $context->bundle);
        $current_fields = $bundle->Fields->getArray();
        $field_types = $this->Field_Types();
        if ($fields = $context->getRequest()->asArray('fields')) {   
            $field_weight = 0;
            foreach ($fields as $weight => $field_id) {
                if (!isset($current_fields[$field_id])) continue;

                $field = $current_fields[$field_id];
                unset($current_fields[$field_id]);
                
                // Make sure that the field type exists
                if (!isset($field_types[$field->getFieldType()])) continue;

                $field->setFieldWeight(++$field_weight);
            }
        }

        // Remove fields
        if (!empty($current_fields)) {
            $field_configs_to_maybe_remove = array();
            foreach ($current_fields as $field_id => $current_field) {
                if (!$current_field->isCustomField()
                    || empty($field_types[$current_field->getFieldType()]['deletable'])
                ) {
                    continue;
                }

                $field_configs_to_maybe_remove[$current_field->getFieldName()] = $current_field->getFieldName();
                $current_field->markRemoved();
            }
        }
        $this->getModel(null, 'Entity')->commit();
        
        // Remove field configs that do not have any actual field
        if (!empty($field_configs_to_maybe_remove)) {
            $field_configs = array('existing' => array(), 'removed' => array());
            foreach ($this->getModel('FieldConfig', 'Entity')->name_in($field_configs_to_maybe_remove)->fetch()->with('Fields') as $field_config) {
                // Do not remove field config if there is any field defined for it
                if (count($field_config->Fields)) {
                    $field_configs['existing'][$field_config->name] = $field_config;
                    continue;
                }
                
                $field_config->markRemoved();
                $field_configs['removed'][$field_config->name] = $field_config;
            }
            if (!empty($field_configs['removed'])) {
                $this->getModel(null, 'Entity')->commit();
                $this->getAddon('Entity')->deleteFieldStorage($field_configs['removed']);
                $this->_application->Action('entity_delete_field_configs_success', array($field_configs['removed']));
            }
        }
        
        // Clear all field cache
        $this->Entity_FieldCacheImpl()->entityFieldCacheClean();

        // Send success
        $context->setSuccess($bundle->getAdminPath() . '/fields');
        if (!empty($field_configs)) {
            $attributes = array();
            foreach ($field_configs as $type => $_field_configs) {
                foreach ($_field_configs as $field_config) {
                    $attributes['fields_' . $type][$field_config->name] = array(
                        'type' => $field_config->type,
                        'type_normalized' => str_replace('_', '-', $field_config->type),
                        'name' => $field_config->name,
                    );
                }
            }
            $context->setSuccessAttributes($attributes);
        }
    }
}