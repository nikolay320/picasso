<?php
class Sabai_Addon_QuestionsMyCRED extends Sabai_Addon
{
    const VERSION = '1.3.28', PACKAGE = 'sabai-discuss';
    
    public function isUninstallable($currentVersion)
    {
        return true;
    }
    
    public function onSabaiPlatformWordpressInit()
    {
        add_action('mycred_init', array($this, 'mycredInitAction'), 9);
        add_filter('mycred_setup_hooks', array($this, 'mycredSetupHooksFilter'));
        add_filter('mycred_all_references', array($this, 'mycredAllReferencesFilter'));
    }
    
    public function mycredInitAction()
    {
        if (!class_exists('Sabai_Addon_QuestionsMyCRED_Hook', false)) {
            require dirname(__FILE__) . '/QuestionsMyCRED/Hook.php';
        }
    }
    
    public function mycredSetupHooksFilter($hooks)
    {
        $hooks['hook_sabai_questions'] = array(
            'title' => _x('Sabai Discuss', 'MyCRED title', 'sabai-discuss'),
            'description' => _x('Awards %_plural% for variosu actions in Sabai Discuss.', 'MyCRED desc', 'sabai-discuss'),
            'callback' => array('Sabai_Addon_QuestionsMyCRED_Hook'),
        );
        return $hooks;
    }
    
    public function mycredAllReferencesFilter($hooks)
    {
        return $hooks += array(
            'sabai_vote_question' => __('Voting Up Question', 'MyCRED', 'sabai-discuss'),
            'sabai_vote_down_question' => __('Voting Down Question', 'MyCRED', 'sabai-discuss'),
            'sabai_unvote_question' => __('Unvoting Question', 'MyCRED', 'sabai-discuss'),
            'sabai_question_voted' => __('Question Voted Up', 'MyCRED', 'sabai-discuss'),
            'sabai_question_voted_down' => __('Question Voted Down', 'MyCRED', 'sabai-discuss'),
            'sabai_vote_answer' => __('Voting Up Answer', 'MyCRED', 'sabai-discuss'),
            'sabai_vote_down_answer' => __('Voting Down Answer', 'MyCRED', 'sabai-discuss'),
            'sabai_unvote_answer' => __('Unvoting Answer', 'MyCRED', 'sabai-discuss'),
            'sabai_answer_voted' => __('Answer Voted Up', 'MyCRED', 'sabai-discuss'),
            'sabai_answer_voted_down' => __('Answer Voted Down', 'MyCRED', 'sabai-discuss'),
            'sabai_accept_answer' => __('Accepting Answer', 'MyCRED', 'sabai-discuss'),
            'sabai_unaccept_answer' => __('Unaccepting Answer', 'MyCRED', 'sabai-discuss'),
            'sabai_answer_accepted' => __('Answer Accepted', 'MyCRED', 'sabai-discuss'),
        );
    }
}