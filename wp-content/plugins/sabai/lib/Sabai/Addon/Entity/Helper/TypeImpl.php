<?php
class Sabai_Addon_Entity_Helper_TypeImpl extends Sabai_Helper
{
    private $_impls = array();

    /**
     * Gets an implementation of Sabai_Addon_Entity_IType interface for a given entity type
     * @param Sabai $application
     * @param string $entityType
     */
    public function help(Sabai $application, $entityType, $useCache = true)
    {
        if (!isset($this->_impls[$entityType])) {
            $types = $this->_getEntityTypes($application, $useCache);
            if (!isset($types[$entityType])
                || !$application->isAddonLoaded($types[$entityType])
            ) {
                throw new Sabai_UnexpectedValueException(sprintf('Invalid entity type: %s', $entityType));
            }
            $this->_impls[$entityType] = $application->getAddon($types[$entityType])->entityGetType($entityType);
        }

        return $this->_impls[$entityType];
    }
    
    protected function _getEntityTypes(Sabai $application, $useCache)
    {
        if (!$useCache
            || (!$entity_types = $application->getPlatform()->getCache('entity_types'))
        ) {
            $entity_types = array();
            foreach ($application->getInstalledAddonsByInterface('Sabai_Addon_Entity_ITypes') as $addon_name) {
                if (!$application->isAddonLoaded($addon_name)) continue;
                
                foreach ($application->getAddon($addon_name)->entityGetTypeNames() as $entity_type) {
                    if (!$application->getAddon($addon_name)->entityGetType($entity_type)) {
                        continue;
                    }
                    $entity_types[$entity_type] = $addon_name;
                }
            }
            $application->getPlatform()->setCache($entity_types, 'entity_types', 0);
        }

        return $entity_types;
    }
}