<?php
abstract class Sabai_Platform
{
    protected $_name, $_routeParam;

    protected function __construct($name, $routeParam = 'q')
    {
        $this->_name = $name;
        $this->_routeParam = $routeParam;
    }

    final public function getName()
    {
        return $this->_name;
    }

    final public function getRouteParam()
    {
        return $this->_routeParam;
    }
    
    public function getHomeUrl()
    {
        return $this->getSiteUrl();
    }
    
    public function getSlug($addon, $name)
    {
        $slugs = $this->getSabai()->System_Slugs();
        return @$slugs[$addon]['slugs'][$name]['slug'];
    }
    
    public function getTitle($addon, $name)
    {
        $slugs = $this->getSabai()->System_Slugs();
        return @$slugs[$addon]['slugs'][$name]['title'];
    }
    
    /**
     * Gets an instance of Sabai
     * @param $loadAddons bool
     * @return Sabai
     */
    abstract public function getSabai($loadAddons = true);
    /**
     * @return SabaiFramework_User_IdentityFetcher
     */
    abstract public function getUserIdentityFetcher();
    /**
     * @return SabaiFramework_User
     */
    abstract public function getCurrentUser();
    /**
     * @return array
     */
    abstract public function getUserRoles();
    /**
     * @param string $role
     * @return bool
     */
    abstract public function isAdministratorRole($role);
    /**
     * @param string $userId
     * @return array
     */
    abstract public function getUserRolesByUser($userId);
    /**
     * @param string|array $roleName
     * @return array Array of Sabai_UserIdentity indexed by user IDs
     */
    abstract public function getUsersByUserRole($roleName);
    abstract public function getDefaultUserRole();
    abstract public function getWriteableDir();
    abstract public function getSitePath();
    abstract public function getPackagePath();
    abstract public function getSiteName();
    abstract public function getSiteEmail();
    abstract public function getSiteUrl();
    abstract public function getSiteAdminUrl();
    abstract public function getAssetsUrl($package = null);
    abstract public function getAssetsDir($package = null);
    abstract public function getLoginUrl($redirect);
    abstract public function getLogoutUrl();
    abstract public function getRegisterForm();
    abstract public function registerUser(array $values);
    abstract public function loginUser($userId);
    abstract public function getDBConnection();
    abstract public function getDBTablePrefix();
    abstract public function mail($to, $subject, $body, array $options = array());
    abstract public function setSessionVar($name, $value, $userId = null);
    abstract public function getSessionVar($name, $userId = null);
    abstract public function deleteSessionVar($name, $userId = null);
    abstract public function setUserMeta($userId, $name, $value);
    abstract public function getUserMeta($userId, $name, $default = null);
    abstract public function deleteUserMeta($userId, $name);
    abstract public function getUsersByMeta($name, $limit = 20, $offset = 0, $order = 'DESC', $isNumber = true);
    abstract public function setCache($data, $id, $lifetime = null);
    abstract public function getCache($id);
    abstract public function deleteCache($id);
    abstract public function clearCache();
    abstract public function logInfo($info);
    abstract public function logWarning($warning);
    abstract public function logError($error);
    abstract public function getLocale();
    abstract public function isLanguageRTL();
    abstract public function setOption($name, $value);
    abstract public function getOption($name, $default = null);
    abstract public function deleteOption($name);
    abstract public function getCustomAssetsDir();
    abstract public function getCustomAssetsDirUrl();
    abstract public function getUserProfileHtml($userId);
    abstract public function resizeImage($imgPath, $destPath, $width, $height, $crop = false);
    abstract public function getHumanTimeDiff($from);
    abstract public function getSiteToSystemTime($timestamp);    
    abstract public function getSystemToSiteTime($timestamp);
    abstract public function unzip($from, $to);
    abstract public function updateDatabase($schema, $previousSchema = null);
    abstract public function addHeader($header, $handle, $index = 10);    
    abstract public function addJsFile($url, $handle, $dependency = null);
    abstract public function addJs($js, $handle, $onDomReady = true, $index = null);
    abstract public function addCssFile($url, $handle, $dependency = null, $media = 'screen');
    abstract public function addCss($css, $handle, $index = null);
    abstract public function setFlash(array $flash);
    abstract public function isAdmin();
    abstract public function getCookieDomain();
    abstract public function getCookiePath();
    abstract public function htmlize($text, array $allowedTags = null);
    abstract public function getStartOfWeek();
    abstract public function getGMTOffset();
}