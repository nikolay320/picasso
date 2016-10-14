<?php
/**
 * AJDE Backender 
 * print out back end customization form set up for the plugin settings
 * 
 * @updated 2016-1
 * @version 1.5.8
 */

if ( function_exists( 'print_ajde_customization_form' ) ) return;

function print_ajde_customization_form($cutomization_pg_array, $ajdePT, $extra_tabs=''){
	
	global $ajde;
	$wp_admin = $ajde->wp_admin;
	$textdomain = 'nylon';
	
	// initial variables
		$font_sizes = array('10px','11px','12px','13px','14px','16px','18px','20px', '22px', '24px','28px','30px','36px','42px','48px','54px','60px');
		$opacity_values = array('0.0','0.1','0.2','0.3','0.4','0.5','0.6','0.7','0.8','0.9','1',);
		$font_styles = array('normal','bold','italic','bold-italic');
		
		$__no_hr_types = array('begin_afterstatement','end_afterstatement','hiddensection_open','hiddensection_close');
	
		//define variables
		$leftside=$rightside='';
		$count=1;
	
	// different types of content
		/*
			notice, image, icon, subheader, note, checkbox, text. textarea, font_size, font_style, border_radius, color, fontation, multicolor, radio, dropdown, checkboxes, yesno, begin_afterstatement, end_afterstatement, hiddensection_open, hiddensection_close, customcode
		*/

	foreach($cutomization_pg_array as $cpa=>$cpav){								
		// left side tabs with different level colors
		$ls_level_code = (isset($cpav['level']))? 'class="'.$cpav['level'].'"': null;
		
		$leftside .= "<li ".$ls_level_code."><a class='".( ($count==1)?'focused':null)."' data-c_id='".$cpav['id']."' title='".$cpav['tab_name']."'><i class='fa fa-".( !empty($cpav['icon'])? $cpav['icon']:'edit')."'></i>".__($cpav['tab_name'],$textdomain)."</a></li>";								
		$tab_type = (isset($cpav['tab_type'] ) )? $cpav['tab_type']:'';
		if( $tab_type !='empty'){ // to not show the right side

			
			// RIGHT SIDE
			$display_default = (!empty($cpav['display']) && $cpav['display']=='show')?'':'display:none';
			
			$rightside.= "<div id='".$cpav['id']."' style='".$display_default."' class='nfer'>
				<h3>".__($cpav['name'],$textdomain)."</h3>";

				if(!empty($cpav['description']))
					$rightside.= "<p class='tab_description'>".$cpav['description']."</p>";
			
			$rightside.="<em class='hr_line'></em>";					
				// font awesome
				require_once('fa_fonts.php');	
				$rightside.= "<div style='display:none' class='fa_icons_selection'><div class='fai_in'><ul class='faicon_ul'>";
				
				// $font_ passed from incldued font awesome file above
				if(!empty($font_)){
					foreach($font_ as $fa){
						$rightside.= "<li><i data-name='".$fa."' class='fa ".$fa."' title='{$fa}'></i></li>";
					}
				}
				$rightside.= "</ul>";
				$rightside.= "</div></div>";

			// EACH field
			foreach($cpav['fields'] as $field){

				if($field['type']=='text' || $field['type']=='textarea'){
					$FIELDVALUE = (!empty($ajdePT[ $field['id']]))? 
							stripslashes($ajdePT[ $field['id']]): null;
				}
				
				// LEGEND or tooltip
				$legend_code = (!empty($field['legend']) )? $wp_admin->tooltips($field['legend'], 'L', false):
					( (!empty($field['tooltip']) )? $wp_admin->tooltips($field['tooltip'], 'L', false): null );
				
				
				switch ($field['type']){
					// notices
					case 'notice':
						$rightside.= "<div class='ajdes_notice'>".__($field['name'],$textdomain)."</div>";
					break;
					//IMAGE
					case 'image':
						$image = ''; 
						$meta = isset($ajdePT[$field['id']])? $ajdePT[$field['id']]:false;
						
						$preview_img_size = (empty($field['preview_img_size']))?'medium'
							: $field['preview_img_size'];
						
						$rightside.= "<div id='pa_".$field['id']."'><p>".$field['name'].$legend_code."</p>";
						
						if ($meta) { $image = wp_get_attachment_image_src($meta, $preview_img_size); $image = $image[0]; } 
						
						$display_saved_image = (!empty($image))?'block':'none';
						$opp = ($display_saved_image=='block')? 'none':'block';

						$rightside.= "<p class='ajde_image_selector'>";
						$rightside.= "<span class='ajt_image_holder' style='display:{$display_saved_image}'><b class='ajde_remove_image'>X</b><img src='{$image}'/></span>";
						$rightside.= "<input type='hidden' class='ajt_image_id' name='{$field['id']}' value='{$meta}'/>";
						$rightside.= "<input type='button' class='ajt_choose_image button' style='display:{$opp}' value='".__('Choose an Image','ajde')."'/>";
						$rightside.= "</p></div>";
						
					break;
					
					case 'icon':
						$field_value = (!empty($ajdePT[ $field['id']]) )? 
							$ajdePT[ $field['id']]:$field['default'];

						$rightside.= "<div class='row_faicons'><p class='fieldname'>".__($field['name'],$textdomain)."</p>";
						// code
						$rightside.= "<p class='acus_line faicon'>
							<i class='fa ".$field_value."'></i>
							<input name='".$field['id']."' class='backender_colorpicker' type='hidden' value='".$field_value."' /></p>";
						$rightside.= "<div class='clear'></div></div>";
					break;

					case 'subheader':
						$rightside.= "<h4 class='acus_subheader'>".__($field['name'],$textdomain)."</h4>";
					break;
					case 'note':
						$rightside.= "<p class='nylon_note'><i>".__($field['name'],$textdomain)."</i></p>";
					break;
					case 'hr': $rightside.= "<em class='hr_line'></em>"; break;
					case 'checkbox':
						$this_value= (!empty($ajdePT[ $field['id']]))? $ajdePT[ $field['id']]: null;						
						$rightside.= "<p><input type='checkbox' name='".$field['id']."' value='yes' ".(($this_value=='yes')?'checked="/checked"/':'')."/> ".$field['name']."</p>";
					break;
					case 'text':
						$default_value = (!empty($field['default']) )? 'placeholder="'.$field['default'].'"':null;
						
						$rightside.= "<p>".__($field['name'],$textdomain).$legend_code."</p><p><span class='nfe_f_width'><input type='text' name='".$field['id']."'";
						$rightside.= 'value="'.$FIELDVALUE.'"';
						$rightside.= $default_value."/></span></p>";
					break;
					case 'password':
						$default_value = (!empty($field['default']) )? 'placeholder="'.$field['default'].'"':null;
						
						$rightside.= "<p>".__($field['name'],$textdomain).$legend_code."</p><p><span class='nfe_f_width'><input type='password' name='".$field['id']."'";
						$rightside.= 'value="'.$FIELDVALUE.'"';
						$rightside.= $default_value."/></span></p>";
					break;
					case 'textarea':						
						$rightside.= "<p>".__($field['name'],$textdomain).$legend_code."</p><p><span class='nfe_f_width'><textarea name='".$field['id']."'>".$FIELDVALUE."</textarea></span></p>";
					break;
					case 'font_size':
						$rightside.= "<p>".__($field['name'],$textdomain)." <select name='".$field['id']."'>";
							$ajde_fval = $ajdePT[ $field['id'] ];
							
							foreach($font_sizes as $fs){
								$selected = ($ajde_fval == $fs)?"selected='selected'":null;	
								$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
							}
						$rightside.= "</select></p>";
					break;
					case 'opacity_value':
						$rightside.= "<p>".__($field['name'],$textdomain)." <select name='".$field['id']."'>";
							$ajde_fval = $ajdePT[ $field['id'] ];
							
							foreach($opacity_values as $fs){
								$selected = ($ajde_fval == $fs)?"selected='selected'":null;	
								$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
							}
						$rightside.= "</select></p>";
					break;
					case 'font_style':
						$rightside.= "<p>".__($field['name'],$textdomain)." <select name='".$field['id']."'>";
							$ajde_fval = $ajdePT[ $field['id'] ];
							foreach($font_styles as $fs){
								$selected = ($ajde_fval == $fs)?"selected='selected'":null;	
								$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
							}
						$rightside.= "</select></p>";
					break;
					case 'border_radius':
						$rightside.= "<p>".__($field['name'],$textdomain)." <select name='".$field['id']."'>";
								$ajde_fval = $ajdePT[ $field['id'] ];
								$border_radius = array('0px','2px','3px','4px','5px','6px','8px','10px');
								foreach($border_radius as $br){
									$selected = ($ajde_fval == $br)?"selected='selected'":null;	
									$rightside.=  "<option value='$br' ".$selected.">$br</option>";
								}
						$rightside.= "</select></p>";
					break;
					case 'color':

						// default hex color
						$hex_color = (!empty($ajdePT[ $field['id']]) )? 
							$ajdePT[ $field['id']]:$field['default'];
						$hex_color_val = (!empty($ajdePT[ $field['id'] ]))? $ajdePT[ $field['id'] ]: null;

						// RGB Color for the color box
						$rgb_color_val = (!empty($field['rgbid']) && !empty($ajdePT[ $field['rgbid'] ]))? $ajdePT[ $field['rgbid'] ]: null;
						$__em_class = (!empty($field['rgbid']))? ' rgb': null;

						$rightside.= "<p class='acus_line color'>
							<em><span class='colorselector{$__em_class}' style='background-color:#".$hex_color."' hex='".$hex_color."' title='".$hex_color."'></span>
							<input name='".$field['id']."' class='backender_colorpicker' type='hidden' value='".$hex_color_val."' default='".$field['default']."'/>";
						if(!empty($field['rgbid'])){
							$rightside .= "<input name='".$field['rgbid']."' class='rgb' type='hidden' value='".$rgb_color_val."' />";
						}
						$rightside .= "</em>".__($field['name'],$textdomain)." </p>";					
					break;					

					case 'fontation':

						$variations = $field['variations'];
						$rightside.= "<div class='row_fontation'><p class='fieldname'>".__($field['name'],$textdomain)."</p>";

						foreach($variations as $variation){
							switch($variation['type']){
								case 'color':
									// default hex color
									$hex_color = (!empty($ajdePT[ $variation['id']]) )? 
										$ajdePT[ $variation['id']]:$variation['default'];
									$hex_color_val = (!empty($ajdePT[ $variation['id'] ]))? $ajdePT[ $variation['id'] ]: null;
									
									$title = (!empty($variation['name']))? $variation['name']:$hex_color;
									$_has_title = (!empty($variation['name']))? true:false;

									// code
									$rightside.= "<p class='acus_line color'>
										<em><span id='{$variation['id']}' class='colorselector ".( ($_has_title)? 'hastitle': '')."' style='background-color:#".$hex_color."' hex='".$hex_color."' title='".$hex_color."' alt='".$title."'></span>
										<input name='".$variation['id']."' class='backender_colorpicker' type='hidden' value='".$hex_color_val."' default='".$variation['default']."'/></em></p>";

								break;

								case 'font_style':
									$rightside.= "<p style='margin:0'><select title='".__('Font Style',$textdomain)."' name='".$variation['id']."'>";
											$f1_fs = (!empty($ajdePT[ $variation['id'] ]))?
												$ajdePT[ $variation['id'] ]:$variation['default'] ;
											foreach($font_styles as $fs){
												$selected = ($f1_fs == $fs)?"selected='selected'":null;	
												$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
											}
									$rightside.= "</select></p>";
								break;

								case 'font_size':
									$rightside.= "<p style='margin:0'><select title='".__('Font Size',$textdomain)."' name='".$variation['id']."'>";
											
											$f1_fs = (!empty($ajdePT[ $variation['id'] ]))?
												$ajdePT[ $variation['id'] ]:$variation['default'] ;
											
											foreach($font_sizes as $fs){
												$selected = ($f1_fs == $fs)?"selected='selected'":null;	
												$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
											}
									$rightside.= "</select></p>";
								break;
								
								case 'opacity_value':
									$rightside.= "<p style='margin:0'><select title='".__('Opacity Value',$textdomain)."' name='".$variation['id']."'>";
											
											$f1_fs = (!empty($ajdePT[ $variation['id'] ]))?
												$ajdePT[ $variation['id'] ]:$variation['default'] ;
											
											foreach($opacity_values as $fs){
												$selected = ($f1_fs == $fs)?"selected='selected'":null;	
												$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
											}
									$rightside.= "</select></p>";
								break;
							}

							
						}
						$rightside.= "<div class='clear'></div></div>";
					break;

					case 'multicolor':

						$variations = $field['variations'];

						$rightside.= "<div class='row_multicolor'>";

						foreach($variations as $variation){
							// default hex color
							$hex_color = (!empty($ajdePT[ $variation['id']]) )? 
								$ajdePT[ $variation['id']]:$variation['default'];
							$hex_color_val = (!empty($ajdePT[ $variation['id'] ]))? $ajdePT[ $variation['id'] ]: null;

							$rightside.= "<p class='acus_line color'>
							<em data-name='".__($variation['name'],$textdomain)."'><span id='{$variation['id']}' class='colorselector' style='background-color:#".$hex_color."' hex='".$hex_color."' title='".$hex_color."'></span>
							<input name='".$variation['id']."' class='backender_colorpicker' type='hidden' value='".$hex_color_val."' default='".$variation['default']."'/></em></p>";
						}

						$rightside.= "<div class='clear'></div><p class='multicolor_alt'></p></div>";

					break;

					case 'radio':
						$rightside.= "<p class='acus_line acus_radio'>".__($field['name'],$textdomain)."</br>";
						$cnt =0;
						foreach($field['options'] as $option=>$option_val){
							$this_value = (!empty($ajdePT[ $field['id'] ]))? $ajdePT[ $field['id'] ]:null;
							
							$checked_or_not = ((!empty($this_value) && ($option == $this_value) ) || (empty($this_value) && $cnt==0) )?
								'checked=\"checked\"':null;

							$option_id = $field['id'].'_'. (str_replace(' ', '_', $option_val));
							
							$rightside.="<em><input id='".$option_id."' type='radio' name='".$field['id']."' value='".$option."' "
							.  $checked_or_not  ."/><label class='ajdebe_radio_btn' for='".$option_id."'><span class='fa'></span>".__($option_val,$textdomain)."</label></em>";
							
							$cnt++;
						}						
						$rightside.= $legend_code."</p>";
						
					break;
					case 'dropdown':
						
						$dropdown_opt = (!empty($ajdePT[ $field['id'] ]))? $ajdePT[ $field['id'] ]
							:( !empty($field['default'])? $field['default']:null);
						
						$rightside.= "<p class='acus_line {$field['id']}'>".__($field['name'],$textdomain)." <select class='ajdebe_dropdown' name='".$field['id']."'>";
						
						foreach($field['options'] as $option=>$option_val){
							$rightside.="<option name='".$field['id']."' value='".$option."' "
							.  ( ($option == $dropdown_opt)? 'selected=\"selected\"':null)  ."/> ".$option_val."</option>";
						}						
						$rightside.= "</select>";

							// description text for this field
							if(!empty( $field['desc'] )){
								$rightside.= "<br/><i style='opacity:0.6'>".$field['desc']."</i>";
							}
						$rightside.= $legend_code."</p>";						
					break;
					case 'checkboxes':
						
						$meta_arr= (!empty($ajdePT[ $field['id'] ]) )? $ajdePT[ $field['id'] ]: null;
						$default_arr= (!empty($field['default'] ) )? $field['default']: null;

						ob_start();
						
						echo "<p class='acus_line acus_checks'><span style='padding-bottom:10px;'>".__($field['name'],$textdomain)."</span>";
						
						// foreach checkbox
						foreach($field['options'] as $option=>$option_val){
							$checked='';
							if(!empty($meta_arr) && is_array($meta_arr)){
								$checked = (in_array($option, $meta_arr))?'checked':'';
							}elseif(!empty($default_arr)){
								$checked = (in_array($option, $default_arr))?'checked':'';
							}

							// option ID
							$option_id = $field['id'].'_'. (str_replace(' ', '_', $option_val));
							
							echo "<span><input id='".$option_id."' type='checkbox' 
							name='".$field['id']."[]' value='".$option."' ".$checked."/>
							<label for='".$option_id."'><span class='fa'></span>".$option_val."</label></span>";
						}						
						echo  "</p>";

						$rightside.= ob_get_clean();
					break;

					// rearrange field
						// fields_array - array(key=>var)
						// order_var
						// selected_var
						// title
						// (o)notes
					case 'rearrange':

						ob_start();
							$_ORDERVAR = $field['order_var'];
							$_SELECTEDVAR = $field['selected_var'];
							$_FIELDSar = $field['fields_array']; // key(var) => value(name)

							
							// saved order
							if(!empty($ajdePT[$_ORDERVAR])){								
								
								$allfields_ = explode(',',$ajdePT[$_ORDERVAR]);
								$fieldsx = array();
								//print_r($allfields_);
								foreach($allfields_ as $fielders){									
									if(!in_array($fielders, $fieldsx)){
										$fieldsx[]= $fielders;
									}
								}
								//print_r($fieldsx);
								$allfields = implode(',', $fieldsx);

								$SAVED_ORDER = array_filter(explode(',', $allfields));
								
							}else{
								$SAVED_ORDER = false;
								$allfields = '';
							}

							$SELECTED = (!empty($ajdePT[$_SELECTEDVAR]))?
								( (is_array( $ajdePT[$_SELECTEDVAR] ))?
									$ajdePT[$_SELECTEDVAR]:
									array_filter( explode(',', $ajdePT[$_SELECTEDVAR]))):
								false;

							$SELECTED_VALS = (is_array($SELECTED))? implode(',', $SELECTED): $SELECTED;

							echo '<h4 class="acus_subheader">'.$field['title'].'</h4>';
							echo !empty($field['notes'])? '<p><i>'.$field['notes'].'</i></p>':'';
							echo '<input class="ajderearrange_order" name="'.$_ORDERVAR.'" value="'.$allfields.'" type="hidden"/>
								<input class="ajderearrange_selected" type="hidden" name="'.$_SELECTEDVAR.'" value="'.( (!empty($SELECTED_VALS))? $SELECTED_VALS:null).'"/>
								<div id="ajdeEVC_arrange_box" class="ajderearrange_box '.$field['id'].'">';

							// if an order array exists already
							if($SAVED_ORDER){
								// for each saved order
								foreach($SAVED_ORDER as $VAL){
									if(!isset($_FIELDSar[$VAL])) continue;

									$FF = (is_array($_FIELDSar[$VAL]))? 
										$_FIELDSar[$VAL][1]:
										$_FIELDSar[$VAL];
									echo (array_key_exists($VAL, $_FIELDSar))? 
										"<p val='".$VAL."'><span class='fa ". ( !empty($SELECTED) && in_array($VAL, $SELECTED)?''
											:'hide') ."'></span>".$FF."</p>":	null;
								}	
								
								// if there are new values in possible items add them to the bottom
								foreach($_FIELDSar as $f=>$v){
									$FF = (is_array($v))? $v[1]:$v;
									echo (!in_array($f, $SAVED_ORDER))? 
										"<p val='".$f."'><span class='fa ". ( !empty($SELECTED) && in_array($f, $SELECTED)?'':'hide') ."'></span>".$FF."</p>": null;
								}
									
							}else{
							// if there isnt a saved order	
								foreach($_FIELDSar as $f=>$v){
									$FF = (is_array($v))? $v[1]:$v;
									echo "<p val='".$f."'><span class='fa ". ( !empty($SELECTED) && in_array($f, $SELECTED)?'':'hide') ."'></span>".$FF."</p>";
								}				
							}

							echo "</div>";

						$rightside .= ob_get_clean();

					break;
					
					case 'yesno':						
						$yesno_value = (!empty( $ajdePT[$field['id'] ]) )? 
							$ajdePT[$field['id']]:'no';
						
						$after_statement = (isset($field['afterstatement']) )?$field['afterstatement']:'';

						$__default = (!empty( $field['default'] ) && $ajdePT[$field['id'] ]!='yes' )? 
							$field['default']
							:$yesno_value;

						$rightside.= "<p class='yesno_row'>".$wp_admin->html_yesnobtn(array('var'=>$__default,'attr'=>array('afterstatement'=>$after_statement) ))."<input type='hidden' name='".$field['id']."' value='".(($__default=='yes')?'yes':'no')."'/><span class='field_name'>".__($field['name'],$textdomain)."{$legend_code}</span>";

							// description text for this field
							if(!empty( $field['desc'] )){
								$rightside.= "<i style='opacity:0.6; padding-top:8px; display:block'>".$field['desc']."</i>";
							}
						$rightside .= '</p>';
					break;

					case 'yesnoALT':
					   $__default = (!empty( $field['default'] ) )?
					      $field['default']
					      :'no';

					   $yesno_value = (!empty( $ajdePT[$field['id'] ]) )?
					      $ajdePT[$field['id']]:$__default;
					   
					   $after_statement = (isset($field['afterstatement']) )?$field['afterstatement']:'';

					   $rightside.= "<p class='yesno_row'>".$wp_admin->html_yesnobtn(array('var'=>$yesno_value,'attr'=>array('afterstatement'=>$after_statement) ))."<input type='hidden' name='".$field['id']."' value='".$yesno_value."'/><span class='field_name'>".__($field['name'],$textdomain)."{$legend_code}</span>";

					      // description text for this field
					      if(!empty( $field['desc'] )){
					         $rightside.= "<i style='opacity:0.6; padding-top:8px; display:block'>".$field['desc']."</i>";
					      }
					   $rightside .= '</p>';
					break;

					case 'begin_afterstatement': 
						
						$yesno_val = (!empty($ajdePT[$field['id']]))? $ajdePT[$field['id']]:'no';
						
						$rightside.= "<div class='backender_yn_sec' id='".$field['id']."' style='display:".(($yesno_val=='yes')?'block':'none')."'>";
					break;
					case 'end_afterstatement': $rightside.= "</div>"; break;
					
					// hidden section open
					case 'hiddensection_open':
						
						$__display = (!empty($field['display']) && $field['display']=='none')? 'style="display:none"':null;
						$__diclass = (!empty($field['display']) && $field['display']=='none')? '':'open';
						
						$rightside.="<div class='ajdeSET_hidden_open {$__diclass}'><h4>{$field['name']}{$legend_code}</h4></div>
						<div class='ajdeSET_hidden_body' {$__display}>";
						
					break;					
					case 'hiddensection_close':	$rightside.="</div>";	break;
					
					// custom code
					case 'customcode':						
						$rightside.=$field['code'];						
					break;
				}
				if(!empty($field['type']) && !in_array($field['type'], $__no_hr_types) ){ $rightside.= "<em class='hr_line'></em>";}
				
			}		
			$rightside.= "</div><!-- nfer-->";
		}
		$count++;
	}
	
	//built out the backender section
	ob_start();
	?>
	<table id='ajde_customization'>
		<tr><td class='backender_left' valign='top'>
			<div id='acus_left'>
				<ul><?php echo $leftside ?></ul>								
			</div>
			<div class="ajde-collapse-menu"><div id="collapse-button" class='ajde_collpase_btn'><div></div></div><span><?php _e('Collpase Menu',$textdomain);?></span></div>
			</td><td width='100%'  valign='top'>
				<div id='acus_right' class='ajde_backender_uix'>
					<p id='acus_arrow' style='top:4px'></p>
					<div class='customization_right_in'>
						<div style='display:none' id='ajde_color_guide'>Testing</div>
						<div id='ajde_clr_picker' class="cp cp-default" style='display:none'></div>
						<?php echo $rightside.$extra_tabs;?>
					</div>
				</div>
			</td>
		</tr>
	</table>	
	<?php
	echo ob_get_clean();
	
}
?>