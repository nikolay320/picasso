<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
class PurShortcodes {
	function __construct(){
		add_shortcode('pur_restricted', array(&$this,'pur_restricted'));
		add_shortcode('pur_restricted_alt', array(&$this,'pur_restricted_alt'));
		add_shortcode('pur_not_logged_in', array(&$this,'pur_not_logged_in'));
	}
	
	function pur_restricted($atts,$content=null,$code=""){
		extract(shortcode_atts(array(
			'capability' 	=> 'view_restricted_content',
			'alt'			=> ''
		), $atts));
		
		if(current_user_can($capability)){
			return do_shortcode($content);
		}else{
			return do_shortcode($alt);
		}
	}
	
	function pur_restricted_alt($atts,$content=null,$code=""){
		extract(shortcode_atts(array(
			'capability' 	=> 'view_restricted_content'
		), $atts));
		
		if(!current_user_can($capability)){
			return do_shortcode($content);
		}else{
			return '';
		}
	}
	
	function pur_not_logged_in($atts,$content=null,$code=""){
		if(!is_user_logged_in()){
			return do_shortcode($content);
		}else{
			return '';
		}
	}
}

?>