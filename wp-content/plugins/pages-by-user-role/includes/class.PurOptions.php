<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
class PurOptions {
	var $capability;
	var $page_title;
	var $menu_text;
	var $option_menu_parent;
	function __construct($args=array()){
		$defaults = array(
			'capability'			=> PUR_CAPABILITY,
			'page_title'			=> __('Pages by User Role','pur'),
			'menu_text'				=> __('Pages by User Role','pur'),
			'option_menu_parent'	=> 'options-general.php',
			'option_show_in_metabox'=> false
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}
		//---	
		add_action('admin_menu',array(&$this,'admin_menu'));
		add_action('wp_ajax_pur_notifications', array(&$this,'pur_notifications'));
	}

	function admin_menu(){
		if(current_user_can('manage_options')){
			$page_id = add_submenu_page( $this->option_menu_parent,$this->page_title ,$this->menu_text,$this->capability,'pur-options',array(&$this,'body'));			
			add_action( 'admin_head-'. $page_id, array(&$this,'head') );	
		}		
	}

	function body(){
		//--------------
		$post_types=array();
		foreach(get_post_types(array('_builtin' => false),'objects','and') as $post_type => $pt){
			$post_types[$post_type]=$pt;
		} 
		//--------------
		$sys_msg='';
		if(isset($_POST['f_save'])){
			$options = array(
				'post_types'=> isset($_POST['post_types'])&&is_array($_POST['post_types'])?$_POST['post_types']:array(),
				'redir_url' => isset($_POST['redir_url'])?$_POST['redir_url']:'',
				'comment_filtering' => isset($_POST['comment_filtering'])?1:0,
				'license_key'=> isset($_POST['license_key'])?$_POST['license_key']:''
			);
			update_option('pur_options',$options);
			$sys_msg='<div id="message" class="updated below-h2">Options saved.</div>';
		}
		
		$options = get_option('pur_options');
		$options = empty($options)?array():$options;
		if(!isset($options['post_types'])){$options['post_types']=array();}
		//----------
?>
<div class="pur-options-main wrap">
<h2>Access Control by User Role Options</h2>
<?php echo $sys_msg ?>
<form name="sform" method="post" action="">
<div id="pur-options-cont">

	<div id="pur-post_types" class="toggle-option">
		<h3 class="option-title">Settings<span>General Settings (default redirect url)</span></h3>
		<div class="option-content">
			<div class="description">If a Page, Post or Custom Post Type does not have a redirect URL, users with no access to the Page, Post or Custom Post Type will get redirected to the default redirect URL.</div>
			<p><label for="home_background">Default Redirect URL</label>
			<input type="text" size="50" name="redir_url" value="<?php echo $options['redir_url']?>" />
			</p>	
			<div class="clear"></div>	
			
			<div class="description">Check to enable comment filtering.</div>
			<div class="pt-option">
				<input type="checkbox" <?php echo @$options['comment_filtering']==1?'checked="checked"':''?> name="comment_filtering" value="1">&nbsp;Check to enable comment filtering.	
			</div>
			<div class="clear"></div>		
		</div>
	<div>

	<div id="pur-post_types" class="toggle-option">
		<h3 class="option-title">Custom Post Types<span>Enable Access Control Options for other post types.</span></h3>
		<div class="option-content">
			<div class="description">Access Control by User Role can be enabled for plugins using WordPress 3.0 Custom Post Types.</div>
<?php if(empty($post_types)):?>
<p>There are no additional Custom Post Types to enable.</p>
<?php else:foreach ($post_types  as $post_type => $pt ): $checked=in_array($post_type,$options['post_types'])?'checked="checked"':''; ?>
			<div class="pt-option">
				<input type="checkbox" <?php echo $checked?> name="post_types[]" value="<?php echo $post_type?>">&nbsp;<?php echo @$pt->labels->name?$pt->labels->name:$post_type;?>		
			</div>
<?php endforeach;endif;?>		
			<div class="clear"></div>
		</div>
	</div>	
	
<?PHP if(!defined('PUR_THEME')):?>
	<div id="pur-license" class="toggle-option">
		<h3 class="option-title">License<span>Item Purchase Code (License Key)</span></h3>
		<div class="option-content">
			<div class="description">
<p>Your purchase code can be found in your license Certificate file.</p>
<p>Go to Codecanyon and click My Account at the top, then click Downloads, and then click the <strong>License Certificate link</strong>.
You will find the code in there and it will look something like this:</p>
<p>Item Purchase Code:<br />bek72585-d6a6-4724-c8c4-9d32f85734g3</p>
<p>This allows us to verify your purchase and provide support to those who have paid. We will also automatically notify you when updates are available. Updates are free to download if you have purchased this once. If you have questions about this, please contact us at <a href="mailto:support@righthere.com">support@righthere.com</a>.</p>			
			</div>
			<div class="pt-option">
				<label for="pur-license">Lincense key:</label>
				<input type="text" size="50" name="license_key" value="<?php echo @$options['license_key']?>" />
			</div>	
			<div class="clear"></div>		
		</div>
	</div>
<?php endif;?>

</div>

<input type="submit" class="button-primary save-button" name="f_save" value="Save" />
</form>
</div>	
<?php		
		
	}
	
	function head(){
?>
<script>
 jQuery(document).ready(function($){ 
 	$(".option-title").click(function(){
		$(this).toggleClass('open').next().toggle();
	});
	var args = {
		action: 'pur_notifications'
	};	
	$.post(ajaxurl,args,function(data){
		if(data.R=='OK'){
			$('#pur-notifications').html(data.MSG);	
		}
	},'json');		
});	
</script>
<?php
		function pur_update_notice(){
			echo sprintf("<div id=\"pur-notifications\"></div>");
		}
		add_action( 'admin_notices', 'pur_update_notice' );
	}
	
	function pur_notifications(){
		$options = get_option('pur_options');
		$options = is_array($options)?$options:array();
		$url = sprintf('http://plugins.righthere.com/?rh_latest_version=PUR&site_url=%s&license_key=%s',urlencode(site_url('/')),urlencode(@$options['license_key']));
		if(defined('PUR_THEME')){$url.="&theme=1";}

		$handle=@fopen($url,'r');
		if($handle){
			$contents = '';
			while (!feof($handle)) {
			  $contents .= fread($handle, 8192);
			}
			fclose($handle);	
			if(!defined('PUR_THEME')){
				$r = json_decode($contents);
				if(is_object($r)&&property_exists($r,'version')){
					if($r->version>PUR_VERSION){
						$response = (object)array(
							'R'		=> 'OK',
							'MSG'	=> sprintf("<div class=\"updated fade\"><p><strong>Pages by User Role update %s is available! <a href=\"%s\">Please update now</a></strong></p></div>",$r->version,$r->url)
						);
						die(json_encode($response));
					}
				}			
			}
		}
		die(json_encode((object)array('R'=>'ERR','MSG'=>'Notification service is not available.')));		
	}
	
	function options_head(){
		function pur_update_notice(){
			echo sprintf("<div id=\"pur-notifications\"></div>");
		}
		add_action( 'admin_notices', 'pur_update_notice' );		
?>
<script>
jQuery(document).ready(function($) {
	var args = {
		action: 'pur_notifications'
	};	
	$.post(ajaxurl,args,function(data){
		if(data.R=='OK'){
			$('#pur-notifications').html(data.MSG);	
		}
	},'json');	
});
</script>
<?php
	}	
}

?>