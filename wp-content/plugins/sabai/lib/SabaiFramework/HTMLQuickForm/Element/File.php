<?php
require_once 'HTML/QuickForm/file.php';

class SabaiFramework_HTMLQuickForm_Element_File extends HTML_QuickForm_file
{
    protected $_multiple = false;
    
    public function __construct($elementName = null, $elementLabel = null, $attributes = null)
    {
        parent::HTML_QuickForm_file($elementName, $elementLabel, $attributes);
    }
    
    public function setMultiple($flag = true)
    {
        $this->_multiple = (bool)$flag;
        if ($flag) {
            $this->setAttribute('multiple', 'multiple');
        } else {
            $this->removeAttribute('multiple');
        }
    }
    
    public function toHtml()
    {        
        if (!$this->_multiple) {
            $attr = $this->_getAttrString($this->_attributes);
        } else {
            $name = $this->getName();
            $this->setName($name . '[]');
            $attr = $this->_getAttrString($this->_attributes);
            $this->setName($name);
        }
        
        return $this->_getTabs() . '<input' . $attr . ' />';
    }
}