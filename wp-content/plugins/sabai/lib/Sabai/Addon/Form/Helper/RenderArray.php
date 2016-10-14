<?php
class Sabai_Addon_Form_Helper_RenderArray extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Form_Form $form, $extraJs = '')
    {
        return $application->Form_Build($form->settings, !$form->rebuild, $form->values, $form->getErrors())->renderArray();
    }
}