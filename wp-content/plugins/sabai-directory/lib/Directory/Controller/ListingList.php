<?php
class Sabai_Addon_Directory_Controller_ListingList extends Sabai_Controller
{ 
    protected function _doExecute(Sabai_Context $context)
    {
        $list = array();
        if ($q = $context->getRequest()->asStr('query')) {
            $query = $this->Entity_Query('content')
                ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
                ->propertyContains('post_title', $q);
            if ($bundle = $context->getRequest()->asStr('bundle')) {
                $query->propertyIsIn('post_entity_bundle_name', explode(',', $bundle));
            } elseif ($bundle_type = $context->getRequest()->asStr('bundle_type')) {
                $query->propertyIs('post_entity_bundle_type', $bundle_type);
            } else {
                return;
            }
            if ($category_id = $context->getRequest()->asInt('category')) {
                $category_ids = array($category_id);
                foreach ($this->Taxonomy_Descendants($category_id, false) as $_category) {
                    $category_ids[] = $_category->id;
                }
                $query->fieldIsIn('directory_category', $category_ids);
            }
            $num = $context->getRequest()->asInt('num', 5);
            if ($num > 100) $num = 5;
            foreach ($query->fetch($num) as $entity) {
                $list[] = array(
                    'id' => $entity->getId(),
                    'title' => $entity->getTitle(),
                    'summary' => $this->Summarize($entity->getContent(), 100),
                    'url' => (string)$this->Entity_Url($entity),
                );
            }
        }
        $context->addTemplate('system_list')->setAttributes(array('list' => $list));
    }
}