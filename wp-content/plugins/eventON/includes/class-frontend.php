<?php
/**
 * evo_frontend class for front and backend.
 *
 * @class 		evo_frontend
 * @version		2.4.2
 * @package		EventON/Classes
 * @category	Class
 * @author 		AJDE
 */

class evo_frontend {

	private $content;
	public $evo_options;

	public function __construct(){
		global $eventon;

		// eventon related wp options access on frontend
		$this->evo_options = get_option('evcal_options_evcal_1');
		$this->evo_lang_opt = get_option('evcal_options_evcal_2');

		// hooks for frontend
		add_action( 'init', array( $this, 'register_scripts' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_default_evo_styles' ), 10 );
		add_action( 'wp_head', array( $this, 'load_dynamic_evo_styles' ), 50 );

		$this->evopt1 = $eventon->evo_generator->evopt1;

		if(empty($this->evopt1['evcal_header_generator']) || (!empty($this->evopt1['evcal_header_generator']) && $this->evopt1['evcal_header_generator']!='yes')){
			add_action( 'wp_head', array( $this, 'generator' ) );
		}

		// schedule deleting past events
			add_action('evo_trash_past_events', array($this, 'evo_perform_trash_past_events'));	
	}

	// styles and scripts
		public function register_scripts() {
			global $eventon;
			
			$evo_opt= $this->evo_options;			
			
			// Google gmap API script -- loadded from class-calendar_generator.php	
			wp_register_script('evo_mobile',$eventon->assets_path.'js/jquery.mobile.min.js', array('jquery'), $eventon->version, true ); // 2.2.17
			wp_register_script('evcal_easing', $eventon->assets_path. 'js/jquery.easing.1.3.js', array('jquery'),'1.0',true );//2.2.24
			wp_register_script('evcal_functions', $eventon->assets_path. 'js/eventon_functions.js', array('jquery'), $eventon->version ,true );// 2.2.22
			wp_register_script('evcal_ajax_handle', $eventon->assets_path. 'js/eventon_script.js', array('jquery'), $eventon->version ,true );
			wp_localize_script( 
				'evcal_ajax_handle', 
				'the_ajax_script', 
				array( 
					'ajaxurl' => admin_url( 'admin-ajax.php' ) , 
					'postnonce' => wp_create_nonce( 'eventon_nonce' )
				)
			);

			// google maps	
			wp_register_script('eventon_gmaps', $eventon->assets_path. 'js/maps/eventon_gen_maps.js', array('jquery'), $eventon->version ,true );	
			wp_register_script('eventon_gmaps_blank', $eventon->assets_path. 'js/maps/eventon_gen_maps_none.js', array('jquery'), $eventon->version ,true );	
			wp_register_script('eventon_init_gmaps', $eventon->assets_path. 'js/maps/eventon_init_gmap.js', array('jquery'),'1.0',true );
			wp_register_script( 'eventon_init_gmaps_blank', $eventon->assets_path. 'js/maps/eventon_init_gmap_blank.js', array('jquery'), $eventon->version ,true ); // load a blank initiate gmap javascript

			$apikey = !empty($evo_opt['evo_gmap_api_key'])? '?key='.$evo_opt['evo_gmap_api_key'] :'';
			wp_register_script( 'evcal_gmaps', apply_filters('eventon_google_map_url', 'https://maps.googleapis.com/maps/api/js'.$apikey), array('jquery'),'1.0',true);

			// STYLES
			wp_register_style('evo_font_icons',$eventon->assets_path.'fonts/font-awesome.css','','4.6.2');		
			
			// Defaults styles and dynamic styles
			wp_register_style('evcal_cal_default',$eventon->assets_path.'css/eventon_styles.css', array(), $eventon->version);	
			//wp_register_style('evo_dynamic_css', admin_url('admin-ajax.php').'?action=evo_dynamic_css');


			global $is_IE;
			if ( $is_IE ) {
				wp_register_style( 'ieStyle', $eventon->assets_path.'css/ie.css', array(), '1.0' );
				wp_enqueue_style( 'ieStyle' );
			}

			// LOAD custom google fonts for skins	
			//$gfonts = (is_ssl())? 'https://fonts.googleapis.com/css?family=Oswald:400,300|Open+Sans:400,300': 'http://fonts.googleapis.com/css?family=Oswald:400,300|Open+Sans:400,300';	
			$gfonts="//fonts.googleapis.com/css?family=Oswald:400,300|Open+Sans:400,300";
			wp_register_style( 'evcal_google_fonts', $gfonts, '', '', 'screen' );
			
			$this->register_evo_dynamic_styles();
		}
		public function register_evo_dynamic_styles(){
			global $eventon;
			$opt= $this->evo_options;
			if(!empty($opt['evcal_css_head']) && $opt['evcal_css_head'] =='no' || empty($opt['evcal_css_head'])){
				if(is_multisite()) {
					$uploads = wp_upload_dir();
					wp_register_style('eventon_dynamic_styles', $uploads['baseurl'] . '/eventon_dynamic_styles.css', 'style');
				} else {
					wp_register_style('eventon_dynamic_styles', 
						$eventon->assets_path. 'css/eventon_dynamic_styles.css', 'style');
				}
			}
		}
		
		public function load_dynamic_evo_styles(){
			$opt= $this->evo_options;
			if(!empty($opt['evcal_css_head']) && $opt['evcal_css_head'] =='yes'){
				
				$dynamic_css = get_option('evo_dyn_css');
				if(!empty($dynamic_css)){
					echo '<style type ="text/css">'.$dynamic_css.'</style>';
				}				
			}else{
				wp_enqueue_style( 'eventon_dynamic_styles');
			}
		}
		public function load_default_evo_scripts(){
			//wp_enqueue_script('add_to_cal');
			wp_enqueue_script('evcal_functions');
			wp_enqueue_script('evo_mobile');
			wp_enqueue_script('evcal_ajax_handle');			
			
			do_action('eventon_enqueue_scripts');

			// map enqueueing is done in calendar shell files
			
		}
		public function load_default_evo_styles(){
			$opt= $this->evo_options;
			if(empty($opt['evo_googlefonts']) || $opt['evo_googlefonts'] =='no')
				wp_enqueue_style( 'evcal_google_fonts' );

			wp_enqueue_style( 'evcal_cal_default');	
			if(empty($opt['evo_fontawesome']) || $opt['evo_fontawesome'] =='no')
				wp_enqueue_style( 'evo_font_icons' );
		}
		public function load_evo_scripts_styles(){
			$this->load_default_evo_scripts();
			$this->load_default_evo_styles();
		}
		public function evo_styles(){
			add_action('wp_head', array($this, 'load_default_evo_scripts'));
		}

	// check if members only
		function is_member_only($shortcode_args){
			 return ( 
			 	($shortcode_args['members_only']=='yes' && is_user_logged_in()) ||
			 	$shortcode_args['members_only']=='no' || empty($shortcode_args['members_only'])
			 )? true: false;
		}
		function nonMemberCalendar(){
			return __('You must login first to see calendar','eventon');
		}

	// language
		function lang($evo_options = '', 
			$field, 
			$default_val, 
			$lang = ''
		){
			global $eventon;
				
			$evo_options = (!empty($evo_options))? $evo_options: $this->evo_lang_opt;
			
			// check for language preference
			if(!empty($lang)){
				$_lang_variation = $lang;
			}else{
				$shortcode_arg = $eventon->evo_generator->shortcode_args;
				$_lang_variation = (!empty($shortcode_arg['lang']))? $shortcode_arg['lang']:'L1';
			}
			
			$new_lang_val = (!empty($evo_options[$_lang_variation][$field]) )?
				stripslashes($evo_options[$_lang_variation][$field]): $default_val;
				
			return $new_lang_val;
		}

	// Event Type Taxonomies
		function get_localized_event_tax_names($lang='', $options='', $options2=''
		){
			$output ='';

			$options = (!empty($options))? $options: get_option('evcal_options_evcal_1');
			$options2 = (!empty($options2))? $options2: get_option('evcal_options_evcal_2');
			$_lang_variation = (!empty($lang))? $lang:'L1';

			
			// foreach event type upto activated event type categories
			for( $x=1; $x< (evo_get_ett_count($options)+1); $x++){
				$ab = ($x==1)? '':$x;

				$_tax_lang_field = 'evcal_lang_et'.$x;

				// check on eventon language values for saved name
				$lang_name = (!empty($options2[$_lang_variation][$_tax_lang_field]))? 
					stripslashes($options2[$_lang_variation][$_tax_lang_field]): null;

				// conditions
				if(!empty($lang_name)){
					$output[$x] = $lang_name;
				}else{
					$output[$x] = (!empty($options['evcal_eventt'.$ab]))? $options['evcal_eventt'.$ab]:'Event Type '.$ab;
				}			
			}
			return $output;
		}
		function get_localized_event_tax_names_by_slug($slug, $lang=''){
			$options = get_option('evcal_options_evcal_1');
			$options2 = get_option('evcal_options_evcal_2');
			$_lang_variation = (!empty($lang))? $lang:'L1';

			// initial values
			$x = ($slug=='event_type')?'1': (substr($slug,-1));
			$ab = ($x==1)? '':$x;
			$_tax_lang_field = 'evcal_lang_et'.$x;

			// check on eventon language values for saved name
			$lang_name = (!empty($options2[$_lang_variation][$_tax_lang_field]))? 
				stripslashes($options2[$_lang_variation][$_tax_lang_field]): null;

			// conditions
			if(!empty($lang_name)){
				return $lang_name;
			}else{
				return (!empty($options['evcal_eventt'.$ab]))? $options['evcal_eventt'.$ab]:'Event Type '.$ab;
			}	

		}

	// Schedule 
	// initiated in install
		function evo_perform_trash_past_events(){

			if(empty($this->evopt1['evcal_move_trash']) || $this->evopt1['evcal_move_trash']!= 'yes') return;

			eventon_trash_past_events();
			//$pp = get_post_meta(483, 'aa', true);
			//$pr = !empty($pp)? $pp+1:1;
			//update_post_meta(483, 'aa2', $pr);
		}

	// EMAILING	
		// get email parts
			public function get_email_part($part){
				global $eventon;

				$file_name = 'email_'.$part.'.php';

				$paths = array(
					0=> TEMPLATEPATH.'/'.$eventon->template_url.'templates/email/',
					1=> AJDE_EVCAL_PATH.'/templates/email/',
				);

				foreach($paths as $path){				
					if(file_exists($path.$file_name) ){	
						$template = $path.$file_name;	
						break;
					}//echo($path.$file_name.'<br/>');
				}

				ob_start();

				include($template);

				return ob_get_clean();
			}

		// Get email body parts
		// to pull full email templates
			public function get_email_body($part, $def_location, $args='', $paths=''){
				global $eventon;

				ob_start();

				$file_location = EVO()->template_locator(
					$part.'.php', 
					$def_location,
					'templates/email/'
				);
				include($file_location);

				return ob_get_clean();
				
				/*
				$file_name = $part.'.php';
				global $eventon;

				if(empty($paths) && !is_array($paths)){
					$paths = array(
						0=> TEMPLATEPATH.'/'.$eventon->template_url.'templates/email/',
						1=> $def_location,
					);
				}

				foreach($paths as $path){	
					// /echo $path.$file_name.'<br/>';			
					if(file_exists($path.$file_name) ){	
						$template = $path.$file_name;	
						break;
					}
				}

				ob_start();

				if($template)
					include($template);

				return ob_get_clean();
				*/
			}
	// front-end website
		/** Output generator to aid debugging. */
			public function generator() {
				global $eventon;
				echo "\n\n" . '<!-- EventON Version -->' . "\n" . '<meta name="generator" content="EventON ' . esc_attr( $eventon->version ) . '" />' . "\n\n";
			}

	// CONTENT FILTERING
		function filter_evo_content($str){

			if(empty($this->evo_options['evo_content_filter']) || $this->evo_options['evo_content_filter']=='evo'){
				$str = wptexturize($str);
				$str = convert_smilies($str);
				$str = convert_chars($str);
				$str = wpautop($str);
				$str = shortcode_unautop($str);
				$str = prepend_attachment($str);
				$str = do_shortcode($str);
				return $str;
			}elseif($this->evo_options['evo_content_filter']=='def'){
				return apply_filters('the_content', $str);
				
			}else{// no filter at all
				return $str;
			}
			
		}
}