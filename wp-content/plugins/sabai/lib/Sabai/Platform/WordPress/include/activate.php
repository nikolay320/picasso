<?php
function sabai_platform_wordpress_activate(Sabai_Platform_WordPress $platform)
{
    if (!function_exists('mb_detect_encoding')) {
        die('Sabai plugin requires the PHP mbstring extension.');
    }
    
    if (defined('SABAI_WORDPRESS_SESSION_PATH')) {
        if (!is_writeable(SABAI_WORDPRESS_SESSION_PATH)) {
            die(sprintf('Configuration error: The path %s set for SABAI_WORDPRESS_SESSION_PATH is not writeable by the server.', SABAI_WORDPRESS_SESSION_PATH));
        }
    }
    
    if (intval(ini_get('max_execution_time')) < 600){
        @ini_set('max_execution_time', '600');
    }
    if (intval(ini_get('memory_limit')) < 128){
        @ini_set('memory_limit', '128M');
    }
    
    try {
        $sabai = $platform->getSabai(true, true);
        // If no exception, the plugin is already installed so do nothing
        return;
    } catch (Sabai_NotInstalledException $e) {
        $sabai = $platform->getSabai(false); // get Sabai without loading add-ons
    }
    
    $log = new ArrayObject();
    
    $log[] = 'Clearing old cache data if any...';
    $platform->clearCache();
    $log[] = 'done...';
    
    if (is_dir($clone_dir = $sabai->getClonesDir())
        && ($files = glob($clone_dir . '/*.php'))
    ) {
        foreach($files as $file) {
            @unlink($file);
        }
    }
    
    $log[] = 'Installing sabai...';
    
    // Pre install
    $platform->updateSabaiOption('page_slugs', array(array(), array(), array()), true);
    
    // Install the System add-on
    try {
        $system = $sabai->fetchAddon('System')->install($log);
        if (!$system_entity = $system->getModel('Addon')->name_is('System')->fetchOne()) {
            die('Failed fetching the System add-on entity.');
        }
        // This will be commited later when the SabaiAddonInstalled event is triggered
        $system_entity->setParams($system->getDefaultConfig(), array(), false);
        $system_entity->events = $system->getEvents();
        $system_entity->commit();
    } catch (Exception $e) {
        die(sprintf('Failed installing the System add-on. Error: %s', $e->getMessage()));
    }
    
    $sabai->reloadAddons();

    $log[] = 'System add-on installed...';
    
    // Install core add-ons
    $addons = array(
        'System' => array(),
        'Form' => array(),
        'Widgets' => array(),
        'WordPress' => array(),
        'Field' => array(),
        'Entity' => array(),
        'Markdown' => array(),
        'Voting' => array(),
        'Comment' => array(),
        'Content' => array(),
        'Taxonomy' => array(),
        'Date' => array(),
        'FieldUI' => array(),
        'File' => array(),
        'Social' => array(),
        'Time' => array(),
    );
    $result = _sabai_platform_wordpress_install_addons($sabai, $addons, $log, array('System' => $system_entity));
    
    $log[] = 'done.';
    
    @mkdir(WP_CONTENT_DIR . '/sabai/assets', 0755, true);
    @mkdir(WP_CONTENT_DIR . '/sabai/sites', 0755, true); // for multisite

    $install_log = implode('', (array)$log);
    $platform->updateSabaiOption('install_log', $install_log);
    
    if (!$result) {
        die (sprintf('Failed installing sabai. Log: %s', $install_log));
    }
}

function sabai_platform_wordpress_activate_plugin(Sabai_Platform_WordPress $platform, $plugin, array $addons)
{    
    if (intval(ini_get('max_execution_time')) < 600){
        @ini_set('max_execution_time', '600');
    }
    if (intval(ini_get('memory_limit')) < 128){
        @ini_set('memory_limit', '128M');
    }
    
    $log = new ArrayObject();
    
    $log[] = 'Installing ' . $plugin . '...';
    
    try {
        $sabai = $platform->getSabai(true, true);
    } catch (Sabai_NotInstalledException $e) {
        die($e->getMessage());
    }
    
    $result = true;
    $installed_addons = $sabai->getInstalledAddons(true);
    if ($addons_to_install = array_diff_key($addons, $installed_addons)) {    
        $result = _sabai_platform_wordpress_install_addons($sabai, $addons_to_install, $log);
    }
    
    $log[] = 'done.';
    
    $platform->clearCache();

    $install_log = implode('', (array)$log);
    
    if (!$result) {
        die (sprintf('Failed installing %s. Log: %s', $plugin, $install_log));
    }
}

function _sabai_platform_wordpress_install_addons(Sabai $sabai, array $addons, $log, array $addonsInstalled = array())
{
    $failed = false;
    foreach ($addons as $addon => $addon_settings) {
        if (isset($addonsInstalled[$addon])) continue;
        
        $addon_settings = array_merge(array('params' => array(), 'priority' => 1), $addon_settings);
        try {
            $entity = $sabai->InstallAddon($addon, $addon_settings['params'], $addon_settings['priority'], $log);
        } catch (Sabai_AddonNotInstallableException $e) {
            continue;
        } catch (Exception $e) {
            $failed = true;
            $log[] = sprintf('failed installing required add-on %s. Error: %s...', $addon, $e->getMessage());
            break;
        }

        $addonsInstalled[$addon] = $entity;

        $log[] = sprintf('%s add-on installed...', $addon);
    }

    $sabai->reloadAddons();

    if (!$failed) {
        foreach ($addonsInstalled as $addon => $addon_entity) {
            $sabai->Action('sabai_addon_installed', array($addon_entity, $log));
        }
        // Reload add-ons data
        $sabai->reloadAddons();
    } else {
        if (!empty($addonsInstalled)) {
            // Uninstall all add-ons
            $log[] = 'Uninstalling installed add-ons...';
            foreach (array_keys($addonsInstalled) as $addon) {
                try {
                    $sabai->getAddon($addon)->uninstall($log);
                } catch (Exception $e) {
                    $log[] = sprintf('failed uninstalling the %s add-on! You must manually uninstall the add-on. Error: %s...', $add-on, $e->getMessage());
                    continue;
                }
                $log[] = sprintf('%s add-on uninstalled...', $addon);
            }
        }
    }

    return !$failed;
}