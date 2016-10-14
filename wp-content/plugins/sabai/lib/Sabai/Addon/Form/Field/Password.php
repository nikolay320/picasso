<?php
class Sabai_Addon_Form_Field_Password extends Sabai_Addon_Form_Field_AbstractField
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        unset($data['#char_validation'], $form['regex']);
        $this->_addon->initTextFieldSettings($form, $data);
        $data['#attributes']['autocomplete'] = 'off';
        $element = $form->createHTMLQuickformElement('password', $name, $data['#label'], $data['#attributes']);
        if (empty($data['#redisplay'])) {
            // Do not display value in HTML to prevent password theft
            $data['#value'] = '';
        }

        return $element;
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!empty($data['#char_validation'])
            && in_array($data['#char_validation'], array('integer', 'numeric', 'alnum', 'alpha'))
        ) {
            $data['#' . $data['#char_validation']] = true;
        }

        if (false !== $validated = $this->_addon->getApplication()->Form_ValidateText($form, $value, $data)) {
            $value = $validated;
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