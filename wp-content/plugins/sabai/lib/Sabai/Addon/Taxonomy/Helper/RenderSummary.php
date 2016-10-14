<?php
// For backward compat with v1.2
class Sabai_Addon_Taxonomy_Helper_RenderSummary extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Taxonomy_Entity $entity, $length = 150, $trimmarker = '...')
    {
        return $application->Summarize($entity->getContent(), $length, $trimmarker);
    }
}