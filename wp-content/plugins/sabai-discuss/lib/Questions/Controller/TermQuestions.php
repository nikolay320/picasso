<?php
require_once dirname(__FILE__) . '/Questions.php';
class Sabai_Addon_Questions_Controller_TermQuestions extends Sabai_Addon_Questions_Controller_Questions
{
    protected function _getDefaultCategoryId(Sabai_Context $context)
    {
        return $context->entity->getBundleType() === 'questions_categories' ? $context->entity->getId() : null;
    }
    
    protected function _getDefaultTagId(Sabai_Context $context)
    {
        return $context->entity->getBundleType() === 'questions_tags' ? $context->entity->getId() : null;
    }
}