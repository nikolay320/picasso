<?php
class Sabai_Addon_Entity_Helper_PermalinkUrl extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $fragment = '')
    {
        return $application->Entity_Url($entity, '', array(), $fragment);
    }
}