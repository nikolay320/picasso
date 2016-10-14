<?php
class Sabai_Addon_Field_Type_Email extends Sabai_Addon_Field_Type_String
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('Email', 'sabai'),
            'default_widget' => $this->_name,
            'default_renderer' => $this->_name,
            'default_settings' => array(
                'min_length' => null,
                'max_length' => null,
                'char_validation' => 'email',
            ),
        );
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {
        $form = parent::fieldTypeGetSettingsForm($settings, $parents);
        $form['char_validation']['#type'] = 'hidden';
        $form['char_validation']['#value'] = 'email';
        return $form;
    }
}