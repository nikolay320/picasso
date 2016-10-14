<?php
class Sabai_Addon_Entity_Model_Bundle extends Sabai_Addon_Entity_Model_Base_Bundle
{    
    public function getPath()
    {
        return $this->path ? $this->path : '/' . str_replace('_', '/', $this->name);
    }

    public function getAdminPath()
    {
        return isset($this->info['admin_path']) ? $this->info['admin_path'] : '/' . str_replace('_', '/', $this->name);
    }
    
    public function setInfo($name, $value = null)
    {
        if ($value === null) {
            $this->info = array_diff_key($this->info, array($name => null));
        } else {
            $this->info = array($name => $value) + $this->info;
        }
    }
}

class Sabai_Addon_Entity_Model_BundleRepository extends Sabai_Addon_Entity_Model_Base_BundleRepository
{
}