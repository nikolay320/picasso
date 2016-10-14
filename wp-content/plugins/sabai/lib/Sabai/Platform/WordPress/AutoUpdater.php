<?php
class Sabai_Platform_WordPress_AutoUpdater
{
    protected $_pluginName, $_remoteUrl = 'http://sabaidiscuss.com/updates/index.php', $_remoteArgs;
    private $_remoteInfo, $_remoteError;  

    /**
     * Initialize a new instance of the WordPress Auto-Update class
     * @param string $pluginName
     * @param array $remoteArgs
     * @param string $remoteUrl
     */
    function __construct($pluginName, array $remoteArgs = array(), $remoteUrl = null)
    {
        $this->_pluginName = $pluginName;
        $this->_remoteArgs = $remoteArgs;
        if (isset($remoteUrl)) {
            $this->_remoteUrl = $remoteUrl;
        }
        // Define the alternative API for updating checking
        add_filter('site_transient_update_plugins', array($this, 'checkUpdate'), 99999);
        // Define the alternative response for information checking
        add_filter('plugins_api', array($this, 'checkInfo'), 99999, 3);
        
        add_action('deleted_site_transient', array($this, 'resetChecked'));
    }
    
    public function resetChecked($transient)
    {
        if ($transient === 'update_plugins') {
            $this->_updateChecked = false;
        }
    }

    /**
     * Add our self-hosted autoupdate plugin to the filter transient
     *
     * @param $transient
     * @return object $transient
     */
    public function checkUpdate($transient)
    {        
        // Get the remote info
        if (!$remote_info = $this->getRemoteInfo()) {
            return $transient;
        }

        // If a newer version is available, add the update
        if (version_compare(Sabai_Platform_WordPress::getPluginData($this->_pluginName, 'Version', '0.0.0'), $remote_info->version, '<')) {
            $obj = new stdClass();  
            $obj->slug = $remote_info->slug;  
            $obj->new_version = $remote_info->version;  
            $obj->url = $remote_info->homepage;  
            if (isset($remote_info->download_link)) { // download link is not available if no valid license key
                $obj->package = $remote_info->download_link;
            }
            $transient->response[$this->_pluginName . '/' . $this->_pluginName . '.php'] = $obj;
            
            if (is_multisite()) {
                set_site_transient('update_plugins', $transient);
            }
        }
        
        return $transient;
    }

    /**
     * Add our self-hosted description to the filter
     *
     * @param bool|object $false
     * @param string $action
     * @param object $arg
     * @return bool|object
     */
    public function checkInfo($false, $action, $arg)
    {
        if ($action === 'plugin_information'
            && $arg->slug === $this->_pluginName
            && (false !== $info = $this->getRemoteInfo())
        ) {
            return $info;
        }
        return $false;
    }

    /**
     * Get information about the remote version
     * @return bool|object
     */
    public function getRemoteInfo()
    {
        if (!isset($this->_remoteInfo)) {
            $info = get_site_transient('sabai_plugin_info');
            if (isset($info[$this->_pluginName])) {
                return $info[$this->_pluginName];
            }
            
            $request_body = array(
                'package' => $this->_pluginName,
                'action' => 'info',
                'wp_version' => get_bloginfo('version'),
                'site_url' => home_url(),
                'package_version' => Sabai_Platform_WordPress::getPluginData($this->_pluginName, 'Version', '0.0.0'),
                'sabai_version' => Sabai_Platform_WordPress::getPluginData('sabai', 'Version', '0.0.0'),
            ) + $this->_remoteArgs;
            $response = wp_remote_post($this->_remoteUrl, array('body' => $request_body));
            if (is_wp_error($response)) {
                $this->_remoteInfo = false;
                $this->_remoteError = $response->get_error_message();
            } elseif (200 != $code = wp_remote_retrieve_response_code($response)) {
                $this->_remoteInfo = false;
                $this->_remoteError = 'The server did not return a valid license information. Response code: ' . $code . '; Request sent: ' . serialize($request_body);
            } else {
                if (!is_array($info)) {
                    $info = array();
                }
                $this->_remoteInfo = $info[$this->_pluginName] = unserialize($response['body']);
                set_site_transient('sabai_plugin_info', $info, 7200); // cache for 2 hours
            }
        }  
        return $this->_remoteInfo;
    }
    
    public function getRemoteError()
    {
        return $this->_remoteError;
    }
}