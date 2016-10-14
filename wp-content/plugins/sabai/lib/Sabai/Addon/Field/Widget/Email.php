<?php
class Sabai_Addon_Field_Widget_Email extends Sabai_Addon_Field_Widget_Textfield
{
    protected function _fieldWidgetGetInfo()
    {
        $info = parent::_fieldWidgetGetInfo();
        $info['field_types'] = array($this->_name);
        $info['default_settings']['autopopulate'] = false;
        $info['default_settings']['checkmx'] = false;
        return $info;
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        $form = parent::fieldWidgetGetSettingsForm($fieldType, $settings, $parents);
        $form['autopopulate'] = array(
            '#type' => 'checkbox',
            '#title' => __("Auto-populate e-mail address field with the current user's e-mail address", 'sabai'),
            '#default_value' => $settings['autopopulate'],
        );
        if ($this->_canCheckMx()) {
            $form['checkmx'] = array(
                '#type' => 'checkbox',
                '#title' => __('Check MX record of e-mail address', 'sabai'),
                '#default_value' => $settings['checkmx'],
            );
        }
        return $form;
    }
    
    protected function _canCheckMx()
    {
        return function_exists('checkdnsrr')
            && version_compare(PHP_VERSION, '5.2.4', '>='); // 2nd parameter of checkdnsrr added in 5.2.4
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        $form = parent::fieldWidgetGetForm($field, $settings, $bundle, $value, $entity, $parents, $admin);
        if (!empty($settings['autopopulate'])) {
            $form['#auto_populate'] = 'email';
        }
        if ($settings['checkmx'] && $this->_canCheckMx()) {
            if (!isset($form['#element_validate'])) {
                $form['#element_validate'] = array();
            }
            $form['#element_validate'][] = array(array($this, 'validateEmail'), array($settings['checkmx']));
        }

        return $form;
    }
    
    public function validateEmail($form, &$value, $element)
    {
        list(, $domain) = explode('@', $value);
        if (!$domain || !checkdnsrr($domain, 'MX')) {
            $form->setError(__('Invalid domain name.', 'sabai'), $element);
        }
    }
}