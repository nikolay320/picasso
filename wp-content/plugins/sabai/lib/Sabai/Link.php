<?php
class Sabai_Link
{
    private $_url, $_label, $_options, $_attributes;

    public function __construct($url, $label, array $options = array(), array $attributes = array())
    {
        $this->_url = $url;
        $this->_label = $label;
        $this->_options = $options;
        $this->_attributes = $attributes;
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function setUrl($url)
    {
        $this->_url = $url;
        return $this;
    }
    
    public function getLabel()
    {
        return $this->_label;
    }
    
    public function setLabel($label, $escape = true)
    {
        $this->_label = $label;
        $this->_options['no_escape'] = !$escape;
        return $this;
    }
        
    public function isNoEscape()
    {
        return !empty($this->_options['no_escape']);
    }
    
    public function getIcon()
    {
        return isset($this->_options['icon']) ? $this->_options['icon'] : null;
    }
    
    public function setIcon($icon)
    {
        $this->_options['icon'] = $icon;
        return $this;
    }
    
    public function setActive($flag = true)
    {
        $this->_options['active'] = (bool)$flag;
        return $this;
    }
    
    public function isActive()
    {
        return !empty($this->_options['active']);
    }
    
    public function setDisabled($flag = true)
    {
        $this->_options['disabled'] = (bool)$flag;
        return $this;
    }
    
    public function isDisabled()
    {
        return !empty($this->_options['disabled']);
    }
    
    public function getAttribute($key)
    {
        return isset($this->_attributes[$key]) ? $this->_attributes[$key] : null;
    }
    
    public function setAttribute($key, $value)
    {
        $this->_attributes[$key] = $value;
        return $this;
    }

    public function __toString()
    {
        
        $label = empty($this->_options['no_escape']) ? Sabai::h($this->_label) : $this->_label;
        if (isset($this->_options['icon'])) {
            $label = '<i class="fa fa-' . Sabai::h($this->_options['icon']) . '"></i> ' . $label;
        }
        if (!isset($this->_attributes['class'])) {
            $this->_attributes['class'] = '';
        }
        if ($this->isActive()) {
            $this->_attributes['class'] .= ' sabai-active';
        } elseif ($this->isDisabled()) {
            $this->_attributes['class'] .= ' sabai-disabled';
        }
        $attributes = array();
        foreach ($this->_attributes as $k => $v) {
            $attributes[$k] = $k . '="' . Sabai::h($v, ENT_COMPAT) . '"'; // Avoid escaping quotes used in javascript
        }
        $link = '<a href="' . $this->_url . '" ' . implode(' ', $attributes) . '>' . $label . '</a>';
        if (!isset($this->_options['bullet-icon'])) {
            return $link;
        }
        return '<i class="fa fa-' . Sabai::h($this->_options['bullet-icon']) . '"></i> ' . $link;
    }
}
