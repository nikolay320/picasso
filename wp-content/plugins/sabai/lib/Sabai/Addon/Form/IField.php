<?php
interface Sabai_Addon_Form_IField
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form);
    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form);
    public function formFieldOnCleanupForm($name, array &$data, Sabai_Addon_Form_Form $form);
    public function formFieldOnRenderForm($name, array &$data, Sabai_Addon_Form_Form $form);
}