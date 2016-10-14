<?php
/**
 * Plugin Name: Material WP
 * Plugin URI: http://codecanyon.net/item/material-wp-material-design-dashboard-theme/12981098?ref=732
 * Text Domain: material-wp
 * Description: Bring Material Design to you WordPress Dashboard.
 * Version: 0.0.23
 * Author: Arindo Duque - 732
 * Author URI: http://weare732.com/
 * Copyright: Arindo Duque, seventhreetwo
 */

/**
 * Loads our incredibily awesome Paradox Framework, which we are going to use a lot.
 */
require 'paradox/paradox.php';

/**
 * Here starts our plugin.
 */
class MaterialWP extends ParadoxFramework {

  /**
   * EVENTS
   * The section bellow handles the events that may happen like activation, deactivation, uninstall and
   * first run
   */
  
  /**
   * Run on plugins Loaded
   */
  public function onPluginsLoaded() {
    load_plugin_textdomain($this->config['textDomain'], false, plugin_basename(dirname(__FILE__)).'/lang');
  } // end onPluginsLoaded;
  
  
  /**
   * Place code that will be run on activation
   */
  public function onActivation() {
    // Set flag to compile
    update_option($this->slugfy('activation-compiled'), false);
    // $this->compileAdminCSS();
  } // end onActivation;
  
  /**
   * SCRIPTS AND STYLES
   * The section bellow handles the adding of scripts and css files to the different hooks WordPress offers
   * such as Admin, Frontend and Login. Calling anyone of these hooks on the child class you automaticaly 
   * add the scripts hooked to the respective hook.
   */
  
  /**
   * Enqueue and register Admin JavaScript files here.
   */
  public function enqueueAdminScripts() {
    //global $wp_scripts;var_dump($wp_scripts);
    
    // Deregister common cause we need to make some changes to it.
    wp_dequeue_script('common');
    wp_deregister_script('common');
    
    // Reeneque our own custom
    wp_enqueue_script('common', $this->url('assets/js/common.min.js'), array('jquery', 'hoverIntent', 'utils'), false, 1);
    
    // Localize it
    wp_localize_script('common', 'commonL10n', array(
		'warnDelete' => __("You are about to permanently delete the selected items.\n  'Cancel' to stop, 'OK' to delete.", 'material-wp'),
		'dismiss'    => __('Dismiss this notice.', 'material-wp'),
	));
    
    // Enqueue base script
    wp_enqueue_script($this->id, $this->url('assets/js/scripts.min.js'), array('jquery'), false, false);
    
  } // end enqueueAdminScripts;
  
  /**
   * Enqueue and register Admin CSS files here.
   */
  public function enqueueAdminStyles() {
    
    // We enqueue our styles, customized for this version
    if (is_rtl()) wp_enqueue_style($this->id, $this->url('assets/css/material-wp-rtl.min.css'));
    else wp_enqueue_style($this->id, $this->url('assets/css/material-wp.min.css'));
    
  } // end enqueueAdminStyles;
  
  /**
   * Enqueue and register Frontend JavaScript files here.
   */
  public function enqueueFrontendScripts() {} // end enqueueFrontendScripts;
  
  /**
   * Enqueue and register Frontend CSS files here.
   */
  public function enqueueFrontendStyles() {
    
    // Only enqueue if user is logged
    if (!is_admin_bar_showing() || !$this->options->getOption('admin-bar-frontend')) return;
    
    // We enqueue our login-specific styles
    wp_enqueue_style($this->slugfy('admin-bar'), $this->url('assets/css/material-wp-admin-bar.css'));
    
    $css = "#wpadminbar {
      background-color: ".$this->options->getOption('primary-color')." !important;
    }";
    
    $css .= "html[class], html[lang] {
      margin-top: ".$this->options->getOption('adminbar-height')."px !important;
    }";
    
    // Print CSS
    printf('<style type="text/css">%s</style>', $css); 
    
  } // end enqueueFrontendStyles;
  
  /**
   * Enqueue and register Login JavaScript files here.
   */
  public function enqueueLoginScripts() {} // end enqueueLoginScripts;
  
  /**
   * Enqueue and register Login CSS files here.
   */
  public function enqueueLoginStyles() {
    
    // If it does not go to the backend, does even show
    if (!$this->options->getOption('login-styles')) return;
    
    // We enqueue our login-specific styles
    wp_enqueue_style($this->slugfy('login'), $this->url('assets/css/material-wp-login.min.css'));
    
  } // end enqueueLoginStyles;
  
  /**
   * IMPORTANT METHODS
   * Set bellow are the must important methods of this framework. Without them, none would work.
   */
  
  /**
   * Here is where we create and manage our admin pages
   */
  public function adminPages() {
    
    // Load admin options
    require "inc/settings.php";
        
    /**
     * EVEN MORE IMPORTANT: Our customizer in the login page goes here!
     */
    //include_once $this->path('modules/login-customizer/login-customizer.php');
    
    /**
     * IMPORTANT: We need to initialize our export functionality
     */
    if (method_exists($this, 'addExportTab')) $this->addExportTab($panel);
    
    /**
     * IMPORTANT: We need to initialize our activation page
     */
    $this->addAutoUpdateOptions($panel);
    
    // Add this to branding
    $this->pages[] = $this->slugfy('settings');
    
  } // end adminPages;
  
  
  /**
   * Place code for your plugin's functionality here.
   */
  public function Plugin() {
    
    // var_dump($this->getDynamicStylesFile('url'));
    // var_dump(get_option($this->slugfy('compiler')));
    
    // remove WP logo from adminbar
    add_action('wp_before_admin_bar_render', array($this, 'editAdminBar'));

    // adds our custom site logo
    add_action('admin_bar_menu', array($this, 'addLogo'), 0);
    
    // Adds plus button
    add_action('in_admin_header', array(&$this, 'addParallaxBlock'));
    
    // Clean Footer on the Left
    add_filter('admin_footer_text', array($this, 'clearFooter'), 99999);
    
    // Clean Footer on the Right
    add_filter('update_footer', array($this, 'clearFooter'), 99999);
    
    // Remove colorscheme selector
    remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker');
    
    // Every time the user change the options, we recompile the styles
    add_action("init", array($this, 'firstCompileAdminCSS'));
    add_action("tf_save_options_$this->id", array($this, 'compileAdminCSS'));
    add_action('activate_blog', array($this, 'compileAdminCSS'));
    
    // Add custom styles to our login page
    add_action('login_enqueue_scripts', array($this, 'loginInlineCSS'));
    
    // Compile and render our dinamic styles in login
    add_action('login_enqueue_scripts', array($this, 'adminDynamicCSS'));
    
    // Sent to the frontend as well
    add_action('wp_enqueue_scripts', array($this, 'adminDynamicCSS'));
    
    // Compile and render our dinamic styles
    add_action('admin_enqueue_scripts', array($this, 'adminDynamicCSS'));
    
    // Add our custom control Body Classes
    add_filter('admin_body_class', array($this, 'addControlAdminClasses'));
    
    // Load our Edit Menu Module
    add_action('init', array($this, 'editMenuModule'));
    
    // Hide Help and Screen Options Tab
    // @since 0.0.19
    add_action('admin_head', array($this, 'removeScreenTab'));
    add_filter('contextual_help', array($this, 'removeHelpTab'), 999, 3);
    
  } // end Plugin;
  
  /**
   * SPECIFIC METHODS CALLED BY PLUGIN
   * The methods bellow are exclusive to this plugin, and they are called by the Plugin method
   */
  
  /**
   * Removes the help tabs from WordPress
   * @param  array  $oldHelp  Old help tabs
   * @param  int    $screenId Screen ID
   * @param  object $screen   Screen object
   * @return array  Old help, to prevent issues
   */
  function removeHelpTab($oldHelp, $screenId, $screen) {
    
    // Check the option
    if (!$this->options->getOption('display-help-tab'))
      $screen->remove_help_tabs();
    
    // Return to prevent bugs
    return $oldHelp;
    
  } // end removeHelpTab;
  
  /**
   * Removes the screen options tab if the user wants to
   */
  public function removeScreenTab() {
    // Screen Options Tab
    if (!$this->options->getOption('display-screen-tab'))
      add_filter('screen_options_show_screen', '__return_false');
  } // end removeScreenTab;
  
  /**
   * Add a class or many to the body in the dashboard
   * @param  string $classes Classes already attached to the admin body class
   * @return string New body classes string
   */
  function addControlAdminClasses($classes) {
    
    // Our control classes carrier
    $controlClasses = array();
    
    // If the menu position is switched, add the control class "material-wp-menu-$position"
    $controlClasses[] = "material-wp-menu-".$this->options->getOption('menu-position');
    
    // Add class for opacity if the user have selected
    if ($this->options->getOption('parallax-options') == 'parallax') {
      $controlClasses[] = 'material-wp-no-opacity'; 
    }
    
    // Return the classes plus our own
    return "$classes ".implode(' ', $controlClasses);
    
  } // end addControlAdminClasses;
  
  /**
   * Check to see if the current page is the login/register page
   * Use this in conjunction with is_admin() to separate the front-end from the back-end of your theme
   * @return bool
   */
  public function isLoginPage() {
    return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
  }
  
  /**
   * Adds the inline CSS styles to the login page
   */
  public function loginInlineCSS() {
    // wp_enqueue_script('jquery');
    // If it does not go to the backend, does even show
    if ($this->isLoginPage() && !$this->options->getOption('login-styles')) return;
    include $this->path('inc/login-styles.php');
  }
  
  /**
   * Run the compiler for the first time for users
   */
  public function firstCompileAdminCSS() {
    
    // Get flag
    $compiled = get_option($this->slugfy('compiled'));
    $activateCompiled = get_option($this->slugfy('activation-compiled'));
    // var_dump($compiled);
    
    // If it never run, run the first time we can
    if ($compiled == false || $activateCompiled == false) $this->compileAdminCSS();
    
    // Update Option
    update_option($this->slugfy('compiled'), true);
    update_option($this->slugfy('activation-compiled'), true);
    
  } // end firstCompileAdminCSS;
  
  /**
   * Compiles the Admin Styles
   */
  public function compileAdminCSS($blogID = false) {
    
    // Full Compiled CSS
    $fullCSS = '';
    
    // Set sass instance

    require_once $this->path('inc/titan-framework/inc/scssphp/scss.inc.php', true);
    if (class_exists('titanscssc')) $sass = new titanscssc();

    // Get our styles
    ob_start();
    
    // We only need our dynamic styles in the backend
    if (is_admin() || $this->isLoginPage()) include $this->path('inc/dynamic-styles.php');
    
    // If we are in the frontend, we just enqueue if options says so
    if (is_admin_bar_showing() && $this->options->getOption('admin-bar-frontend')) {
      include $this->path('inc/wp-admin-bar.php'); 
    }
    
    // Put in a variable
    $styles = ob_get_clean();
    
    // We need to protect our code by handling exceptions
    try {
      
      // If is an object
      if (is_object($sass)) {
                  
        // Compile our code
        $compiledCSS = $sass->compile($styles);

        // Print styles
        // printf('<style type="text/css">%s</style>', $compiledCSS);
        $fullCSS .= $compiledCSS;
      
      } // end if;

    } catch (Exception $e) {
      
      // Add the error to our display
      $this->errors[] = __('Something in your Material WP options may be causing SCSS compiling errors. Please verify if all of your options have correct values (a color field, for example, can not contain a value different of a hex code (#fff))', 'material-wp');
      
    } // end catch;
    
    // If we are in the admin, compile also the custom code
    if (is_admin() || $this->isLoginPage()) {
      
      // We also need to add to this mixture our custom CSS field contents
      $customCSS = '   ' . $this->options->getOption('custom-css');
      // var_dump($customCSS);

      // We need to protect our code by handling exceptions
      try {

        // If is an object
        if (is_object($sass)) {

          // Compile our code
          $compiledCustomCSS = $sass->compile($customCSS);

          // Print styles
          // printf('<style type="text/css">%s</style>', $compiledCustomCSS);
          $fullCSS .= $compiledCustomCSS;
        
        } // end if;

      } catch (Exception $e) {
        
        // Add the error to our display
        $this->errors[] = __('Your custom CSS (with SCSS) code has some syntax error. Please verify.', 'material-wp');

      } // end catch;
      
    } // end if;
    
    /**
     * Now we need to save this in a file that can be cached
     */
    $file = $this->getDynamicStylesFile('path', $blogID);

    // if (file_exists($file)) {} // end if;
    
    // Write to the file
    $status = file_put_contents($file, $fullCSS);

    // Save the status
    update_option($this->slugfy('compiled-version'), uniqid(''));
    update_option($this->slugfy('compiler'), $status);
  
  } // end compileAdminCSS;
  
  /**
   * Gets the dynamic file path for saving the contents of dynamic content
   * @param  string [$type           = 'path'] Type of the return, path or url
   * @return string Path or URL for the file
   */
  public function getDynamicStylesFile($type = 'path', $blogID = false) {
    
    // Check for our special case
    if (is_multisite() && !$this->config['multisite']) {
      
      // Get the ID of the current blog
      $blogID = ($blogID) ? $blogID : get_current_blog_id();
      
      // Generate the filename
      $file = "assets/css/material-wp-$blogID-dynamic.min.css";
      
    } // end if;
    
    else {
      $file = 'assets/css/material-wp-dynamic.min.css';
    } // end else;
    
    // return the results
    return $type == 'path' ? $this->path($file) : $this->url($file);
      
  } // end getDynamicStylesFile;
  
  /**
   * Enqueue our dynamicly created styles
   */
  public function adminDynamicCSS() {
    
    // If it does not go to the backend, does even show
    if ($this->isLoginPage() && !$this->options->getOption('login-styles')) return;
    
    // if we are in the frontend and the user don't want to display that
    if (!is_admin() && !$this->options->getOption('admin-bar-frontend')) return;
    
    // Get a dynamicly created version
    $version = get_option($this->slugfy('compiled-version'));
    
    // Enqueue our dynamic scripts
    wp_enqueue_style($this->slugfy('dynamic'), $this->getDynamicStylesFile('url'), array(), $version);
    
  } // end adminDynamicCSS;
  
  /**
   * Add the edit menu functionality to the Admin Dashboard
   */
  public function editMenuModule() {
    
    // Only load things if this is enabled
    if (is_object($this->options) && $this->options->getOption('menu-reordering') == false) return;
        
    // Require model
    require_once $this->path('modules/module.php');
    
    // Get Menu edit module
    require_once $this->path('modules/menu-editing/menu-editing.php');
    
  }
  
  /**
   * Clear footer text
   */
  public function clearFooter($text) {
    return '';
  }
  
  /**
   * Adds Parallax Block
   */
  public function addParallaxBlock() {
    // We only add if it is not the VC Frontend
    if (isset($_GET['vc_action']) && $_GET['vc_action'] == 'vc_inline') return;
    // Include our SCSS Compiler class
    $this->render('parallax-block');
  }

  /*
   * Remove the WordPress Logo from the WordPress Admin Bar
   */
  public function editAdminBar() {
    
    // If it does not go to the backend, does even show
    if (!is_admin() && !$this->options->getOption('admin-bar-frontend')) return;
    
    // Get global var admin bar
    global $wp_admin_bar;
    
    // Remove undesired nodes
    $wp_admin_bar->remove_menu('my-account');
    $wp_admin_bar->remove_menu('wp-logo');
    
    // Updates
    $update_node = $wp_admin_bar->get_node('updates');
    
    // If Node Exists 
    if ($update_node) {
      
      // Add classes
      $update_node->meta['class'] = "force-mdi tooltiped tooltip-ajust";
      // Ajust title
      $update_node->title = str_replace('<span class="ab-icon"></span>', '<i class="mdi-notification-sync"></i>', $update_node->title);

      $updates = array(
        'id'     => $update_node->id,
        'title'  => $update_node->title,
        'href'   => $update_node->href,
        'parent' => $update_node->parent,
        'meta'   => $update_node->meta,
      );
      
      // Add Editted Updates
      $wp_admin_bar->add_node($updates);
      
    }
    
    // We need to check if this is network
    if (!is_network_admin() && $wp_admin_bar->get_node('comments')) {
      
      // Commnets
      $comments_node = $wp_admin_bar->get_node('comments');
      // Add classes
      $comments_node->meta['class'] = "force-mdi tooltiped tooltip-ajust";
      // Ajust title
      $comments_node->title = str_replace('<span class="ab-icon"></span>', '<i class="mdi-notification-sms"></i>', $comments_node->title);

      $comments = array(
        'id'     => $comments_node->id,
        'title'  => $comments_node->title,
        'href'   => $comments_node->href,
        'parent' => $comments_node->parent,
        'meta'   => $comments_node->meta,
      );
      
      // Editted commnets
      $wp_admin_bar->add_node($comments);
      
    } // end if;
    
    // help
    $help = array(
      'id'    => 'mwp-help',
      'title' => '<i class="mdi-action-help"></i>',
      'href'  => wp_logout_url(),
      'parent'=> 'top-secondary',
      'meta'  => array(
        'class' => "force-mdi tooltiped tooltip-ajust",
        'title' => __('Help', 'material-wp')
      )
    );
    
    // Settings
    $settings = array(
      'id'    => 'mwp-settings',
      'title' => '<i class="mdi-action-settings"></i>',
      'href'  => admin_url('options-general.php'),
      'parent'=> 'top-secondary',
      'meta'  => array(
        'class' => "force-mdi tooltiped tooltip-ajust",
        'title' => __('Settings', 'material-wp')
      )
    );
    
    // Logout
    $logout = array(
      'id'    => 'mwp-logout',
      'title' => '<i class="mdi-action-exit-to-app"></i>',
      'href'  => wp_logout_url(),
      'parent'=> 'top-secondary',
      'meta'  => array(
        'class' => "force-mdi tooltiped tooltip-ajust",
        'title' => __('Logout', 'material-wp')
      )
    );
    
    // Editted My Sites
    // $wp_admin_bar->add_node($mysite);
    
    // Add logout
    $wp_admin_bar->add_node($logout);
    // Add settings
    $wp_admin_bar->add_node($settings);
    // Add help
    // $wp_admin_bar->add_node($help);
  }

  /**
   * Add the custom logo based on user choice
   */
  public function addLogo() {
    
    // If it does not go to the backend, does even show
    if (!is_admin() && !$this->options->getOption('admin-bar-frontend')) return;
    
    // Get admin bar global
    global $wp_admin_bar;
    
    // Get Logo Image
    $logo = $this->options->getOption('custom-logo');
    
    // If has nothing, adds nothing
    if (empty($logo) || !$logo) return;
    
    // We need to check if logo is just an id
    $logo = is_numeric($logo) ? $this->getAttachmentURL($logo) : $logo;

    // Check if title is image or text
    $title = "<img class='material-wp-logo' src='". $logo ."'>";
    
    $args = array(
      'id'     => 'my-site-logo',
      'href'   => admin_url(),
      'title'  => $title,
      'meta'   => array(
        'class' => "custom-site-logo",
      )
    );
    
    // Finaly adds the block
    $wp_admin_bar->add_node($args);
    
  } // end addLogo;
  
} // end MaterialWP;

// Now we need to load our config file
//$config = include 'config.php';
$config = require(dirname(__FILE__).'/config.php');

/**
 * We execute our plugin, passing our config file
 */
$MaterialWP = new MaterialWP($config);
