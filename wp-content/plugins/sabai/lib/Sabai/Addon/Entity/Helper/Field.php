<?php
class Sabai_Addon_Entity_Helper_Field extends Sabai_Helper
{
    private $_fields = array();
    
    /**
     * Returns a field object of an entity 
     * @param Sabai $application
     * @param Sabai_Addon_Entity_Entity|string $entityOrBundleName
     * @param string $fieldName
     */
    public function help(Sabai $application, $entityOrBundleName, $fieldName = null)
    {
        if ($entityOrBundleName instanceof Sabai_Addon_Entity_IEntity) {
            $entityOrBundleName = $entityOrBundleName->getBundleName();
        } elseif (!is_string($entityOrBundleName)) {
            throw new Sabai_InvalidArgumentException();
        }
        // Check if fields for the entity are already loaded
        if (!isset($this->_fields[$entityOrBundleName])) {
            // Load fields
            $this->_fields[$entityOrBundleName] = array();
            foreach ($application->Entity_Bundle($entityOrBundleName)->with('Fields', 'FieldConfig')->Fields as $field) {
                $this->_fields[$entityOrBundleName][$field->getFieldName()] = $field;
            }
        }
        return isset($fieldName) ? @$this->_fields[$entityOrBundleName][$fieldName] : $this->_fields[$entityOrBundleName];
    }
}