<?php
class Sabai_Addon_Entity_Helper_FieldStorageImpl extends Sabai_Helper
{
    private $_handlers, $_impls = array();

    /**
     * Gets an implementation of Sabai_Addon_Entity_IFieldStorage interface
     * @param Sabai $application
     * @param string $storage
     */
    public function help(Sabai $application, $storage)
    {
        if (!isset($this->_impls[$storage])) {
            $field_storages = $this->_getFieldStorages($application);
            // Valid storage type?
            if (!isset($field_storages[$storage])
                || !$application->isAddonLoaded($field_storages[$storage])
            ) {
                throw new Sabai_UnexpectedValueException(sprintf('Invalid field storage: %s', $storage));
            }
            $this->_impls[$storage] = $application->getAddon($field_storages[$storage])->entityGetFieldStorage($storage);
        }

        return $this->_impls[$storage];
    }

    private function _getFieldStorages(Sabai $application)
    {
        if (!$field_storages = $application->getPlatform()->getCache('entity_fieldstorages')) {
            $field_storages = array();
            foreach ($application->getInstalledAddonsByInterface('Sabai_Addon_Entity_IFieldStorages') as $addon_name) {
                if (!$application->isAddonLoaded($addon_name)) continue;
                
                foreach ($application->getAddon($addon_name)->entityGetFieldStorageNames() as $storage_name) {
                    if (!$application->getAddon($addon_name)->entityGetFieldStorage($storage_name)) {
                        continue;
                    }
                    $field_storages[$storage_name] = $addon_name;
                }
            }
            $application->getPlatform()->setCache($field_storages, 'entity_fieldstorages', 0);
        }

        return $field_storages;
    }
}