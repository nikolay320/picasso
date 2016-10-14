<?php
class Sabai_Addon_Content_Controller_ListPosts extends Sabai_Addon_Entity_Controller_ListEntities
{
    protected $_idProperty = 'post_id';
    
    final protected function _getEntityType(Sabai_Context $context)
    {
        return 'content';
    }

    protected function _getBundle(Sabai_Context $context)
    {
        return isset($context->child_bundle) ? $context->child_bundle : $context->bundle;
    }
    
    protected function _createQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return $this->Entity_Query('content')
            ->propertyIs('post_entity_bundle_name', $bundle->name)
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED);
    }
    
    protected function _getSorts(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return array(
            'newest' => array(
                'label' => __('Newest First', 'sabai'),
                'field_name' => 'content_post_published',
                'field_type' => 'content_post_published',
            ),
            'oldest' => array(
                'label' => __('Oldest First', 'sabai'),
                'field_name' => 'content_post_published',
                'field_type' => 'content_post_published',
                'args' => array('asc'),
            ),
            'title' => array(
                'label' => _x('Title', 'sort', 'sabai'),
                'field_name' => 'content_post_title',
                'field_type' => 'content_post_title',
            ),
        ) + parent::_getSorts($context, $bundle);
    }
}