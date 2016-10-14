<?php
class Sabai_Addon_Questions_Controller_CloseQuestion extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $form = array();
        $form['#header'][] = sprintf(
            '<div class="sabai-alert sabai-alert-warning">%s</div>',
            $context->entity->getSingleFieldValue('questions_closed')
                ? __('Do you really want to reopen this question?', 'sabai-discuss')
                : __('Closing this question will prevent new answers to be posted. Are you sure you want to do this?', 'sabai-discuss')
        );
        
        $this->_cancelUrl = $this->Entity_Url($context->entity);
        $this->_submitButtons['submit'] = array(
            '#value' => $context->entity->getSingleFieldValue('questions_closed') ? __('Reopen Question', 'sabai-discuss') : __('Close Question', 'sabai-discuss'),
            '#btn_type' => 'primary',
        );
        if ($update_target_id = $context->getRequest()->asStr('update_target_id')) {
            $this->_ajaxOnSuccess = sprintf('function (result, target, trigger) {
    target.hide();
    SABAI.replace("#%s", "%s");
}', Sabai::h($update_target_id), $this->Entity_Url($context->entity));
            $form['update_target_id'] = array('#type' => 'hidden', '#value' => $update_target_id);
        }
        
        return $form;
    }
    
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $close = $context->entity->getSingleFieldValue('questions_closed') ? false : true;
        $entity = $this->Entity_Save($context->entity, array('questions_closed' => $close));

        // Send success response
        $context->setSuccess($this->Entity_Url($entity));
    }
}