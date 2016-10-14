<?php
/*
Plugin Name:    Crazy Pills
Description:    Stop the shortcode madness with Crazy Pills. Build buttons, boxes, beautiful lists, and highlight text right from your editor, with live preview.
Author:         Hassan Derakhshandeh
Version:        0.4.2
Text Domain:    crazy-pills
Domain Path:    /languages
*/

class CrazyPills {

	var $base_url;

	function __construct() {
		add_action( 'init', array( $this, 'add_button_3' ) );
		add_action( 'init', array( $this, 'i18n' ) );
		add_filter( 'mce_css', array( $this, 'mce_css' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		$this->base_url = trailingslashit( plugins_url( '', __FILE__ ) );
	}

	/**
	 * Load stylesheet for editor preview
	 *
	 * @link http://codex.wordpress.org/Plugin_API/Filter_Reference/mce_css
	 * @return string mce_css
	 * @since 0.1
	 */
	function mce_css( $mce_css ) {
		if( ! empty( $mce_css ) ) $mce_css .= ',';
		$mce_css .= $this->base_url . 'css/styles.css';
		return $mce_css;
	}

	function add_button_3() {
		if ( current_user_can('edit_posts') &&  current_user_can('edit_pages') ) {
			add_filter( 'mce_external_plugins', array( $this, 'mce_external_plugins' ) );
			add_filter( 'mce_buttons_3', array( $this, 'mce_buttons_3' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
			/* make localized strings available on front-end as well, for plugins using editor on the front-end */
			add_action( 'wp_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		}
	}

	function mce_buttons_3( $buttons ) {
		array_push( $buttons, 'callouts', 'highlights', 'buttons', 'checks', 'bullets' );

		return $buttons;
	}

	function mce_external_plugins( $plugin_array ) {
		$plugin_array['callouts'] = $this->base_url . 'js/editor.js';
		$plugin_array['highlights'] = $this->base_url . 'js/editor.js';
		$plugin_array['buttons'] = $this->base_url . 'js/editor.js';
		$plugin_array['checks'] = $this->base_url . 'js/editor.js';
		$plugin_array['bullets'] = $this->base_url . 'js/editor.js';

		return $plugin_array;
	}

	function enqueue() {
		wp_enqueue_style( 'crazypills', $this->base_url . 'css/styles.css', array(), '0.4' );
	}

	public function i18n() {
		load_plugin_textdomain( 'crazy-pills', false, '/languages' );
	}

	function admin_enqueue() {
		wp_localize_script( 'editor', 'crazyPills', array(
			'labels' => array(
				'callouts' => __( 'Callouts', 'crazy-pills' ),
				'callout_default' => __( 'Message box contents...', 'crazy-pills' ),
				'info' => __( 'Info', 'crazy-pills' ),
				'success' => __( 'Success', 'crazy-pills' ),
				'error' => __( 'Error', 'crazy-pills' ),
				'alert' => __( 'Alert', 'crazy-pills' ),
				'highlight' => __( 'Highlight', 'crazy-pills' ),
				'highlight_default' => __( 'Highlighted text...', 'crazy-pills' ),
				'yellow' => __( 'Yellow', 'crazy-pills' ),
				'brown' => __( 'Brown', 'crazy-pills' ),
				'black' => __( 'Black', 'crazy-pills' ),
				'blue' => __( 'Blue', 'crazy-pills' ),
				'green' => __( 'Green', 'crazy-pills' ),
				'silver' => __( 'Silver', 'crazy-pills' ),
				'magenta' => __( 'Magenta', 'crazy-pills' ),
				'natural' => __( 'Natural', 'crazy-pills' ),
				'orange' => __( 'Orange', 'crazy-pills' ),
				'purple' => __( 'Purple', 'crazy-pills' ),
				'red' => __( 'Red', 'crazy-pills' ),
				'teal' => __( 'Teal', 'crazy-pills' ),
				'buttons' => __( 'Buttons', 'crazy-pills' ),
				'button_default' => __( 'Button', 'crazy-pills' ),
				'lightblue' => __( 'Light Blue', 'crazy-pills' ),
				'grey' => __( 'Grey', 'crazy-pills' ),
				'gray' => __( 'Gray', 'crazy-pills' ),
				'checks' => __( 'Check list', 'crazy-pills' ),
				'darkblue' => __( 'Dark Blue', 'crazy-pills' ),
				'pink' => __( 'Pink', 'crazy-pills' ),
				'listitem' => __( 'List item', 'crazy-pills' ),
				'bullets' => __( 'Bullets', 'crazy-pills' ),
			)
		) );
	}
}
$crazy_pills = new CrazyPills;