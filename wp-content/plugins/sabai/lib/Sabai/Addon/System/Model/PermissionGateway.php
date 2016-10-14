<?php
class Sabai_Addon_System_Model_PermissionGateway extends Sabai_Addon_System_Model_Base_PermissionGateway
{
    public function getByReputationPoints($points)
    {
        $sql = sprintf(
            'SELECT permission_name FROM %ssystem_permission WHERE permission_reputation_points <= %d',
            $this->_db->getResourcePrefix(),
            $points
        );
        $ret = array();
        $rs = $this->_db->query($sql);
        while ($row = $rs->fetchRow()) {
            $ret[$row[0]] = 1;
        }
        return $ret;
    }
}