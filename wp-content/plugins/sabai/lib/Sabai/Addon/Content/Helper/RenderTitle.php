<?php
class Sabai_Addon_Content_Helper_RenderTitle extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Content_Entity $entity, $link = true, Sabai_Addon_Content_Entity $parent = null, $parentTitleFormat = null, $featuredIcon = 'certificate', $altTitle = null)
    {
        $options = array(
            'alt' => isset($parent) ? $parent->getTitle() : $altTitle,
            'format' => isset($parent) ? $parentTitleFormat : null,
            'no_link' => !$link,
            'no_feature' => empty($featuredIcon),
        );
        return $application->Entity_RenderTitle($entity, $options);
    }
}