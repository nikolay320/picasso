<?php
abstract class Sabai_Addon_Entity_Controller_Autocomplete extends Sabai_Controller
{
    protected $_template = 'entity_autocomplete';
    
    protected function _doExecute(Sabai_Context $context)
    {
        if (!$context->getRequest()->isAjax()) {
            $context->setBadRequestError();
            return;
        }

        $term = trim($context->getRequest()->asStr('term'));
        if (strlen($term) <= 1) {
            $context->setBadRequestError();
            return;
        }

        $limit = 10;
        $offset = ($context->getRequest()->asInt(Sabai::$p, 1) - 1) * $limit;
        $bundle = $this->_getBundle($context);
        $context->entities = $this->Entity_TypeImpl($bundle->entitytype_name)
            ->entityTypeSearchEntitiesByBundle($term, $bundle, $limit, $offset);
        $context->addTemplate($this->_template);
    }
    
    abstract protected function _getBundle(Sabai_Context $context);
}