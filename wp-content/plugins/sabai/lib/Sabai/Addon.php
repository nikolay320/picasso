<?php
abstract class Sabai_Addon implements SabaiFramework_EventDispatcher_EventListener
{
    protected $_name, $_config, $_application;
    private $_package;
    private static $_models = array();

    public function __construct($name, Sabai $application)
    {
        $this->_name = $name;
        $this->_application = $application;
    }
    
    final public function init(array $config)
    {
        $this->_config = $config;
        $this->_init();
    }
    
    protected function _init() {}

    public function handleEvent(SabaiFramework_EventDispatcher_Event $event)
    {
        return @call_user_func_array(array($this, 'on' . $event->getType()), $event->getVars());
    }
    
    private static function _filterAddonEvent($method)
    {
        return strpos(strtolower($method), 'on') === 0; // event listener methods have their names prefixed with "on"
    }

    private static function _mapAddonEvent($method)
    {
        return strtolower(substr($method, 2)); // strip the "on" prefix
    }
    
    public function getEvents()
    {
        // Fetch method names that start with 'on'
        return array_map(
            array(__CLASS__, '_mapAddonEvent'),
            array_filter(get_class_methods($this), array(__CLASS__, '_filterAddonEvent'))
        );
    }

    final public function __toString()
    {
        return $this->_name;
    }
    
    final public function getApplication()
    {
        return $this->_application;
    }

    final public function getName()
    {
        return $this->_name;
    }

    final public function getVersion()
    {
        return constant(get_class($this) . '::VERSION');
    }
    
    final public function getPackage()
    {
        if (!isset($this->_package)) {
            $this->_package = constant(get_class($this) . '::PACKAGE');
        }
        return $this->_package; 
    }
    
    final public function getType()
    {
        if (!$ret = $this->hasParent()) {
            $ret = $this->getName();
        }
        return $ret;
    }
    
    public function getAddonInfo()
    {
        return array();
    }

    final public function getModel($modelName = null)
    {
        $addon_type = $this->getType();
        if (!isset(self::$_models[$addon_type])) {
            self::$_models[$addon_type] = new Sabai_Model($this);
        }

        return isset($modelName) ? self::$_models[$addon_type]->getRepository($modelName) : self::$_models[$addon_type];
    }

    final public function getConfig($name = null)
    {
        if (0 === $num_args = func_num_args()) return $this->_config;
        if ($num_args === 1) return @$this->_config[$name];

        $args = func_get_args();
        $config = $this->_config;
        foreach ($args as $arg) {
            if (!isset($config[$arg])) return null;

            $config = $config[$arg];
        }

        return $config;
    }

    public function getDefaultConfig()
    {
        // Override this to provide default configuration parameters
        return array();
    }

    public function getNonCacheableConfig()
    {
        // Override this to provide configuration parameters that may not be cached on the file system
        return array();
    }

    final public function loadConfig($configName = null)
    {
        if (!$entity = $this->_application->getAddon('System')->getModel('Addon')->name_is($this->_name)->fetchOne()) {
            throw new RuntimeException(sprintf('Failed fetching the model entity for add-on %s', $this->_name));
        }

        $params = $entity->getParams();
        if (!isset($configName)) {
            $this->_config = $params;

            return $this->_config;
        }

        $this->_config[$configName] = $params[$configName];

        return $this->_config[$configName];
    }

    final public function saveConfig(array $config, $merge = true)
    {
        $this->_application->getAddon('System')->saveAddonConfig($this->_name, $config, $merge);
    }
    
    public function hasParent()
    {
        if (__CLASS__ === $parent_class = get_parent_class($this)) {
            return false;
        }
        return substr($parent_class, strlen('Sabai_Addon_')); // return the parent addon name
    }

    /**
     * Installs the plugin
     * @throws SabaiFramework_DB_SchemaException
     */
    public function install(ArrayObject $log)
    {
        if ($schema = $this->_hasSchema()) {
            $this->_application->getPlatform()->updateDatabase($schema);
        }
        $this->_createVarDirs();

        return $this;
    }

    /**
     * Uninstalls the plugin
     * @throws RuntimeException
     * @throws SabaiFramework_DB_SchemaException
     */
    public function uninstall(ArrayObject $log)
    {
        if ($latest_schema = $this->_hasSchema()) {
            if (false === $schema_old = $this->_getOlderSchemaList($this->getVersion())) {
                throw new RuntimeException('Failed fetching schema files.');
            }

            if (!empty($schema_old)) {
                // get the last schema file
                $previous_schema = array_pop($schema_old);
            } else {
                $previous_schema = $latest_schema;
            }
            $this->_application->getPlatform()->updateDatabase(null, $previous_schema);
        }

        return $this;
    }

    /**
     * Upgrades the plugin
     * @param string $currentVersion
     * @throws RuntimeException
     * @throws SabaiFramework_DB_SchemaException
     */
    public function upgrade($currentVersion, ArrayObject $log)
    {
        if ($this->_hasSchema()) {
            if ((false === $schema_old = $this->_getOlderSchemaList($currentVersion))
                || (false === $schema_new = $this->_getNewerSchemaList($currentVersion))
            ) {
                throw new RuntimeException('Failed fetching schema files.');
            }
            if (!empty($schema_new)) {
                usort($schema_new, array(__CLASS__, '_sortSchema')); // sort from old to new
                $new_schema = array_pop($schema_new);
                $previous_schema = null;
                if (!empty($schema_old)) {
                    usort($schema_old, array(__CLASS__, '_sortSchema')); // sort from old to new
                    // get the last schema file
                    $previous_schema = array_pop($schema_old);
                }
                $this->_application->getPlatform()->updateDatabase($new_schema, $previous_schema);
                $log[] = sprintf(
                    'Updated database schema from %s to %s.',
                    isset($previous_schema) ? basename($previous_schema) : 'none',
                    basename($new_schema)
                );
            }
        }

        $this->_createVarDirs();

        return $this;
    }
    
    protected function _createVarDirs()
    {
        // Create data directory
        if ($dir = $this->hasVarDir()) {
            if (!is_dir($path = $this->getVarDir())) {
                @mkdir($path, 0755, true);
            }
            if (is_array($dir)) {
                foreach ($dir as $_dir) {
                    if (!is_dir($path = $this->getVarDir($_dir))) {
                        @mkdir($path, 0755);
                    }
                }
            }
        }
    }

    private static function _sortSchema($a, $b)
    {
        return version_compare($a, $b, '<') ? -1 : 1;
    }

    private function _getSchemaList()
    {
        $list = array();
        $schema_dir = $this->_application->getAddonPath($this->_name) . '/schema';
        if (false === $files = glob($schema_dir . '/*.php', GLOB_NOSORT)) return false; // return false on error
        foreach ($files as $file) {
            if (preg_match('/^\d+(?:\.\d+)*(?:[a-zA-Z]+\d*)?\.php$/', basename($file))) {
                $file_version = basename($file, '.php');
                $list[$file_version] = $file;
            }
        }

        return $list;
    }

    private function _getOlderSchemaList($version)
    {
        if (!$list = $this->_getSchemaList()) return $list;

        return array_intersect_key($list, array_flip(array_filter(
            array_flip($list),
            create_function('$v', sprintf('return version_compare($v, "%s", "<=");', $version))
        )));
    }

    private function _getNewerSchemaList($version)
    {
        if (!$list = $this->_getSchemaList()) return $list;

        return array_intersect_key($list, array_flip(array_filter(
            array_flip($list),
            create_function('$v', sprintf('return version_compare($v, "%s", ">");', $version))
        )));
    }

    final protected function _hasSchema($version = 'latest')
    {
        $schema_path = $this->_application->getAddonPath($this->_name) . '/schema/' . $version . '.php';

        return file_exists($schema_path) ? $schema_path : false;
    }

    final public function getVarDir($subdir = null)
    {
        $var_dir = $this->_application->Path($this->_application->getPlatform()->getWriteableDir()) . '/' . $this->_name;
        
        return isset($subdir) ? $var_dir . '/' . $subdir : $var_dir;
    }

    public function hasVarDir()
    {
        return false;
    }
    
    public function isInstallable()
    {
        if ($parent_addon = $this->hasParent()) {
            // Installable if the parent addon is already installed
            return $this->_application->isAddonLoaded($parent_addon);
        }
        return true;
    }
    
    public function isUpgradeable($currentVersion, $newVersion)
    {
        if (!version_compare($currentVersion, $newVersion, '<')) {
            return false;
        }
        // Upgradeable if the parent addon is already upgraded
        return ($parent_addon = $this->hasParent()) ? $this->_application->CheckAddonVersion($parent_addon, $newVersion) : true;
    }
        
    public function isReloadable($currentVersion, $newVersion)
    {
        // Upgradeable if the parent addon is already upgraded
        return ($parent_addon = $this->hasParent()) ? $this->_application->CheckAddonVersion($parent_addon, $newVersion) : true;
    }
        
    public function isUninstallable($currentVersion)
    {
        return $this->hasParent() ? true : false;
    }
            
    public function isCloneable()
    {
        return false;
    }
        
    public function hasSettingsPage($currentVersion)
    {
        return false;
    }
    
    public function getSlug($name = null)
    {
        if (!isset($name)) {
            $name = strtolower($this->_name);
        }
        return $this->_application->getPlatform()->getSlug($this->_name, $name);
    }
    
    public function getTitle($name = null)
    {
        if (!isset($name)) {
            $name = strtolower($this->_name);
        }
        return $this->_application->getPlatform()->getTitle($this->_name, $name);
    }
}
