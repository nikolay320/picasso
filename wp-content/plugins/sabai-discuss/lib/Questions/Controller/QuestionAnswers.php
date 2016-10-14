<?php
require_once dirname(__FILE__) . '/Answers.php';

class Sabai_Addon_Questions_Controller_QuestionAnswers extends Sabai_Addon_Questions_Controller_Answers
{
    protected $_displayMode = 'full', $_template = 'questions_answers', $_largeScreenSingleRow = false;
    
    protected function _doExecute(Sabai_Context $context)
    {
        $search_config = $this->getAddon()->getConfig('search');
        $this->_filter = empty($search_config['no_filters']);
        $this->_filterOnChange = !empty($search_config['filters_auto']);
        $this->_showFilters = !empty($search_config['show_filters']);
        parent::_doExecute($context);
        if ($this->HasPermission($context->child_bundle->name . '_add')) {
            $form = $this->Entity_Form($context->child_bundle->name);
            $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME] = $this->Form_SubmitButtons(array('submit' => array('#value' => __('Post Answer', 'sabai-discuss'), '#btn_type' => 'primary')));
            $form['#action'] = $this->Entity_Url($context->entity, '/' . $this->Entity_Addon($context->entity)->getSlug('answers') . '/add');
            $context->answer_form = $form;
        } else {
            $context->answer_form = null;
        }
    }
    
    protected function _createQuery(Sabai_Context $context, Sabai_Addon_Entity_Model_Bundle $bundle = null)
    {
        return parent::_createQuery($context, $bundle)
            ->fieldIs('content_parent', $context->entity->getId())
            ->sortByField('questions_answer_accepted', 'DESC', 'score');
    }
}