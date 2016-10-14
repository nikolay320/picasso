<?php
class Sabai_Addon_Form_Field_Token extends Sabai_Addon_Form_Field_AbstractField
{
    private static $_elements = array();
    
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (empty($data['#token_id'])) {
            $data['#token_id'] = isset($form->settings['#name']) ? $form->settings['#name'] : $form->settings['#id'];
        }

        return self::$_elements[$form->settings['#id']][$name] = $form->createElement('hidden', $name, $data);
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!$this->_addon->getApplication()->TokenValidate($value, $data['#token_id'], !empty($data['#token_reuseable']))) {
        //if (!SabaiFramework_Token::validate($value, $data['#token_id'], !empty($data['#token_reuseable']))) {
            $form->setError(__('Invalid token', 'sabai'));
        }
    }

    public function formFieldOnCleanupForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnRenderForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $lifetime = empty($data['#token_lifetime']) ? 1800 : $data['#token_lifetime'];
        //$token = SabaiFramework_Token::create($data['#token_id'], $lifetime, !empty($data['#token_reobtainable']))->getValue();
        $token = $this->_addon->getApplication()->Token($data['#token_id'], $lifetime, !empty($data['#token_reobtainable']));
        self::$_elements[$form->settings['#id']][$name]->setValue($token);
        $form->renderElement($data);
    }
}