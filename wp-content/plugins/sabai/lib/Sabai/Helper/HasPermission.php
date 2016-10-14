<?php
class Sabai_Helper_HasPermission extends Sabai_Helper
{
    protected $_permissions = array(), $_rolePermissions = array();
    
    /**
     * Checks whether the user has a certain permission, e.g. hasPermission('A').
     * Pass in an array of permission names to check if the user has one of the supplied
     * permissions, e.g. hasPermission(array('A', 'B')).
     * It is also possible to check whether the user has a group of certain permissions
     * by passing in an array of permission array, e.g. hasPermission(array(array('A', 'B', 'C'))).
     * For another example, in order to see whether the user has permission A or both permissions B and C
     * would be: hasPermission(array('A', array('B', 'C')))
     *
     * @param Sabai $application
     * @param string|array $permission
     * @param SabaiFramework_User_Identity|string|null
     * @return bool
     */
    public function help(Sabai $application, $permission, $identity = null)
    {
        if (!isset($identity)) {
            $user = $application->getUser();
            if ($user->isAdministrator()) return true;

            $permissions = $this->_getUserPermissions($application, $user->getIdentity());
        } else {
            if ($identity instanceof SabaiFramework_User_Identity) {
                if ($application->IsAdministrator($identity)) return true;
                
                $permissions = $this->_getUserPermissions($application, $identity);
            } elseif (is_string($identity)) {
                // $identity is a role name
                $permissions = $this->_getRolePermissions($application, $identity);
            } else {
                return false;
            }
        }
        
        if (!empty($permissions)) {        
            if (is_string($permission)) {
                return isset($permissions[$permission]);
            }
        
            foreach ($permission as $_perm) {
                foreach ((array)$_perm as $__perm) {
                    if (!isset($permissions[$__perm])) continue 2;
                }
                return true;
            }
        }
        return false;
    }
    
    protected function _getUserPermissions(Sabai $application, SabaiFramework_User_Identity $identity)
    {
        if (!isset($this->_permissions[$identity->id])) {
            $permissions = array();
        
            if (!$identity->isAnonymous()) {
                // Fetch permissions by roles the user belongs to
                if ($roles = $application->getPlatform()->getUserRolesByUser($identity->id)) {
                    foreach ($application->getModel('Role', 'System')->name_in($roles)->fetch() as $role) {
                        if (!$role->permissions) continue;
                
                        $permissions += $role->permissions;
                    }
                }
            } else {
                // Fetch permission of guest roles
                if (($guest_role = $application->getModel('Role', 'System')->name_is('_guest_')->fetchOne())
                    && $guest_role->permissions
                ) {                
                    $permissions += $guest_role->permissions;
                }
            }
            // Allow addons to set permissions
            $application->Action('system_load_permissions', array($identity, &$permissions));
            
            $this->_permissions[$identity->id] = $permissions;
        }
        return $this->_permissions[$identity->id];
    }
    
    protected function _getRolePermissions(Sabai $application, $role)
    {
        if (!isset($this->_rolePermissions[$role])) {
            $this->_rolePermissions[$role] = ($_role = $application->getModel('Role', 'System')->name_is($role)->fetchOne())
                ? $_role->permissions
                : array();
        }
        return $this->_rolePermissions[$role];
    }
}