<?php
class Sabai_Addon_Taxonomy extends Sabai_Addon
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

    public function systemGetMainRoutes()
    {
        $routes = array();
        foreach ($this->_application->getModel('Bundle', 'Entity')->entitytypeName_is('taxonomy')->fetch() as $bundle) {
            if (!$this->_application->isAddonLoaded($bundle->addon)) continue;
            
            $routes[$bundle->getPath()] = array(
                'controller' => empty($bundle->info['taxonomy_hierarchical']) ? 'ListTerms' : 'ListHierarchicalTerms',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'list_terms',
                'data' => array(
                    'bundle_id' => $bundle->id,
                ),
            );
            $routes[$bundle->getPath() . '/_autocomplete'] = array(
                'controller' => 'Autocomplete',
                'type' => Sabai::ROUTE_CALLBACK,
            );
            $routes[$bundle->getPath() . '/sitemap'] = array(
                'controller' => 'Sitemap',
                'type' => Sabai::ROUTE_CALLBACK,
            );
            $routes[$bundle->getPath() . '/:slug'] = array(
                'controller' => 'ViewTerm',
                'format' => array(':slug' => '[a-z0-9~\.:_\-%]+'),
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'view_term',
            );
            $routes[$bundle->getPath() . '/:slug/edit'] = array(
                'controller' => 'EditTerm',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'edit_term',
            );
            $routes[$bundle->getPath() . '/:slug/delete'] = array(
                'controller' => 'DeleteTerm',
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'delete_term',
            );
            $routes[$bundle->getPath() . '/:slug/feed'] = array(
                'controller' => 'TermFeed',
            );
        }
        $routes += array(
            '/sabai/taxonomy/child_terms' => array(
                'controller' => 'ChildTerms',
                'type' => Sabai::ROUTE_CALLBACK,
            ),
            '/sabai/taxonomy/termlist' => array(
                'controller' => 'TermList',
                'type' => Sabai::ROUTE_CALLBACK,
            ),
        );

        return $routes;
    }

    public function systemOnAccessMainRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'list_terms':
                if (!$context->taxonomy_bundle = $this->_application->getModel('Bundle', 'Entity')->fetchById($route['data']['bundle_id'])) {
                    return false;
                }
                return true;
            case 'view_term':
                $slug = $context->getRequest()->asStr('slug');
                if (!strlen($slug)
                    || (!$slug = rawurldecode($slug))
                    || (!$term = $this->getModel('Term')->entityBundleName_is($context->taxonomy_bundle->name)->name_is($slug)->fetchOne())
                ) {
                    $context->setNotFoundError();
                    return false;
                }
                $context->popInfo();
                $GLOBALS['sabai_entity'] = $context->entity = $term->toEntity();
                $this->_application->Entity_LoadFields($context->entity);
                if (!empty($context->taxonomy_bundle->info['taxonomy_hierarchical'])) {
                    // Fetch parents if not the top level taxonomy term
                    if ($context->entity->getParentId()) {
                        $parent_terms = $this->_application->Taxonomy_Parents($context->entity);
                        foreach ($parent_terms as $parent_term) {
                            $context->setInfo($parent_term->getTitle(), $this->_application->Entity_Url($parent_term));
                        }
                    }
                }
                return true;
            case 'edit_term':
                return $this->_application->HasPermission($context->taxonomy_bundle->name . '_edit');
            case 'delete_term':
                return $this->_application->HasPermission($context->taxonomy_bundle->name . '_delete');
        }
    }

    public function systemGetMainRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'list_terms':
                return $this->_application->Entity_BundleLabel($context->taxonomy_bundle, false);
            case 'view_term':
                return $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT ? __('Info', 'sabai') : $context->entity->getTitle();
            case 'edit_term':
                return sprintf(__('Edit %s', 'sabai'), $this->_application->Entity_BundleLabel($context->taxonomy_bundle, true));
            case 'delete_term':
                return sprintf(__('Delete %s', 'sabai'), $this->_application->Entity_BundleLabel($context->taxonomy_bundle, true));
        }
    }
    
    public function systemGetAdminRoutes()
    {
        $routes = array();
        foreach ($this->_application->getModel('Bundle', 'Entity')->entitytypeName_is('taxonomy')->fetch() as $bundle) { 
            if (!$this->_application->isAddonLoaded($bundle->addon)) continue;
            
            $routes[$bundle->getAdminPath()] = array(
                'controller' => 'ListTerms',
                'access_callback' => true,
                'callback_path' => 'terms',
                'data' => array(
                    'bundle_id' => $bundle->id,
                    'clear_tabs' => true,
                ),
                'title_callback' => true,
                'weight' => 20,
            );
            $routes[$bundle->getAdminPath() . '/add'] = array(
                'controller' => 'AddTerm',
                'title_callback' => true,
                'callback_path' => 'add_term',
            );
            $routes[$bundle->getAdminPath() . '/:entity_id'] = array(
                'controller' => 'EditTerm',
                'format' => array(':entity_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
                'callback_path' => 'edit_term',
            );
            $routes[$bundle->getAdminPath() . '/:entity_id/view'] = array(
                'controller' => 'ViewTerm',
            );
            $routes[$bundle->getAdminPath() . '/:entity_id/delete'] = array(
                'controller' => 'DeleteTerm',
                'title_callback' => true,
                'callback_path' => 'delete_term',
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
            case 'terms':
                if (!$taxonomy_bundle = $this->_application->getModel('Bundle', 'Entity')->fetchById($route['data']['bundle_id'])) {
                    return false;
                }
                $context->taxonomy_bundle = $taxonomy_bundle;
                return true;
            case 'edit_term':
                if ((!$id = $context->getRequest()->asInt('entity_id'))
                    || (!$entity = $this->_application->Entity_Entity('taxonomy', $id))
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
                $context->addMenu(array(
                    'title' => sprintf(__('View %s', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true)),
                    'url' => $bundle->getAdminPath() . '/' . $id . '/view',
                    'options' => array(),
                ), true);
                $context->entity = $entity;
                return true;
        }
    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'terms':
                $bundle_label = $this->_application->Entity_BundleLabel($context->taxonomy_bundle, false);
                return $titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT ? sprintf(_x('All %s', 'tab', 'sabai'), $bundle_label) : $bundle_label;
            case 'edit_term':
                if ($titleType === Sabai::ROUTE_TITLE_TAB_DEFAULT) {
                    return __('Edit', 'sabai');
                }
                return $context->entity->getTitle();
            case 'add_term':
                return sprintf(__('Add %s', 'sabai'), $this->_application->Entity_BundleLabel($context->taxonomy_bundle, true));
            case 'delete_term':
                return sprintf(_x('Delete %s', 'Delete taxonomy term page title', 'sabai'), $this->_application->Entity_BundleLabel($context->taxonomy_bundle, true));
        }
    }
    
    public function systemGetPermissionCategories()
    {
        $ret = array();
        foreach ($this->_application->getModel('Bundle', 'Entity')->entitytypeName_is('taxonomy')->fetch() as $bundle) {
            if (!$this->_application->isAddonLoaded($bundle->addon)) continue;
            
            $ret[$bundle->name] = $this->_application->Entity_BundleLabel($bundle);
        }
        return $ret;
    }

    public function systemGetPermissions()
    {
        $ret = array();    
        $taxonomy_permissions = array(
            'add' => array(__('Add %s', 'sabai'), '', true),
            'edit' => __('Edit %s', 'sabai'),
            'delete' => __('Delete %s', 'sabai'),
        );
        $ipermissions_addon_names = $this->_application->getInstalledAddonsByInterface('Sabai_Addon_Taxonomy_IPermissions');
        foreach ($this->_application->getModel('Bundle', 'Entity')->entitytypeName_is('taxonomy')->fetch() as $bundle) {
            if (!$this->_application->isAddonLoaded($bundle->addon)) continue;
            
            $weight = 0;
            $permissions = $taxonomy_permissions;
            // Add extra permissions added by other add-ons
            foreach ($ipermissions_addon_names as $addon_name) {
                $permissions += $this->_application->getAddon($addon_name)->taxonomyGetPermissions($bundle);
            }
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
            $bundle_info = $this->_application->getAddon($bundle->addon)->taxonomyGetTaxonomy($bundle->name)->taxonomyGetInfo();
            if (!empty($bundle_info['taxonomy_permissions'])) {
                foreach ($bundle_info['taxonomy_permissions'] as $perm => $perm_info) {
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
                            $ret[$perm_name] = $perm_info + array('weight' => $weight, 'category' => $bundle->name, 'description' => '', 'guest_allowed' => false);
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
        $default_permissions = array();
        $ipermissions_addon_names = $this->_application->getInstalledAddonsByInterface('Sabai_Addon_Taxonomy_IPermissions');
        foreach ($this->_application->getModel('Bundle', 'Entity')->entitytypeName_is('taxonomy')->fetch() as $bundle) {
            if (!$this->_application->isAddonLoaded($bundle->addon)) continue;
            
            // Add extra permissions added by other add-ons
            foreach ($ipermissions_addon_names as $addon_name) {
                $default_permissions = array_merge($default_permissions, $this->_application->getAddon($addon_name)->taxonomyGetDefaultPermissions($bundle));
            }
            foreach ($default_permissions as $perm) {
                $ret[] = $bundle->name . '_' . $perm;
            }
            // Get default permissions defined by the addon of bundle
            $bundle_info = $this->_application->getAddon($bundle->addon)->taxonomyGetTaxonomy($bundle->name)->taxonomyGetInfo();
            if (!empty($bundle_info['taxonomy_permissions'])) {
                foreach ($bundle_info['taxonomy_permissions'] as $perm => $perm_info) {
                    if (!empty($perm_info['default'])) {
                        $ret[] = $bundle->name . '_' . $perm;
                    }
                }
            }
            if (!empty($bundle_info['taxonomy_default_permissions'])) {
                foreach ($bundle_info['taxonomy_default_permissions'] as $perm) {
                    $ret[] = $bundle->name . '_' . $perm;
                }
            }
        }
        return $ret;
    }

    public function entityGetTypeNames()
    {
        return array('taxonomy');
    }

    public function entityGetType($typeName)
    {
        return new Sabai_Addon_Taxonomy_EntityType($this, $typeName);
    }

    public function fieldGetTypeNames()
    {
        return array('taxonomy_term_title', 'taxonomy_term_id', 'taxonomy_term_created', 'taxonomy_term_parent',
            'taxonomy_term_entity_bundle_name', 'taxonomy_term_entity_bundle_type',
            'taxonomy_term_user_id', 'taxonomy_term_name', 'taxonomy_terms', 'taxonomy_content', 'taxonomy_content_count');
    }

    public function fieldGetType($name)
    {
        return new Sabai_Addon_Taxonomy_FieldType($this, $name);
    }

    public function fieldGetWidgetNames()
    {
        return array('taxonomy_tagging', 'taxonomy_term_parent', 'taxonomy_select');
    }

    public function fieldGetWidget($name)
    {
        switch ($name) {
            case 'taxonomy_select':
                return new Sabai_Addon_Taxonomy_SelectFieldWidget($this, $name); 
            default:
                return new Sabai_Addon_Taxonomy_FieldWidget($this, $name);
        }
    }

    public function fieldGetFilterNames()
    {
        return array('taxonomy_select');
    }

    public function fieldGetFilter($name)
    {
        return new Sabai_Addon_Taxonomy_FieldFilter($this, $name);
    }
    
    public function fieldGetRendererNames()
    {
        return array('taxonomy_terms');
    }

    public function fieldGetRenderer($name)
    {
        return new Sabai_Addon_Taxonomy_FieldRenderer($this, $name);
    }

    public function onTaxonomyITaxonomiesInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if (!$names = $addon->taxonomyGetTaxonomyNames()) return;

        $bundles = array();
        foreach ($names as $name) {
            $bundles[$name] = $addon->taxonomyGetTaxonomy($name)->taxonomyGetInfo();
            $bundles[$name]['permalink_path'] = $bundles[$name]['path'];
        }
        $this->_application->getAddon('Entity')->createEntityBundles($addon, 'taxonomy', $bundles);
        // Reload system routing/permission tables to reflect changes
        $this->_application->getAddon('System')->reloadRoutes($this)->reloadRoutes($this, true)->reloadPermissions($this);
    }

    public function onTaxonomyITaxonomiesUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getAddon('Entity')->deleteEntityBundles($addon, 'taxonomy');
        // Reload system routing/permission tables to reflect changes
        $this->_application->getAddon('System')->reloadRoutes($this)->reloadRoutes($this, true)->reloadPermissions($this);
    }

    public function onTaxonomyITaxonomiesUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        if (!$names = $addon->taxonomyGetTaxonomyNames()) {
            $this->_application->getAddon('Entity')->deleteEntityBundles($addon, 'taxonomy');
        } else {
            $bundles = array();
            foreach ($names as $name) {
                $bundles[$name] = $addon->taxonomyGetTaxonomy($name)->taxonomyGetInfo();
                $bundles[$name]['permalink_path'] = $bundles[$name]['path'];
            }
            $this->_application->getAddon('Entity')->updateEntityBundles($addon, 'taxonomy', $bundles);
        }
        // Reload system routing/permission tables to reflect changes
        $this->_application->getAddon('System')->reloadRoutes($this)->reloadRoutes($this, true)->reloadPermissions($this);
    }
    
    public function onEntityCreateBundlesSuccess($entityType, $bundles)
    {
        $reload_routes = false;
        
        if ($entityType === 'content') {
            foreach ($bundles as $bundle) {
                if (!isset($bundle->info['taxonomy_terms'])) {
                    continue;
                }
                foreach ($bundle->info['taxonomy_terms'] as $taxonomy_bundle_name => $settings) {
                    if (!$terms_bundle = $this->_application->Entity_Bundle($taxonomy_bundle_name)) {
                        continue;
                    }
            
                    $reload_routes = true;
                    
                    $taxonomy_max_num_items = isset($settings['max_num_items']) ? intval($settings['max_num_items']) : 10;
                    $title = isset($settings['label']) ? $settings['label'] : $this->_application->Entity_BundleLabel($terms_bundle, $taxonomy_max_num_items === 1);
                    $description = isset($settings['description']) ? $settings['description'] : '';
                    // Create field for this taxonomy
                    if (isset($terms_bundle->info['taxonomy_hierarchical']) && $terms_bundle->info['taxonomy_hierarchical'] === true) {
                        $widget = 'taxonomy_select';
                    } else {
                        $widget = isset($settings['widget']) ? $settings['widget'] : null;
                    }
                    if (!empty($settings['filter'])) {
                        $filter = array(
                            'type' => 'taxonomy_select',
                            'name' => $terms_bundle->type,
                            'title' => $title,
                            'settings' => isset($settings['filter']['settings']) ? $settings['filter']['settings'] : null,
                        );
                    }
                    $this->_application->getAddon('Entity')->createEntityField(
                        $bundle,
                        $terms_bundle->type,
                        array(
                            'type' => 'taxonomy_terms',
                            'label' => $title,
                            'description' => $description,
                            'settings' => array(),
                            'required' => !empty($settings['required']),
                            'max_num_items' => $taxonomy_max_num_items,
                            'data' => array('bundle_id' => $terms_bundle->id, 'bundle_name' => $terms_bundle->name),
                            'widget' => $widget,
                            'widget_settings' => isset($settings['widget_settings']) ? $settings['widget_settings'] : null,
                            'weight' => isset($settings['weight']) ? $settings['weight'] : null,
                            'filter' => isset($filter) ? $filter : null,
                            'renderer' => isset($settings['renderer']) ? $settings['renderer'] : null,
                            'renderer_settings' => isset($settings['renderer_settings']) ? $settings['renderer_settings'] : null,
                            'view' => isset($settings['view']) ? $settings['view'] : array('default' => 'default', 'summary' => 'default'),
                        ),
                        Sabai_Addon_Entity::FIELD_REALM_ALL
                    );
                    $this->_application->getAddon('Entity')->createEntityField(
                        $terms_bundle,
                        'taxonomy_content',
                        array(
                            'type' => 'taxonomy_content',
                            'settings' => array(),
                            'label' => isset($settings['content_title']) ? $settings['content_title'] : $this->_application->Entity_BundleLabel($bundle, false),
                            'weight' => 99,
                            'data' => array('bundle_id' => $bundle->id, 'bundle_name' => $bundle->name),
                        ),
                        Sabai_Addon_Entity::FIELD_REALM_ALL
                    );
                    $this->_application->getAddon('Entity')->createEntityField(
                        $terms_bundle,
                        'taxonomy_content_count',
                        array(
                            'type' => 'taxonomy_content_count',
                            'settings' => array(),
                            'label' => isset($settings['content_count_title']) ? $settings['content_count_title'] : sprintf(__('%s count', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true)),
                            'weight' => 99,
                            'data' => array('bundle_id' => $bundle->id, 'bundle_name' => $bundle->name),
                        ),
                        Sabai_Addon_Entity::FIELD_REALM_ALL
                    );
                }
                $bundle->setInfo('taxonomy_terms', array_keys($bundle->info['taxonomy_terms']));
            }
        } elseif ($entityType === 'taxonomy') {
            foreach ($bundles as $bundle) {
                // Add the body field if not explicitly disabled
                if (!isset($bundle->info['taxonomy_body']) || false !== $bundle->info['taxonomy_body']) {
                    $this->_application->getAddon('Entity')->createEntityField(
                        $bundle,
                        'taxonomy_body',
                        array(
                            'type' => 'text',
                            'label' => isset($bundle->info['taxonomy_body']['label']) ? $bundle->info['taxonomy_body']['label'] : __('Body', 'sabai'),
                            'hide_label' => !empty($bundle->info['taxonomy_body']['hide_label']),
                            'description' => isset($bundle->info['taxonomy_body']['description']) ? $bundle->info['taxonomy_body']['description'] : '',
                            'widget' => $this->_application->getPlatform() instanceof Sabai_Platform_WordPress ? 'wordpress_editor' : 'markdown_textarea',
                            'widget_settings' => isset($bundle->info['taxonomy_body']['widget_settings']) ? $bundle->info['taxonomy_body']['widget_settings'] : array(),
                            'required' => !empty($bundle->info['taxonomy_body']['required']),
                            'weight' => isset($bundle->info['taxonomy_body']['weight']) ? $bundle->info['taxonomy_body']['weight'] : null,
                        ),
                        Sabai_Addon_Entity::FIELD_REALM_ALL
                    );
                }
                $bundle->setInfo('taxonomy_body', true);
            }
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
        if ($entityType !== 'taxonomy') {
            return;
        }
        
        $criteria = $this->getModel()->createCriteria('Term')->entityBundleName_in(array_keys($bundles));
        $this->getModel()->getGateway('Term')->deleteByCriteria($criteria);
    }
    
    public function onEntityCreateEntitySuccess(Sabai_Addon_Entity_Model_Bundle $bundle, Sabai_Addon_Entity_IEntity $entity)
    {
        if (!isset($bundle->info['taxonomy_terms'])) {
            return;
        }

        // Get terms added
        $terms_updated = array();
        foreach ($bundle->info['taxonomy_terms'] as $taxonomy_name) {
            if (!$taxonomy_bundle = $this->_application->Entity_Bundle($taxonomy_name)) {
                continue;
            }
            $new_terms = $entity->getFieldValue($taxonomy_bundle->type);
            if (empty($new_terms)) {
                continue;
            }  
            foreach ($new_terms as $new_term) {
                $terms_updated[$taxonomy_bundle->type][$new_term->getId()] = $new_term;
            }
        }
        // Update content count for each term
        $this->_application->Taxonomy_UpdateContentCount($terms_updated, $bundle);
    }
    
    public function onEntityUpdateEntitySuccess(Sabai_Addon_Entity_Model_Bundle $bundle, Sabai_Addon_Entity_IEntity $entity, Sabai_Addon_Entity_IEntity $oldEntity, $valuesChanged)
    {
        if (empty($bundle->info['taxonomy_terms'])) {
            return;
        }
        
        $is_published_or_unpublished = isset($valuesChanged['content_post_status'])
            && ($entity->isPublished() || $oldEntity->isPublished());
        
        $taxonomy_updated = array();
        foreach ($bundle->info['taxonomy_terms'] as $taxonomy_name) {
            if (!$taxonomy_bundle = $this->_application->Entity_Bundle($taxonomy_name)) {
                continue;
            }
            if ($is_published_or_unpublished || isset($valuesChanged[$taxonomy_bundle->type])) {
                $taxonomy_updated[] = $taxonomy_bundle->type;
            }
        }
        
        if (empty($taxonomy_updated)) return;
        
        $terms_updated = array();
        foreach ($taxonomy_updated as $taxonomy_type) {
            // Get terms added or removed
            $current_terms = (array)@$entity->getFieldValue($taxonomy_type);
            $old_terms = (array)@$oldEntity->getFieldValue($taxonomy_type);
            if (empty($current_terms) && empty($old_terms)) {
                continue;
            }
            foreach ($current_terms as $current_term) {
                $terms_updated[$taxonomy_type][$current_term->getId()] = $current_term;
            }
            if ($is_published_or_unpublished) {
                // The content was either published or unpublished. Update all terms.
                foreach ($old_terms as $old_term) {
                    $terms_updated[$taxonomy_type][$old_term->getId()] = $old_term;
                }
            } else {                
                // Update terms that were newly added or removed
                foreach ($old_terms as $old_term) {
                    if (isset($terms_updated[$taxonomy_type][$old_term->getId()])) {
                        unset($terms_updated[$taxonomy_type][$old_term->getId()]);
                    } else {
                        $terms_updated[$taxonomy_type][$old_term->getId()] = $old_term;
                    }
                }
            }
            if (empty($terms_updated[$taxonomy_type])) {
                unset($terms_updated[$taxonomy_type]);
            }
        }
        // Update content count for each term
        $this->_application->Taxonomy_UpdateContentCount($terms_updated, $bundle);
    }
    
    public function onEntityRenderEntities($bundle, $entities, $displayMode)
    {
        if ($bundle->entitytype_name !== 'taxonomy'
            || empty($bundle->info['taxonomy_hierarchical'])
        ) {
            return;
        }
        
        if ($displayMode === 'full' || $displayMode === 'summary') {
            $entity_ids = array_keys($entities);
            // Get child terms
            $child_terms = array();
            foreach ($this->_application->Entity_Query('taxonomy')->propertyIsIn('term_parent', $entity_ids)->sortByProperty('term_title')->fetch() as $child_term) {
                $child_terms[$child_term->getParentId()][$child_term->getId()] = $child_term;
                $entity_ids[] = $child_term->getId();
            }
            // Get content count
            $content_count = $this->getModel()
                ->getGateway('Term')
                ->getContentCount($entity_ids);
            // Assign to entities
            foreach (array_keys($entities) as $entity_id) {
                $entities[$entity_id]->data['content_count'] = isset($content_count[$entity_id]) ? $content_count[$entity_id] : 0;
                if (isset($child_terms[$entity_id])) {
                    foreach (array_keys($child_terms[$entity_id]) as $child_entity_id) {
                        $child_terms[$entity_id][$child_entity_id]->data['content_count'] = isset($content_count[$child_entity_id]) ? $content_count[$child_entity_id] : 0;
                    }
                    $entities[$entity_id]->data['child_terms'] = $child_terms[$entity_id]; 
                } else {
                    $entities[$entity_id]->data['child_terms'] = array();
                }
            }
        }
    }
    
    
    public function onEntityRenderTaxonomyHtml($bundle, $entity, $displayMode, $id, &$classes, &$links, &$buttons)
    {
        if ($displayMode !== 'full') return;
        
        $user = $this->_application->getUser();
        if ($user->isAnonymous()) return;
        
        if ($this->_application->HasPermission($entity->getBundleName() . '_edit')) {
            $links['taxonomy_edit_tag'] = $this->_application->LinkTo(__('Edit', 'sabai'), $this->_application->Entity_Url($entity, '/edit'), array('icon' => 'edit'), array('title' => sprintf(__('Edit this %s', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true))));
        }
        if ($this->_application->HasPermission($entity->getBundleName() . '_delete')) {
            $links['taxonomy_delete_tag'] = $this->_application->LinkToModal(__('Delete', 'sabai'), $this->_application->Entity_Url($entity, '/delete'), array('width' => 470, 'icon' => 'trash-o'), array('title' => sprintf(__('Delete this %s', 'sabai'), $this->_application->Entity_BundleLabel($bundle, true))));
        }
    }
    
    public function onFormBuildContentAdminListposts(&$form, &$storage)
    {
        $this->_onFormBuildContentAdminListposts($form);
    }
    
    public function onFormBuildContentAdminListchildposts(&$form, &$storage)
    {
        $this->_onFormBuildContentAdminListposts($form);
    }
    
    private function _onFormBuildContentAdminListposts(&$form)
    {
        if (empty($form['#bundle']->info['taxonomy_terms'])) {
            return;
        }
        
        $order = 12;
        foreach ($this->_application->getModel('Bundle', 'Entity')->name_in($form['#bundle']->info['taxonomy_terms'])->fetch() as $taxonomy_bundle) {
            $taxonomy_bundle_label_singular = $this->_application->Entity_BundleLabel($taxonomy_bundle, true);
            $form['entities']['#header'][$taxonomy_bundle->type] = array(
                'order' => ++$order,
                'label' => $taxonomy_bundle_label_singular,
            );
            foreach ($form['entities']['#options'] as $entity_id => $data) {
                $terms = @$data['#entity']->getFieldValue($taxonomy_bundle->type);
                if (empty($terms)) continue;

                foreach ($terms as $key => $term) {
                    $terms[$key] = $this->_application->LinkTo($term->getTitle(), $this->_application->Url($form['#bundle']->getAdminPath(), array('taxonomy_terms['. $taxonomy_bundle->type .']' => $term->getId())));
                }
                $form['entities']['#options'][$entity_id][$taxonomy_bundle->type] = implode(', ', $terms);
            }
            if (!empty($taxonomy_bundle->info['taxonomy_hierarchical'])
                && ($options = $this->_application->Taxonomy_Tree($taxonomy_bundle))
            ) {  
                $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME]['action']['#options']['taxonomy_assign_' . $taxonomy_bundle->type] = sprintf(__('Assign %s', 'sabai'), $taxonomy_bundle_label_singular);
                $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME]['action']['#options']['taxonomy_remove_' . $taxonomy_bundle->type] = sprintf(__('Remove %s', 'sabai'), $this->_application->Entity_BundleLabel($taxonomy_bundle, false));
                $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME]['taxonomy_' . $taxonomy_bundle->type] = array(
                    '#type' => 'select',
                    '#default_value' => null,
                    '#multiple' => false,
                    '#options' => $options,
                    '#weight' => 2,
                    '#states' => array(
                        'visible' => array('select[name="action"]' => array('value' => 'taxonomy_assign_' . $taxonomy_bundle->type)),
                    ),
                    '#tree' => false,
                );
                $taxonomy_bundle_label = $this->_application->Entity_BundleLabel($taxonomy_bundle, false);
                $form['#filters']['taxonomy_terms['. $taxonomy_bundle->type .']'] = array(
                    'order' => 1,
                    'default_option_label' => sprintf(_x('All %s', 'Filter', 'sabai'), strtolower($taxonomy_bundle_label), $taxonomy_bundle_label),
                    'options' => $options,
                );
            }
        }
        $form['#submit'][0][] = array($this, 'updateEntities');  
    }
        
    public function onContentAdminPostsUrlParamsFilter(&$urlParams, $context, $bundle)
    {
        if ($taxonomy_terms = $context->getRequest()->asArray('taxonomy_terms')){
            foreach ($taxonomy_terms as $taxonomy_bundle_type => $taxonomy_term_id) {
                $urlParams['taxonomy_terms[' . $taxonomy_bundle_type . ']'] = $taxonomy_term_id;
            }
        }
    }
    
    public function onContentAdminPostsQuery($context, $bundle, $query, $count_query, $sort, $order)
    {
        if ($taxonomy_terms = $context->getRequest()->asArray('taxonomy_terms')) {
            foreach ($taxonomy_terms as $taxonomy_term_id) {
                if (!$taxonomy_term = $this->_application->Entity_Entity('taxonomy', $taxonomy_term_id, false)) {
                    continue;
                }
                $term_ids = array($taxonomy_term_id);
                foreach ($this->getModel('Term')->fetchDescendantsByParent($taxonomy_term_id) as $child_term) {
                    $term_ids[] = $child_term->id;
                }
                $query->fieldIsIn($taxonomy_term->getBundleType(), $term_ids);
                $count_query->fieldIsIn($taxonomy_term->getBundleType(), $term_ids);
            }
        }
    }
    
    public function updateEntities(Sabai_Addon_Form_Form $form)
    {
        if (empty($form->values['entities'])
            || 0 !== strpos($form->values['action'], 'taxonomy_')
        ) {
            return;
        }
        
        if (strpos($form->values['action'], 'taxonomy_remove_') === 0) {
            // Remove terms
            $taxonomy_type = substr($form->values['action'], strlen('taxonomy_remove_'));
            $this->_removeTerms($form->values['entities'], $taxonomy_type);
        } elseif (strpos($form->values['action'], 'taxonomy_assign_') === 0) {
            // Assign selected term
            $taxonomy_type = substr($form->values['action'], strlen('taxonomy_assign_'));
            $term_id = $form->values['taxonomy_' . $taxonomy_type];
            if (!empty($term_id)) {
                $this->_assignTerm($form->values['entities'], $taxonomy_type, $term_id);
            }
        } elseif ($form->values['action'] === 'taxonomy_recalculate_content_count') {
            
        }
    }
    
    protected function _assignTerm($entities, $taxonomyType, $termId)
    {
        foreach ($this->_application->Entity_Entities('content', $entities) as $entity) {
            $term_ids = array($termId);
            if ($current_terms = $entity->getFieldValue($taxonomyType)) {
                foreach ($current_terms as $term) {
                    if ($term->getId() == $termId) continue 2; // already assigned
                    
                    $term_ids[] = $term->getId();
                }
            }
            $this->_application->Entity_Save($entity, array($taxonomyType => $term_ids));
        }
    }
    
    protected function _removeTerms($entities, $taxonomyType)
    {
        foreach ($this->_application->Entity_Entities('content', $entities) as $entity) {
            if (!$entity->getSingleFieldValue($taxonomyType)) {
                continue; // no term
            }
            $this->_application->Entity_Save($entity, array($taxonomyType => false));
        }
    } 
    
    public function isUpgradeable($currentVersion, $newVersion)
    {
        if (!parent::isUpgradeable($currentVersion, $newVersion)) {
            return false;
        }
        if (version_compare($currentVersion, '1.1.0', '<')) {
            $required_addons = array(
                'Entity' => '1.1.0dev12',
            );
            return $this->_application->CheckAddonVersion($required_addons);
        }
        
        return true;
    }
    
    public function onSystemSitemapIndexFilter(&$sitemaps)
    {
        foreach ($this->_application->getModel('Bundle', 'Entity')->entitytypeName_is('taxonomy')->fetch() as $bundle) {
            if (!$this->_application->isAddonLoaded($bundle->addon)
                || (!$count = $this->getModel('Term')->entityBundleName_is($bundle->name)->count())
            ) continue;
            
            $sitemaps[] = array(
                'loc' => $this->_application->Url($bundle->getPath() . '/sitemap.xml'),
                'lastmod' => time(),
                'count' => $count,
            );
        }
    }
}

function is_sabai_taxonomy_term()
{
    return isset($GLOBALS['sabai_entity']) && $GLOBALS['sabai_entity'] instanceof Sabai_Addon_Taxonomy_Entity ? $GLOBALS['sabai_entity'] : false;
}
