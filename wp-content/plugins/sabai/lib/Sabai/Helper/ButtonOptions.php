<?php
class Sabai_Helper_ButtonOptions extends Sabai_Helper
{
    public function help(Sabai $application, $label, $size = 'mini')
    {
        $ret = array();
        foreach (array('sabai-btn-default', 'sabai-btn-primary', 'sabai-btn-info' , 'sabai-btn-success', 'sabai-btn-warning', 'sabai-btn-danger') as $value) {
            $ret[$value] = sprintf(' <button class="sabai-btn sabai-btn-%s %s" onclick="return false;">%s</button>', $size, $value, $label);
        }
        
        return $ret;
    }
}