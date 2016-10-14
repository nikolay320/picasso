<?php
/**
 * Meta boxes for ajde_events
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	EventON/Admin/ajde_events
 * @version     2.3.20
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evo_event_metaboxes{
	public function __construct(){
		add_action( 'add_meta_boxes', array($this,'metabox_init') );
		add_action( 'save_post', array($this,'eventon_save_meta_data'), 1, 2 );
		add_action( 'post_submitbox_misc_actions', array($this,'ajde_events_settings_per_post' ));
	}

	// INIT meta boxes
		function metabox_init(){

			$evcal_opt1= get_option('evcal_options_evcal_1');

			// ajde_events meta boxes
			add_meta_box('ajdeevcal_mb2',__('Event Color','eventon'), array($this,'ajde_evcal_show_box_2'),'ajde_events', 'side', 'core');
			add_meta_box('ajdeevcal_mb1', __('Event Details','eventon'), array($this,'ajde_evcal_show_box'),'ajde_events', 'normal', 'high');	
			
			// if third party is enabled
			if(!empty($evcal_opt1['evcal_paypal_pay']) && $evcal_opt1['evcal_paypal_pay']=='yes' )
				add_meta_box('ajdeevcal_mb3','Third Party Settings', array($this,'ajde_evcal_show_box_3'),'ajde_events', 'normal', 'core');
			
			do_action('eventon_add_meta_boxes');
		}

	// EXTRA event settings for the page
		function ajde_events_settings_per_post(){
			global $post, $eventon, $ajde;

			if ( ! is_object( $post ) ) return;

			if ( $post->post_type != 'ajde_events' ) return;

			if ( isset( $_GET['post'] ) ) {

				$event_pmv = get_post_custom($post->ID);

				$evo_exclude_ev = evo_meta($event_pmv, 'evo_exclude_ev');
				$_featured = evo_meta($event_pmv, '_featured');
				$_cancel = evo_meta($event_pmv, '_cancel');
				$_onlyloggedin = evo_meta($event_pmv, '_onlyloggedin');
				$_completed = evo_meta($event_pmv, '_completed');
			?>
				<div class="misc-pub-section" >
				<div class='evo_event_opts'>
					<p class='yesno_row evo'>
						<?php 	echo $ajde->wp_admin->html_yesnobtn(
							array(
								'id'=>'evo_exclude_ev', 
								'var'=>$evo_exclude_ev,
								'input'=>true,
								'label'=>__('Exclude from calendar','eventon'),
								'guide'=>__('Set this to Yes to hide event from showing in all calendars','eventon'),
								'guide_position'=>'L'
							));
						?>
					</p>
					<p class='yesno_row evo'>
						<?php 	echo $ajde->wp_admin->html_yesnobtn(
							array(
								'id'=>'_featured', 
								'var'=>$_featured,
								'input'=>true,
								'label'=>__('Featured Event','eventon'),
								'guide'=>__('Make this event a featured event','eventon'),
								'guide_position'=>'L'
							));
						?>	
					</p>
					<p class='yesno_row evo'>
						<?php 	echo $ajde->wp_admin->html_yesnobtn(
							array(
								'id'=>'_completed', 
								'var'=>$_completed,
								'input'=>true,
								'label'=>__('Event Completed','eventon'),
								'guide'=>__('Mark this event as completed','eventon'),
								'guide_position'=>'L'
							));
						?>	
					</p>
					<p class='yesno_row evo'>
						<?php 	echo $ajde->wp_admin->html_yesnobtn(
							array(
								'id'=>'_cancel', 
								'var'=>$_cancel,
								'input'=>true,
								'label'=>__('Cancel Event','eventon'),
								'guide'=>__('Cancel this event','eventon'),
								'guide_position'=>'L',
								'attr'=>array('afterstatement'=>'evo_editevent_cancel_text')
							));
						?>	
					</p><p class='yesno_row evo'>
						<?php 	echo $ajde->wp_admin->html_yesnobtn(
							array(
								'id'=>'_onlyloggedin', 
								'var'=>$_onlyloggedin,
								'input'=>true,
								'label'=>__('Only for loggedin users','eventon'),
								'guide'=>__('This will make this event only visible if the users are loggedin to this site','eventon'),
								'guide_position'=>'L',
							));
						?>	
					</p>
					<?php
						$_cancel_reason = evo_meta($event_pmv,'_cancel_reason');
					?>
					<p id='evo_editevent_cancel_text' style='display:<?php echo (!empty($_cancel) && $_cancel=='yes')? 'block':'none';?>'><textarea name="_cancel_reason" style='width:100%' rows="3" placeholder='<?php _e('Type the reason for cancelling','eventon');?>'><?php echo $_cancel_reason;?></textarea></p>
					<?php
						// @since 2.2.28
						do_action('eventon_event_submitbox_misc_actions',$post->ID, $event_pmv);
					?>
				</div>
				</div>
			<?php
			}
		}
	
	/** Event Color Meta box. */	
		function ajde_evcal_show_box_2(){
				
			// Use nonce for verification
			wp_nonce_field( plugin_basename( __FILE__ ), 'evo_noncename_2' );
			$p_id = get_the_ID();
			$ev_vals = get_post_custom($p_id);
			
			$evOpt = get_option('evcal_options_evcal_1');

		?>		
				<table id="meta_tb2" class="form-table meta_tb" >
				<tr>
					<td>
					<?php
						// Hex value cleaning
						$hexcolor = eventon_get_hex_color($ev_vals,'', $evOpt );	
					?>			
					<div id='color_selector' >
						<em id='evColor' style='background-color:<?php echo (!empty($hexcolor) )? $hexcolor: 'na'; ?>'></em>
						<p class='evselectedColor'>
							<span class='evcal_color_hex evcal_chex'  ><?php echo (!empty($hexcolor) )? $hexcolor: 'Hex code'; ?></span>
							<span class='evcal_color_selector_text evcal_chex'><?php _e('Click here to pick a color');?></span>
						</p>
					</div>
					<p style='margin-bottom:0; padding-bottom:0'><i><?php _e('OR Select from other colors','eventon');?></i></p>
					
					<div id='evcal_colors'>
						<?php 
						
							$other_events = get_posts(array(
								'posts_per_page'=>-1,
								'post_type'=>'ajde_events',
								'meta_key' => 'evcal_event_color'
							));
							
							$other_colors='';
							
							foreach($other_events as $ev){ setup_postdata($ev);
								$this_id = $ev->ID;
								
								$hexval = get_post_meta($this_id,'evcal_event_color',true);
								$hexval_num = get_post_meta($this_id,'evcal_event_color_n',true);
								
								
								// hex color cleaning
								$hexval = ($hexval[0]=='#')? substr($hexval,1):$hexval;
								
								
								if(!empty( $hexval) && (empty($other_colors) || (is_array($other_colors) && !in_array($hexval, $other_colors)	)	)	){
									echo "<div class='evcal_color_box' style='background-color:#".$hexval."'color_n='".$hexval_num."' color='".$hexval."'></div>";
									
									$other_colors[]=$hexval;
								}				
							}
							
						?>				
					</div>
					<div class='clear'></div>
					
					
					
					<input id='evcal_event_color' type='hidden' name='evcal_event_color' 
						value='<?php echo str_replace('#','',$hexcolor); ?>'/>
					<input id='evcal_event_color_n' type='hidden' name='evcal_event_color_n' 
						value='<?php echo (!empty($ev_vals["evcal_event_color_n"]) )? $ev_vals["evcal_event_color_n"][0]: null ?>'/>
					</td>
				</tr>
				<?php do_action('eventon_metab2_end'); ?>
				</table>
		<?php }

	// MAIN META BOX CONTENT
		function ajde_evcal_show_box(){
			global $eventon, $ajde;
			
			$evcal_opt1= get_option('evcal_options_evcal_1');
			$evcal_opt2= get_option('evcal_options_evcal_2');
			
			// Use nonce for verification
			wp_nonce_field( plugin_basename( __FILE__ ), 'evo_noncename' );
			
			// The actual fields for data entry
			$p_id = get_the_ID();
			$ev_vals = get_post_custom($p_id);
			
			
			$evcal_allday = (!empty($ev_vals["evcal_allday"]))? $ev_vals["evcal_allday"][0]:null;		
			$show_style_code = ($evcal_allday=='yes') ? "style='display:none'":null;

			$select_a_arr= array('AM','PM');
			
			// --- TIME variations
			$evcal_date_format = eventon_get_timeNdate_format($evcal_opt1);
			$time_hour_span= ($evcal_date_format[2])?25:13;
			
			
			// GET DATE and TIME values
			$_START=(!empty($ev_vals['evcal_srow'][0]))?
				eventon_get_editevent_kaalaya($ev_vals['evcal_srow'][0],$evcal_date_format[1], $evcal_date_format[2]):false;
			$_END=(!empty($ev_vals['evcal_erow'][0]))?
				eventon_get_editevent_kaalaya($ev_vals['evcal_erow'][0],$evcal_date_format[1], $evcal_date_format[2]):false;
			
			
		// array of all meta boxes
			$metabox_array = apply_filters('eventon_event_metaboxs', array(
				array(
					'id'=>'ev_subtitle',
					'name'=>__('Event SubTitle','eventon'),
					'variation'=>'customfield',	
					'hiddenVal'=>'',	
					'iconURL'=>'fa-pencil',
					'iconPOS'=>'',
					'type'=>'code',
					'content'=>'',
					'slug'=>'ev_subtitle'
				),array(
					'id'=>'ev_timedate',
					'name'=>__('Time and Date','eventon'),	
					'hiddenVal'=>'',	
					'iconURL'=>'fa-clock-o','variation'=>'customfield','iconPOS'=>'',
					'type'=>'code',
					'content'=>'',
					'slug'=>'ev_timedate'
				),array(
					'id'=>'ev_location',
					'name'=>__('Location and Venue','eventon'),	
					'hiddenVal'=>'',	
					'iconURL'=>'fa-map-marker','variation'=>'customfield','iconPOS'=>'',
					'type'=>'code',
					'content'=>'',
					'slug'=>'ev_location',
					'guide'=>''
				),array(
					'id'=>'ev_organizer',
					'name'=>__('Organizer','eventon'),	
					'hiddenVal'=>'',	
					'iconURL'=>'fa-microphone','variation'=>'customfield','iconPOS'=>'',
					'type'=>'code',
					'content'=>'',
					'slug'=>'ev_organizer'
				),array(
					'id'=>'ev_uint',
					'name'=>__('User Interaction for event click','eventon'),	
					'hiddenVal'=>'',	
					'iconURL'=>'fa-street-view','variation'=>'customfield','iconPOS'=>'',
					'type'=>'code',
					'content'=>'',
					'slug'=>'ev_uint',
					'guide'=>'This define how you want the events to expand following a click on the eventTop by a user'
				),array(
					'id'=>'ev_learnmore',
					'name'=>__('Learn more about event link','eventon'),	
					'hiddenVal'=>'',	
					'iconURL'=>'fa-random','variation'=>'customfield','iconPOS'=>'',
					'type'=>'code',
					'content'=>'',
					'slug'=>'ev_learnmore',
					'guide'=>'This will create a learn more link in the event card. Make sure your links start with http://'
				)
			));

		// Custom Meta fields for events
			$num = evo_calculate_cmd_count($evcal_opt1);
			for($x =1; $x<=$num; $x++){	
				if(!eventon_is_custom_meta_field_good($x)) continue;

				$fa_icon_class = $evcal_opt1['evcal__fai_00c'.$x];
				$visibility_type = (!empty($evcal_opt1['evcal_ec_f'.$x.'a4']) )? $evcal_opt1['evcal_ec_f'.$x.'a4']:'all' ;
				$metabox_array[] = array(
					'id'=>'evcal_ec_f'.$x.'a1',
					'variation'=>'customfield',
					'name'=>$evcal_opt1['evcal_ec_f'.$x.'a1'],		
					'iconURL'=>$fa_icon_class,
					'iconPOS'=>'',
					'fieldtype'=>'custommetafield',
					'x'=>$x,
					'visibility_type'=>$visibility_type,
					'type'=>'code',
					'content'=>'',
					'slug'=>'evcal_ec_f'.$x.'a1'
				);
			}
		
		// combine array with custom fields
		// $metabox_array = (!empty($evMB_custom) && count($evMB_custom)>0)? array_merge($metabox_array , $evMB_custom): $metabox_array;
		
		$closedmeta = eventon_get_collapse_metaboxes($p_id);
		
		//print_r($closedmeta);
	?>	
		
		<div id='evo_mb' class='eventon_mb'>
			<input type='hidden' id='evo_collapse_meta_boxes' name='evo_collapse_meta_boxes' value=''/>
		<?php
			// initial values
				$visibility_types = array('all'=>__('Everyone','eventon'),'admin'=>__('Admin Only','eventon'),'loggedin'=>__('Loggedin Users Only','eventon'));

			// FOREACH metabox item
			foreach($metabox_array as $mBOX):
				
				// initials
					$icon_style = (!empty($mBOX['iconURL']))?
						'background-image:url('.$mBOX['iconURL'].')'
						:'background-position:'.$mBOX['iconPOS'];
					$icon_class = (!empty($mBOX['iconPOS']))? 'evIcons':'evII';
					
					$guide = (!empty($mBOX['guide']))? 
						$ajde->wp_admin->tooltips($mBOX['guide']):null;
					
					$hiddenVal = (!empty($mBOX['hiddenVal']))?
						'<span class="hiddenVal">'.$mBOX['hiddenVal'].'</span>':null;

					// visibility type ONLY for custom meta fields
						$visibility_type = (!empty($mBOX['visibility_type']))? "<span class='visibility_type'>".__('Visibility Type:','eventon').' '.$visibility_types[$mBOX['visibility_type']] .'</span>': false;
				
					$closed = (!empty($closedmeta) && in_array($mBOX['id'], $closedmeta))? 'closed':null;
		?>
			<div class='evomb_section' id='<?php echo $mBOX['id'];?>'>			
				<div class='evomb_header'>
					<?php // custom field with icons
						if(!empty($mBOX['variation']) && $mBOX['variation']	=='customfield'):?>	
						<span class='evomb_icon <?php echo $icon_class;?>'><i class='fa <?php echo $mBOX['iconURL']; ?>'></i></span>
						
					<?php else:	?>
						<span class='evomb_icon <?php echo $icon_class;?>' style='<?php echo $icon_style?>'></span>
					<?php endif; ?>
					<p><?php echo $mBOX['name'];?><?php echo $hiddenVal;?><?php echo $guide;?><?php echo $visibility_type;?></p>
				</div>
				<div class='evomb_body <?php echo $closed;?>' box_id='<?php echo $mBOX['id'];?>'>
				<?php 

				if(!empty($mBOX['content'])){
					echo $mBOX['content'];
				}else{
					switch($mBOX['id']){
						case 'ev_learnmore':
							

							echo "<div class='evcal_data_block_style1'>
							<div class='evcal_db_data'>
								<input type='text' id='evcal_lmlink' name='evcal_lmlink' value='". ((!empty($ev_vals["evcal_lmlink"]) )? $ev_vals["evcal_lmlink"][0]:null)."' style='width:100%'/><br/>";
								?>
								<span class='yesno_row evo'>
									<?php 	
									$openInNewWindow = (!empty($ev_vals["evcal_lmlink_target"]))? $ev_vals["evcal_lmlink_target"][0]: null;
									echo $ajde->wp_admin->html_yesnobtn(array(
										'id'=>'evcal_lmlink_target',
										'var'=>$openInNewWindow,
										'input'=>true,
										'label'=>__('Open in New window','eventon')
									));?>											
								</span>

							<?php echo "</div></div>";
						break;

						case 'ev_uint':
							?>
							<div class='evcal_data_block_style1'>
								<div class='evcal_db_data'>										
									<?php
										$exlink_option = (!empty($ev_vals["_evcal_exlink_option"]))? $ev_vals["_evcal_exlink_option"][0]:1;
										$exlink_target = (!empty($ev_vals["_evcal_exlink_target"]) && $ev_vals["_evcal_exlink_target"][0]=='yes')?
											$ev_vals["_evcal_exlink_target"][0]:null;
									?>										
									<input id='evcal_exlink_option' type='hidden' name='_evcal_exlink_option' value='<?php echo $exlink_option; ?>'/>
									
									<input id='evcal_exlink_target' type='hidden' name='_evcal_exlink_target' value='<?php echo ($exlink_target) ?>'/>
									
									<?php
										$display_link_input = (!empty($ev_vals["_evcal_exlink_option"]) && $ev_vals["_evcal_exlink_option"][0]!='1')? 'display:block':'display:none';
								
									?>
									<p <?php echo ($exlink_option=='1' || $exlink_option=='3')?"style='display:none'":null;?> id='evo_new_window_io' class='<?php echo ($exlink_target=='yes')?'selected':null;?>'><span></span> <?php _e('Open in new window','eventon');?></p>
									
									<!-- external link field-->
									<input id='evcal_exlink' placeholder='<?php _e('Type the URL address','eventon');?>' type='text' name='evcal_exlink' value='<?php echo (!empty($ev_vals["evcal_exlink"]) )? $ev_vals["evcal_exlink"][0]:null?>' style='width:100%; <?php echo $display_link_input;?>'/>
									
									<div class='evcal_db_uis'>
										<a link='no'  class='evcal_db_ui evcal_db_ui_0 <?php echo ($exlink_option=='X')?'selected':null;?>' title='<?php _e('Do nothing','eventon');?>' value='X'></a>

										<a link='no'  class='evcal_db_ui evcal_db_ui_1 <?php echo ($exlink_option=='1')?'selected':null;?>' title='<?php _e('Slide Down Event Card','eventon');?>' value='1'></a>
										
										<!-- open as link-->
										<a link='yes' class='evcal_db_ui evcal_db_ui_2 <?php echo ($exlink_option=='2')?'selected':null;?>' title='<?php _e('External Link','eventon');?>' value='2'></a>	
										
										<!-- open as popup -->
										<a link='yes' class='evcal_db_ui evcal_db_ui_3 <?php echo ($exlink_option=='3')?' selected':null;?>' title='<?php _e('Popup Window','eventon');?>' value='3'></a>
										
										<?php
											// (-- addon --)
											if(has_action('evcal_ui_click_additions')){do_action('evcal_ui_click_additions');}
										?>							
										<div class='clear'></div>
									</div>
								</div>
							</div>
							<?php
						break;

						case 'ev_organizer':
							?>
							<div class='evcal_data_block_style1'>
								<p class='edb_icon evcal_edb_map'></p>
								<div class='evcal_db_data'>
									<div class='evcal_location_data_section'>		
									<p>
									<?php
										// organier terms for event post
											$termMeta = $evo_organizer_tax_id = '';
											$organizer_terms = wp_get_post_terms($p_id, 'event_organizer');
											if ( $organizer_terms && ! is_wp_error( $organizer_terms ) ){
												$evo_organizer_tax_id =  $organizer_terms[0]->term_id;
												$termMeta = get_option( "taxonomy_$evo_organizer_tax_id");
											}

										// Get all available organizer terms
											$terms = get_terms('event_organizer', array('hide_empty'=>false));
											
											if(count($terms)>0){
												echo "<select id='evcal_organizer_field' name='evcal_organizer_name_select' class='evo_select_field' style='max-width:425px;'>
													<option value='-'>".__('Select a saved organizer','eventon')."</option>";
											    foreach ( $terms as $term ) {

											    	$ORG_imgid = $ORG_imgsrc = '';
											    	$t_id = $term->term_id;						    	
											    	$term_meta = get_option( "taxonomy_$t_id" );
											    	$__selected = ($evo_organizer_tax_id== $t_id)? "selected='selected'":null;

											    	// organizer image
											    		$ORG_imgid = (!empty($term_meta['evo_org_img'])? $term_meta['evo_org_img']:null);
											    		$img_src = (!empty($ORG_imgid))? 
															wp_get_attachment_image_src($ORG_imgid,'medium'): false;
															$ORG_imgsrc = ($img_src)? $img_src[0]: '';

											       	echo "<option value='". $term->name ."' data-tid='{$t_id}' data-contact='".( $this->termmeta($term_meta,'evcal_org_contact'))  ."' data-img='". ( $this->termmeta($term_meta,'evo_org_img') ) ."' {$__selected} data-imgsrc='{$ORG_imgsrc}' data-exlink='".( $this->termmeta($term_meta,'evcal_org_exlink') )  ."' data-address='".( $this->termmeta($term_meta,'evcal_org_address') )  ."'>" . $term->name . "</option>";						        
											    }						    
											    echo "</select>";

											    echo "<span class='evoselectfield_data_view evo_btn' style='display:".($evo_organizer_tax_id?'inline-block':'none')."'>".__('Edit Organizer','eventon')."</span>";

											    echo "<label for='evcal_organizer_field'>".__('Choose already saved organizer or type new one below. NOTE: if you retype an existing organizer it will replace old information for that saved organizer','eventon')."</label>";
											}
									?>

									
									<input id='evo_organizer_tax_id' type='hidden' name='evo_organizer_tax_id' value='<?php echo $evo_organizer_tax_id;?>'/>
									</p>
									
									<div class='evoselectfield_saved_data'>
									<p><input type='text' id='evcal_organizer_name' name='evcal_organizer' value="<?php echo !empty($organizer_terms[0])? $organizer_terms[0]->name:''; ?>" style='width:100%' placeholder='<?php _e('eg. Blue Light Band','eventon');?>'/><label for='evcal_organizer'><?php _e('Event Organizer Name','eventon')?></label></p>
									<!-- organizer contact -->
									<p><input type='text' id='evcal_org_contact' name='evcal_org_contact' value="<?php echo $this->termmeta($termMeta,'evcal_org_contact');?>" style='width:100%' placeholder='<?php _e('eg. noone[at] thismail.com','eventon');?>'/><label for='evcal_org_contact'><?php _e('(Optional) Organizer Contact Information','eventon')?></label></p>

									<!-- organizer address-->
									<p><input type='text' id='evcal_org_address' name='evcal_org_address' value="<?php echo $this->termmeta($termMeta,'evcal_org_address');?>" style='width:100%' placeholder='<?php _e('eg. 123 Everywhere St., Neverland AB','eventon');?>'/><label for='evcal_org_address'><?php _e('(Optional) Organizer Address','eventon')?></label></p>

									<!-- organizer link -->
									<p><input type='text' id='evcal_org_exlink' name='evcal_org_exlink' value="<?php echo $this->termmeta($termMeta,'evcal_org_exlink');?>" style='width:100%' placeholder='<?php _e('eg. http://www.mysite.com/user','eventon');?>'/>

										<span class='yesno_row evo'>
											<?php 	
											$_evocal_org_exlink_target = (!empty($ev_vals["_evocal_org_exlink_target"]))? $ev_vals["_evocal_org_exlink_target"][0]: null;
											echo $ajde->wp_admin->html_yesnobtn(array(
												'id'=>'_evocal_org_exlink_target', 
												'var'=>$_evocal_org_exlink_target,
												'input'=>true,
												'label'=>__('Open organizer link in new window','eventon')
											));?>											
										</span>

									<label for='evcal_org_exlink'><?php _e('Link to the organizers page','eventon')?></label></p>
									
									<!-- image -->
									<?php 
										$org_img_id = $this->termmeta($termMeta,'evo_org_img');

										// image soruce array
										$img_src = ($org_img_id)? 
											wp_get_attachment_image_src($org_img_id,'medium'): null;

											$org_img_src = (!empty($img_src))? $img_src[0]: null;

										$__button_text = (!empty($org_img_id))? __('Remove Image','eventon'): __('Choose Image','eventon');
										$__button_text_not = (empty($org_img_id))? __('Remove Image','eventon'): __('Choose Image','eventon');
										$__button_class = (!empty($org_img_id))? 'removeimg':'chooseimg';
										// /echo $loc_img_id.' '.$img_src.'66';
									?>
									<div class='evo_metafield_image' style='padding-top:10px'>
										<p>
											<input id='evo_org_img_id' class='evo_org_img custom_upload_image evo_meta_img' name="evo_org_img" type="hidden" value="<?php echo ($org_img_id)? $org_img_id: null;?>" /> 
				                    		<input class="custom_upload_image_button button <?php echo $__button_class;?>" data-txt='<?php echo $__button_text_not;?>' type="button" value="<?php echo $__button_text;?>" /><br/>
				                    		<span class='evo_org_image_src image_src'>
				                    			<img src='<?php echo $org_img_src;?>' style='<?php echo !empty($org_img_id)?'':'display:none;';?> margin-top:8px'/>
				                    		</span>
				                    		<label><?php _e('Event Organizer Image','eventon');?> (<?php _e('Recommended Resolution 80x80px','eventon');?>)</label>
				                    	</p>
				                    </div>

				                    </div> <!-- evoselectfield_saved_data-->

				                    </div><!--.evcal_location_data_section-->
									
									<!-- yea no field - hide organizer field from eventcard -->
									<p class='yesno_row evo'>
										<?php 	
										$evo_evcrd_field_org = (!empty($ev_vals["evo_evcrd_field_org"]))? $ev_vals["evo_evcrd_field_org"][0]: null;
										echo $ajde->wp_admin->html_yesnobtn(array('id'=>'evo_org_field_ec', 'var'=>$evo_evcrd_field_org));?>
										
										<input type='hidden' name='evo_evcrd_field_org' value="<?php echo (!empty($ev_vals["evo_evcrd_field_org"]) && $ev_vals["evo_evcrd_field_org"][0]=='yes')?'yes':'no';?>"/>
										<label for='evo_evcrd_field_org'><?php _e('Hide Organizer field from EventCard','eventon')?></label>
									</p>
									<p style='clear:both'></p>
								</div>
							</div>
							<?php
						break;

						case 'ev_location':
							?>
							<div class='evcal_data_block_style1'>
								<p class='edb_icon evcal_edb_map'></p>
								<div class='evcal_db_data'>
									<div class='evcal_location_data_section'>	
									<p>
									<?php

										// location terms for event post
											$evo_location_tax_id = $termMeta= $evoLocSlug = '';

											$location_terms = wp_get_post_terms($p_id, 'event_location');
											if ( $location_terms && ! is_wp_error( $location_terms ) ){
												$evo_location_tax_id =  $location_terms[0]->term_id;
												$evoLocSlug = $location_terms[0]->slug;
												$termMeta = get_option( "taxonomy_$evo_location_tax_id");
											}

										// GET all available location terms
											$terms = get_terms('event_location', array('hide_empty'=>false) );
											
											if(count($terms)>0){

												echo "<select id='evcal_location_field' name='evcal_location_name_select' class='evo_select_field'>
													<option value='-'>".__('Select a saved location','eventon')."</option>";
											    foreach ( $terms as $term ) {

											    	$loc_img_src = $loc_img_id='';
											    	$t_id = $term->term_id;
											    	$term_meta = get_option( "taxonomy_$t_id" );
											    	$__selected = ($evo_location_tax_id== $t_id)? "selected='selected'":null;

											    	// location image
											    	$loc_img_id = $this->termmeta($term_meta,'evo_loc_img');
													$img_src = (!empty($loc_img_id))? 
														wp_get_attachment_image_src($loc_img_id,'medium'): false;
														$loc_img_src = ($img_src)? $img_src[0]: '';

														$locationName = str_replace('"', "'",  $term->name);

											       	echo "<option value='". $locationName ."' data-tid='{$t_id}' data-address='".( $this->termmeta($term_meta,'location_address'))  ."' data-lat='". ( $this->termmeta($term_meta,'location_lat') ) ."' data-lon='". ( $this->termmeta($term_meta,'location_lon') ) ."' {$__selected} data-loc_img_id='".$loc_img_id."' data-loc_img_src='{$loc_img_src}' data-link='". ( $this->termmeta($term_meta,'evcal_location_link') ) ."'>" . $term->name . "</option>";
											    }
											    echo "</select>";

											    echo "<span class='evoselectfield_data_view evo_btn' style='display:".($evo_location_tax_id?'':'none')."'>".__('Edit Location','eventon')."</span>";

											    echo "<label for='evcal_location_field'>".__('Choose already saved location or type new one below','eventon')."</label>";
											}										
									?>
									<input id='evo_location_tax' type='hidden' name='evo_location_tax_id' value='<?php echo $evo_location_tax_id;?>'/>
									<input type='hidden' name='evo_location_tax_id_old' value='<?php echo $evo_location_tax_id;?>'/>
									<input id='evo_location_slug' type='hidden' name='evo_location_tax_slug' value='<?php echo $evoLocSlug;?>'/>
									</p>

									<div class='evoselectfield_saved_data' style='display:<?php echo $evo_location_tax_id?'none':'';?>'>
									<p><input type='text' id='evcal_location_name' name='evcal_location_name' value="<?php echo (!empty($location_terms[0])? $location_terms[0]->name:''); ?>" style='width:100%' placeholder='<?php _e('eg. Irving City Park','eventon');?>'/><label for='evcal_location_name'><?php _e('Event Location Name','eventon')?></label></p>

									<p><input type='text' id='evcal_location' name='evcal_location' value="<?php echo $this->termmeta($termMeta,'location_address'); ?>" style='width:100%' placeholder='<?php _e('eg. 12 Rue de Rivoli, Paris','eventon');?>'/><label for='evcal_location'><?php _e('Event Location Address','eventon')?></label></p>
												
									<!-- location lat lon -->
									<p><input type='text' id='evcal_lat' class='evcal_latlon' name='evcal_lat' value='<?php echo $this->termmeta($termMeta,'location_lat'); ?>' placeholder='<?php _e('Latitude','eventon');?>' title='<?php _e('Latitude','eventon');?>'/>
									<input type='text' id='evcal_lon' class='evcal_latlon' name='evcal_lon' value='<?php echo $this->termmeta($termMeta,'location_lon');?>' placeholder='<?php _e('Longitude','eventon')?>' title='<?php _e('Longitude','eventon')?>'/></p>
									<p><i><?php _e('<b>NOTE:</b> LatLong will be auto generated for address provided for faster google map drawing. If location marker is not correct feel free to edit the LatLong values to correct location marker coordinates above. <br/>Location address field is <b>REQUIRED</b> for this to work.','eventon')?> <br/><a style='color:#B3DDEC' href='http://itouchmap.com/latlong.html' target='_blank'><?php _e('Find LanLat for address','eventon');?></a></i></p>

									<!-- Location link -->
									<p><input type='text' id='evcal_location_link' name='evcal_location_link' value="<?php echo $this->termmeta($termMeta,'evcal_location_link'); ?>" style='width:100%' placeholder='<?php _e('eg. http://www.locationlink.com','eventon');?>'/><label for='evcal_location_link'><?php _e('Event Location Link','eventon')?></label></p>

									<!-- image -->
										<?php 
											$loc_img_id = $this->termmeta($termMeta,'evo_loc_img');

											// image soruce array
											$img_src = ($loc_img_id)? 
												wp_get_attachment_image_src($loc_img_id,'medium'): null;

												$loc_img_src = (!empty($img_src))? $img_src[0]: null;

											$__button_text = (!empty($loc_img_id))? __('Remove Image','eventon'): __('Choose Image','eventon');
											$__button_text_not = (empty($loc_img_id))? __('Remove Image','eventon'): __('Choose Image','eventon');
											$__button_class = (!empty($loc_img_id))? 'removeimg':'chooseimg';

											// /echo $loc_img_id.' '.$img_src.'66';
										?>
										<div class='evo_metafield_image' style='padding-top:10px'>					
											<p >
												<input id='evo_loc_img_id' class='evo_loc_img custom_upload_image evo_meta_img' name="evo_loc_img" type="hidden" value="<?php echo ($loc_img_id)? $loc_img_id: null;?>" /> 
					                    		<input class="custom_upload_image_button button <?php echo $__button_class;?>" data-txt='<?php echo $__button_text_not;?>' type="button" value="<?php echo $__button_text;?>" /><br/>
					                    		<span class='evo_loc_image_src image_src'>
					                    			<img src='<?php echo $loc_img_src;?>' style='<?php echo !empty($loc_img_id)?'':'display:none';?>'/>
					                    		</span>
					                    		<label><?php _e('Event Location Image','eventon');?></label>
					                    	</p>
					                    </div>
									</div><!--evoselectfield_saved_data-->
									</div><!--.evcal_location_data_section-->

									<!-- HIDE Location name from eventcard -->
										<p class='yesno_row evo'>
											<?php 	
											$locationNM_val = (!empty($ev_vals["evcal_hide_locname"]))? $ev_vals["evcal_hide_locname"][0]: 'no';
											echo $ajde->wp_admin->html_yesnobtn(array('id'=>'evo_locname', 'var'=>$locationNM_val));?>
											
											<input type='hidden' name='evcal_hide_locname' value="<?php echo (!empty($ev_vals["evcal_hide_locname"]) && $ev_vals["evcal_hide_locname"][0]=='yes')?'yes': 'no';?>"/>
											<label for='evcal_hide_locname'><?php _e('Hide Location Name from Event Card','eventon')?></label>
										</p>
										<p style='clear:both'></p>

									<!-- HIDE google map option -->
										<p class='yesno_row evo'>
											<?php 	
											$location_val = (!empty($ev_vals["evcal_gmap_gen"]))? $ev_vals["evcal_gmap_gen"][0]: 'yes';
											echo $ajde->wp_admin->html_yesnobtn(array('id'=>'evo_genGmap', 'var'=>$location_val));?>
											
											<input type='hidden' name='evcal_gmap_gen' value="<?php echo (!empty($ev_vals["evcal_gmap_gen"]) && $ev_vals["evcal_gmap_gen"][0]=='yes')?'yes': ( empty($ev_vals["evcal_gmap_gen"])? 'yes':'no' );?>"/>
											<label for='evcal_gmap_gen'><?php _e('Generate Google Map from the address','eventon')?></label>
										</p>
										<p style='clear:both'></p>

									<!-- Show location name over image -->
										<p class='yesno_row evo'>
											<?php 	
											$evcal_name_over_img = (!empty($ev_vals["evcal_name_over_img"]))? $ev_vals["evcal_name_over_img"][0]: 'no';
											echo $ajde->wp_admin->html_yesnobtn(array('id'=>'evcal_name_over_img', 'var'=>$evcal_name_over_img));?>
											
											<input type='hidden' name='evcal_name_over_img' value="<?php echo (!empty($ev_vals["evcal_name_over_img"]) && $ev_vals["evcal_name_over_img"][0]=='yes')?'yes':'no';?>"/>
											<label for='evcal_name_over_img'><?php _e('Show location name & address over location image (If location image exist)','eventon')?></label>
										</p><p style='clear:both'></p>
								</div>
							</div>
							<?php
						break;

						case 'ev_timedate':
							// Minute increment	
							$minIncre = !empty($evcal_opt1['evo_minute_increment'])? (int)$evcal_opt1['evo_minute_increment']:60;
							$minADJ = 60/$minIncre;
							ob_start();
							?>
							<!-- date and time formats to use -->
							<input type='hidden' name='_evo_date_format' value='<?php echo $evcal_date_format[1];?>'/>
							<input type='hidden' name='_evo_time_format' value='<?php echo ($evcal_date_format[2])?'24h':'12h';?>'/>	
							<div id='evcal_dates' date_format='<?php echo $evcal_date_format[0];?>'>	
								<p class='yesno_row evo fcw'>
									<?php 	echo $ajde->wp_admin->html_yesnobtn(array(
										'id'=>'evcal_allday_yn_btn', 
										'var'=>$evcal_allday, 
										'attr'=>array('allday_switch'=>'1',)
										));?>			
									<input type='hidden' name='evcal_allday' value="<?php echo ($evcal_allday=='yes')?'yes':'no';?>"/>
									<label for='evcal_allday_yn_btn'><?php _e('All Day Event', 'eventon')?></label>
								</p><p style='clear:both'></p>
								
								<!-- START TIME-->
								<div class='evo_start_event evo_datetimes'>
									<div class='evo_date'>
										<p id='evcal_start_date_label'><?php _e('Event Start Date', 'eventon')?></p>
										<input id='evo_dp_from' class='evcal_data_picker datapicker_on' type='text' id='evcal_start_date' name='evcal_start_date' value='<?php echo ($_START)?$_START[0]:null?>' placeholder='<?php echo $evcal_date_format[1];?>'/>					
										<span><?php _e('Select a Date', 'eventon')?></span>
									</div>					
									<div class='evcal_date_time switch_for_evsdate evcal_time_selector' <?php echo $show_style_code?>>
										<div class='evcal_select'>
											<select id='evcal_start_time_hour' class='evcal_date_select' name='evcal_start_time_hour'>
												<?php
													//echo "<option value=''>--</option>";
													$start_time_h = ($_START)?$_START[1]:null;						
												for($x=1; $x<$time_hour_span;$x++){	
													$y = ($time_hour_span==25)? sprintf("%02d",($x-1)): $x;							
													echo "<option value='$y'".(($start_time_h==$y)?'selected="selected"':'').">$y</option>";
												}?>
											</select>
										</div><p style='display:inline; font-size:24px;padding:4px 2px'>:</p>
										<div class='evcal_select'>						
											<select id='evcal_start_time_min' class='evcal_date_select' name='evcal_start_time_min'>
												<?php	
													//echo "<option value=''>--</option>";
													$start_time_m = ($_START)?	$_START[2]: null;
													for($x=0; $x<$minIncre;$x++){
														$min = $minADJ * $x;
														$min = ($min<10)?('0'.$min):$min;
														echo "<option value='$min'".(($start_time_m==$min)?'selected="selected"':'').">$min</option>";
													}?>
											</select>
										</div>
										
										<?php if(!$evcal_date_format[2]):?>
										<div class='evcal_select evcal_ampm_sel'>
											<select name='evcal_st_ampm' id='evcal_st_ampm' >
												<?php
													$evcal_st_ampm = ($_START)?$_START[3]:null;
													foreach($select_a_arr as $sar){
														echo "<option value='".$sar."' ".(($evcal_st_ampm==$sar)?'selected="selected"':'').">".$sar."</option>";
													}
												?>								
											</select>
										</div>	
										<?php endif;?>
										<br/>
										<span><?php _e('Select a Time', 'eventon')?></span>
									</div><div class='clear'></div>
								</div>
								
								<!-- END TIME -->
								<?php 
									$evo_hide_endtime = (!empty($ev_vals["evo_hide_endtime"]) )? $ev_vals["evo_hide_endtime"][0]:null;
								?>
								<div class='evo_end_event evo_datetimes switch_for_evsdate'>
									<div class='evo_enddate_selection' style='<?php echo ($evo_hide_endtime=='yes')?'opacity:0.5':null;?>'>
									<div class='evo_date'>
										<p><?php _e('Event End Date','eventon')?></p>
										<input id='evo_dp_to' class='evcal_data_picker datapicker_on' type='text' id='evcal_end_date' name='evcal_end_date' value='<?php echo ($_END)? $_END[0]:null; ?>'/>					
										<span><?php _e('Select a Date','eventon')?></span>					
									</div>
									<div class='evcal_date_time evcal_time_selector' <?php echo $show_style_code?>>
										<div class='evcal_select'>
											<select class='evcal_date_select' name='evcal_end_time_hour'>
												<?php	
													//echo "<option value=''>--</option>";
													$end_time_h = ($_END)?$_END[1]:null;
													for($x=1; $x<$time_hour_span;$x++){
														$y = ($time_hour_span==25)? sprintf("%02d",($x-1)): $x;								
														echo "<option value='$y'".(($end_time_h==$y)?'selected="selected"':'').">$y</option>";
													}
												?>
											</select>
										</div><p style='display:inline; font-size:24px;padding:4px'>:</p>
										<div class='evcal_select'>
											<select class='evcal_date_select' name='evcal_end_time_min'>
												<?php	
													//echo "<option value=''>--</option>";
													$end_time_m = ($_END[2])?$_END[2]:null;
													for($x=0; $x<$minIncre;$x++){
														$min = $minADJ * $x;
														$min = ($min<10)?('0'.$min):$min;
														echo "<option value='$min'".(($end_time_m==$min)?'selected="selected"':'').">$min</option>";
													}
												?>
											</select>
										</div>					
										<?php if(!$evcal_date_format[2]):?>
										<div class='evcal_select evcal_ampm_sel'>
											<select name='evcal_et_ampm'>
												<?php
													$evcal_et_ampm = ($_END)?$_END[3]:null;								
													foreach($select_a_arr as $sar){
														echo "<option value='".$sar."' ".(($evcal_et_ampm==$sar)?'selected="selected"':'').">".$sar."</option>";
													}
												?>								
											</select>
										</div>
										<?php endif;?>
										<br/>
										<span><?php _e('Select the Time','eventon')?></span>
									</div><div class='clear'></div>
									</div>

									<!-- timezone value -->				
									<p style='padding-top:10px'><input type='text' name='evo_event_timezone' value='<?php echo (!empty($ev_vals["evo_event_timezone"]) )? $ev_vals["evo_event_timezone"][0]:null;?>' placeholder='<?php _e('Timezone text eg.PST','eventon');?>'/><label for=""><?php _e('Event timezone','eventon');?><?php $ajde->wp_admin->tooltips( __('Timezone text you type in here ex. PST will show next to event time on calendar.','eventon'),'',true);?></label></p>
									
									<!-- end time yes/no option -->					
									<p class='yesno_row evo '>
										<?php 	echo $ajde->wp_admin->html_yesnobtn(array('id'=>'evo_endtime', 'var'=>$evo_hide_endtime, 'attr'=>array('afterstatement'=>'evo_span_hidden_end')));?>
										
										<input type='hidden' name='evo_hide_endtime' value="<?php echo ($evo_hide_endtime=='yes')?'yes':'no';?>"/>
										<label for='evo_hide_endtime'><?php _e('Hide End Time from calendar', 'eventon')?></label>
									</p>
									<?php 
										// span event to hidden end time
										$evo_span_hidden_end = (!empty($ev_vals["evo_span_hidden_end"]) )? $ev_vals["evo_span_hidden_end"][0]:null;
										$evo_span_hidd_display = ($evo_hide_endtime && $evo_hide_endtime=='yes')? 'block':'none';
									?>
									<p class='yesno_row evo ' id='evo_span_hidden_end' style='display:<?php echo $evo_span_hidd_display;?>'>
										<?php 	echo $ajde->wp_admin->html_yesnobtn(array('id'=>'evo_span_hidden_end', 'var'=>$evo_span_hidden_end));?>
										
										<input type='hidden' name='evo_span_hidden_end' value="<?php echo ($evo_span_hidden_end=='yes')?'yes':'no';?>"/>
										<label for='evo_span_hidden_end'><?php _e('Span the event until hidden end time','eventon')?><?php $ajde->wp_admin->tooltips( __('If event end time goes beyond start time +  and you want the event to show in the calendar until end time expire, select this.','eventon'),'',true);?></label>
									</p>

									<?php 
										// month long event
										$_evo_month_long = (!empty($ev_vals["_evo_month_long"]) )? $ev_vals["_evo_month_long"][0]:null;
										$_event_month = (!empty($ev_vals["_event_month"]) )? $ev_vals["_event_month"][0]:null;
										
									?>
									<p class='yesno_row evo ' id='_evo_month_long' >
										<?php 	echo $ajde->wp_admin->html_yesnobtn(array('id'=>'_evo_month_long', 'var'=>$_evo_month_long));?>
										
										<input type='hidden' name='_evo_month_long' value="<?php echo ($_evo_month_long=='yes')?'yes':'no';?>"/>					
										<label for='_evo_month_long'><?php _e('Show this event for the entire start event Month','eventon')?><?php $ajde->wp_admin->tooltips( __('This will show this event for the entire month that the event start date is set to.','eventon'),'',true);?></label>
									</p>
									<input id='evo_event_month' type='hidden' name='_event_month' value="<?php echo $_event_month;?>"/><p style='clear:both'></p>
									

									<?php 
										// Year long event
										$evo_year_long = (!empty($ev_vals["evo_year_long"]) )? $ev_vals["evo_year_long"][0]:null;
										$event_year = (!empty($ev_vals["event_year"]) )? $ev_vals["event_year"][0]:null;
										
									?>
									<p class='yesno_row evo ' id='evo_year_long' >
										<?php 	echo $ajde->wp_admin->html_yesnobtn(array('id'=>'evo_year_long', 'var'=>$evo_year_long));?>
										
										<input type='hidden' name='evo_year_long' value="<?php echo ($evo_year_long=='yes')?'yes':'no';?>"/>					
										<label for='evo_year_long'><?php _e('Show this event for the entire start event Year','eventon')?><?php $ajde->wp_admin->tooltips( __('This will show this event on every month of the year. The year will be based off the start date you choose above. If year long is set, month long will be overridden.','eventon'),'',true);?></label>
									</p>
									<input id='evo_event_year' type='hidden' name='event_year' value="<?php echo $event_year;?>"/><p style='clear:both'></p>

								</div>
								<div style='clear:both'></div>			
								<?php 
									// Recurring events 
									$evcal_repeat = (!empty($ev_vals["evcal_repeat"]) )? $ev_vals["evcal_repeat"][0]:null;
								?>
								<div id='evcal_rep' class='evd'>
									<div class='evcalr_1'>
										<p class='yesno_row evo '>
											<?php 	
											echo $ajde->wp_admin->html_yesnobtn(array(
												'id'=>'evd_repeat', 
												'var'=>$evcal_repeat,
												'attr'=>array(
													'afterstatement'=>'evo_editevent_repeatevents'
												)
											));
											?>						
											<input type='hidden' name='evcal_repeat' value="<?php echo ($evcal_repeat=='yes')?'yes':'no';?>"/>
											<label for='evcal_repeat'><?php _e('Repeating event', 'eventon')?></label>
										</p><p style='clear:both'></p>
									</div>
									<p class='eventon_ev_post_set_line'></p>
									<?php
										// initial values
										$display = (!empty($ev_vals["evcal_repeat"]) && $evcal_repeat=='yes')? '':'none';
										// repeat frequency array
										$repeat_freq= apply_filters('evo_repeat_intervals', array('daily'=>'days','weekly'=>'weeks','monthly'=>'months','yearly'=>'years', 'custom'=>'custom') );
										$evcal_rep_gap = (!empty($ev_vals['evcal_rep_gap']) )?$ev_vals['evcal_rep_gap'][0]:1;
										$freq = (!empty($ev_vals["evcal_rep_freq"]) )?
												 ($repeat_freq[ $ev_vals["evcal_rep_freq"][0] ]): null;
									?>
									<div id='evo_editevent_repeatevents' class='evcalr_2 evo_repeat_options' style='display:<?php echo $display ?>'>
										
										<!-- REPEAT SERIES -->
										<div class='repeat_series'>
											<p class='yesno_row evo '>
												<?php 	
												$_evcal_rep_series = evo_meta($ev_vals, '_evcal_rep_series');
												$display = evo_meta_yesno($ev_vals, '_evcal_rep_series','yes','','none');

												echo $ajde->wp_admin->html_yesnobtn(array(
													'id'=>'evo_repeat', 
													'var'=>$_evcal_rep_series,	
												));
												?>						
												<input type='hidden' name='_evcal_rep_series' value="<?php echo ($_evcal_rep_series=='yes')?'yes':'no';?>"/>
												<label for='_evcal_rep_series'><?php _e('Show other future repeating instances of this event on event card', 'eventon')?></label>
											</p><p style='clear:both'></p>
										</div>

										<p class='repeat_type evcalr_2_freq evcalr_2_p'><span class='evo_form_label'><?php _e('Event Repeat Type','eventon');?>:</span> <select id='evcal_rep_freq' name='evcal_rep_freq'>
										<?php
											$evcal_rep_freq = (!empty($ev_vals['evcal_rep_freq']))?$ev_vals['evcal_rep_freq'][0]:null;
											foreach($repeat_freq as $refv=>$ref){
												echo "<option field='".$ref."' value='".$refv."' ".(($evcal_rep_freq==$refv)?'selected="selected"':'').">".$refv."</option>";
											}						
										?></select></p><!--.repeat_type-->
										
										<div class='evo_preset_repeat_settings' style='display:<?php echo (!empty($ev_vals['evcal_rep_freq']) && $ev_vals['evcal_rep_freq'][0]=='custom')? 'none':'block';?>'>		
											<p class='gap evcalr_2_rep evcalr_2_p'><span class='evo_form_label'><?php _e('Gap between repeats','eventon');?>:</span>
											<input type='number' name='evcal_rep_gap' min='1' max='100' value='<?php echo $evcal_rep_gap;?>' placeholder='1'/>	 <span id='evcal_re'><?php echo $freq;?></span></p>
										<?php
											
											// repeat number
												$evcal_rep_num = (!empty($ev_vals['evcal_rep_num']) )?  $ev_vals['evcal_rep_num'][0]:1;
											
											// repeat by
												$evp_repeat_rb = (!empty($ev_vals['evp_repeat_rb']) )? $ev_vals['evp_repeat_rb'][0]: null;	
												$evo_rep_WK = (!empty($ev_vals['evo_rep_WK']) )? unserialize($ev_vals['evo_rep_WK'][0]): array();
												$evo_repeat_wom = (!empty($ev_vals['evo_repeat_wom']) )? $ev_vals['evo_repeat_wom'][0]: null;
												
											// display none section
												$__display_none_1 =  (!empty($ev_vals['evcal_rep_freq']) && $ev_vals['evcal_rep_freq'][0] =='monthly')? 'block': 'none';
												$__display_none_2 =  ($__display_none_1=='block' && !empty($ev_vals['evp_repeat_rb']) && $ev_vals['evp_repeat_rb'][0] =='dow')? 'block': 'none';
										?>
										<?php // monthly only ?>
											<p class='repeat_by evcalr_2_p evo_rep_month' style='display:<?php echo $__display_none_1;?>'>
												<span class='evo_form_label'><?php _e('Repeat by','eventon');?>:</span>
												<select id='evo_rep_by' name='evp_repeat_rb'>
													<option value='dom' <?php echo ('dom'==$evp_repeat_rb)? 'selected="selected"':null;?>><?php _e('Day of the month','eventon');?></option>
													<option value='dow' <?php echo ('dow'==$evp_repeat_rb)? 'selected="selected"':null;?>><?php _e('Day of the week','eventon');?></option>
												</select>
											</p>
											<p class='evo_days_list evo_rep_month_2'  style='display:<?php echo $__display_none_2;?>'>
												<span class='evo_form_label'><?php _e('Repeat on selected days','eventon');?>: </span>
												<?php
													$days = array('S','M','T','W','T','F','S');
													for($x=0; $x<7; $x++){
														echo "<em><input type='checkbox' name='evo_rep_WK[]' value='{$x}' ". ((in_array($x, $evo_rep_WK))? 'checked="checked"':null)."><label>".$days[$x]."</label></em>";
													}
												?>
											</p>
											<p class='evcalr_2_p evo_rep_month_2'  style='display:<?php echo $__display_none_2;?>'>
												<span class='evo_form_label'><?php _e('Week of month to repeat','eventon');?>: </span>
												<select id='evo_wom' name='evo_repeat_wom'>
													<?php
													// week of the month for repeat
														echo "<option value='1' ".(($evo_repeat_wom==1)? 'selected="selected"':null).">".__('First','eventon')."</option>";
														echo "<option value='2' ".(($evo_repeat_wom==2)? 'selected="selected"':null).">".__('Second','eventon')."</option>";
														echo "<option value='3' ".(($evo_repeat_wom==3)? 'selected="selected"':null).">".__('Third','eventon')."</option>";
														echo "<option value='4' ".(($evo_repeat_wom==4)? 'selected="selected"':null).">".__('Fourth','eventon')."</option>";
														echo "<option value='5' ".(($evo_repeat_wom==5)? 'selected="selected"':null).">".__('Fifth','eventon')."</option>";
														echo "<option value='-1' ".(($evo_repeat_wom==-1)? 'selected="selected"':null).">".__('Last','eventon')."</option>";
													?>
												</select>
											</p>										
											<p class='evo_month_rep_value evo_rep_month_2' style='display:none'></p>
											<p class='evcalr_2_numr evcalr_2_p'><span class='evo_form_label'><?php _e('Number of repeats','eventon');?>:</span>
												<input type='number' name='evcal_rep_num' min='1' value='<?php echo $evcal_rep_num;?>' placeholder='1'/>						
											</p>
										</div><!--evo_preset_repeat_settings-->
										
										<!-- Custom repeat -->
										<div class='repeat_information' style='display:<?php echo (!empty($ev_vals['evcal_rep_freq']) && $ev_vals['evcal_rep_freq'][0]=='custom')? 'block':'none';?>'>
											<p><?php _e('CUSTOM REPEAT TIMES','eventon');?><br/><i style='opacity:0.7'><?php _e('NOTE: Below repeat intervals are in addition to the above main event time.','eventon');?></i></p>
											<?php

												//print_r(unserialize($ev_vals['aaa'][0]));					
												date_default_timezone_set('UTC');	

												echo "<p id='no_repeats' style='display:none;opacity:0.7'>There are no additional custom repeats!</p>";

												echo "<ul class='evo_custom_repeat_list'>";
												$count =0;
												if(!empty($ev_vals['repeat_intervals'])){								
													$repeat_times = (unserialize($ev_vals['repeat_intervals'][0]));
													// datre format sting to display for repeats
													$date_format_string = $evcal_date_format[1].' '.( $evcal_date_format[2]? 'G:i':'h:ia');
													
													foreach($repeat_times as $rt){
														echo '<li style="display:'.(($count==0 || $count>3)?'none':'block').'" class="'.($count==0?'initial':'').($count>3?' over':'').'"><span>'.__('from','eventon').'</span> '.date($date_format_string,$rt[0]).' <span class="e">End</span> '.date($date_format_string,$rt[1]).'<em alt="Delete">x</em>
														<input type="hidden" name="repeat_intervals['.$count.'][0]" value="'.$rt[0].'"/><input type="hidden" name="repeat_intervals['.$count.'][1]" value="'.$rt[1].'"/></li>';
														$count++;
													}								
												}
												echo "</ul>";
												echo ($count>3 && !empty($ev_vals['repeat_intervals']))? "<p style='padding-bottom:20px'>There are ".($count-1)." repeat intervals. <span class='evo_repeat_interval_view_all' data-show='no'>".__('View All','eventon')."</span></p>":null;
											?>
											<div class='evo_repeat_interval_new' style='display:none'>
												<p><span><?php _e('FROM','eventon');?>:</span><input class='ristD' name='repeat_date'/> <input class='ristT' name='repeat_time'/><br/><span><?php _e('TO','eventon');?>:</span><input class='rietD' name='repeat_date'/> <input class='rietT' name='repeat_time'/></p>
											</div>
											<p class='evo_repeat_interval_button'><a id='evo_add_repeat_interval' class='button_evo'>+ <?php _e('Add New Repeat Interval','eventon');?></a><span></span></p>
										</div>	
									</div>
								</div>	
							</div>
							
							<?php
						break;

						case 'ev_subtitle':
							echo "<div class='evcal_data_block_style1'>
								<div class='evcal_db_data'>
									<input type='text' id='evcal_subtitle' name='evcal_subtitle' value='".evo_meta($ev_vals, 'evcal_subtitle', true)."' style='width:100%'/>
								</div>
							</div>";
						break;
					}

					// for custom meta field for evnet
					if(!empty($mBOX['fieldtype']) && $mBOX['fieldtype']=='custommetafield'){

						$x = $mBOX['x'];

						echo "<div class='evcal_data_block_style1'>
								<div class='evcal_db_data'>";

							// FIELD
							$__saved_field_value = (!empty($ev_vals["_evcal_ec_f".$x."a1_cus"]) )? $ev_vals["_evcal_ec_f".$x."a1_cus"][0]:null ;
							$__field_id = '_evcal_ec_f'.$x.'a1_cus';

							// wysiwyg editor
							if(!empty($evcal_opt1['evcal_ec_f'.$x.'a2']) && 
								$evcal_opt1['evcal_ec_f'.$x.'a2']=='textarea'){
								
								wp_editor($__saved_field_value, $__field_id);
								
							// button
							}elseif(!empty($evcal_opt1['evcal_ec_f'.$x.'a2']) && 
								$evcal_opt1['evcal_ec_f'.$x.'a2']=='button'){
								
								$__saved_field_link = (!empty($ev_vals["_evcal_ec_f".$x."a1_cusL"]) )? $ev_vals["_evcal_ec_f".$x."a1_cusL"][0]:null ;

								echo "<input type='text' id='".$__field_id."' name='_evcal_ec_f".$x."a1_cus' ";
								echo 'value="'. $__saved_field_value.'"';						
								echo "style='width:100%' placeholder='Button Text' title='Button Text'/>";

								echo "<input type='text' id='".$__field_id."' name='_evcal_ec_f".$x."a1_cusL' ";
								echo 'value="'. $__saved_field_link.'"';						
								echo "style='width:100%' placeholder='Button Link' title='Button Link'/>";

									$onw = (!empty($ev_vals["_evcal_ec_f".$x."_onw"]) )? $ev_vals["_evcal_ec_f".$x."_onw"][0]:null ;
								?>

								<span class='yesno_row evo'>
									<?php 	
									$openInNewWindow = (!empty($ev_vals['_evcal_ec_f'.$x . '_onw']))? $ev_vals['_evcal_ec_f'.$x . '_onw'][0]: null;

									echo $ajde->wp_admin->html_yesnobtn(array(
										'id'=>'_evcal_ec_f'.$x . '_onw',
										'var'=>$openInNewWindow,
										'input'=>true,
										'label'=>__('Open in New window','eventon')
									));?>											
								</span>
								<?php
							
							// text	
							}else{
								echo "<input type='text' id='".$__field_id."' name='_evcal_ec_f".$x."a1_cus' ";										
								echo 'value="'. $__saved_field_value.'"';						
								echo "style='width:100%'/>";								
							}

						echo "</div></div>";
					}
				}

				?>
					
				</div>
			</div>
		<?php	endforeach;	?>


				<div class='evomb_section' id='<?php echo $mBOX['id'];?>'>			
					<div class='evomb_header'>
						<span class="evomb_icon evII"><i class="fa fa-plug"></i></span>
						<p>Additional Functionality</p>
					</div>
					<p style='padding:15px 25px; margin:0' class="evomb_body_additional">Looking for additional functionality including event tickets, frontend event submissions, RSVP to events, photo gallery and more? Check out <a href='http://www.myeventon.com/addons/' target='_blank'>eventON addons</a>.</p>
				</div>					

			<div class='evMB_end'></div>
		</div>
	<?php }

	//	THIRD PARTY event related settings 
		function ajde_evcal_show_box_3(){	
			
			global $eventon, $ajde;
			
			$evcal_opt1= get_option('evcal_options_evcal_1');
				$evcal_opt2= get_option('evcal_options_evcal_2');
				
				// Use nonce for verification
				wp_nonce_field( plugin_basename( __FILE__ ), 'evo_noncename' );
				
				// The actual fields for data entry
				$p_id = get_the_ID();
				$ev_vals = get_post_custom($p_id);
			
			?>
			<table id="meta_tb" class="form-table meta_tb evoThirdparty_meta" >
				<?php
					// (---) hook for addons
					if(has_action('eventon_post_settings_metabox_table'))
						do_action('eventon_post_settings_metabox_table');
				
					if(has_action('eventon_post_time_settings'))
						do_action('eventon_post_time_settings');

				// PAYPAL
					if($evcal_opt1['evcal_paypal_pay']=='yes'):
					?>
					<tr>
						<td colspan='2' class='evo_thirdparty_table_td'>
							<div class='evo3rdp_header'>
								<span class='evo3rdp_icon'><i class='fa fa-paypal'></i></span>
								<p><?php _e('Paypal "BUY NOW" button','eventon');?></p>
							</div>	
							<div class='evo_3rdp_inside'>
								<p class='evo_thirdparty'>
									<label for='evcal_paypal_text'><?php _e('Text to show above buy now button','eventon')?></label><br/>			
									<input type='text' id='evcal_paypal_text' name='evcal_paypal_text' value='<?php echo (!empty($ev_vals["evcal_paypal_text"]) )? $ev_vals["evcal_paypal_text"][0]:null?>' style='width:100%'/>
								</p>
								<p class='evo_thirdparty'><label for='evcal_paypal_item_price'><?php _e('Enter the price for paypal buy now button <i>eg. 23.99</i>')?><?php $ajde->wp_admin->tooltips('Type the price without currency symbol to create a buy now button for this event. This will show on front-end calendar for this event','',true);?></label><br/>			
									<input placeholder='eg. 29.99' type='text' id='evcal_paypal_item_price' name='evcal_paypal_item_price' value='<?php echo (!empty($ev_vals["evcal_paypal_item_price"]) )? $ev_vals["evcal_paypal_item_price"][0]:null?>' style='width:100%'/>
								</p>
								<p class='evo_thirdparty'>
									<label for='evcal_paypal_email'><?php _e('Custom Email address to receive payments','eventon')?><?php $ajde->wp_admin->tooltips('This email address will override the email saved under eventON settings for paypal to accept payments to this email instead of paypal email saved in eventon settings.','',true);?></label><br/>			
									<input type='text' id='evcal_paypal_email' name='evcal_paypal_email' value='<?php echo (!empty($ev_vals["evcal_paypal_email"]) )? $ev_vals["evcal_paypal_email"][0]:null?>' style='width:100%'/>
								</p>
							</div>		
						</td>			
					</tr>
					<?php endif; ?>
				</table>
			<?php
		}
		
	/** Save the Event data meta box. **/
		function eventon_save_meta_data($post_id, $post){
			if($post->post_type!='ajde_events')
				return;
				
			// Stop WP from clearing custom fields on autosave
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
				return;

			// Prevent quick edit from clearing custom fields
			if (defined('DOING_AJAX') && DOING_AJAX)
				return;

			
			// verify this came from the our screen and with proper authorization,
			// because save_post can be triggered at other times
			if( isset($_POST['evo_noncename']) ){
				if ( !wp_verify_nonce( $_POST['evo_noncename'], plugin_basename( __FILE__ ) ) ){
					return;
				}
			}
			// Check permissions
			if ( !current_user_can( 'edit_post', $post_id ) )
				return;	

			global $pagenow;
			$_allowed = array( 'post-new.php', 'post.php' );
			if(!in_array($pagenow, $_allowed)) return;
			
			
			// $_POST FIELDS array
				$fields_ar =apply_filters('eventon_event_metafields', array(
					'evcal_allday','evcal_event_color','evcal_event_color_n',
					'evo_location_tax_id','evcal_location','evcal_location_name','evcal_location_link','evo_location_tax','evo_loc_img','evo_org_img','evcal_name_over_img',
					'evcal_organizer','evo_organizer_tax_id','evcal_org_contact','evcal_org_address','evcal_org_img','evcal_org_exlink','_evocal_org_exlink_target',
					'evcal_exlink','evcal_lmlink','evcal_subtitle',
					'evcal_hide_locname','evcal_gmap_gen','evcal_mu_id','evcal_paypal_item_price','evcal_paypal_text','evcal_paypal_email',
					'evcal_repeat','_evcal_rep_series','evcal_rep_freq','evcal_rep_gap','evcal_rep_num',
					'evp_repeat_rb','evo_repeat_wom','evo_rep_WK',
					'evcal_lmlink_target','_evcal_exlink_target','_evcal_exlink_option',
					'evo_hide_endtime','evo_span_hidden_end','evo_year_long','event_year','_evo_month_long','_event_month',
					'evo_evcrd_field_org','evo_event_timezone',

					'evo_exclude_ev',
					'_featured',
					'_completed',
					'_cancel','_cancel_reason',
					'_onlyloggedin',
					
					'evcal_lat','evcal_lon',
				));

			// append custom fields based on activated number
				$evcal_opt1= get_option('evcal_options_evcal_1');
				$num = evo_calculate_cmd_count($evcal_opt1);
				for($x =1; $x<=$num; $x++){	
					if(eventon_is_custom_meta_field_good($x)){
						$fields_ar[]= '_evcal_ec_f'.$x.'a1_cus';
						$fields_ar[]= '_evcal_ec_f'.$x.'a1_cusL';
						$fields_ar[]= '_evcal_ec_f'.$x.'_onw';
					}
				}
						
			// field names that pertains only to event date information
				$fields_sub_ar = apply_filters('eventon_event_date_metafields', array(
					'evcal_start_date','evcal_end_date', 'evcal_start_time_hour','evcal_start_time_min','evcal_st_ampm',
					'evcal_end_time_hour','evcal_end_time_min','evcal_et_ampm','evcal_allday'
					)
				);
				
			
			// DATE and TIME data
				$date_POST_values='';
				foreach($fields_sub_ar as $ff){
					
					// end date value fix for -- hide end date
					if($ff=='evcal_end_date' && !empty($_POST['evo_hide_endtime']) && $_POST['evo_hide_endtime']=='yes'){

						if($_POST['evo_span_hidden_end'] && $_POST['evo_span_hidden_end']=='yes'){
							$date_POST_values['evcal_end_date']=$_POST['evcal_end_date'];
						}else{
							$date_POST_values['evcal_end_date']=$_POST['evcal_start_date'];
						}
						//$date_POST_values['evcal_end_date']=$_POST['evcal_end_date'];
						
					}else{
						if(!empty($_POST[$ff]))
							$date_POST_values[$ff]=$_POST[$ff];
					}
					// remove these values from previously saved
					delete_post_meta($post_id, $ff);
				}
			
			// convert the post times into proper unix time stamps
				if(!empty($_POST['_evo_date_format']) && !empty($_POST['_evo_time_format']))
					$proper_time = eventon_get_unix_time($date_POST_values, $_POST['_evo_date_format'], $_POST['_evo_time_format']);		

			// if Repeating event save repeating intervals
				if( eventon_is_good_repeat_data() && !empty($proper_time['unix_start']) ){

					$unix_E = (!empty($proper_time['unix_end']))? $proper_time['unix_end']: $proper_time['unix_start'];
					$repeat_intervals = eventon_get_repeat_intervals($proper_time['unix_start'], $unix_E);

					// save repeat interval array as post meta
					if ( !empty($repeat_intervals) ){
						asort($repeat_intervals);
						update_post_meta( $post_id, 'repeat_intervals', $repeat_intervals);
					}
				}

				//update_post_meta($post_id, 'aaa', $_POST['repeat_intervals']);

			// run through all the custom meta fields
				foreach($fields_ar as $f_val){
					
					if(!empty ($_POST[$f_val])){

						$post_value = ( $_POST[$f_val]);
						update_post_meta( $post_id, $f_val,$post_value);

						// ux val for single events linking to event page	
						if($f_val=='evcal_exlink' && $_POST['_evcal_exlink_option']=='4'){
							update_post_meta( $post_id, 'evcal_exlink',get_permalink($post_id) );
						}

					}else{
						if(defined('DOING_AUTOSAVE') && !DOING_AUTOSAVE){
							// if the meta value is set to empty, then delete that meta value
							delete_post_meta($post_id, $f_val);
						}
						delete_post_meta($post_id, $f_val);
					}
					
				}
						
			// full time converted to unix time stamp
				if ( !empty($proper_time['unix_start']) )
					update_post_meta( $post_id, 'evcal_srow', $proper_time['unix_start']);
				
				if ( !empty($proper_time['unix_end']) )
					update_post_meta( $post_id, 'evcal_erow', $proper_time['unix_end']);

			// save event year if not set
				if( (empty($_POST['event_year']) && !empty($proper_time['unix_start'])) || 
					(!empty($_POST['event_year']) &&
						$_POST['event_year']=='yes')
				){
					$year = date('Y', $proper_time['unix_start']);
					update_post_meta( $post_id, 'event_year', $year);
				}

			// save event month if not set
				if( (empty($_POST['_event_month']) && !empty($proper_time['unix_start'])) || 
					(!empty($_POST['_event_month']) &&
						$_POST['_event_month']=='yes')
				){
					$month = date('n', $proper_time['unix_start']);
					update_post_meta( $post_id, '_event_month', $month);
				}
					
			//set event color code to 1 for none select colors
				if ( !isset( $_POST['evcal_event_color_n'] ) )
					update_post_meta( $post_id, 'evcal_event_color_n',1);
								
			// save featured event data default value no
				$_featured = get_post_meta($post_id, '_featured',true);
				if(empty( $_featured) )
					update_post_meta( $post_id, '_featured','no');
			
			// LOCATION as taxonomy
				// if location name is choosen from the list
				$debug = 1;
				if(isset($_POST['evcal_location_name_select'], $_POST['evcal_location_name']) && $_POST['evcal_location_name_select'] == $_POST['evcal_location_name']){
					
					// 
					if(!empty($_POST['evo_location_tax_id'])){
						$term_name = esc_attr($_POST['evcal_location_name']);
						$term_slug = esc_attr($_POST['evo_location_tax_slug']);
						$termID = array((int)($_POST['evo_location_tax_id']));
						
						$term_meta = $latlon = array();

						// generate latLon
						if(isset($_POST['evcal_location']))
							$latlon = eventon_get_latlon_from_address($_POST['evcal_location']);
							
						// longitude
						$term_meta['location_lon'] = (!empty($_POST['evcal_lon']))?$_POST['evcal_lon']:
							(!empty($latlon['lng'])? floatval($latlon['lng']): null);

						// latitude
						$term_meta['location_lat'] = (!empty($_POST['evcal_lat']))?$_POST['evcal_lat']:
							(!empty($latlon['lat'])? floatval($latlon['lat']): null);

						$term_meta['evcal_location_link'] = (isset($_POST['evcal_location_link']))?$_POST['evcal_location_link']:null;
						$term_meta['location_address'] = (isset($_POST['evcal_location']))?$_POST['evcal_location']:null;

						$term_meta['evo_loc_img'] = (isset($_POST['evo_loc_img']))?$_POST['evo_loc_img']:null;
						update_option("taxonomy_".$_POST['evo_location_tax_id'], $term_meta);
						
						wp_set_object_terms( $post_id, $termID, 'event_location');	
						$debug = 2;						
					}
					
				}elseif(isset($_POST['evcal_location_name'])){
				// create new taxonomy from new values

					$term_name = esc_attr($_POST['evcal_location_name']);
					$term_slug = str_replace(" ", "-", $term_name);

					// create wp term
					$new_term_ = wp_insert_term( $term_name, 'event_location', array('slug'=>$term_slug) );

					if(!is_wp_error($new_term_)){
						$term_meta = $latlon = array();

						// generate latLon
						if(isset($_POST['evcal_location']))
							$latlon = eventon_get_latlon_from_address($_POST['evcal_location']);

						// latitude and longitude
						$term_meta['location_lon'] = (!empty($_POST['evcal_lon']))? $_POST['evcal_lon']:
							(!empty($latlon['lng'])? floatval($latlon['lng']): null);
						$term_meta['location_lat'] = (!empty($_POST['evcal_lat']))? $_POST['evcal_lat']:
							(!empty($latlon['lat'])? floatval($latlon['lat']): null);

						$term_meta['evcal_location_link'] = (isset($_POST['evcal_location_link']))?$_POST['evcal_location_link']:null;
						$term_meta['location_address'] = (isset($_POST['evcal_location']))?$_POST['evcal_location']:null;
						$term_meta['evo_loc_img'] = (isset($_POST['evo_loc_img']))?$_POST['evo_loc_img']:null;
						update_option("taxonomy_".$new_term_['term_id'], $term_meta);
						wp_set_post_terms( $post_id, array($term_name), 'event_location');
						$debug = 3;
					}						
				}
				// if location is intended removed
					if(empty($_POST['evcal_location_name']) && isset($_POST['evcal_location_name_select']) && $_POST['evcal_location_name_select']=='-'){
						// delete all location taxonomies attached to this event
						wp_delete_object_term_relationships($post_id,'event_location');
						$debug = 4;
					}
					//update_post_meta(521, 'aaa',$debug);

			// ORGANIZER as taxonomy
				// Selected value from list - update other values
				if(isset($_POST['evcal_organizer_name_select'], $_POST['evcal_organizer']) && $_POST['evcal_organizer_name_select'] == $_POST['evcal_organizer']){

					if(!empty($_POST['evo_organizer_tax_id'])){
						$term_name = esc_attr($_POST['evcal_organizer']);
						$term_meta = array();
						$term_meta['evcal_org_contact'] = (isset($_POST['evcal_org_contact']))?
							str_replace('"', "'", $_POST['evcal_org_contact']):null; 
						$term_meta['evcal_org_address'] = (isset($_POST['evcal_org_address']))?
							str_replace('"', "'", $_POST['evcal_org_address']):null;
						$term_meta['evo_org_img'] = (isset($_POST['evo_org_img']))?$_POST['evo_org_img']:null;;
						$term_meta['evcal_org_exlink'] = (isset($_POST['evcal_org_exlink']))?$_POST['evcal_org_exlink']:null;;
						update_option("taxonomy_".$_POST['evo_organizer_tax_id'], $term_meta);
						wp_set_post_terms( $post_id, array($term_name), 'event_organizer');
					}
				}elseif(isset($_POST['evcal_organizer'])){
				// create new taxonomy from new values

					$term_name = esc_attr($_POST['evcal_organizer']);
					$term_slug = str_replace(" ", "-", $term_name);

					// create wp term
					$new_term_ = wp_insert_term( $term_name, 'event_organizer', array('slug'=>$term_slug) );

					if(!is_wp_error($new_term_)){
						$term_meta = array();
						$term_meta['evcal_org_contact'] = (isset($_POST['evcal_org_contact']))?
							str_replace('"', "'", $_POST['evcal_org_contact']):null;
						$term_meta['evcal_org_address'] = (isset($_POST['evcal_org_address']))?
							str_replace('"', "'", $_POST['evcal_org_address']):null;
						$term_meta['evo_org_img'] = (isset($_POST['evo_org_img']))?$_POST['evo_org_img']:null;
						$term_meta['evcal_org_exlink'] = (isset($_POST['evcal_org_exlink']))?$_POST['evcal_org_exlink']:null;
						update_option("taxonomy_".$new_term_['term_id'], $term_meta);

						wp_set_post_terms( $post_id, array($term_name), 'event_organizer');
					}				
				}

				// if organizer is intended removed
					if(empty($_POST['evcal_organizer']) && isset($_POST['evcal_organizer_name_select']) && $_POST['evcal_organizer_name_select']=='-'){
						// delete all organizer taxonomies attached to this event
						wp_delete_object_term_relationships($post_id,'event_organizer');
					}
			
			// (---) hook for addons
			do_action('eventon_save_meta', $fields_ar, $post_id);

			// save user closed meta field boxes
			if(!empty($_POST['evo_collapse_meta_boxes']))
				eventon_save_collapse_metaboxes($post_id, $_POST['evo_collapse_meta_boxes'],true );
				
		}

	// Supporting functions
		function termmeta($term_meta, $var){
			return !empty( $term_meta[$var] ) ? 
				stripslashes(str_replace('"', "'", (esc_attr( $term_meta[$var] )) )) : 
				null;
		}
}
$evometabox = new evo_event_metaboxes();