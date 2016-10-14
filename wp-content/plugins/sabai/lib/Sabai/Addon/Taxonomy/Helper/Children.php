<?php
class Sabai_Addon_Taxonomy_Helper_Children extends Sabai_Helper
{
    public function help(Sabai $application, $entity, $sort = null, $order = null, $asEntity = true)
    {
        $entity_id = $entity instanceof Sabai_Addon_Taxonomy_Entity ? $entity->getId() : $entity;
        $terms = $application->getModel('Term', 'Taxonomy')->fetchByParent($entity_id, 0, 0, $sort, $order);
        if (!$asEntity) {
            return $terms;
        }
        $ret = array();
        foreach ($terms as $term) {
            $ret[] = $term->toEntity();
        }
        return $ret;
    }
}