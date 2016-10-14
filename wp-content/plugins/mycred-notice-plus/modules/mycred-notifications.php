<?php
// No direct access please
if ( ! defined( 'MYCRED_NOTICE_VERSION' ) ) exit;

/**
 * myCRED_Notifications class
 * @since 1.0
 * @version 1.2
 */
if ( ! class_exists( 'myCRED_Notifications' ) && class_exists( 'myCRED_Module' ) ) :
	class myCRED_Notifications extends myCRED_Module {

		/**
		 * Construct
		 */
		function __construct() {

			parent::__construct( 'myCRED_Notifications', array(
				'module_name' => 'notifications',
				'defaults'    => array(
					'life'           => 7,
					'template'       => '<p>%entry%</p><h1>%cred_f%</h1>',
					'use_css'        => 1,
					'duration'       => 3000,
					'position'       => 'top-left',
					'padding'        => 50,
					'colors'         => array(
						'bg'             => '#dedede',
						'border'         => '#dedede',
						'text'           => '#333333',
						'negative'       => 0,
						'nbg'            => '#333333',
						'nborder'        => '#333333',
						'ntext'          => '#dedede'
					),
					'border'         => array(
						'width'          => 1,
						'radius'         => 5
					),
					'css'            => '#mycred-notificiation-wrap .notice-item { padding: 12px; line-height: 22px; font-size: 12px; border-style: solid; }
#mycred-notificiation-wrap .notice-item p { display: block; margin: 0; padding: 0; line-height: 16px; }
#mycred-notificiation-wrap .notice-item h1 { margin: 0 !important; padding: 0; }',
					'instant'        => array(
						'use'            => 0,
						'check'          => 15
					),
					'types'          => array( 'mycred_default' ),
					'rank_demotion'  => array(
						'use'            => 0,
						'template'       => '<p>You have been demoted to</p><h4>%rank_title%</h4>%rank_logo%'
					),
					'rank_promotion' => array(
						'use'            => 0,
						'template'       => '<p>You have been promoted to</p><h4>%rank_title%</h4>%rank_logo%'
					),
					'badges'         => array(
						'use'            => 0,
						'template'       => '<p>You have earned a new badge!</p><h4>%badge_title%</h4>%badge_image%'
					)
				),
				'register'    => false,
				'add_to_core' => true
			) );

			add_filter( 'mycred_add_finished', array( $this, 'mycred_add' ), 999, 3 );
			if ( version_compare( myCRED_VERSION, '1.6', '<' ) )
				add_filter( 'mycred_add', array( $this, 'mycred_add' ), 999, 2 );

			add_filter( 'mycred_badge_user_value',  array( $this, 'new_badge' ), 99, 3 );
			add_action( 'mycred_user_got_demoted',  array( $this, 'new_rank_demotion' ), 99, 2 );
			add_action( 'mycred_user_got_promoted', array( $this, 'new_rank_promotion' ), 99, 2 );

			add_action( 'mycred_reset_key',         array( $this, 'cron' ) );

		}

		/**
		 * Delete Old Notices
		 * @since 1.3
		 * @version 1.0
		 */
		public function cron() {

			// No lifespan set
			if ( ! isset( $this->notifications['life'] ) || $this->notifications['life'] == 0 ) return;

			global $wpdb;

			$table = mycred_notice_db_table();

			// Get lifespan in unix timestamp
			$time = strtotime( '-' . $this->notifications['life'] . ' days', current_time( 'timestamp' ) );

			$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE time < %d AND status != 1;", $time ) );

		}

		/**
		 * Module Init
		 * @since 1.0
		 * @version 1.1
		 */
		public function module_init() {

			if ( ! is_user_logged_in() ) return;

			$this->current_user_id = get_current_user_id();

			add_action( 'mycred_front_enqueue',   array( $this, 'register_assets' ) );
			add_action( 'wp_head',                array( $this, 'load_style' ) );
			add_action( 'wp_footer',              array( $this, 'wp_footer' ), 99999 );
			add_action( 'wp_ajax_mycred-inotify', array( $this, 'instant_notification' ) );

			add_action( 'mycred_admin_enqueue',   array( $this, 'admin_enqueue' ) );

		}

		/**
		 * Register Assets
		 * @since 1.0
		 * @version 1.1.1
		 */
		public function register_assets() {

			$freq = $this->notifications['instant']['check'];
			if ( strlen( $this->notifications['instant']['check'] ) < 3 )
				$freq = abs( $this->notifications['instant']['check']*1000 );

			// max speed 5s intervals.
			if ( $freq < 5000 )
				$freq = 5000;

			$duration = $this->notifications['duration'];
			if ( strlen( $this->notifications['duration'] ) < 3 && $this->notifications['duration'] != 0 )
				$duration = abs( $this->notifications['duration']*1000 );

			// Register Styling
			if ( (bool) $this->notifications['use_css'] ) {

				wp_register_style(
					'mycred-notifications',
					plugins_url( 'assets/css/notify.css', myCRED_NOTICE ),
					false,
					MYCRED_NOTICE_CSS_VERSION . '.3',
					'all',
					true
				);

				wp_enqueue_style( 'mycred-notifications' );

			}

			// Register script
			wp_register_script(
				'mycred-notifications',
				plugins_url( 'assets/js/notify.js', myCRED_NOTICE ),
				array( 'jquery' ),
				MYCRED_NOTICE_JS_VERSION . '.1',
				true
			);

			wp_localize_script(
				'mycred-notifications',
				'myCRED_Notice',
				array(
					'ajaxurl'   => admin_url( 'admin-ajax.php' ),
					'duration'  => $duration,
					'frequency' => $freq,
					'token'     => wp_create_nonce( 'mycred-instant-notice' ),
					'user_id'   => $this->current_user_id
				)
			);

			wp_enqueue_script( 'mycred-notifications' );

			// Register instant script
			if ( (bool) $this->notifications['instant']['use'] ) {

				wp_register_script(
					'mycred-instant-notifications',
					plugins_url( 'assets/js/inotify.js', myCRED_NOTICE ),
					array( 'jquery' ),
					MYCRED_NOTICE_JS_VERSION . '.1'
				);

				wp_enqueue_script( 'mycred-instant-notifications' );

			}

		}

		/**
		 * Load Styling
		 * @since 1.0
		 * @version 1.2
		 */
		public function load_style() {

			echo '
<style type="text/css">
#mycred-notificiation-wrap .notice-item {
	background-color: ' . $this->notifications['colors']['bg'] . ' !important;
	color: ' . $this->notifications['colors']['text'] . ' !important;
	border-color: ' . $this->notifications['colors']['border'] . ' !important;
	border-width: ' . $this->notifications['border']['width'] . 'px !important;
	border-radius: ' . $this->notifications['border']['radius'] . 'px !important;';

			if ( in_array( $this->notifications['position'], array( 'top-left', 'top-right' ) ) )
				echo 'margin: 0 0 24px 0 !important;';
			else
				echo 'margin: 24px 0 0 0 !important;';

			echo '
}
#mycred-notificiation-wrap .negative.notice-item {
	background-color: ' . $this->notifications['colors']['nbg'] . ' !important;
	color: ' . $this->notifications['colors']['ntext'] . ' !important;
	border-color: ' . $this->notifications['colors']['nborder'] . ' !important;
}
#mycred-notificiation-wrap {';

			$padding = $this->notifications['padding'];
			if ( $this->notifications['position'] == 'top-left' )
				echo 'top: ' . $padding . 'px !important; left: ' . $padding . 'px !important;';

			elseif ( $this->notifications['position'] == 'top-right' )
				echo 'top: ' . $padding . 'px !important; right: ' . $padding . 'px !important;';

			elseif ( $this->notifications['position'] == 'bottom-left' )
				echo 'bottom: ' . $padding . 'px !important; left: ' . $padding . 'px !important;';

			elseif ( $this->notifications['position'] == 'bottom-right' )
				echo 'bottom: ' . $padding . 'px !important; right: ' . $padding . 'px !important;';

			echo '} ' . $this->notifications['css'] . '
</style>';

		}

		/**
		 * Load Notice in Footer
		 * @since 1.0
		 * @version 1.2.1
		 */
		public function wp_footer() {

			$notices = mycred_get_pending_notices( $this->current_user_id );

			echo '<div id="mycred-notificiation-wrap"></div>';

			if ( empty( $notices ) ) return;

			if ( $this->notifications['duration'] == 0 )
				$stay = 'true';
			else
				$stay = 'false';

			echo '<!-- Notice --><script type="text/javascript">( function( $ ) {';

			do_action_ref_array( 'mycred_before_notifications', array( &$notices ) );
			foreach ( $notices as $notice ) {

				// Parse The actual message
				$entry = $this->core->template_tags_general( $notice->entry );
				$entry = str_replace( array( "\r", "\n", "\t" ), '', $entry );
				$entry = str_replace( '"', "'", $entry );

				$classes = '';
				if ( $notice->data != '' )
					$classes = ' ' . $notice->data;

				echo '$.noticeAdd({ text: "' . $entry . '", stay: ' . $stay . ', type: "' . esc_js( $notice->type ) . '", classes: "notice-item' . esc_js( $classes ) . '"});' . "\n";

			}
			do_action_ref_array( 'mycred_after_notifications', array( &$notices ) );

			echo ' } )( jQuery );</script>';

			mycred_delete_pending_notices( $this->current_user_id );

		}

		/**
		 * myCRED Add
		 * @since 1.0
		 * @version 1.3
		 */
		public function mycred_add( $reply, $request, $mycred ) {

			if ( $reply === false ) return $reply;

			if ( $request['type'] == '' )
				$request['type'] = 'mycred_default';

			if ( isset( $this->notifications['types'] ) && ! in_array( $request['type'], $this->notifications['types'] ) )
				return $reply;

			extract( $request );

			if ( $request['type'] != $mycred->cred_id )
				$mycred = mycred( $type );

			$template = str_replace( '%entry%',  $entry, $this->notifications['template'] );
			$template = str_replace( '%amount%', $amount, $template );

			$template = $mycred->template_tags_amount( $template, $amount );

			$entry          = new StdClass();
			$entry->ref     = $ref;
			$entry->user_id = $user_id;
			$entry->time    = current_time( 'timestamp' );
			$entry->creds   = $amount;
			$entry->entry   = $entry;
			$entry->ref_id  = $ref_id;
			$entry->data    = $data;

			$template = $mycred->parse_template_tags( $template, $entry );

			$template = apply_filters( 'mycred_notifications_note', $template, $request, $mycred );

			$type = 'positive';
			if ( $request['amount'] < 0 )
				$type = 'negative';

			$classes = array();
			$classes[] = str_replace( '_', '-', $request['type'] );
			$classes[] = str_replace( '_', '-', $ref );

			$classes = apply_filters( 'mycred_notifications_classes', $classes, $request, $mycred );

			if ( ! empty( $template ) )
				mycred_add_pending_notice( $user_id, array(
					'entry' => $template,
					'type'  => $type,
					'data'  => implode( ' ', $classes )
				) );

			return $reply;

		}

		/**
		 * myCRED New Badge
		 * @since 1.3
		 * @version 1.0
		 */
		public function new_badge( $level, $user_id, $badge_id ) {

			if ( ! isset( $this->notifications['badges'] ) || $this->notifications['badges']['use'] == 0 )
				return $level;

			$level_image = get_post_meta( $badge_id, 'level_image' . $level, true );
			if ( $level_image == '' )
				$level_image = get_post_meta( $badge_id, 'main_image', true );

			$template = str_replace( '%badge_title%', get_the_title( $badge_id ), $this->notifications['badges']['template'] );

			if ( $level_image != '' )
				$level_image = '<img src="' . $level_image . '" alt="" />';

			$template = str_replace( '%badge_image%', $level_image, $template );
			$template = str_replace( '%badge_level%', $level, $template );

			if ( ! empty( $template ) )
				mycred_add_pending_notice( $user_id, array(
					'entry' => $template,
					'type'  => 'badges'
				) );

			return $level;

		}

		/**
		 * myCRED Rank Demotion
		 * @since 1.3
		 * @version 1.0
		 */
		public function new_rank_demotion( $user_id, $rank_id ) {

			if ( ! isset( $this->notifications['rank_demotion'] ) || $this->notifications['rank_demotion']['use'] == 0 )
				return;

			$template = str_replace( '%rank_title%', get_the_title( $rank_id ), $this->notifications['rank_demotion']['template'] );
			$template = str_replace( '%rank_logo%',  get_the_post_thumbnail( $rank_id, 'full' ), $template );

			if ( ! empty( $template ) )
				mycred_add_pending_notice( $user_id, array(
					'entry' => $template,
					'type'  => 'rank_demotion'
				) );

		}

		/**
		 * myCRED Rank Promotion
		 * @since 1.3
		 * @version 1.0
		 */
		public function new_rank_promotion( $user_id, $rank_id ) {

			if ( ! isset( $this->notifications['rank_promotion'] ) || $this->notifications['rank_promotion']['use'] == 0 )
				return;

			$template = str_replace( '%rank_title%', get_the_title( $rank_id ), $this->notifications['rank_promotion']['template'] );
			$template = str_replace( '%rank_logo%',  get_the_post_thumbnail( $rank_id, 'full' ), $template );

			if ( ! empty( $template ) )
				mycred_add_pending_notice( $user_id, array(
					'entry' => $template,
					'type'  => 'rank_promotion'
				) );

		}

		/**
		 * AJAX: Instant Notification Check
		 * @since 1.1
		 * @version 1.3
		 */
		public function instant_notification() {

			// Security
			check_ajax_referer( 'mycred-instant-notice', 'token' );
	
			// Get notices
			$notices = mycred_get_pending_notices( $this->current_user_id );
			if ( empty( $notices ) ) die( 0 );

			if ( $this->notifications['duration'] == 0 )
				$stay = 'true';
			else
				$stay = 'false';

			$show = array();

			do_action_ref_array( 'mycred_before_notifications', array( &$notices ) );
			foreach ( $notices as $notice ) {

				// Parse The actual message
				$entry = $this->core->template_tags_general( $notice->entry );
				$entry = str_replace( array( "\r", "\n", "\t" ), '', $entry );
				$entry = str_replace( '"', "'", $entry );

				$show[] = array(
					'text' => $entry,
					'stay' => $stay,
					'type' => $notice->type
				);

			}
			do_action_ref_array( 'mycred_after_notifications', array( &$notices ) );

			mycred_delete_pending_notices( $this->current_user_id );

			wp_send_json( $show );

		}

		/**
		 * Add Color Picker
		 * @since 1.0
		 * @version 1.1
		 */
		public function admin_enqueue() {

			$screen = get_current_screen();
			if ( substr( $screen->id, -26 ) != '_page_myCRED_page_settings' ) return;

			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );

		}

		/**
		 * Settings Page
		 * @since 1.0
		 * @version 1.2
		 */
		public function after_general_settings( $mycred = NULL ) {

			global $mycred_types;

			$settings = $this->notifications;
			if ( ! isset( $settings['types'] ) )
				$settings['types'] = array( 'mycred_default' );

			if ( strlen( $settings['duration'] ) > 2 )
				$settings['duration'] = substr( $settings['duration'], 0, 1 );

			$settings = wp_parse_args( $settings, $this->default_prefs );

?>
<h4><div class="icon icon-active"></div><?php _e( 'Notifications Plus', 'mycred_notice' ); ?></h4>
<div class="body" style="display:none;">
	<label class="subheader"><?php _e( 'Styling', 'mycred_notice' ); ?></label>
	<ol>
		<li>
			<label for="<?php echo $this->field_id( 'use_css' ); ?>"><input type="checkbox" name="<?php echo $this->field_name( 'use_css' ); ?>" id="<?php echo $this->field_id( 'use_css' ); ?>" <?php checked( $settings['use_css'], 1 ); ?> value="1" /> <?php _e( 'Use the included CSS Styling for notifications.', 'mycred_notice' ); ?></label>
		</li>
	</ol>

	<?php if ( count( $mycred_types ) > 1 ) : ?>

	<label class="subheader"><?php _e( 'Point Types', 'mycred_notice' ); ?></label>
	<ol>
		<li>

			<?php foreach ( $mycred_types as $type => $label ) { if ( $type == 'mycred_default' ) $label = $this->core->plural(); ?>

			<label for="<?php echo $this->field_id( array( 'types', $type ) ); ?>"><input type="checkbox" name="<?php echo $this->field_name( array( 'types', $type ) ); ?>" id="<?php echo $this->field_id( array( 'types', $type ) ); ?>" value="<?php echo $type; ?>"<?php if ( in_array( $type, $settings['types'] ) ) echo ' checked="checked"'; ?> /> <?php echo $label; ?></label><br />

			<?php } ?>

		</li>
	</ol>

	<?php else : ?>

	<input type="hidden" name="<?php echo $this->field_name( 'types' ); ?>[]" value="mycred_default" />

	<?php endif; ?>

	<label class="subheader" for="<?php echo $this->field_id( array( 'position', 'top-left' ) ); ?>"><?php _e( 'Position', 'mycred_notice' ); ?></label>
	<ol class="inline">
		<li>
			<img src="<?php echo plugins_url( 'assets/images/top-left.png', myCRED_NOTICE ); ?>" alt="" /><br />
			<input type="radio" name="<?php echo $this->field_name( 'position' ); ?>" id="<?php echo $this->field_id( array( 'position', 'top-left' ) ); ?>" <?php checked( $settings['position'], 'top-left' ); ?> value="top-left" />
			<label for="<?php echo $this->field_id( array( 'position', 'top-left' ) ); ?>"><?php _e( 'Top Left', 'mycred_notice' ); ?></label>
		</li>
		<li>
			<img src="<?php echo plugins_url( 'assets/images/top-right.png', myCRED_NOTICE ); ?>" alt="" /><br />
			<input type="radio" name="<?php echo $this->field_name( 'position' ); ?>" id="<?php echo $this->field_id( array( 'position', 'top-right' ) ); ?>" <?php checked( $settings['position'], 'top-right' ); ?> value="top-right" />
			<label for="<?php echo $this->field_id( array( 'position', 'top-right' ) ); ?>"><?php _e( 'Top Right', 'mycred_notice' ); ?></label>
		</li>
		<li>
			<img src="<?php echo plugins_url( 'assets/images/bottom-left.png', myCRED_NOTICE ); ?>" alt="" /><br />
			<input type="radio" name="<?php echo $this->field_name( 'position' ); ?>" id="<?php echo $this->field_id( array( 'position', 'bottom-left' ) ); ?>" <?php checked( $settings['position'], 'bottom-left' ); ?> value="bottom-left" />
			<label for="<?php echo $this->field_id( array( 'position', 'bottom-left' ) ); ?>"><?php _e( 'Bottom Left', 'mycred_notice' ); ?></label>
		</li>
		<li>
			<img src="<?php echo plugins_url( 'assets/images/bottom-right.png', myCRED_NOTICE ); ?>" alt="" /><br />
			<input type="radio" name="<?php echo $this->field_name( 'position' ); ?>" id="<?php echo $this->field_id( array( 'position', 'bottom-right' ) ); ?>" <?php checked( $settings['position'], 'bottom-right' ); ?> value="bottom-right" />
			<label for="<?php echo $this->field_id( array( 'position', 'bottom-right' ) ); ?>"><?php _e( 'Bottom Right', 'mycred_notice' ); ?></label>
		</li>
	</ol>
	<label class="subheader" for="<?php echo $this->field_id( 'padding' ); ?>"><?php _e( 'Padding', 'mycred_notice' ); ?></label>
	<ol>
		<li>
			<div class="h2"><input type="text" name="<?php echo $this->field_name( 'padding' ); ?>" id="<?php echo $this->field_id( 'padding' ); ?>" value="<?php echo absint( $settings['padding'] ); ?>" class="short" /> px</div>
			<span class="description"><?php _e( 'Number of pixels between the edge of the screen and the notification box.<br />Applied both horizontally and vertically depending on the set position.', 'mycred_notice' ); ?></span>
		</li>
	</ol>
	<label class="subheader"><?php _e( 'Border', 'mycred_notice' ); ?></label>
	<ol class="inline">
		<li>
			<label for="<?php echo $this->field_id( array( 'border', 'width' ) ); ?>"><?php _e( 'Width', 'mycred_notice' ); ?></label>
			<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'border', 'width' ) ); ?>" id="<?php echo $this->field_id( array( 'border', 'width' ) ); ?>" value="<?php echo absint( $settings['border']['width'] ); ?>" class="short" /> px</div>
		</li>
		<li>
			<label for="<?php echo $this->field_id( array( 'border', 'radius' ) ); ?>"><?php _e( 'Radius', 'mycred_notice' ); ?></label>
			<div class="h2"><input type="text" name="<?php echo $this->field_name( array( 'border', 'radius' ) ); ?>" id="<?php echo $this->field_id( array( 'border', 'radius' ) ); ?>" value="<?php echo absint( $settings['border']['radius'] ); ?>" class="short" /> px</div>
		</li>
	</ol>
	<label class="subheader"><?php _e( 'Colors', 'mycred_notice' ); ?></label>
	<ol class="inline">
		<li>
			<label for="<?php echo $this->field_id( array( 'colors', 'bg' ) ); ?>"><?php _e( 'Background Color', 'mycred_notice' ); ?></label><br />
			<input type="text" name="<?php echo $this->field_name( array( 'colors', 'bg' ) ); ?>" id="<?php echo $this->field_id( array( 'colors', 'bg' ) ); ?>" value="<?php echo esc_attr( $settings['colors']['bg'] ); ?>" class="wp-color-picker-field" data-default-color="#dedede" />
		</li>
		<li>
			<label for="<?php echo $this->field_id( array( 'colors', 'border' ) ); ?>"><?php _e( 'Border Color', 'mycred_notice' ); ?></label><br />
			<input type="text" name="<?php echo $this->field_name( array( 'colors', 'border' ) ); ?>" id="<?php echo $this->field_id( array( 'colors', 'border' ) ); ?>" value="<?php echo esc_attr( $settings['colors']['border'] ); ?>" class="wp-color-picker-field" data-default-color="#dedede" />
		</li>
		<li>
			<label for="<?php echo $this->field_id( array( 'colors', 'text' ) ); ?>"><?php _e( 'Text Color', 'mycred_notice' ); ?></label><br />
			<input type="text" name="<?php echo $this->field_name( array( 'colors', 'text' ) ); ?>" id="<?php echo $this->field_id( array( 'colors', 'text' ) ); ?>" value="<?php echo esc_attr( $settings['colors']['text'] ); ?>" class="wp-color-picker-field" data-default-color="#333333" />
		</li>
	</ol>
	<label class="subheader">&nbsp;</label>
	<ol>
		<li>
			<label for="<?php echo $this->field_id( array( 'colors', 'negative' ) ); ?>"><input type="checkbox" name="<?php echo $this->field_name( array( 'colors', 'negative' ) ); ?>" id="<?php echo $this->field_id( array( 'colors', 'negative' ) ); ?>" <?php checked( $settings['colors']['negative'], 1 ); ?> value="1" /> <?php echo $this->core->template_tags_general( __( 'Use different colors when users loose %plural%.', 'mycred_notice' ) ); ?></label>
		</li>
	</ol>
	<div id="negative-colors" style="display:<?php if ( (bool) $settings['colors']['negative'] ) echo 'block'; else echo 'none'; ?>;">
		<label class="subheader"><?php _e( 'Negative Colors', 'mycred_notice' ); ?></label>
		<ol class="inline">
			<li>
				<label for="<?php echo $this->field_id( array( 'colors', 'nbg' ) ); ?>"><?php _e( 'Background Color', 'mycred_notice' ); ?></label><br />
				<input type="text" name="<?php echo $this->field_name( array( 'colors', 'nbg' ) ); ?>" id="<?php echo $this->field_id( array( 'colors', 'nbg' ) ); ?>" value="<?php echo esc_attr( $settings['colors']['nbg'] ); ?>" class="wp-color-picker-field" data-default-color="#333333" />
			</li>
			<li>
				<label for="<?php echo $this->field_id( array( 'colors', 'nborder' ) ); ?>"><?php _e( 'Border Color', 'mycred_notice' ); ?></label><br />
				<input type="text" name="<?php echo $this->field_name( array( 'colors', 'nborder' ) ); ?>" id="<?php echo $this->field_id( array( 'colors', 'nborder' ) ); ?>" value="<?php echo esc_attr( $settings['colors']['nborder'] ); ?>" class="wp-color-picker-field" data-default-color="#333333" />
			</li>
			<li>
				<label for="<?php echo $this->field_id( array( 'colors', 'ntext' ) ); ?>"><?php _e( 'Text Color', 'mycred_notice' ); ?></label><br />
				<input type="text" name="<?php echo $this->field_name( array( 'colors', 'ntext' ) ); ?>" id="<?php echo $this->field_id( array( 'colors', 'ntext' ) ); ?>" value="<?php echo esc_attr( $settings['colors']['ntext'] ); ?>" class="wp-color-picker-field" data-default-color="#dedede" />
			</li>
		</ol>
	</div>
	<label class="subheader" for="<?php echo $this->field_id( 'css' ); ?>"><?php _e( 'Custom CSS', 'mycred_notice' ); ?></label>
	<ol>
		<li>
			<textarea name="<?php echo $this->field_name( 'css' ); ?>" id="<?php echo $this->field_id( 'css' ); ?>" rows="5" cols="50" class="large-text code"><?php echo esc_attr( $settings['css'] ); ?></textarea>
			<a href="javascript:void(0);" id="retore-default-css"><?php _e( 'Restore to default', 'mycred_notice' ); ?></a>
		</li>
	</ol>
	<label class="subheader" for="<?php echo $this->field_id( 'template' ); ?>"><?php _e( 'Template', 'mycred_notice' ); ?></label>
	<ol>
		<li>
			<textarea name="<?php echo $this->field_name( 'template' ); ?>" id="<?php echo $this->field_id( 'template' ); ?>" rows="5" cols="50" class="large-text code"><?php echo esc_attr( $settings['template'] ); ?></textarea>
			<span class="description"><?php _e( 'Use %entry% to show the log entry in the notice and %amount% for the amount.', 'mycred_notice' ); ?></span> <a href="javascript:void(0);" id="retore-default-notice"><?php _e( 'Restore to default', 'mycred_notice' ); ?></a>
		</li>
	</ol>
	<label class="subheader" for="<?php echo $this->field_id( 'life' ); ?>"><?php _e( 'Lifespan', 'mycred_notice' ); ?></label>
	<ol>
		<li>
			<div class="h2"><input type="text" name="<?php echo $this->field_name( 'life' ); ?>" id="<?php echo $this->field_id( 'life' ); ?>" value="<?php echo absint( $settings['life'] ); ?>" class="short" /></div>
			<span class="description"><?php _e( 'The number of days a users notification is saved before being automatically deleted.', 'mycred_notice' ); ?></span>
		</li>
	</ol>
	<label class="subheader" for="<?php echo $this->field_id( 'duration' ); ?>"><?php _e( 'Duration', 'mycred_notice' ); ?></label>
	<ol>
		<li>
			<div class="h2"><input type="number" name="<?php echo $this->field_name( 'duration' ); ?>" id="<?php echo $this->field_id( 'duration' ); ?>" value="<?php echo absint( $settings['duration'] ); ?>" class="short" min="0" max="60" /></div>
			<span class="description"><?php _e( 'Number of seconds before a notice is automatically removed after being shown to user. Use zero to disable.', 'mycred_notice' ); ?></span>
		</li>
	</ol>
	<label class="subheader"><?php _e( 'Instant Notifications', 'mycred_notice' ); ?></label>
	<ol>
		<li>
			<label for="<?php echo $this->field_id( array( 'instant', 'use' ) ); ?>"><input type="checkbox" name="<?php echo $this->field_name( array( 'instant', 'use' ) ); ?>" id="<?php echo $this->field_id( array( 'instant', 'use' ) ); ?>" <?php checked( $settings['instant']['use'], 1 ); ?> value="1" /> <?php _e( 'Enable Instant Notifications', 'mycred_notice' ); ?></label>
		</li>
	</ol>
	<div id="instant-notifications" style="display:<?php if ( (bool) $settings['instant']['use'] ) echo 'block'; else echo 'none'; ?>;">
		<label class="subheader" for="<?php echo $this->field_id( array( 'instant', 'check' ) ); ?>"><?php _e( 'Update Frequency', 'mycred_notice' ); ?></label>
		<ol>
			<li>
				<div class="h2"><input type="number" name="<?php echo $this->field_name( array( 'instant', 'check' ) ); ?>" id="<?php echo $this->field_id( array( 'instant', 'check' ) ); ?>" value="<?php echo absint( $settings['instant']['check'] ); ?>" class="short" min="5" /> <?php _e( 'seconds' ); ?></div>
				<span class="description"><?php _e( 'How often should this add-on check for new notifications. Can not be lower then 5 seconds.', 'mycred_notice' ); ?></span>
			</li>
		</ol>
	</div>

	<?php if ( function_exists( 'mycred_user_got_demoted' ) ) : ?>

	<label class="subheader" for="<?php echo $this->field_id( array( 'rank_demotion', 'use' ) ); ?>"><?php _e( 'Rank Demotion', 'mycred_notice' ); ?></label>
	<ol>
		<li>
			<label for="<?php echo $this->field_id( array( 'rank_demotion', 'use' ) ); ?>"><input type="checkbox" name="<?php echo $this->field_name( array( 'rank_demotion', 'use' ) ); ?>" id="<?php echo $this->field_id( array( 'rank_demotion', 'use' ) ); ?>" <?php checked( $settings['rank_demotion']['use'], 1 ); ?> value="1" /> <?php _e( 'Use custom notifications for rank demotions.', 'mycred_notice' ); ?></label>
		</li>
		<li class="empty">&nbsp;</li>
		<li>
			<textarea name="<?php echo $this->field_name( array( 'rank_demotion', 'template' ) ); ?>" id="<?php echo $this->field_id( array( 'rank_demotion', 'template' ) ); ?>" rows="5" cols="50" class="large-text code"><?php echo esc_attr( $settings['rank_demotion']['template'] ); ?></textarea>
			<span class="description"><?php printf( __( 'Available template tags: %s - rank title, %s - rank logo.', 'mycred_notice' ), '<code>%rank_title%</code>', '<code>%rank_logo%</code>' ); ?></span> <a href="javascript:void(0);" id="retore-default-demotion"><?php _e( 'Restore to default', 'mycred_notice' ); ?></a>
		</li>
	</ol>
	<label class="subheader" for="<?php echo $this->field_id( array( 'rank_promotion', 'use' ) ); ?>"><?php _e( 'Rank Promotion', 'mycred_notice' ); ?></label>
	<ol>
		<li>
			<label for="<?php echo $this->field_id( array( 'rank_promotion', 'use' ) ); ?>"><input type="checkbox" name="<?php echo $this->field_name( array( 'rank_promotion', 'use' ) ); ?>" id="<?php echo $this->field_id( array( 'rank_promotion', 'use' ) ); ?>" <?php checked( $settings['rank_promotion']['use'], 1 ); ?> value="1" /> <?php _e( 'Use custom notifications for rank promotions.', 'mycred_notice' ); ?></label>
		</li>
		<li class="empty">&nbsp;</li>
		<li>
			<textarea name="<?php echo $this->field_name( array( 'rank_promotion', 'template' ) ); ?>" id="<?php echo $this->field_id( array( 'rank_promotion', 'template' ) ); ?>" rows="5" cols="50" class="large-text code"><?php echo esc_attr( $settings['rank_promotion']['template'] ); ?></textarea>
			<span class="description"><?php printf( __( 'Available template tags: %s - rank title, %s - rank logo.', 'mycred_notice' ), '<code>%rank_title%</code>', '<code>%rank_logo%</code>' ); ?></span> <a href="javascript:void(0);" id="retore-default-promotion"><?php _e( 'Restore to default', 'mycred_notice' ); ?></a>
		</li>
	</ol>

	<?php endif; ?>

	<?php if ( function_exists( 'mycred_check_if_user_gets_badge' ) ) : ?>

	<label class="subheader" for="<?php echo $this->field_id( array( 'badges', 'use' ) ); ?>"><?php _e( 'Badges', 'mycred_notice' ); ?></label>
	<ol>
		<li>
			<label for="<?php echo $this->field_id( array( 'badges', 'use' ) ); ?>"><input type="checkbox" name="<?php echo $this->field_name( array( 'badges', 'use' ) ); ?>" id="<?php echo $this->field_id( array( 'badges', 'use' ) ); ?>" <?php checked( $settings['badges']['use'], 1 ); ?> value="1" /> <?php _e( 'Use custom notifications when a user earns a badge.', 'mycred_notice' ); ?></label>
		</li>
		<li class="empty">&nbsp;</li>
		<li>
			<textarea name="<?php echo $this->field_name( array( 'badges', 'template' ) ); ?>" id="<?php echo $this->field_id( array( 'badges', 'template' ) ); ?>" rows="5" cols="50" class="large-text code"><?php echo esc_attr( $settings['badges']['template'] ); ?></textarea>
			<span class="description"><?php printf( __( 'Available template tags: %s - badge title, %s - badge logo, %s - badge level.', 'mycred_notice' ), '<code>%badge_title%</code>', '<code>%badge_image%</code>', '<code>%badge_level%</code>' ); ?></span> <a href="javascript:void(0);" id="retore-default-badge"><?php _e( 'Restore to default', 'mycred_notice' ); ?></a>
		</li>
	</ol>

	<?php endif; ?>

</div>
<script type="text/javascript">
jQuery(document).ready(function($){

	// Load wp color picker
	$( '.wp-color-picker-field' ).wpColorPicker();

	$( '#retore-default-css' ).click(function(){
		$( '#<?php echo $this->field_id( 'css' ); ?>' ).val( '<?php echo esc_js( $this->default_prefs['css'] ); ?>' );
	});

	$( '#retore-default-notice' ).click(function(){
		$( '#<?php echo $this->field_id( 'template' ); ?>' ).val( '<?php echo $this->default_prefs['template']; ?>' );
	});

	$( '#retore-default-demotion' ).click(function(){
		$( '#<?php echo $this->field_id( array( 'rank_demotion', 'template' ) ); ?>' ).val( '<?php echo $this->default_prefs['rank_demotion']['template']; ?>' );
	});

	$( '#retore-default-promotion' ).click(function(){
		$( '#<?php echo $this->field_id( array( 'rank_promotion', 'template' ) ); ?>' ).val( '<?php echo $this->default_prefs['rank_promotion']['template']; ?>' );
	});

	$( '#retore-default-badge' ).click(function(){
		$( '#<?php echo $this->field_id( array( 'badges', 'template' ) ); ?>' ).val( '<?php echo $this->default_prefs['badges']['template']; ?>' );
	});

	// Toggle Instant Notifications
	$( '#<?php echo $this->field_id( array( 'instant', 'use' ) ); ?>' ).click(function(){

		if ( $(this).is(':checked') ) {
			$( 'div#instant-notifications' ).show();
		}
		else {
			$( 'div#instant-notifications' ).hide();
		}

	});

	// Toggle Negative Colors
	$( '#<?php echo $this->field_id( array( 'colors', 'negative' ) ); ?>' ).click(function(){

		if ( $(this).is(':checked') ) {
			$( 'div#negative-colors' ).show();
		}
		else {
			$( 'div#negative-colors' ).hide();
		}

	});

});
</script>
<?php

		}

		/**
		 * Sanitize & Save Settings
		 * @since 1.0
		 * @version 1.1
		 */
		public function sanitize_extra_settings( $new_data, $data, $general ) {

			$allowed_tags                                     = $this->core->allowed_html_tags();
			$new_data['notifications']['use_css']             = ( isset( $data['notifications']['use_css'] ) ) ? 1: 0;

			$new_data['notifications']['position']            = ( ( isset( $data['notifications']['position'] ) ) ? $data['notifications']['position'] : 'top-right' );
			$new_data['notifications']['types']               = ( ( isset( $data['notifications']['types'] ) ) ? $data['notifications']['types'] : array( 'mycred_default' ) );

			$new_data['notifications']['padding']             = absint( $data['notifications']['padding'] );

			$new_data['notifications']['border']['width']     = sanitize_text_field( $data['notifications']['border']['width'] );
			$new_data['notifications']['border']['radius']    = sanitize_text_field( $data['notifications']['border']['radius'] );

			$new_data['notifications']['colors']['bg']        = sanitize_text_field( $data['notifications']['colors']['bg'] );
			$new_data['notifications']['colors']['border']    = sanitize_text_field( $data['notifications']['colors']['border'] );
			$new_data['notifications']['colors']['text']      = sanitize_text_field( $data['notifications']['colors']['text'] );

			$new_data['notifications']['colors']['negative']  = ( isset( $data['notifications']['colors']['negative'] ) ) ? 1 : 0;
			$new_data['notifications']['colors']['nbg']       = sanitize_text_field( $data['notifications']['colors']['nbg'] );
			$new_data['notifications']['colors']['nborder']   = sanitize_text_field( $data['notifications']['colors']['nborder'] );
			$new_data['notifications']['colors']['ntext']     = sanitize_text_field( $data['notifications']['colors']['ntext'] );

			$new_data['notifications']['css']                 = wp_kses_post( $data['notifications']['css'] );

			$new_data['notifications']['template']            = wp_kses( $data['notifications']['template'], $allowed_tags );
			$new_data['notifications']['life']                = absint( $data['notifications']['life'] );
			$new_data['notifications']['duration']            = absint( $data['notifications']['duration'] );

			$new_data['notifications']['instant']['use']      = ( isset( $data['notifications']['instant']['use'] ) ) ? 1 : 0;
			$new_data['notifications']['instant']['check']    = absint( $data['notifications']['instant']['check'] );

			if ( $new_data['notifications']['instant']['use'] === 1 && $new_data['notifications']['instant']['check'] < 5 )
				$new_data['notifications']['instant']['check'] = 5;

			if ( function_exists( 'mycred_user_got_demoted' ) ) {

				$new_data['notifications']['rank_demotion']['use']       = ( isset( $data['notifications']['rank_demotion']['use'] ) ) ? 1 : 0;
				$new_data['notifications']['rank_demotion']['template']  = wp_kses( $data['notifications']['rank_demotion']['template'], $allowed_tags );

				$new_data['notifications']['rank_promotion']['use']      = ( isset( $data['notifications']['rank_promotion']['use'] ) ) ? 1 : 0;
				$new_data['notifications']['rank_promotion']['template'] = wp_kses( $data['notifications']['rank_promotion']['template'], $allowed_tags );

			}
			else {

				$new_data['notifications']['rank_demotion']['use']       = 0;
				$new_data['notifications']['rank_demotion']['template']  = $this->default_prefs['rank_demotion']['template'];

				$new_data['notifications']['rank_promotion']['use']      = 0;
				$new_data['notifications']['rank_promotion']['template'] = $this->default_prefs['rank_promotion']['template'];

			}

			if ( function_exists( 'mycred_check_if_user_gets_badge' ) ) {

				$new_data['notifications']['badges']['use']      = ( isset( $data['notifications']['badges']['use'] ) ) ? 1 : 0;
				$new_data['notifications']['badges']['template'] = wp_kses( $data['notifications']['badges']['template'], $allowed_tags );

			}
			else {

				$new_data['notifications']['badges']['use']      = 0;
				$new_data['notifications']['badges']['template'] = $this->default_prefs['badges']['template'];

			}

			return $new_data;

		}

	}
endif;

?>