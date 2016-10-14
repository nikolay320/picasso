<?php
if ( class_exists( 'myCRED_Hook' ) ) :
	class kleo_child_eventon_mycred_class extends myCRED_Hook {

		/**
		 * Construct
		 */
		function __construct( $hook_prefs, $type = 'mycred_default' ) {
			parent::__construct( array(
				'id'       => 'kleo_child_eventon_adding_event',
				'defaults' => array(
					'kleo_child_eventon_add'   => array(
						'creds'   => 1,
						'log'     => '%plural% for adding approved event'
					)
				)
			), $hook_prefs, $type );
		}

		/**
		 * Hook into WordPress
		 */
		public function run() {
			// Since we are running a single instance, we do not need to check
			// if points are set to zero (disable). myCRED will check if this
			// hook has been enabled before calling this method so no need to check
			// that either.
			//add_action( 'personal_options_update',  array( $this, 'profile_update' ) );
			//add_action( 'edit_user_profile_update', array( $this, 'profile_update' ) );
			add_action( 'transition_post_status', array( $this, 'kleo_child_eventon_process_event' ) );
		}

		/**
		 * Check if the user qualifies for points
		 */
		public function kleo_child_eventon_process_event( $new_status, $old_status, $post ) {
			$post_type = get_post_type($post->ID);
			$event = get_post($post->ID); 

			if( $post_type !== 'ajde_events' )
			return;
file_put_contents(__DIR__.'/badges_log.txt', ' - 1 '.$event->post_author.' '.$old_status.' '.$new_status.' '.$event->post_status);

			if(($old_status == 'draft' && $new_status == 'publish') || ($event->post_status == 'draft' && $new_status == 'publish')) {
file_put_contents(__DIR__.'/badges_log.txt', ' - 2 '.$event->post_author);
				if ( $this->core->exclude_user( $event->post_author ) ) return;
file_put_contents(__DIR__.'/badges_log.txt', ' - 3 '.$event->post_author);
file_put_contents(__DIR__.'/badges_log.txt', ' - 3 |'.$event->ID.'| '.$event->post_author);

				if ( $this->has_entry( 'kleo_child_eventon_add', $event->ID, $event->post_author ) ) return;
file_put_contents(__DIR__.'/badges_log.txt', ' - 4 '.$event->post_author);

				// Execute
				$this->core->add_creds(
					'kleo_child_eventon_add',
					$event->post_author,
					$this->prefs['kleo_child_eventon_add']['creds'],
					$this->prefs['kleo_child_eventon_add']['log'],
					$event->ID,
					'',
					$this->mycred_type
				);
			}

			// Check if user is excluded (required)
			/*if ( $this->core->exclude_user( $user_id ) ) return;

			// Check to see if user has filled in their first and last name
			if ( empty( $_POST['first_name'] ) || empty( $_POST['last_name'] ) ) return;

			// Make sure this is a unique event
			if ( $this->has_entry( 'completing_profile', '', $user_id ) ) return;

			// Execute
			$this->core->add_creds(
				'completing_profile',
				$user_id,
				$this->prefs['creds'],
				$this->prefs['log'],
				'',
				'',
				$this->mycred_type
			);*/
		}

		/**
		 * Add Settings
		 */
		public function preferences() {
			// Our settings are available under $this->prefs
			$prefs = $this->prefs; ?>

<!-- First we set the amount -->
<label class="subheader"><?php echo $this->core->plural(); ?></label>
<ol>
	<li>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array('kleo_child_eventon_add' => 'creds') ); ?>" id="<?php echo $this->field_id( array('kleo_child_eventon_add' => 'creds') ); ?>" value="<?php echo $this->core->format_number( $prefs['kleo_child_eventon_add']['creds'] ); ?>" size="8" /></div>
	</li>
</ol>
<!-- Then the log template -->
<label class="subheader"><?php _e( 'Log template', 'mycred' ); ?></label>
<ol>
	<li>
		<div class="h2"><input type="text" name="<?php echo $this->field_name( array('kleo_child_eventon_add' => 'log') ); ?>" id="<?php echo $this->field_id( array('kleo_child_eventon_add' => 'log') ); ?>" value="<?php echo $prefs['kleo_child_eventon_add']['log']; ?>" class="long" /></div>
	</li>
</ol>
	<?php
		}

		/**
		 * Sanitize Preferences
		 */
		public function sanitise_preferences( $data ) {
			$new_data = $data;

			// Apply defaults if any field is left empty
			$new_data['kleo_child_eventon_add']['creds'] = ( !empty( $data['kleo_child_eventon_add']['creds'] ) ) ? $data['kleo_child_eventon_add']['creds'] : $this->defaults['kleo_child_eventon_add']['creds'];
			$new_data['kleo_child_eventon_add']['log'] = ( !empty( $data['kleo_child_eventon_add']['log'] ) ) ? sanitize_text_field( $data['kleo_child_eventon_add']['log'] ) : $this->defaults['kleo_child_eventon_add']['log'];

			return $new_data;
		}
	}
endif;
?>