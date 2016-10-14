<?php
/**
 * 
 * Security check. No one can access without Wordpress itself
 * 
 * */
defined('ABSPATH') or die();

// Including necessary files
include_once('php/elFinderConnector.class.php');
include_once('php/elFinder.class.php');
include_once('php/elFinderVolumeDriver.class.php');
include_once('php/elFinderVolumeLocalFileSystem.class.php');

/**
 * 
 * elFinder class to manipulate elfinder
 * 
 * */

class FM_EL_Finder{
	
	// Important data
	
	/**
	 * 
	 * @var array $base_path Base url(s) for the current user
	 * 
	 * */
	public $base_path;
	
	/**
	 * 
	 * Constructor function
	 * 
	 * */
	public function __construct(){
	
		
	}
	
	/**
	 * 
	 * Connect function
	 * @return object
	 * 
	 * */
	public function connect($options){
		
		return new elFinderConnector(new elFinder($options));
		
	}
	
}
