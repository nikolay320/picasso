<?php
/**
 * AJDE wp-admin all the other required parts for wp-admin
 *
 * @version 0.4
 * @updated 2015-10
 */

if(class_exists('ajde_wp_admin')) return;

class ajde_wp_admin{
	public $content = '';
	public function __construct(){}
	
	// lightbox content box
		function lightbox_content($arg){
			$defaults = array(
				'content'=>'',
				'class'=>'regular',
				'attr'=>'',
				'title'=>'',
				'subtitle'=>'',
				'type'=>'normal',
				'hidden_content'=>'',
				'width'=>'',
			);
			$args = (!empty($arg) && is_array($arg) && count($arg)>0) ? 
				array_merge($defaults, $arg) : $defaults;

						
			$_padding_class = (!empty($args['type']) && $args['type']=='padded')? ' padd':null;

			//print_r($args);
			$content='';
			$content .= 
			"<div class='ajde_popup {$args['class']}{$_padding_class}' {$args['attr']} style='display:none; ". ( (!empty($args['width']))? 'width:'.$args['width'].'px;':null )."'>				
					<div class='ajde_header'>
						<a class='ajde_backbtn' style='display:none'><i class='fa fa-angle-left'></i></a>
						<p id='ajde_title'>{$args['title']}</p>
						". ( (!empty($args['subtitle']))? "<p id='ajde_subtitle'>{$args['subtitle']}</p>":null) ."
						<a class='ajde_close_pop_btn'>X</a>
					</div>							
					<div id='ajde_loading'></div>";

				$content .= (!empty($args['max_height']))? "<div class='ajde_lightbox_outter maxbox' style='max-height:{$args['max_height']}px'>":null;
				$content .= "<div class='ajde_popup_text'>{$args['content']}</div>";
				$content .= (!empty($args['max_height']))? "</div>":null;
				$content .= "	<p class='message'></p>
					
				</div>";
			
			$this->content .= $content;
			add_action('admin_footer', array($this, 'actual_output_popup'));
		}
		function actual_output_popup($content){			
			echo "<div id='ajde_popup_outter'>";
			echo $this->content;
			echo "</div><div id='ajde_popup_bg'></div>";
		}

	// YES NO Button
		function html_yesnobtn($args=''){

			$defaults = array(
				'id'=>'',
				'var'=>'',
				'no'=>'',
				'default'=>'',
				'input'=>false,
				'inputAttr'=>'',
				'label'=>'',
				'guide'=>'',
				'guide_position'=>'',
				'abs'=>'no',// absolute positioning of the button
				'attr'=>'', // array
				'afterstatement'=>'',
			);
			
			$args = shortcode_atts($defaults, $args);

			$_attr = $no = '';

			if(!empty($args['var'])){
				$no = ($args['var']	=='yes')? 
					 null: 
					 ( (!empty($args['default']) && $args['default']=='yes')? null:'NO');
			}else{
				$no = (!empty($args['default']) && $args['default']=='yes')? null:'NO';
			}

			if(!empty($args['attr'])){
				foreach($args['attr'] as $at=>$av){
					$_attr .= $at.'="'.$av.'" ';
				}
			}

			// input field
			$input = '';
			if($args['input']){
				$input_value = (!empty($args['var']))? 
					$args['var']: (!empty($args['default'])? $args['default']:'no');

				// Attribut values for input field
				$inputAttr = '';
				if(!empty($args['inputAttr'])){
					foreach($args['inputAttr'] as $at=>$av){
						$inputAttr .= $at.'="'.$av.'" ';
					}
				}

				// input field
				$input = "<input {$inputAttr} type='hidden' name='{$args['id']}' value='{$input_value}'/>";
			}

			$guide = '';
			if(!empty($args['guide'])){
				$guide = $this->tooltips($args['guide'], $args['guide_position']);
			}

			$label = '';
			if(!empty($args['label']))
				$label = "<label class='ajde_yn_btn_label' for='{$args['id']}'>{$args['label']}{$guide}</label>";

			return '<span id="'.$args['id'].'" class="ajde_yn_btn '.($no? 'NO':null).''.(($args['abs']=='yes')? ' absolute':null).'" '.$_attr.'><span class="btn_inner" style=""><span class="catchHandle"></span></span></span>'.$input.$label;
		}
	
	// tool tips
		function tooltips($content, $position='', $echo = false){
			// tool tip position
				if(!empty($position)){
					$L = ' L';
					
					if($position=='UL')
						$L = ' UL';
					if($position=='U')
						$L = ' U';
				}else{
					$L = null;
				}

			$output = "<span class='ajdeToolTip{$L} fa'><em>{$content}</em></span>";

			if(!$echo)
				return $output;			
			
			echo $output;
		}
		function echo_tooltips($content, $position=''){
			$this->tooltips($content, $position='',true);
		}

	// icon selector
		function icons(){
			include_once('fa_fonts.php');
			ob_start();?>			
			<div class='ajde_fa_icons_selector'>
				<div class="fai_in">
					<ul class="faicon_ul">
					<?php
					// $font_ passed from incldued font awesome file above
					if(!empty($font_)){
						foreach($font_ as $fa){
							echo "<li><i data-name='".$fa."' class='fa ".$fa."' title='{$fa}'></i></li>";
						}
					}
					?>						
					</ul>
				</div>
			</div>
			<?php return ob_get_clean();
		}

	// Options panel for custom posts
		function options_panel($fields, $PMV){

			global $ajde;
			$ajde->load_colorpicker();

			ob_start();

			echo "<div class='ajde_options_panel'>";
			foreach($fields as $field){
				$VAL = (!empty($field['id']) && !empty($PMV[$field['id']]))? $PMV[$field['id']][0]:false;
				$DEFAULT = (!empty($field['default']) && !empty($PMV[$field['default']]))? $PMV[$field['default']][0]:false;
				$TOOLTIP = !empty($field['tooltip'])? $this->tooltips($field['tooltip']):false;

				switch ($field['type']) {
					case 'note':
						echo "<p>{$field['content']}</p>";
					break;	
					case 'text':
						$DEF = !empty($field['default'])? $field['default']:'';
						echo "<p><label>{$field['label']}{$TOOLTIP}</label><input name='{$field['id']}' value='{$VAL}' placeholder='{$DEF}'/></p>";
					break;	
					case 'textarea':
						$content = $VAL? stripcslashes($VAL): 
							( !empty($field['default'])? $field['default']:'');
						echo "<p><label>{$field['label']}{$TOOLTIP}</label><textarea name='{$field['id']}'>{$content}</textarea></p>";
					break;
					case 'image':
						$image = ''; 
						
						echo "<p><label>{$field['label']}{$TOOLTIP}</label></p>";
						$preview_img_size = (empty($field['preview_img_size']))?'medium': $field['preview_img_size'];
						echo '<span class="custom_default_image" style="display:none">'.$image.'</span>';  
						if ($VAL) { $image = wp_get_attachment_image_src($VAL, $preview_img_size); $image = $image[0]; } 
						
						$img_code = (empty($image))? "<p class='custom_no_preview_img'><i>No Image Selected</i></p><img src='' style='display:none' class='custom_preview_image' />"
							: '<p class="custom_no_preview_img" style="display:none"><i>No Image Selected</i></p><img src="'.$image.'" class="custom_preview_image" alt="" />';
						
						echo '<input name="'.$field['id'].'" type="hidden" class="custom_upload_image" value="'.$VAL.'" /> 
							'.$img_code.'<br /> 
		                    <input class="custom_upload_image_button button" type="button" value="Choose Image" /> 
		                    <small> <a href="#" class="custom_clear_image_button">Remove Image</a></small> 
		                    <br clear="all" />';
					break;
					case 'color':
						$DEF = (!empty($field['default'])? $field['default']:'3d3d3d');
						$color = $VAL? $VAL: $DEF;
						echo "<p class='row_color'><label>{$field['label']}{$TOOLTIP}</label><em>
							<span id='{$field['id']}' class='colorselector' style='background-color:#{$color}' hex='{$color}'></span>
							<input type='hidden' name='{$field['id']}' data-default='{$DEF}'/>
						</em></p>";
					break;
					case 'wysiwyg':
						echo "<p><label>{$field['label']}{$TOOLTIP}</label></p>";
						$content = $VAL? stripcslashes($VAL): 
							( !empty($field['default'])? $field['default']:'');
						wp_editor($content, $field['id']);
					
					break;
					case 'select':
						if(empty($field['options'])) break;
						echo "<p><label>{$field['label']}</label> <select name='{$field['id']}'>";
						foreach($field['options'] as $sfield=>$sval){							
							echo "<option value='{$sfield}' ".($VAL==$sfield?'selected="selected"':'').">{$sval}</option>";
						}
						echo "</select>{$TOOLTIP}</p>";
					break;
					case 'yesno':
						echo "<p id='ajde_field_{$field['id']}'>".$this->html_yesnobtn(array('label'=>$field['label'],'input'=>true, 'default'=>$DEFAULT,
							'abs'=>'yes',
							'attr'=> (!empty($field['attr'])? $field['attr']:''),
							'var'=>$VAL,
							'id'=>$field['id'], 
							))."{$TOOLTIP}</p>";
					break;
					case 'beginafterstatement':	
						$yesno_val = (!empty($PMV[$field['val']]))? $PMV[$field['val']][0]:'no';
						echo "<div id='{$field['id']}' class='ajde_options_inner' style='display:".(($yesno_val=='yes')?'block':'none')."'>";
					break;
					case 'endafterstatement':
						echo "</div>";
					break;
					// for show if select
					case 'beginShowIf':
						$showIf = (!empty($PMV[$field['varname']]))? $PMV[$field['varname']][0]:false;
						$classes = implode(' ', $field['values']);

						echo "<div class='ajdeShowIf {$classes} {$field['varname']}' class='ajde_options_inner' style='display:".(($showIf && in_array($showIf, $field['values']))?'block':'none')."'>";
					break;
					case 'endShowIf':
						echo "</div>";
					break;
				}
			}
			echo "</div>";
			echo "<div id='ajde_clr_picker' class='cp cp-default' style='display:none; position:absolute; z-index:99;'></div>";

			return ob_get_clean();
			

		}

	// SHORTCODE GENERATOR
	// @version 1.0
	// shortcode generator interpret fields
		private $_in_select_step=false;
		public function shortcodeInterpret($var){
			global $ajde;

			// initial values
				$line_class = array('fieldline');
				$textDomain = $ajde->domain;

			ob_start();		
			
			// GUIDE popup
			$guide = (!empty($var['guide']))? $ajde->wp_admin->tooltips($var['guide'], 'L',false):null;

			// afterstatemnt class
			if(!empty($var['afterstatement'])){	$line_class[]='trig_afterst'; }

			// select step class
			if($this->_in_select_step){ $line_class[]='ss_in'; }


			if(!empty($var['type'])):

			switch($var['type']){
				// custom type and its html pluggability
				case has_action($textDomain."_shortcode_box_interpret_{$var['type']}"):
					do_action($textDomain."_shortcode_box_interpret_{$var['type']}");
				
				case 'YN':
					$line_class[]='ajdeYN_row';

					echo "<div class='".implode(' ', $line_class)."'>";
					echo $ajde->wp_admin->html_yesnobtn(array(
						'var'=>$var['var'],
						'default'=>( ($var['default']=='no')? 'NO':null ),
						'guide'=>(!empty($var['guide'])? $var['guide']:''), 
						'guide_position'=>(!empty($var['guide_position'])? $var['guide_position']:'L'),
						'label'=>$var['name'],
						'abs'=>'yes',
						'attr'=>array('codevar'=>$var['var'])
						));
					echo "</div>";					
				break;

				case 'customcode':	echo !empty($var['value'])? $var['value']:'';	break;
				
				case 'note':
					echo 
					"<div class='".implode(' ', $line_class)."'><p class='label'>".$var['name']."</p></div>";
				break;
				case 'text':
					echo 
					"<div class='".implode(' ', $line_class)."'>
						<p class='label'><input class='ajdePOSH_input' type='text' codevar='".$var['var']."' placeholder='".( (!empty($var['placeholder']))?$var['placeholder']:null) ."'/> ".$var['name']."".$guide."</p>
					</div>";
				break;

				case 'fmy':
					$line_class[]='fmy';
					echo 
					"<div class='".implode(' ', $line_class)."'>
						<p class='label'>
							<input class='ajdePOSH_input short' type='text' codevar='fixed_month' placeholder='eg. 11' title='Month'/><input class='ajdePOSH_input short' type='text' codevar='fixed_year' placeholder='eg. 2014' title='Year'/> ".$var['name']."".$guide."</p>
					</div>";
				break;
				case 'fdmy':
					$line_class[]='fdmy';
					echo 
					"<div class='".implode(' ', $line_class)."'>
						<p class='label'>
							<input class='ajdePOSH_input short shorter' type='text' codevar='fixed_date' placeholder='eg. 31' title='Date'/><input class='ajdePOSH_input short shorter' type='text' codevar='fixed_month' placeholder='eg. 11' title='Month'/><input class='ajdePOSH_input short shorter' type='text' codevar='fixed_year' placeholder='eg. 2014' title='Year'/> ".$var['name']."".$guide."</p>
					</div>";
				break;
				
				case 'taxonomy':
					
					$terms = get_terms($var['var']);
					
					$view ='';
					if(!empty($terms) && count($terms)>0){
						foreach($terms as $term){
							if(!isset($term)) continue;
							$view.= '<em>'.$term->name .' ('.$term->term_id.')</em>';
						}
					}

					$view_html = (!empty($view))? '<span class="ajdePOSH_tax">Possible Values <span >'. $view .'</span></span>': null;				
					
					echo 
					"<div class='".implode(' ', $line_class)."'>
						<p class='label'><input class='ajdePOSH_input' type='text' codevar='".$var['var']."' placeholder='".( (!empty($var['placeholder']))?$var['placeholder']:null) ."'/> ".$var['name']." {$view_html}</p>
					</div>";
				break;
				
				case 'select':
					echo 
					"<div class='".implode(' ', $line_class)."'>
						<p class='label'>
							<select class='ajdePOSH_select' codevar='".$var['var']."'>";
							$default = (!empty($var['default']))? $var['default']: null;
							foreach($var['options'] as $valf=>$val){
								echo "<option value='".$valf."' ".( $default==$valf? 'selected="selected"':null).">".$val."</option>";
							}						
							echo 
							"</select> ".$var['name']."".$guide."</p>
					</div>";
				break;

				// select steps
				case 'select_step':
					$line_class[]='select_step_line';
					echo 
					"<div class='".implode(' ', $line_class)."'>
						<p class='label '>
							<select class='ajdePOSH_select_step' data-codevar='".$var['var']."'>";
							
							foreach($var['options'] as $f=>$val){
								echo (!empty($val))? "<option value='".$f."'>".$val."</option>":null;
							}		
							echo 
							"</select> ".__($var['name'],$textDomain)."".$guide."</p>
					</div>";
				break;

				case 'open_select_steps':
					echo "<div id='".$var['id']."' class='ajde_open_ss' style='display:none' data-step='".$var['id']."' >";
					$this->_in_select_step=true;	// set select step section to on
				break;

				case 'close_select_step':	echo "</div>";	$this->_in_select_step=false; break;
				
			}// end switch

			endif;

			// afterstatement
			if(!empty($var['afterstatement'])){
				echo "<div class='ajde_afterst ".$var['afterstatement']."' style='display:none'>";
			}

			// closestatement
			if(!empty($var['closestatement'])){
				echo "</div>";
			}
			
			return ob_get_clean();
		}
	// get the HTML content for the shortcode generator
		public function get_content($shortcode_guide_array, $base_shortcode){
			global $ajde;
				
			$__text_a = __('Select option below to customize shortcode variable values', $ajde->domain);
			ob_start();

			?>		
				<div id='ajdePOSH_outter' class='<?php echo $base_shortcode;?>'>
					<h3 class='notifications '><em id='ajdePOSH_back' class='fa'></em><span id='ajdePOSH_subtitle' data-section='' data-bf='<?php echo $__text_a;?>'><?php echo $__text_a;?></span></h3>
					<div class='ajdePOSH_inner'>
						<div class='step1 steps'>
						<?php					
							foreach($shortcode_guide_array as $options){
								$__step_2 = (empty($options['variables']))? ' nostep':null;
								
								echo "<div class='ajdePOSH_btn{$__step_2}' step2='".$options['id']."' code='".$options['code']."'>".$options['name']."</div>";
							}	
						?>				
						</div>
						<div class='step2 steps' >
							<?php
								foreach($shortcode_guide_array as $options){
									if(!empty($options['variables']) ) {
										echo "<div id='".$options['id']."' class='step2_in' style='display:none'>";										
										// each shortcode option variable row
										foreach($options['variables'] as $var){
											echo $this->shortcodeInterpret($var);
										}	echo "</div>";
									}
								}						
							?>					
						</div><!-- step 2-->
						<div class='clear'></div>
					</div>
					<div class='ajdePOSH_footer'>
						<p id='ajdePOSH_var_'></p>
						<p id='ajdePOSH_code' data-defsc='<?php echo $base_shortcode;?>' data-curcode='<?php echo $base_shortcode;?>' code='<?php echo $base_shortcode;?>' >[<?php echo $base_shortcode;?>]</p>
						<span class='ajdePOSH_insert' title='Click to insert shortcode'></span>
					</div>
				</div>
			
			<?php
			return ob_get_clean();
		
		}

}