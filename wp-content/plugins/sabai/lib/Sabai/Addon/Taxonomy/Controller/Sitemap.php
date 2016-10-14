<?php
class Sabai_Addon_Taxonomy_Controller_Sitemap extends Sabai_Addon_System_Controller_Sitemap
{
    protected $_entityPriority = 0.2;
    
    protected function _getCacheId(Sabai_Context $context)
    {
        return 'taxonomy_sitemap_' . $context->taxonomy_bundle->name;
    }
    
    protected function _getQuery(Sabai_Context $context)
    {
        return $this->Entity_Query('taxonomy')
            ->propertyIs('term_entity_bundle_name', $context->taxonomy_bundle->name);
    }
}