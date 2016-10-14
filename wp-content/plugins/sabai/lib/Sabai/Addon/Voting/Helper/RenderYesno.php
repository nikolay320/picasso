<?php
class Sabai_Addon_Voting_Helper_RenderYesno extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $containerClass, array $options = array())
    {
        if ($application->Entity_IsAuthor($entity, $application->getUser())) {
            $can_vote = $application->HasPermission($entity->getBundleName() . '_voting_own_helpful');
        } else {
            $can_vote = $application->HasPermission($entity->getBundleName() . '_voting_helpful');
        }
        if (!$can_vote) {
            return '';
        }
        
        $options += array(
            'format' => '%s %s',
            'yes_label' => __('Yes', 'sabai'),
            'no_label' => __('No', 'sabai'),
            'yes_btn_class' => 'sabai-btn sabai-btn-default sabai-btn-xs',
            'no_btn_class' => 'sabai-btn sabai-btn-default sabai-btn-xs',
        );
        if (isset($entity->data['voting_helpful_voted'])) {
            if ($entity->data['voting_helpful_voted'] == 1) {
                $options['yes_btn_class'] .= ' sabai-active';
            } elseif ($entity->data['voting_helpful_voted'] == 0) {
                $options['no_btn_class'] .= ' sabai-active';
            }
        }
        $vote_token = $application->Token('voting_vote_entity', 1800, true);
        $on_success = 'trigger.closest("'. $containerClass .'").find(".sabai-voting-helpful-yes").toggleClass("sabai-active", result.value == 1).end().find(".sabai-voting-helpful-no").toggleClass("sabai-active", result.value !== false && result.value == 0); return false;';     
        $yes_btn = $application->LinkToRemote(
            $options['yes_label'],
            '',
            $application->Entity_Url($entity, '/vote/helpful/form'),
            array('url' => $application->Entity_Url($entity, '/vote/helpful', array(Sabai_Request::PARAM_TOKEN => $vote_token, 'value' => 1)), 'post' => true, 'success' => $on_success, 'loadingImage' => false),
            array('class' => $options['yes_btn_class'] . ' sabai-voting-helpful-yes')
        );
        $no_btn = $application->LinkToRemote(
            $options['no_label'],
            '',
            $application->Entity_Url($entity, '/vote/helpful/form'),
            array('url' => $application->Entity_Url($entity, '/vote/helpful', array(Sabai_Request::PARAM_TOKEN => $vote_token, 'value' => 0)), 'post' => true, 'success' => $on_success, 'loadingImage' => false),
            array('class' => $options['no_btn_class'] . ' sabai-voting-helpful-no')
        );
        return sprintf($options['format'], $yes_btn, $no_btn);
    }
}