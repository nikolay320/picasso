<?php
class Sabai_Addon_Form_Helper_Fields extends Sabai_Helper
{
    /**
     * Returns all available form fields
     * @param Sabai $application
     */
    public function help(Sabai $application, $useCache = true)
    {
        if (!$useCache
            || (!$form_fields = $application->getPlatform()->getCache('form_fields'))
        ) {
            $form_fields = array();
            foreach ($application->getInstalledAddonsByInterface('Sabai_Addon_Form_IFields') as $addon_name) {
                if (!$application->isAddonLoaded($addon_name)) continue;
                
                foreach ($application->getAddon($addon_name)->formGetFieldTypes() as $field_type) {
                    if (!$application->getAddon($addon_name)->formGetField($field_type)) {
                        continue;
                    }
                    $form_fields[$field_type] = $addon_name;
                }
            }
            $application->getPlatform()->setCache($form_fields, 'form_fields');
        }

        return $form_fields;
    }
}