<?php
require_once 'Sabai/Platform.php';

class Sabai_Platform_WordPress extends Sabai_Platform
{
    const VERSION = '1.3.28';
    private $_mainContent, $_mainRoute, $_template, $_userToBeDeleted,
        $_js = array(), $_jsIndex = 0, $_htmlHead = array(), $_css = array(), $_cssIndex = 0,
        $_shortcode, $_jqueryUiLoaded, $_jqueryUiCssLoaded, $_isContentFiltered = false;
    private static $_instance;

    protected function __construct()
    {
        parent::__construct('WordPress');
    }

    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
            if (!defined('SABAI_WORDPRESS_SESSION_TRANSIENT')) {
                define('SABAI_WORDPRESS_SESSION_TRANSIENT', true);
            }
            if (SABAI_WORDPRESS_SESSION_TRANSIENT && !defined('SABAI_WORDPRESS_SESSION_TRANSIENT_LIFETIME')) {
                define('SABAI_WORDPRESS_SESSION_TRANSIENT_LIFETIME', 1800);
            }
            if (!defined('SABAI_WORDPRESS_ADMIN_CAPABILITY')) {
                define('SABAI_WORDPRESS_ADMIN_CAPABILITY', 'activate_plugins');
            }
        }
        return self::$_instance;
    }

    public function getUserIdentityFetcher()
    {
        return new Sabai_Platform_WordPress_UserIdentityFetcher(__('Guest', 'sabai'));
    }

    public function getCurrentUser()
    {
        $wp_user = wp_get_current_user();
        if ($wp_user->ID == 0) return false;

        $thumbnail_large = $thumbnail_medium = $thumbnail_small = '';
        $avatar_default = get_option('avatar_default');
        if (preg_match("/src='(.*?)'/i", get_avatar($wp_user->user_email, Sabai::THUMBNAIL_SIZE_LARGE, $avatar_default), $matches)) {
            $thumbnail_large = $matches[1];
        }
        if (preg_match("/src='(.*?)'/i", get_avatar($wp_user->user_email, Sabai::THUMBNAIL_SIZE_MEDIUM, $avatar_default), $matches)) {
            $thumbnail_medium = $matches[1];
        }
        if (preg_match("/src='(.*?)'/i", get_avatar($wp_user->user_email, Sabai::THUMBNAIL_SIZE_SMALL, $avatar_default), $matches)) {
            $thumbnail_small = $matches[1];
        }
        return new SabaiFramework_User(new Sabai_UserIdentity(
            $wp_user->ID,
            $wp_user->user_login,
            array(
                'name' => $wp_user->display_name,
                'email' => $wp_user->user_email,
                'url' => $wp_user->user_url,
                'created' => strtotime($wp_user->user_registered),
                'thumbnail_large' => $thumbnail_large,
                'thumbnail_medium' => $thumbnail_medium,
                'thumbnail_small' => $thumbnail_small,
            )
        ));
    }

    public function getUserRoles()
    {
        global $wp_roles;

        if (!isset($wp_roles)) $wp_roles = new WP_Roles();

        return $wp_roles->get_names();
    }
    
    public function isAdministrator(Sabai_UserIdentity $identity)
    {
        $id = $identity->id;
        return is_super_admin($id)
            || user_can($id, SABAI_WORDPRESS_ADMIN_CAPABILITY)
            || user_can($id, 'manage_sabai_content')
            || user_can($id, 'manage_sabai');
    }

    public function isAdministratorRole($roleName)
    {
        $role = get_role($roleName);
        if (!is_object($role)) return false;
        
        return $role->has_cap(SABAI_WORDPRESS_ADMIN_CAPABILITY)
            || $role->has_cap('manage_sabai_content')
            || $role->has_cap('manage_sabai');
    }

    public function getUserRolesByUser($userId)
    {
        $user = new WP_User($userId);

        return $user->roles;
    }
    
    public function getUsersByUserRole($roleName)
    {
        $ret = array();
        $avatar_default = get_option('avatar_default');
        $avatar_rating = get_option('avatar_rating');
        foreach ((array)$roleName as $role_name) {
            foreach (get_users(array('role' => $role_name)) as $user) {
                if (!isset($ret[$user->ID])) {
                    $ret[$user->ID] = new Sabai_Platform_WordPress_UserIdentity($user, array(), $avatar_default, $avatar_rating);
                }
            }
        }

        return $ret;
    }
    
    public function getDefaultUserRole()
    {
        return get_option('default_role');
    }

    public function getWriteableDir()
    {
        $ret = WP_CONTENT_DIR . '/sabai';
        if (is_multisite() && $GLOBALS['blog_id'] != 1) {
            $ret .= '/sites/' . $GLOBALS['blog_id'];
        }
        return $ret;                
    }
        
    public function getSitePath()
    {
        return rtrim(ABSPATH, '/');
    }

    public function getSiteName()
    {
        return get_option('blogname');
    }

    public function getSiteEmail()
    {
        return get_option('admin_email');
    }

    public function getSiteUrl()
    {
        return site_url();
    }

    public function getSiteAdminUrl()
    {
        return rtrim(admin_url(), '/');
    }
    
    public function getPackagePath()
    {
        return WP_PLUGIN_DIR;
    }

    public function getAssetsUrl($package = null)
    {
        return plugins_url() . (isset($package) ? '/' . $package . '/assets' :  '/sabai/assets');
    }

    public function getAssetsDir($package = null)
    {
        return self::getPluginsDir() . (isset($package) ? '/' . $package . '/assets' :  '/sabai/assets');
    }
    
    public function getLoginUrl($redirect)
    {
        return wp_login_url($redirect);
    }

    public function getLogoutUrl()
    {
        return wp_logout_url();
    }

    public function getRegisterForm()
    {
        $form = array(
            '#element_validate' => array(array($this, 'validateRegister')),
            'username' => array(
                '#type' => 'textfield',
                '#title' => __('Username', 'sabai'),
                '#required' => true,
            ),
            'email' => array(
                '#type' => 'email',
                '#title' => __('E-mail Address', 'sabai'),
                '#required' => true,
            ),
        );
        ob_start();
        do_action('register_form');
        if ($extra = ob_get_clean()) {
            $form['extra'] = array(
                '#type' => 'markup',
                '#markup' => $extra,
            );
        }
        return $form;
    }
    
    public function validateRegister(Sabai_Addon_Form_Form $form)
    {
        if (username_exists($form->values['username'])) {
            $form->setError(__('The username is already taken.'), 'username');
        }
        if (email_exists($form->values['email'])) {
            $form->setError(__('The e-mail address is already taken.'), 'email');
        }
    }
    
    public function registerUser(array $values)
    {
        $user_id = register_new_user($values['username'], $values['email']);
        if (is_wp_error($user_id)) {
            throw new Sabai_RuntimeException($user_id->get_error_message());
        }
        return $user_id;
    }
    
    public function loginUser($userId)
    {
        if (!$user = get_user_by('id', $userId)) return false;
        
        wp_set_current_user($user->ID, $user->user_login);
        wp_set_auth_cookie($user->ID);
        do_action('wp_login', $user->user_login);
        return new Sabai_Platform_WordPress_UserIdentity($user);
    }
    
    public function getHomeUrl()
    {
        return home_url();
    }

    public function getDBConnection()
    {
        return new Sabai_Platform_WordPress_DBConnection();
    }

    public function getDBTablePrefix()
    {
        return $GLOBALS['wpdb']->prefix . 'sabai_';
    }

    public function mail($to, $subject, $body, array $options = array())
    {
        $options += array(
            'from' => $this->getSiteName(),
            'from_email' => $this->getSiteEmail(),
            'attachments' => array(),
            'headers' => array(),
        );
        
        $options['headers'][] = sprintf('From: %s <%s>', $options['from'], $options['from_email']);

        // Attachments?
        if (!empty($options['attachments'])) {
            foreach (array_keys($options['attachments']) as $i) {
                // wp_mail() accepts file path only
                $options['attachments'][$i] = $options['attachments'][$i]['path'];
            }
        }
        
        if (!empty($options['is_html'])) {
            add_filter('wp_mail_content_type', array($this, 'onWpMailContentType'));
        }

        wp_mail($to, $subject, $body, $options['headers'], $options['attachments']);
        
        if (!empty($options['is_html'])) {
            remove_filter('wp_mail_content_type', array($this, 'onWpMailContentType'));
        }

        return $this;
    }
    
    public function onWpMailContentType()
    {
        return 'text/html';
    }

    public function setSessionVar($name, $value, $userId = null)
    {
        $name = $GLOBALS['wpdb']->prefix . $name;
        if (SABAI_WORDPRESS_SESSION_TRANSIENT) {
            if (isset($userId)) {
                if (empty($userId)) {
                    return $this;
                }
                $name .= ':' . $userId;
            }
            $this->setCache($value, 'session_' . $name, SABAI_WORDPRESS_SESSION_TRANSIENT_LIFETIME);
        } else {
            $_SESSION['sabai'][$name] = $value;
        }
        return $this;
    }

    public function getSessionVar($name, $userId = null)
    {
        $name = $GLOBALS['wpdb']->prefix . $name;
        if (SABAI_WORDPRESS_SESSION_TRANSIENT) {
            if (isset($userId)) {
                if (empty($userId)) {
                    return;
                }
                $name .= ':' . $userId;
            }
            $ret = $this->getCache('session_' . $name);
            return $ret === false ? null : $ret;
        }
        return isset($_SESSION['sabai'][$name])
            ? $_SESSION['sabai'][$name]
            : null;
    }

    public function deleteSessionVar($name, $userId = null)
    {
        $name = $GLOBALS['wpdb']->prefix . $name;
        if (SABAI_WORDPRESS_SESSION_TRANSIENT) {
            if (isset($userId)) {
                if (empty($userId)) {
                    return;
                }
                $name .= ':' . $userId;
            }
            $this->deleteCache('session_' . $name);
        } else {
            if (isset($_SESSION['sabai'][$name])) {
                unset($_SESSION['sabai'][$name]);
            }
        }

        return $this;
    }

    public function setUserMeta($userId, $name, $value)
    {
        update_user_meta($userId, $GLOBALS['wpdb']->prefix . 'sabai_sabai_' . $name, $value);

        return $this;
    }

    public function getUserMeta($userId, $name, $default = null)
    {
        $ret = get_user_meta($userId, $GLOBALS['wpdb']->prefix . 'sabai_sabai_' . $name, true);
        return $ret === '' ? $default : $ret;
    }

    public function deleteUserMeta($userId, $name)
    {
        delete_user_meta($userId, $GLOBALS['wpdb']->prefix . 'sabai_sabai_' . $name);

        return $this;
    }
    
    public function getUsersByMeta($name, $limit = 20, $offset = 0, $order = 'DESC', $isNumber = true)
    {
        $query = new WP_User_Query(array(
            'meta_key' => $meta_key = $GLOBALS['wpdb']->prefix . 'sabai_sabai_' . $name,
            'orderby' => $isNumber ? 'meta_value_num' : 'meta_value',
            'order' => $order,
            'number' => $limit,
            'offset' => $offset,
        ));
        $ret = array();
        if (!empty($query->results)) {
            $avatar_default = get_option('avatar_default');
            $avatar_rating = get_option('avatar_rating');
            foreach ($query->results as $user) {
                $ret[$user->ID] = new Sabai_Platform_WordPress_UserIdentity($user, array($name => $user->get($meta_key)), $avatar_default, $avatar_rating);
            }
        }
        return $ret;
    }

    public function getCache($id)
    {
        return get_transient('sabai_' . md5($id));
    }

    public function setCache($data, $id, $lifetime = null)
    {
        // Always set expiration to prevent this cache data from being autoloaded on every request by WP.
        // Lifetime can be set to 0 to never expire but the value will be autoloaded on every request.
        if (!isset($lifetime)) {
            $lifetime = 604800;
        }
        set_transient('sabai_' . md5($id), $data, $lifetime);

        return $this;
    }

    public function deleteCache($id)
    {
        delete_transient('sabai_' . md5($id));

        return $this;
    }
    
    public function clearCache()
    {
        global $wpdb;
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE ('_transient_sabai_%')");
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE ('_transient_timeout_sabai_%')");
        // delete plugin autoupdate remote info cache
        delete_site_transient('sabai_plugin_info');
        
        return $this;
    }

    public function logInfo($info)
    {
        error_log(sprintf('[sabai][INFO] %s', $info) . PHP_EOL);
        return $this;
    }

    public function logWarning($warning)
    {
        error_log(sprintf('[sabai][WARNING] %s', $warning) . PHP_EOL);
        return $this;
    }

    public function logError($error)
    {
        error_log(sprintf('[sabai][ERROR] %s', $error) . PHP_EOL);
        return $this;
    }
    
    public function getLocale()
    {
        return get_locale();
    }
    
    public function isLanguageRTL()
    {
        // for some reason the is_rtl() function is not available on certain installs
        return function_exists('is_rtl') ? is_rtl() : false;
    }
    
    public function htmlize($text, array $allowedTags = null)
    {
        if (!strlen($text)) {
            return '';
        }
        if (isset($allowedTags)) {
            $text = wp_kses($text, $allowedTags);
            $text = balanceTags($text, true);
            if (in_array('a', $allowedTags)) {
                $text = make_clickable($text);
            }
            $text = wptexturize($text);
            $text = convert_chars($text);
            if (in_array('p', $allowedTags) && in_array('br', $allowedTags)) {
                $text = wpautop($text);
                $text = shortcode_unautop($text);
            }
            return $text;
        }
        
        $text = wp_kses_post($text);
        $text = balanceTags($text, true);
        if (isset($GLOBALS['wp_embed'])) {
            $text = $GLOBALS['wp_embed']->autoembed($text);
        }
        $text = make_clickable($text);
        $text = wptexturize($text);
        $text = convert_smilies($text);
        $text = convert_chars($text);
        $text = wpautop($text);
        return shortcode_unautop($text);
    }
    
    public function getCookieDomain()
    {
        return COOKIE_DOMAIN;
    }
    
    public function getCookiePath()
    {
        return COOKIEPATH;
    }
    
    public function setOption($name, $value)
    {
        $this->_updateOption('sabai_sabai_' . strtolower($name), $value);
        return $this;
    }
    
    public function getOption($name, $default = null)
    {
        return get_option('sabai_sabai_' . strtolower($name), $default);
    }
    
    public function deleteOption($name)
    {
        delete_option('sabai_sabai_' . strtolower($name));
    }
    
    public function getStartOfWeek()
    {
        return ($ret = (int)get_option('start_of_week')) ? $ret : 7;
    }
    
    public function getGMTOffset()
    {
        return (int)get_option('gmt_offset');
    }
    
    public function getCustomAssetsDir()
    {
        return WP_CONTENT_DIR  . '/sabai/assets';
    }
    
    public function getCustomAssetsDirUrl()
    {    
        return WP_CONTENT_URL . '/sabai/assets';
    }
    
    public function getUserProfileHtml($userId)
    {
        return nl2br(get_the_author_meta('description', $userId));
    }
    
    public function resizeImage($imgPath, $destPath, $width, $height, $crop = false)
    {
        $img = wp_get_image_editor($imgPath);
        if (!is_wp_error($img)) {
            $img->resize($width, $height, $crop);
            $img->save($destPath);
        }
    }
    
    public function getHumanTimeDiff($from)
    {
        $diff = human_time_diff($from, time());
        return $from - time() > 0 ? $diff : sprintf(__('%s ago', 'sabai'), $diff);
    }

    public function run()
    {
        $this->_addFiltersAndActions();
        // Allow custom functions
        @include_once WP_CONTENT_DIR  . '/sabai/functions.php';

        if (is_admin()) {
            $this->_admin();

            return;
        }

        if (false === $this->_main()) {
            // Not a Sabai page
            // Add sabai stylesheet
            add_action('wp_print_styles', array($this, 'onWpPrintStylesAction'));
        }
    }

    private function _main()
    {
        // Do not run Sabai if not using the pretty permalinks
        if (!$permalink_structure = get_option('permalink_structure')) return false;

        $sabai_page_requested = false;
        $site_path = parse_url(home_url(), PHP_URL_PATH);
        // Some sites have *.php in their custom permalink URL
        if ($pos = strpos($permalink_structure, '.php')) {
            $site_path .= substr($permalink_structure, 0, $pos + 4);
        }
        $request_path = $this->_getRequestPath();
        if ($site_path) {
            if (0 !== strpos($request_path, $site_path))  {// is a valid path requested?
                // Sabai page was not requested, so clear flash messages that might
                // have been saved in the session during previous requests.
                if ($user_id = get_current_user_id()) {
                    $this->deleteSessionVar('system_flash', $user_id);
                }
                return false;
            }
            $page_request_path = substr($request_path, strlen($site_path)); // get the requested page path
        } else {
            $page_request_path = $request_path;
        }
        if ($page_request_path === '/') {
            // Check if the Sabai page is configured as the front page
            if (($front_page_id = get_option('page_on_front'))
                && ($sabai_page_slug = $this->_isSabaiPageId($front_page_id))
            ) {
                $sabai_page_requested = '';
                $page_request_path = '/' . $sabai_page_slug;
            }
        } else {
            // Normal page has been requested, check if it is a Sabai page
            $is_permalink = null;
            if (false !== $sabai_page_slug = $this->_isSabaiPagePath($page_request_path, $is_permalink)) {
                $sabai_page_requested = '/' . $sabai_page_slug;
                if (strpos(trim($page_request_path, '/'), '/')) {
                    // Do not redirect if not top page
                    remove_action('template_redirect', 'redirect_canonical');
                }
            }
        }
        if (false === $sabai_page_requested) {
            // Sabai page was not requested, so clear flash messages that might
            // have been saved in the session during previous requests.
            if ($user_id = get_current_user_id()) {
                $this->deleteSessionVar('system_flash', $user_id);
            }
            return false;
        }

        $fix_request_uri = $use_wp_page = false;
        if (empty($is_permalink)) {
            $fix_request_uri = true;
        } else {
            $offset = strlen($sabai_page_requested) + 1;
            if ($last_slash_pos = @strpos($page_request_path, '/', $offset)) {
                $fix_request_uri = true;
                $post_name = substr($page_request_path, $offset, $last_slash_pos - $offset);
                $sabai_page_requested .= '/' . $post_name;
            }
        }
        if ($fix_request_uri) {
            // Prepare REQUEST_URI for WordPress core to fetch page
            $_SERVER['ORIG_REQUEST_URI'] = $_SERVER['REQUEST_URI']; // save original
            // http_build_query does urlencode, so need a little adjustment for RFC1738 compat
            $_SERVER['REQUEST_URI'] = sprintf('%s%s/?%s', $site_path, $sabai_page_requested, strtr(http_build_query($_GET), array('%7E' => '~', '+' => '%20')));
            $_SERVER['PATH_INFO'] = '';
        }
        
        $this->_mainRoute = $page_request_path;
        
        // Add filter to make sure request parameters are not included in WP query vars
        add_filter('request', array($this, 'onRequestFilter'));
        // Add action method to run Sabai
        add_action('wp', array($this, 'onWpAction'), 1);
        
        // Removes rel="next" links
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
        // Stops 301 rediretion loop
        remove_filter('template_redirect', 'redirect_canonical');
        add_filter('redirect_canonical', '__return_false', 99999);

        // Do not redirect using 404 Redirection plugin
        remove_action('wp', 'redirect_all_404s', 1);
        
        if (class_exists('BuddyPress', false)) {
            add_filter('sabai_system_user_profile_links', array($this, 'onSabaiSystemUserProfileLinksFilter'), 10, 2);
        }
    }

    private function _isSabaiPageId($id)
    {
        $page_slugs = $this->getSabaiOption('page_slugs', array());
        if (is_array($page_slugs[2])
            && ($slug = array_search($id, $page_slugs[2]))
        ) {
            return $slug;
        }
        return false;
    }

    private function _isSabaiPagePath($path, &$isPermalink = null)
    {
        $slug = trim($path, '/');
        if (strpos($slug, 'sabai') === 0) {
            return 'sabai';
        }
        if (!$slug) {
            return false;
        }
        if ($slugs = $this->getSabaiOption('slugs')) {
            $_slug = $slug;
            do {
                if (isset($slugs['slug'][$_slug])) {
                    $isPermalink = $slugs['slug'][$_slug]['is_permalink'];
                    add_action('pre_get_posts', array($this, 'onPreGetPostsAction'));
                    return $_slug;
                }
            } while (($_slug = dirname($_slug)) && strlen($_slug) > 1);
        }
        if ($page_slugs = $this->getSabaiOption('page_slugs')) {
            do {
                if (isset($page_slugs[0][$slug])
                    && !empty($page_slugs[2][$slug]) // make sure page ID is set so that page exists
                    && 'publish' === get_post_status($page_slugs[2][$slug]) // make sure the page is published
                ) {
                    return $page_slugs[0][$slug];
                }
            } while (($slug = dirname($slug)) && strlen($slug) > 1);
        }
        
        return false;
    }
    
    public function getSlug($addon, $name)
    {
        if (($page_slugs = $this->getSabaiOption('page_slugs'))
            && isset($page_slugs[1][$addon][$name])
        ) {
            return $page_slugs[1][$addon][$name];
        }
        return parent::getSlug($addon, $name);
    }
    
    public function getTitle($addon, $name)
    {
        if (($page_slugs = $this->getSabaiOption('page_slugs'))
            && ($slug = @$page_slugs[1][$addon][$name])
            && ($page_id = @$page_slugs[2][$slug])
            && ($post = get_post($page_id))
        ) {
            return $post->post_title;
        }
        return parent::getTitle($addon, $name);
    }

    private function _getRequestPath()
    {
        if (strpos($_SERVER['REQUEST_URI'], 'wp-load.php')) {
            return @parse_url(wp_get_referer(), PHP_URL_PATH);
        }

        if (strpos($_SERVER['SCRIPT_FILENAME'], 'index.php')) {
            $ret = $_SERVER['REQUEST_URI'];

            // Remove the GET variables from the request URI if any
            if (false !== $pos = strpos($ret, '?')) {
                $ret = substr($ret, 0, $pos);
            }

            return $ret;
        }

        return false;
    }

    public function onRequestFilter($queryVars)
    {
        // Prevent WP from using Sabai request parameters to determine the requested page
        return array_diff_key($queryVars, $_REQUEST);
    }

    public function onWpAction()
    {
        $GLOBALS['wp_query']->is_404 = false;
        $this->_mainContent = $this->_runMain($this->getSabai(), $this->_mainRoute);
        if (!defined('SABAI_WORDPRESS_DISABLE_REMOVE_FILTERS')
            || !SABAI_WORDPRESS_DISABLE_REMOVE_FILTERS
        ) {
            remove_all_filters('the_content');
        }
        add_filter('the_content', array($this, 'onTheContentFilter'), 99999);
    }

    private function _runMain(Sabai $sabai, $route)
    {
        // Create request
        $request = new Sabai_Request(true, true); // force stripslashes since WP adds them vis wp_magic_quotes() if magic_quotes_gpc is off
        // Create context
        $context = new Sabai_Context();
        $context->setRequest($request)->addTemplateDir($this->getAssetsDir() . '/templates');
        // Run Sabai
        try {
            $response = $sabai->run(new Sabai_MainRoutingController(), $context, $route);
            if (!$context->isView()
                || $request->isAjax()
                || $context->getContainer() !== '#sabai-content'
                || $context->getContentType() !== 'html'
            ) {
                if (!$request->isAjax()
                    && $context->isError()
                    && $context->getErrorType() == 404
                    && ($template_404 = get_404_template())
                ) {
                    $response->sendStatusHeader(404);
                    include $template_404;
                } else {
                    if ($request->isAjax() === '#sabai-content') { // whole sabai content requested?
                        $response->setInlineLayoutHtmlTemplate(dirname(__FILE__) . '/WordPress/layout/main_inline.html.php');
                    }
                    $response->send($context); // no HTML content or layout
                }
                exit;
            }
            if ($context->isView()
                && !isset($GLOBALS['sabai_entity']) // body class already added by WordPress add-on?
            ) {
                add_filter('body_class', array($this, 'onBodyClassFilter'));
            }
            ob_start();
            $layout_dir = dirname(__FILE__) . '/WordPress/layout';
            $response->setInlineLayoutHtmlTemplate($layout_dir . '/main_inline.html.php')
                ->setLayoutHtmlTemplate($layout_dir . '/main.html.php')
                ->send($context);
            return ob_get_clean();
        } catch (Exception $e) {
            if (is_super_admin() || (defined('WP_DEBUG') && WP_DEBUG)) {
                // Print trace
                return sprintf('<p>%s</p><p><pre>%s</pre></p>', Sabai::h($e->getMessage()), Sabai::h($e->getTraceAsString()));
            }

            return sprintf('<p>%s</p>', 'An error occurred while processing the request. Please contact the administrator of the website for further information.');
        }
    }

    private function _admin()
    {        
        // Do not include WP admin header automatically if sabai admin page
        if (isset($_REQUEST['page']) && is_string($_REQUEST['page']) && 0 === strpos($_REQUEST['page'], 'sabai')) {
            $_GET['noheader'] = 1;
            // Get valid WP admin page for the requested Sabai route
            if (isset($_REQUEST['q'])
                && ($admin_menus = $this->_getAdminMenus())
            ) {
                $path = $_REQUEST['q'];
                do {
                    if (isset($admin_menus[$path])) {
                        $_GET['page'] = $_REQUEST['page'] = 'sabai' . $path;
                        break;
                    } 
                } while (DIRECTORY_SEPARATOR !== $path = dirname($path));
            }
        }

        add_action('admin_print_styles', array($this, 'onAdminPrintStylesAction'));
        add_action('admin_menu', array($this, 'onAdminMenuAction'));
        add_action('admin_notices', array($this, 'onAdminNoticesAction'));
        add_action('post_updated', array($this, 'onPostUpdatedAction'), 10, 3);
        add_action('activated_plugin', array($this, 'onActivatedPluginAction'));
        add_action('deactivated_plugin', array($this, 'onDeactivatedPluginAction'));
        add_filter('extra_plugin_headers', array($this, 'onExtraPluginHeadersFilter'));
        add_action('after_plugin_row_sabai/sabai.php', array($this, 'onAfterPluginRowSabaiAction'), 11, 3);
        
        if (function_exists( 'members_get_capabilities')) {
            add_filter('members_get_capabilities', array($this, 'onMembersGetCapabilitiesFilter'));
        }
        
        add_action('wp_ajax_sabai', array($this, 'onWpAjaxAction'));
    }
    
    public function onBodyClassFilter($classes)
    {
        $classes[] = 'sabai' . str_replace('/', '-', $GLOBALS['sabai_route']);
        return $classes;
    }
    
    public function onExtraPluginHeadersFilter($headers)
    {
        $headers[] = 'Sabai License Package';
        return $headers;
    }

    public function onMembersGetCapabilitiesFilter($caps)
    {
        // Add sabai capablities to the list of capabilities in the Members plugin 
        $caps[] = 'manage_sabai_content';
        $caps[] = 'manage_sabai';
        return $caps;
    }
    
    public function getSabaiPlugins($activeOnly = true, $force = false)
    {
        if ($force || false === $sabai_plugin_names = $this->getCache($id = 'wordpress_sabai_plugins_' . (int)$activeOnly)) {
            $sabai_plugin_names = array();
            if ($sabai_plugin_dirs = glob(self::getPluginsDir() . '/sabai-*', GLOB_ONLYDIR | GLOB_NOSORT)) {
                if (!function_exists('is_plugin_active')) {
                    require ABSPATH . 'wp-admin/includes/plugin.php';
                }
                foreach ($sabai_plugin_dirs as $sabai_plugin_dir) {
                    $sabai_plugin_name = basename($sabai_plugin_dir);
                    if (!$activeOnly || is_plugin_active($sabai_plugin_name . '/' . $sabai_plugin_name . '.php')) {
                        $sabai_plugin_names[$sabai_plugin_name] = array(
                            'css' => file_exists($this->getAssetsDir($sabai_plugin_name) . '/css/main.min.css'),
                            'css_rtl' => file_exists($this->getAssetsDir($sabai_plugin_name) . '/css/main-rtl.min.css'),
                            'mo' => file_exists($sabai_plugin_dir . '/languages/' . $sabai_plugin_name . '.pot'),
                        );
                    }
                }
            }
            $this->setCache($sabai_plugin_names, $id);
        }
        return $sabai_plugin_names;
    }

    public function onAdminMenuAction()
    {
        add_options_page('Sabai', 'Sabai', current_user_can(SABAI_WORDPRESS_ADMIN_CAPABILITY) ? SABAI_WORDPRESS_ADMIN_CAPABILITY : 'manage_sabai', 'sabai/settings', array($this, 'runAdmin'));
        
        // Allow super users and users with the manage_sabai_content capability to access sabai content administration pages
        if (current_user_can(SABAI_WORDPRESS_ADMIN_CAPABILITY)) {
            $capability = SABAI_WORDPRESS_ADMIN_CAPABILITY;
        } elseif (current_user_can('manage_sabai_content')) {
            $capability = 'manage_sabai_content';
        } else {
            return;
        }
        
        $admin_menus = $this->_getAdminMenus();
        if ($admin_menus && !empty($admin_menus['/']['children'])) {
            $position = 26.583425;
            foreach ($admin_menus['/']['children'] as $route) {
                if (!isset($admin_menus[$route])) continue;
                
                if (in_array($route, array('/settings'))) {
                    continue;
                }
            
                $menu = $admin_menus[$route];
                $label = isset($menu['label']) ? $menu['label'] : $menu['title'];
                
                $position += 0.000001;
                add_menu_page($label, $label, $capability, 'sabai' . $route, array($this, 'runAdmin'), isset($menu['icon']) ? 'div' : '', (string)$position);
                add_submenu_page('sabai' . $route, $menu['title'], $menu['title'], $capability, 'sabai' . $route, array($this, 'runAdmin'));
            
                if (empty($menu['children'])) {
                    continue;
                }
                
                foreach ($menu['children'] as $_route) {
                    if (!isset($admin_menus[$_route])) continue;
                
                    $_menu = $admin_menus[$_route];
                    add_submenu_page('sabai' . $route, $_menu['title'], $_menu['title'], $capability, 'sabai' . $_route, array($this, 'runAdmin'));
                }
            }
        }
        
        $admin_post_type_menus = $this->getSabaiOption('admin_post_type_menus');
        if ($admin_post_type_menus) {
            foreach ($admin_post_type_menus as $route => $menu) {
                add_submenu_page('edit.php?post_type=' . $menu['post_type'], $menu['title'], $menu['title'], $capability, 'sabai' . $route, array($this, 'runPostTypeAdmin'));
            }
        }
    }
    
    public function onAdminNoticesAction()
    {
        if (get_option('permalink_structure') === '') {
            echo '<div class="updated fade"><p>' . __('You must <a href="options-permalink.php">change your permalink structure</a> for Sabai plugins to work properly.', 'sabai') . '</p></div>';
        }
        
        if (current_user_can(SABAI_WORDPRESS_ADMIN_CAPABILITY)) {
            if (false === $updates = $this->getCache('wordpress_addon_updates')) {
                $updates = array();
                $installed_addons = $this->getSabai()->getInstalledAddons();
                $local_addons = $this->getSabai()->getLocalAddons();
                foreach ($installed_addons as $addon_name => $installed_addon) {
                    if (isset($local_addons[$addon_name])
                        && version_compare($installed_addon['version'], $local_addons[$addon_name]['version'], '<')
                    ) {
                        $updates[] = $addon_name;
                    }
                }
                // This can be cached for as long as we want since the cache is cleared upon both plugin install/update/uninstall and add-on upgrade/uninstall operations.
                $this->setCache($updates, 'wordpress_addon_updates');
            }
            if (!empty($updates)) {
                echo '<div class="updated fade"><p>' . sprintf(__('There are %1$d upgradable Sabai add-on(s). Please go to the <a href="%2$s">add-on listing section</a> and upgrade all add-ons.', 'sabai'), count($updates), admin_url('admin.php?page=sabai/settings#sabai-system-admin-addons-installed')) . '</p></div>';
            }
        }
    }
    
    public function onPostUpdatedAction($postId, $postAfter, $postBefore)
    {
        // Has slug been changed?
        if ($postAfter->post_name === $postBefore->post_name) return;
        
        // Is it a Sabai page?
        $page_slugs = $this->getSabaiOption('page_slugs', array());
        if (!is_array($page_slugs[2])
            || (!$slug = array_search($postId, $page_slugs[2]))
        ) {
            return;
        }
        
        // Update Sabai page slug data
        $new_slug = $postAfter->post_name;
        unset($page_slugs[0][$slug], $page_slugs[2][$slug]);
        $page_slugs[0][$new_slug] = $new_slug;
        $page_slugs[2][$new_slug] = $postId;
        foreach (array_keys($page_slugs[1]) as $addon_name) {
            if (isset($page_slugs[1][$addon_name][$slug])) {
                unset($page_slugs[1][$addon_name][$slug]);
                $page_slugs[1][$addon_name][$new_slug] = $new_slug;
                break;
            }
        }
        $this->updateSabaiOption('page_slugs', $page_slugs);
    }
    
    public function onActivatedPluginAction($plugin)
    {
        if (strpos($plugin, 'sabai-') === 0) $this->clearCache();
    }
    
    public function onDeactivatedPluginAction($plugin)
    {
        if (strpos($plugin, 'sabai-') === 0) $this->clearCache();
    }
    
    public function onAfterPluginRowSabaiAction($pluginFile, $pluginData, $status)
    {
        if (is_plugin_active($pluginFile) && !empty($pluginData['update'])) {
            $wp_list_table = _get_list_table('WP_Plugins_List_Table');
            echo '<tr class="plugin-update-tr"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div style="">';
            _e('IMPORTANT! Always bulk update all Sabai plugins to the same version to prevent errors.', 'sabai');
            echo '</div></td></tr>';
        }
    }
    
    public function onDeleteSiteTransientUpdatePluginsAction()
    {
        // Delete addon update info
        $this->deleteCache('wordpress_addon_updates');
  
        // Delete update info of plugins that have been updated
        if ($info = get_site_transient('sabai_plugin_info')) {
            $save = false;
            $plugin_names = $this->getSabaiPlugins(false);
            foreach (array_keys($plugin_names) as $plugin_name) {
                if (!isset($info[$plugin_name])) {
                    continue;
                }
                if (false === $info[$plugin_name]
                    || version_compare(self::getPluginData($plugin_name, 'Version', '0.0.0'), $info[$plugin_name]->version, '>=')
                ) {
                    unset($info[$plugin_name]);
                    $save = true;
                }
            }
            if ($save) {
                set_site_transient('sabai_plugin_info', $info, 7200); // cache for 2 hours
            }
        }
    }
    
    public function onWpAjaxAction()
    {
        $this->_runAdmin($this->getSabai());
    }
    
    public function runPostTypeAdmin()
    {
        $route = null;
        $sabai = $this->getSabai();
        if (($slash_pos = strpos($_REQUEST['page'], '/'))
            && ($route = substr($_REQUEST['page'], $slash_pos))
        ) {
            $admin_url = admin_url() . 'edit.php?post_type=' . rawurlencode($_REQUEST['post_type']) . '&page=sabai' . rawurlencode($route) . '&';
            $sabai->setScriptUrl($admin_url, 'admin' . $route)->setCurrentScriptName('admin' . $route);
        } else {
            $sabai->setCurrentScriptName('admin');
        }
        $this->_runAdmin($sabai, $route);
    }

    public function runAdmin()
    {
        $route = null;
        $sabai = $this->getSabai();
        if (($slash_pos = strpos($_REQUEST['page'], '/'))
            && ($route = substr($_REQUEST['page'], $slash_pos))
        ) {
            if (in_array($route, array('/settings'))) {
                $admin_url = admin_url() . 'options-general.php?page=sabai' . $route . '&';
            } else {
                $admin_url = admin_url() . 'admin.php?page=sabai' . $route . '&';
            }
            $sabai->setScriptUrl($admin_url, 'admin' . $route)->setCurrentScriptName('admin' . $route);
        } else {
            $sabai->setCurrentScriptName('admin');
        }
        $this->_runAdmin($sabai, $route);
    }
    
    protected function _runAdmin($sabai, $route = null)
    {
        // Create request
        $request = new Sabai_Platform_WordPress_AdminRequest(true, true);
        // Set the default route if none requested
        if (empty($_REQUEST[$sabai->getRouteParam()]) && isset($route)) {
            $request->set($sabai->getRouteParam(), $route);
        }
        
        $context = new Sabai_Context();
        $context->setRequest($request)->addTemplateDir($this->getAssetsDir() . '/templates');

        try {
            // Run Sabai         
            $response = $sabai->run(new Sabai_AdminRoutingController(), $context);
            if (!$context->isView()
                || $request->isAjax()
                || $context->getContainer() !== '#sabai-content'
                || $context->getContentType() !== 'html'
            ) {
                if ($context->isView()
                    && $context->getContainer() === '#sabai-content'
                ) {
                    $response->setInlineLayoutHtmlTemplate(dirname(__FILE__) . '/WordPress/layout/admin_inline.html.php');
                }
                $response->send($context);
            } else {
                $layout_dir = dirname(__FILE__) . '/WordPress/layout';
                $response->setInlineLayoutHtmlTemplate($layout_dir . '/admin_inline.html.php')
                    ->setLayoutHtmlTemplate($layout_dir . '/admin.html.php')
                    ->send($context);
            }
        } catch (Exception $e) {
            // Display error message
            require_once ABSPATH . 'wp-admin/admin-header.php';
            printf('<p>%s</p><p><pre>%s</pre></p>', $e->getMessage(), $e->getTraceAsString());
            require_once ABSPATH . 'wp-admin/admin-footer.php';
        }
        exit;
    }

    public function getSabai($loadAddons = true, $reload = false)
    {
        require_once 'Sabai.php';

        if (!SabaiFramework::started()) {
            SabaiFramework::start(get_option('blog_charset', 'UTF-8'), get_locale(), !SABAI_WORDPRESS_SESSION_TRANSIENT);
            Sabai::$p = defined('SABAI_WORDPRESS_PAGE_PARAM') ? SABAI_WORDPRESS_PAGE_PARAM : 'p';
        }
        if (!$sabai = Sabai::exists()) {
            $sabai = $this->_createSabai();
        }
        if ($loadAddons) {
            if ($reload) {
                $sabai->reloadAddons();
            } else {
                $sabai->loadAddons();
            }
        }

        return $sabai;
    }

    private function _createSabai()
    {
        $permalink_structure = get_option('permalink_structure');
        if ($pos = strpos($permalink_structure, '.php')) {
            $main_url = home_url() . substr($permalink_structure, 0, $pos + 4);
        } else {
            $main_url = home_url();
        }       
        $sabai = Sabai::create($this)
            ->setScriptUrl($main_url, 'main')
            ->setScriptUrl(admin_url() . 'admin.php?page=sabai' . '&', 'admin')
            ->setModRewriteFormat(rtrim($main_url, '/') . '%1$s', 'main');
        if (is_ssl()) {
            $sabai->isSsl(true);
        }
        // Init helpers
        $helper_broker = $sabai->getHelperBroker();
        $helper_broker->setHelper('Date', array($this, 'dateHelper'))
            ->setHelper('Time', array($this, 'timeHelper'))
            ->setHelper('DateTime', array($this, 'dateTimeHelper'))
            ->setHelper('LoadJquery', array($this, 'loadJqueryHelper'))
            ->setHelper('LoadJqueryUi', array($this, 'loadJqueryUiHelper'))
            ->setHelper('LoadJqueryMasonry', array($this, 'loadJqueryMasonryHelper'))
            ->setHelper('LoadJson2', array($this, 'loadJson2Helper'))
            ->setHelper('Token', array($this, 'tokenHelper'))
            ->setHelper('TokenValidate', array($this, 'tokenValidateHelper'))
            ->setHelper('GravatarUrl', array($this, 'gravatarUrlHelper'))
            ->setHelper('Slugify', array($this, 'slugifyHelper'))
            ->setHelper('Summarize', array($this, 'summarizeHelper'))
            ->setHelper('Action', array(new Sabai_Platform_WordPress_ActionHelper(), 'help'))
            ->setHelper('Filter', array(new Sabai_Platform_WordPress_FilterHelper(), 'help'));

        if (class_exists('BuddyPress', false)) {
            $helper_broker->setHelper('UserIdentityUrl', array($this, 'bpUserIdentityUrlHelper'));
        }
        
        // Allow custom helpers
        if ($helpers = apply_filters('sabai_helpers', array())) {
            foreach ($helpers as $name => $callback) {
                $helper_broker->setHelper($name, $callback);
            }
        }

        return $sabai;
    }

    private function _addFiltersAndActions()
    {
        add_action('init', array($this, 'onInitAction'), 3); // earlier than most plugins
        add_action('admin_init', array($this, 'onAdminInitAction'));
        add_action('widgets_init', array($this, 'onWidgetsInitAction'));
        add_filter('robots_txt', array($this, 'onRobotsTxt'));
        add_action('wp_login', array($this, 'onWpLoginAction'));
        add_action('wp_logout', array($this, 'onWpLogoutAction'));
        add_action('admin_head-widgets.php', array($this, 'onAdminHeadWidgetsPhpAction'));
        add_action('sabai_cron', array($this, 'onSabaiCron'));
        add_action('wp_before_admin_bar_render', array($this, 'onWpBeforeAdminBarRenderAction'));
        add_action('delete_user', array($this, 'onDeleteUserAction'));
        add_action('deleted_user', array($this, 'onDeletedUserAction'));
        // Disable WP comments
        add_filter('comments_template', array($this, 'onCommentsTemplateFilter'));
        
        add_shortcode('sabai', array($this, 'onSabaiShortcode'));

        // Always append the redirect_to parameter to login/register/lostpassword url generated by the Theme My Login plugin
        add_filter('tml_action_url', array($this, 'onTmlActionUrlFilter'), 10, 2);
        // Always load jquery in the header
        add_action('wp_enqueue_scripts', array($this, 'onWpEnqueueScripts'), 1);
    }
    
    public function onWpEnqueueScripts()
    {
        wp_enqueue_script('jquery', false, array(), false, false);
    }
    
    public function onTmlActionUrlFilter($url, $action)
    {
        if (isset($_REQUEST['redirect_to']) && in_array($action, array('login', 'register', 'lostpassword'))) {
            return add_query_arg('redirect_to', $_REQUEST['redirect_to'], $url);
        }
        return $url;
    }

    public function onSabaiShortcode($atts, $content, $tag)
    {
        if (!isset($atts['path']) || !strlen($atts['path']) || false === $this->_isSabaiPagePath($atts['path'])) {
            return;
        }
        $query = array();
        if (isset($atts['query'])) {
            if (is_array($atts['query'])) {
                $query = $atts['query'];
            } else {
                parse_str($atts['query'], $query);
            }
        }
        $query['return'] = !empty($atts['return']);
        return $this->shortcode($atts['path'], $query);
    }

    public function onSabaiCron()
    {
        $this->getSabai()->Cron();
    }
    
    public function onInitAction()
    {
        load_plugin_textdomain('sabai', false, 'sabai/languages/');
        $sabai_plugins = $this->getSabaiPlugins();
        foreach ($sabai_plugins as $plugin_name => $sabai_plugin) {
            if ($sabai_plugin['mo']) {
                load_plugin_textdomain($plugin_name, false, $plugin_name . '/languages/');
            }
        }
        
        $this->getSabai()->Action('sabai_platform_wordpress_init');
    }
    
    public function onAdminInitAction()
    {
        // Run autoupdater
        if (current_user_can(SABAI_WORDPRESS_ADMIN_CAPABILITY)) {            
            // Enable update notification if any license key is set
            $license_keys = $this->getSabaiOption('license_keys', array());
            if (!empty($license_keys)) {
                $plugin_names = $this->getSabaiPlugins(false);
                foreach ($license_keys as $sabai_plugin_name => $license_key) {
                    if (!isset($plugin_names[$sabai_plugin_name])
                        || !strlen((string)@$license_key['value'])
                    ) {
                        continue;
                    }
                    $remote_args = array(
                        'license_type' => $license_key['type'],
                        'license_key' => $license_key['value'],
                    );
                    require_once 'Sabai/Platform/WordPress/AutoUpdater.php';
                    new Sabai_Platform_WordPress_AutoUpdater($sabai_plugin_name, $remote_args);
                }
                // Use whichever license key to fetch info of the Sabai package
                if (isset($remote_args)) {
                    new Sabai_Platform_WordPress_AutoUpdater('sabai', $remote_args);
                }
            }
            
            // Add a hook to clear cache of upgradable add-ons when plugins are installed/updated/uninstalled
            add_action('delete_site_transient_update_plugins', array($this, 'onDeleteSiteTransientUpdatePluginsAction'));
        }
        // Invoke add-ons
        $this->getSabai()->Action('sabai_platform_wordpress_admin_init');
    }

    public function onWidgetsInitAction()
    {
        try {
            if (!$widgets = $this->getSabai()->Widgets_Widgets()) {
                return;
            }
        } catch (Exception $e) {
            $this->logError($e);
            return;
        }
        
        require_once 'Sabai/Platform/WordPress/Widget.php';
        // Fetch all sabai widgets and then convert each to a wp widget
        foreach ($widgets as $widget_name => $widget) {
            $class = sprintf('Sabai_Platform_WordPress_Widget_Sabai_%s', $widget_name);
            if (class_exists($class, false)) {
                continue;
            }
            eval(sprintf('
class %s extends Sabai_Platform_WordPress_Widget {
    public function __construct() {
        parent::__construct("%s", "%s", "%s", "%s");
    }
}
                ', $class, $widget['addon'], $widget_name, $widget['title'], $widget['summary']));
            register_widget($class);
        }
    }
    
    public function onRobotsTxt($output)
    {
        $public = get_option('blog_public');
        if ('0' != $public) {
            $site_url = site_url();
            $path = (string)parse_url($site_url, PHP_URL_PATH);
            // Disallow content files
            $output .= "\nDisallow: $path/wp-content/sabai/";
            // Allow thumbnail files
            $output .= "\nAllow: $path/wp-content/sabai/File/thumbnails/"; // allow thumbnail files
            // Disallow library files
            $output .= "\nDisallow: $path/wp-content/plugins/sabai/";
            $plugin_names = $this->getSabaiPlugins(false);
            foreach (array_keys($plugin_names) as $plugin) {
                $output .= "\nDisallow: $path/wp-content/plugins/$plugin/";
            }
            // Add linkt to sitemap index
            $output .= "\nSitemap: $site_url/sabai-sitemap-index.xml";
        }
        return $output;
    }
    
    public function onWpLoginAction()
    {
        if (!SABAI_WORDPRESS_SESSION_TRANSIENT) {
            SabaiFramework::startSession();
            session_regenerate_id(true); // to prevent session fixation attack
        }
        $this->deleteSessionVar('system_permissions');
    }
        
    public function onWpLogoutAction()
    {
        if (!SABAI_WORDPRESS_SESSION_TRANSIENT && session_id()) {
            $_SESSION = array();
            session_destroy();
        }
    }

    public function onWpPrintStylesAction()
    {
        if (!apply_filters('sabai_wordpress_print_styles', true)) return;
        
        wp_enqueue_style('sabai', $this->getAssetsUrl() . '/css/main.min.css', array(), self::VERSION);
        wp_enqueue_style('sabai-font-awesome', $this->getAssetsUrl() . '/css/font-awesome.min.css', array(), self::VERSION);
        $sabai_plugins = $this->getSabaiPlugins();
        foreach ($sabai_plugins as $plugin_name => $sabai_plugin) {
            if ($sabai_plugin['css']) {
                wp_enqueue_style($plugin_name, $this->getAssetsUrl($plugin_name) . '/css/main.min.css', array('sabai'), self::VERSION);
            }
        }
        if ($this->isLanguageRTL()) {
            wp_enqueue_style('sabai-rtl', $this->getAssetsUrl() . '/css/main-rtl.min.css', array('sabai'), self::VERSION);
            foreach ($sabai_plugins as $plugin_name => $sabai_plugin) {
                if ($sabai_plugin['css_rtl']) {
                    wp_enqueue_style($plugin_name . '-rtl', $this->getAssetsUrl($plugin_name) . '/css/main-rtl.min.css', array($plugin_name), self::VERSION);
                }
            }
        }
        // Add custom stylesheet by theme
        if (file_exists($this->getCustomAssetsDir() . '/style.css')) {
            wp_enqueue_style('sabai-wordpress', $this->getCustomAssetsDirUrl() . '/style.css', array('sabai'), self::VERSION);
        }
    }
    
    public function onAdminPrintStylesAction()
    {
        echo '<style type="text/css">';
        $admin_menus = $this->_getAdminMenus();
        if ($admin_menus && !empty($admin_menus['/']['children'])) {
            foreach ($admin_menus['/']['children'] as $route) {
                if (!isset($admin_menus[$route])) continue;
                
                $menu = $admin_menus[$route];
                if (!isset($menu['icon'])) continue;
                
                printf('
#toplevel_page_sabai%1$s .wp-menu-image {
    background:transparent url(%2$s) no-repeat center center !important;
}
#toplevel_page_sabai%1$s:hover .wp-menu-image, #toplevel_page_sabai%1$s.wp-has-current-submenu .wp-menu-image {
    background-image:url(%3$s) !important;
}',
                    str_replace('/', '-', $route),
                    $this->getSiteUrl() . '/' . (isset($menu['icon_dark']) ? $menu['icon_dark'] : $menu['icon']),
                    $this->getSiteUrl() . '/' . $menu['icon']
                );
            }
        }
        echo '</style>';
    }

    public function getSabaiOption($key, $default = null)
    {
        return get_option($this->_getSabaiOptionName($key), $default);
    }

    public function updateSabaiOption($key, $value, $new = false)
    {
        return $this->_updateOption($this->_getSabaiOptionName($key), $value, $new);
    }

    private function _getSabaiOptionName($key)
    {
        return 'sabai_sabai_' . $key;
    }
    
    protected function _updateOption($key, $value, $new = false)
    {
        if ($new) {
            delete_option($key);
            return add_option($key, $value);
        }
        return false === get_option($key) ? add_option($key, $value) : update_option($key, $value);
    }

    /* Begin WordPress filter methods */

    public function onTheContentFilter($content)
    {
        if ($GLOBALS['wp_query']->get_queried_object_id() == $GLOBALS['post']->ID
            && (in_the_loop() || (defined('SABAI_WORDPRESS_SKIP_IN_THE_LOOP_CHECK') && SABAI_WORDPRESS_SKIP_IN_THE_LOOP_CHECK))
        ) {
            if (defined('SABAI_WORDPRESS_FILTER_CONTENT_ONCE')
                && SABAI_WORDPRESS_FILTER_CONTENT_ONCE
            ) {
                if (!$this->_isContentFiltered) {
                    $content = false !== strpos($content, '%sabai%') ? str_replace('%sabai%', $this->_mainContent, $content) : $this->_mainContent;
                    $this->_isContentFiltered = true;
                }
            } else { 
                $content = false !== strpos($content, '%sabai%') ? str_replace('%sabai%', $this->_mainContent, $content) : $this->_mainContent;
            }
        }
        return $content;
    }

    public function onDeleteUserAction($userId)
    {
        // Cache user data here so that we can reference it after the user actually being deleted
        $identity = $this->getSabai()->UserIdentity($userId);
        if (!$identity->isAnonymous()) $this->_userToBeDeleted[$userId] = $identity;
    }

    public function onDeletedUserAction($userId)
    {
        if (!isset($this->_userToBeDeleted[$userId])) return;

        // Notify that a user account has been dleted
        $this->getSabai()->Action('sabai_user_deleted', array($this->_userToBeDeleted[$userId]));

        unset($this->_userToBeDeleted[$userId]);
    }

    public function onCommentsTemplateFilter($file)
    {
        // disable comments on Sabai WordPress pages by including a blank template file
        return isset($this->_mainContent) ? dirname(__FILE__) . '/WordPress/comments_template.php' : $file;
    }

    public function onAdminHeadWidgetsPhpAction()
    {
        echo '<style type="text/css">.sabai-form-field {margin:0 0 1em;}</style>';
    }

    public function onWpBeforeAdminBarRenderAction()
    {
        if (is_user_logged_in()) {        
            if ($menus = $this->getSabai()->getAddon('System')->getUserMenus()) {
                $this->_addAdminBarNodes($menus);
            }
        }
    }
    
    private function _addAdminBarNodes(array $nodes, $parent = '', $realParent = '')
    {        
        foreach ((array)@$nodes[$parent] as $node_id => $node) {
            $GLOBALS['wp_admin_bar']->add_menu(array(
                'id' => 'sabai-' . $node_id,
                'parent' => $realParent,
                'title' => (string)@$node['title'],
                'href' => (string)@$node['url'],
                'meta' => (array)@$node['meta'])
            );
            if (!empty($nodes[$node_id])) {
                $this->_addAdminBarNodes($nodes, $node_id, 'sabai-' . $node_id);
            }
        }
    }

    /* End WordPress filter methods */

    public function getTemplate()
    {
        if (!isset($this->_template)) {
            require_once 'Sabai/Platform/WordPress/Template.php';
            $this->_template = new Sabai_Platform_WordPress_Template($this);
        }

        return $this->_template;
    }

    public function dateHelper(Sabai $application, $timestamp)
    {
        return date_i18n(get_option('date_format'), $timestamp + get_option('gmt_offset') * 3600);
    }

    public function timeHelper(Sabai $application, $timestamp)
    {
        return date_i18n(get_option('time_format'), $timestamp + get_option('gmt_offset') * 3600);
    }

    public function dateTimeHelper(Sabai $application, $timestamp)
    {
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp + get_option('gmt_offset') * 3600);
    }
    
    public function loadJqueryHelper(Sabai $application){}
    
    public function loadJqueryUiHelper(Sabai $application, array $components = null, $loadCss = false)
    {
        if (!$this->_jqueryUiLoaded) {
            wp_enqueue_script('jquery-ui-core', false, array(), null, false);
            $this->_jqueryUiLoaded = true;
        }
        if ($loadCss && !$this->_jqueryUiCssLoaded) {
            wp_enqueue_style('jquery-ui', apply_filters('sabai_jquery_ui_theme_url', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $GLOBALS['wp_scripts']->registered['jquery-ui-core']->ver . '/themes/ui-lightness/jquery-ui.min.css'));
            $this->_jqueryUiCssLoaded = true;
        }
        foreach ($components as $component) {
            $script = strpos($component, 'effects') === 0 ? 'jquery-' . $component : 'jquery-ui-' . $component;
            wp_enqueue_script($script, false, array(), null, false);
        }
    }
    
    public function loadJqueryMasonryHelper(Sabai $application)
    {
        wp_enqueue_script('jquery-masonry', false, array(), null, false);
    }
    
    public function loadJson2Helper(Sabai $application)
    {
        wp_enqueue_script('json2', false, array(), null, false);
    }
        
    public function tokenHelper(Sabai $application, $tokenId, $tokenLifetime = 1800, $reobtainable = false)
    {
        return wp_create_nonce('sabai_' . $tokenId);
    }
        
    public function tokenValidateHelper(Sabai $application, $tokenValue, $tokenId, $reuseable)
    {
        $result = wp_verify_nonce($tokenValue, 'sabai_' . $tokenId);
        // 1 indicates that the nonce has been generated in the past 12 hours or less.
        // 2 indicates that the nonce was generated between 12 and 24 hours ago.
        // Use 1 for enhanced security
        return $result === 1;
    }
            
    public function gravatarUrlHelper(Sabai $application, $email, $size = 96, $default = 'mm', $rating = null, $secure = false)
    {
        if (preg_match('/src=("|\')(.*?)("|\')/i', get_avatar($email, $size, $default), $matches)) {
            return str_replace('&amp;', '&', $matches[2]);
        }
    }
    
    public function getSiteToSystemTime($timestamp)
    {
        // mktime should return UTC in WP
        return intval($timestamp - get_option('gmt_offset') * 3600);
    }
    
    public function getSystemToSiteTime($timestamp)
    {
        return intval($timestamp + get_option('gmt_offset') * 3600);
    }
    
    public function slugifyHelper(Sabai $application, $string)
    {
        return rawurldecode(sanitize_title($string));
    }
    
    public function summarizeHelper(Sabai $application, $text, $length = 0, $trimmarker = '...')
    {
        if (!strlen($text)) return '';
        
        $text = strip_shortcodes(strip_tags(strtr($text, array("\r" => '', "\n" => ' '))));
        
        return empty($length) ? $text : mb_strimwidth($text, 0, $length, $trimmarker);
    }
    
    public function bpUserIdentityUrlHelper(Sabai $application, SabaiFramework_User_Identity $user)
    {
        return bp_core_get_user_domain($user->id);
    }
    
    public function shortcode($path, array $attributes = array())
    {
        if (!isset($this->_shortcode)) {
            $this->_shortcode = new Sabai_Platform_WordPress_Shortcode();
        }
        return $this->_shortcode->render($path, $attributes);
    }
    
    public function activate()
    {
        require_once 'Sabai/Platform/WordPress/include/activate.php';
        sabai_platform_wordpress_activate($this);
    }
    
    public function activatePlugin($plugin, array $addons)
    {
        require_once 'Sabai/Platform/WordPress/include/activate.php';
        sabai_platform_wordpress_activate_plugin($this, $plugin, $addons);
    }
    
    public function createPage($slug, $title, ArrayObject $log = null)
    {
        require_once 'Sabai/Platform/WordPress/include/create_page.php';
        return sabai_platform_wordpress_create_page($this, $slug, $title, $log);
    }
      
    public static function getPluginsDir()
    {
        return dirname(dirname(dirname(dirname(dirname(__FILE__)))));
    }
    
    public static function getPluginData($pluginName, $key = null, $default = false)
    {        
        $plugin_file = self::getPluginsDir() . '/' . $pluginName . '/' . $pluginName . '.php';
        if (!file_exists($plugin_file)) {
            return $default;
        }
        // Fetch plugin data for version comparison
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin_data = get_plugin_data($plugin_file, false, false);
        
        return isset($key) ? (isset($plugin_data[$key]) ? $plugin_data[$key] : $default) : $plugin_data;
    }
    
    public function onPreGetPostsAction($query)
    {
        if (!$query->is_main_query()) return;
        
        if (is_post_type_archive()) {
            $bundle_name = $query->get('post_type');
        } elseif (is_tax()) {
            if ($object = $query->get_queried_object()) {
                $bundle_name = $object->taxonomy;
            }
        }
        
        if (!isset($bundle_name) || 
            (!$bundle = $this->getSabai()->Entity_Bundle($bundle_name))
        ) {
            return;
        }
        
        $path = $bundle->path;
        do {
            if ($page = get_page_by_path(trim($path, '/'))) {
                break;
            }
         } while (($path = dirname($path)) && $path !== '/');
        
        if (!$page || $page->post_status !== 'publish') return;
            
        $query->set('page_id', $page->ID);
        $query->set('post_type', 'page');
        $query->is_archive = $query->is_post_type_archive = $query->is_tax = false;
        $query->is_page = $query->is_singular = true;
        unset($query->queried_object);
    }
    
    private function _getAdminMenus()
    {
        if (!$menus = $this->getCache('wordpress_admin_menus')) {
            $menus = $this->getSabai()->getAddon('System')->getAdminMenus();
            $this->setCache($menus, 'wordpress_admin_menus');
        }
        return $menus;
    }
    
    public function unzip($from, $to)
    {
        global $wp_filesystem;
        if (!isset($wp_filesystem)) WP_Filesystem();
        
        if (true !== $result = unzip_file($from, $to)) {
            throw new Sabai_RuntimeException($result->get_error_message());
        }
    }
    
    public function updateDatabase($schema, $previousSchema = null)
    {
        require_once 'Sabai/Platform/WordPress/include/update_database.php';
        sabai_platform_wordpress_update_database($this, $schema, $previousSchema);
    }
    
    public function addHeader($head, $handle, $index = 10)
    {
        $this->_htmlHead[$index][$handle] = $head;
    }
    
    public function clearHeader()
    {
        $this->_htmlHead = $this->_css = array();
    }
    
    public function getHeaderHtml()
    {
        $html = array();
        if (!empty($this->_htmlHead)) {        
            ksort($this->_htmlHead);
            foreach (array_keys($this->_htmlHead) as $i) {
                foreach (array_keys($this->_htmlHead[$i]) as $j) {
                    $html[] = $this->_htmlHead[$i][$j];
                }
            }
        }
        if (!empty($this->_css)) {
            $html = array('<style type="text/css">');
            ksort($this->_css);
            foreach (array_keys($this->_css) as $i) {
                foreach (array_keys($this->_css[$i]) as $j) {
                    $html[] = $this->_css[$i][$j];
                }       
            }
            $html[] = '</style>';
        }
        
        return empty($html) ? '' : implode(PHP_EOL, $html);
    }
    
    public function clearJs()
    {
        $this->_js = array();
        $this->_jsIndex = 0;
    }

    public function addJsFile($url, $handle, $dependency = null)
    {
        wp_enqueue_script($handle, $url, (array)$dependency, null, false);
    }

    public function addJs($js, $handle, $onDomReady = true, $index = null)
    {
        if (!isset($index)) {
            $index = ++$this->_jsIndex;
        }
        if (!$onDomReady) {
            $this->_js[0][$index][$handle] = $js;
        } else {
            $this->_js[1][$index][$handle] = $js;
        }
    }

    public function addCssFile($url, $handle, $dependency = null, $media = 'screen')
    {
        wp_enqueue_style($handle, $url, (array)$dependency, false, $media);
    }
    
    public function addCss($css, $handle, $index = null)
    {
        if (!isset($index)) {
            $index = ++$this->_cssIndex;
        }
        $this->_css[$index][$handle] = $css;
    }
    
    public function getJsHtml()
    {
        if (empty($this->_js)) return '';
        
        $html = array('<script type="text/javascript">');
        if (!empty($this->_js[0])) {
            ksort($this->_js[0]);
            foreach (array_keys($this->_js[0]) as $i) {
                foreach (array_keys($this->_js[0][$i]) as $j) {
                    $html[] = $this->_js[0][$i][$j];
                }
            }            
        }
        if (!empty($this->_js[1])) {
            ksort($this->_js[1]);
            $html[] = 'jQuery(document).ready(function($) {';
            foreach (array_keys($this->_js[1]) as $i) {
                foreach (array_keys($this->_js[1][$i]) as $j) {
                    $html[] = $this->_js[1][$i][$j];
                }
            }
            $html[] = '});';
        }
        $html[] = '</script>';
        return implode(PHP_EOL, $html);
    }
    
    public function setFlash(array $flash)
    {
        foreach ($flash as $_flash) {
            $this->addJs(sprintf('SABAI.flash("%s", "%s");', $_flash['msg'], $_flash['level']), 'sabai-flash');
        }
    }
    
    public function isAdmin()
    {
        return is_admin();
    }
    
    public function onSabaiSystemUserProfileLinksFilter($links, $identity)
    {
        $links[bp_core_get_user_domain($identity->id)] = array('label' => __('View profile', 'sabai'));
        return $links;
    }
}
