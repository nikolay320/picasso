<?php

// Todo: prevent direct access

// Prevents class redeclaration
if (!class_exists('ParadoxFramework')) :

/**
 * Here we start our awesome framework
 * Paradox aims to provide us with all the tools we need to rapidly get a plugin project of the drawing board.
 */
class ParadoxFramework {
  
  /** @property string $id Unique indentifier of our plugin. This is used to save options and much more */
  public $id;
  
  /** @property string $td Carries the text domain to be used accross the application. */
  public $td;
  
  /** @property array $configs This variable carries our plugin initialization settings */
  protected $config;
  
  /** @property object $options Carries the Titan Framework Object */
  public $options;
  
  /** @property array $pages Save our pages name so we can brand it later if desired */
  public $pages = array();
  
  /** @property array $errors We will use this variable to store our errors to enqueue the notices */
  public $errors = array();
  
  /**
   * Initializes the plugin adding all important hooks and generating important instances of our framework.
   */
  public final function __construct($config) {
    
    // We need to check if frontendCSS is passed, if not, add it
    $config['frontendCSS'] = isset($config['frontendCSS']) ? $config['frontendCSS'] : false;
    
    // Load the configs passed by the constructor into our variable $config
    $this->config = $config;
    
    // We load the $id variable with the slug to make it easier to use the plugin slug
    $this->id = $this->config['slug'];
    
    // For the exact same reason, we load our text domain
    $this->td = $this->config['textDomain'];
    
    // We also need to load this own file for references using the framework itself
    $this->config['frameworkFile'] = __FILE__;
    
    /**
     * Now we start to run our setting up functions
     */
    
    // First we need to setup our path and url variables, depending if this is a plugin or a theme
    $this->setupPath();
    
    // We need also to setup our core and so on based on the config array
    $this->setupCore();
    
    // We also need to reset our FILE var, because of the updater
    $this->config['file'] = $this->path($this->config['fullSlug'].".php");
    
    // Makes sure the plugin is defined before trying to use it
    if (!function_exists('is_plugin_active_for_network')) {
        require_once(ABSPATH.'/wp-admin/includes/plugin.php');
    }
    
    // Check if this is network enabled
    $this->config['multisite'] = is_plugin_active_for_network(plugin_basename($this->config['file']));
  

    // Now we have to deal with plugin initialization like textdomain, activation, deactivation events
    $this->setupEvents();
    
    // Load our scripts and styles
    $this->enqueueScriptsAndStyles();
    
    // Run on plugins loaded
    add_action('plugins_loaded', array($this, 'onPluginsLoaded'));
    
    
    // Enqueue our framework errors
    add_action('admin_notices', array($this, 'adminNotices'));
    
    // Enqueue the saving of our settings
    add_action('admin_init', array($this, 'importSettings'));
    
    
    // Finally: Run our Plugin
    add_action('init', array(&$this, 'Plugin'), 0);
    
  } // end construct;
  
  /**
   * SETUPING ENVIROMENT AND FRAMEWORK
   * This section is extreamly important to the framework itself, it handles loading of modules,
   * enqueuing of scripts, config change via filters and etc.
   */
  
  /**
   * This method handles the path and url setups, used on our utility belt methods
   * @return null;
   */
  protected final function setupPath() {
    
    // We need to check for the type of this project
    if ($this->config['type'] === 'theme') {
      
      // Set paths and so on relative to themes directory
      $this->config['path'] = get_stylesheet_directory().'/';
      $this->config['url']  = get_stylesheet_directory_uri().'/';
      
      // Do the same thing to our framework
      $this->config['frameworkPath'] = get_stylesheet_directory().'/paradox/';
      $this->config['frameworkURL']  = get_stylesheet_directory_uri().'/paradox/';
      
    } // end if;
    
    // If this is a plugin (or anything else, for that matter), setup:
    else {
      
      // Setup our Plugin path
      $this->config['path'] = plugin_dir_path($this->config['file']);
      $this->config['url']  = plugin_dir_url($this->config['file']);
      
       // Same for framework
      $this->config['frameworkPath'] = plugin_dir_path($this->config['frameworkFile']);
      $this->config['frameworkURL']  = plugin_dir_url($this->config['frameworkFile']);
      
    } // end else;
    
  } // end setupPath;
  
  /**
   * This function adds the plugin/theme related events
   * Currently it is very hard to this using in themes
   */
  protected final function setupCore() {
    
    // If titan framework is enabled
    if ($this->config['options'] === true) $this->addTitanFramework();
    
    // If less preprocessor is enabled
    if ($this->config['less'] === true) $this->addLess();
    
    // If auto updates are enabled
    if ($this->config['autoUpdate'] === true) $this->addAutoUpdate();
    
    // If in debugging mode
    if ($this->config['debug'] === true) $this->debug();
    
    // If branding is enabled
    if ($this->config['branding'] === true) add_action('admin_head', array(&$this, 'addBranding'));
    
    // Add our export action
    add_action('wp_ajax_'.$this->slugfy('export-settings'), array($this, 'exportSettings'));
    
  } // end setupCore;
  
  /**
   * This function adds the plugin/theme related events
   * Currently it is very hard to this using in themes
   */
  protected final function setupEvents() {
    
    // Check for a setup option to see if this is the first run
    $firstRun = get_option($this->slugfy('first_run'));
    
    // If it is the first run, we need to call our method
    if ($firstRun === false) {
      
      // Enqueue our custom method to init hook
      add_action('init', array(&$this, 'onFirstRun'));
      
      // Update the first run option in the database
      $update = update_option($this->slugfy('first_run'), true);
      
    } // end if;
    
    // Enqueue our on activation method
    add_action('activate_'.$this->config['fullSlug'].'/'.$this->config['fullSlug'].'.php', array(&$this, 'onActivation'));
    
    // Enqueue our deactivation hook
    add_action('deactivate_'.$this->config['fullSlug'].'/'.$this->config['fullSlug'].'.php', array(&$this, 'onDeactivation'));
    
    // Enqueue our uninstall hook
    add_action('uninstall'.$this->config['fullSlug'].'/'.$this->config['fullSlug'].'.php', array(&$this, 'onUninstall'));
    
  } // end setupEvents;
  
  public function enqueueScriptsAndStyles() {
    
    // Adds the framework default Backend scripts and styles
    add_action('admin_enqueue_scripts', array($this, 'defaultAdminScripts'));
    add_action('admin_enqueue_scripts', array($this, 'defaultAdminStyles'));
    
    // Adds Backend scripts and styles
    add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));
    add_action('admin_enqueue_scripts', array($this, 'enqueueAdminStyles'));
    
    // Adds Frontend scripts and styles
    add_action('wp_enqueue_scripts', array($this, 'enqueueFrontendScripts'));
    add_action('wp_enqueue_scripts', array($this, 'enqueueFrontendStyles'));
    
    // Adds Login scripts ans styles
    add_action('login_enqueue_scripts', array($this, 'enqueueLoginScripts'));
    add_action('login_enqueue_scripts', array($this, 'enqueueLoginStyles'));
    
  } // end enqueueScriptsAndStyles
  
  /**
   * UTILITY BELT
   * This is one of the single most important parts of the framework, a utility belt that makes much easier to 
   * get assets, paths, urls and etc.
   */
  
  /**
   * Return absolute path to some plugin subdirectory
   * @return string Absolute path
   */
  public function path($dir, $relativeToFramework = false) {
    return $relativeToFramework ? $this->config['frameworkPath'].$dir : $this->config['path'].$dir;
  }
  
  /**
   * Return url to some plugin subdirectory
   * @return string Url to passed path
   */
  public function url($dir, $relativeToFramework = false) {
    return $relativeToFramework ? $this->config['frameworkURL'].$dir : $this->config['url'].$dir;
  }
  
  /**
   * Return full URL relative to some file in assets
   * @return string Full URL to path
   */
  public function getAsset($asset, $assetsDir = 'img', $relativeToFramework = false) {
    return $this->url("assets/$assetsDir/$asset", $relativeToFramework);
  }
  
  /**
   * Render Views
   * @param string $view View to be rendered.
   * @param Array $vars Variables to be made available on the view escope, via extract().
   */
  public function render($view, $vars = false, $relativeToFramework = false) {
    // Make passed variables available
    if (is_array($vars)) extract($vars);
    // Load our view
    include $this->path("views/$view.php", $relativeToFramework);
  }
  
  /**
   * This function return 'slugfied' options terms to be used as options ids
   * @param string $term Returns a string based on the term and this plugin slug.
   * @return string
   */
  public function slugfy($term) {
    return $this->id.'_'.$term;
  } // end slugfy;
  
  /**
   * Used to get info directly retrieved from the plugin header
   */
  public function getPluginInfo($info) {
    $plugin_info = get_plugin_data($this->path($this->config['fullSlug'].'.php'));
    return $plugin_info[$info];
  } // end getPluginInfo;
  
  /**
   * If we are in development environment, we can add some debugging features to this method
   * @return null;
   */
  protected function debug() {} // end debug;
  
  /**
   * This function loads Titan Framework
   * @return null;
   */
  protected function addTitanFramework() {
    
    // Require the library
    require_once $this->path('inc/titan-framework/titan-framework-embedder.php', true);
    
    // Crete the options engine
    add_action('after_setup_theme', array($this, 'attachTitanFramework'));
    
  } // end addTitanFramework;
  
  /**
   * Attaches Titan Framework to the Paradox
   */
  public function attachTitanFramework() {
    
    // If Titan Framework does not exists, quit
    if (!class_exists('ParadoxTitanFramework')) return;
    

    
    // Instantialize our less handler in this object
    $this->options = ParadoxTitanFramework::getInstance($this->id, array(
      'css'       => $this->config['frontendCSS'],
      'multisite' => $this->config['multisite'],
    ));
    
    // Call out adminPages function
    $this->adminPages();
  
  } // end attachTitanFramework;
  
  /**
   * This function simply loads less library, if nothing else required it before,
   * and set the less property to contain it.
   * @return null;
   */
  protected function addLess() {
    
    // Require the library
    if (!class_exists('lessc')) require_once $this->path('inc/less/lessc.inc.php', true);
    
    // Instantialize our less handler in this object
    $this->less = new lessc;
    
  } // end addLess;
  
  /**
   * We enqueue our admin notices here, so we can be sure they will get displayed
   */
  public function adminNotices() {
    
    // We need to check if we have errors, otherwise we just exit the execution
    if (!empty($this->errors)) {
     
      // Now we render our error template
      $this->render('errors', false, true);
      
    } // end if;
    
  } // end adminNotices;
  
  /**
   * Return the attchment URL based on the multisite or single install
   * @param  int    $id ID of the attachment page
   * @return string URL of the attachment
   */
  public function getAttachmentURL($id) {
    
    // Global switched
    global $switched;
    
    // If this is a network, we need to switch to the main blog
    if ($this->config['multisite']) {
      
      // Change to main blog
      switch_to_blog(1);
      
      // Get the url
      $URL = wp_get_attachment_url($id);
      
      // restore
      restore_current_blog();
      
      // return the URL
      return $URL;
      
    } // end if;
    
    // If is not a multisite
    else {
      
      // Return the URL directly
      return wp_get_attachment_url($id);
      
    }

  } // end getAttachmentURL;
  
  /**
   * BRANDING AND STYLES
   * This section bellow adds the functions to whitelabel and brading the plugin.
   */
  
  /**
   * This function checks if each page visited is one of our own, so we can add the brandinf elemtens
   */
  public function addBranding() {
    
    // We need to get the current page id
    $currentPage = get_current_screen();
    
    // We need to loop our of our pages, so we can see if the current is one of them
    foreach ($this->pages as $page) {
      
      // If it is adds our display
      if (strpos($currentPage->id, $page) !== false) {
        
        // Body Classes
        add_filter('admin_body_class', array($this, 'addAdminBodyClasses'));
        
        // Adds Header, footer and Help
        add_action('admin_head', array(&$this, 'branding'), 999999);
        
      } // end if;
      
    } // end foreach;
    
  } // end addBranding;
  
  public function branding() {
    
    // Add Header
    add_action('admin_notices', array($this, 'addHeader'));
    
    // Add Footer
    add_action('in_admin_footer', array($this, 'addFooter'));
    
    // Our Support Tabs
    ob_start();
    $this->render('branding/tab-support', false, true);
    $tabSupport = ob_get_contents();
    ob_end_clean();
    
    // Our Rate Tabs
    ob_start();
    $this->render('branding/tab-rate', false, true);
    $tabRate = ob_get_contents();
    ob_end_clean();
    
    // Get our current screen to check for our slug
    $screen = get_current_screen();
    
    // Add my_help_tab if current screen is My Admin Page
    $screen->add_help_tab(array(
      'id'      => 'get-support',
      'title'   => __('Get Support', $this->td),
      'content' => $tabSupport,
    ));
    
    // Adds the rate tab
    $screen->add_help_tab(array(
      'id'      => 'rate-our-plugin',
      'title'   => __('Rate our Plugin', $this->td),
      'content' => $tabRate,
    ));
    
  } // end branding;
  
  /**
   * Create the plugins custom Footer
   * @return array The menu items of our custom footer
   */
  public function createFooterMenu() {
    
    // Menu carrier
     return array(
      // link => name
      $this->getPluginInfo('Name') . ' ' . $this->getPluginInfo('Version'),
      $this->getPluginInfo('PluginURI') => __('Get Support', $this->td),
    );
    
  } // end createFooterMenu;
  
  /**
   * Load the plugins custom Header
   */
  public function addHeader() {
    $this->render('branding/header', false, true);
  } // end addHeader;
  
  /**
   * Load the plugins custom Footer
   */
  public function addFooter() {
    $this->render('branding/footer', false, true);
  } // end addFooter;
  
  /**
   * Add our custom classes to the admin body tag
   */
  public function addAdminBodyClasses($classes) {
    return "$classes plugin-page-732 plugin-{$this->id}-732";
  } // end addAdminBodyClasses;
  
  /**
   * AUTOUPDATE 
   * This section handles our autoupdates and buyer checking.
   */
  
  /**
   *  This function adds our autoupdates functionality so your verifies buyers always get the newest version
   *  of the plugins and themes
   */
  protected function addAutoUpdate() {
    
    // We need to check the purchase code everytime the buyer adds a new one and saves
    add_action("tf_save_options_$this->id", array(&$this, 'checkBuyer'));
    
    // Enable our auto updates library
    add_action('init', array(&$this, 'enableAutoUpdates'));
    
  } // end addAutoUpdate;
  
  
  /**
   * This function adds the auto updates and other commom fields.
   * The panel should be passed in order add this options as a tab, if false, it will be added to
   * the settings menu.
   * @param mixed [$panel = false] The panel to add the tab or false
   */
  public function addAutoUpdateOptions($panel = false, $parent = 'options-general.php') {
    
    // We need to recheck if autoupdated is enable
    if ($this->config['autoUpdate'] === false) return;
    
    // It does not matter if this is a valid panel or a new create subpage, the args are the same
    $args = array(
      'id'         => $this->slugfy('activation'),
      'parent'     => $parent,
      'capability' => 'edit_users',
      
      'name' => sprintf(__('Activate %s', $this->td), $this->config['name']),
      'desc' => sprintf(__('Activate your %s to get automatic updates', $this->td), $this->config['type']),
    );
    
    // We need to check if the panel is passed, if is not, we should create our page
    $panel === false 
      ? $panel = $this->options->createAdminPanel($args) // Set our oanel as a child
      : $panel = $panel->createTab($args); // In the case that a valid panel is passed, we need to add a tab
    
    // Now that we defenivily have our activation panel, we need to add our options
    $panel->createOption(array(
      'id'          => 'purchase-code',
      'type'        => 'text',
      'name'        => __('Purchase Code', $this->td),
      'desc'        => sprintf(__('Paste the purchase code that you received when you bought our item. Don\'t know where to find it? %s', $this->td), '<a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Can-I-Find-my-Purchase-Code-" target="_blank">Read this.</a>'),
    ));
    
    // Get Our newsletter form
    ob_start();
    $this->render('newsletter-form', false, true);
    $newsletterForm = ob_get_contents();
    ob_end_clean();
    
    // Now we add our mailchimp signup form
    $panel->createOption(array(
      'id'          => 'newsletter-form',
      'type'        => 'custom',
      'name'        => __('Join the Club!', $this->td),
      'custom'      => $newsletterForm,
    ));
    
    // Save Button
    $panel->createOption(array(
      'type'        => 'save',
      'name'        => __('Save', $this->td),
    ));
    
    // We must not forget to add this to our pages, so we can brand it later
    $this->pages[] = $panel->settings['id'];
    
  } // end addAutoUpdateOptions;
  
  /**
   * Check if our user is validated or not.
   * Only runs on the save of new purchase code
   */
  public function checkBuyer() {
    
    // Check if user has a purchase code
    $purchaseCode = $this->options->getOption('purchase-code');
    
    // Check if this buyer is already chcked
    if (!empty($purchaseCode)) {
      
      // Check if we already validated his purchase code
      $isValid = $this->validatePurchaseCode($purchaseCode);
      
      // Save new check
      update_option($this->slugfy('is-checked'), $isValid);
      
    } // end if;
  
  } // end checkBuyer;
  
  /**
   * Validate buyer purchase code
   * @param  string  $purchaseCode The purchase code to be validate
   * @return boolean returns if the user is validate or not
   */
  public function validatePurchaseCode($purchaseCode) {
    
    // Your Username
    $username = '732';
     
    // Set API Key  
    $APIKey = 'qdwhqspw1gezf5r1vtvw3t4k1rvuymqa';
     
    // Open cURL channel
    $ch = curl_init();
      
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, "http://marketplace.envato.com/api/edge/". $username ."/". $APIKey ."/verify-purchase:". $purchaseCode .".json");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
      
    // Decode returned JSON
    $output = json_decode(curl_exec($ch), true);
     
    // Close Channel
    curl_close($ch);
    
    // Return output
    return isset($output['verify-purchase']['buyer']);
    
  } // end validatePurchaseCode;
  
  /**
   * Install AutoUpdates
   */
  public function enableAutoUpdates() {
    
    // This buyer is already checked
    $isChecked = get_option($this->slugfy('is-checked'));
    
    // Check if it's checked
    if ($isChecked) {
      
      // Requiring library
      require $this->path('inc/updater/plugin-update-checker.php', true);
      
      // Instantiating it
      $updateChecker = PucFactory::buildUpdateChecker(
        $this->config['updatesURL'],  // Metadata URL.
        $this->config['file'],        // Full path to the main plugin file.
        $this->config['fullSlug']     // Plugin slug. Usually it's the same as the name of the directory.
      );
      
    } // end if;
    
  } // end autoUpdates;
  
  /**
   * This function adds the export tab if enabled
   * The panel should be passed in order add this options as a tab, if false, it will be added to
   * the settings menu.
   * @param mixed [$panel = false] The panel to add the tab or false
   */
  public function addExportTab($panel = false, $parent = 'options-general.php') {
    
    // It does not matter if this is a valid panel or a new create subpage, the args are the same
    $args = array(
      'id'         => $this->slugfy('export'),
      'parent'     => $parent,
      //'capability' => 'edit_users',
      
      'name' => sprintf(__('Import / Export', $this->td), $this->config['name']),
      'desc' => sprintf(__('Import and Export the settings of %s.', $this->td), $this->config['name']),
    );
    
    // We need to check if the panel is passed, if is not, we should create our page
    $panel === false 
      ? $panel = $this->options->createAdminPanel($args) // Set our oanel as a child
      : $panel = $panel->createTab($args); // In the case that a valid panel is passed, we need to add a tab
    
    // We add our heading to separate the different options
    $panel->createOption(array(
      'name' => __('Export Settings', $this->id),
      'type' => 'heading',
    ));
    
    // Now we have to add our fields for export
    $panel->createOption(array(
      'name'   => __('Export to File', $this->td),
      'custom' => sprintf(__('<a style="font-style: normal;" class="button button-primary" href="%s">Export Settings</a><br><br>
      Click in the button above to get the export file containing all the settings for this plugin.', $this->td), admin_url("admin-ajax.php?action=".$this->slugfy('export-settings'))),
      'type'   => 'custom',
    ));
    
    // Options to export settings to a string
    $panel->createOption(array(
      'name'   => __('Copy the Settings', $this->td),
      'custom' => '<textarea class="large-text" rows="5" cols="50">'. $this->exportSettings(true) .'</textarea>',
      'type'   => 'custom',
    ));
    
    // The heading for our import part
    $panel->createOption(array(
      'name' => __('Import Settings', $this->id),
      'type' => 'heading',
    ));
    
    // And the field for import
    $panel->createOption(array(
      'name'   => __('Import your Settings', $this->td),
      'custom' => '<p class="description">'.__('Paste the contents of the JSON file you\'ve exported (or from the textarea you\'ve copied) to import your settings!', $this->td).'</p><textarea name="settings" class="large-text" rows="5" cols="50"></textarea><br><button name="paradox-import-settings" class="button button-primary">'. __('Import Settings', $this->td) .'</button><input name="namespace" value="'. $this->id .'" type="hidden">',
      'type'   => 'custom',
    ));
    
    // We must not forget to add this to our pages, so we can brand it later
    $this->pages[] = $panel->settings['id'];
    
  } // end addExportTab;
  
  /**
   * This function handles the import of our settings
   */
  public function importSettings() {
  
    // We need to check if this post is for us
    if (!isset($_POST['paradox-import-settings']) && !isset($_POST['namespace'])) return;
    
    // Check if we have any settings to add
    if (!isset($_POST['settings']) || empty($_POST['settings'])) {
      
      // Add to errors
      $this->errors[] = __('The settings field to import was empty.', $this->td);
      return;
      
    } // end if;
    
    // Get our settings now
    $json = $_POST['settings'];
    
    // Fix encoding
    $json = stripslashes($json);
    
    // Now we break it from JSON
    $settings = json_decode($json, true);
    
    // Check if json decode went wrong
    if (json_last_error() !== JSON_ERROR_NONE) {
      
      // Add error
      $this->errors[] = __('The JSON code you have inputed has syntax errors. The import could not be done.');
      return;
      
    } // end if
    
    // Now we need to serialize the array to be saved
    $settings = serialize($settings);
    
    // Finally we can save it
    update_option($_POST['namespace'].'_options', $settings);
  
  } // end importSettings;
  
  /**
   * This function handles the export of our settings
   */
  public function exportSettings($return = false) {

    // Get our namespace
    $namespace = $this->id;
    
    // Get all of our options
    $options = get_option($this->id.'_options');
    
    // Test if is not false and then unsealize
    if ($options !== false) $options = unserialize($options);
    
    // If we have custom images we need to change their ID number to actual URLs
    $customImgs = array('custom-bg', 'custom-logo');
    
    // For each one we have to test if they are numerals and them set the url
    foreach ($customImgs as $img) {
      
      // Check if options is set
      if (!isset($options[$img])) continue;
        
      // check if it is numeral. If it is, get URL
      if (is_numeric($options[$img])) $options[$img] = $this->getAttachmentURL($options[$img]);
      
    } // end foreach;
    
    // Generate our JSON code
    $json = json_encode($options);
    
    // if this is to be returned
    if ($return) {
      
      // return our json string
      return $json;
        
    } // end if;
    
    // Else, if we don not need to return
    else {
      
      // Set headers
      header('Content-disposition: attachment; filename='. $this->id .'-'. date('Y-m-d') .'.json');
      header('Content-type: application/json');
      
      // Echo our json info
      echo $json;

      // Kill the execution
      die();
      
    } // end else;
    
    
  } // end exportSettings;
  
  /**
   * EVENTS
   * The section bellow handles the events that may happen like activation, deactivation, uninstall and
   * first run
   */
  
  /**
   * Place code that will be run on the first Run of the plugin
   */
  public function onFirstRun() {} // end onFirstRun;
  
  /**
   * Place code that will be run on activation
   */
  public function onActivation() {} // end onActivation;
  
  /**
   * Run on plugins Loaded
   */
  public function onPluginsLoaded() {} // end onPluginsLoaded;
  
  /**
   * Place code that will be run on deactivation
   */
  public function onDeactivation() {} // end onDeactivation;
  
  /**
   * Place code that will be run on uninstall
   */
  public function onUninstall() {} // end onUninstall;
  
  /**
   * SCRIPTS AND STYLES
   * The section bellow handles the adding of scripts and css files to the different hooks WordPress offers
   * such as Admin, Frontend and Login. Calling anyone of these hooks on the child class you automaticaly 
   * add the scripts hooked to the respective hook.
   */

  /**
   * Enqueue and register our default Admin CSS files here.
   * IMPORTANT: SHould not be used by Plugin or theme specifics
   */
  public function defaultAdminStyles() {
    
    // Our corrections to the styles of titan framework
    wp_enqueue_style($this->slugfy('admin-styles'), $this->url('assets/css/paradox.min.css', true));
    
  } // end defaultAdminStyles;
  
  /**
   * Enqueue and register our Default Admin CSS files here.
   */
  public function defaultAdminScripts() {} // end defaultAdminScripts;
  
  /**
   * Enqueue and register Admin JavaScript files here.
   */
  public function enqueueAdminScripts() {} // end enqueueAdminScripts;
  
  /**
   * Enqueue and register Admin CSS files here.
   */
  public function enqueueAdminStyles() {} // end enqueueAdminStyles;
  
  /**
   * Enqueue and register Frontend JavaScript files here.
   */
  public function enqueueFrontendScripts() {} // end enqueueFrontendScripts;
  
  /**
   * Enqueue and register Frontend CSS files here.
   */
  public function enqueueFrontendStyles() {} // end enqueueFrontendStyles;
  
  /**
   * Enqueue and register Login JavaScript files here.
   */
  public function enqueueLoginScripts() {} // end enqueueLoginScripts;
  
  /**
   * Enqueue and register Login CSS files here.
   */
  public function enqueueLoginStyles() {} // end enqueueLoginStyles;
  
  /**
   * IMPORTANT METHODS
   * Set bellow are the must important methods of this framework. Without them, none would work.
   */
  
  /**
   * Here is where we create and manage our admin pages
   */
  public function adminPages() {} // end adminPages;
  
  /**
   * Place code for your plugin's functionality here.
   */
  public function Plugin() {} // end Plugin;

} // end Paradoxframework;

endif;