<?php
class Sabai_Addon_Content_Helper_PreviewUrl extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $path = '', array $params = array(), $fragment = '')
    {     
        return $application->Url(array(
            'route' => $application->Entity_Bundle($entity)->getPath() . '/preview/' . $entity->getId(),
            'script' => 'main',
        ));
    }
}