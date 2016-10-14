<?php
/*
Plugin Name: Project Manager Pro with Buddypress 
Description: All actions e.g making discussion, commenting on discussion, assiging task at a private project will push activity at buddypress only to the memebers activity screen
Author: Mahibul Hasan
Plugin url: mahibul.wpenginer.com
Author url: http://www.upwork.com/o/profiles/users/_~01ecf4a954c6b739b9/	
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define('CPMBUDDYPRESS_DIR', dirname(__FILE__));
define('CPMBUDDYPRESS_BASE', plugin_basename(__FILE__));
define('CPMBUDDYPRESS_URL', plugins_url('/', __FILE__));
define('CPMBUDDYPRESS_TEXT_DOMAIN', 'cpm_buddypress');

//including classes
include CPMBUDDYPRESS_DIR . '/classes/projects-with-buddpress.php';
include CPMBUDDYPRESS_DIR . '/classes/project-actions.php';


//instanciating the main class
$wedev_projects_with_buddypress = new Wedev_Projects_With_Buddypress();



?>