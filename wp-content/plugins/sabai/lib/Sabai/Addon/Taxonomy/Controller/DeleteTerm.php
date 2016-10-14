<?php
class Sabai_Addon_Taxonomy_Controller_DeleteTerm extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $form = array();
        $form['#header'][] = sprintf(
            '<div class="sabai-alert sabai-alert-warning">%s</div>',
            __('Are you sure you want to <em>permanently</em> delete this item?', 'sabai')
        );
        $this->_cancelUrl = $this->Entity_Url($context->entity);
        $this->_submitButtons['submit'] = array(
            '#value' => sprintf(__('Delete %s', 'sabai'), $this->Entity_BundleLabel($context->taxonomy_bundle, true)),
            '#btn_type' => 'primary',
        );
        $this->_ajaxCancelType = 'none';
        if ($delete_target_id = $context->getRequest()->asStr('delete_target_id')) {
            $this->_ajaxOnSuccess = sprintf('function (result, target, trigger) {
  target.hide();
  $("#%s").fadeTo("fast", 0, function(){$(this).slideUp("medium", function(){$(this).remove();});});
}', Sabai::h($delete_target_id));
        }
        
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $this->getAddon('Entity')->deleteEntities('taxonomy', array($context->entity->getId() => $context->entity));
        $context->setSuccess($context->taxonomy_bundle->getAdminPath());
    }
}
