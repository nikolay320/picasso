<?php
class Sabai_Addon_Directory_Controller_Reviews extends Sabai_Addon_Content_Controller_ListPosts
{    
    protected function _getSorts(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $config = $this->getAddon()->getConfig('display');
        $this->_perPage = $config['review_perpage'];
        $this->_defaultSort = $config['review_sort'];
        $sorts = parent::_getSorts($context, $bundle) + array(
            'rating' => array(
                'label' => __('Rating', 'sabai-directory'),
                'field_name' => 'directory_rating',
                'field_type' => 'directory_rating',
            ),
            'helpfulness' => array(
                'label' => __('Helpfulness', 'sabai-directory'),
                'field_name' => 'voting_helpful',
                'field_type' => 'voting_helpful',
            ),
        );
        return isset($config['review_sorts']) ? array_intersect_key($sorts, array_flip($config['review_sorts'])) : $sorts;
    }
    
    protected function _getFilterFormTarget(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle)
    {
        return '.sabai-directory-review-filters';
    }
}