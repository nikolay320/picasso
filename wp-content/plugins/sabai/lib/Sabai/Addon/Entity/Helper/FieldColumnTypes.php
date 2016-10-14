<?php
class Sabai_Addon_Entity_Helper_FieldColumnTypes extends Sabai_Helper
{
    /**
     * Returns column types index by entity field names
     * @param Sabai $application
     * @param bool $useCache
     */
    public function help(Sabai $application, $useCache = true)
    {
        if (!$useCache
            || (!$ret = $application->getPlatform()->getCache('entity_field_column_types'))
        ) {
            $field_schema = $application->Entity_FieldSchema();
            foreach ($application->getModel('FieldConfig', 'Entity')->fetch() as $field_config) {
                if (empty($field_schema[$field_config->name])) continue;
                
                $ret[$field_config->name] = array();
                foreach ($field_schema[$field_config->name]['columns'] as $column => $column_info) {
                    $ret[$field_config->name][$column] = $column_info['type'];
                }
            }
            $application->getPlatform()->setCache($ret, 'entity_field_column_types', 0);
        }

        return $ret;
    }
}