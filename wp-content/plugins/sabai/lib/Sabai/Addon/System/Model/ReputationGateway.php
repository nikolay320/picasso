<?php
class Sabai_Addon_System_Model_ReputationGateway extends Sabai_Addon_System_Model_Base_ReputationGateway
{
    public function getPermissionsByReputationPoints($points)
    {
        $sql = sprintf(
            'SELECT reputation_permission FROM %ssystem_reputation WHERE reputation_required_points <= %d',
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