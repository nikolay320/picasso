<?php
class Sabai_Addon_Directory_Helper_DirectoryList extends Sabai_Helper
{
    private $_addonNames;
    
    public function help(Sabai $application, $key = null, $name = false)
    {
        switch ($key) {
            case 'review':
                $func = 'getReviewBundleName';
                break;
            case 'photo':
                $func = 'getPhotoBundleName';
                break;
            case 'category':
                $func = 'getCategoryBundleName';
                break;
            case 'addon':
                $func = 'getName';
                break;
            default:
                $func = 'getListingBundleName';
        }
        $addon = $application->getAddon('Directory');
        $options = array($application->getAddon('Directory')->$func() => $name ? $addon->getName() : $addon->getTitle('directory'));
        // Fetch cloned directory add-ons
        if (!isset($this->_addonNames)) {
            $this->_addonNames = $application->getModel('Addon', 'System')->parentAddon_is('Directory')->fetch()->getArray('name');
        }
        foreach ($this->_addonNames as $addon_name) {
            $options[$application->getAddon($addon_name)->$func()] = $name ? $addon_name : $application->getAddon($addon_name)->getTitle('directory');
        }
        
        return $options;
    }
}