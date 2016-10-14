<?php
class Sabai_Addon_Voting_Helper_RenderFavorite extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, array $settings = array())
    {
        $settings += array(
            'icon' => 'star',
            'icon_size' => 'large',
        );
        $target_id = 'sabai-voting-star-favorite-' . $entity->getId();
        if ($settings['icon_size'] === 'large') {
            $class = 'fa-lg';
        } else {
            $class = '';
        }
        if (!$application->getUser()->isAnonymous()) {
            $token = $application->Token('voting_vote_entity', 1800, true);
            $class .= empty($entity->data['voting_favorite_voted']) ? ' fa fa-' . $settings['icon'] . '-o' : ' fa fa-' . $settings['icon'];
            $link = $application->LinkToRemote(
                '<i class="' . $class . '"></i>',
                '#' . $target_id,
                $application->Entity_Url($entity, '/vote/favorite/form'),
                array('url' => $application->Entity_Url($entity, '/vote/favorite', array(Sabai_Request::PARAM_TOKEN => $token, 'value' => 1)), 'no_escape' => true, 'post' => true, 'success' => 'target.find("i").toggleClass("fa-' . $settings['icon'] . '", result.value == 1).toggleClass("fa-' . $settings['icon'] . '-o", result.value != 1).end().find("span").text(parseInt(result.sum, 10)); return false;', 'loadingImage' => false),
                array('title' => __('Mark/unmark this post as favorite (click again to undo)', 'sabai'), 'data-sabaipopover-title' => __('Mark/unmark this post as favorite', 'sabai'))
            );
        } else {
            $class .= ' fa fa-' . $settings['icon'] . '-o';  
            $link = $application->LinkToRemote(
                '<i class="' . $class . '"></i>',
                '#' . $target_id,
                $application->Entity_Url($entity, '/vote/favorite/form'),
                array('url' => $application->Entity_Url($entity, '/vote/favorite', array('value' => 1)), 'no_escape' => true, 'post' => true, 'loadingImage' => false),
                array('title' => __('Mark/unmark this post as favorite (click again to undo)', 'sabai'), 'data-sabaipopover-title' => __('Mark/unmark this post as favorite', 'sabai'))
            );
        }
        
        return sprintf('<span class="sabai-voting-star" id="%s">%s <span class="sabai-number">%d</span></span>', $target_id, $link, $entity->getSingleFieldValue('voting_favorite', 'count'));
    }
}