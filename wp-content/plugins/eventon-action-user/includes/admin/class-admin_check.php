<?php
/**
 * EventON Addon Error decyphering
 *
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	ERRORS
 * @version     0.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('addon_check')){
class addon_check{


	private $errorCode = '00';
	private $addon_data ;
	function __construct($addon_data){
		$this->addon_data = $addon_data;

		//$this->check_evo_exists();
	}

	function initial_check(){

		// check if eventon version is 2.2.19 or higher
		global $eventon;

		if(!empty($eventon->version) && version_compare($eventon->version, '2.2.18')<0){// lower eventon than needed
			$this->errorCode = '03';
			$this->show_notice();
			return false;
		}else{// good eventon version
			$addon_file_ = false;
			// check for class addon file url and if it exists via constants
			if(defined('AJDE_EVCAL_PATH')){
				$path = AJDE_EVCAL_PATH;
				$url = $path .'/classes/class-evo-addons.php';
				$addon_file_ =file_exists($url)? $url: false;
			}elseif(function_exists('evo_get_addon_class_file')){
				$url = evo_get_addon_class_file();
				$addon_file_ =file_exists($url)? $url: false;
			}
			
			if(!$addon_file_){// eventon is not present
				$this->errorCode = '01'; 
				$this->show_notice();
				return false;
			}else{// class file exists and good to go

				// check for eventon versions
				if(!empty($eventon->version) && version_compare($eventon->version, $this->addon_data['evo_version'])<0){				
					$this->errorCode = '02'; 
					$this->show_notice();
					return false;
				}else{
					include_once( $addon_file_);
					if(!class_exists('evo_addon')){
						$this->errorCode = '01'; 
						$this->show_notice();
						return false;
					}else{
						return $addon_file_;
					}					
				}
			}
		}
	}

	public function show_notice(){
		add_action('admin_notices', array($this, 'notice'));
	}
	public function notice(){
		$extra = ($this->errorCode!='00')? $this->check($this->errorCode):null;
		?>
        <div class="message error"><p><?php printf(__('EventON %s is NOT active! - '), $this->addon_data['name']); echo $extra;?></p></div>
        <?php
	}

	public function check($code){
		global $eventon_au;
		$decypher = array(
			'01'=> 'You do not have EventON main plugin - EventON main plugin is REQUIRED to run '.$this->addon_data['name'].'.',
			'02'=>	$this->addon_data['name'].' need EventON version <b>'.$this->addon_data['evo_version'].'</b> or higher to work correctly, please update EventON.',
			'03'=>	'EventON version is older than what is suggested for this addon. Please Update EventON.',
		);
		return $decypher[$code];
	}
}
}