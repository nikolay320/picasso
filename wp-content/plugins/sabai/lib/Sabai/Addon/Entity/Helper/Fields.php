<?php
class Sabai_Addon_Entity_Helper_Fields extends Sabai_Helper
{    
    /**
     * Returns an array of all viewable fields of an entity by a user
     */
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $withProperty = false, SabaiFramework_User_Identity $identity = null)
    {
        $ret = array();
        if (!isset($identity)) {
            $identity = $application->getUser()->getIdentity();
        }
        if (!$is_admin = $application->IsAdministrator($identity)) {
            $viewer_roles = $identity->isAnonymous()
                ? array('_guest_')
                : $application->getPlatform()->getUserRolesByUser($identity->id);
        }
        foreach (array_keys($entity->getFieldValues($withProperty)) as $field_name) {
            if (!$field = $this->_isValidField($application, $entity, $field_name)) continue;
            
            if (!$is_admin && !$this->_isViewableField($field, $viewer_roles)) continue;

            $ret[$field_name] = $field;
        }
        return $ret;
    }
    
    protected function _isValidField(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $fieldName)
    {
        return strpos($fieldName, 'field_meta_') === 0 // exclude custom meta fields
            || (!$field = $application->Entity_Field($entity, $fieldName))
        ? false : $field;
    }
    
    protected function _isViewableField(Sabai_Addon_Entity_Model_Field $field, array $viewerRoles)
    {
        if (null === $roles = $field->getFieldData('view_roles')) return true;
        
        if (empty($roles)) return false;
        
        foreach ($roles as $role) {
            if (in_array($role, $viewerRoles)) {
                return true;
            }
        }
        return false;
    }
}