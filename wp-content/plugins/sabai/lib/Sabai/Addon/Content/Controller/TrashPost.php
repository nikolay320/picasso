<?php
class Sabai_Addon_Content_Controller_TrashPost extends Sabai_Addon_Form_Controller
{
    protected $_defaultTrashType = Sabai_Addon_Content::TRASH_TYPE_SPAM, $_defaultOnly = false;
    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        if (!$this->HasPermission($context->entity->getBundleName() . '_manage')) {
            $bundle = $this->Entity_Bundle($context->entity);
            if (!$bundle) {
                return false;
            }
            $is_trashable = $this->getAddon($bundle->addon)
                ->contentGetContentType($bundle->name)
                ->contentTypeIsPostTrashable($context->entity, $this->getUser());
            if (!$is_trashable) {
                return false;
            }
        }
        
        $form = array();
        $form['#header'][] = sprintf(
            '<div class="sabai-alert sabai-alert-warning">%s</div>',
            __('Are you sure you want to delete this post?', 'sabai')
        );
        $form['#entity'] = $context->entity;
        
        if ($this->_defaultOnly) {
            $form['type'] = array(
                '#type' => 'hidden',
                '#value' => $this->_defaultTrashType,
            );
        } else {
            $form['type'] = array(
                '#type' => $this->_defaultOnly ? 'hidden' : 'radios',
                '#options' => $this->Content_TrashPostOptions(),
                '#title' => __('Reason for deletion', 'sabai'),
                '#default_value' => $this->_defaultTrashType,
                '#required' => true,
            );
        }
        $form['comment'] = array(
            '#type' => 'textfield',
            '#title' => $this->_defaultOnly ? __('Reason for deletion', 'sabai') : __('Comment', 'sabai'),
            '#states' => array(
                'visible' => array(
                    'input[name="type"]' => array('value' => Sabai_Addon_Content::TRASH_TYPE_OTHER),
                ),
            ),
            '#required' => array($this, 'isCommentRequired'),
        );
        
        $this->_cancelUrl = $this->Entity_Url($context->entity);
        $this->_submitButtons['submit'] = array(
            '#value' => sprintf(__('Delete %s', 'sabai'), $this->Entity_BundleLabel($this->Entity_Bundle($context->entity), true)),
            '#btn_type' => 'danger',
            '#attributes' => array('class' => 'sabai-content-btn-trash-' . str_replace('_', '-', $context->bundle->type)),
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
        $this->Content_TrashPosts($context->entity, $form->values['type'], @$form->values['comment']);
        $context->setSuccess($context->bundle->getPath());
    }
    
    public function isCommentRequired($form)
    {
        return $form->values['type'] == Sabai_Addon_Content::TRASH_TYPE_OTHER;
    }
}
