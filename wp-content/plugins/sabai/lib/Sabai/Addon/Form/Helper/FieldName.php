<?php
class Sabai_Addon_Form_Helper_FieldName extends Sabai_Helper
{
    /**
     * @param Sabai $application
     * @param array $names
     */
    public function help(Sabai $application, array $names)
    {
        $ret = (string)array_shift($names);
        foreach ($names as $name) {
            $ret .= '[' . $name . ']';
        }

        return $ret;
    }
}