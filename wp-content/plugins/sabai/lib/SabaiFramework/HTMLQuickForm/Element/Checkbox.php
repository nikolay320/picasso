<?php
require_once 'HTML/QuickForm/checkbox.php';

class SabaiFramework_HTMLQuickForm_Element_Checkbox extends HTML_QuickForm_checkbox
{    
    public function __construct($elementName = null, $elementLabel = null, $text = '', $attributes = null)
    {
        parent::HTML_QuickForm_checkbox($elementName, $elementLabel, $text, $attributes);
    }

    /*
     * Overrides the parent method to cope with the bug below
     * http://pear.php.net/bugs/bug.php?id=15298
     */
    public function getValue()
    {
        return $this->getAttribute('value');
    }
}