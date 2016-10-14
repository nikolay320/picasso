<?php
require_once dirname(__FILE__) . '/Questions.php';

class Sabai_Addon_Questions_Controller_UserQuestions extends Sabai_Addon_Questions_Controller_Questions
{    
    protected function _createQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return parent::_createQuery($context, $bundle)
            ->propertyIs('post_user_id', $context->identity->id);
    }
    
    protected function _getDefaultSettings(Sabai_Context $context)
    {
        $settings = parent::_getDefaultSettings($context);
        $settings['featured_only'] = false;
        $settings['hide_searchbox'] = true; // hide for now since the autocomplete feature does not filter by user posts
        $settings['hide_askbtn'] = true;
        return $settings;
    }
}