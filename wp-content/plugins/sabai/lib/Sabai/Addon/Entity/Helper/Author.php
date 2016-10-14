<?php
class Sabai_Addon_Entity_Helper_Author extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $entity)
    {
        if (!method_exists($entity, 'getAuthor')
            || (!$author = $entity->getAuthor())
        ) {
            $author = $application->UserIdentity($entity->getAuthorId());
        }
        if ($author->isAnonymous() && !$author->email) {
            if ($guest_author_info = $entity->getGuestAuthorInfo()) {
                // Because anonymous identity object is shared, we need to clone it to give a specific identity
                $author = clone $author;
                $author->name = $guest_author_info['name'];
                $author->email = $guest_author_info['email'];
                $author->url = $guest_author_info['url'];
            }
        }
        $entity->setAuthor($author);
        
        return $author;
    }
}