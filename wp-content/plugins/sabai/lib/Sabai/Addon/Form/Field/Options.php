<?php
class Sabai_Addon_Form_Field_Options extends Sabai_Addon_Form_Field_AbstractField
{
    private static $_preRenderCallbackAdded = false;
    
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!isset($data['#template'])) {
            // Modify template slightly so that the field decription is displayed at the top of the table.
            $data['#template'] = '<div<!-- BEGIN id --> id="{id}"<!-- END id --> class="sabai-form-field<!-- BEGIN class --> {class}<!-- END class -->">
  <!-- BEGIN label --><div class="sabai-form-field-label"><span>{label}</span><!-- BEGIN required --><span class="sabai-form-field-required">*</span><!-- END required --></div><!-- END label -->
  <!-- BEGIN label_2 --><div class="sabai-form-field-description sabai-form-field-description-top">{label_2}</div><!-- END label_2 -->
  <!-- BEGIN error_msg --><span class="sabai-form-field-error">{error}</span><!-- END error_msg -->
  {element}
</div>';
        }
        $data['#id'] = $form->getFieldId($name);
        if (!empty($data['#multiple'])) {
            $type = 'checkbox';
            $default_name = '[default][]';
        } else {
            $type = 'radio';
            $default_name = '[default]';
        }
        $input = array('<li class="sabai-form-field-option">');
        $input[] = '<input type="%9$s" name="%1$s%10$s" value="%2$d"%5$s%14$s />';
        $input[] = '<input type="text" name="%1$s[options][%2$d][label]" value="%3$s" size="15" placeholder="%7$s" class="sabai-form-field-option-label" />';
        $input[] = '<input type="text" name="%1$s[options][%2$d][value]" value="%4$s" size="15" placeholder="%8$s" class="sabai-form-field-option-value" />';
        if (empty($data['#disable_add'])) {
            $input[] = '<a href="#" class="sabai-btn sabai-btn-xs sabai-btn-success" onclick="SABAI.addOption(\'#%6$s ul\', \'%1$s\', this, %11$s); return false;"><i class="fa fa-plus"></i></a>';
        }
        if (empty($data['#disable_remove'])) {
            $input[] = '<a href="#" class="sabai-btn sabai-btn-xs sabai-btn-danger" onclick="SABAI.removeOption(\'#%6$s ul\', this, \'%12$s\'); return false;"><i class="fa fa-minus"></i></a>';
        }
        if (empty($data['#disable_sort'])) {
            $input[] = '<span class="sabai-btn sabai-btn-xs sabai-btn-default%13$s"><i class="fa fa-arrows-v"></i></span>';
        }
        $input[] = '</li>';
        $input = implode(PHP_EOL, $input);
    
        $inputs = array();
        $label_title = isset($data['#label_title']) ? Sabai::h((string)$data['#label_title']) : __('Label', 'sabai');
        $value_title = isset($data['#value_title']) ? Sabai::h((string)$data['#value_title']) : __('Value', 'sabai');
        if (!empty($data['#default_value']['options'])) {
            foreach ($data['#default_value']['options'] as $key => $option) {
                if (!strlen(@$option['label']) && !strlen(@$option['value'])) {
                    unset($data['#default_value']['options'][$key]);
                }
            }
        }
        if (!empty($data['#default_value']['options'])) {
            $first_option = current($data['#default_value']['options']);
            if (!is_array($first_option)) {
                // not coming from request, probably from saved values
                $new_options = array();
                foreach ($data['#default_value']['options'] as $value => $label) {
                    $new_options[] = array('value' => $value, 'label' => $label);
                }
                $data['#default_value']['options'] = $new_options;
            }
            $data['#_default_values'] = isset($data['#default_value']['default']) ? (array)$data['#default_value']['default'] : array();
            if ($options_value_disabled = isset($data['#options_value_disabled']) ? $data['#options_value_disabled'] : array()) {
                $input_disabled = array('<li class="sabai-form-field-option sabai-form-field-option-disabled">');
                $input_disabled[] = '<input type="%9$s" name="%1$s%10$s" value="%2$d"%5$s%14$s />';
                $input_disabled[] = '<input type="text" name="%1$s[options][%2$d][label]" value="%3$s" size="15" placeholder="%7$s" class="sabai-form-field-option-label" />';
                $input_disabled[] = '<input type="text" value="%4$s" size="15" placeholder="%8$s" disabled="disabled" />';
                $input_disabled[] = '<input type="hidden" name="%1$s[options][%2$d][value]" value="%4$s" class="sabai-form-field-option-value" />';
                if (empty($data['#disable_add'])) {
                    $input_disabled[] = '<a href="#" class="sabai-btn sabai-btn-xs sabai-btn-success sabai-disabled" onclick="return false;"><i class="fa fa-plus"></i></a>';
                }
                if (empty($data['#disable_remove'])) {
                    $input_disabled[] = '<a href="#" class="sabai-btn sabai-btn-xs sabai-btn-danger sabai-disabled" onclick="return false;"><i class="fa fa-minus"></i></a>';
                }
                if (empty($data['#disable_sort'])) {
                    $input_disabled[] = '<span class="sabai-btn sabai-btn-xs sabai-btn-default%13$s"><i class="fa fa-arrows-v"></i></span>';
                }
                $input_disabled[] = '</li>';
                $input_disabled = implode(PHP_EOL, $input_disabled);
            }
            $options_disabled = isset($data['#options_disabled']) ? $data['#options_disabled'] : array();
            $options_sort_disabled = isset($data['#options_sort_disabled']) ? $data['#options_sort_disabled'] : array();
            foreach ($data['#default_value']['options'] as $key => $option) {
                $inputs[] = sprintf(
                    in_array($option['value'], $options_value_disabled) ? $input_disabled : $input,
                    $name,
                    $key,
                    Sabai::h($option['label']),
                    Sabai::h($option['value']),
                    in_array($option['value'], $data['#_default_values']) ? ' checked="checked"' : '',
                    $data['#id'],
                    $label_title,
                    $value_title,
                    $type,
                    $default_name,
                    $type === 'checkbox' ? 'true' : 'false',
                    __('Are you sure?', 'sabai'),
                    in_array($option['value'], $options_sort_disabled) ? ' sabai-disabled' : '',
                    in_array($option['value'], $options_disabled) ? ' disabled="disabled"' : ''
                );
            }
            if (empty($data['#disable_add_more'])) {
                $inputs[] = sprintf(
                    $input,
                    $name,
                    ++$key,
                    '',
                    '',
                    $type === 'checkbox' ? ' checked="checked"' : '',
                    $data['#id'],
                    $label_title,
                    $value_title,
                    $type,
                    $default_name,
                    $type === 'checkbox' ? 'true' : 'false',
                    __('Are you sure?', 'sabai'),
                    '',
                    in_array($option['value'], $options_disabled) ? ' disabled="disabled"' : ''
                );
            }
            $data['#options'] = array_keys($data['#default_value']);
        } else {
            for ($i = 0; $i < 3; $i++) {
                $inputs[] = sprintf(
                    $input,
                    $name,
                    $i,
                    '',
                    '',
                    '',
                    $data['#id'],
                    $label_title,
                    $value_title,
                    $type,
                    $default_name,
                    $type === 'checkbox' ? 'true' : 'false',
                    __('Are you sure?', 'sabai'),
                    '',
                    ''
                );
            }
            $data['#options'] = array();
        }
        $markup = array('<ul style="margin:0 0 5px;">' . implode(PHP_EOL, $inputs) . '</ul>');
        $markup[] = '<button class="sabai-btn sabai-btn-xs sabai-btn-success" style="display:block;"><i class="fa fa-plus"></i> ' . __('Add from CSV') . '</button>';
        $markup[] = sprintf(
            '<textarea placeholder="%s" name="%s[csv]" rows="2" style="margin-top:10px; width:100%%; display:none;"></textarea>',
            str_repeat($label_title . ',' . $value_title . PHP_EOL, 2),
            $name
        );
        $data['#default_value'] = $data['#value'] = null;
        $data['#markup'] = implode(PHP_EOL, $markup);
        
        $form->addJs('jQuery(document).ready(function($){
    $("#'. $data['#id'] .'").find("ul").sortable({handle:".sabai-btn-default", containment:"parent", axis:"y", cancel:".sabai-disabled"}).end()
        .find("button").click(function(){
            var $this = $(this), tarea = $this.next();
            if (tarea.is(":visible")) {
                tarea.slideUp("fast");
                $this.removeClass("sabai-active").blur();
            } else {
                tarea.slideDown("fast").autosize().focus();
                $this.addClass("sabai-active");
            }
            return false;
        });
});');
        
        if (!self::$_preRenderCallbackAdded) {
            $form->settings['#pre_render'][] = array($this, 'preRenderCallback');
            self::$_preRenderCallbackAdded = true;
        }
        
        return $form->createHTMLQuickformElement('static', $name, $data['#label'], $data['#markup']);
    }
    
    protected function _loadCsvData(&$value)
    {
        $value['csv'] = trim($value['csv']);
        if (!strlen($value['csv'])) return;
        
        foreach (explode(PHP_EOL, $value['csv']) as $line) {
            if ($line = trim($line)) {
                $_line = array_map('trim', explode(',', $line));
                if (isset($_line[0]) && strlen($_line[0])) {
                    $value['options'][] = array(
                        'label' => $_line[0],
                        'value' => isset($_line[1]) && strlen($_line[1]) ? $_line[1] : $_line[0],
                    );
                }
            }
        }
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (isset($value['csv'])) {
            $this->_loadCsvData($value);
        }
        $options = array();
        $default_value = array();
        if (!isset($value['default'])) {
            $value['default'] = array();
        } else {
            settype($value['default'], 'array');
        }
        if (!empty($data['#options_disabled']) && !empty($data['#_default_values'])) {
            // Add options that are disabled but selected by default
            foreach ($data['#options_disabled'] as $option_disabled) {
                if (in_array($option_disabled, $data['#_default_values'])) {
                    $default_value[] = $option_disabled;
                }
            }
        }
        foreach ((array)@$value['options'] as $key => $option) {
            $option['value'] = trim($option['value']);
            if (!strlen($option['value'])) {
                continue;
            }
            if (isset($data['#value_regex']) && strlen($data['#value_regex'])) {
                if (!preg_match($data['#value_regex'], $option['value'])) {
                    $error = isset($data['#value_regex_error_message'])
                        ? $data['#value_regex_error_message']
                        : sprintf(__('The input value did not match the regular expression: %s', 'sabai'), $data['#value_regex']);
                    $form->setError($error, $name);
                }
            }
            $options[$option['value']] = $option['label'];
            if (in_array($key, $value['default']) && !in_array($option['value'], $default_value)) {
                $default_value[] = $option['value'];
            }
        }
        
        if (empty($options)) {
            if ($form->isFieldRequired($data)) {
                $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : __('Please fill out this field.', 'sabai'), $name);
            }
        } else {
            if (empty($default_value)
                && !empty($data['#require_default'])
            ) {
                $form->setError(isset($data['#default_required_error_message']) ? $data['#default_required_error_message'] : __('Please select at least one option.', 'sabai'), $name);
            }
        }
        
        $value = array('options' => $options, 'default' => $default_value);
    }

    public function formFieldOnCleanupForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnRenderForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
    }
    
    public function preRenderCallback($form)
    {        
        $this->_addon->getApplication()->LoadJqueryUi(array('sortable'));
    }
}