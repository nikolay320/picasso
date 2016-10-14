<?php
class Sabai_Addon_Field extends Sabai_Addon
    implements Sabai_Addon_Field_ITypes,
               Sabai_Addon_Field_IWidgets,
               Sabai_Addon_Field_IRenderers,
               Sabai_Addon_Field_IFilters,
               Sabai_Addon_System_IAdminSettings
{
    const VERSION = '1.3.28', PACKAGE = 'sabai';
    const COLUMN_TYPE_INTEGER = 'integer', COLUMN_TYPE_BOOLEAN = 'boolean', COLUMN_TYPE_VARCHAR = 'text',
        COLUMN_TYPE_TEXT = 'clob', COLUMN_TYPE_DECIMAL = 'decimal';

    public function fieldGetTypeNames()
    {
        return array('boolean', 'number', 'string', 'text', 'user', 'html', 'choice', 'captcha',
            'sectionbreak', 'link', 'range', 'video', 'email', 'phone');
    }

    public function fieldGetType($name)
    {
        switch ($name) {
            case 'boolean':
                return new Sabai_Addon_Field_Type_Boolean($this, $name);
            case 'number':
                return new Sabai_Addon_Field_Type_Number($this, $name);
            case 'string':
                return new Sabai_Addon_Field_Type_String($this, $name);
            case 'text':
                return new Sabai_Addon_Field_Type_Text($this, $name);
            case 'user':
                return new Sabai_Addon_Field_Type_User($this, $name);
            case 'html':
                return new Sabai_Addon_Field_Type_HTML($this, $name);
            case 'choice':
                return new Sabai_Addon_Field_Type_Choice($this, $name);
            case 'captcha':
                return new Sabai_Addon_Field_Type_CAPTCHA($this, $name);
            case 'sectionbreak':
                return new Sabai_Addon_Field_Type_SectionBreak($this, $name);
            case 'link':
                return new Sabai_Addon_Field_Type_Link($this, $name);
            case 'range':
                return new Sabai_Addon_Field_Type_Range($this, $name);
            case 'video':
                return new Sabai_Addon_Field_Type_Video($this, $name);
            case 'email':
                return new Sabai_Addon_Field_Type_Email($this, $name);
            case 'phone':
                return new Sabai_Addon_Field_Type_Phone($this, $name);
        }
    }

    public function fieldGetWidgetNames()
    {
        $ret = array('textfield', 'textarea', 'select', 'radiobuttons', 'checkboxes',
            'checkbox', 'user_select', 'html', 'sectionbreak', 'link', 'range', 'slider',
            'video', 'email', 'phone');
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            $ret[] = 'recaptcha';
        }
        return $ret;
    }

    public function fieldGetWidget($name)
    {
        switch ($name) {
            case 'textfield':
                return new Sabai_Addon_Field_Widget_Textfield($this, $name);
            case 'textarea':
                return new Sabai_Addon_Field_Widget_Textarea($this, $name);
            case 'select':
                return new Sabai_Addon_Field_Widget_Select($this, $name);
            case 'radiobuttons':
                return new Sabai_Addon_Field_Widget_RadioButtons($this, $name);
            case 'checkboxes':
                return new Sabai_Addon_Field_Widget_Checkboxes($this, $name);
            case 'checkbox':
                return new Sabai_Addon_Field_Widget_Checkbox($this, $name);
            case 'user_select':
                return new Sabai_Addon_Field_Widget_User($this, $name);
            case 'html':
                return new Sabai_Addon_Field_Widget_HTML($this, $name);
            case 'sectionbreak':
                return new Sabai_Addon_Field_Widget_SectionBreak($this, $name);
            case 'link':
                return new Sabai_Addon_Field_Widget_Link($this, $name);
            case 'range':
                return new Sabai_Addon_Field_Widget_Range($this, $name);
            case 'slider':
                return new Sabai_Addon_Field_Widget_Slider($this, $name);
            case 'video':
                return new Sabai_Addon_Field_Widget_Video($this, $name);
            case 'user_select':
                return new Sabai_Addon_Field_Widget_User($this, $name);
            case 'email':
                return new Sabai_Addon_Field_Widget_Email($this, $name);
            case 'phone':
                return new Sabai_Addon_Field_Widget_Phone($this, $name);
            case 'recaptcha':
                return new Sabai_Addon_Field_Widget_reCAPTCHA($this, $name);
        }
    }
    
    public function fieldGetRendererNames()
    {
        return array('string', 'number', 'choice', 'text', 'boolean', 'user', 'link', 'range', 'video', 'phone', 'email');
    }

    public function fieldGetRenderer($name)
    {
        switch ($name) {
            case 'text':
                return new Sabai_Addon_Field_Renderer_Text($this, $name);
            case 'video':
                return new Sabai_Addon_Field_Renderer_Video($this, $name);
            default:
                return new Sabai_Addon_Field_Renderer_Default($this, $name);
        }
    }
    
    public function fieldGetFilterNames()
    {
        return array('option', 'keyword', 'boolean', 'number', 'range');
    }

    public function fieldGetFilter($name)
    {
        switch ($name) {
            case 'option':
                return new Sabai_Addon_Field_Filter_Option($this, $name);
            case 'keyword':
                return new Sabai_Addon_Field_Filter_Keyword($this, $name);
            case 'boolean':
                return new Sabai_Addon_Field_Filter_Boolean($this, $name);
            case 'number':
                return new Sabai_Addon_Field_Filter_Number($this, $name);
            case 'range':
                return new Sabai_Addon_Field_Filter_Range($this, $name);
        }
    }

    public function onFieldITypesInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        $this->_application->getPlatform()
            ->deleteCache('field_types')
            ->setOption('field_types', array($addon->getName() => $addon->fieldGetTypeNames()) + $this->_getFieldTypes());
    }

    public function onFieldITypesUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        $this->_application->getPlatform()->deleteCache('field_types');
        $field_types = $this->_getFieldTypes();
        if (isset($field_types[$addon->getName()])) {
            foreach ($field_types[$addon->getName()] as $field_type_deleted) {
                $this->_application->Action('field_type_deleted', array($field_type_deleted));
            }
            unset($field_types[$addon->getName()]);
            $this->_application->getPlatform()->setOption('field_types', $field_types);
        }
    }

    public function onFieldITypesUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        $this->_application->getPlatform()->deleteCache('field_types');
        $field_types = $this->_getFieldTypes();
        if (isset($field_types[$addon->getName()])) {
            foreach (array_diff($field_types[$addon->getName()], $addon->fieldGetTypeNames()) as $field_type_deleted) {
                $this->_application->Action('field_type_deleted', array($field_type_deleted));
            }
        }
        $this->_application->getPlatform()->setOption('field_types', array($addon->getName() => $addon->fieldGetTypeNames()) + $field_types);
    }
    
    protected function _getFieldTypes()
    {
        $field_types = $this->_application->getPlatform()->getOption('field_types');
        if (!is_array($field_types)) {
            // For compat with version 1.2.x or lower
            $field_types = array();
            $db = $this->_application->getDB();
            $sql = sprintf('SELECT type_name, type_addon FROM %sfield_type', $db->getResourcePrefix());
            try {
                $rs = $db->query($sql);
                while ($row = $rs->fetchRow()) {
                    $field_types[$row[1]][] = $row[0];
                }
            } catch (SabaiFramework_DB_QueryException $e) {
                
            }
        }
        return $field_types;
    }

    public function onFieldIWidgetsInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        $this->_application->getPlatform()->deleteCache('field_types')->deleteCache('field_widgets');
    }

    public function onFieldIWidgetsUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        $this->_application->getPlatform()->deleteCache('field_types')->deleteCache('field_widgets');
    }

    public function onFieldIWidgetsUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        $this->_application->getPlatform()->deleteCache('field_types')->deleteCache('field_widgets');
    }
    
    public function onFieldIRenderersInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        $this->_application->getPlatform()->deleteCache('field_types')->deleteCache('field_renderers');
    }

    public function onFieldIRenderersUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        $this->_application->getPlatform()->deleteCache('field_types')->deleteCache('field_renderers');
    }

    public function onFieldIRenderersUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        $this->_application->getPlatform()->deleteCache('field_types')->deleteCache('field_renderers');
    }
    
    public function onFieldIFiltersInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        $this->_application->getPlatform()->deleteCache('field_types')->deleteCache('field_filters');
    }

    public function onFieldIFiltersUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        $this->_application->getPlatform()->deleteCache('field_types')->deleteCache('field_filters');
    }

    public function onFieldIFiltersUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        $this->_application->getPlatform()->deleteCache('field_types')->deleteCache('field_filters');
    }
    
    public function onFieldUpgradeSuccess(Sabai_Addon $addon, $log, $previousVersion)
    {
        $this->_application->Field_Upgrade($log, $previousVersion);
    }
    
    public function getDefaultConfig()
    {
        return array('recaptcha' => array('sitekey' => '', 'secret' => ''));
    }
    
    public function systemGetAdminSettingsForm()
    {
        return array(
            'recaptcha' => array(
                '#tree' => true,
                '#collapsible' => false,
                'sitekey' => array(
                    '#title' => __('reCAPTCHA Site Key', 'sabai'),
                    '#type' => 'textfield',
                    '#default_value' => $this->_config['recaptcha']['sitekey'],
                ),
                'secret' => array(
                    '#title' => __('reCAPTCHA Secret Key', 'sabai'),
                    '#type' => 'textfield',
                    '#default_value' => $this->_config['recaptcha']['secret'],
                ),
            ),
        );
    }
}