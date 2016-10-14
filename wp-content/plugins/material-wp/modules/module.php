<?php
/**
 * Our Module Framework
 * This is the class meant to be extended for our other modules
 */
class MaterialWPModule {
  
  // Keeps our framework
  public $f;
  
  /**
   * Loads the framework instance into our variable
   * @param object $framework Intance of ParadoxFramework
   */
  public function __construct($framework) {
    
    // Loads the framework
    $this->f = $framework;
    
    // Enqueue scripts and styles
    add_action('admin_enqueue_scripts', array($this, 'adminEnqueue'));
    
    // Admin Panels
    // Fix of Daniel: Priority 11
    add_action('init', array($this, 'adminPanels'), 11);
    
    // Run the module
    $this->run();
    
  } // end construct;
  
  /**
   * Runs the module functionality
   */
  public function run() {} // end run;
  
  /**
   * Enqueues Admin Script and Styles
   */
  public function adminEnqueue() {} // end adminEnqueue;
  
  /**
   * Handles admin panels
   */
  public function adminPanels() {} // end adminPanels;
  
} // end MaterialWPModule;