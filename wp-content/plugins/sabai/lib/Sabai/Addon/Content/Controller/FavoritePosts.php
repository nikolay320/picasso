<?php
abstract class Sabai_Addon_Content_Controller_FavoritePosts extends Sabai_Addon_Entity_Controller_ListEntities
{
    protected $_defaultSort = 'added', $_displayMode = 'favorited', $_idProperty = 'post_id';
    
    final protected function _getEntityType(Sabai_Context $context)
    {
        return 'content';
    }
    
    protected function _getSorts(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return array(
            'active' => array(
                'label' => __('Active', 'sabai'),
            ),
            'added' => array(
                'label' => __('Added', 'sabai'),
            ),
        );
    }
    
    protected function _paginate(Sabai_Context $context, $sort, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        if (!$bundles = $this->_getBundleNames($context)) {
            return;
        }
        $gateway = $this->getModel(null, 'Content')->getGateway('Post');
        $pager = new SabaiFramework_Paginator_Custom(
            array($gateway, 'countUserFavorites'),
            array($this, 'fetchUserFavorites'),
            $this->_perPage,
            array($sort),
            array($this->_getUserId($context), $bundles)
        );
        return $pager->setCurrentPage($context->getRequest()->asInt(Sabai::$p, $this->_defaultPage));
    }
    
    public function fetchUserFavorites($userId, $bundles, $limit, $offset, $sort)
    {
        $entity_ids = $this->getModel(null, 'Content')->getGateway('Post')->fetchUserFavorites($userId, $bundles, $limit, $offset, $sort);
        return $this->Entity_Entities('content', $entity_ids, true, true); 
    }
    
    protected function _getUserId(Sabai_Context $context)
    {
        return isset($context->identity) ? $context->identity->id : $this->getUser()->id;
    }
    
    abstract protected function _getBundleNames(Sabai_Context $context);
}