<?php
abstract class Sabai_Addon_Entity_Entity implements Sabai_Addon_Entity_IEntity
{
    public $data = array();
    protected $_bundleName, $_bundleType, $_properties, $_contentField, $_contentColumn, $_fieldValues = array(), $_fieldTypes = array(), $_fieldsLoaded = false, $_fromCache = false;
    
    public function __construct($bundleName, $bundleType, array $properties, $contentField, $contentColum = 'value')
    {
        $this->_bundleName = $bundleName;
        $this->_bundleType = $bundleType;
        $this->_properties = $properties;
        $this->_contentField = $contentField;
        $this->_contentColumn = $contentColum;
    }
    
    public function getBundleName()
    {
        return $this->_bundleName;
    }
    
    public function getBundleType()
    {
        return $this->_bundleType;
    }
    
    public function getContentField()
    {
        return $this->_contentField;
    }
    
    public function addFieldValue($name, $value)
    {
        $this->_fieldValues[$name][] = $value;
        return $this;
    }
    
    public function getFieldValue($name)
    {
        return isset($this->_fieldValues[$name]) ? $this->_fieldValues[$name] : (isset($this->_properties[$name]) ? array($this->_properties[$name]) : null);
    }
    
    public function getSingleFieldValue($name, $key = null, $index = 0)
    {
        return isset($key) ? @$this->_fieldValues[$name][$index][$key] : @$this->_fieldValues[$name][$index];
    }

    public function getFieldValues($withProperty = false)
    {
        return $withProperty ? $this->_properties + $this->_fieldValues : $this->_fieldValues;
    }
    
    public function getFieldType($name)
    {
        return $this->_fieldTypes[$name];
    }

    public function getFieldTypes($unique = true)
    {
        return $unique ? array_unique($this->_fieldTypes) : $this->_fieldTypes;
    }
    
    public function getFieldNamesByType($type)
    {
        return array_keys($this->_fieldTypes, $type);
    }
    
    public function initFields(array $values, array $types)
    {
        $this->_fieldValues = $values;
        $this->_fieldTypes = $types;
        $this->_fieldsLoaded = true;

        return $this;
    }
    
    public function isFieldsLoaded()
    {
        return $this->_fieldsLoaded;
    }
    
    public function __get($name)
    {
        return $this->getFieldValue($name);
    }
    
    public function __isset($name)
    {
        return isset($this->_fieldValues[$name]);
    }
    
    public function __unset($name)
    {
        unset($this->_fieldValues[$name]);
    }
    
    public function __toString()
    {
        return $this->getTitle();
    }
        
    public function serialize()
    {
        return serialize(array($this->_bundleName, $this->_bundleType, $this->_properties));
    }

    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);
        $this->_bundleName = $unserialized[0];
        $this->_bundleType = $unserialized[1];
        $this->_properties = $unserialized[2];
        $this->_fromCache = true;
    }
    
    public function isFromCache()
    {
        return $this->_fromCache;
    }
    
    public function getContent()
    {
        return (string)$this->getSingleFieldValue($this->_contentField, $this->_contentColumn);
    }
}