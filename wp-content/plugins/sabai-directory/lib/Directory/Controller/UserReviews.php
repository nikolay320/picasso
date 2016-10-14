<?php
require_once dirname(__FILE__) . '/Reviews.php';

class Sabai_Addon_Directory_Controller_UserReviews extends Sabai_Addon_Directory_Controller_Reviews
{    
    protected function _createQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return parent::_createQuery($context, $bundle)
            ->propertyIs('post_user_id', $context->identity->id);
    }
}