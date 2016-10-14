<?php
class Sabai_Addon_Questions_Controller_Tags extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {
        // Define query
        $query = $this->Entity_Query('taxonomy')
            ->propertyIs('term_entity_bundle_name', $this->getAddon()->getTagsBundleName())
            ->fieldIs('taxonomy_content_count', 'questions', 'content_bundle_name')
            ->fieldIsGreaterThan('taxonomy_content_count', 0);
        
        // Add sorts
        $sorts = array(
            'name' => array(
                'label' => __('Name', 'sabai-discuss'),
            ),
            'popular' => array(
                'label' => __('Most Popular', 'sabai-discuss'),
            ),
            'newest' => array(
                'label' => __('Newest First', 'sabai-discuss'),
            ),
        );
        
        // Get currens sort and append to query
        $current_sort = $context->getRequest()->asStr('sort', 'name', array_keys($sorts));
        switch ($current_sort) {
            case 'name':
                $query->sortByProperty('term_title');
                break;
            case 'newest':
                $query->sortByProperty('term_created', 'DESC');
                break;
            case 'popular':
                $query->sortByField('taxonomy_content_count', 'DESC');
                break;
        }
        
        // Query with pagination
        $paginator = $query->paginate(50)->setCurrentPage($context->getRequest()->asInt(Sabai::$p, 1));
        
        // Generate sort links
        foreach ($sorts as $key => $sort) {
            if (!is_array($sort)) {
                $sort = array('label' => $sort);
                $attr = array();
            } else {
                $attr = isset($sort['title']) ? array('title' => $sort['title']) : array();
            }
            $sorts[$key] = $this->LinkToRemote($sort['label'], $context->getContainer(), $this->Url($context->getRoute(), array('sort' => $key)), array(), $attr);
        }
        
        // Set response
        $context->addTemplate('questions_tagcloud')
            ->setAttributes(array(
                'entities' => $paginator->getElements(),
                'paginator' => $paginator,
                'current_sort' => $current_sort,
                'sorts' => $sorts,
                'links' => array(),
            ));
    }
}