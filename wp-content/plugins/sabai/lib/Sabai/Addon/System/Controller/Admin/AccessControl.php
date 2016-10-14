<?php
abstract class Sabai_Addon_System_Controller_Admin_AccessControl extends Sabai_Addon_Form_Controller
{
    protected $_roles, $_adminRoles = array(), $_allPermissions = array(), $_collapsed = false;
    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_successFlash = __('Settings saved.', 'sabai');
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary');
        $this->_roles = $this->System_Roles();
        foreach ($this->_roles as $role_name => $role) {
            if ($role->isGuest()) {
                continue;
            }
            if ($this->getPlatform()->isAdministratorRole($role_name)) {
                $this->_adminRoles[$role_name] = $role_name;
            }
        }
        $form = array(
            '#tree' => true,
            'permissions' => array(
                '#title' => __('Permissions', 'sabai'),
                '#collapsed' => $this->_collapsed,
            ),
        );
        $form['permissions'] += $this->_getPermissionFormSettings($context, $this->_getCategories($context));
        
        return $form;
    }
    
    protected function _getPermissionFormSettings(Sabai_Context $context, array $categories)
    {
        $form = array(
            'normal' => array(),
        );
        $text_align = $this->getPlatform()->isLanguageRTL() ? 'right' : 'left';
        foreach ($this->System_PermissionCategories($categories) as $category_name => $category) {
            // Create grid table
            $form['normal'][$category_name] = array(
                '#type' => 'grid',
                '#collapsible' => false,
                '#row_attributes' => array('@all' => array('label' => array('style' => 'text-align:' . $text_align . ';'))),
                '#column_attributes' => array('label' => array('style' => 'text-align:' . $text_align . '; width:40%')),
                '#weight' => array_search($category_name, $categories),
                'label' => array(
                    '#type' => 'item',
                    '#title' => $category['title'],
                ),
            );
            // Add columns
            $role_weight = 0;
            $role_permissions = array();
            foreach ($this->_roles as $role_name => $role) {
                $role_permissions[$role_name] = $role->permissions;
                $is_admin_role = in_array($role_name, $this->_adminRoles);
                $role_title = $this->Translate($role->title);
                $form['normal'][$category_name][$role->name] = array(
                    '#type' => 'checkbox',
                    '#title' => $role_title,
                    '#disabled' => $is_admin_role,
                    '#weight' => $is_admin_role ? 0 : ++$role_weight,
                );
                $form['normal'][$category_name]['#column_attributes'][$role->name]
                    = array('style' => 'width:' . round(60 / count($this->_roles)) .'%');
            }
            // Add rows
            foreach ($category['permissions'] as $permission_name => $permission) {
                $form['normal'][$category_name]['#default_value'][$permission_name] = array('label' => $permission['title']);
                foreach ($this->_roles as $role_name => $role) {
                    if (isset($this->_adminRoles[$role_name])) {
                        $form['normal'][$category_name]['#default_value'][$permission_name][$role_name] = 1; 
                    } elseif ($role->isGuest()) {
                        if (!$permission['guest_allowed']) {
                            $form['normal'][$category_name]['#row_settings'][$permission_name][$role_name]
                                = array('#attributes' => array('disabled' => 'disabled'));
                        } else {
                            $form['normal'][$category_name]['#default_value'][$permission_name][$role_name]
                                = !empty($role_permissions[$role_name][$permission_name]);
                        }
                    } else {
                        $form['normal'][$category_name]['#default_value'][$permission_name][$role_name] = !empty($role_permissions[$role_name][$permission_name]);
                    }
                }
                $this->_allPermissions[] = $permission_name;
            }
        }

        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $roles_processed = array();
        foreach ($this->_extractPermissionsByRole($form->values['permissions']['normal']) as $role_name => $permissions) {
            $roles_processed[$role_name] = $role_name;
            if (in_array($role_name, $this->_adminRoles)) continue;

            $this->_roles[$role_name]->removePermission($this->_allPermissions)->addPermission(array_keys($permissions));
        }
        // Remove permissions from roles without any permissions selected
        foreach (array_keys($this->_roles) as $role_name) {
            if (in_array($role_name, $roles_processed)
                || in_array($role_name, $this->_adminRoles)
            ) {
                continue;
            }
                
            $this->_roles[$role_name]->removePermission($this->_allPermissions);
        }
            
        // Commit
        $this->getModel(null, 'System')->commit();
    }
    
    protected function _extractPermissionsByRole($values, $excludeEmpty = true, &$max = null)
    {
        $ret = array();
        foreach ($values as $category_name => $permissions) {
            foreach ($permissions as $permission_name => $roles) {
                foreach ($roles as $role_name => $value) {
                    if (!isset($this->_roles[$role_name])
                        || ($excludeEmpty && empty($value)) 
                    ) {
                        continue;
                    }
                        
                    $ret[$role_name][$permission_name] = $value;
                    if (isset($max) && $value > $max) {
                        $max = $value;
                    }
                }
            }
        }
        return $ret;
    }
    
    abstract protected function _getCategories(Sabai_Context $context);
}