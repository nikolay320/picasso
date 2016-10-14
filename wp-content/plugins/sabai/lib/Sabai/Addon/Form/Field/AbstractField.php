<?php
abstract class Sabai_Addon_Form_Field_AbstractField implements Sabai_Addon_Form_IField
{
    protected $_addon;

    public function __construct(Sabai_Addon $addon)
    {
        $this->_addon = $addon;
    }
}