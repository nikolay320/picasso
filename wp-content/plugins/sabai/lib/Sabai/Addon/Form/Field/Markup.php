<?php
class Sabai_Addon_Form_Field_Markup extends Sabai_Addon_Form_Field_AbstractField
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!isset($data['#markup'])) {
            if (!isset($data['#value'])) {
                $data['#markup'] = isset($data['#default_value']) ? $data['#default_value'] : '';
            } else {
                $data['#markup'] = $data['#value'];
            }
        }
        $data['#value'] = $data['#default_value'] = null;
        $data['#template'] = false; // no template

        return $form->createHTMLQuickformElement('static', $name, null, $data['#markup']);
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnCleanupForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnRenderForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
    }
}