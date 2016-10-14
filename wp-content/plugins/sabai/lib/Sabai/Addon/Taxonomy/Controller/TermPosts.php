<?php
class Sabai_Addon_Taxonomy_Controller_TermPosts extends Sabai_Addon_Content_Controller_ListPosts
{    
    protected function _createQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        if (!empty($context->taxonomy_bundle->info['taxonomy_hierarchical'])) {
            $term_ids = $this->getModel('Term', 'Taxonomy')->fetchDescendantsByParent($context->entity->getId())->getAllIds();
            $term_ids[] = $context->entity->getId();
        } else {
            $term_ids = array($context->entity->getId());
        }
        return $this->Entity_Query('content')
            ->fieldIsIn($context->taxonomy_bundle->type, $term_ids)
            ->propertyIs('post_entity_bundle_name', $bundle->name)
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED);
    }
}