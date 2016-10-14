<?php
class Sabai_Addon_Field_Widget_User extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        switch ($this->_name) {
            case 'user_select':
                return array(
                    'label' => __('Select list', 'sabai'),
                    'field_types' => array('user'),
                    'accept_multiple' => true,
                    'default_settings' => array(
                        'enhanced_ui' => true,
                        'current_user_selected' => false,
                    ),
                    'is_fieldset' => true,
                );
        }
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        switch ($this->_name) {
            case 'user_select':
                return array(
                    'current_user_selected' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Set current user selected by default', 'sabai'),
                        '#default_value' => $settings['current_user_selected'],
                    ),
                    'enhanced_ui' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Enable enhanced user interface (recommended)', 'sabai'),
                        '#default_value' => $settings['enhanced_ui'],
                    ),
                );
        }
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        if (isset($value)) {
            $default_value = array();
            foreach ($value as $_value) {
                $default_value[] = $_value->id;
            }
        } else {
            $default_value = null;
        }
        switch ($this->_name) {
            case 'user_select':
                $default_text = isset($settings['default_text']) ? $settings['default_text'] : __('Select User', 'sabai');
                if ($settings['enhanced_ui']) {
                    return array(
                        '#type' => 'user',
                        '#default_value' => $this->_getDefaultValue($value, $settings),
                        '#multiple' => $field->getFieldMaxNumItems() != 1,
                        '#attributes' => array('placeholder' => $default_text),
                    );
                }
                if (isset($value)) {
                    $default_value = array();
                    foreach ($value as $_value) {
                        $default_value[] = $_value->id;
                    }
                }
                if (!empty($default_value)) {
                    if ($field->getFieldMaxNumItems() == 1) {
                        $default_value = array_shift($default_value);
                    }
                } else {
                    if ($settings['current_user_selected']) {
                        $default_value = $this->_addon->getApplication()->getUser()->id;
                    } else {
                        $default_value = null; 
                    }
                }
                return array(
                    '#type' => 'select',
                    '#empty_value' => 0,
                    '#max_selection' => $field->getFieldMaxNumItems(),
                    '#default_value' => $default_value,
                    '#multiple' => $field->getFieldMaxNumItems() != 1,
                    '#options' => array(0 => $default_text) + $this->_getUserList(),
                );
        }
    }
	
    private function _getDefaultValue($value, array $settings)
    {
        if (isset($value)) {
            $default_value = array();
            foreach ($value as $entity) {
                if (!is_object($entity)) continue;

                $default_value[$entity->id] = $entity->id;
            }
        } else {
            $default_value = null;
        }
        if (empty($default_value)
            && $settings['current_user_selected']
            && !$this->_addon->getApplication()->getUser()->isAnonymous()
        ) {
            $default_value = $this->_addon->getApplication()->getUser()->id;
        }
        return $default_value;
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        switch ($this->_name) {
            case 'user_select':
                return sprintf(
                    '<select disabled="disabled"><option>%s</option></select>',
                    __('Select User', 'sabai')
                );
        }
    }

    private function _getUserList($limit = 200)
    {
        $ret = array();
        $identities = $this->_addon->getApplication()
            ->getPlatform()
            ->getUserIdentityFetcher()
            ->fetch($limit, 0, 'name', 'ASC');
        foreach ($identities as $identity) {
            $ret[$identity->id] = $identity->name;
        }

        return $ret;
    }
}