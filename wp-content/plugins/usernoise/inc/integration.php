<?php
class UN_Integration {
	public function __construct(){
		if (!is_admin()){
			add_action('wp_enqueue_scripts', array($this, '_init'));
			add_action('wp_head', 'un_script', 0, 99);
		}
	}

	public function _init(){
		if (is_user_logged_in()){
			wp_enqueue_style('usernoise-adminbar', usernoise_url('/css/admin-bar.css'), null, UN_VERSION);
		}
		wp_enqueue_script('usernoise-button', usernoise_url('/js/usernoise.js'), array('jquery'),
		 	UN_VERSION, true);
		wp_enqueue_style('usernoise-icons', usernoise_url('/js/vendor/font/css/usernoise-embedded.css'), array(), UN_VERSION);
		wp_enqueue_style('usernoise-button', usernoise_url('/css/button.css'), null, UN_VERSION);
		//wp_enqueue_style('usernoise-popup', usernoise_url('/js/popup/dist/popup.css'), null, UN_VERSION);
		wp_enqueue_style('usernoise-button-font', usernoise_url('/vendor/icons/css/embedded.css'), null, UN_VERSION);
		$this->refresh_cookie();
	}

	public function refresh_cookie(){
		if (!isset($_COOKIE['un_unique'])){
			setcookie('un_unique', $id = wp_generate_password(), time() + 60 * 60 * 24 * 365, "/");
		} else if (isset($_COOKIE['un_unique'])){
			setcookie('un_unique', $id = $_COOKIE['un_unique'], time() + 60 * 60 * 24 * 365, "/");
		}
		return $id;
	}
}
global $un_integration;
$un_integration = new UN_Integration;
