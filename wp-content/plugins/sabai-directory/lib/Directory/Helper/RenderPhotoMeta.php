<?php
class Sabai_Addon_Directory_Helper_RenderPhotoMeta extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_Entity $photo, $linkToListing = false)
    {
        if (!empty($linkToListing)) {
            $listing = $application->Content_ParentPost($photo);
        }
        $photo_link = $application->Entity_Permalink($photo, array('atts' => array('rel' => 'nofollow'), 'title' => $application->getPlatform()->getHumanTimeDiff($photo->getTimestamp())));
        if ($photo->content_reference) {
            return sprintf(
                __('%s by %s in %s', 'sabai-directory'),
                $photo_link,
                $application->UserIdentityLink($application->Entity_Author($photo)),
                $application->Entity_Permalink($photo->content_reference[0], array('atts' => array('rel' => 'nofollow'), 'title' => !empty($listing) ? $listing->getTitle() : null))
            );
        }
        if (empty($photo->directory_photo[0]['official'])) {
            if (!empty($listing)) {
                return sprintf(
                    __('%s by %s in %s', 'sabai-directory'),
                    $photo_link,
                    $application->UserIdentityLink($application->Entity_Author($photo)),
                    $application->Entity_Permalink($listing)
                );
            }
            return sprintf(
                __('%s by %s', 'sabai-directory'),
                $photo_link,
                $application->UserIdentityLink($application->Entity_Author($photo))
            );
        }
        if (!empty($listing)) {
            return sprintf(
                __('%s in %s', 'sabai-directory'),
                $photo_link,
                $application->Entity_Permalink($listing)
            );
        }
        return $photo_link;
    }
}