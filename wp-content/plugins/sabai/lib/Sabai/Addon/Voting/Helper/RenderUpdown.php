<?php
class Sabai_Addon_Voting_Helper_RenderUpdown extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity)
    {
        $updown = $entity->getFieldValue('voting_updown');
        $target_id = 'sabai-voting-updown-updown-' . $entity->getId();
        $class = '';
        if ($voted = @$entity->data['voting_updown_voted']) {
            if ($voted == 1) {
                $class = 'sabai-voting-updown-up';
            } elseif ($voted == -1) {
                $class = 'sabai-voting-updown-down';
            }
        }
        if ($application->Entity_IsAuthor($entity, $application->getUser())) {
            $can_upvote = $application->HasPermission($entity->getBundleName() . '_voting_own_updown');
        } else {
            $can_upvote = $application->HasPermission($entity->getBundleName() . '_voting_updown');
        }
        $can_downvote = $application->HasPermission($entity->getBundleName() . '_voting_down_updown');
        if ($can_upvote || $can_downvote) {
            $vote_token = $application->Token('voting_vote_entity', 1800, true);
            $on_success = 'target.find("> span").text(parseInt(result.sum, 10)); target.toggleClass("sabai-voting-updown-up", result.value == 1); target.toggleClass("sabai-voting-updown-down", result.value == -1); return false;';     
            if ($can_upvote) {
                $up_link = $application->LinkToRemote(
                    '',
                    '#' . $target_id,
                    $application->Entity_Url($entity, '/vote/updown/form'),
                    array('url' => $application->Entity_Url($entity, '/vote/updown', array(Sabai_Request::PARAM_TOKEN => $vote_token, 'value' => 1)), 'post' => true, 'success' => $on_success, 'loadingImage' => false),
                    array('title' => __('This post is useful (click again to undo)', 'sabai'), 'data-sabaipopover-title' => __('Mark this post as useful', 'sabai'))
                );
            } else {
                $up_link = sprintf('<a href="#%s" onclick="return false;" class="%s"></a>', $target_id, $class);
            }
            if ($can_downvote) {
                $down_link = $application->LinkToRemote(
                    '',
                    '#' . $target_id,
                    $application->Entity_Url($entity, '/vote/updown/form'),
                    array('url' => $application->Entity_Url($entity, '/vote/updown', array(Sabai_Request::PARAM_TOKEN => $vote_token, 'value' => -1)), 'post' => true, 'success' => $on_success, 'loadingImage' => false),
                    array('title' => __('This post is not useful (click again to undo)', 'sabai'), 'data-sabaipopover-title' => __('Unmark this post as useful', 'sabai'))
                );
            } else {
                $down_link = sprintf('<a href="#%s" onclick="return false;" class="%s"></a>', $target_id, $class);
            }
        } else {
            $up_link = sprintf('<a href="#%s" onclick="return false;" class="%s"></a>', $target_id, $class);
            $down_link = sprintf('<a href="#%s" onclick="return false;" class="%s"></a>', $target_id, $class);
        }

        return sprintf(
            '<div class="sabai-voting-updown %s" id="%s"><div class="sabai-voting-arrow-up fa fa-thumbs-up">%s</div><span class="sabai-number%s" title="%s">%d</span><div class="sabai-voting-arrow-down">%s</div></div>',
            $class,
            $target_id,
            $up_link,
            strlen($updown[0]['sum']) > 2 ? ' sabai-bignumber' : '',
            sprintf(_n('%d vote', '%d votes', $updown[0]['count'], 'sabai'), $updown[0]['count']),
            $updown[0]['sum'],
            $down_link
        );
    }
}