<?php
class Sabai_Addon_Questions_Controller_Answers extends Sabai_Addon_Content_Controller_ListPosts
{    
    protected function _getSorts(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $config = $this->getAddon()->getConfig('front');
        $this->_perPage = $config['answer_perpage'];
        $this->_defaultSort = $config['answer_sort'];
        $sorts = parent::_getSorts($context, $bundle) + array(
            'votes' => array(
                'label' => __('Most Votes', 'sabai-discuss'),
                'field_name' => 'voting_updown',
                'field_type' => 'voting_updown',
            ),
            'active' => array(
                'label' => __('Recently Active', 'sabai-discuss'),
                'field_name' => 'content_activity',
                'field_type' => 'content_activity',
            ),
        );
        unset($sorts['title']);
        return isset($config['answer_sorts']) ? array_intersect_key($sorts, array_flip($config['answer_sorts'])) : $sorts;
    }
    
    protected function _getFilterFormTarget(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        return '.sabai-questions-answers-filters';
    }
}