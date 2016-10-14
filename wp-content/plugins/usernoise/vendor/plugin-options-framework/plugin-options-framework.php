<?php /*
WordPress plugin options framework
Version: 0.4
*/

require('vendor/html-helpers.php');

if (!class_exists('Plugin_Options_Framework_0_4')){
	class Plugin_Options_Framework_0_4{
		var $plugin_file;
		var $renderer;
		var $namespace;
		var $tabs;
		function __construct($plugin_file, $tabs = array(), $options = array()){
			$this->plugin_file = $plugin_file;
			$this->tabs = $tabs;
			$this->options = wp_parse_args($options);
			$this->renderer = isset($options['fields']) ? new $options['fields']($this) : new Plugin_Options_Framework_Fields_0_2_5($this);
			$this->namespace = isset($options['namespace']) ? $options['namespace'] : pathinfo($this->plugin_file, PATHINFO_FILENAME);
			add_action('admin_menu', array($this, '_admin_menu'));
			add_action('admin_init', array($this, '_admin_init'));
			add_filter('whitelist_options', array($this, '_whitelist_options'));
		}

		function set_fields($fields){
			$this->fields = $fields;
		}

		function _admin_enqueue_scripts() {
			wp_enqueue_script( 'farbtastic' );
			wp_enqueue_script( 'pof-admin', plugins_url( 'js/admin.js', __FILE__ ) );
			do_action( 'pof_enqueue_scripts_' . $this->namespace );
			foreach ( $this->tabs as $tab ) {
				if ( ! empty( $tab['js'] ) ) {
					wp_enqueue_script($tab['js']);
				}

			}
		}

		function get_default_storage_hash(){
			$res = array();
			$tab = $this->get_current_tab();
			foreach($tab['fields'] as $field){
				if (isset($field['name'])){
					$res[$this->get_storage_name($field['name'])] = isset($field['default']) ?
						$this->addslashes_deep($field['default']) : null;
				}
			}
			return $res;
		}

		function addslashes_deep($value) {
			if ( is_array($value) ) {
				$value = array_map('stripslashes_deep', $value);
			} elseif ( is_object($value) ) {
				$vars = get_object_vars( $value );
				foreach ($vars as $key=>$data) {
					$value->{$key} = stripslashes_deep( $data );
				}
			} else {
				$value = addslashes($value);
			}
			return $value;
		}

		function _whitelist_options($options){
			global $this_file, $parent_file, $action;
			if ($this_file != 'options.php' || $parent_file != 'options-general.php' ||
			 	(isset($_POST['option_page']) && $_POST['option_page'] != 'aioe') || $action != 'update'
				|| $this->namespace != stripslashes($_POST['_namespace']))
				return $options;
			if ($_REQUEST['reset'])
				$_POST = array_merge($_POST, $this->get_default_storage_hash());
			return $options;
		}

		function _admin_enqueue_styles(){
			wp_enqueue_style('farbtastic');
			wp_enqueue_style('pof-admin-style', plugins_url('css/admin-style.css', __FILE__));
			foreach($this->tabs as $tab){
				if (!empty($tab['css'])){
					wp_enqueue_style('pof-tab-' . $tab['slug'] . "-css", $tab['css']);
				}
			}
		}

		function _admin_init(){
			if ($tab = $this->get_current_tab()){
				foreach ( $tab['fields'] as $field ) {
					if ( isset( $field['name'] ) && ( ! isset( $field['disabled'] ) || ! $field['disabled'] ) ) {
						register_setting( $this->namespace, $this->get_storage_name( $field['name'] ),
							isset( $field['sanitize'] ) ? $field['sanitize'] : array( $this, '_return_same' ) );
					}

				}
			}
		}

		function _return_same($val){
			return $val;
		}

		function extract_plugin_name(){
			$data = get_plugin_data($this->plugin_file);
			return $data['Name'];
		}

		function page_title(){
			return isset($this->options['page_title']) ? $this->options['page_title'] : $this->extract_plugin_name() . " " . __('Settings');
		}

		function menu_title(){
			return isset($this->options['menu_title']) ? $this->options['menu_title'] : $this->extract_plugin_name();
		}

		function _admin_menu(){
			add_options_page($this->page_title(), $this->menu_title(), 'manage_options',
				$this->namespace, array($this, 'render'));
			add_action('admin_print_styles-settings_page_' .$this->namespace, array($this, '_admin_enqueue_styles'));
			add_action('admin_print_scripts-settings_page_' .$this->namespace, array($this, '_admin_enqueue_scripts'));
		}

		function get_storage_name($name){
			return $name;
		}



		function delete_option($name){
			delete_option($this->get_storage_name($name));
		}
		function get_tab($slug){
			foreach($this->tabs as $tab){
				if ($tab['slug'] === $slug)
					return $tab;
			}
		}
		function get_current_tab(){
			if (count($this->tabs))
				return isset($_REQUEST['tab']) ? $this->get_tab($_REQUEST['tab']) : $this->tabs[0];
			return null;

		}
		function render(){
			$tab = $this->get_current_tab();
			?>
			<div class="wrap">
					<?php do_action('pof_before_page_title', $this->namespace) ?>
					<?php screen_icon( 'plugins' ); ?>
					<h2><?php echo $this->page_title() ?></h2>
					<?php do_action('pof_after_page_title', $this->namespace) ?>
				<?php if ($tab): ?>
						<?php $this->renderer->render($this, $tab) ?>
				<?php else: ?>
					<div class="error">
						<p><?php _e('Please provide some settings fields when creating an options framework instance')?></p>
					</div>
				<?php endif ?>
				<?php do_action('pof_after_form', $this->namespace) ?>
			</div><?php
		}
	}
}

if (!class_exists('Plugin_Options_Framework_Fields_0_2_5')){
	class Plugin_Options_Framework_Fields_0_2_5{
		var $pof;
		var $h;
		function __construct($pof){
			$this->pof = $pof;
			$this->h = new HTML_Helpers_0_4();
		}

		function render($pof, $tab){
			echo '<div id="no-js-message" class="error"><p>' . __('This page requires JavaScript to be enabled in your browser. Please enable JavaScript.') . "</p></div>";
			echo '<div id="pof" class="hide-if-no-js">';
			$tabs_count =  $this->render_tabs($pof, $tab);
			$section_index = 0;
			$tab_index = 0;
			echo '<form action="options.php" method="post" id="pof-form"> ';
			$this->h->hidden_field('_namespace', $this->pof->namespace);
			$this->h->hidden_field('tab', $tab['slug']);
			$this->h->hidden_field('reset', '', array('id' => 'reset'));
			settings_fields($this->pof->namespace);
			echo "\n<div class=\"pof-tabs\" id=\"pof-tabs\">\n";
			foreach($tab['fields'] as $field){
				switch($field['type']){
					case 'tab':
						if ($section_index){
							echo "\n<div style='clear: both'></div></div></div></div></div>\n";
						}
						if ($tab_index) echo "\n<div style='clear: both'></div></div>\n"; // close current tab
						echo ("\n<div class=\"pof-tab" . ($tab_index ? ' pof-tab-hidden' : '') . "\" id=\"" . esc_attr($this->tab_id($tab_index, $field))  . "\">\r");
						$tab_index++;
						$section_index = 0;
						break;
					case 'section':
						if ($section_index) echo "\n<div style='clear: both'></div></div></div></div></div>\n"; //close current section
						echo "<div class=\"metabox-holder\"" . (isset($field['show_if']) ? 'data-show_if="' . $this->input_name($field['show_if']) . '" ' : '') . ">\n<div class=\"postbox\"><div class=\"group\">";
						echo '<h3>' . $field['title'] . "</h3>\n<div class=\"inside\">";
						$section_index++;
						break;
					case 'section_break':
						if ($section_index) {
							echo "\n<div style='clear: both'></div></div></div></div></div>\n"; //close current section
							$section_index = 0;
						}
						break;
					default:
						$this->render_field($field);
				}
			}
			if ($section_index){
				echo("<div style='clear: both'></div>");
				echo "\n</div></div></div></div>\n";
			}

				echo "\n<div style='clear: both'></div></div></div>\n"; // tabs
			?>
			<div id="pof-submit">
				<input type="submit" class="button-primary" name="update" value="<?php esc_attr_e( 'Save Options', 'usernoise' ); ?>" />
				<input type="submit" class="reset-button button-secondary hide-if-no-js" value="<?php esc_attr_e( 'Restore Defaults', 'usernoise' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to reset. All the settings will be reset to defaults!' ) ); ?>' );" id="button-reset" />
				<div class="clear"></div>
			</div>
			</form>
			<?php
		}

		function input_name($name){
			return $this->pof->get_storage_name($name);
		}

		function render_field($field){
			$this->render_field_prefix($field);
			$method = $field['type'];
			$this->$method($field);
			$this->render_field_suffix($field);
		}

		function render_field_prefix($field){
			echo "<div class=\"field field-{$field['type']}" . (isset($field['class']) ? " " . $field['class'] : '') . "\" " . (isset($field['name']) ? "id=\"field-{$field['name']}\" " : '') . (isset($field['show_if']) ? 'data-show_if="' . $this->input_name($field['show_if']) . '" ' : '') . ">\n";
			if (!(isset($field['notitle']) && $field['notitle']))
				echo "<h4 class=\"heading field-title\">" .
				(isset($field['title']) ? $field['title'] : 'Noname field') . "</h4>\n";
			if (!(isset($field['nowrapper']) && $field['nowrapper']))
			echo "<div class=\"option\">\n";
		}

		function render_field_suffix($field){
			if (!(isset($field['nowrapper']) && $field['nowrapper']))
				echo "</div>\n";
			if (isset($field['legend'])){
				echo "<div class=\"legend\">{$field['legend']}</div>";
			}
			echo "</div>";
		}

		function checkbox($field){
			$classes = array('checkbox');
			if (isset($field['class']))
				$classes []= $field['class'];
			$checkbox_attr = array('class' => $classes);
			if (isset($field['disabled']) && $field['disabled'])
				$checkbox_attr['disabled'] = 'disabled';
			$this->h->checkbox($this->input_name($field['name']), 1,
				get_option($field['name'], $field['default']), $checkbox_attr
				);
			$label = isset($field['label']) ? $field['label'] : $field['title'];
			$this->h->label($label, array('for' => $this->input_name($field['name'])));
		}

		function field_classes($defaults, $field){
			$classes = $defaults;
			if (isset($field['class']) && $field['class']){
				if (is_string($field['class']))
					$field['class'] = explode(' ', $field['class']);
				$classes = array_merge($classes, $field['class']);
			}
			return $classes;
		}

		function checkboxes($field){
			foreach($field['values'] as $value => $label){
				$checkbox = $this->h->_checkbox($this->input_name($field['name'] . "[]"), $value,
					in_array($value, get_option($field['name'], isset($field['default']) ? $field['default'] : array()))
				);
				$this->h->label($checkbox . " " . $label . "<br>");
			}

		}


		function select($field){
			$attr = array();
			if (isset($field['disabled']) && $field['disabled'])
				$attr['disabled'] = 'disabled';
			$this->h->select($this->input_name($field['name']), $field['values'],
				get_option($field['name'], isset($field['default']) ? $field['default'] : null), $attr);
		}

		function color($field){
			?>
			<div class="pof-color-picker">
				<?php $this->h->text_field($this->input_name($field['name']), get_option($field['name'])); ?>
				<input type="button" class="pickcolor button hide-if-no-js" value="<?php esc_attr_e( 'Select a Color', 'usernoise'); ?>" />
				<div class="picker" id="picker-<?php echo sanitize_title_with_dashes($field['name']) ?>" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
			</div>
			<?php
		}

		function custom($field){
			echo $field['html'];
		}

		function html($field){
			echo $field['html'];
		}

		function text($field){
			$attr = array();
			if (isset($field['disabled']) && $field['disabled'])
				$attr['disabled'] = 'disabled';
			$attr['class'] = $this->field_classes(array('text'), $field);
			$this->h->text_field($this->input_name($field['name']),
				get_option($field['name'], isset($field['default']) ? $field['default'] : null), $attr);
		}
		function number($field){
			$attr = array();
			if (isset($field['disabled']) && $field['disabled'])
				$attr['disabled'] = 'disabled';
			$attr['class'] = $this->field_classes(array('text'), $field);
			$this->h->number_field($this->input_name($field['name']),
				get_option($field['name'], isset($field['default']) ? $field['default'] : null), $attr);
		}

		function password($field){
			$attr = array();
			if (isset($field['disabled']) && $field['disabled'])
				$attr['disabled'] = 'disabled';
			$attr['class'] = $this->field_classes(array('password'), $field);
			$this->h->password_field($this->input_name($field['name']),
				$this->pof->get_option($field['name']), $attr);
		}

		function editor($field){
			if (function_exists('wp_editor')){
				wp_editor( $this->pof->get_option($field['name']), $this->input_name($field['name']), array( 'media_buttons' => true ) );
			} else {
				$this->textarea($field);
				echo "<small>";
				_e('Upgrade to WordPress 3.3 or later to enable WYSIWYG editor');
				echo "</small>\n";
			}
		}

		function radio($field){
			foreach($field['options'] as $text => $value){
				echo "<div class=\"radio-wrapper\">\n";
				echo '<input id="' . $field['name'] . "_". esc_attr(sanitize_title_with_dashes($value)) . '" class="radio' . (isset($field['class']) ? " " .
					$field['class'] : '') . '" type="radio" name="' . $this->input_name($field['name']) .
					'" '. checked( $this->pof->get_option($field['name']), $value, false) .' value="' . esc_attr($value) . '" />';
					echo '<label class="title" for="'. $field['name'] . "_" . esc_attr(sanitize_title_with_dashes($value)) . '">' . $text . '</label>';
				echo "</div>\n";
			}
		}

		function textarea($field){
			$attr = array();
			if (isset($field['disabled']) && $field['disabled'])
				$attr['disabled'] = 'disabled';
			if (isset($field['readonly']) && $field['readonly'])
				$attr['disabled'] = 'readonly';

			$attr['class'] = $this->field_classes(array('textarea'), $field);
			$attr['rows'] = isset($field['rows']) ? $field['rows'] : get_option('default_post_edit_rows');
			$this->h->textarea($this->input_name($field['name']),
				$this->pof->get_option($field['name']), $attr
			);
		}

		function tab_id($tab_index, $field){
			return isset($field['id']) ? $field['id'] : 'nav-tab-' . $tab_index++;
		}

		function get_tabs($fields){
			$tab_index = 0;
			$tabs = array();
			foreach($fields as $field){
				if ($field['type'] == 'tab'){
					$tabs[$this->tab_id($tab_index++, $field)] = $field['title'];
				}
			}
			return $tabs;
		}

		function render_tab($config, $active){
			$classes = array('nav-tab');
			if ($active)
				$classes []= 'nav-tab-active';
			if (isset($config['tab-class']))
				$classes []= $config['tab-class'];
			$this->h->link_to($config['title'], "?page=" . $_REQUEST['page'] . "&tab=" . $config['slug'],
				array('class' => $classes, 'id' => $config['slug']));
		}

		function render_tabs($pof, $tab){ ?>
			<h2 class="nav-tab-wrapper" id="pof-tabs-nav">
			<?php
			$index = 0;

			foreach($pof->tabs as $name => $tab_config){
				$this->render_tab($tab_config, $tab['slug'] == $tab_config['slug'] || !$tab && $index++ == 0);
			}

			?></h2><?php
		}
	}
}
