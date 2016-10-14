<?php
// For backward compat with v1.2
class Sabai_Addon_Content_Helper_RenderSummary extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Content_Entity $entity, $length = 150, $trimmarker = '...')
    {
        return $application->Summarize($entity->getContent(), $length, $trimmarker);
    }
}