<?php
global $un_settings;
class UN_Settings {
	var $options;
	var $h;
	public function __construct(){
		global $hook_suffix, $un_upgrade;
		add_filter('un_notification_options', array($this, '_all_in_one_promo'));
		add_action('admin_print_styles-settings_page_usernoise', array($this, '_print_styles'));
		add_action('admin_enqueue_scripts', array($this, '_enqueue_scripts'), 9);
		$this->h = new HTML_Helpers_0_4;
		if (is_admin() && !defined('DOING_AJAX'))
		$this->options = new Plugin_Options_Framework_0_4(USERNOISE_MAIN,
			array(
				array(
					'slug' => 'general',
					'title' => __('General', 'usernoise'),
					'fields' => $this->get_general_settings()
				),
				array(
					'slug' => 'form',
					'title' => __('Form', 'usernoise'),
					'fields' => $this->get_form_settings()
				),
				array(
					'slug' => 'notification',
					'title' => __('Notifications', 'usernoise'),
					'fields' => $this->get_notification_settings()
				),
				array(
					'slug' => 'discussions',
					'title' => __('Discussions', 'usernoise'),
					'fields' => $this->get_discussions_fields()
				),
				array(
					'slug' => 'external',
					'title' => __('External', 'usernoise'),
					'fields' => $this->get_external_fields()
				),
				array(
					'slug' => 'pro',
					'title' => __('Pro', 'usernoise'),
					'fields' => $this->get_pro_fields()
				),

			),
			array('page_title' => __('Usernoise settings', 'usernoise')));
	}

	public function get_discussions_fields(){
		return array(
			array(
				'type' => 'html',
				'notitle' => true,
				'html' => __('<code>[feedbacks]</code> - use it to display the discussion on any page you want.', 'usernoise') . "<br>" .
				 __('<code>[feedbacks type="praise"]</code> will show specific feedback category by default.', 'usernoise') . "<br>" .
				 __('<code>[feedbacks notabs]</code> hides the feedback type tabs.', 'usernoise')
			),
			array(
				'type' => 'checkbox',
				'name' => UN_COMMENTS_ENABLE,
				'default' => true,
				'title' => __('Enable public comments', 'usernoise'),
			),
			array(
				'type' => 'text',
				'name' => UN_SHOW_ALL_FEEDBACKS_LINK,
				'default' => null,
				'title' => __('<code>[feedbacks]</code> page URL', 'usernoise'),
				'legend' => __('If you\'re using <code>[feedbacks]</code> shortcode
					on some of the pages - you can link there from the popup by entering
					its URL here.', 'usernoise')
			)
		);
	}

	public function get_pro_fields(){
		return array(
			array(
				'type' => 'html',
				'notitle' => true,
				'html' => __('This tab was discontinued in 4.0. Please contact the support if you need any options those were here.', 'usernoise')
			)
		);
	}

	public function _enqueue_scripts($type){
		global $submenu_file;
		if ($type == 'settings_page_usernoise'){
			wp_enqueue_script('un_settings', usernoise_url('/js/settings.js'), array('jquery'));
		}
	}

	public function _print_styles(){
		wp_enqueue_style('un-admin', usernoise_url('/css/admin.css'));
		wp_enqueue_style('un-font-awesome', usernoise_url('/vendor/font-awesome/font-awesome-embedded.css'));
	}

	function get_general_settings(){
		$positions = array(
			'left' => __('Left', 'usernoise'),
			'right' => __('Right', 'usernoise'),
			'bottom' => __('Bottom', 'usernoise'),
			'top' => __('Top', 'usernoise')
		);
		return array(
			array('type' => 'checkbox', 'name' => UN_PUBLISH_DIRECTLY, 'title' => __('Publish feedback directly without approval', 'usernoise'),
				'label' => __('Publish feedback directly without approval', 'usernoise'), 'default' => 0),
			array('type' => 'checkbox', 'name' => UN_DISABLE_ON_MOBILES,
				'title' => __('Disable on mobile devices', 'usernoise'),
				'label' => __('Disable on mobile devices', 'usernoise'),
				'default' => '1'),
			array('type' => 'checkbox', 'name' => UN_ONLY_REGISTERED,
				'title' => __("Only allow for registered users", 'usernoise'),
				'label' => __("Only allow for registered users", 'usernoise'),
				'default' => 0),
			array('type' => 'checkbox', 'name' => UN_SHOW_FEEDBACK_BUTTON,
				'title' => __('Show feedback button', 'usernoise'),
				'label' => __('Show feedback button', 'usernoise'),
				'default' => '1'
				),
			array('type' => 'select', 'name' => UN_FEEDBACK_BUTTON_POSITION,
				'title' => __('Position', 'usernoise'), 'values' => $this->h->hash2options($positions),
				'default' => 'left'),
			array('type' => 'text', 'name' => UN_FEEDBACK_BUTTON_TEXT,
				'title' => __('Text', 'usernoise'),
				'default' => _x('Feedback', 'button', 'usernoise')),
			array('type' => 'select', 'name' => UN_FEEDBACK_BUTTON_ICON, 'title' => __('Button Icon', 'usernoise'),
				'values' => $this->h->collection2options(un_get_icons(), 'icon', 'label', __('No icon', 'usernoise'), array('data-icon')),
				'legend' => __('Icon used on the button. <strong>May conflict with Twitter Bootstrap-based themes</strong>', 'usernoise'),
				'default' => ''
			),
			array('type' => 'color', 'name' => UN_FEEDBACK_BUTTON_TEXT_COLOR,
				'title' => __('Text color', 'usernoise'),
				'default' => '#FFFFFF'),
			array('type' => 'color', 'name' => UN_FEEDBACK_BUTTON_COLOR,
				'title' => __('Background color', 'usernoise'),
				'default' => '#0096DE'),
			);
	}

	function get_form_settings(){
		return array(
			array('type' => 'checkbox', 'name' => UN_FEEDBACK_FORM_SCREENSHOT_ENABLE,
				'title' => __('Enable screenshots', 'usernoise'),
				'label' => __('Enable screenshots', 'usernoise'),
				'default' => false,
				'legend' => __('Please note, that screenshots may be inaccurate, as they are based on html2canvas library', 'usernoise')
				),
			array('type' => 'select', 'name' => UN_FEEDBACK_FORM_SCREENSHOT_FORMAT,
				'title' => __('Screenshot format', 'usernoise'),
				'values' => array(array('PNG', 'png'),array('JPEG', 'jpeg')),
				'default' => 'jpeg',
				'show_if' => UN_FEEDBACK_FORM_SCREENSHOT_ENABLE
				),
			array('type' => 'text', 'name' => UN_FEEDBACK_FORM_SCREENSHOT_QUALITY,
				'title' => __('JPEG image quality', 'usernoise'),
				'default' => 0.2,
				'show_if' => UN_FEEDBACK_FORM_SCREENSHOT_ENABLE,
				'class' => 'micro',
				'legend' => __('Decrease quality if your screenshots do not save properly', 'usernoise')),
			array('type' => 'checkbox', 'name' => UN_FEEDBACK_FORM_SHOW_TYPE,
				'title' => __('Ask for feedback type', 'usernoise'),
				'label' => __('Ask for feedback type', 'usernoise'),
				'default' => '1'),
			array('type' => 'checkbox', 'name' => UN_FEEDBACK_FORM_SHOW_SUMMARY,
				'title' => __('Ask for a summary', 'usernoise'),
				'label' => __('Ask for a summary', 'usernoise'),
				'default' => '1'),
			array('type' => 'checkbox', 'name' => UN_FEEDBACK_FORM_SHOW_EMAIL,
				'title' => __('Ask for an email', 'usernoise'),
				'label' => __('Ask for an email', 'usernoise'),
				'default' => '1'),
			array('type' => 'checkbox', 'name' => UN_FEEDBACK_FORM_SHOW_NAME,
				'title' => __('Ask for name', 'usernoise'),
				'label' => __('Ask for name', 'usernoise'),
				'default' => '1'),
			);
	}

	function get_notification_settings(){
		return array(
			array('type' => 'checkbox', 'name' => UN_ADMIN_NOTIFY_ON_FEEDBACK,
				'title' => __('New feedback received admin notification', 'usernoise'),
				'label' => __('Enable', 'usernoise'),
				'default' => '1',
				'legend' =>
					sprintf(__('Notification emails will be sent to: <a href="mailto:%s">%s</a>', 'usernoise'),
					apply_filters('un_admin_notification_email', get_option('admin_email')),
					apply_filters('un_admin_notification_email', get_option('admin_email'))) . " " .
					sprintf(__('(you can change it at <a href="%s">%s</a> page).', 'usernoise'),
						admin_url('options-general.php'), __('General Options'))
					),
			array('type' => 'text',
				'name' => UNPRO_ADMIN_NOTIFICATION_EMAIL,
				'default' => get_option('admin_email'),
				'title' => __('Admin email for notifications', 'usernoise'),
				'class' => 'small'),
			array('type' => 'text', 'name' => UNPRO_NOTIFICATIONS_SITE,
				'title' => __('Site URL in notification text', 'usernoise'),
				'label' => __('Site URL in notification text', 'usernoise'),
				'default' => get_bloginfo('url'))
		);
	}

	function get_external_fields() {

		return array(array(
			'type' => 'custom',
			'html' => $this->get_external_html(),
			'title' => __('Code for external usage', 'usernoise')));
	}

	function get_external_html() {
		ob_start();
		global $un_integration;
		?>
		<!-- Put this code into your site's &lt;head&gt; tag -->
		<!-- You may need to exclude this line if jQuery is already loaded at your site -->
		<script type='text/javascript' src='<?php echo includes_url('js/jquery/jquery.js') ?>'></script>

		<link rel="stylesheet" type="text/css" href="<?php echo usernoise_url('/js/vendor/font/css/usernoise-embedded.css') ?>"></link>
		<link rel="stylesheet" type="text/css" href="<?php echo usernoise_url('/css/button.css') ?>"></link>
		<link rel="stylesheet" type="text/css" href="<?php echo usernoise_url('/js/popup/dist/popup.css') ?>"></link>
		<link rel="stylesheet" type="text/css" href="<?php echo usernoise_url('/js/discussion/dist/discussion.css') ?>"></link>
		<link rel="stylesheet" type="text/css" href="<?php echo usernoise_url('/vendor/icons/css/embedded.css') ?>"></link>

		<?php echo un_script(array('external' => true)) ?>
		<script type='text/javascript' src='<?php echo usernoise_url('/js/usernoise.js?ver='. UN_VERSION) ?>'></script>
		<script type='text/javascript' src='<?php echo usernoise_url('/js/button.js?ver='. UN_VERSION) ?>'></script>
		<!-- end of Usernoise code -->
		<?php
		return "<textarea rows='20'>" . ob_get_clean() . "</textarea>";
	}



	public function _all_in_one_promo($options){
		$options []= array('type' => 'custom',
			'title' => __('Notifications do not work right?', 'usernoise'),
			'html' => __("Check out <a href='http://codecanyon.net/item/all-in-one-email-for-wordpress/1290390'>All in One Email plugin</a>. It adds email options missing in WordPress natively.", 'usernoise'));
		return $options;
	}

}

function un_init_settings() {
	global $un_settings;
	$un_settings = new UN_Settings;
}

add_action('plugins_loaded', 'un_init_settings');


function un_get_option($name, $default = null){
	return trim(get_option($name)) ? get_option($name) : $default;
}

function un_set_option($name, $value){
	global $un_settings;
	return set_option($name, $value);
}
