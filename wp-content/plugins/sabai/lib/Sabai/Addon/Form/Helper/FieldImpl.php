<?php
if (!class_exists('SabaiFramework_HTMLQuickForm', false)) {
    require 'SabaiFramework/HTMLQuickForm.php';
}

class Sabai_Addon_Form_Helper_FieldImpl extends Sabai_Helper
{
    private $_impls = array();

    /**
     * Gets an implementation of Sabai_Addon_Form_IField interface for a field type
     * @param Sabai $application
     * @param string $field
     */
    public function help(Sabai $application, $field, $returnFalse = false, $useCache = true)
    {
        if (!isset($this->_impls[$field])) {
            $fields = $application->Form_Fields($useCache);
            // Valid field type?
            if (!isset($fields[$field])
                || (!$application->isAddonLoaded($fields[$field]))
            ) {
                // for deprecated renderer
                if ($field === 'file_file') {
                    return $this->help($application, 'file', $returnFalse, $useCache);
                }
                
                if ($returnFalse) return false;
                throw new Sabai_UnexpectedValueException(sprintf('Invalid form field type: %s', $field));
            }
            $this->_impls[$field] = $application->getAddon($fields[$field])->formGetField($field);
        }

        return $this->_impls[$field];
    }
}