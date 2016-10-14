<?php
require_once 'SabaiFramework/Application/Http.php';

abstract class Sabai extends SabaiFramework_Application_Http
{
    public static $p;
    private static $_instance;
    private $_isRunning = false, $_db,
        $_eventDispatcher, $_platform, $_user, $_currentAddon = 'System',
        $_addons = array(), $_addonsLoaded = array(), $_addonsLoadedTimestamp;

    // System version constants
    const VERSION = '1.3.28', PACKAGE = 'sabai', PHP_VERSION_MIN = '5.2.0', PHP_VERSION_MAX = '', MYSQL_VERSION_MIN = '5.0.3';

    // Route type constants
    const ROUTE_NORMAL = 0, ROUTE_TAB = 1, ROUTE_MENU = 2, ROUTE_CALLBACK = 3, ROUTE_INLINE_TAB = 4,
        ROUTE_ACCESS_LINK = 0, ROUTE_ACCESS_CONTENT = 1,
        ROUTE_TITLE_NORMAL = 0, ROUTE_TITLE_TAB = 1, ROUTE_TITLE_TAB_DEFAULT = 2, ROUTE_TITLE_MENU = 3;

    const ADDON_NAME_REGEX = '/^[a-zA-Z0-9]{2,}$/';

    // Define various image sizes
    const THUMBNAIL_SIZE_SMALL = 24, THUMBNAIL_SIZE_MEDIUM = 50, THUMBNAIL_SIZE_LARGE = 100;
    
    public function autoload($class)
    {
        if (0 === strpos($class, 'Sabai_') || 0 === strpos($class, 'SabaiFramework_')) {
            include str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        }
    }

    public static function exists()
    {
        return isset(self::$_instance) ? self::$_instance : false;
    }

    public static function create(Sabai_Platform $platform)
    {
        require 'Sabai/Web.php';
        $sabai = new Sabai_Web($platform);
        $sabai->_init();
        self::$_instance = $sabai;

        return self::$_instance;
    }

    protected function __construct(Sabai_Platform $platform)
    {
        parent::__construct($platform->getRouteParam());
        $this->_platform = $platform;
    }

    private function _init()
    {
        spl_autoload_register(array($this, 'autoload'));
        $this->_db = SabaiFramework_DB::factory($this->_platform->getDBConnection(), $this->_platform->getDBTablePrefix());
        $helper_broker = new Sabai_HelperBroker($this);
        $helper_broker->addHelperDir(dirname(__FILE__) . '/Sabai/Helper', 'Sabai_Helper_');
        $this->setHelperBroker($helper_broker);
        $this->_eventDispatcher = new Sabai_EventDispatcher($this);
    }

    public function loadAddons()
    {
        if (!isset($this->_addonsLoadedTimestamp)) {
            $this->_loadAddons();
        }

        return $this;
    }

    public function reloadAddons($clearObjectCache = true)
    {
        if ($clearObjectCache) {
            $this->_addons = array();
        }
        $this->_loadAddons(true);

        return $this;
    }

    public function run(SabaiFramework_Application_Controller $controller, SabaiFramework_Application_Context $context, $route = null)
    {
        $this->_isRunning = true;

        // Invoke SabaiRun event
        $this->Action('sabai_run', array($context, $controller));

        $response = parent::run($controller, $context, $route);

        // Invoke SabaiRunComplete event
        $this->Action('sabai_run_complete', array($context));

        return $response;
    }

    public function isRunning()
    {
        return $this->_isRunning;
    } 

    /**
     *
     * @return Sabai_Platform
     */
    public function getPlatform()
    {
        return $this->_platform;
    }

    /**
     *
     * @return SabaiFramework_DB 
     */
    public function getDB()
    {
        return $this->_db;
    }

    public function getLibDir()
    {
        return $this->Path(dirname(__FILE__));
    }

    public function setUser(SabaiFramework_User $user)
    {
        $user_changed = $this->_user && $this->_user->id !== $user->id;
        // Notify that the current user object has been initialized
        $this->Action('sabai_user_initialized', array($user, $user_changed));
        
        $this->_user = $user;
        
        return $this;
    }

    /**
     * @return SabaiFramework_User
     */
    public function getUser()
    {
        if (!isset($this->_user)) {
            // Initialize the current user object if not any set
            if ($user = $this->_platform->getCurrentUser()) {
                $user->setAdministrator($this->_platform->isAdministrator($user->getIdentity()));
            } else {
                $user = new SabaiFramework_User($this->_platform->getUserIdentityFetcher()->getAnonymous());
            }
            $this->setUser($user);
        }
        
        return $this->_user;
    }
    
    public function getEventDispatcher()
    {
        return $this->_eventDispatcher;
    }

    public function setCurrentAddon($addonName)
    {
        $this->_currentAddon = $addonName;

        return $this;
    }

    public function getCurrentAddon()
    {
        return $this->_currentAddon;
    }

    public function getAddonsLoadedTimestamp()
    {
        return $this->_addonsLoadedTimestamp;
    }

    public function isAddonLoaded($addonName)
    {
        return isset($this->_addonsLoaded[$addonName]);
    }

    /**
     * A shortcut method for fetching a addon object
     * @param string $addonName
     * @return Sabai_Addon
     */
    public function getAddon($addonName = null)
    {
        if (!isset($addonName)) $addonName = $this->_currentAddon;

        if (!isset($this->_addons[$addonName])) {
            // Create addon
            $addon = $this->_createAddon($addonName);
            // Let the addon have chance to initialize itself
            $addon->init($this->_addonsLoaded[$addonName]['config']);
            // Add to memory cache
            $this->_addons[$addonName] = $addon;
        }

        return $this->_addons[$addonName];
    }
    
    /**
     * Gets a addon which is not yet installed
     *
     * @param string $addonName
     * @param array $config
     * @return Sabai_Addon
     */
    public function fetchAddon($addonName, array $config = array())
    {
        $addon = $this->_createAddon($addonName);
        // Let the addon have chance to initialize itself
        $addon->init($config + $addon->getDefaultConfig());
        
        return $addon;
    }
    
    private function _createAddon($addonName)
    {
        // Instantiate addon
        $addon_file_path = $this->getAddonPath($addonName) . '.php';
        if (!@include_once $addon_file_path) {
            // Reload add-on data and try again
            $this->reloadAddonData($addonName);
            if (!@include_once $addon_file_path) {
                $this->clearAddonInfoCache();
                throw new Sabai_AddonNotFoundException('Add-on file for add-on ' . $addonName . ' was not found at ' . $addon_file_path);
            }
        }
        $reflection = new ReflectionClass('Sabai_Addon_' . $addonName);
        return $reflection->newInstanceArgs(array($addonName, $this));       
    }

    /**
     * Returns the full path to a addon directory
     * @param string $addonName
     * @return string
     */
    public function getAddonPath($addonName, $throwException = true)
    {
        $path = $this->_getAddonData($addonName, 'path', $throwException);
        if (isset($path)) {
            return $this->PackagePath() . $path;
        }
        return $this->_getAddonData($addonName, 'clone', $throwException)
            ? $this->getClonesDir() . '/' . $addonName
            : $this->getLibDir() . '/Sabai/Addon/' . $addonName;
        
    }
    
    public function getAddonPackage($addonName)
    {
        return $this->_getAddonData($addonName, 'package');
    }
    
    protected function _getAddonData($addonName, $key, $throwException = true)
    {
        if (!isset($this->_addonsLoaded[$addonName])) {
            // Fetch from local file
            $local_addons = $this->getLocalAddons();
            if (isset($local_addons[$addonName])) {
                return @$local_addons[$addonName][$key];
            }
            
            // No local file data, so force reload
            $this->reloadAddonData($addonName, $throwException);
        }
        return @$this->_addonsLoaded[$addonName][$key];        
    }
    
    protected function reloadAddonData($addonName, $throwException = true)
    {
        $this->_loadAddons(true);
        if (isset($this->_addonsLoaded[$addonName])) {
            return;
        }
        // Can't find the add-on. Generate the add-on file if it is a cloned add-on
        if ($addonName !== 'System'
            && ($addon_info = $this->getAddon('System')->getModel('Addon')->name_is($addonName)->fetchOne()) 
            && $addon_info->parent_addon
            && isset($this->_addonsLoaded[$addon_info->parent_addon])
            && $this->CloneAddon($addon_info->parent_addon, $addonName)
        ) {
            // Load the cloned add-on
            $this->_loadAddons(true);
            if (isset($this->_addonsLoaded[$addonName])) {
                return;
            }
        }
        if ($throwException) {
            $this->clearAddonInfoCache();
            throw new Sabai_AddonNotInstalledException('The following add-on is not installed or loaded: ' . $addonName);
        }
    }

    /**
     * A shortcut method for fetching the model object of an addon
     * @param string $modelName
     * @param string $addonName
     * @return Sabai_Model
     */
    public function getModel($modelName = null, $addonName = null)
    {
        return $this->getAddon($addonName)->getModel($modelName);
    }

    public function getInstalledAddons($force = false)
    {
        if ($force || (false === $installed_addons = $this->_platform->getCache('sabai_addons_installed'))) {
            $installed_addons = array();
            try {
                $addons = $this->fetchAddon('System')->getModel('Addon')->fetch(0, 0, 'priority', 'DESC');
                foreach ($addons as $addon) {
                    $installed_addons[$addon->name] = array(
                        'version' => $addon->version,
                        'config' => $addon->getParams(false),
                        'events' => $addon->events,
                        'parent' => $addon->parent_addon,
                    );
                    if ($addon->parent_addon
                        && !file_exists($this->getClonesDir() . '/' . $addon->name . '.php')
                    ) {
                        $this->CloneAddon($addon->parent_addon, $addon->name);
                    }
                }
            } catch (SabaiFramework_DB_QueryException $e) {
                // Probably Sabai has not been installed yet
                $this->LogError($e);
            }
            $this->_platform->setCache($installed_addons, 'sabai_addons_installed');
        }

        return $installed_addons;
    }
    
    public function getClonesDir()
    {
        return $this->getPlatform()->getWriteableDir() . '/System/clones';
    }

    public function getInstalledAddon($addonName, $force = false)
    {
        $addons = $this->getInstalledAddons($force);

        return isset($addons[$addonName]) ? $addons[$addonName] : false;
    }

    public function getInstalledAddonInterfaces($force = false)
    {
        $local = $this->getLocalAddons($force);
        $addons = $this->getInstalledAddons($force);
        $data = array();
        foreach (array_keys($addons) as $addon_name) {
            if (!empty($local[$addon_name]['interfaces'])) {
                foreach ($local[$addon_name]['interfaces'] as $interface) {
                    $data[$interface][$addon_name] = true;
                }
            }
        }

        return $data;
    }

    public function getInstalledAddonsByInterface($interface, $force = false)
    {
        $interfaces = $this->getInstalledAddonInterfaces($force);

        return isset($interfaces[$interface]) ? array_keys($interfaces[$interface]) : array();
    }
    
    public function clearAddonInfoCache()
    {
        $this->_platform->deleteCache('sabai_addons_loaded');
        $this->_platform->deleteCache('sabai_addons_local');
        $this->_platform->deleteCache('sabai_addons_installed');
    }

    private function _loadAddons($force = false)
    {
        if ($force
            || (!$data = $this->_platform->getCache('sabai_addons_loaded'))
            || empty($data['addons'])
        ) {
            $data = array('addons' => array(), 'timestamp' => time());
            $this->_eventDispatcher->clear();
            $local = $this->getLocalAddons($force);
            $installed_addons = $this->getInstalledAddons($force);
            foreach (array_keys($installed_addons) as $addon_name) {
                if ($addon_data = @$local[$addon_name]) {
                    $data['addons'][$addon_name] = array(
                        'config' => $installed_addons[$addon_name]['config'],
                        'events' => $installed_addons[$addon_name]['events'],
                        'parent' => $installed_addons[$addon_name]['parent'],
                        'path' => $addon_data['path'],
                        'package' => $addon_data['package'],
                        'clone' => !empty($addon_data['clone']),
                    );
                }
            }
            $this->_platform->setCache($data, 'sabai_addons_loaded', 0);
        }

        if (empty($data['addons'])) {
            $this->clearAddonInfoCache();
            throw new Sabai_NotInstalledException();
        }

        $this->_addonsLoaded = array();
        $this->_addonsLoadedTimestamp = $data['timestamp'];
        
        // Load addons
        foreach ($data['addons'] as $addon_name => $addon_data) {
            $this->_addonsLoaded[$addon_name] = array(
                'path' => $addon_data['path'],
                'config' => $addon_data['config'],
                'package' => $addon_data['package'],
                'parent' => @$addon_data['parent'], // suppress error for backward compat with < 1.1.4
            );
            if (!empty($addon_data['clone'])) {
                $this->_addonsLoaded[$addon_name]['clone'] = true;
            }
            if (!empty($addon_data['events'])) {
                foreach ($addon_data['events'] as $event) {
                    if (is_array($event)) {
                        $event_name = $event[0];
                        $event_priority = $event[1];
                    } else {
                        $event_name = $event;
                        $event_priority = $addon_name === 'System' ? 99 : 10;
                    }
                    $this->_eventDispatcher->addListener(strtolower($event_name), $addon_name, $event_priority);
                }
            }
        }
        
        $this->Action('sabai_addons_loaded');
    }

    public function getLocalAddons($force = false)
    {
        if ($force || (false === $addons = $this->_platform->getCache('sabai_addons_local'))) {
            $logs = array('Loading local add-on files');
            $addons = array();
            $lib_dir = $this->Path(dirname(__FILE__));
            // Get paths to available addons
            $directories = $this->Filter('sabai_addon_paths', array());
            // Add directory where cloned add-on files should be placed
            $directories[] = $clones_dir = $this->getClonesDir();
            $clones_dir = $this->Path($clones_dir);
            // Add the core path
            array_unshift($directories, $lib_dir . '/Sabai/Addon');
            $package_root = $this->PackagePath();
            foreach ($directories as $directory) {
                if (is_array($directory)) {
                    if (empty($directory[1]) || version_compare($directory[1], self::VERSION, '<')) continue;
                    $version = $directory[1]; 
                    $directory = $directory[0];
                }
                $logs[] = 'Searching add-on files from ' . $directory;
                $directory = $this->Path($directory);
                if (!$files = glob($directory . '/*.php', GLOB_NOSORT)) {
                    $logs[] = 'No valid files found under ' . $directory;
                    continue;
                }
                foreach ($files as $file) {
                    $addon_name = basename($file, '.php');
                    // Skip addons without a valid name
                    if (!preg_match(self::ADDON_NAME_REGEX, $addon_name)) continue;
                
                    // Skip if addon already loaded unless newer version
                    if (isset($addons[$addon_name])
                        && $addons[$addon_name]['version'] >= $version // use plugin version to assume add-on version
                    ) continue;
                    
                    $logs[] = 'File for add-on ' . $addon_name . ' found';

                    require_once $file;
                    $addon_class = 'Sabai_Addon_' . $addon_name;
                    if (!class_exists($addon_class, false)) continue;
                    
                    $logs[] = 'Class for add-on ' . $addon_name . ' found';

                    $interfaces = class_implements($addon_class, false);
                    if (is_callable(array($addon_class, 'interfaces'))
                        && ($_interfaces = call_user_func(array($addon_class, 'interfaces'))) // check for extra interfaces implemented
                    ) {
                        $interfaces += array_flip($_interfaces);
                    }
                    $is_clone = strpos($directory, $clones_dir) === 0;
                    $addons[$addon_name] = array(
                        'version' => $version = constant($addon_class . '::VERSION'),
                        'package' => constant($addon_class . '::PACKAGE'),
                        'interfaces' => array_keys($interfaces),
                        'path' => $is_clone || strpos($directory, $lib_dir) === 0
                            ? null
                            : substr($directory, strlen($package_root)) . '/' . $addon_name,
                        'clone' => $is_clone,
                    );
                    
                    $logs[] = 'Add-on ' . $addon_name . ' (' . $version . ') loaded';
                }
            }
            $logs[] = 'done.';
            ksort($addons);
            $this->_platform->setCache($addons, 'sabai_addons_local')
                ->updateSabaiOption('addons_local_log', implode('...', $logs));
        }

        return $addons;
    }
    
    /**
     * Alias for htmlspecialchars()
     *
     * @param string $str
     * @param int $quoteStyle
     * @param bool $doubleEncode
     * @return string
     */
    public static function h($str, $quoteStyle = ENT_QUOTES, $doubleEncode = false)
    {
        return htmlspecialchars($str, $quoteStyle, SABAI_CHARSET, $doubleEncode);
    }

    /**
     * Echos out the result of htmlspecialchars()
     *
     * @param string $str
     * @param int $quoteStyle
     * @param bool $doubleEncode
     * @return string
     */
    public static function _h($str, $quoteStyle = ENT_QUOTES, $doubleEncode = false)
    {
        echo htmlspecialchars($str, $quoteStyle, SABAI_CHARSET, $doubleEncode);
    }
}
