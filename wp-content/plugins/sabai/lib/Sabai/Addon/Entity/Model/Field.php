<?php
class Sabai_Addon_Entity_Model_Field extends Sabai_Addon_Entity_Model_Base_Field
    implements Sabai_Addon_Field_IField
{
    private $_data;
    
    public function __toString()
    {
        $title = $this->getFieldTitle();
        return strlen($title) ? $title : (string)$this->getFieldLabel();
    }
    
    public function getFieldId()
    {
        return $this->id;
    }

    public function getFieldType()
    {
        return $this->FieldConfig->type;
    }

    public function getFieldName()
    {
        return $this->FieldConfig->name;
    }

    public function getFieldLabel()
    {
        return (null === $label = $this->getFieldData('label')) ? (string)$this->getFieldData('admin_title') : $label;
    }
        
    public function getFieldView($view = 'default')
    {
        $_view = $this->getFieldData('view');
        return isset($_view[$view]) ? $_view[$view] : null;
    }
    
    public function getFieldTitle($view = 'default')
    {
        $_title = $this->getFieldData('title');
        return is_array($_title) ? (string)@$_title[$view] : $_title; // check if array for 1.2
    }
     
    public function getFieldTitleType($view = 'default')
    {
        $_title_type = (array)$this->getFieldData('title_type');
        return (string)@$_title_type[$view];
    }

    public function getFieldSettings()
    {
        return (array)$this->FieldConfig->settings;
    }

    public function isPropertyField()
    {
        return $this->FieldConfig->property;
    }

    public function getFieldStorage()
    {
        return $this->FieldConfig->storage;
    }

    public function getFieldDescription()
    {
        return $this->getFieldData('description');
    }

    public function getFieldRequired()
    {
        return (bool)$this->getFieldData('required');
    }

    public function getFieldDisabled()
    {
        return (bool)$this->getFieldData('disabled');
    }
    
    public function getFieldMaxNumItems()
    {
        return (int)$this->getFieldData('max_num_items');
    }

    public function getFieldWidget()
    {
        return $this->getFieldData('widget');
    }

    public function getFieldWidgetSettings()
    {
        $widget = $this->getFieldData('widget');
        $settings = (array)$this->getFieldData('widget_settings');
        return isset($settings[$widget]) ? $settings[$widget] : array();
    }
    
    public function getFieldRenderer($view = 'default')
    {
        $renderer = $this->getFieldData('renderer');
        return is_array($renderer) ? @$renderer[$view] : $renderer;
    }

    public function getFieldRendererSettings($view = 'default', $renderer = null)
    {
        $settings = (array)$this->getFieldData('renderer_settings');
        return isset($renderer) ? (array)@$settings[$view][$renderer] : (array)@$settings[$view];
    }
        
    public function getFieldDefaultValue()
    {
        return $this->getFieldData('default_value');
    }

    public function getFieldWeight()
    {
        return (int)$this->getFieldData('weight');
    }

    public function setFieldType($type)
    {
        $this->FieldConfig->type = $type;

        return $this;
    }

    public function setFieldName($name)
    {
        $this->FieldConfig->name = $name;

        return $this;
    }
    
    public function setFieldSettings(array $settings)
    {
        $this->FieldConfig->settings = $settings;

        return $this;
    }
    
    public function setFieldMaxNumItems($num)
    {
        return $this->setFieldData('max_num_items', (int)$num);
    }

    public function setFieldWeight($weight)
    {
        return $this->setFieldData('weight', (int)$weight);
    }

    public function setFieldStorage($storage)
    {
        $this->FieldConfig->storage = $storage;

        return $this;
    }

    public function setFieldWidget($widget)
    {
        return $this->setFieldData('widget', $widget);
    }

    public function setFieldWidgetSettings(array $settings)
    {
        $widget_settings = (array)$this->getFieldData('widget_settings');
        $widget_settings[$this->getFieldWidget()] = $settings;
        return $this->setFieldData('widget_settings', $widget_settings);
    }

    public function onCommit()
    {
        parent::onCommit();
        if (isset($this->_data)) $this->data = $this->_data;
    }

    private function &_getFieldData()
    {
        if (!isset($this->_data)) {
            if (!$this->data
                || (!$this->_data = $this->data)
            ) {
                $this->_data = array();
            }
        }

        return $this->_data;
    }

    public function getFieldData($name)
    {
        $data = $this->_getFieldData();

        return array_key_exists($name, $data) ? $data[$name] : null;
    }

    public function setFieldData($name, $value)
    {
        $data =& $this->_getFieldData();
        $data[$name] = $value;

        return $this->markDirty();
    }
    
    public function hasFieldData($name)
    {
        $data = $this->_getFieldData();

        return array_key_exists($name, $data);
    }
    
    public function isCustomField()
    {
        return strpos($this->getFieldName(), 'field_') === 0;
    }
}

class Sabai_Addon_Entity_Model_FieldRepository extends Sabai_Addon_Entity_Model_Base_FieldRepository
{
}