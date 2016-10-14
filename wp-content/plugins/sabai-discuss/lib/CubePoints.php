<?php
class Sabai_Addon_CubePoints extends Sabai_Addon
{
    const VERSION = '1.3.28', PACKAGE = 'sabai-discuss';
    
    private static $_helper, $_cpId = 'sabai_discuss';
    
    public function isUninstallable($currentVersion)
    {
        return true;
    }
    
    public function onSabaiRun()
    {
        if ($this->hasParent()
            || !$this->_isCubePointsPluginActive()
            || !$this->_isCubePointsIntegrationEnabled()
        ) {
            return;
        }
        
        $this->_application->getHelperBroker()->setHelper('Questions_UpdateUserReputation', array(__CLASS__, 'updateUserReputation'));
    }
    
    public static function updateUserReputation(Sabai $application, $pointId, SabaiFramework_User_Identity $identity, $points, $addonName = null)
    {
        if (!isset($addonName)) {
            $addonName = $application->getCurrentAddon();
        }
        if (!isset(self::$_helper)) {
            require_once $application->getAddonPath('Questions') . '/Helper/UpdateUserReputation.php';
            self::$_helper = new Sabai_Addon_Questions_Helper_UpdateUserReputation();
        }
        // Update reputation points for the Questions add-on
        $reputation = self::$_helper->help($application, $pointId, $identity, $points, $addonName);
        
        // Update points for the CubePoints plugin
        cp_points(self::$_cpId . '_' . $pointId, $identity->id, $points, $addonName);
        
        return $reputation;
    }
    
    public function onSabaiPlatformWordPressAdminInit()
    {
        if ($this->hasParent()
            || !$this->_isCubePointsPluginActive()
        ) {
            return;
        }
        
        require_once Sabai_Platform_WordPress::getPluginsDir() . '/cubepoints/cp_core.php';
        cp_module_register(
            'SabaiDiscuss Integration',
            self::$_cpId,
            $this->getVersion(),
            'onokazu',
            'http://codecanyon.net/user/onokazu/portfolio',
            'http://www.SabaiApps.com/',
            'Adds CubePoints support to SabaiDiscuss.',
            true
        );     
        add_action('cp_logs_description', array($this, 'cpLogsDescription'), 10, 4);
    }
    
    public function cpLogsDescription($type, $uid, $points, $data)
    {
        $prefix = self::$_cpId . '_';
        if (0 !== strpos($type, $prefix)) {
            return;
        }
        switch (substr($type, strlen($prefix))) {
            case 'accept_answer':
                $log = $points > 0 ? __('Accepted an answer', 'sabai-discuss') : __('Unaccepted an answer', 'sabai-discuss');
                break;
            case 'answer_accepted':
                $log = $points > 0 ? __('Answer was accepted', 'sabai-discuss') : __('Answer was unaccepted', 'sabai-discuss');
                break;
            case 'vote_question':
                $log = $points > 0 ? __('Voted a question up', 'sabai-discuss') : __('Voted a question down', 'sabai-discuss');
                break;
            case 'question_voted':
                $log = $points > 0 ? __('Question was voted up', 'sabai-discuss') : __('Question was voted down', 'sabai-discuss');
                break;
            case 'vote_answer':
                $log = $points > 0 ? __('Voted an answer up', 'sabai-discuss') : __('Voted an answer down', 'sabai-discuss');
                break;
            case 'answer_voted':
                $log = $points > 0 ? __('Answer was voted up', 'sabai-discuss') : __('Answer was voted down', 'sabai-discuss');
                break;
            case 'content_deleted':
                $log = __('A question or an answer was deleted as being spam', 'sabai-discuss');
                break;
            default:
                return;
        }
        printf('[%s] %s', $data, $log);
    }
    
    private function _isCubePointsPluginActive()
    {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return is_plugin_active('cubepoints/cubepoints.php');
    }
    
    private function _isCubePointsIntegrationEnabled()
    {
        if (!function_exists('cp_module_activated')) {
            require_once Sabai_Platform_WordPress::getPluginsDir() . '/cubepoints/cp_core.php';
        }
        return cp_module_activated(self::$_cpId);
    }
}