<?php
class Sabai_Addon_Questions_Helper_QuestionsList extends Sabai_Helper
{
    private $_addonNames;
    
    public function help(Sabai $application, $key = null, $name = false)
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
            case 'addon':
                $func = 'getName';
                break;
            default:
                $func = 'getQuestionsBundleName';
        }
        $addon = $application->getAddon('Questions');
        $options = array($addon->$func() => $name ? $addon->getName() : $application->getAddon('Questions')->getTitle('questions'));
        // Fetch cloned questions add-ons
        if (!isset($this->_addonNames)) {
            $this->_addonNames = $application->getModel('Addon', 'System')->parentAddon_is('Questions')->fetch()->getArray('name');
        }
        foreach ($this->_addonNames as $addon_name) {
            $options[$application->getAddon($addon_name)->$func()] = $name ? $addon_name : $application->getAddon($addon_name)->getTitle('questions');
        }
        
        return $options;
    }
}