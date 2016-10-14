<?php
class Sabai_Addon_Taxonomy_Controller_ListTerms extends Sabai_Addon_Entity_Controller_ListEntities
{
    protected $_perPage = 100, $_idProperty = 'term_id';

    final protected function _getEntityType(Sabai_Context $context)
    {
        return 'taxonomy';
    }

    protected function _getBundle(Sabai_Context $context)
    {
        return $context->taxonomy_bundle;
    }
    
    protected function _createQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return $this->Entity_Query('taxonomy')->propertyIs('term_entity_bundle_name', $bundle->name)->sortByProperty('term_title');
    }
}