<?php
class SabaiFramework_Application_Url implements ArrayAccess
{
    private $_data = array();

    public function __construct($scriptUrl, array $params = array(), $fragment = '', $separator = '&amp;')
    {
        $this->_data = array(
            'script_url' => $scriptUrl,
            'params' => $params,
            'fragment' => $fragment,
            'separator' => $separator,
        );
    }

    public function &__get($name)
    {
        return $this->_data[$name];
    }

    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }
    
    public function set($name, $value)
    {
        $this->_data[$name] = $value;
        return $this;
    }

    public function __toString()
    {
        if (!empty($this->_data['params'])
            && ($query_str = http_build_query($this->_data['params'], null, $this->_data['separator']))
        ) {
            $query_str = strtr($query_str, array('%7E' => '~', '+' => '%20')); // http_query_query does urlencode, so need a little adjustment for RFC3986 compat
            if (strpos($this->_data['script_url'], '?')) {
                $url = $this->_data['script_url'] . $query_str;
            } else {
                $url = $this->_data['script_url'] . '?' . $query_str;
            }
        } else {
            $url = $this->_data['script_url'];
        }

        return strlen($this->_data['fragment']) ? $url . '#' . rawurlencode($this->_data['fragment']) : $url;
    }

    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->_data[$offset];
    }
}