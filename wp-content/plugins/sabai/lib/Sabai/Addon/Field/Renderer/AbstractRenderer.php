<?php
abstract class Sabai_Addon_Field_Renderer_AbstractRenderer implements Sabai_Addon_Field_IRenderer
{
    protected $_addon, $_name, $_info = array();

    public function __construct(Sabai_Addon $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldRendererGetInfo($key = null)
    {
        if (!isset($this->_info[$this->_name])) {
            $this->_info[$this->_name] = $this->_fieldRendererGetInfo();
        }

        return isset($key) ? @$this->_info[$this->_name][$key] : $this->_info[$this->_name];
    }
    
    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array()){}

    abstract protected function _fieldRendererGetInfo();
}