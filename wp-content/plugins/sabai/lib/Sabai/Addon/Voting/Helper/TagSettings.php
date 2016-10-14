<?php
class Sabai_Addon_Voting_Helper_TagSettings extends Sabai_Helper
{
    private $_settings = array();
    
    public function help(Sabai $application, $tag = null)
    {
        if (isset($tag)) {
            if (!isset($this->settings[$tag])) {
                if (!$field_config = $application->getModel('FieldConfig', 'Entity')->name_is('voting_' . $tag)->fetchOne()) {
                    throw new Sabai_UnexpectedValueException('Invalid voting type: ' . $tag);
                }
                $this->_settings[$tag] = $field_config->settings;
            }
            return $this->_settings[$tag];
        }
        foreach ($application->getModel('FieldConfig', 'Entity')->type_in($application->getAddon('Voting')->fieldGetTypeNames())->fetch() as $field_config) {
            $this->_settings[$field_config->settings['tag']] = $field_config->settings;
        }
        return $this->_settings;
    }
}