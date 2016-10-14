<?php
class Sabai_Addon_Entity_Model_Filter extends Sabai_Addon_Entity_Model_Base_Filter
{ 
    public function isCustomFilter()
    {
        return strpos($this->name, 'field_') === 0 || !empty($this->data['is_custom']);
    }
    
    public function __set($name, $value)
    {
        parent::__set($name, $value);
        if ($name === 'Field' && is_object($value)) {
            $this->bundle_id = $value->bundle_id;
        }
    }
    
    public function settings(array $settings)
    {
        return $this->data['settings'] + $settings;
    }
    
    public function getLabel()
    {
        return isset($this->data['title']) && strlen($this->data['title']) ? $this->data['title'] : (string)@$this->data['label'];
    }
}

class Sabai_Addon_Entity_Model_FilterRepository extends Sabai_Addon_Entity_Model_Base_FilterRepository
{
}