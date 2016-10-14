<?php
class Sabai_Addon_Taxonomy_Helper_Parents extends Sabai_Helper
{    
    public function help(Sabai $application, $entity, $asEntity = true)
    {
        $entity_id = $entity instanceof Sabai_Addon_Taxonomy_Entity ? $entity->getId() : $entity;
        $terms = $application->getModel('Term', 'Taxonomy')->fetchParents($entity_id);
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