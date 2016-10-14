<?php
class Sabai_Addon_Entity_Helper_IsAuthor extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $entity, SabaiFramework_User $user = null)
    {
        if (!isset($user)) {
            $user = $application->getUser();
        }
        
        if (!$user->isAnonymous()) {
            return $entity->getAuthorId() === $user->id;
        }
        
        return (!$entity->getAuthorId() // must be a guest post
            && ($guest_author_info = $entity->getGuestAuthorInfo())
            && !empty($guest_author_info['guid'])
            && ($cookie = $application->Cookie('sabai_entity_guids'))
            && ($cookie_guids = explode(',', $cookie))
            && in_array($guest_author_info['guid'], $cookie_guids)
        );
    }
}