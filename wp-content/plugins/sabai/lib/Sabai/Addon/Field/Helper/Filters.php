<?php
class Sabai_Addon_Field_Helper_Filters extends Sabai_Helper
{
    /**
     * Returns all available field filters
     * @param Sabai $application
     */
    public function help(Sabai $application, $useCache = true)
    {
        if (!$useCache
            || (!$field_filters = $application->getPlatform()->getCache('field_filters'))
        ) {
            $field_filters = array();
            foreach ($application->getInstalledAddonsByInterface('Sabai_Addon_Field_IFilters') as $addon_name) {
                if (!$application->isAddonLoaded($addon_name)) continue;
                
                foreach ($application->getAddon($addon_name)->fieldGetFilterNames() as $filter_name) {
                    if (!$application->getAddon($addon_name)->fieldGetFilter($filter_name)) {
                        continue;
                    }
                    $field_filters[$filter_name] = $addon_name;
                }
            }
            $application->getPlatform()->setCache($application->Filter('field_filters', $field_filters), 'field_filters');
        }

        return $field_filters;
    }
}