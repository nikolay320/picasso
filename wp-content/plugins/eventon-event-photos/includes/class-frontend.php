<?php
/**
 * Event Photos front end class
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	eventon-photos/classes
 * @version     0.1
 */
class evoep_front{
	
	function __construct(){
		global $eventon_ep;

		include_once('class-functions.php');
		$this->functions = new evoep_functions();

		add_filter('eventon_eventCard_evoep', array($this, 'frontend_box'), 10, 2);
		add_filter('eventon_eventcard_array', array($this, 'eventcard_array'), 10, 4);
		add_filter('evo_eventcard_adds', array($this, 'eventcard_adds'), 10, 1);

		// scripts and styles 
		add_action( 'init', array( $this, 'register_styles_scripts' ) ,15);	

		$this->opt = $eventon_ep->opt;
		$this->opt2 = $eventon_ep->opt2;

		add_action('wp_footer', array($this, 'footer_content'));

	}

	// STYLES: for photos 
		public function register_styles_scripts(){
			global $eventon_ep;			
			wp_register_style( 'evoep_styles',$eventon_ep->plugin_url.'/assets/EP_styles.css');
			wp_register_style( 'photoswipe',$eventon_ep->plugin_url.'/assets/css/photoswipe.css');

			$skin = (!empty($eventon_ep->opt['evoEP_skin']))? $eventon_ep->opt['evoEP_skin']:'default';
			wp_register_style( 'skin',$eventon_ep->plugin_url.'/assets/css/skins/'.$skin.'-skin/'.$skin.'-skin.css');
			
			wp_register_script('photoswipe',$eventon_ep->plugin_url.'/assets/js/photoswipe.min.js', array('jquery'), $eventon_ep->version, true );
			wp_register_script('photoswipe_ui',$eventon_ep->plugin_url.'/assets/js/photoswipe-ui-default.min.js', array('jquery'), $eventon_ep->version, true );
			wp_register_script('evoep_script',$eventon_ep->plugin_url.'/assets/EP_script.js', array('jquery'), $eventon_ep->version, true );
			
			$this->print_scripts();
			add_action( 'wp_enqueue_scripts', array($this,'print_styles' ));				
		}
		public function print_scripts(){
			wp_enqueue_script('photoswipe');	
			wp_enqueue_script('photoswipe_ui');	
			wp_enqueue_script('evoep_script');	
		}
		function print_styles(){
			wp_enqueue_style( 'photoswipe');	
			wp_enqueue_style( 'skin');	
			wp_enqueue_style( 'evoep_styles');	
		}

	// photos EVENTCARD form HTML
		// add photos box to front end
			function frontend_box($object, $helpers){
				global $eventon_ep;
				$event_pmv = get_post_custom($object->event_id);
					
				// photos enabled for this event
					if(empty($event_pmv['event_photos']) || (!empty($event_pmv['event_photos']) && $event_pmv['event_photos'][0]=='no') ) return;

					$evoep_images = !empty($event_pmv['evoep_images'])? $event_pmv['evoep_images'][0]:false;

					if(!$evoep_images) return;

				$lang = (!empty($eventon->evo_generator->shortcode_args['lang'])? $eventon->evo_generator->shortcode_args['lang']:'L1');

				$output = '';

				$output.=  "<div class='evorow evcal_evdata_row bordb evcal_evrow_ep evo_metarow_photo".$helpers['end_row_class']."' data-event_id='".$object->event_id."' data-lang='{$lang}'>
							<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__evoEP_001', 'fa-photo',$helpers['evOPT'] )."'></i></span>
							<div class='evcal_evdata_cell'>							
								<h3 class='evo_h3'>".evo_lang('Event Photos',$lang,$this->opt2)."</h3>
								<div class='evoep_image_collection evo-gallery'>";
					
					if($evoep_images){
						$imgs = explode(',', $evoep_images);
						$imgs = array_filter($imgs);
						$count =1;
						foreach($imgs as $img){
							$caption = get_post_field('post_excerpt',$img);
							$url = wp_get_attachment_thumb_url($img);
							$bigimg = wp_get_attachment_image_src($img, 'full');
							//print_r($bigimg);
							$medimg = wp_get_attachment_image_src($img, 'medium');
								$bigimg = ($bigimg)? $bigimg: null;
								$medimg = ($medimg)? $medimg: null;
							//print_r($attachment);
							
							$thumbsize = !empty($this->opt['evoEP_thumb'])? ' thumb'.$this->opt['evoEP_thumb']: '';
							
							$output .= '<a href="'.($bigimg? $bigimg[0]:'').'" data-size="'.($bigimg? $bigimg[1].'x'.$bigimg[2]:'').'" data-med="'.($medimg? $medimg[0]:'').'" data-med-size="'.($medimg? $medimg[1].'x'.$medimg[2]:'').'" data-author="" class="'.($count==1? 'evo-gallery__img--main':'').$thumbsize.'" title="'.$caption.'">
								  <img src="'.$url.'"  />
								  <figure>'.$caption.'</figure>
								</a>';

							$count ++;
						}

					}
					
					
				$output.=  "</div></div>".$helpers['end'];
				$output.=  "</div>";
				return $output;
			}
		
		// add eventon photos to eventcard field to filter
			function eventcard_array($array, $pmv, $eventid, $__repeatInterval){
				$array['evoep']= array(
					'event_id' => $eventid,
					'__repeatInterval'=>(!empty($__repeatInterval)? $__repeatInterval:0)
				);
				return $array;
			}
			function eventcard_adds($array){
				$array[] = 'evoep';
				return $array;
			}

	// footer content
		function footer_content(){
			?>
			<div id="evo-gallery" class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
			    <div class="pswp__bg"></div>
			    <div class="pswp__scroll-wrap">
			      <div class="pswp__container">
					<div class="pswp__item"></div>
					<div class="pswp__item"></div>
					<div class="pswp__item"></div>
			      </div>

			      <div class="pswp__ui pswp__ui--hidden">

			        <div class="pswp__top-bar">

						<div class="pswp__counter"></div>
						<div class="pswp__button pswp__button--close" title="Close (Esc)"></div>
						<div class="pswp__button pswp__button--share" title="Share"></div>
						<div class="pswp__button pswp__button--fs" title="Toggle fullscreen"></div>
						<div class="pswp__button pswp__button--zoom" title="Zoom in/out"></div>
						<div class="pswp__preloader">
							<div class="pswp__preloader__icn">
							  <div class="pswp__preloader__cut">
							    <div class="pswp__preloader__donut"></div>
							  </div>
							</div>
						</div>
			        </div>
					<div class="pswp__loading-indicator"><div class="pswp__loading-indicator__line"></div></div>
			        <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
			            <div class="pswp__share-tooltip">
							<a href="#" class="pswp__share--facebook"></a>
							<a href="#" class="pswp__share--twitter"></a>
							<a href="#" class="pswp__share--pinterest"></a>
							<a href="#" download class="pswp__share--download"></a>
			            </div>
			        </div>
			        <div class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></div>
			        <div class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></div>
			        <div class="pswp__caption">
			          <div class="pswp__caption__center">
			          </div>
			        </div>
			      </div>
			    </div>
			</div>

			<?php
		}

	// SUPPORT functions	
		// RETURN: language
			function lang($variable, $default_text){
				global $eventon_ep;
				return $eventon_ep->lang($variable, $default_text);
			}
		// function replace event name from string
			function replace_en($string){
				return str_replace('[event-name]', "<span class='eventName'>Event Name</span>", $string);
			}		
		
	    
}
