<?php
class Sabai_Addon_Entity_Helper_SetGuestAuthorCookie extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $entity, $lifetime = 8640000 /* 100 days */)
    {
        // Set cookie to track guest user
        if ($entity->getAuthorId()) {
            return;
        } else {
            $application->Entity_LoadFields($entity);
            if ((!$guest_author_info = $entity->getGuestAuthorInfo())
                || empty($guest_author_info['guid'])
            ) {
                return;
            }
        }
        
        $cookie = $application->Cookie('sabai_entity_guids');
        if (is_string($cookie)
            && strlen($cookie)
            && ($entity_guids = explode(',', $cookie))
        ) {
            if (false !== $key = array_search($guest_author_info['guid'], $entity_guids)) {
                // remove from array so that the guid is always appended
                unset($entity_guids[$key]);
            }
        } else {
            $entity_guids = array();
        }
        $entity_guids[] = $guest_author_info['guid'];
        if (count($entity_guids) > 10) {
            $entity_guids = array_slice($entity_guids, -10, 10); // maximum of 10 guest posts
        }
        $application->Cookie('sabai_entity_guids', implode(',', $entity_guids), time() + $lifetime, true);
    }
}