<?php
// For backward compat with v1.2
class Sabai_Addon_Content_Helper_RenderBody extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Content_Entity $entity)
    {
        return $application->Entity_RenderField($entity, 'content_body');
    }
}