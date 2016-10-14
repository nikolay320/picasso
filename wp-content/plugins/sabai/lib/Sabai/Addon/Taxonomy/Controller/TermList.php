<?php
class Sabai_Addon_Taxonomy_Controller_TermList extends Sabai_Controller
{    
    protected function _doExecute(Sabai_Context $context)
    {
        $context->addTemplate('system_list');
        $context->list = array();
        $parent_id = $context->getRequest()->asInt('parent');
        if ($bundle_name = $context->getRequest()->asStr('bundle')) {
            $bundle_names = explode(',', $bundle_name);
            $list = array();
            foreach ($bundle_names as $bundle_name) {
                $terms = $this->Taxonomy_Terms($bundle_name);
                if (isset($terms[$parent_id])) {
                    $this->_listTerms($list, $terms, $parent_id);
                    if ($parent_id) break; // found branch
                }
            }
            $context->list = array_values($list);
            return;
        }
        if (!$bundle_type = $context->getRequest()->asStr('bundle_type')) {
            return;
        }
        
        $query = $this->Entity_Query('taxonomy')->propertyIs('term_entity_bundle_type', $bundle_type);
        if ($parent_id) {
            $term_ids = array();
            foreach ($this->Taxonomy_Descendants($parent_id, false) as $_term) {
                $term_ids[] = $_term->id;
            }
            if (empty($term_ids)) {
                return;
            }
            $query->propertyIsIn('term_id', $term_ids);
        }
        $list = array();
        foreach ($query->fetch() as $term) {
            $list[$term->getId()] = array(
                'name' => $term->getSlug(),
                'title' => $term->getTitle(),
                'summary' => $this->Summarize($term->getContent(), 100),
                'url' => (string)$this->Entity_Url($term),
                'parent' => (int)$term->getParentId(),
            );
        }
        $context->list = array_values($list);
    }
    
    protected function _listTerms(&$list, $terms, $parent)
    {
        foreach ($terms[$parent] as $term_id => $term) {
            unset($term['fields']);
            $list[$term_id] = $term;
            if (isset($terms[$term_id])) {
                $this->_listTerms($list, $terms, $term_id);
            }
        }
    }
}