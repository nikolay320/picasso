<?php
if( !  class_exists( 'OptionsBuddy_Settings_Field' ) ) {
	require_once dirname( __FILE__ ) . '/class.options-buddy.php';
}

class BP_User_Notification_Email_Settings_Control_Admin {
 
    private $setting_page;
    
	private $message = '';//used to show notices
	
    public function __construct() {
        //create a options page
        //make sure to read the code below
        $this->setting_page = new OptionsBuddy_Settings_Page( 'bp_user_email_preference' );
        $this->setting_page->set_bp_mode();
        //by default,  example_page will be used as option name and you can retrieve all options by using get_option('example_page')
        //if you want use a different option_name, you can pass it to set_option_name method as below
        
        //$this->setting_page_example->set_option_name('my_new_option_name');
        //now all the options for example_page will be stored in the 'my_new_option_name' option and you can get it by using get_option('my_new_option_name')
        
        //if you don't want to group all the fields in single option and want to store each field individually in the option table, you can set that too as below
        // if you cann use_unique_option method, all the fields will be stored in individual option(the option name will be field name ) and 
        //you can retrieve them using get_option('field_name')
        
       // $this->setting_page_example->use_unique_option();
        
        //incase your mood changed and you want to use single option to store evrything, you can call this use_single_option method again
        //use single option is the default 
        //$this->setting_page_example->use_single_option();
        
        
        //if it pleases you, you can set the optgroup too, if you don't set,. it is same as the page name
        //$this->setting_page_example->set_optgroup('buddypress');
        //now, let us create an options page, what do you say
        
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    public function admin_init() {

        //set the settings
        
        $page = $this->setting_page;
        //add_section
        //you can pass section_id, section_title, section_description, the section id must be unique for this page, section descriptiopn is optional
        $section = $page->add_section( 'basic_section', __( 'Email Settings', 'bunec' ), __( 'Select the preference to be activated for the users when they register & activate their account.', 'bunec' ) );
        //since option buddy allows method chaining, you can start adding field in the same line above using ad_field or add_fields to add multiple field
        //or you can add fields later to a section by calling get_section('section_id');
        
       $settings_default = bunec_get_default_settings();
        
       foreach ( $settings_default as $key => $val ) {
            $section->add_field ( array(
                    'name' => $key,
                    'label' => $val['label'],
                    'desc' => isset( $val['desc'] ) ? $val['desc']: '',
                    'type' => 'select',
                    'default' => $val['val'],
                    'options' => array(
                        'yes' => 'Yes',
                        'no' => 'No'
                    )
                ) );
	   }
        
        
       
        $page->init();
        
		$this->bulk_update();
    }

    public function admin_menu() {
        add_options_page( __( 'User Notification Email Settings', 'bunec' ),  __( 'User Notification Email Settings', 'bunec' ),  'manage_options', 'bp-user-notification-email-control', array( $this, 'render' ) );
    }

	
	public function render() {
		$this->setting_page->render();//render settings page
		
		?>
		<div id="bunec-admin-bulk-actions">
			<form method="post" action="">
				<?php wp_nonce_field( 'bunec-bulk-update' );?>
				<p><?php _e( 'Bulk updating settings will reset it to your current default settings for all the users. It will reset the settings for the users who have already set their preference too.', 'bunec');?></p>
				<input type="submit" class="button button-primary" name="bunec-bulk-update" value="<?php _e( 'Bulk Update All Members Preference', 'bunec' );?>">
			</form>
			<style type="text/css">
				#bunec-admin-bulk-actions{
					margin-top: 20px;
				}
				#bunec-admin-bulk-actions p {
					background: #FFE600;
					color: #333;
					padding: 10px;
					margin-bottom: 10px;
				}
				#bunec-admin-bulk-actions input[type='submit'] {
					background: #D03E13;
					border-color: #AE3F1E;
				}
			</style>
		</div>
<?php
	}

	
	private function bulk_update() {
		//is it bulk update
		if(  empty( $_POST[ 'bunec-bulk-update'] ) ) {
			return ;
		}
		
		if( !  wp_verify_nonce( $_POST['_wpnonce'], 'bunec-bulk-update' ) ) {
			wp_die( __( 'Auth check failed.', 'bunec' ) );
		}
		//only admins with capability to manage users can do it
		if( !  current_user_can( 'delete_users' ) ) {
			return ;
		}
		
		//apologies for the implicit dependency
		$settings = BP_User_Notification_Email_Settings_Control::get_instance()->get_settings();
		if( empty( $settings ) ) {
			return ;
		}
		
		$keys = array_keys( $settings );
		
		if( empty( $keys ) ) {
			return ;
		}
		
		$meta_keys = array_map( 'esc_sql', $keys);
		
		$list = '\'' . join( '\', \'', $meta_keys ) . '\'';
		
		$meta_list = '(' . $list .')';
		
		//delete current preference
		
		global $wpdb;
		
		$updated = 0;
		
		$dele_sql = "DELETE FROM {$wpdb->usermeta} WHERE meta_key IN {$meta_list}";
		
		$wpdb->query( $dele_sql );
		
		//now update for each key
		foreach ( $settings  as $key => $val ) {
			
			$update_settings_query = "INSERT INTO {$wpdb->usermeta} (user_id, meta_key, meta_value) 
				SELECT  ID, %s as meta_key, %s as meta_value   FROM {$wpdb->users} where ID !=0";
				
			$prepered_query = $wpdb->prepare( $update_settings_query, $key, $val );
			
			$wpdb->query( $prepered_query );
			$updated = 1;
		}
		
		if( $updated ) {
			$this->message = __( 'Notification settings updated for all users', 'bunec' );
			add_action( 'admin_notices', array( $this, 'show_notice' ) );
		}
	}
	
	
	public function show_notice() {
		if( empty( $this->message ) ) {
			return ;
		}
		echo "<div class='updated'>";
		echo "<p>{$this->message}</p>";
		echo "</div>";
	}
}

new BP_User_Notification_Email_Settings_Control_Admin();