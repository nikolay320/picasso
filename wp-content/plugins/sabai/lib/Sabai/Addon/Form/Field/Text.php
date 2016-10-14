<?php
class Sabai_Addon_Form_Field_Text extends Sabai_Addon_Form_Field_AbstractField
{
    static private $_maskedElements = array(), $_maskedInputJsLoaded = false;
    
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (isset($data['#separator'])) {
            // value is an array, so it must be converted to a string
            if (isset($data['#default_value']) && is_array($data['#default_value'])) {
                $data['#default_value'] = implode($data['#separator'], $data['#default_value']);
            }
            if (isset($data['#value']) && is_array($data['#value'])) {
                $data['#value'] = implode($data['#separator'], $data['#value']);
            }
        }
        if (isset($data['#placeholder'])) {
            $data['#attributes']['placeholder'] = $data['#placeholder'];
        }
        
        $this->_addon->initTextFieldSettings($form, $data);

        $attr = $data['#attributes'];
        $type = $data['#type'];
        switch ($data['#type']) {
            case 'number':
            case 'range':
                if (isset($data['#min_value'])) {
                    $attr['min'] = $data['#min_value'];
                }
                if (isset($data['#max_value'])) {
                    $attr['max'] = $data['#max_value'];
                }
                if (isset($data['#step'])) {
                    $attr['step'] = $data['#step'];
                }
                break;
            case 'email':
                break;
            case 'url':
                break;
            default:
                $type = 'text';
        }

        if (!empty($data['#mask'])) {
            // Register pre render callback if this is the first date element
            if (!isset(self::$_maskedElements[$form->settings['#id']])) {
                $form->settings['#pre_render'][] = array($this, 'preRenderCallback');
            }
            if (!isset($data['#id'])) {
                $data['#id'] = $form->getFieldId($name);
            }
            self::$_maskedElements[$form->settings['#id']][$name] = array(
                'id' => $data['#id'],
                'mask' => $data['#mask'],
            );
            if (!isset($data['#class'])) {
                $data['#class'] = '';
            }
            $data['#class'] .= ' sabai-form-type-textfield-masked';
            $attr['data-mask'] = $data['#mask'];
        }
        
        $element = $form->createHTMLQuickformElement('text', $name, $data['#label'], $attr);
        $element->setType($type);
        return $element;
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        $application = $this->_addon->getApplication();
        if (isset($data['#separator'])) {
            $value = explode($data['#separator'], $value);
            foreach (array_keys($value) as $key) {
                if (false === $validated = $application->Form_ValidateText($form, $value[$key], $data, null, false)) {
                    return;
                }
                if (!strlen($validated)) {
                    unset($value[$key]);
                    continue;
                }
                $value[$key] = $validated;
            }
            if (empty($value)) {
                if ($form->isFieldRequired($data)) {
                    $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : __('Please fill out this field.', 'sabai'), $name);

                    return;
                }
            } else {
                if (!empty($data['#max_selection'])) {
                    if (count($value) > $data['#max_selection']) {
                        $form->setError(sprintf(__('Maximum of %d items is allowed for this field.', 'sabai'), $data['#max_selection']), $name);
                    }
                }
            }
        } else {
            if (false !== $validated = $application->Form_ValidateText($form, $value, $data)) {
                $value = $validated;
            }
        }
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
        if (!empty(self::$_maskedElements[$form->settings['#id']])) return;
        
        if (!self::$_maskedInputJsLoaded) {
            $this->_addon->getApplication()->LoadJs('jquery.maskedinput.min.js', 'jquery-maskedinput', array('jquery'));
            self::$_maskedInputJsLoaded = true;
        }
        
        $js = array();
        foreach (self::$_maskedElements[$form->settings['#id']] as $data) {
            $js[] = '$("#'. $data['id'] .' input[type=text]").mask("' . $data['mask'] . '");';
        }
        // Add js
        $form->addJs(sprintf(
            'jQuery(document).ready(function ($) {
    $(SABAI).bind("clonefield.sabai", function (e, data) {
        if (data.clone.hasClass("sabai-form-type-textfield-masked")) {
            var input = data.clone.removeAttr("id").find("input[type=text]");
            input.mask(input.data("mask"));
        }
    });
    %s
});',
            implode(PHP_EOL, $js)
        ));
    }
}