<?php
class Sabai_Addon_Questions_Controller_Admin_AccessControl extends Sabai_Addon_System_Controller_Admin_AccessControl
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $form = parent::_doGetFormSettings($context, $formStorage);
        $config = $this->getAddon()->getConfig();
        $role_options = array();
        foreach ($this->_roles as $role_name => $role) {
            if ($role->isGuest()) {
                continue;
            }
            $role_options[$role_name] = $role->title;
        }
        $form['access'] = array(
            '#title' => __('Restrict Access', 'sabai-discuss'),
            '#weight' => -1,
            'type' => array(              
                '#type' => 'radios',
                '#options' => array(
                    'public' => __('Public', 'sabai-discuss'),
                    'private' => __('Private', 'sabai-discuss'),
                    'closed' => _x('Closed', 'access control', 'sabai-discuss'),
                ),
                '#options_description' => array(
                    'public' => __('Everyone including non-registered users are allowed access.', 'sabai-discuss'),
                    'private' => __('Only members of selected roles are allowed access.', 'sabai-discuss'),
                    'closed' => __('Closed or disabled. No one is allowed access.', 'sabai-discuss')
                ),
                '#default_value' => $config['access']['type'],
            ),
            'roles' => array(
                '#title' => __('Only accessible by:', 'sabai-discuss'),
                '#type' => 'checkboxes',
                '#options' => $role_options,
                '#states' => array(
                    'visible' => array(
                        'input[name="access[type]"]' => array('value' => 'private'),
                    ),
                ),
                '#default_value' => array_merge((array)$config['access']['roles'], array_keys($this->_adminRoles)),
                '#options_disabled' => array_keys($this->_adminRoles),
            ),
            'redirect' => array(
                '#type' => 'textfield',
                '#title' => __('Redirect to:', 'sabai-discuss'),
                '#description' => __('Enter the path to URL that users denied access are redirected to.', 'sabai-discuss'),
                '#field_prefix' => rtrim($this->getScriptUrl('main'), '/') . '/',
                '#default_value' => $config['access']['redirect'],
                '#size' => 30,
                '#states' => array(
                    'visible' => array(
                        'input[name="access[type]"]' => array('value' => array('private', 'closed')),
                    ),
                ),
            ),
        );
        $form['permissions'] += array(
            'use_reputation' => array(
                '#type' => 'checkbox',
                '#on_label' => __('Permissions granted based on reputation points', 'sabai-discuss'),
                '#description' => __('Check this option to switch to reputation based user permission system.', 'sabai-discuss'),
                '#default_value' => !empty($config['perm_rep_enable']),
                '#weight' => -1,
            ),
        );
        $form['permissions']['normal']['#states'] = array(
            'visible' => array(
                'input[name="permissions[use_reputation][]"]' => array('type' => 'checked', 'value' => false),
            ),
        );
        $form['permissions']['reputation']['#states'] = array(
            'visible' => array(
                'input[name="permissions[use_reputation][]"]' => array('type' => 'checked', 'value' => true),
            ),
        );
        
        return $form;
    }
    
    
    protected function _getPermissionFormSettings(Sabai_Context $context, array $categories)
    {
        $form = array(
            'normal' => array(),
            'reputation' => array(),
        );
        $config = $this->getAddon()->getConfig();
        $default_points = $this->_getDefaultReputationPoints();        
        $text_align = $this->getPlatform()->isLanguageRTL() ? 'right' : 'left';
        foreach ($this->System_PermissionCategories($categories) as $category_name => $category) {
            // Create grid table
            $form['normal'][$category_name] = $form['reputation'][$category_name] = array(
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
                if ($role->isGuest()) {
                    // Guest role users do not have reputation points, so use checkboxes
                    $form['reputation'][$category_name][$role->name] = $form['normal'][$category_name][$role->name];
                } else {
                    $form['reputation'][$category_name][$role->name] = array(
                        '#type' => 'textfield',
                        '#size' => 6,
                        '#title' => $role_title,
                        '#disabled' => $is_admin_role,
                        '#weight' => $is_admin_role ? 0 : ++$role_weight,
                    );
                }
                $form['normal'][$category_name]['#column_attributes'][$role->name]
                    = $form['reputation'][$category_name]['#column_attributes'][$role->name]
                    = array('style' => 'width:' . round(60 / count($this->_roles)) .'%');
            }
            // Add rows
            foreach ($category['permissions'] as $permission_name => $permission) {
                $form['normal'][$category_name]['#default_value'][$permission_name]
                    = $form['reputation'][$category_name]['#default_value'][$permission_name]
                    = array('label' => $permission['title']);
                foreach ($this->_roles as $role_name => $role) {
                    if (isset($this->_adminRoles[$role_name])) {
                        $form['normal'][$category_name]['#default_value'][$permission_name][$role_name] = 1;
                        $form['reputation'][$category_name]['#default_value'][$permission_name][$role_name] = 0; 
                    } elseif ($role->isGuest()) {
                        if (!$permission['guest_allowed']) {
                            $form['normal'][$category_name]['#row_settings'][$permission_name][$role_name]
                                = $form['reputation'][$category_name]['#row_settings'][$permission_name][$role_name]
                                = array('#attributes' => array('disabled' => 'disabled'));
                        } else {
                            $form['normal'][$category_name]['#default_value'][$permission_name][$role_name]
                                = $form['reputation'][$category_name]['#default_value'][$permission_name][$role_name]
                                = !empty($role_permissions[$role_name][$permission_name]);
                        }
                    } else {
                        $form['normal'][$category_name]['#default_value'][$permission_name][$role_name] = !empty($role_permissions[$role_name][$permission_name]);
                        if (isset($config['perm_rep'][$role_name][$permission_name])) {
                            $form['reputation'][$category_name]['#default_value'][$permission_name][$role_name] = $config['perm_rep'][$role_name][$permission_name];
                        } elseif (isset($default_points[$permission_name])) {
                            $form['reputation'][$category_name]['#default_value'][$permission_name][$role_name] = $default_points[$permission_name];
                        } else {
                            $form['reputation'][$category_name]['#default_value'][$permission_name][$role_name] = 0;
                        }
                    }
                }
                $this->_allPermissions[] = $permission_name;
            }
        }

        return $form;
    }
    
    protected function _getCategories(Sabai_Context $context)
    {
        return array(
            $this->getAddon()->getQuestionsBundleName(),
            $this->getAddon()->getAnswersBundleName(),
            $this->getAddon()->getCategoriesBundleName(),
            $this->getAddon()->getTagsBundleName(),
        );
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $new_config = array(
            'access' => $form->values['access'],
        );
        if ($form->values['permissions']['use_reputation']) {
            $current_max = (int)$this->getAddon()->getConfig('rep_max');
            $permissions_by_role = $this->_extractPermissionsByRole($form->values['permissions']['reputation'], false, $current_max);
            $guest_permissions = $permissions_by_role['_guest_'];
            unset($permissions_by_role['_guest_']);
            $new_config += array(
                'perm_rep_enable' => true,
                'perm_rep' => array_diff_key($permissions_by_role, $this->_adminRoles), // do not save settings for admin roles,
                'rep_max' => $current_max, // save max for creating progress bars,
            );
            // Save guest role permissions. Use array_filter to exclude empty values
            $guest_permissions = array_keys(array_filter($guest_permissions));
            $this->_roles['_guest_']->removePermission($this->_allPermissions);
            if (!empty($guest_permissions)) {
                $this->_roles['_guest_']->addPermission($guest_permissions);
            }
            $this->_roles['_guest_']->commit();
        } else {
            parent::submitForm($form, $context);
            
            // Disable reputation based perm system
            $new_config += array(
                'perm_rep_enable' => false,
            );
        }
        
        $this->getAddon()->saveConfig($new_config);
        $this->reloadAddons();
    }
    
    private function _getDefaultReputationPoints()
    {
        $questions_bundle = $this->getAddon()->getQuestionsBundleName();
        $answers_bundle = $this->getAddon()->getAnswersBundleName();
        $tags_bundle = $this->getAddon()->getTagsBundleName();
        $categories_bundle = $this->getAddon()->getCategoriesBundleName();
        return array(
            sprintf('%s_add', $tags_bundle) => 0,
            sprintf('%s_edit', $tags_bundle) => 300,
            sprintf('%s_delete', $tags_bundle) => 1000,
            sprintf('%s_add', $categories_bundle) => 300,
            sprintf('%s_edit', $categories_bundle) => 300,
            sprintf('%s_delete', $categories_bundle) => 1000,
            sprintf('%s_add', $questions_bundle) => 0,
            sprintf('%s_edit_own', $questions_bundle) => 0,
            sprintf('%s_edit_any', $questions_bundle) => 300,
            sprintf('%s_manage', $questions_bundle) => 600,
            sprintf('%s_trash_own', $questions_bundle) => 0,
            sprintf('%s_close_own', $questions_bundle) => 0,
            sprintf('%s_close_any', $questions_bundle) => 400,
            sprintf('%s_voting_updown', $questions_bundle) => 15,
            sprintf('%s_voting_own_updown', $questions_bundle) => 100,
            sprintf('%s_voting_down_updown', $questions_bundle) => 125,
            sprintf('%s_voting_flag', $questions_bundle) => 15,
            sprintf('%s_comment_add', $questions_bundle) => 50,
            sprintf('%s_comment_edit_own', $questions_bundle) => 0,
            sprintf('%s_comment_edit_any', $questions_bundle) => 300,
            sprintf('%s_comment_delete_own', $questions_bundle) => 0,
            sprintf('%s_comment_vote', $questions_bundle) => 15,
            sprintf('%s_comment_vote_own', $questions_bundle) => 100,
            sprintf('%s_comment_flag', $questions_bundle) => 15,
            sprintf('%s_add', $answers_bundle) => 0,
            sprintf('%s_edit_own', $answers_bundle) => 0,
            sprintf('%s_edit_any', $answers_bundle) => 300,
            sprintf('%s_accept_any', $answers_bundle) => 500,
            sprintf('%s_manage', $answers_bundle) => 600,
            sprintf('%s_trash_own', $answers_bundle) => 0,
            sprintf('%s_voting_updown', $answers_bundle) => 15,
            sprintf('%s_voting_own_updown', $answers_bundle) => 100,
            sprintf('%s_voting_down_updown', $answers_bundle) => 125,
            sprintf('%s_voting_flag', $answers_bundle) => 15,
            sprintf('%s_comment_add', $answers_bundle) => 50,
            sprintf('%s_comment_edit_own', $answers_bundle) => 0,
            sprintf('%s_comment_edit_any', $answers_bundle) => 300,
            sprintf('%s_comment_delete_own', $answers_bundle) => 0,
            sprintf('%s_comment_vote', $answers_bundle) => 15,
            sprintf('%s_comment_vote_own', $answers_bundle) => 100,
            sprintf('%s_comment_flag', $answers_bundle) => 15,
        );
    }
}