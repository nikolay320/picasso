<?php
class Sabai_Addon_Entity_Helper_SortableFields extends Sabai_Helper
{    
    /**
     * @param Sabai $application
     * @param string $bundleName
     */
    public function help(Sabai $application, $bundleName, $customOnly = false, $useCache = true)
    {
        $cache_id = 'entity_sortable_fields_' . $bundleName;
        if (!$useCache
            || (!$ret = $application->getPlatform()->getCache($cache_id))
        ) {
            $ret = array();
            $field_types = $application->Field_Types();
            foreach ($application->Entity_Field($bundleName) as $field) {
                if ($customOnly && !$field->isCustomField()) continue;
                
                if (strpos($field->getFieldName(), 'field_meta_') === 0) continue; // exclude meta fields
            
                if ($sortable = @$field_types[$field->getFieldType()]['sortable']) {
                    $field_title = (string)$field;
                    if (is_array($sortable)) {
                        foreach ($sortable as $_sortable) {
                            $name = $field->getFieldName();
                            if (!empty($_sortable['args'])) {
                                $name .= ',' . implode(',', $_sortable['args']);
                            }
                            $ret[$name] = array(
                                'label' => isset($_sortable['label']) ? sprintf($_sortable['label'], $field_title) : $field_title,
                                'field_name' => $field->getFieldName(),
                                'field_type' => $field->getFieldType(),
                            );
                        }
                    } else {
                        $ret[$field->getFieldName()] = array(
                            'label' => $field_title,
                            'field_name' => $field->getFieldName(),
                            'field_type' => $field->getFieldType(),
                        );
                    }
                }
            }
            $application->getPlatform()->setCache($ret, $cache_id);
        }
        return $ret;
    }
}