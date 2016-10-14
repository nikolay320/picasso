<?php
require_once dirname(__FILE__) . '/Reviews.php';

class Sabai_Addon_Directory_Controller_ListingReviews extends Sabai_Addon_Directory_Controller_Reviews
{
    protected $_displayMode = 'full', $_largeScreenSingleRow = false;
    
    protected function _doExecute(Sabai_Context $context)
    {
        $search_config = $this->getAddon()->getConfig('search');
        $this->_filter = empty($search_config['no_filters']);
        $this->_filterOnChange = !empty($search_config['filters_auto']);
        $this->_showFilters = !empty($search_config['show_filters']);
        parent::_doExecute($context);
    }
    
    protected function _getLinks(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null, array $urlParams = array())
    {
        if (!$this->getUser()->isAnonymous() && !$this->HasPermission($context->child_bundle->name . '_add')) {
            return array();
        }
        return array(
            1 => array(
                $this->LinkTo(
                    __('Write a Review', 'sabai-directory'),
                    $this->Entity_Url($context->entity, '/reviews/add'),
                    array('icon' => 'pencil'),
                    array(
                        'title' => __('Write a Review', 'sabai-directory'),
                        'class' => 'sabai-btn sabai-btn-sm sabai-btn-primary sabai-directory-btn-review',
                    )
                ),
            ),
        );
    }
    
    protected function _createQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return parent::_createQuery($context, $bundle)
            ->fieldIs('content_parent', $context->entity->getId());
    }
}