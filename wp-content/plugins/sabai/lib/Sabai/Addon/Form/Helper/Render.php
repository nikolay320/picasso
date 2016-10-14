<?php
class Sabai_Addon_Form_Helper_Render extends Sabai_Helper
{
    public function help(Sabai $application, $form, $extraJs = '', $elementsOnly = false)
    {
        if ($form instanceof Sabai_Addon_Form_Form) {
            $settings = $form->settings;
            $rebuild = !$form->rebuild;
            $values = $form->values;
            $errors = $form->getErrors();
        } elseif (is_array($form)) {
            $settings = $form;
            $rebuild = true;
            $values = null;
            $errors = array();
        } else {
            return '';
        }
        list($html, $js) = $application->Form_Build($settings, $rebuild, $values, $errors)->render($elementsOnly);
        return !$js && !strlen($extraJs) ? $html : sprintf('
%s
<script type="text/javascript">
%s
%s
</script>', $html, $js, $extraJs);
    }
}