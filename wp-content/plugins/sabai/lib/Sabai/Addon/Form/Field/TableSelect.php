<?php
class Sabai_Addon_Form_Field_TableSelect extends Sabai_Addon_Form_Field_AbstractField
{
    private static $_preRenderCallbackAdded = false;
    
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $element = $form->createHTMLQuickformElement('grid', $name, $data['#label'], array('actAsGroup' => false));
        $element->updateAttributes($data['#attributes']);
        $element->setAttribute('class', 'sabai-table sabai-table-hover');
        $element->setEmptyText(isset($data['#empty_text']) ? $data['#empty_text'] : __('No records found.', 'sabai'));

        // Define checkbox/radio element to be added to the first column of each row
        if (!$data['#disabled']) {
            // Define columns
            if (!empty($data['#multiple'])) {
                $select_element_setting = array(
                    '#type' => 'checkbox',
                    '#value' => $data['#value'],
                );
                // Add a check-all button?
                if (!isset($data['#js_select']) || false !== $data['#js_select']) {
                    $element->addColumnName('<input type="checkbox" class="sabai-form-check-trigger" />', array('class' => 'sabai-form-check'));
                    $select_element_setting['#attributes']['class'] = 'sabai-form-check-target';
                } else {
                    $element->addColumnName('', array('class' => 'sabai-form-check'));
                }
            } else {
                $element->addColumnName('', array('class' => 'sabai-form-check'));
                $select_element_setting = array(
                    '#type' => 'radio',
                    '#value' => $data['#value'],
                );
            }
        }

        // Add header
        $i = 0;
        foreach ($data['#header'] as $header_name => $header_label) {
            if (!is_array($header_label)) {
                $i += 10;
                $data['#header'][$header_name] = array(
                    'label' => $header_label,
                    'order' => $i,
                );
            } elseif (!isset($header_label['order'])) {
                $i += 10;
                $data['#header'][$header_name]['order'] = $i;
            }
        }
        uasort($data['#header'], array(__CLASS__, 'sortHeaders'));
        foreach ($data['#header'] as $header_name => $header_label) {
            $element->addColumnName($header_label['label'], @$data['#column_attributes'][$header_name]);
        }

        // Add rows
        if (!isset($data['#options_disabled'])) {
            $data['#options_disabled'] = array();
        }
        if (!empty($data['#options'])) {
            if (!$data['#disabled']) {
                $select_element_setting = $select_element_setting + $form->defaultElementSettings();
            }
            $item_element_setting = array('#type' => 'item') + $form->defaultElementSettings();
            foreach ($data['#options'] as $option_id => $option_labels) {
                if (!is_array($option_labels)) {
                    if (!$option_labels = @array_combine(array_keys($data['#header']), explode(',', $option_labels))) {
                        continue;
                    }
                    $data['#options'][$option_id] = $option_labels;
                }
                $_name = sprintf('%s[0]', $name);
                if (!$data['#disabled']) {
                    $_select_element_setting = $select_element_setting;
                    $_select_element_setting['#on_value'] = $option_id;

                    // Set selected?
                    if (isset($data['#default_value'])) {
                        if (!empty($data['#multiple'])) {
                            $_select_element_setting['#default_value'] = (array)$data['#default_value'];
                        } else {
                            if (in_array($option_id, (array)$data['#default_value'])) {
                                $_select_element_setting['#default_value'] = $option_id;
                            }
                        }
                    }

                    // Disable?
                    if (in_array($option_id, $data['#options_disabled'])) {
                        // Since all select elements in the field have the same field name, we need to manually disable the element
                        // by adding the disabled attribute to disable each element individually
                        $_select_element_setting['#attributes']['disabled'] = 'disabled'; // not allowed to select this option
                        unset($_select_element_setting['#disabled']);
                    } else {
                        unset($_select_element_setting['#attributes']['disabled'], $_select_element_setting['#disabled']);
                    }

                    $row = array($form->createElement($_select_element_setting['#type'], $_name, $_select_element_setting));
                } else {
                    $row = array();
                }
                $row_attr = isset($data['#row_attributes'][$option_id]) ? $data['#row_attributes'][$option_id] : array();
                $row_attr[0]['class'] = 'sabai-form-check';
                foreach (array_keys($data['#header']) as $header_name) {
                    $_item_element_setting = $item_element_setting;
                    $_item_element_setting['#markup'] = isset($option_labels[$header_name]) ? $option_labels[$header_name] : '';
                    if (is_int($column_index = $header_name)) {
                        // Prevent overwriting the first element in row
                        $column_index = $header_name + 1;
                    }
                    $row[$column_index] = $form->createElement($_item_element_setting['#type'], $_name . '[' . $header_name . ']', $_item_element_setting);
                    if (isset($data['#row_attributes']['@all'][$header_name])) {
                        if (!isset($row_attr[$column_index])) {
                            $row_attr[$column_index] = $data['#row_attributes']['@all'][$header_name];
                        } else {
                            $row_attr[$column_index] += $data['#row_attributes']['@all'][$header_name];
                        }
                    }
                }
                $element->addRow($row, $row_attr);
            }
        }
        $data['#default_value'] = $data['#value'] = null;
        if (!isset($data['#prefix'])) {
            $data['#prefix'] = '';
        }
        $data['#prefix'] = '<div class="sabai-table-responsive">' . $data['#prefix'];
        if (!isset($data['#suffix'])) {
            $data['#suffix'] = '';
        }
        $data['#suffix'] .= '</div>';
        
        if (!self::$_preRenderCallbackAdded) {
            $form->settings['#pre_render'][] = array($this, 'preRenderCallback');
            self::$_preRenderCallbackAdded = true;
        }

        return $element;
    }
    
    public function sortHeaders($a, $b)
    {
        return $a['order'] < $b['order'] ? -1 : 1; 
    }
    
    public function sortOptions($a, $b)
    {
        if (!isset($a['#weight']) || !isset($b['#weight'])) {
            return 0;
        }
        return $a['#weight'] < $b['#weight'] ? -1 : 1; 
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        // Is it a required field?
        if (is_null($value)) {
            if ($form->isFieldRequired($data)) {
                $form->setError(__('Selection required.', 'sabai'), $name);
            }

            return;
        }

        // No options
        if (empty($data['#options'])) return;

        // The submitted value comes wrapped with an additional layer of array,
        // so we remove that here to get the right one.
        $value = $value[0];

        // Are all the selected options valid?
        foreach ((array)$value as $_value) {
            if (empty($data['#skip_validate_option']) && !isset($data['#options'][$_value])) {
                $form->setError(__('Invalid option selected.', 'sabai'), $name);

                return;
            }
        }
    }

    public function formFieldOnCleanupForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnRenderForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!empty($data['#sortable'])) {
            $data['#id'] = $form->getFieldId($name);
            $form->addJs(sprintf(
                'jQuery(document).ready(function($){
    $("#%s").find("tbody").sortable({
        containment: "parent",
        axis: "y",
        update: function (event, ui) {}
    });
});',
                $data['#id']
            ));
        }
        $form->renderElement($data);
    }
    
    public function preRenderCallback($form)
    {        
        $this->_addon->getApplication()->LoadJqueryUi(array('sortable'));
    }
}