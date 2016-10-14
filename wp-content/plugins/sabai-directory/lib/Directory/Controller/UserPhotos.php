<?php
require_once dirname(__FILE__) . '/Photos.php';

class Sabai_Addon_Directory_Controller_UserPhotos extends Sabai_Addon_Directory_Controller_Photos
{
    protected $_displayMode = 'summary';
    
    protected function _createQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return parent::_createQuery($context, $bundle)
            ->propertyIs('post_user_id', $context->identity->id)
            ->fieldIsNull('directory_photo', 'official'); // exclude ofiicial photos
    }
}