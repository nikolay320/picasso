<?php
class Sabai_Addon_Field_Helper_Types extends Sabai_Helper
{
    private $_features = array();

    /**
     * Returns all available field types
     * @param Sabai $application
     */
    public function help(Sabai $application, $useCache = true)
    {
        if (!$useCache
            || (!$field_types = $application->getPlatform()->getCache('field_types'))
        ) {
            $field_types = array();
            foreach ($application->getInstalledAddonsByInterface('Sabai_Addon_Field_ITypes') as $addon_name) {
                if (!$application->isAddonLoaded($addon_name)) continue;
                
                foreach ($application->getAddon($addon_name)->fieldGetTypeNames() as $type) {
                    $field_type = $application->getAddon($addon_name)->fieldGetType($type);
                    if (!is_object($field_type)
                        || null === ($info = $field_type->fieldTypeGetInfo())
                    ) {
                        continue;
                    }
                    $creatable = isset($info['creatable']) && !$info['creatable'] ? false : true;
                    $editable = $creatable || !isset($info['editable']) || $info['editable'] ? true : false;
                    $deletable = $editable && (!isset($info['deletable']) || $info['deletable']) ? true : false;
                    $widgets = $this->_getFeaturesByFieldType($application, isset($info['act_as_widget']) ? $info['act_as_widget'] : (isset($info['act_as']) ? $info['act_as'] : $type), 'Widget', 'label', __('Default', 'sabai'));
                    $renderers = $this->_getFeaturesByFieldType($application, isset($info['act_as_renderer']) ? $info['act_as_renderer'] : (isset($info['act_as']) ? $info['act_as'] : $type), 'Renderer', 'label', __('Default', 'sabai'));
                    $filters = $this->_getFeaturesByFieldType($application, isset($info['act_as_filter']) ? $info['act_as_filter'] : (isset($info['act_as']) ? $info['act_as'] : $type), 'Filter', 'label', __('Default', 'sabai'));
                    $sortable = $field_type instanceof Sabai_Addon_Field_ISortable;
                    $field_types[$type] = array(
                        'addon' => $addon_name,
                        'type' => $type,
                        'default_widget' => isset($info['default_widget']) && isset($widgets[$info['default_widget']]) ? $info['default_widget'] : current(array_keys($widgets)),
                        'default_renderer' => isset($info['default_renderer']) && isset($widgets[$info['default_renderer']]) ? $info['default_renderer'] : current(array_keys($renderers)),
                        'widgets' => $widgets,
                        'renderers' => $renderers,
                        'filters' => $filters,
                        'label' => (string)@$info['label'],
                        'description' => (string)@$info['description'],
                        'creatable' => $creatable,
                        'editable' => $editable,
                        'deletable' => $deletable,
                        'sortable' => $sortable ? (isset($info['sorts']) ? $info['sorts'] : true) : false,
                        'creatable_filters' => $this->_getFeaturesByFieldType($application, isset($info['act_as']) ? $info['act_as'] : $type, 'Filter', 'creatable', true),
                    );
                    $field_types[$type] += $info;
                }
            }
            uasort($field_types, array(__CLASS__, '_sortFieldTypesCallback'));
            $application->getPlatform()->setCache($application->Filter('field_types', $field_types), 'field_types', 0);
        }

        return $field_types;
    }

    private static function _sortFieldTypesCallback($a, $b)
    {
        return strcmp($a['label'], $b['label']);
    }
    
    private function _getFeaturesByFieldType(Sabai $application, $fieldType, $featureName, $key = 'label', $default = null)
    {
        if (!isset($this->_features[$featureName][$key])) {
            if (!isset($this->_features[$featureName])) {
                $this->_features[$featureName] = array();
            }
            $this->_features[$featureName][$key] = array();
            $helper = 'Field_' . $featureName . 's';
            $method1 = 'fieldGet' . $featureName;
            $method2 = 'field' . $featureName . 'GetInfo';
            foreach ($application->$helper() as $name => $addon) {
                if (!$application->isAddonLoaded($addon)) {
                    continue;
                }
                $info = $application->getAddon($addon)->$method1($name)->$method2();
                foreach ((array)@$info['field_types'] as $field_type) {
                    if (isset($info[$key])) {
                        if ($info[$key] === false) continue;
                    } else {
                        if (!isset($default)) continue;
                        $info[$key] = $default;
                    }
                    
                    $this->_features[$featureName][$key][$field_type][$name] = @$info[$key];
                }
            }
        }
        return isset($this->_features[$featureName][$key][$fieldType]) ? $this->_features[$featureName][$key][$fieldType] : array();        
    }
}