<?php
abstract class Sabai_Addon_Voting_Controller_FlaggedPosts extends Sabai_Addon_Content_Controller_ListPosts
{    
    protected $_bundleNames = array();
    
    protected function _doExecute(Sabai_Context $context)
    {
        foreach ($this->_getBundleNames($context) as $bundle_name) {
            if ($this->HasPermission($bundle_name . '_manage')) {
                $this->_bundleNames[] = $bundle_name;
            }
        }
        if (empty($this->_bundleNames)) {
            $context->setForbiddenError();
            return;
        }
        
        parent::_doExecute($context);
        
        $context->clearTabs();
    }
    
    protected function _getBundle(Sabai_Context $context)
    {
        return;
    }
    
    protected function _getSorts(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return array(
            'recent' => array(
                'label' => __('Recent', 'sabai'),
                'field_name' => 'voting_flag',
                'field_type' => 'voting_flag',
            ),
            'score' => array(
                'label' => __('Score', 'sabai'),
                'field_name' => 'voting_flag',
                'field_type' => 'voting_flag',
                'args' => array('type' => 'sum'),
            ),
            'flags' => array(
                'label' => _x('Flags', 'sort', 'sabai'),
                'field_name' => 'voting_flag',
                'field_type' => 'voting_flag',
                'args' => array('type' => 'count'),
            ),
        );
    }
    
    protected function _createQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return $this->Entity_Query('content')
            ->propertyIs('post_status', Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            ->propertyIsIn('post_entity_bundle_name', $this->_bundleNames)
            ->fieldIsGreaterThan('voting_flag', 0, 'count');
    }
    
    abstract protected function _getBundleNames(Sabai_Context $context);
}