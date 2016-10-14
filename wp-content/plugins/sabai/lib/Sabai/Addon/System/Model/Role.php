<?php
class Sabai_Addon_System_Model_Role extends Sabai_Addon_System_Model_Base_Role
{
    public function setPermissions(array $permissions)
    {
        $perms = array();
        foreach ($permissions as $permission) {
            $perms[$permission] = 1;
        }
        $this->permissions = $perms;

        return $this;
    }

    public function addPermission($permission)
    {
        $permissions = $this->permissions;
        foreach ((array)$permission as $_permission) {
            if (empty($permissions[$_permission])) {
                $permissions[$_permission] = 1;
            }
        }
        $this->permissions = $permissions;

        return $this;
    }

    public function removePermission($permission)
    {
        $permissions = $this->permissions;
        if (!empty($permissions)) { 
            foreach ((array)$permission as $_permission) {
                unset($permissions[$_permission]);
            }
            $this->permissions = $permissions;
        }

        return $this;
    }
    
    public function removeAllPermissions()
    {
        $this->permissions = array();
        
        return $this;
    }
    
    public function isGuest()
    {
        return $this->name === '_guest_';
    }
}

class Sabai_Addon_System_Model_RoleRepository extends Sabai_Addon_System_Model_Base_RoleRepository
{
}