<?php
require_once dirname(__FILE__) . '/Listings.php';
class Sabai_Addon_Directory_Controller_TermListings extends Sabai_Addon_Directory_Controller_Listings
{   
    protected function _getDefaultCategoryId(Sabai_Context $context)
    {
        return $context->entity->getId();
    }
}