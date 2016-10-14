<?php
require_once dirname(__FILE__) . '/Leads.php';

class Sabai_Addon_Directory_Controller_MyListingLeads extends Sabai_Addon_Directory_Controller_Leads
{    
    protected function _getListingIds(Sabai_Context $context)
    {
        return $context->entity->getId();
    }
}
