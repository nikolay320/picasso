<?php
class Sabai_Addon_Content_Controller_EditChildPost extends Sabai_Addon_Content_Controller_EditPost
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $form = parent::_doGetFormSettings($context, $formStorage);
        // Remove parent content selection field
        unset($form['content_parent']);
        // Add parent content static field if not on modal window or Ajax request
        if ($context->getContainer() !== '#sabai-modal' && !$context->getRequest()->isAjax()) {
            $parent = $this->Content_ParentPost($context->entity, false);
            $form['content_parent'] = array(
                '#type' => 'item',
                '#title' => $this->Entity_BundleLabel($parent, true),
                '#markup' => $this->Entity_Permalink($parent),
                '#weight' => -1,
            );
        }
        
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        // Make sure the parent entity can not be changed
        unset($form->values['content_parent']);
        
        return parent::submitForm($form, $context);
    }
}
