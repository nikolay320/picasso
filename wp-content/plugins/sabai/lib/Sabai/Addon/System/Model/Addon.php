<?php
class Sabai_Addon_System_Model_Addon extends Sabai_Addon_System_Model_Base_Addon
{
    public function getParams($includeNonCacheable = true)
    {
        if (!$params = @unserialize($this->params)) return array();

        if (!isset($params[0])) return $params; // backward compat

        if (!$includeNonCacheable) return $params[0];

        return array_merge($params[0], $params[1]);
    }

    public function setParams(array $params, array $paramsNonCacheable = array(), $merge = true)
    {
        if ($merge) {
            $all_params = array_merge($this->getParams(), $params, $paramsNonCacheable);
            $params = array_diff_key($all_params, $paramsNonCacheable);
            $paramsNonCacheable = array_diff_key($all_params, $params);
        }

        $this->params = serialize(array($params, $paramsNonCacheable));
        
        return $this;
    }

    public function isUninstallable()
    {
        return (bool)$this->uninstallable;
    }
}

class Sabai_Addon_System_Model_AddonRepository extends Sabai_Addon_System_Model_Base_AddonRepository
{
}