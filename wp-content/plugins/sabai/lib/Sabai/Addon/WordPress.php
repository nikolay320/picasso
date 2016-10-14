<?php
class Sabai_Addon_WordPress extends Sabai_Addon
    implements Sabai_Addon_System_IAdminRouter,
               Sabai_Addon_System_IAdminSettings,
               Sabai_Addon_Form_IFields,
               Sabai_Addon_Field_IWidgets,
               Sabai_Addon_Field_IRenderers
{
    const VERSION = '1.3.28', PACKAGE = 'sabai';
    
    protected $_path;
    
    protected function _init()
    {
        $this->_path = $this->_application->Path(dirname(__FILE__) . '/Directory');
        
        return $this;
    }
    
    public function systemGetAdminRoutes()
    {
        $ret = array(
            '/wordpress/permalink' => array(
                'controller' => 'Permalink',
            ),
            '/wordpress/verify-license' => array(
                'type' => Sabai::ROUTE_CALLBACK,
                'controller' => 'VerifyLicense',
            ),
            '/wordpress/pages' => array(
                'controller' => 'Pages',
            ),
        );
        if ($slugs = $this->_application->System_Slugs()) {
            foreach ($slugs as $addon => $slugs_info) {
                if (isset($slugs_info['admin_route'])) {
                    $ret[$slugs_info['admin_route']] = array(
                        'type' => Sabai::ROUTE_TAB,
                        'controller' => 'PageSettings',
                        'weight' => isset($slugs_info['admin_weight']) ? $slugs_info['admin_weight'] : null,
                        'title_callback' => true,
                        'access_callback' => true,
                        'callback_path' => 'page_settings',
                        'data' => array('addon' => $addon),
                    );
                }
            }
        }
        
        return $ret;
    }

    public function systemOnAccessAdminRoute(Sabai_Context $context, $path, $accessType, array &$route)
    {
        switch ($path) {
            case 'page_settings':
                $context->slug_addon = $route['data']['addon'];
                return true;
        }
    }

    public function systemGetAdminRouteTitle(Sabai_Context $context, $path, $title, $titleType, array $route)
    {
        switch ($path) {
            case 'page_settings':
                return _x('Pages', 'tab', 'sabai');
        }
    }

    public function onSystemAdminInfoFilter(&$info)
    {
        $info += array(
            'wordpress_locale' => array('name' => 'WordPress Locale', 'value' => get_locale()),
            'wordpress_lang_dir' => array('name' => 'WP_LANG_DIR', 'value' => WP_LANG_DIR),
        );
        if ($_plugin_info = get_site_transient('sabai_plugin_info')) {
            $plugin_info = array();
            $plugin_names = array_keys($this->_application->getPlatform()->getSabaiPlugins(false));
            foreach (array_merge(array('sabai'), $plugin_names) as $plugin_name) {
                if (!$plugin = @$_plugin_info[$plugin_name]) continue;
                $plugin_info[] = sprintf('<b>%s</b> (Version: %s, Date: %s, Download URL: %s)', $plugin_name, $plugin->version, $plugin->last_updated, $plugin->download_link);
            }
            $info['wordpress_plugin_info'] = array('name' => 'WordPress Plugin Info', 'value' => implode('<br />', $plugin_info));
        }
    }
    
    public function onSystemISlugsUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getAddon('System')->reloadRoutes($this, true);
        $this->_createSlugPages($addon);
    }
    
    public function onSystemISlugsInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getAddon('System')->reloadRoutes($this, true);
        $this->_createSlugPages($addon);
    }
    
    protected function _createSlugPages($addon)
    {
        $platform = $this->_application->getPlatform();
        $slugs = $platform->getSabaiOption('page_slugs');
        foreach ($addon->systemGetSlugs() as $slug_name => $slug) {
            if (isset($slugs[1][$addon->getName()][$slug_name]) || !$slug['is_required'] || !$slug['title']) continue;
            
            $_slug = isset($slug['slug']) ? $slug['slug'] : $slug_name;
            if ($page_id = $platform->createPage($_slug, $slug['title'])) {
                $slugs[0][$_slug] = $_slug;
                $slugs[1][$addon->getName()][$slug_name] = $_slug;
                $slugs[2][$_slug] = $page_id;
            }
        }
        $platform->updateSabaiOption('page_slugs', $slugs);
    }
    
    public function onSystemISlugsUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $slugs = $this->_application->getPlatform()->getSabaiOption('page_slugs', array());
        if (!empty($slugs[0]) && !empty($slugs[1][$addon->getName()])) {
            // Remove slugs and ids of the uninstalled plugin from the global slug list
            $addon_slugs = array_flip(array_values($slugs[1][$addon->getName()])); // slugs as key
            $slugs[0] = array_diff_key($slugs[0], $addon_slugs); // remove from slugs by slug list
            $slugs[2] = array_diff_key($slugs[2], $addon_slugs); // remoev from page ids by slug list
            unset($slugs[1][$addon->getName()]); // unset slugs by plugin
        }
        $this->_application->getPlatform()->updateSabaiOption('page_slugs', $slugs);
        $this->_application->getAddon('System')->reloadRoutes($this, true);
    }
    
    public function onSystemIAdminMenusInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('wordpress_admin_menus');
    }
    
    public function onSystemIAdminMenusUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('wordpress_admin_menus');
    }
    
    public function onSystemIAdminMenusUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('wordpress_admin_menus');
    }
    
    public function onSabaiWebResponseRenderContentAdminAddPost($context, $response, $template)
    {
        $context->addTemplate('wordpress_admin_content_post');
    }
        
    public function onSabaiWebResponseRenderContentAdminAddChildPost($context, $response, $template)
    {
        $context->addTemplate('wordpress_admin_content_post');
    }
    
    public function onSabaiWebResponseRenderContentAdminEditPost($context, $response, $template)
    {
        $context->addTemplate('wordpress_admin_content_post');
    }
        
    public function onSabaiWebResponseRenderContentAdminEditChildPost($context, $response, $template)
    {
        $context->addTemplate('wordpress_admin_content_post');
    }
    
    public function onSabaiWebResponseRenderTaxonomyAdminAddTerm($context, $response, $template)
    {
        $context->addTemplate('wordpress_admin_taxonomy_term');
    }
    
    public function onSabaiWebResponseRenderTaxonomyAdminEditTerm($context, $response, $template)
    {
        $context->addTemplate('wordpress_admin_taxonomy_term');
    }

    public function fieldGetWidgetNames()
    {
        return array('wordpress_captcha', 'wordpress_editor');
    }

    public function fieldGetWidget($name)
    {
        switch ($name) {
            case 'wordpress_captcha':
                return new Sabai_Addon_WordPress_CaptchaFieldWidget($this, $name);
            case 'wordpress_editor':
                return new Sabai_Addon_WordPress_EditorFieldWidget($this, $name);
        }
    }
    
    public function fieldGetRendererNames()
    {
        return array();
    }

    public function fieldGetRenderer($name)
    {
        switch ($name) {
            case 'text':
                return new Sabai_Addon_WordPress_TextFieldRenderer($this, $name);
        }
    }
    
    public function onFieldRenderersFilter(&$renderers)
    {
        $renderers['text'] = $this->_name;
    }
    
    public function formGetFieldTypes()
    {
        return array('wordpress_editor');
    }

    public function formGetField($type)
    {
        switch ($type) {
            case 'wordpress_editor':
                return new Sabai_Addon_WordPress_EditorFormField($this, $type);
        }
    }
    
    public function onFormBuildSystemAdminSettings(&$form)
    {
        if ($form['#name'] !== 'system_admin_settings') return;
        
        $form[$this->_name] = array(
            '#tree' => true,
            '#weight' => 3,
        );
        $plugin_names = $this->_application->getPlatform()->getSabaiPlugins(false, true);
        if (!empty($plugin_names)) {
            $form[$this->_name]['envato_license_keys'] = array(
                '#title' => __('CodeCanyon.net Purchase Code Settings', 'sabai'),
                '#class' => 'sabai-form-group',
                'info' => array(
                    '#type' => 'markup',
                    '#markup' => '<p>' . __('Enter the item purchase code you received from CodeCanyon.net to enable automatic updates for each plugin. Make sure that you enter a valid purchase code here otherwise it will just slow your site down.', 'sabai') . '</p>',
                ),
                '#plugins' => array(),
            );
            $license_keys = $this->_application->getPlatform()->getSabaiOption('license_keys', array());
            foreach (array_keys($plugin_names) as $plugin_name) {
                if (!$plugin_data = Sabai_Platform_WordPress::getPluginData($plugin_name)) {
                    continue;
                }
                if (!empty($plugin_data['Sabai License Package'])) {
                    $form[$this->_name]['envato_license_keys']['#plugins'][$plugin_name] = $plugin_data['Sabai License Package'];
                    // skipe this plugin since it uses other package's license
                    continue;
                }
                $form[$this->_name]['envato_license_keys']['#plugins'][$plugin_name] = $plugin_name;
                $form[$this->_name]['envato_license_keys'][$plugin_name] = array(
                    '#type' => 'textfield',
                    '#min_length' => 36,
                    '#max_length' => 36,
                    '#regex' => '/^[a-z0-9-]+$/',
                    '#default_value' => isset($license_keys[$plugin_name]) && $license_keys[$plugin_name]['type'] === 'envato' ? $license_keys[$plugin_name]['value'] : null,
                    '#title' => $plugin_data['Name'],
                    '#size' => 30,
                );
            }
        }
        
        $form['#submit'][0][] = array($this, 'submitSystemAdminSettingsForm');
    }

    public function submitSystemAdminSettingsForm($form)
    {
        // Save license keys to WP options table
        $license_keys = array();
        foreach ($form->settings[$this->_name]['envato_license_keys']['#plugins'] as $plugin_name => $license_plugins) {
            foreach (explode(',', $license_plugins) as $license_plugin) {
                if (isset($form->values[$this->_name]['envato_license_keys'][$license_plugin])) {
                    $license_keys[$plugin_name] = array('type' => 'envato', 'value' => $form->values[$this->_name]['envato_license_keys'][$license_plugin]);
                    continue 2;
                }
            }
        }
        $this->_application->getPlatform()->updateSabaiOption('license_keys', $license_keys);
    }
    
    public function onSabaiAddonUninstalled($addonEntity, ArrayObject $log)
    {
        $this->_application->getPlatform()->deleteCache('wordpress_addon_updates');
    }

    public function onSabaiAddonUpgraded($addonEntity, $previousVersion, ArrayObject $log)
    {
        if ($addonEntity->name === 'WordPress'
            && version_compare($previousVersion, self::VERSION, '<')
        ) {
            // re-schedule event
            wp_clear_scheduled_hook('sabai_cron');
            if (!wp_next_scheduled('sabai_cron')) {
                wp_schedule_event(time(), 'twicedaily', 'sabai_cron');
            }
        }
        
        $this->_application->getPlatform()->deleteCache('wordpress_addon_updates');
    }
    
    public function getDefaultConfig()
    {
        return array(
            'shortcode_roles' => array(),
        );
    }
    
    public function onEntityViewEntity(Sabai_Addon_Entity_Entity $entity)
    {
        add_filter('body_class', array($this, 'addBodyClass'));
        add_filter('single_post_title', array($this, 'singlePostTitle'));
    }
    
    public function addBodyClass($classes)
    {
        if (isset($GLOBALS['sabai_entity'])) {        
            $classes[] = 'sabai-entity-id-' . $GLOBALS['sabai_entity']->getId();
            $classes[] = 'sabai-entity-bundle-name-' . $GLOBALS['sabai_entity']->getBundleName();
            $classes[] = 'sabai-entity-bundle-type-' . $GLOBALS['sabai_entity']->getBundleType();
        }
        return $classes;
    }
    
    public function singlePostTitle($postTitle)
    {        
        return isset($GLOBALS['sabai_entity']) ? $GLOBALS['sabai_entity']->getTitle() : $postTitle;
    }
    
    public function systemGetAdminSettingsForm()
    {
        $admin_roles = array_keys($this->_application->AdministratorRoles());
        return array(
            'shortcode_roles' => array(
                '#title' => __('User roles allowed to use shortcodes in content', 'sabai'),
                '#type' => 'checkboxes',
                '#options' => $this->_application->getPlatform()->getUserRoles(),
                '#default_value' => array_merge($admin_roles, (array)$this->_config['shortcode_roles']),
                '#options_disabled' => $admin_roles,
                '#class' => 'sabai-form-inline',
            ),
        );
    }
    
    public function onEntityFieldWidgetFormFilter(&$form, $entity, $field, $value, $widget, $settings, $admin)
    {
        if (!$admin && $widget === 'html') {
            $form['#markup'] = do_shortcode($form['#markup']);
        }
    }
}
