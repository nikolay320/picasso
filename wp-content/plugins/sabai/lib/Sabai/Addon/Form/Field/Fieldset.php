<?php
class Sabai_Addon_Form_Field_Fieldset extends Sabai_Addon_Form_Field_AbstractField
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (isset($form->settings['#tree_allow_override']) && !$form->settings['#tree_allow_override']) {
            // #tree setting not allowed to be overridden for the whole form
            $data['#tree_allow_override'] = false;
            $data['#tree'] = $form->settings['#tree'];
        } elseif (!empty($form->settings['#tree']) && !isset($data['#tree'])) {
            // inherit form #tree setting
            $data['#tree'] = true;
        }
        if (!array_key_exists('#position', $data)) {
            $data['#position'] = null;
        }
        $elements = array();
        foreach (array_keys($data['#children']) as $weight) {
            if (!is_int($weight)) continue;
            foreach (array_keys($data['#children'][$weight]) as $_key) {
                $_data =& $data['#children'][$weight][$_key];
                // Make the child element required/disabled if not set as not required explicitly and the fieldset is set as required/disabled
                if (!isset($_data['#required']) && isset($data['#required'])) {
                    $_data['#required'] = $data['#required'];
                }
                if (!isset($_data['#disabled']) && !empty($data['#disabled'])) {
                    $_data['#disabled'] = true;
                }
                // Append parent element name if #tree is true for the parent element and #tree is not set to false explicitly for the current element
                if (!empty($data['#tree']) && (!$data['#tree_allow_override'] || false !== @$_data['#tree'])) {
                    $_data['#tree'] = true;
                    if (is_int($_key) && !empty($data['#nowrap'])) {
                        $_name = $name;
                    } else {
                        $_name = sprintf('%s[%s]', $name, $_key);
                        if (!isset($_data['#value']) && isset($data['#value'][$_key])) {
                            $_data['#value'] = $data['#value'][$_key];
                        }
                        if (!isset($_data['#default_value']) && isset($data['#default_value'][$_key])) {
                            $_data['#default_value'] = $data['#default_value'][$_key];
                        }
                    }
                } else {
                    $_name = $_key;
                }
                $_data['#tree_allow_override'] = $data['#tree_allow_override'];
                if ($element = $form->createElement($_data['#type'], $_name, $_data)) {
                    $elements[] = $element;
                }
            }
        }
        $data['#value'] = $data['#default_value'] = null;

        return empty($elements) ? null : $form->createHTMLQuickformElement('group', $name, $data['#label'], $elements, $data['#position']);
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        // Process child elements
        foreach (array_keys($data['#children']) as $weight) {
            if (!is_int($weight)) continue;

            foreach (array_keys($data['#children'][$weight]) as $ele_key) {
                $ele_data =& $data['#children'][$weight][$ele_key];
                if (!empty($data['#tree'])) {
                    if (is_int($ele_key) && !empty($data['#nowrap'])) {
                        // Send form submit notification to the element
                        try {
                            $this->_addon->getApplication()->Form_FieldImpl($ele_data['#type'])->formFieldOnSubmitForm($name, $value, $ele_data, $form);
                        } catch (Sabai_IException $e) {
                            // Catch any application level exception that might occur and display it as a form element error.
                            $form->setError($e->getMessage(), $name);
                        }

                        if ($form->hasError($name)) break;
                        
                        // Process custom validations if any
                        foreach ($ele_data['#element_validate'] as $callback) {
                            try {
                                $this->_addon->getApplication()->CallUserFuncArray($callback, array($form, &$value, $ele_data));
                            } catch (Sabai_IException $e) {
                                $form->setError($e->getMessage(), $ele_data);
                            }
                        }
                        
                        break;
                    }
                 
                    if (is_null($value) || !array_key_exists($ele_key, $value)) {
                        $value[$ele_key] = null;
                    }
                    $ele_name = sprintf('%s[%s]', $name, $ele_key);

                    // Send form submit notification to the element
                    try {
                        $this->_addon->getApplication()->Form_FieldImpl($ele_data['#type'])->formFieldOnSubmitForm($ele_name, $value[$ele_key], $ele_data, $form);
                    } catch (Sabai_IException $e) {
                        // Catch any application level exception that might occur and display it as a form element error.
                        $form->setError($e->getMessage(), $ele_name);
                    }

                    // Any error?
                    if ($form->hasError($ele_name)) continue;

                    // Copy the value to be used in subsequent validation steps
                    $ele_value =& $value[$ele_key];
                } else {
                    $ele_name = $ele_key;
                    // Since the name of element does not belongs to the group name hierarchy, we must fetch the element's value from the global scope.
                    if (!isset($form->values[$ele_name])) {
                        $form->values[$ele_name] = null;
                    }

                    // Send form submit notification to the element.
                    try {
                        $this->_addon->getApplication()->Form_FieldImpl($ele_data['#type'])->formFieldOnSubmitForm($ele_name, $form->values[$ele_name], $ele_data, $form);
                    } catch (Sabai_IException $e) {
                        // Catch any application level exception that might occur and display it as a form element error.
                        $form->setError($e->getMessage(), $ele_name);
                    }

                    // Any error?
                    if ($form->hasError($ele_name)) continue;

                    // Copy the value to be used in subsequent validation steps
                    $ele_value =& $form->values[$ele_name];
                }

                // Process custom validations if any
                foreach ($ele_data['#element_validate'] as $callback) {
                    try {
                        $this->_addon->getApplication()->CallUserFuncArray($callback, array($form, &$ele_value, $ele_data));
                    } catch (Sabai_IException $e) {
                        $form->setError($e->getMessage(), $ele_data);
                    }
                }
            }
        }
    }

    public function formFieldOnCleanupForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        // Process child elements
        foreach (array_keys($data['#children']) as $weight) {
            if (!is_int($weight)) continue;
            
            foreach (array_keys($data['#children'][$weight]) as $ele_name) {
                $ele_data =& $data['#children'][$weight][$ele_name];
                if (is_int($ele_name) && !empty($data['#nowrap'])) {
                    try {
                        $this->_addon->getApplication()->Form_FieldImpl($ele_data['#type'])->formFieldOnCleanupForm($name, $ele_data, $form);
                    } catch (Exception $e) {
                        // Catch any exception that might be thrown so that all elements are cleaned up properly.
                        $this->_addon->getApplication()->LogError($e);
                    }
                    break;
                }
                if (!empty($data['#tree'])) {
                    $ele_name = sprintf('%s[%s]', $name, $ele_name);
                }
                try {
                    $this->_addon->getApplication()->Form_FieldImpl($ele_data['#type'])->formFieldOnCleanupForm($ele_name, $ele_data, $form);
                } catch (Exception $e) {
                    // Catch any exception that might be thrown so that all elements are cleaned up properly.
                    $this->_addon->getApplication()->LogError($e);
                }
            }
        }
    }

    public function formFieldOnRenderForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $child_count = 0;
        foreach (array_keys($data['#children']) as $key) {
            if (false === strpos($key, '#')) {
                ++$child_count;
            }
        }
        if ($child_count === 0) {
            // No child elements, so tempty template to render nothing
            $data['#template'] = '';
            $form->renderElement($data);
        } else {        
            $form->renderElement($data);
            $form->renderChildElements($name, $data);
        }
    }
}