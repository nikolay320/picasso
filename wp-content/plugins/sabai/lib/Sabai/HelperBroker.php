<?php
class Sabai_HelperBroker extends SabaiFramework_Application_HelperBroker
{    
    public function helperExists($name)
    {
        if (parent::helperExists($name)) return true; // helper found

        if (strpos($name, '_', 1)) {
            // Search addon's helper directory
            if ((list($addon_name, $helper_name) = explode('_', $name))
                && $this->_application->isAddonLoaded($addon_name)
            ) {
                $class = 'Sabai_Addon_' . $addon_name . '_Helper_' . $helper_name;
                if (!class_exists($class, false)) {
                    require $this->_application->getAddonPath($addon_name) . '/Helper/' . $helper_name . '.php';
                }
                $this->setHelper($name, array(new $class(), 'help'));

                return true;
            }
        }
        
        // Is it a normal function?
        if (function_exists($name)) {
            $this->setHelper($name, $name);
        }

        return false;
    }
}