<?php
class Sabai_Addon_Content_Controller_Admin_DeletePost extends Sabai_Addon_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $form = array(
            'filter' => array('#type' => 'hidden', '#value' => $context->getRequest()->asStr('filter')),
            'sort' => array('#type' => 'hidden', '#value' => $context->getRequest()->asStr('sort')),
            'order' => array('#type' => 'hidden', '#value' => $context->getRequest()->asStr('order')),
        );
        $form['#header'][] = sprintf(
            '<div class="sabai-alert sabai-alert-warning">%s</div>',
            __('Are you sure you want to permanently delete this post?', 'sabai')
        );
        $this->_submitButtons['submit'] = array(
            '#value' => __('Delete Permanently', 'sabai'),
            '#btn_type' => 'danger',
        );
        
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $this->Content_DeletePosts($context->entity);
        $bundle = $context->child_bundle ? $context->child_bundle : $context->bundle;
        $context->setSuccess($this->Url($bundle->getAdminPath(), array(
            'filter' => $context->getRequest()->asStr('filter'),
            'sort' => $context->getRequest()->asStr('sort'),
            'order' => $context->getRequest()->asStr('order')
        )));
    }
}