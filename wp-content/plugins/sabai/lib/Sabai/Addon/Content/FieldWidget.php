<?php
class Sabai_Addon_Content_FieldWidget implements Sabai_Addon_Field_IWidget
{
    private $_addon, $_name, $_info;

    public function __construct(Sabai_Addon_Content $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldWidgetGetInfo($key = null)
    {
        if (!isset($this->_info)) {
            $this->_info = $this->_getInfo();
        }
        return isset($key) ? @$this->_info[$key] : $this->_info;
    }
    
    private function _getInfo()
    {
        switch ($this->_name) {
            case 'content_post_title':
                $info = array(
                    'label' => __('Text input field', 'sabai'),
                    'field_types' => array('content_post_title'),
                    'default_settings' => array(
                        'size' => 'large',
                        'min' => null,
                        'max' => null,
                    ),
                );
                break;
            case 'content_post_title_hidden':
                $info = array(
                    'label' => __('Hidden field', 'sabai'),
                    'field_types' => array('content_post_title'),
                    'default_settings' => array(
                        'size' => null,
                    ),
                    'is_hidden' => true,
                    'disable_edit_label' => true,
                    'disable_edit_description' => true,
                    'requirable' => false,
                );
                break;
            default:
                $info = array();
        }
        return $info;
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        switch ($this->_name) {
            case 'content_post_title':
                return array(
                    'size' => array(
                        '#type' => 'select',
                        '#title' => __('Field size', 'sabai'),
                        '#options' => array(
                            'small' => __('Small', 'sabai'),
                            'medium' => __('Medium', 'sabai'),
                            'large' => __('Large (responsive)', 'sabai'),
                        ),
                        '#default_value' => $settings['size'],
                    ),
                    'min' => array(
                        '#title' => __('Minimum length', 'sabai'),
                        '#type' => 'number',
                        '#integer' => true,
                        '#description' => __('The minimum length of value in characters.', 'sabai'),
                        '#size' => 5,
                        '#default_value' => $settings['min'],
                    ),
                    'max' => array(
                        '#title' => __('Maximum length', 'sabai'),
                        '#type' => 'number',
                        '#integer' => true,
                        '#description' => __('The maximum length of value in characters.', 'sabai'),
                        '#size' => 5,
                        '#default_value' => $settings['max'],
                    ),
                );
            default:
                return array();
        };
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        switch ($this->_name) {
            case 'content_post_title':
                return array(
                    '#type' => 'textfield',
                    '#size' => $this->_getSize($settings['size']),
                    '#default_value' => $value,
                    '#min_length' => $settings['min'],
                    '#max_length' => $settings['max'],
                );
            case 'content_post_title_hidden':
                return array(
                    '#type' => 'hidden',
                    '#value' => (string)$value,
                );
            default:
                return array();
        }
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        switch ($this->_name) {
            case 'content_post_title':
                return sprintf('<input type="text" disabled="disabled"%s />', $this->_getSizeHtmlAttr($settings['size']));
            case 'content_post_title_hidden':
                return '<input type="text" disabled="disabled" />';
		}
    }
    
    private function _getSizeHtmlAttr($size, $default = ' style="width:100%;"')
    {
        return ($size = $this->_getSize($size)) ? sprintf(' size="%d"', $size) : $default;
    }
    
    private function _getSize($size)
    {
        $sizes = array('small' => 20, 'medium' => 50, 'large' => null);
        return $size && isset($sizes[$size]) ? $sizes[$size] : null;
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $settings, array $parents = array())
    {

    }
}