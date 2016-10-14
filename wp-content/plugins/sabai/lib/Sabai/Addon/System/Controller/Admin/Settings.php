<?php
class Sabai_Addon_System_Controller_Admin_Settings extends Sabai_Addon_Form_Controller
{
    protected $_reloadAddons = true;
    
    protected function _doExecute(Sabai_Context $context)
    {        
        $this->_cancelUrl = null;
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary');
        $this->_successFlash = __('Settings saved.', 'sabai');
        if ($context->getContainer() !== '#sabai-content') {
            $this->_ajaxSubmit = true;
            $this->_ajaxOnSuccessFlash = true;
            $this->_ajaxOnSuccess = 'function (result, target, trigger) {
    if (target.attr("#id") === "sabai-content") return true;
    target.hide();
}';
        }
        parent::_doExecute($context);
        if ($context->isSuccess()) {
            $context->setSuccess($this->_getSuccessUrl($context));
            if ($this->_reloadAddons) {
                $this->reloadAddons();
            }
        }
    }
    
    protected function _getSuccessUrl(Sabai_Context $context)
    {
        return $this->Url('/settings', array('refresh' => 0));
    }
    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    { 
        return array();
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        unset($form->values[Sabai_Addon_Form::FORM_BUILD_ID_NAME], $form->values[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME], $form->values[Sabai_Request::PARAM_TOKEN]);
        $this->_saveConfig($context, $form->values);
    }
    
    protected function _saveConfig(Sabai_Context $context, array $config)
    {
        $this->getAddon()->saveConfig($config);
    }
}