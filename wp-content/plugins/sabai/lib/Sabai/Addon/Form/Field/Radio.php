<?php
class Sabai_Addon_Form_Field_Radio extends Sabai_Addon_Form_Field_AbstractField
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!isset($data['#on_value'])) $data['#on_value'] = 1;
        if (isset($data['#on_label'])) {
            $data['#options'] = array($data['#on_value'] => empty($data['#title_no_escape']) ? $data['#on_label'] : Sabai::h($data['#on_label']));
        } else {
            $data['#options'] = array($data['#on_value'] => $data['#label'][0]);
            $data['#label'][0] = ''; // remove the title part of the label
        }
        $attr = array($data['#on_value'] => $data['#attributes']); // altselect element attributes must be set like this
        return $form->createHTMLQuickformElement('altselect', $name, $data['#label'], $data['#options'] , $attr);
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        // Is it a required field?
        if (is_null($value)) {
            if ($form->isFieldRequired($data)) {
                $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : sprintf(__('Selection required.', 'sabai'), $data['#label'][0]), $name);
            }

            return;
        }

        if ($value != $data['#on_value']) {
            $form->setError(sprintf(__('Invalid option selected.', 'sabai'), $data['#label'][0]), $name);
        }
    }

    public function formFieldOnCleanupForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnRenderForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
    }
}