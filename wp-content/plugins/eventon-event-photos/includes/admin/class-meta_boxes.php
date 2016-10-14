<?php
/**
 * Meta boxes for event photos
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	evoep/Admin/
 * @version     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evoep_metaboxes{
	public function __construct(){
		add_action( 'add_meta_boxes', array($this,'add_meta_boxes') );
		add_action( 'eventon_save_meta', array($this,'save_meta_data'), 10 , 2 );
	}
	function add_meta_boxes(){
		add_meta_box('evoep_mb1','Event Photos', array($this,'metabox_content'),'ajde_events', 'normal', 'high');
	}

	function metabox_content(){
		global $post, $eventon_ep, $eventon;
		$pmv = get_post_meta($post->ID);
		wp_nonce_field( plugin_basename( __FILE__ ), 'evoep_nonce' );
		
		$event_photos = (!empty($pmv['event_photos']) && $pmv['event_photos'][0]=='yes')? 'yes': 'no';
		//$datetime = new evo_datetime();
		//$repeatIntervals = !empty($pmv['repeat_intervals'])? unserialize($pmv['repeat_intervals'][0]): false;

		$wp_date_format = get_option('date_format');
	?>
	<div class='eventon_mb'>
	<div class="evoep">
		<p class='yesno_leg_line ' style='padding:10px'>
			<?php echo eventon_html_yesnobtn(array(
				'var'=>$event_photos, 
				'id'=>'event_photos',
				'label'=>__('Activate photos for this event','eventon'),
				'input'=> true,
				'attr'=>array('afterstatement'=>'evoep_details')
			)); ?>
		</p>
		<div id='evoep_details' class='evors_details evomb_body ' <?php echo ( $event_photos=='yes')? null:'style="display:none"'; ?>>		
			<div class="evoep_image_gallery">
				<p><?php _e('Select Images for the gallery','eventon');?></p>
				<div class='evoep_images'>
					<input type="hidden" name='evoep_images' value='<?php echo !empty($pmv['evoep_images'])? $pmv['evoep_images'][0]:'';?>' class='evpep_img_ids'/>
					<div class='evpep_img_holder'>
						<?php
						$evoep_images = !empty($pmv['evoep_images'])? $pmv['evoep_images'][0]:false;
						if($evoep_images){
							$imgs = explode(',', $evoep_images);
							$imgs = array_filter($imgs);
							foreach($imgs as $img){
								$caption = get_post_field('post_excerpt',$img);
								$url = wp_get_attachment_thumb_url($img);
								//print_r($attachment);
								echo "<span data-imgid='{$img}'><b class='remove_evoep'>X</b><img title='{$caption}' data-imgid='{$img}' src='{$url}'></span>";
							}

						}
						?>
					</div>
				</div>
				<p><input id='evoep_select_images' type='button' class='button' value='<?php _e('Add Image','eventon');?>'/></p>				
			</div>			
		</div>
	</div>
	</div>
	<?php
	}

	// Save the data from meta box
	function save_meta_data($arr, $post_id){
		$fields = array(
			'event_photos', 'event_photos',
			'evoep_images', 'evoep_images',
		);
		foreach($fields as $field){
			if(!empty($_POST[$field])){
				update_post_meta( $post_id, $field, $_POST[$field] );
			}else{
				delete_post_meta($post_id, $field);
			}
		}			
	}
}