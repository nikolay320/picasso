<?php
// For backward compat with v1.2
class Sabai_Addon_Taxonomy_Helper_RenderBody extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Taxonomy_Entity $entity)
    {
        return $application->Entity_RenderField($entity, 'taxonomy_body');
    }
}