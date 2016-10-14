<?php
class Sabai_Addon_Form_Field_Textarea extends Sabai_Addon_Form_Field_AbstractField
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $data['#attributes']['rows'] = !empty($data['#rows']) ? $data['#rows'] : 8;
        if (!empty($data['#cols'])) {
            $data['#attributes']['cols'] = $data['#cols'];
            $style_width = 'width:' . ceil($data['#cols'] * 0.7) . 'em;';
        } else {
            $style_width = "width:100%;";
        }
        if (!isset($data['#attributes']['style']['width'])) {
            $data['#attributes']['style'] = $style_width;
        } else {
            $data['#attributes']['style'] .= $style_width;
        }

        return $form->createHTMLQuickformElement('textarea', $name, $data['#label'], $data['#attributes']);
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (false !== $validated = $this->_addon->getApplication()->Form_ValidateText($form, $value, $data, null, true, true)) {
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