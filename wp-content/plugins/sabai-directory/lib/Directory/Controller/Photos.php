<?php
class Sabai_Addon_Directory_Controller_Photos extends Sabai_Addon_Content_Controller_ListPosts
{   
    protected function _doExecute(Sabai_Context $context)
    {
        $this->LoadJqueryMasonry();
        
        // If a specific photo is requested, set the page to where the photo appears
        if (($current_photo_id = $context->getRequest()->asInt('photo_id'))
            && !$context->getRequest()->asInt(Sabai::$p)
            && ($current_photo = $this->Entity_Entity('content', $current_photo_id, false))
        ) {
            $newer_photo_count = $this->_createQuery($context, $this->_getBundle($context))
                ->propertyIsOrGreaterThan('post_published', $current_photo->getTimestamp())
                ->count();
            $this->_defaultSort = 'newest';
            $context->getRequest()->set(Sabai::$p, ceil($newer_photo_count/ $this->_perPage))
                ->set('sort', 'newest');
        }
        
        parent::_doExecute($context);
        
        if (!empty($context->entities)) {
            if ($current_photo_id && !empty($context->entities[$current_photo_id])) {
                $context->current_photo = $context->entities[$current_photo_id];
            } else {
                $current_photo_ids = array_keys($context->entities);
                $context->current_photo = $context->entities[array_shift($current_photo_ids)];
            }
            $context->link_to_listing = true;
        }
    }
    
    protected function _getSorts(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        $config = $this->getAddon()->getConfig('display');
        $this->_perPage = $config['photo_perpage'];
        $this->_defaultSort = $config['photo_sort'];
        $sorts = parent::_getSorts($context, $bundle) + array(
            'votes' => array(
                'label' => __('Votes', 'sabai-directory'),
                'field_name' => 'voting_helpful',
                'field_type' => 'voting_helpful',
            ),
        );
        return isset($config['photo_sorts']) ? array_intersect_key($sorts, array_flip($config['photo_sorts'])) : $sorts;
    }
}