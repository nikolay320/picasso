<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
class pur_settings {
	function __construct(){
		add_filter('pop-options_pur',array(&$this,'options'),10,1);
	}
	
	function options($t){
		$i = count($t);

		//-------------------------	
		$i = count($t);
		$t[$i] = (object)array();
		$t[$i]->id 			= 'pur-defaults'; 
		$t[$i]->label 		= __( 'General', 'pur' );
		$t[$i]->right_label	= __( 'General Settings (default redir url)', 'pur' );
		$t[$i]->page_title	= __( 'General', 'pur' );
		$t[$i]->theme_option = true;
		$t[$i]->plugin_option = true;
		$t[$i]->priority      = 9;
		$t[$i]->options = array(
			(object)array(
				'id'	=> 'redir_url',
				'type'	=> 'textarea',
				'label'	=> __('Default Redirect URL','pur'),
				'description' => __('If a Page, Post or Custom Post Type does not have a redirect URL, users with no access to the Page, Post or Custom Post Type will get redirected to the default redirect URL.','pur'),
				'el_properties' => array(),
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'id'	=> 'comment_filtering',
				'type'	=> 'checkbox',
				'label'	=> __('Check to enable comment filtering. ','pur'),
				'description' => __('Check to enable comment filtering.','pur'),
				'el_properties' => array(),
				'save_option'=>true,
				'load_option'=>true
			),	
			(object)array(
				'id'	=> 'pur_include_ajax',
				'type'	=> 'checkbox',
				'label'	=> __('Check to enable ajax filtering. ','pur'),
				'description' => __('Check to enable ajax filtering.','pur'),
				'el_properties' => array(),
				'save_option'=>true,
				'load_option'=>true
			),	
			(object)array(
				'id'	=> 'login_redir',
				'type'	=> 'checkbox',
				'label'	=> __('Redirect to login','pur'),
				'description' => __('If a visitor(not logged user) is trying to access a restricted page, check this option to redirect to login, or leave it unchecked to redirect to the defined redirect url.','pur'),
				'value'=>1,
				'default'=>1,
				'el_properties' => array(),
				'save_option'=>true,
				'load_option'=>true
			),			
			(object)array(
				'type'=>'clear'
			),
			(object)array(
				'type'	=> 'submit',
				'label'	=> __('Save','sws'),
				'class' => 'button-primary',
				'save_option'=>false,
				'load_option'=>false
			)
		);
		
		//-------------------------	
		//--------------
		$post_types=array();
		foreach(get_post_types(array('_builtin' => false),'objects','and') as $post_type => $pt){
			$post_types[$post_type]=$pt;
		} 
		//--------------
		if(count($post_types)>0){
			$i = count($t);
			$t[$i] = (object)array();
			$t[$i]->id 			= 'pur-post-types'; 
			$t[$i]->label 		= __( 'Enable Custom Post Types', 'pur' );
			$t[$i]->right_label	= __( 'Enable Page Access Options for Custom Post Types', 'pur' );
			$t[$i]->page_title	= __( 'Enable Custom Post Types', 'pur' );
			$t[$i]->theme_option = true;
			$t[$i]->plugin_option = true;
			$t[$i]->options = array();
			
			$j=0;
			foreach($post_types as $post_type => $pt){
				$tmp=(object)array(
					//'id'	=> 'post_types_'.$post_type,
					'name'	=> 'post_types[]',
					'type'	=> 'checkbox',
					'option_value'=>$post_type,
					'label'	=> (@$pt->labels->name?$pt->labels->name:$post_type),
					'el_properties' => array(),
					'save_option'=>true,
					'load_option'=>true
				);
				if($j==0){
					$tmp->description = __("Page Access by User Role can be enabled for plugins using WordPress 3.0 Custom Post Types.",'pur');
					$tmp->description_rowspan = count($post_types);
				}
				$t[$i]->options[]=$tmp;
				$j++;
			}
			
			$t[$i]->options[]=(object)array(
					'type'=>'clear'
				);
			$t[$i]->options[]=(object)array(
					'type'	=> 'submit',
					'label'	=> __('Save','sws'),
					'class' => 'button-primary',
					'save_option'=>false,
					'load_option'=>false
				);		
		}		


		
		// Custom Post Types
		$i = count($t);
		$t[$i] = (object)array();
		$t[$i]->id 			= 'cpur'; 
		$t[$i]->label 		= __( 'Custom Post Types', 'pur' );
		$t[$i]->right_label	= __( 'Backend option', 'pur' );
		$t[$i]->page_title	= __( 'Custom Post Types', 'pur' );
		$t[$i]->theme_option = true;
		$t[$i]->plugin_option = true;

		$t[$i]->options = array(
			(object)array(
				'id'	=> 'disable_cpur',
				'type'	=> 'yesno',
				'label'	=> __('Disable CPUR','pur'),
				'description' => sprintf('<p>%s</p><p>%s</p>',
					__('Oops! If you have left out yourself from accessing a custom post type, choose this option so the post type is visible again.','pur'),
					__('This option applies to the backend.','pur')
				),
				'el_properties' => array(),
				'save_option'=>true,
				'load_option'=>true
			)			
		);		
		
		global $wp_roles;
		$roles = $wp_roles->get_names();
		
		if(is_array($roles)&&count($roles)>0){
			$j=0;
			foreach($post_types as $post_type => $pt){
				$tmp=(object)array(
					'type'	=> 'label',
					'label'	=> (@$pt->labels->name?$pt->labels->name:$post_type),
					'save_option'=>false,
					'load_option'=>false
				);
				if($j==0){
					$tmp->description = __("You can restrict access to certain custom post types by checking the user roles that should have access to it.  Please observe that unchecking a role does not mean that it will enable the post type for that role.",'pur');
					$tmp->description_rowspan = count($post_types);
				}
				$t[$i]->options[]=$tmp;
				$j++;		
				//-----			
				foreach($roles as $role => $role_name){
					$t[$i]->options[]=(object)array(
						'id'	=> sprintf("cpur_%s_%s",$post_type,$role),
						'name'	=> sprintf("cpur_%s[]",$post_type),
						'type'	=> 'checkbox',
						'option_value'=>$role,
						'label'	=> $role_name,
						'el_properties' => array(),
						'save_option'=>true,
						'load_option'=>true
					);
				}	
			}
		}
		
		$t[$i]->options[]=(object)array(
				'type'=>'clear'
			);
		$t[$i]->options[]=(object)array(
				'type'	=> 'submit',
				'label'	=> __('Save','sws'),
				'class' => 'button-primary',
				'save_option'=>false,
				'load_option'=>false
			);									

		//------------------
		$i = count($t);
		$t[$i] = (object)array();
		$t[$i]->id 			= 'cpur_archive'; 
		$t[$i]->label 		= __( 'Post Type Archive', 'pur' );
		$t[$i]->right_label	= __( 'Frontend option.', 'pur' );
		$t[$i]->page_title	= __( 'Post Type Archive', 'pur' );
		$t[$i]->theme_option = true;
		$t[$i]->plugin_option = true;
		$t[$i]->options = array();	
		
		if(is_array($roles)&&count($roles)>0){
			$j=0;
			foreach($post_types as $post_type => $pt){
				$tmp=(object)array(
					'type'	=> 'subtitle',
					'label'	=> (@$pt->labels->name?$pt->labels->name:$post_type),
					'save_option'=>false,
					'load_option'=>false
				);
				if($j==0){
					$tmp->description = sprintf('<p>%s</p><p>%s</p>',
						__("This option applies to the frontend.",'pur'),
						__("You can restrict access to certain custom post types by checking the user roles that should have access to it.  Please observe that unchecking a role does not mean that it will enable the post type for that role.",'pur')
					);
					$tmp->description_rowspan = count($post_types);
				}
				$t[$i]->options[]=$tmp;
				$j++;		
				//-----			
				foreach($roles as $role => $role_name){
					$t[$i]->options[]=(object)array(
						'id'	=> sprintf("cpur_archive_%s_%s",$post_type,$role),
						'name'	=> sprintf("cpur_archive_%s[]",$post_type),
						'type'	=> 'checkbox',
						'option_value'=>$role,
						'label'	=> $role_name,
						'el_properties' => array(),
						'save_option'=>true,
						'load_option'=>true
					);
					/*
					$t[$i]->options[]=(object)array(
						'id'	=> sprintf("cpur_archive_url_%s_%s",$post_type,$role),
						'name'	=> sprintf("cpur_archive_url_%s_%s",$post_type,$role),
						'type'	=> 'text',
						'label'	=> __('Redirect url','pur'),
						'el_properties' => array(),
						'save_option'=>true,
						'load_option'=>true
					);	
					*/					
				}	
			}
		}		
		
		$t[$i]->options[]=(object)array(
			'type'	=> 'subtitle',
			'label'	=> __('Default Redirect Url','pur')
		);	
		
		$t[$i]->options[]=(object)array(
			'id'	=> 'cpur_archive_url_default',
			'name'	=> 'cpur_archive_url_default',
			'type'	=> 'text',
			'label'	=> __('Default Redirect Url','pur'),
			'el_properties' => array(),
			'save_option'=>true,
			'load_option'=>true
		);	
		
		$t[$i]->options[]=(object)array(
				'type'=>'clear'
			);
		$t[$i]->options[]=(object)array(
				'type'	=> 'submit',
				'label'	=> __('Save','sws'),
				'class' => 'button-primary',
				'save_option'=>false,
				'load_option'=>false
			);									

		//------------------
		$i = count($t);
		$t[$i] = (object)array();
		$t[$i]->id 			= 'cpur_metabox'; 
		$t[$i]->label 		= __( 'Access Control Metabox', 'pur' );
		$t[$i]->right_label	= __( 'By User Role', 'pur' );
		$t[$i]->page_title	= __( 'Access Control Metabox', 'pur' );
		$t[$i]->theme_option = true;
		$t[$i]->plugin_option = true;

		$t[$i]->options = array(
			(object)array(
				'id'	=> 'restricted_metabox',
				'type'	=> 'yesno',
				'label'	=> __('Restrict Access Control','pur'),
				'description' => __('Choose yes and check the user roles that should get the Access Control metabox when editing a page/post.  By default all roles with edit capability get the Access Control Metabox.','pur'),
				'el_properties' => array(),
				'save_option'=>true,
				'load_option'=>true
			)			
		);				
		
		foreach($roles as $role => $role_name){
			$t[$i]->options[]=(object)array(
				'id'	=> sprintf("cpur_metabox_%s",$role),
				'name'	=> 'allowed_metabox[]',
				'type'	=> 'checkbox',
				'option_value'=>$role,
				'label'	=> $role_name,
				'el_properties' => array(),
				'save_option'=>true,
				'load_option'=>true
			);
		}			
		
		$t[$i]->options[]=(object)array(
				'type'=>'clear'
			);
		$t[$i]->options[]=(object)array(
				'type'	=> 'submit',
				'label'	=> __('Save','sws'),
				'class' => 'button-primary',
				'save_option'=>false,
				'load_option'=>false
			);	
		/* keep this for when wp have an improved taxonomy exclude argument for wp_query
		$taxonomies = get_taxonomies( array('public'=>1), 'objects' );
		$i = count($t);
		$t[$i] = (object)array();
		$t[$i]->id 			= 'cpur_tax'; 
		$t[$i]->label 		= __('PUR Taxonomies','pur');
		$t[$i]->right_label	= __('PUR Taxonomies','pur');
		$t[$i]->page_title	= __('PUR Taxonomies','pur');
		$t[$i]->theme_option = true;
		$t[$i]->plugin_option = true;

		$t[$i]->options[]=(object)array(
			'type'			=> 'subtitle',
			'label'			=> __('Link taxonomies to PUR','pur'),
			'description'	=> sprintf('<p>%s</p><p>%s</p>',
				__('Check the taxonomies that you want to control with PUR.','pur'),
				__('The checked taxonomies will have the user role checkbox when editing a term.','pur')
			)
		);			
		
		if( is_array($taxonomies) && count($taxonomies)>0 ){
			foreach( $taxonomies as $taxonomy => $tax ){
				$t[$i]->options[]=(object)array(
					'id'	=> sprintf("cpur_taxonomies_%s",$taxonomy),
					'name'	=> 'cpur_taxonomies[]',
					'type'	=> 'checkbox',
					'option_value'=>$taxonomy,
					'default'	=> ('category'==$taxonomy?$taxonomy:''),
					'label'	=> $tax->labels->name,
					'el_properties' => array(),
					'save_option'=>true,
					'load_option'=>true
				);				
			}
		}	
		*/ 
		//------------------
		$i = count($t);
		$t[$i] = (object)array();
		$t[$i]->id 			= 'cpur_invert'; 
		$t[$i]->label 		= __( 'Enable for wp-admin', 'pur' );
		$t[$i]->right_label	= __( 'Invert PUR functionality', 'pur' );
		$t[$i]->page_title	= __( 'Enable for wp-admin', 'pur' );
		$t[$i]->theme_option = true;
		$t[$i]->plugin_option = true;

		$t[$i]->options = array(
			(object)array(
				'id'	=> 'inverted_pur',
				'type'	=> 'yesno',
				'label'	=> __('Inverted PUR','pur'),
				'description' => __('The original behaviour of the plugin is to restrict content in the frontend, but not in the admin.  If you choose yes, the plugin will do the oposite:  restrict posts in the backend, but show them in the frontend.','pur'),
				'el_properties' => array(),
				'save_option'=>true,
				'load_option'=>true
			)			
		);	
		$t[$i]->options[]=(object)array(
				'type'=>'clear'
			);
		$t[$i]->options[]=(object)array(
				'type'	=> 'submit',
				'label'	=> __('Save','sws'),
				'class' => 'button-primary',
				'save_option'=>false,
				'load_option'=>false
			);	
		//------------------
		$i = count($t);
		$t[$i] = (object)array();
		$t[$i]->id 			= 'cpur_advanced'; 
		$t[$i]->label 		= __( 'Advanced Options', 'pur' );
		$t[$i]->right_label	= __( 'Advanced Options', 'pur' );
		$t[$i]->page_title	= __( 'Advanced Options', 'pur' );
		$t[$i]->theme_option = true;
		$t[$i]->plugin_option = true;

		$t[$i]->options = array(
			(object)array(
				'id'	=> 'custom_redirect',
				'type'	=> 'yesno',
				'label'	=> __('Custom Redirect Behaviour','pur'),
				'description'	=> sprintf('<p>%s</p><p>%s</p>',
					__('Choose yes to override the redirect behavior. ','pur'),
					__('When a visitor does not have access to a page, the Raw html content will be sent to the browser.  This can be html, javascript (propery encapsulated with script tags), etc.','rhc')
				),	
				'hidegroup'	=> '#custom_redirect_fields',
				'hidevalues' => array('0'),							
				'el_properties' => array(),
				'save_option'=>true,
				'load_option'=>true
			)			
		);			
		
		$t[$i]->options[]=	(object)array(
				'id'	=> 'custom_redirect_fields',
				'type'=>'div_start'
			);	
		
		$t[$i]->options[]=(object)array(
				'id'			=> 'raw_html',
				'type' 			=> 'textarea',
				'label'			=> __('Raw html on redirect','rhc'),
				'el_properties' => array('rows'=>'15','cols'=>'50'),
				'save_option'=>true,
				'load_option'=>true
			);	
			
		$t[$i]->options[]=	(object)array(
				'type'=>'div_end'
			);
						
		$t[$i]->options[]=(object)array(
				'type'=>'clear'
			);
		$t[$i]->options[]=(object)array(
				'type'	=> 'submit',
				'label'	=> __('Save','sws'),
				'class' => 'button-primary',
				'save_option'=>false,
				'load_option'=>false
			);	

		//------------------
		/*
		$i = count($t);
		$t[$i] = (object)array();
		$t[$i]->id 			= 'cpur_archive'; 
		$t[$i]->label 		= __('Archive PUR','pur');
		$t[$i]->right_label	= __('Archive PUR','pur');
		$t[$i]->page_title	= __('Archive PUR','pur');
		$t[$i]->theme_option = true;
		$t[$i]->plugin_option = true;
		$t[$i]->options = array();

		$t[$i]->options[]=(object)array(
				'type'	=> 'subtitle',
				'label'	=> __('Rectrict Frontend Archive Pages','pur')
			);
		
		$taxonomies = get_taxonomies( array('public'=>true), 'objects');	

		if(is_array($roles)&&count($roles)>0){
			$j=0;
			foreach($taxonomies as $post_type => $pt){
				$tmp=(object)array(
					'type'	=> 'subtitle',
					'label'	=> (@$pt->labels->name?$pt->labels->name:$post_type),
					'save_option'=>false,
					'load_option'=>false
				);
				if($j==0){
					$tmp->description = sprintf('<p>%s</p><p>%s</p><p>%s</p><p>%s</p>',
						__("You can restrict access to taxonomies by checking the user roles that should have access to it.  Please observe that unchecking a role does not mean that it will enable the taxonomy for that role.",'pur'),
						__("If no role is checked for a taxonomy, it will be public."),
						__("If a redirect url is not defined, user will be redirected to site home page."),
						__('This option only applies to the frontend.','pur')
					);
					$tmp->description_rowspan = count($post_types);
				}
				$t[$i]->options[]=$tmp;
				$j++;		
				//-----			
				foreach($roles as $role => $role_name){
					$t[$i]->options[]=(object)array(
						'id'	=> sprintf("cpur_tax_%s_%s",$post_type,$role),
						'name'	=> sprintf("cpur_tax_%s[]",$post_type),
						'type'	=> 'checkbox',
						'option_value'=>$role,
						'label'	=> $role_name,
						'el_properties' => array(),
						'save_option'=>true,
						'load_option'=>true
					);
					
					$t[$i]->options[]=(object)array(
						'id'	=> sprintf("cpur_taxurl_%s_%s",$post_type,$role),
						'name'	=> sprintf("cpur_taxurl_%s_%s",$post_type,$role),
						'type'	=> 'text',
						'label'	=> __('Redirect url','pur'),
						'el_properties' => array(),
						'save_option'=>true,
						'load_option'=>true
					);					
				}			
				//-----			

			}
		}

	
		$t[$i]->options[]=(object)array(
				'type'=>'clear'
			);
		$t[$i]->options[]=(object)array(
				'type'	=> 'submit',
				'label'	=> __('Save','sws'),
				'class' => 'button-primary',
				'save_option'=>false,
				'load_option'=>false
			);	
		*/
		
		// Help
		$i = count( $t );
		$t[ $i ]                = (object) array();
		$t[ $i ]->id            = 'cpur_help';
		$t[ $i ]->label         = __( 'Help', 'pur' );
		$t[ $i ]->right_label   = __( 'Help', 'pur' );
		$t[ $i ]->page_title    = __( 'Help', 'pur' );
		$t[ $i ]->theme_option  = true;
		$t[ $i ]->plugin_option = true;
		$t[ $i ]->priority_end  = 10;
		$t[ $i ]->options       = array();

		$t[ $i ]->options[] = (object) array(
			'type'     => 'callback',
			'callback' => array( $this, 'help_tab_cb' ),
		);

		return $t;
	}

	function help_tab_cb() {
		return sprintf( '%s<br><br>',
			sprintf( __( 'For detailed information on how to use the Pages by User Role, please visit our knowledge base at the %s', 'pur' ),
				sprintf( '<a href="https://righthere.zendesk.com/">%s</a>.', __( 'Help Center', 'pur' )
				)
			)
		);
	}
}
?>