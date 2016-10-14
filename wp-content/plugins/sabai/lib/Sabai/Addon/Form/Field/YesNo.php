<?php
class Sabai_Addon_Form_Field_YesNo extends Sabai_Addon_Form_Field_Radios
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!isset($data['#options'])) {
            $data['#options'] = array(1 => __('Yes', 'sabai'), 0 => __('No', 'sabai'));
        }
        if (!isset($data['#empty_value'])) {
            $data['#empty_value'] = 0;
        }
        if (isset($data['#class'])) {
            $data['#class'] .= ' sabai-form-inline sabai-form-type-radios';
        } else {
            $data['#class'] = 'sabai-form-inline sabai-form-type-radios';
        }
        return parent::formFieldGetFormElement($name, $data, $form);
    }
}