<?php
class Sabai_Addon_Form_Helper_SubmitButtons extends Sabai_Helper
{    
    public function help(Sabai $application, array $buttons = null, $defaultCallback = null, $defaultCallbackWeight = Sabai_Addon_Form::FORM_CALLBACK_WEIGHT_DEFAULT)
    {
        $submits = array(
            '#tree' => true,
            '#weight' => 99999,
            '#class' => 'sabai-form-buttons sabai-form-inline',
        );
        if (!isset($buttons)) {
            $buttons = array(array('#value' => __('Submit', 'sabai'), '#btn_type' => 'primary'));
        }
        // Add submit button and cancel link
        foreach ($buttons as $name => $button) {
            $submits[$name] = $button + array('#type' => 'submit', '#attributes' => array('style' => 'display:inline;'));
            if ($submits[$name]['#type'] !== 'submit') {
                if (!isset($submits[$name]['#tree'])) {
                    // Do not prefix with FORM_SUBMIT_BUTTON_NAME
                    $submits[$name]['#tree'] = false;
                }
                continue;
            }
            if (!isset($submits[$name]['#class'])) {
                $submits[$name]['#class'] = 'sabai-form-action';
            } else {
                $submits[$name]['#class'] .= ' sabai-form-action';
            }
            if (isset($defaultCallback) && !isset($submits[$name]['#submit'])) {
                $submits[$name]['#submit'] = array(
                    $defaultCallbackWeight => $defaultCallback,
                );
            }
        }
        return $submits;
    }
}