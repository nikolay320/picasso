<?php
require_once dirname(__FILE__) . '/Answers.php';

class Sabai_Addon_Questions_Controller_UserAnswers extends Sabai_Addon_Questions_Controller_Answers
{    
    protected function _createQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return parent::_createQuery($context, $bundle)
            ->propertyIs('post_user_id', $context->identity->id);
    }
}