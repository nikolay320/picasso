<?php
/*
 * BPQA admin settings page
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

/**
 * BPQA admin function - add menu items to admin dashboard
 */
function bpqa_admin_menu() {

    //add settings page menu item
    add_submenu_page( 'options-general.php', 'quick-activity', __( 'Quick Activity', 'BPQA' ), 'manage_options', 'bpqa_settings', 'bpqa_settings_page' );

}
add_action( 'admin_menu', 'bpqa_admin_menu' );

function bpqa_admin_enqueue_scripts() {
	wp_enqueue_script( 'bpqa-codemirror', BPQA_URL .'/assets/js/codemirror.min.js', array( 'jquery' ), false );
	wp_enqueue_style( 'bpqa-codemirror', BPQA_URL .'/assets/css/codemirror.css' );
	
}
add_action( 'admin_enqueue_scripts', 'bpqa_admin_enqueue_scripts' );
/*
 * Set the default options page if not exist
 */

function bpqa_options_init() {
    global $bpqa_options;
    
    //get the options 
    $bpqa_options = get_option( 'bpqa_options' );
    
    //check if not exists
    if ( false === $bpqa_options ) {
        //default options
    	$options = array(
    			'toolbar_button'  	=> array( 'use' => 0, 'title' => 'Say Something' ),
    			'floating_button' 	=> array( 'use' => 1, 'top' => '100', 'location' => 'left', 'icon' => 'bpqa-button-light-blue.png' ),
    			'form'				=> array( 'max_characters' => '', 'text_placeholder' => 'Post something to activity...' ),
    			'style'				=> array( 'custom_css' => '' )
    	);
    	
        //update default options
        update_option( 'bpqa_options', $options );
    }

}
add_action( 'admin_init', 'bpqa_options_init' );

/*
 * Main settings page
 */

function bpqa_settings_page() {
    ?>
    <div class="wrap">
        <h2><?php _e( 'Buddypress Quick Activity Settings Page', 'BPQA' ); ?></h2>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'bpqa_options' );
            do_settings_sections( 'bpqa' );
            ?>
            <div class="clear"></div>
            <input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'BPQA' ); ?>" />
        </form>
        <br >
        <br >
        <div style="font-size: 14px;font-style: italic;">
            <h2 style="margin-bottom:10px"><?php _e( 'Shortcodes usage', 'BPQA' ); ?></h2>
            
            <h3><?php _e( 'Quick Activity button shortcode</h4>','BPQA' ); ?></h3>
            <p><code>[bpqa_button]</code> - <?php _e('use in the content area of a post or page.','BPQA' ); ?></p>
            <p><code><&#63;php echo do_shortcode('[bpqa_button]'); &#63;></code> - <?php _e( 'Use anywhere in a template files.','BPQA' ); ?></p>
            <h4><?php _e( 'Shorcode attributes:','BPQA');?></h4>
            <ol>
                <li><strong>type</strong> - <?php _e( 'Excepts the values button, link or image. The "type" attribute controls the way the "Quick Activity" button will be displayed.','BPQA'); ?>
                    <ol>
                        <li><?php _e( 'The value "button" will create an HTML input type button.','BPQA'); ?></li>
                        <li><?php _e( 'The value "Link" will create an hyperlink','BPQA'); ?></li>
                        <li><?php _e( 'The value "Image" will display the "Quick Activity" button as an image','BPQA'); ?></li>
                    </ol>
                </li>
                <li>
                	<strong>img</strong> - 
                	<?php _e( "the attribute img will only work when choosing \"image\" as a value for the attribute \"type\". Using the attribute \"img\" you can choose the image that will be displayed as the \"Quick Activity\" button.",'BPQA' ); ?>
                	<?php _e( "In order to use the \"img\" attribute its value needs to be the name of the image that you want to use. The image must exist in the plugin\'s directory under \"buttons\" folder.",'BPQA' ); ?>
                    <code>wp-content/plugins/buddypress-quick-activity/buttons</code>
                </li>
                <li>
                	<strong>title</strong> - 
                	<?php _e( "The \"title\" attribute will work when the \"type\" attributes gets either \"button\" or \"link\" values. Using the \"title\" attribute you can define the label of the  \"Quick activity\" button or link.",'BPQA'); ?></li>
                <li>
                	<strong>class</strong> - <?php _e( "give the \"Quick Activity\" button a custom CLASS tag",'BPQA'); ?></li>
                <li><strong>id</strong> - <?php _e( "give the \"Quick Activity\" button a custom ID tag",'BPQA'); ?></li>
            </ol>
            
            <h3><?php _e( 'Quick Activity in-page form shortcode</h4>','BPQA' ); ?></h3>
            <p><code>[bpqa_form]</code> - <?php _e('use in the content area of a post or page.','BPQA' ); ?></p>
            <p><code><&#63;php echo do_shortcode('[bpqa_form]'); &#63;></code> - <?php _e( 'Use anywhere in a template files.','BPQA' ); ?></p>
            <h4><?php _e( 'Shorcode attributes:','BPQA');?></h4>
                			
            <ol>
                <li>
                	<strong>template</strong> - 
                	<?php _e( "Set the form template. The value needs to be the name of the template's folder. The templates can be found in ",'BPQA'); ?>
                	<code>wp-content/plugins/buddypress-quick-activity/form-templates/</code>
                </li>
                <li>
                	<strong>post_to_groups</strong> - 
                	<?php _e( "Set the value to 1 to allow users to post activity to groups.",'BPQA' ); ?>
                </li>
                <li>
                	<strong>max_characters</strong> - 
                	<?php _e( "Use this attribute if you'd like to limit the number of characters that can be used in the textarea.",'BPQA'); ?>
                </li>
            </ol>

        </div>

        <h4><?php _e( 'Shortcodes examples:','BPQA'); ?></h2>
        <ol>
            <li>
            	<?php _e( "Create \"Quick Activity\" form with the lightblue template",'BPQA'); ?>
            	 - <code>[bpqa_form template="lightblue"]</code>	
            </li>
            <li>
            	<?php _e( "Create \"Quick Activity\" form with the green template, allow posting to groups and set the max characters to 50",'BPQA'); ?>
            	- <code>[bpqa_form template="green" post_to_groups="1" max_characters="50"]</code>
            </li>
        </ol>
        <div style="padding:10px 0px;margin-top:20px;border-top:1px solid #e5e5e5;color:#777;"><?php _e( 'BuddyPress Quick Activity developed by <a style="text-decoration:none;" href="http://geomywp.com" target="_blank">Eyal Fitoussi</a>', 'BPQA' ); ?></div>
    </div>
    <?php

}
/*
 * register fields
 */

function bpqa_admin_register_settings() {
    global $blog_id;

    register_setting( 'bpqa_options', 'bpqa_options', 'bpqa_admin_options_validate' );

    // toolbar button sections and field
    add_settings_section( 'bpqa_toolbar_button_options', __( 'Quick Activity Admin-bar Button', 'BPQA' ), 'bpqa_toolbar_button_section_text', 'bpqa' );
    add_settings_field( 'bpqa_admin_toolbar_use', __( 'Admin-bar button', 'BPQA' ), 'bpqa_admin_toolbar_use', 'bpqa', 'bpqa_toolbar_button_options' );
    add_settings_field( 'bpqa_admin_toolbar_title', __( 'Title', 'BPQA' ), 'bpqa_admin_toolbar_title', 'bpqa', 'bpqa_toolbar_button_options' );

    //floating button sections and fields
    add_settings_section( 'bpqa_floating_button_options', __( 'Quick Activity Floating Button ', 'BPQA' ), 'bpqa_floating_button_section_text', 'bpqa' );
    add_settings_field( 'bpqa_admin_floating_use', __( 'Floating button', 'BPQA' ), 'bpqa_admin_floating_use', 'bpqa', 'bpqa_floating_button_options' );
    add_settings_field( 'bpqa_admin_floating_top', __( 'Top offset', 'BPQA' ), 'bpqa_admin_floating_top', 'bpqa', 'bpqa_floating_button_options' );
    add_settings_field( 'bpqa_admin_floating_locaiton', __( 'Location', 'BPQA' ), 'bpqa_admin_floating_location', 'bpqa', 'bpqa_floating_button_options' );
    add_settings_field( 'bpqa_admin_floating_icon', __( 'Image', 'BPQA' ), 'bpqa_admin_floating_icon', 'bpqa', 'bpqa_floating_button_options' );
    
    //form sections and fields
    add_settings_section( 'bpqa_form_options', __( 'Quick Activity Pop-up Form Options ', 'BPQA' ), 'bpqa_form_section_text', 'bpqa' );
    add_settings_field( 'bpqa_admin_form_popup_template', __( 'Form template', 'BPQA' ), 'bpqa_admin_form_popup_template', 'bpqa', 'bpqa_form_options' );
    add_settings_field( 'bpqa_admin_form_text_placeholder', __( 'Textarea placeholder', 'BPQA' ), 'bpqa_admin_form_text_placeholder', 'bpqa', 'bpqa_form_options' );
    add_settings_field( 'bpqa_admin_form_max_characters', __( 'Maximum characters', 'BPQA' ), 'bpqa_admin_form_max_characters', 'bpqa', 'bpqa_form_options' );
    add_settings_field( 'bpqa_admin_form_groups_publish', __( 'Post to groups', 'BPQA' ), 'bpqa_admin_form_groups_publish', 'bpqa', 'bpqa_form_options' );

    //form sections and fields
    add_settings_section( 'bpqa_style_options', __( 'Styling', 'BPQA' ), 'bpqa_style_section_text', 'bpqa' );
    add_settings_field( 'bpqa_admin_style_custom_css', __( 'Add custom CSS', 'BPQA' ), 'bpqa_admin_style_custom_css', 'bpqa', 'bpqa_style_options' );
    
}
add_action( 'admin_init', 'bpqa_admin_register_settings' );

function bpqa_toolbar_button_section_text() {
    ?>
    <p><?php _e( 'Add "Quick Activity" button to the admin-bar.', 'BPQA' ); ?></p>
    <?php

}

function bpqa_admin_toolbar_use() {
    $bpqa_options = get_option( 'bpqa_options' );
    ?>
    <input type="checkbox" name="bpqa_options[toolbar_button][use]" <?php if ( isset( $bpqa_options['toolbar_button']['use'] ) && $bpqa_options['toolbar_button']['use'] == 1 ) echo 'checked="checked"'; ?> value="1" />
    <span class="description"><?php _e( 'Enable Quick Activity button in the toolbar', 'BPQA' ); ?></span>
    <?php

}

function bpqa_admin_toolbar_title() {
    $bpqa_options = get_option( 'bpqa_options' );
    ?>
    <input type="text" name="bpqa_options[toolbar_button][title]" value="<?php echo $bpqa_options['toolbar_button']['title']; ?>" />
    <span class="description"><?php _e( "Button's label", 'BPQA' ); ?></span>
    <?php

}

function bpqa_floating_button_section_text() {
    ?>
    <p><?php _e( 'Display a floating "Quick Activity" button in the left or right side of the screen', 'BPQA' ); ?></p>
    <?php

}

function bpqa_admin_floating_use() {
    $bpqa_options = get_option( 'bpqa_options' );
    ?>
    <input type="checkbox" <?php if ( isset( $bpqa_options['floating_button']['use'] ) && $bpqa_options['floating_button']['use'] == 1 ) echo 'checked="checked"'; ?> name="bpqa_options[floating_button][use]" value="1" />
    <span class="description"><?php _e( 'Enable floating button', 'BPQA' ); ?></span>
    <?php

}

function bpqa_admin_floating_top() {
    $bpqa_options = get_option( 'bpqa_options' );
    ?>
    <input type="text" name="bpqa_options[floating_button][top]" size="5" value="<?php echo $bpqa_options['floating_button']['top']; ?>" />px
    <span class="description"><?php _e( 'Define the "Top" attribute for the button in pixels', 'BPQA' ); ?></span>
    <?php

}

function bpqa_admin_floating_location() {
    $bpqa_options = get_option( 'bpqa_options' );
    ?>
    <select name="bpqa_options[floating_button][location]" >
        <option <?php if ( $bpqa_options['floating_button']['location'] == 'left' ) echo 'selected="selected"'; ?> value="left"><?php _e( 'Left', 'BPQA' ); ?></option>
        <option <?php if ( $bpqa_options['floating_button']['location'] == 'right' ) echo 'selected="selected"'; ?> value="right"><?php _e( 'Right', 'BPQA' ); ?></option>
    </select>
    <span class="description"><?php _e( "In which side of the screen would you like to display the button?", 'BPQA' ); ?></span>
    <?php

}

function bpqa_admin_floating_icon() {
    $bpqa_options = get_option( 'bpqa_options' );
    $map_icons    = glob( BPQA_PATH . '/buttons/*.*' );
    $display_icon = BPQA_URL . '/buttons/';
    $cic          = 1;
    foreach ( $map_icons as $map_icon ) :
        echo '<span><input type="radio" name="bpqa_options[floating_button][icon]" value="' . basename( $map_icon ) . '"';
        echo ( ( isset( $bpqa_options['floating_button']['icon'] ) && $bpqa_options['floating_button']['icon'] == basename( $map_icon ) ) || $cic == 1 ) ? ' checked="checked"' : '';
        echo ' />
		<img src="' . $display_icon . basename( $map_icon ) . '" height="40px" width="35px"/></span>';
        $cic++;
    endforeach;

}

function bpqa_form_section_text() {
	?>
    <p><?php _e( 'Setup the Quick Activity pop-up form.', 'BPQA' ); ?></p>
    <?php
}

function bpqa_admin_form_popup_template() {
	$bpqa_options = get_option( 'bpqa_options' );
	
	echo '<select name="bpqa_options[form][popup_template]" >';
	foreach ( glob( BPQA_PATH.'/form-templates/*', GLOB_ONLYDIR ) as $dir ) {
		$selected = ( !empty( $bpqa_options['form']['popup_template'] ) && $bpqa_options['form']['popup_template'] == basename($dir) ) ? 'selected="selected"' : '';
		echo '<option value="'.basename($dir).'" '.$selected.'>'.basename($dir).'</div>';
	}	
	echo '</select>';
	echo '<span class="description">'.__( "Choose the pop-up form template", 'BPQA' ).'</span>';

}

function bpqa_admin_form_text_placeholder() {
	$bpqa_options = get_option( 'bpqa_options' );
	?>
    <input type="text" size="25" name="bpqa_options[form][text_placeholder]" value="<?php echo ( isset( $bpqa_options['form']['text_placeholder'] ) ) ? $bpqa_options['form']['text_placeholder'] : ''; ?>" />
    <span class="description"><?php _e( 'Type the default ( placeholder ) text that will be displayed in the textarea.', 'BPQA' ); ?></span>
    <?php

}

function bpqa_admin_form_max_characters() {
	$bpqa_options = get_option( 'bpqa_options' );
	?>
    <input type="text" size="5" name="bpqa_options[form][max_characters]" value="<?php echo ( isset( $bpqa_options['form']['max_characters'] ) ) ? $bpqa_options['form']['max_characters'] : ''; ?>" />
    <span class="description"><?php _e( 'Enter the maximum number of characters to be used in the text area ( leave blank for unlimited ).', 'BPQA' ); ?></span>
    <?php

}

function bpqa_admin_form_groups_publish() {
    $bpqa_options = get_option( 'bpqa_options' );
    ?>
    <input type="checkbox" name="bpqa_options[form][groups_publish]" <?php if ( isset( $bpqa_options['form']['groups_publish'] ) && $bpqa_options['form']['groups_publish'] == 1 ) echo 'checked="checked"'; ?> value="1" />
    <span class="description"><?php _e( 'Allow posting activity to groups', 'BPQA' ); ?></span>
    <?php
}

function bpqa_style_section_text() {
	?>
    <p><?php _e( 'add custom css', 'BPQA' ); ?></p>
    <?php

}

function bpqa_admin_style_custom_css() {
	$bpqa_options = get_option( 'bpqa_options' );
	?>
    <textarea id="bpqa-css-textarea" size="15" name="bpqa_options[style][custom_css]" ><?php echo ( isset( $bpqa_options['style']['custom_css'] ) ) ? $bpqa_options['style']['custom_css'] : ''; ?></textarea>
   
    <script>
    	jQuery(document).ready(function() {

    		var editor = CodeMirror.fromTextArea(document.getElementById("bpqa-css-textarea"), {
    		  mode: "application/xml",
    		  styleActiveLine: true,
    		  lineNumbers: true,
    		  lineWrapping: true
    		});
    		        	
   		});
    </script>
    <?php
}
/*
 * Validate the updated values and return them to be saved 
 */

function bpqa_admin_options_validate( $input ) {

    $bpqa_options = get_option( 'bpqa_options' );

    $valid_input = $bpqa_options;

    $valid_input['toolbar_button']['use']       = ( isset( $input['toolbar_button']['use'] ) && $input['toolbar_button']['use'] == 1 ) ? 1 : 0;
    $valid_input['toolbar_button']['title']     = ( !empty( $input['toolbar_button']['title'] ) ) ? $input['toolbar_button']['title'] : 'Say Something';
    $valid_input['floating_button']['use']      = ( isset( $input['floating_button']['use'] ) && $input['floating_button']['use'] == 1 ) ? 1 : 0;
    $valid_input['floating_button']['location'] = ( $input['floating_button']['location'] == 'right' ) ? 'right' : 'left';
    $valid_input['floating_button']['top']      = ( !empty( $input['floating_button']['top'] ) ) ? $input['floating_button']['top'] : '100';
    $valid_input['floating_button']['icon']     = $input['floating_button']['icon'];
    $valid_input['form']['popup_template']      = $input['form']['popup_template'];
    $valid_input['form']['text_placeholder']    = $input['form']['text_placeholder'];
    $valid_input['form']['max_characters']      = ( !empty( $input['form']['max_characters'] ) ) ? $input['form']['max_characters'] : '';
    $valid_input['form']['groups_publish']      = ( isset( $input['form']['groups_publish'] ) && $input['form']['groups_publish'] == 1 ) ? 1 : 0;
    $valid_input['style']['custom_css']      	= ( !empty( $input['style']['custom_css'] ) ) ? $input['style']['custom_css'] : '';
     
    return $valid_input;
}
