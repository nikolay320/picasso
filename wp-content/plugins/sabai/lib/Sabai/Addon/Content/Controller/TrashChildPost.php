<?php
class Sabai_Addon_Content_Controller_TrashChildPost extends Sabai_Addon_Content_Controller_TrashPost
{
    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {        
        // Fetch parent entity before deletion
        $parent_post = $this->Content_ParentPost($context->entity, false);
        $parent_bundle = $this->Entity_Bundle($parent_post);
        $bundle = $this->Entity_Bundle($context->entity);
        
        parent::submitForm($form, $context);
        
        // Redirect to parent entity page
        $context->setSuccess($this->Entity_Url($parent_post, substr($bundle->getPath(), strlen($parent_bundle->getPath()))));
    }
}
