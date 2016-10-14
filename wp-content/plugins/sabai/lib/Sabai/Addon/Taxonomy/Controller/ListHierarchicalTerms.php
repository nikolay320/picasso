<?php
class Sabai_Addon_Taxonomy_Controller_ListHierarchicalTerms extends Sabai_Addon_Taxonomy_Controller_ListTerms
{        
    protected function _createQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return parent::_createQuery($context, $bundle)->propertyIs('term_parent', 0);
    }
    
    protected function _getSorts(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return array();
    }
}