<?php
class Sabai_Addon_Questions_Controller_Admin_Emails extends Sabai_Addon_System_Controller_Admin_EmailSettings
{    
    protected function _getEmailSettings(Sabai_Context $context)
    {        
        return $this->Questions_NotificationSettings();
    }
    
    protected function _getCurrentEmailSettings(Sabai_Context $context)
    {
        $settings = parent::_getCurrentEmailSettings($context);
        
        // For backward compat with 1.1.8 or lower versions
        $config = $this->getAddon()->getConfig();
        if (isset($config['emails'])) {
            foreach ($config['emails'] as $name => $_settings) {
                if (isset($_settings['enable'])) {
                    $settings[$name]['enable'] = !empty($_settings['enable']);
                }
                if (isset($_settings['roles'])) {
                    $settings[$name]['roles'] = $_settings['roles'];
                }
                if (isset($_settings['email']['subject'])) {
                    $settings[$name]['email']['subject'] = $_settings['email']['subject'];
                }
                if (isset($_settings['email']['body'])) {
                    $settings[$name]['email']['body'] = $_settings['email']['body'];
                }
            }
        }
        
        return $settings;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        parent::submitForm($form, $context);

        // For backward compat with 1.1.8 or lower versions
        $config = $this->getAddon()->getConfig();
        if (isset($config['emails'])) {
            unset($config['emails']);
            $this->getAddon()->saveConfig($config, false);
            $this->reloadAddons();
        }
    }
}