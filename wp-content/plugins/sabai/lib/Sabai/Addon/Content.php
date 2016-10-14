<?php
class Sabai_Addon_Content extends Sabai_Addon
    implements Sabai_Addon_Field_ITypes,
               Sabai_Addon_Field_IWidgets,
               Sabai_Addon_Field_IFilters,
               Sabai_Addon_Field_IRenderers,
               Sabai_Addon_Entity_ITypes,
               Sabai_Addon_System_IMainRouter,
               Sabai_Addon_System_IAdminRouter,
               Sabai_Addon_System_IPermissionCategories,
               Sabai_Addon_System_IPermissions
{
    const VERSION = '1.3.28', PACKAGE = 'sabai';
    const POST_STATUS_PUBLISHED = 'published', POST_STATUS_DRAFT = 'draft', POST_STATUS_PENDING = 'pending', POST_STATUS_TRASHED = 'trashed',
        TRASH_TYPE_SPAM = 1, TRASH_TYPE_OFFTOPIC = 2, TRASH_TYPE_OTHER = 3;

    public function entityGetTypeNames()
    {
        return array('content');
    }

    public function entityGetType($typeName)
    {
        return new Sabai_Addon_Content_EntityType($this, $typeName);
    }
    
    protected function _getContentTypeInfo($addonName, $bundleName)
    {
        return $this->_application->Filter('content_type_info', $this->_application->getAddon($addonName)->contentGetContentType($bundleName)->contentTypeGetInfo(), array($addonName, $bundleName));
    }

    public function onContentIContentTypesInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if (!$names = $addon->contentGetContentTypeNames()) return;

        $bundles = array();
        foreach ($names as $name) {
            $bundles[$name] = $this->_getContentTypeInfo($addon->getName(), $name);
            unset($bundles[$name]['content_permissions']);
        }
        $this->_application->getAddon('Entity')->createEntityBundles($addon, 'content', $bundles);
        
        // Reload system routing/premission tables to reflect changes
        $this->_application->getAddon('System')->reloadRoutes($this)->reloadRoutes($this, true)->reloadPermissions($this);
    }

    public function onContentIContentTypesUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getAddon('Entity')->deleteEntityBundles($addon, 'content');
        
        // Reload system routing/permission tables to reflect changes
        $this->_application->getAddon('System')->reloadRoutes($this)->reloadRoutes($this, true)->reloadPermissions($this);
    }

    public function onContentIContentTypesUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        if (!$names = $addon->contentGetContentTypeNames()) {
            $this->_application->getAddon('Entity')->deleteEntityBundles($addon, 'content');
        } else {
            $bundles = array();
            foreach ($names as $name) {
                $bundles[$name] = $this->_getContentTypeInfo($addon->getName(), $name);
                unset($bundles[$name]['content_permissions']);
            }
            $this->_application->getAddon('Entity')->updateEntityBundles($addon, 'content', $bundles);
        }
        
        // Reload system routing tables to reflect changes
        $this->_application->getAddon('System')->reloadRoutes($this)->reloadRoutes($this, true)->reloadPermissions($this);
    }
    
    public function systemGetPermissionCategories()
    {
        $ret = array();
        foreach ($this->_application->getModel('Bundle', 'Entity')->entitytypeName_is('content')->fetch() as $bundle) {
            if (!$this->_application->isAddonLoaded($bundle->addon)) continue;
            
            $ret[$bundle->name] = $this->_application->Entity_BundleLabel($bundle, false);
        }
        return $ret;
    }

    public function systemGetPermissions()
    {
        $ret = array();
        foreach ($this->_application->getModel('Bundle', 'Entity')->entitytypeName_is('content')->fetch() as $bundle) {
            if (!$this->_application->isAddonLoaded($bundle->addon)) continue;
            
            $weight = 0;
            $guest_author_enabled = !empty($bundle->info['content_guest_author']); // is content submittable by guest users?
            $permissions = array(
                'add' => array(__('Add %s', 'sabai'), '', $guest_author_enabled),
                'add2' => array(__('Add %s (without approval)', 'sabai'), '', $guest_author_enabled),
                'edit_own' => array(__('Edit own %s', 'sabai'), '', $guest_author_enabled),
                'edit_any' => __('Edit any %2$s', 'sabai'),
                'trash_own' => array(__('Delete own %s', 'sabai'), '', $guest_author_enabled),
                'manage' => __('Delete any %2$s / Manage flagged %1$s', 'sabai'),
            );
            // Add extra permissions by other add-ons
            $permissions = $this->_application->Filter('content_permissions', $permissions, array($bundle));
            $bundle_label = $this->_application->Entity_BundleLabel($bundle, false);
            $bundle_label_singular = $this->_application->Entity_BundleLabel($bundle, true);
            $bundle_label_lc = strtolower($bundle_label);
            $bundle_label_singular_lc = strtolower($bundle_label_singular);
            foreach ($permissions as $perm => $perm_label) {
                $weight += 5;
                if (is_array($perm_label)) {
                    $perm_desc = sprintf($perm_label[1], $bundle_label_lc, $bundle_label_singular_lc, $bundle_label, $bundle_label_singular);
                    $guest_allowed = !empty($perm_label[2]);
                    $perm_label = $perm_label[0];
                } else {
                    $perm_desc = '';
                    $guest_allowed = false;
                }
                $ret[$bundle->name . '_' . $perm] = array(
                    'label' => sprintf($perm_label, $bundle_label_lc, $bundle_label_singular_lc, $bundle_label, $bundle_label_singular),
                    'description' => $perm_desc,
                    'category' => $bundle->name,
                    'weight' => $weight,
                    'guest_allowed' => $guest_allowed,
                );
            }
            // Overwrite defaults or add new permissions defined by the addon of bundle
            $bundle_info = $this->_getContentTypeInfo($bundle->addon, $bundle->name);
            if (!empty($bundle_info['content_permissions'])) {
                foreach ($bundle_info['content_permissions'] as $perm => $perm_info) {
                    // Remove permission if info is set to false, otherwise, add new or overwrite existing one
                    if (false === $perm_info) {
                        unset($ret[$bundle->name . '_' . $perm]);
                    } else {
                        $perm_name = $bundle->name . '_' . $perm;
                        if (array_key_exists('label', $perm_info)) {
                            $perm_info['label'] = sprintf($perm_info['label'], $bundle_label_lc, $bundle_label_singular_lc, $bundle_label, $bundle_label_singular);
                        }
                        if (!isset($ret[$perm_name])) {
                            $weight += 5;
                            $ret[$perm_name] = $perm_info + array(
                                'weight' => $weight,
                                'category' => $bundle->name,
                                'description' => '',
                                'guest_allowed' => false);
                        } else {
                            $ret[$perm_name] = $perm_info + $ret[$perm_name];
                        }
                        
                    }
                }
            }
        }
        return $ret;
    }
    
    public function systemGetDefaultPermissions()
    {
        $ret = array();
        $permissions = array('add', 'add2', 'edit_own', 'trash_own');
        foreach ($this->_application->getModel('Bundle', 'Entity')->entitytypeName_is('content')->fetch() as $bundle) {
            if (!$this->_application->isAddonLoaded($bundle->addon)) continue;
            
            // Add extra permissions by other add-ons
            $permissions = $this->_application->Filter('content_default_permissions', $permissions, array($bundle));
            foreach ($permissions as $perm) {
                $ret[] = $bundle->name . '_' . $perm;
            }
            // Get default permissions defined by the addon of bundle
            $bundle_info = $this->_getContentTypeInfo($bundle->addon, $bundle->name);
            if (!empty($bundle_info['content_permissions'])) {
                foreach ($bundle_info['content_permissions'] as $perm => $perm_info) {
                    if (!empty($perm_info['default'])) {
                        $ret[] = $bundle->name . '_' . $perm;
                    }
                }
            }
            if (!empty($bundle_info['content_default_permissions'])) {
                foreach ($bundle_info['content_default_permissions'] as $perm) {
                    $ret[] = $bundle->name . '_' . $perm;
                }
            }
        }
        return $ret;
    }

    public function fieldGetTypeNames()
    {
        return array('content_post_title', 'content_post_status', 'content_post_published', 'content_post_id', 'content_post_views',
            'content_post_entity_bundle_name', 'content_post_entity_bundle_type', 'content_post_user_id',
            'content_post_slug', 'content_parent', 'content_children', 'content_children_count',
            'content_trashed', 'content_activity', 'content_guest_author', 'content_reference', 'content_featured');
    }

    public function fieldGetType($name)
    {
        switch ($name) {
            case 'content_guest_author':
                return new Sabai_Addon_Content_GuestAuthorFieldType($this, $name);
            case 'content_activity':
                return new Sabai_Addon_Content_ActivityFieldType($this, $name);
            case 'content_reference':
                return new Sabai_Addon_Content_ReferenceFieldType($this, $name);
            case 'content_children_count':
                return new Sabai_Addon_Content_ChildCountFieldType($this, $name);
            case 'content_parent':
                return new Sabai_Addon_Content_ParentFieldType($this, $name);
            case 'content_featured':
                return new Sabai_Addon_Content_FeaturedFieldType($this, $name);
            default:
                return new Sabai_Addon_Content_FieldType($this, $name);
        }
    }

    public function fieldGetWidgetNames()
    {
        return array('content_post_title', 'content_post_title_hidden', 'content_parent_autocomplete', 'content_guest_author');
    }

    public function fieldGetWidget($name)
    {
        switch ($name) {
            case 'content_parent_autocomplete':
                return new Sabai_Addon_Content_ParentFieldWidget($this, $name);
            case 'content_guest_author':
                return new Sabai_Addon_Content_GuestAuthorFieldWidget($this, $name);
            default:
                return new Sabai_Addon_Content_FieldWidget($this, $name);
        }
    }

    public function fieldGetFilterNames()
    {
        return array('content_featured');
    }

    public function fieldGetFilter($name)
    {
        return new Sabai_Addon_Content_FeaturedFieldFilter($this, $name);
    }
    
    public function fieldGetRendererNames()
    {
        return array('content_post_title');
    }

    public function fieldGetRenderer($name)
    {
        return new Sabai_Addon_Content_PostTitleFieldRenderer($this, $name);
    }
    
    public function systemGetMainRoutes()
    {
        $routes = array();
        
        foreach ($this->_application->getModel('Bundle', 'Entity')->entitytypeName_is('content')->fetch() as $bundle) {
            if (!$this->_application->isAddonLoaded($bundle->addon)) continue;
            
            $public = !isset($bundle->info['public']) || $bundle->info['public'] !== false;
            if (empty($bundle->info['parent'])) {
                $routes[$bundle->getPath()] = array(
                    'controller' => 'ListPosts',
                    'access_callback' => true,
                    'title_callback' => true,
                    'callback_path' => 'posts',
                    'data' => array(
                        'bundle_id' => $bundle->id,
                    ),
                    'priority' => 3,
                );
                $routes[$bundle->getPath() . '/_autocomplete'] = array(
                    'controller' => 'Autocomplete',
                    'access_callback' => true,
                    'callback_path' => 'autocomplete',
                    'type' => Sabai::ROUTE_CALLBACK,
                );
                $routes[$bundle->getPath() . '/sitemap'] = array(
                    'controller' => 'Sitemap',
                    'type' => Sabai::ROUTE_CALLBACK,
                    'priority' => 3,
                );
                if ($public) {
                    $routes[$bundle->info['permalink_path'] . '/:slug'] = array(
                        'controller' => 'ViewPost',
                        'access_callback' => true,
                        'title_callback' => true,
                        'callback_path' => 'post',
                        'format' => array(':slug' => '[a-z0-9~\.:_\-%]+'),
                        'priority' => 3,
                    );
                    $routes[$bundle->getPath() . '/add'] = array(
                        'controller' => 'AddPost',
                        'access_callback' => true,
                        'title_callback' => true,
                        'callback_path' => 'add_post',
                        'priority' => 3,
                    );
                    $routes[$bundle->info['permalink_path'] . '/:slug/edit'] = array(
                        'controller' => 'EditPost',
                        'access_callback' => true,
                        'title_callback' => true,
                        'callback_path' => 'edit_post',
                        'priority' => 3,
                    );
                    $routes[$bundle->info['permalink_path'] . '/:slug/delete'] = array(
                        'controller' => 'TrashPost',
                        'access_callback' => true,
                        'title_callback' => true,
                        'callback_path' => 'trash_post',
                        'priority' => 3,
                    );
                }
                if (!empty($bundle->info['content_previewable'])) {
                    $routes[$bundle->getPath() . '/preview/:entity_id'] = array(
                        'controller' => 'PreviewPost',
                        'format' => array(':entity_id' => '\d+'),
                        'access_callback' => true,
                        'title_callback' => true,
                        'callback_path' => 'preview_post',
                        'priority' => 3,
                    );
                }
            } else {
                if (!$parent_bundle = $this->_application->Entity_Bundle($bundle->info['parent'])) continue;

                $routes[$bundle->getPath()] = array(
                    'controller' => 'RedirectToParent',
                    'access_callback' => true,
                    'title_callback' => true,
                    'callback_path' => 'child_posts',
                    'data' => array(
                        'bundle_id' => $bundle->id,
                    ),
                    'priority' => 3,
                );
                $routes[$bundle->getPath() . '/:entity_id'] = array(
                    'controller' => 'RedirectToParentPost',
                    'format' => array(':entity_id' => '\d+'),
                    'access_callback' => true,
                    'title_callback' => true,
                    'callback_path' => 'child_post',
                    'priority' => 3,
                );
                if ($public) {
                    $routes[$bundle->getPath() . '/:entity_id/edit'] = array(
                        'controller' => 'EditChildPost',
                        'access_callback' => true,
                        'title_callback' => true,
                        'callback_path' => 'edit_child_post',
                        'priority' => 3,
                    );
                    $routes[$bundle->getPath() . '/:entity_id/delete'] = array(
                        'controller' => 'TrashChildPost',
                        'access_callback' => true,
                        'title_callback' => true,
                        'callback_path' => 'trash_child_post',
                        'priority' => 3,
                    );
                    // Add child content pages if the path starts with the parent bundle's path
                    if (strpos($bundle->getPath(), $parent_bundle->getPath() . '/') === 0) {
                        $base_path = $parent_bundle->info['permalink_path'] . '/:slug' . substr($bundle->getPath(), strlen($parent_bundle->getPath()));
                        $routes[$base_path] = array(
                            'controller' => 'ListPosts',
                            'access_callback' => true,
                            'title_callback' => true,
                            'callback_path' => 'post_children',
                            'data' => array(
                                'bundle_id' => $bundle->id,
                            ),
                            'priority' => 3,
                        );
                        $routes[$base_path . '/add'] = array(
                            'controller' => 'AddChildPost',
                            'access_callback' => true,
                            'title_callback' => true,
                            'callback_path' => 'add_post_child',
                            'priority' => 3,
                        );
                    }
                }
                if (!empty($bundle->info['content_previewable'])) {
                    $routes[$bundle->getPath() . '/preview/:entity_id'] = array(
                        'controller' => 'PreviewPost',
                        'format' => array(':entity_id' => '\d+'),
                        'access_callback' => true,
                        'title_callback' => true,
                        'callback_path' => 'preview_post',
                        'priority' => 3,
                    );
                }
            }
        }

        return $routes;
    }

    public function systemOnAccessMainRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'posts':
                if (isset($route['data']['bundle_id'])) {
                    if (!$context->bundle = $this->_application->getModel('Bundle', 'Entity')->fetchById($route['data']['bundle_id'])) {
                        return false;
                    }
                } elseif (isset($route['data']['bundle_name'])) {
                    if (!$context->bundle = $this->_application->Entity_Bundle($route['data']['bundle_name'])) {
                        return false;
                    }
                } else {
                    return false;
                }
                // Set the default error URL
                $context->setErrorUrl($route['path']);
                // Add current addon's template directory
                $context->addTemplateDir($this->_application->getPlatform()->getAssetsDir($this->_application->getAddon($context->bundle->addon)->getPackage()) . '/templates');
                // Let the original addon allow/deny access
                return $this->_application->getAddon($route['addon'])->systemOnAccessMainRoute($context, $route['callback_path'], $accessType, $route);
            case 'autocomplete':
                return !$this->_application->getUser()->isAnonymous();       
            case 'add_post':
                if ($accessType !== Sabai::ROUTE_ACCESS_CONTENT) {
                    // Show link to any user
                    return true;
                }
                if (!$this->_application->HasPermission($context->bundle->name . '_add')) {
                    if ($this->_application->getUser()->isAnonymous()) {        
                        $context->setUnauthorizedError($route['path']);
                    }
                    return false;
                }
                return true;
            case 'preview_post':
                $bundle = $context->child_bundle ? $context->child_bundle : $context->bundle;
                if (empty($bundle->info['content_previewable'])) {
                    return false;
                }
                if ((!$id = $context->getRequest()->asInt('entity_id'))
                    || (!$context->entity = $this->_application->Entity_Entity('content', $id))
                    || $context->entity->getBundleName() !== $bundle->name
                    || (!$context->entity->isPending() && !$context->entity->isDraft())
                ) {
                    return false;
                }
                return $this->_application->getUser()->isAdministrator()
                    || $context->entity->getAuthorId() === $this->_application->getUser()->id;
            case 'post':
                $slug = $context->getRequest()->asStr('slug');
                if (!strlen($slug)
                    || (!$slug = rawurldecode($slug))
                    || (!$post = $this->getModel('Post')->entityBundleName_is($context->bundle->name)->slug_is($slug)->fetchOne())
                    || $post->status !== self::POST_STATUS_PUBLISHED
                ) {
                    if (!is_numeric($slug)
                        || (!$post = $this->getModel('Post')->entityBundleName_is($context->bundle->name)->id_is($slug)->fetchOne())
                        || $post->status !== self::POST_STATUS_PUBLISHED
                    ) {
                        $context->setNotFoundError();
                        return false;
                    }
                }
                $GLOBALS['sabai_entity'] = $context->entity = $post->toEntity();
                $this->_application->Entity_LoadFields($context->entity);
                $context->setUrl($this->_application->Entity_Url($context->entity));
                return $this->_isPostRoutable($context->bundle, $context->entity);
            case 'post_children':
                return (($bundle_id = $route['data']['bundle_id'])
                    && ($context->child_bundle = $this->_application->getModel('Bundle', 'Entity')->fetchById($bundle_id))
                    && $context->child_bundle->entitytype_name === $context->bundle->entitytype_name 
                );
            case 'add_post_child':
                if ($accessType !== Sabai::ROUTE_ACCESS_CONTENT) {
                    // Show link to any user
                    return true;
                }
                if (!$this->_application->HasPermission($context->child_bundle->name . '_add')) {
                    if ($this->_application->getUser()->isAnonymous()) {
                        $context->setUnauthorizedError($this->_application->Entity_Url($context->entity, substr($route['path'], strpos($route['path'], ':slug') + 5)));
                    }
                    return false;
                }
                return true;
            case 'child_posts':
                if ($accessType !== Sabai::ROUTE_ACCESS_CONTENT) return true;
                if (isset($route['data']['bundle_id'])) {
                    if (!$context->child_bundle = $this->_application->getModel('Bundle', 'Entity')->fetchById($route['data']['bundle_id'])) {
                        return false;
                    }
                } elseif (isset($route['data']['bundle_name'])) {
                    if (!$context->child_bundle = $this->_application->Entity_Bundle($route['data']['bundle_name'])) {
                        return false;
                    }
                } else {
                    return false;
                }
                return true;
            case 'child_post':
                if ((!$id = $context->getRequest()->asInt('entity_id'))
                    || (!$context->entity = $this->_application->Entity_Entity('content', $id, false))
                    || $context->entity->getBundleName() !== $context->child_bundle->name
                    || !$context->entity->isPublished()
                ) {
                    $context->setNotFoundError();
                    return false;
                }
                $this->_application->Entity_LoadFields($context->entity);
                return $this->_isPostRoutable($context->child_bundle, $context->entity);
            case 'edit_post':
                return $this->_application->HasPermission($context->bundle->name . '_edit_any')
                    || ($this->_application->Entity_IsAuthor($context->entity, $this->_application->getUser())
                           && $this->_application->HasPermission($context->bundle->name . '_edit_own'));
            case 'edit_child_post':
                return $this->_application->HasPermission($context->child_bundle->name . '_edit_any')
                    || ($this->_application->Entity_IsAuthor($context->entity, $this->_application->getUser())
                           && $this->_application->HasPermission($context->child_bundle->name . '_edit_own'));
            case 'trash_post':
                return $this->_application->HasPermission($context->bundle->name . '_manage')
                    || ($this->_application->Entity_IsAuthor($context->entity, $this->_application->getUser())
                           && $this->_application->HasPermission($context->bundle->name . '_trash_own'));
            case 'trash_child_post':
                return $this->_application->HasPermission($context->child_bundle->name . '_manage')
                    || ($this->_application->Entity_IsAuthor($context->entity, $this->_application->getUser())
                           && $this->_application->HasPermission($context->child_bundle->name . '_trash_own'));
        }
    }

    public function systemGetMainRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'posts':
                // Let the original addon render title
                return $this->_application->getAddon($route['addon'])->systemGetMainRouteTitle($context, $route['callback_path'], $title, $titleType, $route);
            case 'child_posts':
                return $this->_application->Entity_BundleLabel($context->child_bundle, false);
            case 'post':
            case 'child_post':
            case 'preview_post':
                return $context->entity->getTitle();
            case 'add_post':
                return sprintf(__('Add %s', 'sabai'), $this->_application->Entity_BundleLabel($context->bundle, true));
            case 'edit_post':
                return sprintf(__('Edit %s', 'sabai'), $this->_application->Entity_BundleLabel($context->bundle, true));
            case 'trash_post':
                return sprintf(__('Delete %s', 'sabai'), $this->_application->Entity_BundleLabel($context->bundle, true));
            case 'post_children':
                return sprintf(__('%s for "%s"', 'sabai'), $this->_application->Entity_BundleLabel($context->child_bundle, false), $context->entity->getTitle());
            case 'add_post_child':
                return sprintf(__('Add %s', 'sabai'), $this->_application->Entity_BundleLabel($context->child_bundle, true));
            case 'edit_child_post':
                return sprintf(__('Edit %s', 'sabai'), $this->_application->Entity_BundleLabel($context->child_bundle, true));
            case 'trash_child_post':
                return sprintf(__('Delete %s', 'sabai'), $this->_application->Entity_BundleLabel($context->child_bundle, true));
            case 'user_posts':
                return $this->_application->Entity_BundleLabel($context->bundle, false);
        }
    }
    
    private function _isPostRoutable($bundle, $entity)
    {
        if (!isset($bundle->info['public']) || $bundle->info['public'] !== false) return true;
        
        return $this->_application->getAddon($bundle->addon)
            ->contentGetContentType($bundle->name)
            ->contentTypeIsPostRoutable($entity, $this->_application->getUser());
    }
    
    public function onEntityCreateBundlesSuccess($entityType, $bundles)
    {
        if (!in_array($entityType, array('content'))) return;

        $reload_routes = false;
        foreach ($bundles as $bundle) {
            // Associate with trash info field
            $this->_application->getAddon('Entity')->createEntityField(
                $bundle,
                'content_trashed',
                array(
                    'type' => 'content_trashed',
                    'settings' => array(),
                    'max_num_items' => 1,
                ),
                Sabai_Addon_Entity::FIELD_REALM_ALL
            );
            // Add content activity field
            $this->_application->getAddon('Entity')->createEntityField(
                $bundle,
                'content_activity',
                array(
                    'type' => 'content_activity',
                    'settings' => array(),
                    'weight' => 99,
                    'max_num_items' => 1,
                ),
                Sabai_Addon_Entity::FIELD_REALM_ALL
            );
            // Add the body field if not explicitly disabled
            if (!isset($bundle->info['content_body']) || false !== $bundle->info['content_body']) {
                $body_settings = (array)$bundle->info['content_body'];
                $field_settings = array(
                    'type' => 'text',
                    'hide_label' => !empty($body_settings['hide_label']),
                    'label' => isset($body_settings['label']) ? $body_settings['label'] : __('Body', 'sabai'),
                    'description' => isset($body_settings['description']) ? $body_settings['description'] : '',
                    'widget' => $this->_application->getPlatform() instanceof Sabai_Platform_WordPress ? 'wordpress_editor' : 'markdown_textarea',
                    'widget_settings' => isset($body_settings['widget_settings']) ? $body_settings['widget_settings'] : array(),
                    'required' => !empty($body_settings['required']),
                    'weight' => isset($body_settings['weight']) ? $body_settings['weight'] : null,
                );
                if (!isset($body_settings['filter']) || false !== $body_settings['filter']) {
                    $field_settings['filter'] = array(
                        'type' => 'keyword',
                        'name' => 'content_body',
                        'title' => __('Keyword', 'sabai'),
                        'row' => @$field_settings['filter']['row'],
                        'col' => @$field_settings['filter']['col'],
                        'weight' => @$field_settings['filter']['weight'],
                        'settings' => @$field_settings['filter']['settings'],
                    );
                }
                $field_settings += $body_settings;
                $this->_application->getAddon('Entity')->createEntityField($bundle, 'content_body', $field_settings, Sabai_Addon_Entity::FIELD_REALM_ALL);
                $bundle->setInfo('content_body', true);
            }
            // Add the featured content field?
            if (isset($bundle->info['content_featurable'])) {
                if ($bundle->info['content_featurable'] !== false) {
                    $this->_application->getAddon('Entity')->createEntityField(
                        $bundle,
                        'content_featured',
                        array(
                            'type' => 'content_featured',
                            'max_num_items' => 1,
                            'filter' => array(
                                'type' => 'content_featured',
                                'name' => 'content_featured',
                                'title' => __('Featured/Unfeatured', 'sabai'),
                                'row' => @$bundle->info['content_featurable']['filter']['row'],
                                'col' => @$bundle->info['content_featurable']['filter']['col'],
                                'weight' => @$bundle->info['content_featurable']['filter']['weight'],
                                'settings' => @$bundle->info['content_featurable']['filter']['settings'],
                            ),
                        ),
                        Sabai_Addon_Entity::FIELD_REALM_ALL
                    );
                    $bundle->setInfo('content_featurable', true);
                } else {
                    if ($field = $this->_application->Entity_Field($bundle->name, 'content_featured')) {
                        $field->markRemoved()->commit();
                    }
                }
            }
            // Add the guest author field if not explicitly disabled
            if (isset($bundle->info['content_guest_author'])) {
                if ($bundle->info['content_guest_author'] !== false) {
                    $field_settings = (array)@$bundle->info['content_guest_author'];
                    $this->_application->getAddon('Entity')->createEntityField(
                        $bundle,
                        'content_guest_author',
                        array(
                            'type' => 'content_guest_author',
                            'label' => isset($field_settings['label']) ? $field_settings['label'] : __('Guest Author', 'sabai'),
                            'hide_label' => !empty($field_settings['hide_label']),
                            'description' => isset($field_settings['description']) ? $field_settings['description'] : '',
                            'widget' => 'content_guest_author',
                            'widget_settings' => isset($field_settings['widget_settings']) ? $field_settings['widget_settings'] : array(),
                            'weight' => isset($field_settings['weight']) ? $field_settings['weight'] : null,
                            'max_num_items' => 1,
                        ),
                        Sabai_Addon_Entity::FIELD_REALM_ALL
                    );
                    $bundle->setInfo('content_guest_author', true);
                } else {
                    if ($field = $this->_application->Entity_Field($bundle->name, 'content_guest_author')) {
                        $field->markRemoved()->commit();
                    }
                }
            }
            
            // Add the content reference field?
            if (isset($bundle->info['content_reference'])) {
                if ($bundle->info['content_reference'] !== false) {
                    $this->_application->getAddon('Entity')->createEntityField(
                        $bundle,
                        'content_reference',
                        array(
                            'type' => 'content_reference',
                            'label' => is_string($bundle->info['content_reference'])
                                ? $this->_application->Entity_BundleLabel($bundle->info['content_reference'], true)
                                : null,
                        ),
                        Sabai_Addon_Entity::FIELD_REALM_ALL
                    );
                } else {
                    if ($field = $this->_application->Entity_Field($bundle->name, 'content_reference')) {
                        $field->markRemoved()->commit();
                    }
                }
                $bundle->setInfo('content_reference');
            }
             
            // Check if the content type is a child content type
            if (empty($bundle->info['parent'])) {
                // not a child content type
                continue;
            }
            $parent_bundle_name = $bundle->info['parent'];
            if (!$parent_bundle = $this->_application->Entity_Bundle($parent_bundle_name)) {
                continue;
            }        
            // It is a child content type, so add parent/child relationship fields
            $reload_routes = true;

            $this->_application->getAddon('Entity')->createEntityField(
                $bundle,
                'content_parent',
                array(
                    'type' => 'content_parent',
                    'settings' => array(),
                    'label' => $this->_application->Entity_BundleLabel($parent_bundle, true),
                    'weight' => 99,
                    'required' => true,
                    'max_num_items' => 1,
                ),
                Sabai_Addon_Entity::FIELD_REALM_ALL
            );
            $this->_application->getAddon('Entity')->createEntityField(
                $parent_bundle,
                'content_children',
                array(
                    'type' => 'content_children',
                    'settings' => array(),
                    'weight' => 99,
                ),
                Sabai_Addon_Entity::FIELD_REALM_ALL
            );
            $this->_application->getAddon('Entity')->createEntityField(
                $parent_bundle,
                'content_children_count',
                array(
                    'type' => 'content_children_count',
                    'settings' => array(),
                    'weight' => 99,
                ),
                Sabai_Addon_Entity::FIELD_REALM_ALL
            );
        }
        if ($reload_routes) {
            // Reload system routing tables to reflect changes
            $this->_application->getAddon('System')->reloadRoutes($this);
        }
    }
    
    public function onEntityUpdateBundlesSuccess($entityType, $bundles)
    {
        $this->onEntityCreateBundlesSuccess($entityType, $bundles);
    }
    
    public function onEntityDeleteBundlesSuccess($entityType, $bundles)
    {
        if ($entityType !== 'content') return;
        
        $criteria = $this->getModel()->createCriteria('Post')->entityBundleName_in(array_keys($bundles));
        $this->getModel()->getGateway('Post')->deleteByCriteria($criteria);
    }
    
    public function systemGetAdminRoutes()
    {
        $routes = array();
        
        foreach ($this->_application->getModel('Bundle', 'Entity')->entitytypeName_is('content')->fetch() as $bundle) {
            if (!$this->_application->isAddonLoaded($bundle->addon)) continue;
            
            $is_child_bundle = !empty($bundle->info['parent']);
            $routes[$bundle->getAdminPath()] = array(
                'controller' => $is_child_bundle ? 'ListChildPosts' : 'ListPosts',
                'access_callback' => true,
                'callback_path' => $is_child_bundle ? 'child_posts' : 'posts',
                'data' => array(
                    'bundle_id' => $bundle->id,
                    'clear_tabs' => true,
                ),
                'title_callback' => true,
                'ajax' => 1,
            );
            $routes[$bundle->getAdminPath() . '/add'] = array(
                'controller' => $is_child_bundle ? 'AddChildPost' : 'AddPost',
                'title_callback' => true,
                'callback_path' => 'add_post',
                'weight' => 1,
            );
            $routes[$bundle->getAdminPath() . '/:entity_id'] = array(
                'controller' => $is_child_bundle ? 'EditChildPost' : 'EditPost',
                'format' => array(':entity_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'edit_post',
                'data' => array('clear_tabs' => true),
            );
            $routes[$bundle->getAdminPath() . '/:entity_id/view'] = array(
                'controller' => 'ViewPost',
                'access_callback' => true,
                'callback_path' => 'view_post',
            );
            $routes[$bundle->getAdminPath() . '/:entity_id/trash'] = array(
                'controller' => 'TrashPost',
                'access_callback' => true,
                'callback_path' => 'trash_post',
            );
            $routes[$bundle->getAdminPath() . '/:entity_id/delete'] = array(
                'controller' => 'DeletePost',
                'access_callback' => true,
                'callback_path' => 'delete_post',
            );
            $routes[$bundle->getAdminPath() . '/:entity_id/restore'] = array(
                'controller' => 'RestorePost',
                'access_callback' => true,
                'callback_path' => 'restore_post',
            );
            $routes[$bundle->getAdminPath() . '/_autocomplete'] = array(
                'controller' => 'Autocomplete',
                'type' => Sabai::ROUTE_CALLBACK,
            );
        }   

        return $routes;
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'posts':
                if (!$bundle = $this->_application->getModel('Bundle', 'Entity')->fetchById($route['data']['bundle_id'])) {
                    return false;
                }
                $context->bundle = $bundle;
                // Add current addon's template directory
                $context->addTemplateDir($this->_application->getPlatform()->getAssetsDir($this->_application->getAddon($context->bundle->addon)->getPackage()) . '/templates');
                return true;
            case 'child_posts':
                if (!$bundle = $this->_application->getModel('Bundle', 'Entity')->fetchById($route['data']['bundle_id'])) {
                    return false;
                }
                $context->child_bundle = $bundle;
                return true;
            case 'edit_post':
                if ((!$id = $context->getRequest()->asInt('entity_id'))
                    || (!$entity = $this->_application->Entity_Entity('content', $id))
                ) {
                    return false;
                }
                // Add new content link to top level menu
                $bundle = $this->_application->Entity_Bundle($entity);
                $context->addMenu(array(
                    'title' => sprintf(__('Add %s', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true)),
                    'url' => $bundle->getAdminPath() . '/add',
                    'options' => array(),
                ), true);
                if (!$entity->isTrashed()
                    && (!isset($bundle->info['public']) || $bundle->info['public'] !== false)
                ) {
                    $context->addMenu(array(
                        'title' => sprintf(__('View %s', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true)),
                        'url' => $bundle->getAdminPath() . '/' . $id . '/view',
                        'options' => array(),
                    ), true);
                }
                $context->entity = $entity;
                return true;
            case 'trash_post':
                return !$context->entity->isTrashed();
            case 'view_post':
                $bundle = $this->_application->Entity_Bundle($context->entity);
                return !$context->entity->isTrashed() && (!isset($bundle->info['public']) || $bundle->info['public'] !== false);
            case 'delete_post':
                return $context->entity->isTrashed();
            case 'restore_post':
                return $context->entity->isTrashed() && !$context->entity->getSingleFieldValue('content_trashed', 'parent_entity_id');
        }
    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'posts':
            case 'child_posts':
                $bundle_label = $this->_application->Entity_BundleLabel($context->child_bundle ? $context->child_bundle : $context->bundle, false);
                return $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT ? sprintf(_x('All %s', 'tab', 'sabai'), $bundle_label) : $bundle_label;
            case 'edit_post':
                if ($titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT) {
                    return __('Edit', 'sabai');
                }
                return strlen($context->entity->getTitle())
                    ? $context->entity->getTitle()
                    : sprintf('%s #%d', $this->_application->Entity_BundleLabel($context->child_bundle ? $context->child_bundle : $context->bundle, true), $context->entity->getId());
            case 'add_post':
                return sprintf(__('Add %s', 'sabai'), $this->_application->Entity_BundleLabel($context->child_bundle ? $context->child_bundle : $context->bundle, true));
        }
    }
    
    public function onEntityCreateContentEntity($bundle, &$values)
    {
        if (!isset($values['content_activity'])) {
            $values['content_activity'] = array(
                array(
                    'active_at' => !empty($values['content_post_published']) ? $values['content_post_published'] : time(),
                    'edited_at' => 0,
                ),
            );
        }
    }
        
    public function onEntityUpdateContentEntity($bundle, $entity, &$values)
    {
        if (!isset($values['content_activity'])) {
            // update activity timestamp only when the title, body, or another specific field is modified
            if (!$updated = isset($values['content_body']) || isset($values['content_post_title'])) {
                if (!empty($bundle->info['content_activity'])) {
                    foreach ((array)$bundle->info['content_activity'] as $activity) {
                        if (isset($values[$activity])) {
                            $updated = true;
                            break;
                        }
                    }
                }
            }
            if ($updated) {   
                $values['content_activity'] = array(
                    array(
                        'active_at' => time(),
                        'edited_at' => time(),
                    ),
                );
            }
        }
    }
    
    public function onEntityCreateContentEntitySuccess($bundle, $entity, $values, $extraArgs)
    {
        if (empty($extraArgs['content_skip_update_parent'])) {
            $this->updateParentPost($entity, $entity->getTimestamp(), true);
        }

        if ($entity->isPending()) {
            $this->_application->Content_CountPendingPosts(true);
        }
    }
    
    public function onEntityUpdateContentEntitySuccess($bundle, $entity, $oldEntity, $values, $extraArgs)
    {
        $timestamp = $update_children_count = $update_pending_count = false;
        // Content updated?
        if (isset($values['content_body']) || isset($values['content_post_title'])) {
            // Update last activity timestamp of the parent with the last edited timestamp of the updated entity
            $timestamp = $entity->getSingleFieldValue('content_activity', 'edited_at');
        }
        // Content status changed?
        if (isset($values['content_post_status'])) {
            if ($oldEntity->isPublished() || $entity->isPublished()) {
                // The content was published or unpublished
                $update_children_count = true;
            }
            if ($oldEntity->isPending() || $entity->isPending()) {
                $update_pending_count = true;
            }
        }
        // Update number of pending posts
        if ($update_pending_count) {
            $this->_application->Content_CountPendingPosts(true);
        }
        
        if (empty($extraArgs['content_skip_update_parent'])) {
            $this->updateParentPost($entity, $timestamp, $update_children_count);
        }
        
        if ($oldEntity->isPending() && $entity->isPublished()) {
            $this->_application->Action('content_post_published', array($entity));
            $this->_application->Action('content_' . $bundle->type . '_post_published', array($entity));
        }
    }
        
    public function onEntityDeleteContentEntitySuccess($bundle, $entity, $entityIds, $extraArgs)
    {         
        // Fetch parent
        if (!$parent = $this->_application->Content_ParentPost($entity, false)) {
            return;
        }
        // Do not update if parent is also deleted
        if (in_array($parent->getId(), $entityIds)) {
            return;
        }        
        $last_active_content = $this->_application->Entity_Query('content')
            ->fieldIs('content_parent', $parent->getId())
            ->sortByField('content_activity', 'DESC', 'active_at')
            ->fetch(1);
        if (empty($last_active_content)) {
            // no last active child content
            $last_active_at = ($edited_at = $parent->getSingleFieldValue('content_activity', 'edited_at'))
                ? $edited_at
                : $parent->getTimestamp();
        } else {
            $last_active_content = array_shift($last_active_content);
            $last_active_at = $last_active_content->getSingleFieldValue('content_activity', 'active_at');
        }
        
        if (empty($extraArgs['content_skip_update_parent'])) {
            $this->updateParentPost($entity, $last_active_at);
        }
    }
    
    public function updateParentPost(Sabai_Addon_Content_Entity $entity, $timestamp = false, $updateChildrenCount = false, $isParent = false)
    {
        if (!$isParent) {
            if (!$parent = $this->_application->Content_ParentPost($entity)) {
                return;
            }
        } else {
            // Parent post was passed as the first argument
            $parent = $entity;
        }
        
        $values = array();
        
        if ($timestamp) {
            // Update active timestamp of the parent post
            $parent_edited_at = (int)$parent->getSingleFieldValue('content_activity', 'edited_at');
            $values = array(
                'content_activity' => array(
                    'active_at' => $timestamp > $parent_edited_at ? $timestamp : $parent_edited_at,
                    'edited_at' => $parent_edited_at,
                ),
            );
        }
        
        if ($updateChildrenCount) {
            $current_count = $parent->getSingleFieldValue('content_children_count');
            // Count the total number of child posts
            $count = $this->_application->Entity_Query('content')
                ->fieldIs('content_parent', $parent->getId())
                ->propertyIs('post_status', self::POST_STATUS_PUBLISHED)
                ->groupByProperty('post_entity_bundle_type')
                ->count();
            if (!empty($count)) {
                foreach ($count as $child_bundle_type => $_count) {
                    $values['content_children_count'][] = array('value' => $_count, 'child_bundle_name' => $child_bundle_type);
                }
                // Set child entity count to 0 if no count
                foreach (array_keys($current_count) as $child_bundle_type) {
                    if (!isset($count[$child_bundle_type])) {
                        $values['content_children_count'][] = array('value' => 0, 'child_bundle_name' => $child_bundle_type);
                    }
                }
            } else {
                if (!empty($current_count)) {
                    // Set all child entity count to 0 
                    foreach (array_keys($current_count) as $child_bundle_type) {
                        $values['content_children_count'][] = array('value' => 0, 'child_bundle_name' => $child_bundle_type);
                    }
                }
            }
        }
        
        if (!empty($values)) {
            $this->_application->Entity_Save($parent, $values);
        }
    }
    
    public function isUpgradeable($currentVersion, $newVersion)
    {
        if (!parent::isUpgradeable($currentVersion, $newVersion)) {
            return false;
        }
        if (version_compare($currentVersion, '1.1.0dev78', '<')) {
            $required_addons = array(
                'Entity' => '1.1.0dev12',
            );
            return $this->_application->CheckAddonVersion($required_addons);
        }
        
        return true;
    }
    
    public function uninstall(ArrayObject $log)
    {
        parent::uninstall($log);
        // Clear view count
        $this->_application->getPlatform()->deleteUserMeta($this->_application->getUser()->id, 'content_viewed', $views);
    }
    
    public function onSabaiRunCron($lastRunTimestamp, $logs)
    {        
        $featured_expired_posts = $this->_application->Entity_Query('content')
            ->fieldIsGreaterThan('content_featured', 0, 'expires_at')
            ->fieldIsOrSmallerThan('content_featured', time(), 'expires_at')
            ->fetch();
        if ($count = count($featured_expired_posts)) {
            foreach ($featured_expired_posts as $entity) {
                $this->_application->Entity_Save($entity, array('content_featured' => false));
            }
            $logs[] = sprintf(__('Unfeatured %d content post(s)', 'sabai'), $count);
        }
    }
        
    public function onSystemSitemapIndexFilter(&$sitemaps)
    {
        foreach ($this->_application->getModel('Bundle', 'Entity')->entitytypeName_is('content')->fetch() as $bundle) {
            if (!$this->_application->isAddonLoaded($bundle->addon)
                || !empty($bundle->info['parent'])
                || (!$count = $this->getModel('Post')->entityBundleName_is($bundle->name)->count())
            ) continue;

            $sitemaps[] = array(
                'loc' => $this->_application->Url($bundle->getPath() . '/sitemap.xml'),
                'lastmod' => time(),
                'count' => $count,
            );
        }
    }
 
    public function onVotingContentEntityVotedFlag(Sabai_Addon_Entity_IEntity $entity, $results)
    {
        if ($entity->isTrashed()) return;

        $this->_application->Content_CountFlaggedPosts(true); // update flag count cache
    }
            
    public function onVotingContentEntityVoteDeletedFlag(Sabai_Addon_Entity_IEntity $entity)
    {
        $this->_application->Content_CountFlaggedPosts(true); // update flag count cache
    }
}

function is_sabai_content_post()
{
    return isset($GLOBALS['sabai_entity']) && $GLOBALS['sabai_entity'] instanceof Sabai_Addon_Content_Entity ? $GLOBALS['sabai_entity'] : false;
}
