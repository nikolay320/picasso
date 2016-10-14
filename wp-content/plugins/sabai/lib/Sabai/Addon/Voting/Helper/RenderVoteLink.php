<?php
class Sabai_Addon_Voting_Helper_RenderVoteLink extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, array $options = array())
    {
        $options += array(
            'tag' => 'helpful',
            'active' => false,
            'label' => __('Vote', 'sabai'),
            'title' => '',
            'is_active_js' => 'result.value == 1',
            'icon' => 'thumbs-up',
        );
        $vote_token = $application->Token('voting_vote_entity', 1800, true);
        $on_success = 'trigger.toggleClass("sabai-active", ' . $options['is_active_js'] .'); return false;';
        $path = '/vote/' . $options['tag'];
        return $application->LinkToRemote(
            $options['label'],
            '',
            $application->Entity_Url($entity, $path . '/form'),
            array(
                'icon' => $options['icon'],
                'active' => !empty($options['active']),
                'url' => $application->Entity_Url($entity, $path, array(Sabai_Request::PARAM_TOKEN => $vote_token, 'value' => 1)),
                'post' => true,
                'success' => $on_success,
                'loadingImage' => false
            ),
            array('title' => $options['title'], 'class' => 'sabai-voting-btn-' . $options['tag'], 'nofollow' => 'nofollow')
        );
    }
}