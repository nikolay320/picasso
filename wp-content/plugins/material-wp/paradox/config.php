<?php

/**
 * This is a sample config file
 */
return array(
  
  /**
   * Plugin Slug
   * This is used in various place, like saving options and so on
   */
  'slug' => 'paradox',
  
  /**
   * Plugin Full Slug (optional)
   * This is used in various place, like in updates modules and so on.
   * Must be EXACTLY like the file and directory name
   */
  'fullSlug' => 'paradox',
  
  /**
   * Plugin Name
   * This is used in various place
   */
  'name' => 'Paradox',
  
  /**
   * Text Domain to be used in the plugin
   */
  'textDomain' => 'textDomain',
  
  /**
   * Is this a plugin or is this a theme?
   */
  'type' => 'plugin',
  
  /**
   * Lang directory
   */
  'lang' => 'lang',
  
  /**
   * Enable or disable the custom branding of our plugin
   */
  'branding' => true,
  
  /**
   * Is this in production mode or dev?
   */
  'debug' => true,
  
  /**
   * Enables the loading of titan framework to serve options
   */
  'options' => true,
  
  /**
   * Enable or disable the auto update feature.
   * Note that the autoupdate feature relies on having an options framework installed, so 
   * enablign this options will overwrite a false in the 'options'
   */
  'autoUpdate' => true,
  
  /**
   * If we set autoUpdates to true, we mjst provide the URL where our updates lie
   */
  'updatesURL' => 'http://weare732.com/versions/updates/?action=get_metadata&slug=',
  
  /**
   * Enable the less loading, it will be available in $this->less
   */
  'less' => true,
  
  /**
   * DANGER ZONE: ADVANCED SETTINGS
   * Do not change anything beyond this point if you don't know exactly what it does!
   */
  
  /**
   * It is important to us to pass a variable of some file contained in the root of the plugin
   * Like this very own config file, so that's what we do here.
   */
  'file' => __FILE__,
  
);