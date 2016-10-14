<?php
class Sabai_Addon_Voting_Helper_TemplateTags extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Voting_Model_Vote $vote, $prefix = 'flag_')
    {
        $user = $vote->User;
        return array(
            '{' . $prefix . 'id}' => $vote->id,
            '{' . $prefix . 'user_name}' => $user->name,
            '{' . $prefix . 'user_email}' => $user->email,
            '{' . $prefix . 'date}' => $application->Date($vote->created ? $vote->created : time()),
            '{' . $prefix . 'score}' => $vote->value,
            '{' . $prefix . 'reason}' => $vote->getFlagReason(),
        );
    }
}