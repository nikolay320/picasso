<?php
class Sabai_Addon_Form_Field_Grid extends Sabai_Addon_Form_Field_AbstractField
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!isset($data['#template']) && empty($data['#disable_template_override'])) {
        // Modify template slightly so that the field decription is displayed at the top of the table.
            $data['#template'] = '<div<!-- BEGIN id --> id="{id}"<!-- END id --> class="sabai-form-field<!-- BEGIN class --> {class}<!-- END class -->">
  <!-- BEGIN label --><div class="sabai-form-field-label"><span>{label}</span><!-- BEGIN required --><span class="sabai-form-field-required">*</span><!-- END required --></div><!-- END label -->
  <!-- BEGIN label_2 --><div class="sabai-form-field-description sabai-form-field-description-top">{label_2}</div><!-- END label_2 -->
  <!-- BEGIN error_msg --><span class="sabai-form-field-error">{error}</span><!-- END error_msg -->
  {element}
</div>';
        }
        $element = $form->createHTMLQuickformElement('grid', $name, $data['#label'], array('actAsGroup' => true));
        $element->updateAttributes($data['#attributes']);
        $element->setAttribute('class', 'sabai-table');
        $element->setEmptyText(isset($data['#empty_text']) ? $data['#empty_text'] : __('No records found.', 'sabai'));
        // Define columns
        $columns = array();
        foreach (array_keys($data['#children']) as $weight) {
            if (!is_int($weight)) continue;

            foreach (array_keys($data['#children'][$weight]) as $column_name) {
                $columns[$column_name] =& $data['#children'][$weight][$column_name];
                $element->addColumnName($columns[$column_name]['#title'], @$data['#column_attributes'][$column_name]);
                $columns[$column_name]['#title'] = '';
            }
        }

        if (empty($data['#default_value'])) {
            // $data['#_default_value'] is required during submit
            $data['#_default_value'] = array();
            $data['#default_value'] = null;
            return $element;
        }

        // Add rows
        foreach (array_keys($data['#default_value']) as $i) {
            $row = $row_attr = array();
            $row_attr = isset($data['#row_attributes'][$i]) ? $data['#row_attributes'][$i] : array();
            foreach (array_keys($columns) as $column_index => $column_name) {
                $column_settings = $columns[$column_name];
                // Append column specific settings if any
                if (!empty($data['#row_settings'][$i][$column_name])) {
                    $column_settings = $data['#row_settings'][$i][$column_name] + $column_settings;
                }
                if (isset($data['#default_value'][$i][$column_name])) {
                    $column_settings += array('#default_value' => $data['#default_value'][$i][$column_name]);
                }
                if (isset($data['#value'][$i][$column_name])) {
                    $column_settings += array('#value' => $data['#value'][$i][$column_name]);
                }
                // Always prepend element name of the grid
                $column_settings['#tree'] = true;
                $column_settings['#tree_allow_override'] = false;
                if ($column_settings['#type'] !== 'radio' || empty($column_settings['#single_value'])) {
                    $_name = sprintf('%s[%s][%s]', $name, $i, $column_name);
                } else {
                    // Only single value allowed for this column
                    $_name = sprintf('%s[0][%s]', $name, $column_name);
                }
                $row[$column_index] = $form->createElement($column_settings['#type'], $_name, $column_settings);
                // Update the new settings for this column
                $data['#row_settings'][$i][$column_name] = $column_settings;
				$row_attr[$column_index] = isset($data['#row_attributes'][$i][$column_name]) ? $data['#row_attributes'][$i][$column_name] :array();
                if (isset($data['#row_attributes']['@all'][$column_name])) {
                    $row_attr[$column_index] += $data['#row_attributes']['@all'][$column_name];
                }
            }

            $element->addRow($row, $row_attr);
        }
        // $data['#_default_value'] is required during submit
        $data['#_default_value'] = $data['#default_value'];
        $data['#default_value'] = $data['#value'] = null;

        return $element;
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!is_array($value)) {
            $value = array();
        }
        
        // Process child elements
        foreach (array_keys($data['#children']) as $weight) {
            if (!is_int($weight)) continue;

            foreach (array_keys($data['#children'][$weight]) as $ele_key) {
                $ele_data =& $data['#children'][$weight][$ele_key];
                foreach (array_keys($data['#_default_value']) as $i) {
                    if (!isset($value[$i]) || !array_key_exists($ele_key, $value[$i])) {
                        $value[$i][$ele_key] = null;
                    }
                    $ele_name = sprintf('%s[%s][%s]', $name, $i, $ele_key);

                    // Custom settings for this column?
                    if (!empty($data['#row_settings'][$i][$ele_key])) {
                        $ele_data = array_merge($ele_data, $data['#row_settings'][$i][$ele_key]);
                    }

                    // Send form submit notification to the element.
                    try {
                        $this->_addon->getApplication()->Form_FieldImpl($ele_data['#type'])->formFieldOnSubmitForm($ele_name, $value[$i][$ele_key], $ele_data, $form);
                    } catch (Sabai_IException $e) {
                        // Catch any application level exception that might occur and display it as a form element error.
                        $form->setError($e->getMessage(), $ele_name);
                    }

                    // Any error?
                    if ($form->hasError($ele_name)) continue;

                    // Copy the value to be used in subsequent validations
                    $ele_value =& $value[$i][$ele_key];

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
    }

    public function formFieldOnCleanupForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        // Process child elements
        foreach (array_keys($data['#children']) as $weight) {
            if (!is_int($weight)) continue;
            
            foreach (array_keys($data['#children'][$weight]) as $ele_name) {
                $ele_data =& $data['#children'][$weight][$ele_name];
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
        $form->renderElement($data);
        $form->renderChildElements($name, $data);
    }
}