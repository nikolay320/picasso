<?php
class Sabai_Addon_Content_Controller_Admin_ListChildPosts extends Sabai_Addon_Content_Controller_Admin_ListPosts
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $form = parent::_doGetFormSettings($context, $formStorage);
        
        $form['entities']['#header'][$context->bundle->name] = array(
            'order' => 11,
            'label' => $this->Entity_BundleLabel($context->bundle, true),
        );
        foreach ($form['entities']['#options'] as $entity_id => $data) {
            $parent_post = $this->Content_ParentPost($data['#entity'], false);
            if (!$parent_post) {
                // For some reason the paernt post does not exist
                continue;
            }
            $parent_title = mb_strimwidth($parent_post->getTitle(), 0, 70, '...');
            $form['entities']['#options'][$entity_id][$context->bundle->name] = $this->LinkTo(
                $parent_title,
                $this->Url($context->getRoute(), array('content_parent' => $parent_post->getId()))
            );
        }
        unset($form['entities']['#header']['views']);
        
        return $form;
    }
    
    protected function _getBundle(Sabai_Context $context)
    {
        return $context->child_bundle;
    }
    
    protected function _getLinks(Sabai_Context $context)
    {
        $bundle = $this->_getBundle($context);
        return array(
            $this->LinkTo(
                sprintf(__('Add %s', 'sabai'), $this->Entity_BundleLabel($bundle, true)),
                $this->Url($bundle->getAdminPath() . '/add', array('content_parent' => $context->getRequest()->asInt('content_parent'))),
                array('icon' => 'plus'),
                array('class' => 'sabai-btn sabai-btn-primary sabai-btn-sm')
            ),
        );
    }
}