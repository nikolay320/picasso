<?php

/*
 * Plugin Name: Bp Stickers
 * Version: 1.3
 * Author: asghar hatampoor
 * Author URI: http://webcaffe.ir
 * Plugin URI: http://Webcaffe.ir
 * Description: Allow Users to add stickers in activity posts and message by clicking on icons
 */
if ( !defined( 'ABSPATH' ) ) exit;

if ( !defined( 'BPST_PLUGIN_VERSION' ) )
	define( 'BPST_PLUGIN_VERSION', '1.3.0' );

if ( !defined( 'BPST_PLUGIN_DIRNAME' ) )
	define( 'BPST_PLUGIN_DIRNAME', basename( dirname( __FILE__ ) ) );
	

if ( !defined( 'BPST_PLUGIN_DIR' ) )
	define( 'BPST_PLUGIN_DIR', trailingslashit( WP_PLUGIN_DIR . '/' . BPST_PLUGIN_DIRNAME ) );

if ( !defined( 'BPST_PLUGIN_URL' ) ) {
	$plugin_url = trailingslashit( plugins_url( BPST_PLUGIN_DIRNAME ) );
	define( 'BPST_PLUGIN_URL', $plugin_url );
}

class BpStickers{
    private static  $instance;
    private function __construct(){
      add_action('wp_enqueue_scripts',  array($this,'load_js'));
      add_action('wp_print_styles',  array($this,'load_css')); 	  
      add_action('bp_before_activity_post_form',array($this,'list_stickers'));  
      add_action('bp_activity_entry_comments',array($this,'list_stickers_comment'));  
	  add_action('bp_after_messages_compose_content',array($this,'list_stickers')); 
      add_action('bp_after_message_reply_box',array($this,'list_stickers'));
      add_action('wp_ajax_bp_sticker_ajax',array($this,'bp_sticker_ajax'));
      add_action( 'wp_ajax_nopriv_bp_sticker_ajax',array($this, 'bp_sticker_ajax') );    
	 add_filter( 'bp_get_activity_latest_update',array($this, 'bp_st_translate_sticker')); 
	 add_filter( 'bp_get_activity_latest_update_excerpt',array($this, 'bp_st_translate_sticker')); 
	 add_filter( 'bp_get_activity_content_body',array($this, 'bp_st_translate_sticker'));
     add_filter( 'bp_get_activity_action',array($this, 'bp_st_translate_sticker')); 	 
	 add_filter( 'bp_get_activity_content',array($this, 'bp_st_translate_sticker')); 
	 add_filter( 'bp_get_activity_parent_content',array($this, 'bp_st_translate_sticker' )); 	
     add_filter( 'bp_get_the_thread_message_content',array($this, 'bp_st_translate_sticker' )); 
	 add_filter( 'bp_get_message_thread_excerpt',array($this, 'bp_st_translate_sticker' ));
	

    }
function load_js(){

      $url= BPST_PLUGIN_URL. 'js/bp-sticker.js';     
      wp_enqueue_script('bp-sticker-js',$url,array('jquery'));
	 
    }
    
function load_css(){
	 $url= BPST_PLUGIN_URL. 'css/bp-sticker.css';
     wp_enqueue_style('bp-stiker-css',$url, array(),BPST_PLUGIN_VERSION,'all');
    }
public static function get_instance(){
        if(!isset (self::$instance))
                self::$instance=new self();
        return self::$instance;
    }
public static function list_stickers($message){
	global $bp;	
	  $html ="<span class='bp-smiley-button'>
	  <a class='buddypress-smiley-button'  ><i class='dashicons dashicons-smiley'></i></a>
	  </span>
	  <span class='bp-smiley-no'>
	  <a class='buddypress-smiley-button' ><i class='dashicons dashicons-no'></i></a>
	  </span><div id='sl'></div>"; 
     echo apply_filters( 'list_stickers',$html);	
    }
public static function list_stickers_comment($message){
	global $bp;	
	$activity_id = (int) bp_get_activity_id();	
	if ( !isset( $activity_id ) || $activity_id == 0 )
		return false;
  $html ="<a class='bp-smiley-button-comment' rel='comm_".$activity_id."'>
	  <div class='buddypress-smiley-button'><i class='dashicons dashicons-smiley'></i></div>
	  </a>
	  <span class='bp-smiley-no-comment'>
	  <a class='buddypress-smiley-button' ><i class='dashicons dashicons-no'></i></a>
	  </span><div class='sl-".$activity_id."-comm'></div>"; 
   
     echo apply_filters( 'list_stickers_comment',$html);	
    }
function bp_sticker_ajax(){
    $html ="<div class='divsti' >"; 
    foreach (self::replace_with_st() as $codes => $imgs) {     
		 $filename =  preg_replace('/.[^.]*$/', '', $codes);
		 $icon = BPST_PLUGIN_URL. 'images/sticons/'. $imgs.'';
		 $html.="<img src='$icon' data-code=':$filename:' class='smiley' /> ";
        }	
	 $html .="</div>"; 
     echo  $html;
 	wp_die( );  
}


function replace_with_st(){ 
$dir = BPST_PLUGIN_DIR."images/sticons/";
if ($opendir = opendir($dir)) { 
  $images=array();
  while (($file = readdir($opendir)) !==FALSE) { 
   if($file != "." && $file != "..") {
    $images["$file"]=$file;
   }
  } 
} 

   return $images;      
   }

function bp_st_translate_sticker( $content) {
	global $bp;
      foreach( self::replace_with_st() as $codes => $imgs) {
	  $filename = preg_replace('/.[^.]*$/', '', $codes);
	  $icon = BPST_PLUGIN_URL. 'images/sticons/'. $imgs.'';
           $code[] =":$filename:";
           $img[] = "<img src='$icon' class='st-smiley'/> ";					
        }
        $icons = str_replace($code, $img, $content);
        return $icons;
   }

}

 BpStickers::get_instance();
?>