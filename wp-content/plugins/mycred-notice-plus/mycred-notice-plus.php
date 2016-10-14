<?php
/**
 * Plugin Name: myCRED Notifications Plus
 * Description: Notifications with options to style, show notifications for ranks and badges and optional instant notifications.
 * Version: 1.3.1
 * Author: Gabriel S Merovingi
 * Author URI: http://www.merovingi.com
 * Requires at least: WP 3.8
 * Tested up to: WP 4.4.1
 * Text Domain: mycred_notice
 * Domain Path: /lang
 * License: Copyrighted
 *
 * Copyright © 2013 - 2016 Gabriel S Merovingi
 * 
 * Permission is hereby granted, to the licensed domain to install and run this
 * software and associated documentation files (the "Software") for an unlimited
 * time with the followning restrictions:
 *
 * - This software is only used under the domain name registered with the purchased
 *   license though the myCRED website (mycred.me). Exception is given for localhost
 *   installations or test enviroments.
 *
 * - This software can not be copied and installed on a website not licensed.
 *
 * - This software is supported only if no changes are made to the software files
 *   or documentation. All support is voided as soon as any changes are made.
 *
 * - This software is not copied and re-sold under the current brand or any other
 *   branding in any medium or format.
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
define( 'MYCRED_NOTICE_VERSION',      '1.3.1' );
define( 'MYCRED_NOTICE_JS_VERSION',   MYCRED_NOTICE_VERSION . '.1' );
define( 'MYCRED_NOTICE_CSS_VERSION',  MYCRED_NOTICE_VERSION . '.1' );

define( 'MYCRED_NOTICE_SLUG',        'mycred-notice-plus' );
define( 'myCRED_NOTICE',              __FILE__ );
define( 'MYCRED_NOTICE_ROOT_DIR',     plugin_dir_path( myCRED_NOTICE ) );
define( 'MYCRED_NOTICE_ASSETS_DIR',   MYCRED_NOTICE_ROOT_DIR . 'assets/' );
define( 'MYCRED_NOTICE_INCLUDES_DIR', MYCRED_NOTICE_ROOT_DIR . 'includes/' );
define( 'MYCRED_NOTICE_MODULES_DIR',  MYCRED_NOTICE_ROOT_DIR . 'modules/' );

/**
 * myCRED_Notice_Plus_Plugin class
 * @since 1.0
 * @version 1.2
 */
if ( ! class_exists( 'myCRED_Notice_Plus_Plugin' ) ) :
	class myCRED_Notice_Plus_Plugin {

		/**
		 * Construct
		 */
		function __construct() {

			require_once MYCRED_NOTICE_INCLUDES_DIR . 'mycred-notice-functions.php';

			add_action( 'mycred_pre_init',                       array( $this, 'mycred_pre_init' ) );

			register_activation_hook( myCRED_NOTICE,             array( $this, 'activate_mycred_notifications' ) );
			register_uninstall_hook(  myCRED_NOTICE,             'mycred_note_plus_plugin_uninstall' );

			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_plugin_update' ), 210 );
			add_filter( 'plugins_api',                           array( $this, 'plugin_api_call' ), 210, 3 );
			add_filter( 'plugin_row_meta',                       array( $this, 'plugin_view_info' ), 10, 3 );

		}

		/**
		 * Load Translation
		 * @since 1.0
		 * @version 1.1.2
		 */
		function mycred_pre_init() {

			$locale = apply_filters( 'plugin_locale', get_locale(), 'mycred_notice' );
			load_textdomain( 'mycred_notice', WP_LANG_DIR . "/mycred-notice-plus/mycred-notice-plus-$locale.mo" );
			load_plugin_textdomain( 'mycred_notice', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

			require_once MYCRED_NOTICE_MODULES_DIR . 'mycred-notifications.php';

			$notice = new myCRED_Notifications();
			$notice->load();

		}

		/**
		 * Activate
		 * @since 1.0
		 * @version 1.0.2
		 */
		function activate_mycred_notifications() {

			global $wpdb;

			$message = array();

			// WordPress check
			$wp_version = $GLOBALS['wp_version'];
			if ( version_compare( $wp_version, '3.8', '<' ) )
				$message[] = __( 'This myCRED Add-on requires WordPress 3.8 or higher. Version detected:', 'mycred_notice' ) . ' ' . $wp_version;

			// PHP check
			$php_version = phpversion();
			if ( version_compare( $php_version, '5.3', '<' ) )
				$message[] = __( 'This myCRED Add-on requires PHP 5.3 or higher. Version detected: ', 'mycred_notice' ) . ' ' . $php_version;

			// SQL check
			$sql_version = $wpdb->db_version();
			if ( version_compare( $sql_version, '5.0', '<' ) )
				$message[] = __( 'This myCRED Add-on requires SQL 5.0 or higher. Version detected: ', 'mycred_notice' ) . ' ' . $sql_version;

			// myCRED Check
			if ( defined( 'myCRED_VERSION' ) && version_compare( myCRED_VERSION, '1.4', '<' ) )
				$message[] = __( 'This add-on requires myCRED 1.4 or higher. Version detected:', 'mycred_notice' ) . ' ' . myCRED_VERSION;

			// Not empty $message means there are issues
			if ( ! empty( $message ) ) {

				$error_message = implode( "\n", $message );
				die( __( 'Sorry but your WordPress installation does not reach the minimum requirements for running this add-on. The following errors were given:', 'mycred_notice' ) . "\n" . $error_message );

			}

			mycred_notice_plus_install_db();

		}
		
		/**
		 * Plugin API Update Check
		 * Call home to see if there is a new version of this plugin.
		 * If the license is not set up, this will never receive anything.
		 * @since 1.0
		 * @version 1.1
		 */
		function check_for_plugin_update( $checked_data ) {

			global $wp_version;

			if ( empty( $checked_data->checked ) )
				return $checked_data;

			$args = array(
				'slug'    => MYCRED_NOTICE_SLUG,
				'version' => MYCRED_NOTICE_VERSION,
				'site'    => site_url()
			);
			$request_string = array(
				'body'       => array(
					'action'     => 'version', 
					'request'    => serialize( $args ),
					'api-key'    => md5( get_bloginfo( 'url' ) )
				),
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' )
			);

			// Start checking for an update
			$response = wp_remote_post( 'http://mycred.me/api/plugins/', $request_string );

			if ( ! is_wp_error( $response ) ) {

				$result = maybe_unserialize( $response['body'] );

				if ( is_object( $result ) && ! empty( $result ) )
					$checked_data->response[ MYCRED_NOTICE_SLUG . '/' . MYCRED_NOTICE_SLUG . '.php' ] = $result;

			}

			return $checked_data;

		}

		/**
		 * Plugin View Info
		 * @since 1.3
		 * @version 1.0.1
		 */
		function plugin_view_info( $plugin_meta, $file, $plugin_data ) {

			if ( $file != plugin_basename( myCRED_NOTICE ) ) return $plugin_meta;

			$plugin_meta[] = sprintf( '<a href="%s" class="thickbox" aria-label="%s" data-title="%s">%s</a>',
				esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . MYCRED_NOTICE_SLUG .
				'&TB_iframe=true&width=600&height=550' ) ),
				esc_attr( __( 'More information about this plugin', 'mycred_notice' ) ),
				esc_attr( 'myCRED Notifications Plus' ),
				__( 'View details', 'mycred_notice' )
			);

			$url     = str_replace( array( 'https://', 'http://' ), '', get_bloginfo( 'url' ) );
			$expires = get_option( 'mycred-premium-' . MYCRED_NOTICE_SLUG . '-expires', '' );
			if ( $expires != '' ) {

				if ( $expires == 'never' )
					$plugin_meta[] = 'Unlimited License';

				elseif ( absint( $expires ) > 0 ) {

					$days = ceil( ( $expires - current_time( 'timestamp' ) ) / DAY_IN_SECONDS );
					if ( $days > 0 )
						$plugin_meta[] = sprintf(
							'License Expires in <strong%s>%s</strong>',
							( ( $days < 30 ) ? ' style="color:red;"' : '' ),
							sprintf( _n( '1 day', '%d days', $days ), $days )
						);

					$renew = get_option( 'mycred-premium-' . MYCRED_NOTICE_SLUG . '-renew', '' );
					if ( $days < 30 && $renew != '' )
						$plugin_meta[] = '<a href="' . esc_url( $renew ) . '" target="_blank" class="delete">Renew License</a>';

				}

			}

			else $plugin_meta[] = '<a href="http://mycred.me/about/terms/#product-licenses" target="_blank">No license found for - ' . $url . '</a>';

			return $plugin_meta;

		}

		/**
		 * Plugin API Information
		 * @since 1.0
		 * @version 1.1
		 */
		function plugin_api_call( $result, $action, $args ) {

			global $wp_version;

			if ( ! isset( $args->slug ) || ( $args->slug != MYCRED_NOTICE_SLUG ) )
				return $result;

			// Get the current version
			$args = array(
				'slug'    => MYCRED_NOTICE_SLUG,
				'version' => MYCRED_NOTICE_VERSION,
				'site'    => site_url()
			);
			$request_string = array(
				'body'       => array(
					'action'     => 'info', 
					'request'    => serialize( $args ),
					'api-key'    => md5( get_bloginfo( 'url' ) )
				),
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' )
			);

			$request = wp_remote_post( 'http://mycred.me/api/plugins/', $request_string );

			if ( ! is_wp_error( $request ) )
				$result = maybe_unserialize( $request['body'] );

			if ( $result->license_expires != '' )
				update_option( 'mycred-premium-' . MYCRED_NOTICE_SLUG . '-expires', $result->license_expires );

			if ( $result->license_renew != '' )
				update_option( 'mycred-premium-' . MYCRED_NOTICE_SLUG . '-renew',   $result->license_renew );

			return $result;

		}

	}
endif;

$mycred_notice_plus = new myCRED_Notice_Plus_Plugin();

?>