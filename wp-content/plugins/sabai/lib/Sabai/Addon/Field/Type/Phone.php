<?php
class Sabai_Addon_Field_Type_Phone extends Sabai_Addon_Field_Type_String
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('Phone Number', 'sabai'),
            'default_widget' => $this->_name,
            'default_renderer' => $this->_name,
            'default_settings' => array(
                'min_length' => null,
                'max_length' => null,
                'char_validation' => 'none',
                'mask' => '(999) 999-9999',
            ),
        );
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {
        $form = parent::fieldTypeGetSettingsForm($settings, $parents);
        unset($form['char_validation'], $form['regex'], $form['min_length'], $form['max_length']);
        return $form;
    }
}