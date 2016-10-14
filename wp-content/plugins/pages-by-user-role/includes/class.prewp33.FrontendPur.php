<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
class FrontendPur {

	function __construct(){
		add_action('init',array(&$this,'init'));	
	}
	
	function init(){
		add_filter('template_redirect',array(&$this,'template_redirect'));
		add_filter('the_content',array(&$this,'the_content'),10,1);
		add_filter('wp_nav_menu_args',array(&$this,'wp_nav_menu_args'),10,1);//default nav menu 
		add_filter('wp_list_pages_excludes',array(&$this,'wp_list_pages_excludes'),10,1);//wp_list_pages
		add_filter('wp_list_categories',array(&$this,'wp_list_categories'),10,1);
		add_filter('wp_get_nav_menu_items',array(&$this,'wp_get_nav_menu_items'),10,3);//nav menu
		add_action('parse_query',array(&$this,'parse_query'),10,1);
		add_action('pre_get_posts',array(&$this,'parse_query'),10,1);		
		add_filter('the_comments',array(&$this,'get_comment'),10,2);	
		if(is_admin())add_action('init',array(&$this,'_admin_menu'),9999);
	}
	
	function get_comment($comments,$c_query){
		if(is_admin())return $comments;
		$options = get_option('pur_options');
		$comment_filtering = isset($options['comment_filtering'])?true:false;

		if($comment_filtering){
			$tmp = array();
			foreach($comments as $c){
				if($this->check_access($c->comment_post_ID)){
					$tmp[]=$c;
				}
			}
			$comments = $tmp;
		}

		return $comments;
	}
	
	function parse_query(&$arr){
		if(is_admin())return;
		if(@$arr->is_single||@$arr->is_page)return;
		$this->template_redirect();//with this the post becomes unexistant to the user, so redirection never happens.
		$arr->query_vars['post__not_in']=$this->current_user_excluded_post_ids();
		$arr->query_vars['category__not_in']=$this->current_user_excluded_categories();		
	} 
	  
	function wp_get_nav_menu_items($items, $menu, $args){
		if(is_admin())return $items;
		$exclude = $this->current_user_excluded_post_ids();
		$excluded_categories = $this->current_user_excluded_categories();
		foreach($items as $i => $item){		
			if($item->type=='post_type' && in_array($item->object_id,$exclude)){
				if('1'==get_post_meta($item->object_id,'pur_show_in_nav',true))continue;
				unset($items[$i]);
			}else if($item->type=='taxonomy' && $item->object=='category' && in_array($item->object_id,$excluded_categories)){
				unset($items[$i]);
			}
		}
		return $items;
	}
	
	/*
	function wp_nav_menu_args($args){
		$args['exclude']=','.implode(",",$this->current_user_excluded_post_ids());
		return $args;
	}
	*/ 
	function wp_nav_menu_args($args){
		$exclude = isset($args['exclude'])?explode(',',$args['exclude']):array();
		$pur_exclude = $this->current_user_excluded_post_ids();
		foreach($pur_exclude as $excluded_id){
			if('1'==get_post_meta($excluded_id,'pur_show_in_nav',true))continue;
			$exclude[]=$excluded_id;
		}		
		$args['exclude']=','.implode(",",$exclude);
		return $args;
	}
	
	/*
	function wp_list_pages_excludes($exclude_array){
		return array_merge($exclude_array,$this->current_user_excluded_post_ids());
	}
	*/
	function wp_list_pages_excludes($exclude_array){
		foreach( $this->current_user_excluded_post_ids() as $excluded_id){
			if('1'==get_post_meta($excluded_id,'pur_show_in_nav',true))continue;
			$exclude_array[]=$excluded_id;
		}		
		return $exclude_array;
	}
	
	function wp_list_categories($str){
		//until wp_list_categories adds a filter like wp_list_pages_excludes, 
		//will have to just hide the category from the user with css
		$cats = $this->current_user_excluded_categories();
		if(count($cats)>0){
			$str.="<style>";
			foreach($cats as $cat_id){
				$str.=".cat-item.cat-item-$cat_id{display:none;}";
			}			
			$str.="</style>";
		}
		return $str;
	}
	
	function get_uroles($for_sql=true){
		global $wpdb,$userdata;
		$wp_capabilities = $wpdb->prefix.'capabilities';
		$uroles = array();
		if(!is_null($userdata)&&is_array($userdata->$wp_capabilities)&&count($userdata->$wp_capabilities)>0){
			foreach($userdata->$wp_capabilities as $urole => $active){
				if('1'==$active){
					if($for_sql){
						$uroles[]=sprintf("'%s'",$urole);
					}else{
						$uroles[]=$urole;
					}
				}
			}		
		}
		return $uroles;				
	}
	
	function current_user_excluded_post_ids(){
		global $wpdb,$userdata;
		
		$also_exclude = array();
		$extrafilter = '';
		if(is_user_logged_in()){
			$wp_capabilities = $wpdb->prefix.'capabilities';
			if(is_array($userdata->$wp_capabilities)&&count($userdata->$wp_capabilities)>0){
				$uroles = $this->get_uroles(true);		
				if(count($uroles)>0){
					$extrafilter = "AND(M.post_id NOT IN (SELECT DISTINCT(post_id) FROM `{$wpdb->postmeta}` WHERE meta_key='pur-available-roles' AND meta_value IN (".implode(',',$uroles).")))";			
					//--
					$sql = "SELECT DISTINCT(M.post_id) FROM {$wpdb->posts} P INNER JOIN `{$wpdb->postmeta}` M ON P.ID=M.post_id AND P.post_status='publish' WHERE M.`meta_key` LIKE 'pur-blocked-roles'";
					$sql.= "AND(M.post_id IN (SELECT DISTINCT(post_id) FROM `{$wpdb->postmeta}` WHERE meta_key='pur-blocked-roles' AND meta_value IN (".implode(',',$uroles).")))";
					$also_exclude = $wpdb->get_col($sql,0);	
				}	
			}
		}

		$sql = "SELECT DISTINCT(M.post_id) FROM {$wpdb->posts} P INNER JOIN `{$wpdb->postmeta}` M ON P.ID=M.post_id AND P.post_status='publish' WHERE M.`meta_key` LIKE 'pur-available-roles' $extrafilter";
		$exclude = $wpdb->get_col($sql,0);
		//---
		$exclude = array_merge($exclude,$also_exclude);		
		return empty($exclude)?array(0):$exclude;
	}

	function current_user_excluded_categories(){
		$pur_roles = get_option('pur-category-roles');
		$pur_roles = is_array($pur_roles)?$pur_roles:array();

		$uroles = $this->get_uroles(false);

		$exclude = array();
		if(isset($pur_roles['category'])&&is_array($pur_roles['category'])&&count($pur_roles['category'])>0){
			foreach($pur_roles['category'] as $term_id => $roles){
				if(count($roles)==0)continue;//no role set for category so no need to exclude.
				$r = array_intersect($roles,$uroles);
				if( 0==count($r) ){
					$exclude[]=$term_id;
				}
			}		
		}
		return $exclude;
	}
	
	function the_content($content){	
		global $post;
		if(@!$this->check_access($post->ID)){
			return $this->no_access_the_content($post->ID,$content);
		}
		return $content;
	}
	
	function template_redirect(){
		if(is_single()||is_page()){
			global $userdata, $wp_query;
			$page_obj = $wp_query->get_queried_object();
			
			if(!$this->check_access($page_obj->ID)){
				$this->no_access_page($page_obj->ID);
			}
		}
		return;
	}
	
	function check_access($post_id){
		global $wpdb,$userdata;
		if( $this->current_user_is_in_blocked_roles($post_id) )return false;
		$pur_roles = get_post_meta($post_id,'pur-available-roles');
		$pur_roles = is_array($pur_roles)?$pur_roles:array();
		if(count($pur_roles)==0){
			return $this->check_category_access($post_id);
		}else{
			if(!is_user_logged_in()){
				return false;
			}
			
			$wp_capabilities = $wpdb->prefix.'capabilities';
			if(!is_array($userdata->$wp_capabilities)||count($userdata->$wp_capabilities)==0){
				return false;
			}
			
			foreach($userdata->$wp_capabilities as $urole => $active){
				if('1'==$active && in_array($urole,$pur_roles)){
					return $this->check_category_access($post_id);
				}
			}
		}
		
		return false;
	}
	
	function current_user_is_in_blocked_roles($post_id){
		global $wpdb,$userdata;
		if(is_user_logged_in()){
			$blocked_roles = get_post_meta($post_id,'pur-blocked-roles');
			$blocked_roles = is_array($blocked_roles)?$blocked_roles:array();	
			if(count($blocked_roles)>0){
				$wp_capabilities = $wpdb->prefix.'capabilities';
				if(!is_array($userdata->$wp_capabilities)||count($userdata->$wp_capabilities)==0){
					return false;
				}
				foreach($userdata->$wp_capabilities as $urole => $active){
					if('1'==$active && in_array($urole,$blocked_roles)){
						return true;
					}
				}
			}
		}
		return false;
	}
	
	function check_category_access($post_id){
		$post_categories = wp_get_post_categories( $post_id );
		if(count($post_categories)==0){
			return true;
		}else{
			$excluded = $this->current_user_excluded_categories();

			foreach($post_categories as $category_id){
				if(in_array($category_id,$excluded)){
					return false;
				}
			}
			return true;
		}
	}
	
	function no_access_the_content($post_id,$content){
		$content = __('Restricted user content','pur');
		return $content;
	}
	
	function no_access_page($post_id){
// 1. if not logged redir to login (if pur option redir url is enabled)
// 2. redir to custom redir url
// 3. redir to default
		$options = get_option('pur_options');
		
		$redir_url = get_post_meta($post_id,'pur_redir_url',true);
		if(trim($redir_url)==''){
			$redir_url = isset($options['redir_url'])?$options['redir_url']:'';
		}
		
		$login_redir = isset($options['login_redir'])?intval($options['login_redir']):1;
		
		if(!is_user_logged_in()&&$login_redir){
			$redir_url = site_url('/wp-login.php?redirect_to='.$_SERVER['REQUEST_URI']);
			wp_redirect($redir_url);
			die();
		}elseif(trim($redir_url)!=''){
			wp_redirect($redir_url);
			die();
		}
		
		wp_die( __('You dont have access to this page, contact the website administrator.','pur'), 'No access' );
	}
	
	function _admin_menu(){
		global $wp_post_types, $pur_wp_post_types;
		$pur_wp_post_types = $wp_post_types;
		
		$options = get_option('pur_options');
		if(isset($options['disable_cpur'])&&$options['disable_cpur']=='1'){
			return;
		}
		
		if(is_array($wp_post_types)&&count($wp_post_types)>0){
			foreach($wp_post_types as $post_type => $pt){
				$option_name = "cpur_".$post_type;
				if(isset($options[$option_name])&&is_array($options[$option_name])&&count($options[$option_name])>0){
					$remove = true;
					foreach($options[$option_name] as $role_enabled){
						if(current_user_can($role_enabled)){
							$remove = false;
							break;
						}
					}
					if($remove){
						unset($wp_post_types[$post_type]);
					}
				}
			}
		}	
	}
}

?>