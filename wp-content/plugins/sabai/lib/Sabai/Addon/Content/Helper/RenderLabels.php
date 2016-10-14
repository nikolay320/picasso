<?php
class Sabai_Addon_Content_Helper_RenderLabels extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Content_Entity $entity, array $labels = array(), $featuredIcon = 'certificate')
    {
        return $application->Entity_RenderLabels($entity, array('labels' => $labels, 'no_feature' => empty($featuredIcon)));
    }
}