<?php
class Sabai_Addon_Entity_Helper_Render extends Sabai_Helper
{
    public function help(Sabai $sabai, $entityType, array $entities = null, $bundleName = null, $displayMode = 'full')
    {
        if ($entityType instanceof Sabai_Addon_Entity_IEntity) {
            $entities[$entityType->getId()] = $entityType;
            $bundleName = $entityType->getBundleName();
            $entityType = $entityType->getType();
        } else {
            if (empty($entities)) {
                return array();
            }
            if (!isset($bundleName)) {
                return $this->_renderEntities($sabai, $entityType, $entities, $displayMode);
            }
        }
        return $this->_renderBundleEntities($sabai, $entityType, $entities, $bundleName, $displayMode);
    }
    
    private function _renderBundleEntities(Sabai $sabai, $entityType, array $entities, $bundleName, $displayMode)
    {
        $bundle = $sabai->Entity_Bundle($bundleName);
        
        // Notify entities are being rendered
        $sabai->Action('entity_render_entities', array($bundle, $entities, $displayMode));
        
        $ret = array();
        foreach ($entities as $entity_id => $entity) {
            $ret[$entity_id] = $this->_renderEntity($sabai, $bundle, $entity, $displayMode);
        }
        return $ret;
    }
    
    private function _renderEntities(Sabai $sabai, $entityType, array $entities, $displayMode)
    {    
        $entities_by_bundle = $bundles = array();
        foreach ($entities as $entity_id => $entity) {
            $entities_by_bundle[$entity->getBundleName()][$entity_id] = $entity;
        }
        foreach ($sabai->getModel('Bundle', 'Entity')
            ->name_in(array_keys($entities_by_bundle))
            ->fetch()
        as $bundle) {
            // Notify entities are being rendered
            $sabai->Action('entity_render_entities', array($bundle, $entities_by_bundle[$bundle->name], $displayMode));
            $bundles[$bundle->name] = $bundle;
        }
        $ret = array();
        foreach ($entities as $entity_id => $entity) {
            $ret[$entity_id] = $this->_renderEntity($sabai, $bundles[$entity->getBundleName()], $entity, $displayMode);
        }
        return $ret;
    }
    
    private function _renderEntity($application, $bundle, $entity, $displayMode)
    {        
        $entity_type = $entity->getType();
        $classes = array(
            'sabai-entity',
            'sabai-entity-type-' . $entity_type,
            'sabai-entity-bundle-name-' . str_replace('_', '-', $entity->getBundleName()),
            'sabai-entity-bundle-type-' . str_replace('_', '-', $entity->getBundleType()),
            'sabai-entity-mode-' . $displayMode,
        );
        $links = array();
        $buttons = array('links' => array(), 'options' => array());
        $id = 'sabai-entity-' . $entity_type . '-' . $entity->getId();
        
        // Let plugins modify classes and links for this entity
        $event_args = array($bundle, $entity, $displayMode, $id, &$classes, &$links, &$buttons);
        $application->Action('entity_render_html', $event_args);
        $application->Action('entity_render_' . $entity_type . '_html', $event_args);
        $application->Action('entity_render_' . $entity_type . '_' . $entity->getBundleType() . '_html', $event_args);
        
        return array(
            'entity' => $entity,
            'display_mode' => $displayMode,
            'classes' => $classes,
            'class' => str_replace('_', '-', implode(' ', $classes)),
            'id' => $id,
            'links' => $links,
            'buttons' => $buttons['links'],
            'button_options' => $buttons['options']
        );
    }
}