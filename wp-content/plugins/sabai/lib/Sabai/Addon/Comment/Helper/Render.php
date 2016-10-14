<?php
class Sabai_Addon_Comment_Helper_Render extends Sabai_Helper
{
    public function help(Sabai $application, $comment, Sabai_Addon_Entity_IEntity $entity, Sabai_Addon_Entity_IEntity $parentEntity = null, $voteToken = null, $modal = false, $tag = 'li')
    {
        if (!is_array($comment)) {
            $comment = $comment->toArray();
        }
        $meta = $actions = $classes = array();
        $comment_user_id = $comment['author']->id;
        if (!$application->getUser()->isAnonymous()) {
            $is_own_comment = $comment_user_id === $application->getUser()->id;
            // Add edit link?
            if ($application->HasPermission($entity->getBundleName() . '_comment_edit_any')
                || ($is_own_comment && $application->HasPermission($entity->getBundleName() . '_comment_edit_own'))
            ) {
                $edit_title = __('Edit', 'sabai');
                $edit_url = $application->Entity_Url($entity, '/comments/' . $comment['id'] . '/edit');
                $edit_attr = array('title' => __('Edit this Comment', 'sabai'));
                if ($modal) {
                    $actions[] = $application->LinkToModal(
                        $edit_title,
                        $edit_url,
                        array('content' => 'target.focusFirstInput();', 'icon' => 'edit', 'width' => 600),
                        $edit_attr
                    );
                } else {
                    $actions[] = $application->LinkToRemote(
                        $edit_title,
                        '#sabai-comment-' . $comment['id'],
                        $edit_url,
                        array('target' => '.sabai-comment-form', 'content' => 'jQuery("#sabai-comment-' . $comment['id'] . ' .sabai-comment-main").hide(); target.focusFirstInput();', 'icon' => 'edit'),
                        $edit_attr
                    );
                }
            }
            // Add delete link?
            if ($application->getUser()->isAdministrator()
                || ($is_own_comment && $application->HasPermission($entity->getBundleName() . '_comment_delete_own'))
            ) {
                $actions[] = $application->LinkToModal(__('Delete', 'sabai'), $application->Entity_Url($entity, '/comments/' . $comment['id'] . '/delete'), array('width' => 470, 'icon' => 'trash-o'), array('title' => __('Delete this Comment', 'sabai')));
            }
            // Add unhide link?
            if ($application->HasPermission($entity->getBundleName() . '_manage')) {
                if ($comment['is_hidden']) {
                    $actions[] = $application->LinkToModal(__('Unhide', 'sabai'), $application->Entity_Url($entity, '/comments/' . $comment['id'] . '/hide'), array('width' => 470, 'icon' => 'eye'), array('title' => __('Unhide this Comment', 'sabai')));
                }
            }
            if ($comment['flag_count']) {
                // Show flag score as well if the user has the xxx_manage permission, otherwise show only the flag count 
                if ($application->HasPermission($entity->getBundleName() . '_manage')) {
                    $meta[] = '<li class="sabai-comment-flags"><span><i class="fa fa-flag"></i> ' . $comment['flag_count'] . ' (' . $comment['flag_sum'] . ')</span></li>';
                } else { 
                    $meta[] = '<li class="sabai-comment-flags"><span><i class="fa fa-flag"></i> ' . $comment['flag_count'] . '</span></li>';
                }
            } else {
                $meta[] = '<li class="sabai-comment-flags" style="display:none;"><span></span></li>';
            }
            // Display vote count
            if ($comment['vote_count']) {
                $meta[] = '<li class="sabai-comment-votes"><span><i class="fa fa-thumbs-up"></i> ' . $comment['vote_sum'] . '</span></li>';
            } else {
                $meta[] = '<li class="sabai-comment-votes" style="display:none;"><span></span></li>';
            }
            // Add flag/vote links?
            if ($voteToken !== false) {
                if (!$comment['flag_disabled'] && $application->HasPermission($entity->getBundleName() . '_comment_flag')) {
                    $meta[] = '<li class="sabai-comment-flag">' . $application->LinkToModal(__('flag', 'sabai'), $application->Entity_Url($entity, '/comments/' . $comment['id'] . '/flag'), array('width' => 470, 'error' => 'trigger.closest("li").remove();', 'icon' => 'flag'), array('title' => __('Flag this comment', 'sabai'))) . '</li>';
                }
                if (!$comment['vote_disabled']
                    && $application->HasPermission($entity->getBundleName() . '_comment_vote')
                    && (!$is_own_comment || $application->HasPermission($entity->getBundleName() . '_comment_vote_own'))
                ) {
                    if (!isset($voteToken)) {
                        $voteToken = $application->Token('comment_vote_comment', 1800, true);
                    }
                    $meta[] = '<li class="sabai-comment-vote">' . $application->LinkToRemote(__('vote', 'sabai'), '#sabai-comment-' . $comment['id'], $application->Entity_Url($entity, '/comments/' . $comment['id'] . '/vote', array(Sabai_Request::PARAM_TOKEN => $voteToken, 'value' => 1)), array('success' => 'target.find(".sabai-comment-votes").show().find("span").html("<i class=\"fa fa-thumbs-up\"></i> " + result.count); trigger.closest("li").remove(); target.find(".sabai-comment-meta li.sabai-comment-flag").remove(); return false;', 'error' => 'trigger.closest("li").remove();', 'icon' => 'thumbs-up'), array('title' => __('Vote for this comment', 'sabai'))) . '</li>';
                }
            }
        } else {
            // Display vote count
            if ($comment['vote_sum']) {
                $meta[] = '<li class="sabai-comment-votes"><span><i class="fa fa-thumbs-up"></i> ' . $comment['vote_sum'] . '</span></li>';
            }
        }
        
        $html = '<div class="sabai-comment-avatar">%1$s</div>
<ul class="sabai-comment-meta"><li class="sabai-comment-author">%2$s</li><li class="sabai-comment-date"><span title="%3$s">%5$s</span>%11$s</li>%6$s</ul>
<div class="sabai-comment-main">%7$s</div>
<div class="sabai-comment-form"></div>
%8$s
';
        if ($comment['is_hidden']) {
            $classes[] = 'sabai-comment-hidden';
        }
        if ($comment_user_id === $entity->getAuthorId()) {
            $classes[] = 'sabai-comment-owner';
        }
        if (isset($parentEntity) || ($parentEntity = $application->Content_ParentPost($entity, false))) {
            if ($comment_user_id === $parentEntity->getAuthorId()) {
                $classes[] = 'sabai-comment-parent-owner';
            }
        }
        return sprintf(
            isset($tag) ? '<%9$s id="sabai-comment-%4$d" class="%10$s">' . $html . '</%9$s>' : $html,
            $application->UserIdentityThumbnailSmall($comment['author']),
            $application->UserIdentityLink($comment['author']),
            $application->DateTime($comment['published_at']),
            $comment['id'],
            $application->getPlatform()->getHumanTimeDiff($comment['published_at']),
            !empty($meta) ? implode('', $meta) : '',
            $comment['body'],
            empty($actions) ? '' : '<div class="sabai-comment-actions">' . $application->ButtonLinks($actions) . '</div>',
            $tag,
            empty($classes) ? '' : implode(' ', $classes),
            $comment['edit_count'] ? '<i class="fa fa-pencil" title="' . sprintf(_n('this comment was edited %1$s', 'this comment was edited %2$d times, last edited %1$s', $comment['edit_count'], 'sabai'), $application->getPlatform()->getHumanTimeDiff($comment['edit_last_at']), $comment['edit_count']) . '"></i>' : ''
        );
    }
}