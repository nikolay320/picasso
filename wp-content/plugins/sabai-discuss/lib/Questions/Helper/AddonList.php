<?php
class Sabai_Addon_Questions_Helper_AddonList extends Sabai_Helper
{
    private $_addonNames;
    
    public function help(Sabai $application, $key = null)
    {
        switch ($key) {
            case 'answer':
                $func = 'getAnswersBundleName';
                break;
            case 'category':
                $func = 'getCategoriesBundleName';
                break;
            case 'tag':
                $func = 'getTagsBundleName';
                break;
            case 'question':
                $func = 'getQuestionsBundleName';
                break;
            default:
                $func = 'getName';
        }
        $options = array($application->getAddon('Questions')->$func() => $application->getAddon('Questions')->getTitle('questions'));
        // Fetch cloned directory add-ons
        if (!isset($this->_addonNames)) {
            $this->_addonNames = $application->getModel('Addon', 'System')->parentAddon_is('Questions')->fetch()->getArray('name');
        }
        foreach ($this->_addonNames as $addon_name) {
            $options[$application->getAddon($addon_name)->$func()] = $application->getAddon($addon_name)->getTitle('questions');
        }
        
        return $options;
    }
}