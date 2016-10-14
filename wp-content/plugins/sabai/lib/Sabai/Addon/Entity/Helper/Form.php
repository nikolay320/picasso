<?php
class Sabai_Addon_Entity_Helper_Form extends Sabai_Helper
{    
    public function help(Sabai $application, $bundleName, array $values = null, $admin = false, $wrap = false)
    {
        $entity = null;
        if ($bundleName instanceof Sabai_Addon_Entity_IEntity) {
            $bundle = $application->Entity_Bundle($bundleName);
            if ($bundleName->getId()) {
                $entity = $bundleName;
            }
        } elseif ($bundleName instanceof Sabai_Addon_Entity_Model_Bundle) {
            $bundle = $bundleName;   
        } else {
            if (!$bundle = $application->Entity_Bundle($bundleName)) {
                throw new Sabai_RuntimeException('Invalid bundle: ' . $bundleName);
            }
        }
        $entity_type = $bundle->entitytype_name;
        
        // Fetch user roles for later use
        if (!$application->getUser()->isAnonymous()) {
            $current_user_roles = $application->getPlatform()->getUserRolesByUser($application->getUser()->getIdentity()->id);
        } else {
            $current_user_roles = array('_guest_');
        }
        $super_user_roles = array_keys($application->AdministratorRoles());

        // Load field values if an existing entity has been passed
        if (isset($entity)) {
            $application->Entity_LoadFields($entity_type, array($entity->getId() => $entity), null, true, false);
        }
        if (isset($values)) {
            // The values are supplied here to check if fields have been added dynamically before submit. 
            // Form fields will be populated after the form submission is validated, so do not populate them here.
            $do_not_populate_fields = true;
        }
        
        $fields = array();
        foreach ($bundle->Fields->with('FieldConfig') as $field) {
            if (!$field->getFieldWidget() || $field->getFieldDisabled()) continue;
             
            // Any user role restriction to view this field?
            if (!$user_roles = (array)$field->getFieldData('user_roles')) {
                // No user roles defined
                if ($field->isCustomField()) {
                    continue;
                }
            } else {
                $has_role = false;
                foreach ($user_roles as $role) {
                    // Admin roles must check differently because WP network admin does not have a role
                    if (in_array($role, $super_user_roles)) {
                        if ($application->getUser()->isAdministrator()) {
                            $has_role = true;
                            break;
                        }
                    } else {
                        if (in_array($role, $current_user_roles)) {
                            $has_role = true;
                            break;
                        }
                    }
                }
                if (!$has_role) {
                    continue; // the current user does not have a role that is permitted to view this field
                }
            }
            $fields[$field->getFieldName()] = $field;
        }

        $form = $_fields = array();
        foreach ($application->Filter('entity_form_fields', $fields, array(isset($entity) ? $entity : $bundleName, $admin)) as $field_name => $field) {
            if (!$ifieldwidget = $application->Field_WidgetImpl($field->getFieldWidget(), true)) {
                continue;
            }
            $field_widget_info = $ifieldwidget->fieldWidgetGetInfo();
            // Is this an backend side only field?
            if (!$admin && !empty($field_widget_info['admin_only'])) {
                continue;
            }
            $field_value = null;
            $form_ele = array(
                '#tree' => true,
                '#title' => ($admin || !$field->getFieldData('hide_label')) ? $field->getFieldLabel() : '',
                '#description' => $field->getFieldDescription(),
                '#weight' => $field->getFieldWeight(),
                '#required' => $field->getFieldRequired() && (false !== @$field_widget_info['requirable']) && empty($field_widget_info['partially_requirable']),
                '#collapsible' => false,
                '#states' => array(),
            );
            /*
            foreach ($field->getFieldConditions() as $dependee => $condition) {
                $state = isset($condition['state']) ? $condition['state'] : 'visible';
                $type = isset($condition['type']) ? $condition['type'] : 'value';
                if ($type === 'checked') {
                    $dependee = sprintf('[name="%s[]"]', $dependee);
                    $condition['value'] = (bool)$condition['value'];
                } else {
                    $dependee = sprintf('[name="%s"],[name="%s[]"]', $dependee);
                }
                $form_ele['#states'][$state][$dependee]  = array('type' => $type, 'value' => $condition['value']);
            }
             */
            if (isset($values[$field_name])) {
                if (is_array($values[$field_name])
                    && array_key_exists(0, $values[$field_name])
                ) {
                    $field_value = $values[$field_name];
                }
            } elseif (isset($entity)) {
                $field_value = $entity->getFieldValue($field_name);
            }
                
            if (!$ifieldwidget->fieldWidgetGetInfo('accept_multiple')) {
                if ($repeatable = $ifieldwidget->fieldWidgetGetInfo('repeatable')) {
                    $repeatable = (array)$repeatable;
                    if (!isset($repeatable['group_fields']) || $repeatable['group_fields'] !== false) {
                        $form_ele['#class'] = 'sabai-form-group';
                    }
                    $max_num_values = $field->getFieldMaxNumItems();
                    if (!empty($field_value)) {
                        $field_element_count = count($field_value);
                        if ($max_num_values && $max_num_values < $field_element_count) {
                            $field_element_count = $max_num_values;
                            $field_value = array_slice($field_value, 0, $field_element_count, true);
                        }
                        foreach ($field_value as $key => $_field_value) {
                            if (!$form_ele[$key] = $this->_getEntityFormElement($application, $bundle, $field, $entity, $key, empty($do_not_populate_fields) ? $_field_value : null, !isset($entity), $admin, $wrap)) {
                                continue 2;
                            }
                        }
                        $next_index = ++$key;
                    } else {
                        if (!$form_ele[0] = $this->_getEntityFormElement($application, $bundle, $field, $entity, 0, null, !isset($entity), $admin, $wrap)) {
                            continue;
                        }
                        $next_index = 1;
                    }
                    if ($max_num_values !== 1) {
                        $form_ele['_add'] = $this->_getEntityFormAddElementLink($field, $repeatable, $max_num_values, $next_index, $wrap);
                    }
                } else {
                    if (!$_form_ele = $this->_getEntityFormElement($application, $bundle, $field, $entity, 0, isset($field_value) && empty($do_not_populate_fields) ? array_shift($field_value) : null, !isset($entity), $admin, $wrap)) {
                        continue;
                    }
                    if (isset($_form_ele['#type'])) {
                        switch ($_form_ele['#type']) {
                            case 'hidden':
                                continue;
                            case 'markup':
                            case 'sectionbreak':
                                // prevent the form element from being rendered as a fieldset
                                $form_ele = array('#weight' => $field->getFieldWeight()) + $_form_ele;
                                break;
                            default:
                                $form_ele[0] = $_form_ele;
                        }
                    } else {
                        $form_ele[0] = $_form_ele;
                    }
                }
            } else {
                if (!$_form_ele = $this->_getEntityFormElement($application, $bundle, $field, $entity, null, empty($do_not_populate_fields) ? $field_value : null, !isset($entity), $admin, $wrap)) {
                    continue;
                }
                $form_ele = $_form_ele + $form_ele;
                $form_ele['#required'] = $field->getFieldRequired();
            }
            if (isset($form_ele[0])) {
                // Make only the first element required if multiple input fields 
                $form_ele[0]['#required'] = $field->getFieldRequired();
                // Remove container labels if any defined by the element
                if (array_key_exists('#title', $form_ele[0])) {
                    $form_ele['#title'] = $form_ele[0]['#title'];
                    unset($form_ele[0]['#title']);
                }
                if (array_key_exists('#description', $form_ele[0])) {
                    $form_ele['#description'] = $form_ele[0]['#description'];
                    unset($form_ele[0]['#description']);
                }
            }
            
            $form[$field_name] = $form_ele;
            $_fields[$field_name] = $field;
        }
        
        if ($wrap) {
            $form = array($wrap => array('#tree' => true) + $form);
        }
        $form += array(
            '#fields' => $_fields,
            '#inherits' => array('entity_form'),
            '#bundle' => $bundle,
            '#entity' => $entity,
            '#wrap' => $wrap,
            '#token_id' => 'entity_form',
        );

        return $form;
    }

    private function _getEntityFormElement(Sabai $application, Sabai_Addon_Entity_Model_Bundle $bundle, Sabai_Addon_Field_IField $field, Sabai_Addon_Entity_IEntity $entity = null, $key = null, $value = null, $setDefaultValue = true, $admin = false, $wrap = false)
    {
        if (!$iwidget = $application->Field_WidgetImpl($field->getFieldWidget(), true)) {
            return false;
        }
        // Init widget settings
        $widget_settings = $field->getFieldWidgetSettings() + (array)$iwidget->fieldWidgetGetInfo('default_settings');
        $parents = $wrap ? array($wrap) : array();
        $parents[] = $field->getFieldName();
        if (isset($key)) {
            $parents[] = $key;
        }
        if (!$ele = $iwidget->fieldWidgetGetForm($field, $widget_settings, $bundle, $value, $entity, $parents, $admin)) {
            // do not display this form element
            return false;
        }

        // Let other add-ons to modify configuration
        $ele = $application->Filter('entity_field_widget_form', $ele, array($entity, $field, $value, $field->getFieldWidget(), $widget_settings, $admin));

        $class = 'sabai-entity-field ' . str_replace('_', '-', 'sabai-entity-field-type-' . $field->getFieldType() . ' sabai-entity-field-name-' . $field->getFieldName());
        if (!isset($ele['#class'])) {
            $ele['#class'] = $class;
        } else {
            $ele['#class'] .= ' ' . $class;
        }
        if ($setDefaultValue) {
            if (!isset($ele['#default_value'])) {
                $default_value = $field->getFieldDefaultValue();
                if (!$iwidget->fieldWidgetGetInfo('accept_multiple')) {
                    $default_value = $default_value[0];
                }
                if (method_exists($iwidget, 'fieldWidgetSetDefaultValue')) {
                    $iwidget->fieldWidgetSetDefaultValue($field, $widget_settings, $ele, $default_value);
                } else {
                    $ele['#default_value'] = $default_value;
                }
            }
        } else {
            if ($value === null
                && (!isset($ele['#entity_set_default_value']) || $ele['#entity_set_default_value'])
            ) { // set default value to null if no entity value
                $ele['#default_value'] = null;
            }
        }
        // Make the field not required by default. This will be overriden by the actual setting if needed.
        $ele['#required'] = false;

        return $ele;
    }

    private function _getEntityFormAddElementLink(Sabai_Addon_Field_IField $field, array $options, $maxNum, $nextIndex, $wrap)
    {
        return array(
            '#type' => 'item',
            '#markup' => sprintf(
                '<a href="#" class="sabai-btn sabai-btn-default sabai-btn-xs sabai-form-field-add" data-field-name="%s" data-field-max-num="%d" data-field-next-index="%d" data-field-form-wrap="%s"><i class="fa fa-plus"></i> %s</a>',
                $field->getFieldName(),
                $maxNum,
                $nextIndex,
                $wrap ? $wrap : '',
                isset($options['label']) ? Sabai::h($options['label']) : __('Add More', 'sabai')
            ),
            '#class' => 'sabai-form-field-add',
        );
    }
}