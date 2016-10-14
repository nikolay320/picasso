<?php
class Sabai_Addon_Entity_Helper_Link extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, array $options = array(), $path = '', array $params = array(), $fragment = '')
    {
        $classes = array(
            'sabai-entity-type-' . $entity->getType(),
            'sabai-entity-bundle-name-' . $entity->getBundleName(),
            'sabai-entity-bundle-type-' . $entity->getBundleType(),
        );
        $title = $entity->getTitle();
        if (!strlen($title)) {
            $title = __('Untitled', 'sabai');
        }
        return $application->LinkTo(
            $title,
            $application->Entity_Url($entity, $path, $params, $fragment),
            $options,
            array('class' => str_replace('_', '-', implode(' ', $classes)))
        );
    }
}