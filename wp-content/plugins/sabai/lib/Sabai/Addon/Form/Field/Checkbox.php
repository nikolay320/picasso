<?php
class Sabai_Addon_Form_Field_Checkbox extends Sabai_Addon_Form_Field_Radio
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $element = parent::formFieldGetFormElement($name, $data, $form);
        $element->setMultiple(true);

        return $element;
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        // Is it a required field?
        if (!is_array($value) || count($value) === 0) {
            if ($form->isFieldRequired($data)) {
                $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : sprintf(__('Selection required.', 'sabai'), $data['#label'][0]), $name);
            }

            $value = isset($data['#off_value']) ? $data['#off_value'] : false;

            return;
        }

        if ($value[0] != $data['#on_value']) {
            $form->setError(sprintf(__('Invalid option selected.', 'sabai'), $data['#label'][0]), $name);

            return;
        }

        $value = $value[0];
    }
}