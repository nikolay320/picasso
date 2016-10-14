<?php
class Sabai_Addon_Field_Helper_Upgrade extends Sabai_Helper
{
    public function onFieldUpgradeSuccess(Sabai $application, $log, $previousVersion)
    {
        if (version_compare($previousVersion, '1.3.0', '<')) {            
            $field_types = array();
            $db = $application->getDB();
            $sql = sprintf('SELECT type_name, type_addon FROM %sfield_type', $db->getResourcePrefix());
            try {
                $rs = $db->query($sql);
                while ($row = $rs->fetchRow()) {
                    $field_types[$row[0]] = $row[1];
                }
            } catch (SabaiFramework_DB_QueryException $e) {
                $application->LogError($e);
            }
            $application->getPlatform()->setOption('field_types', $field_types);
        }
    }
}