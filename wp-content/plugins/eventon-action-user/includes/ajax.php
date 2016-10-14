<?php
/**
 * EventON ActionUser Ajax Handlers
 *
 * Handles AJAX requests via wp_ajax hook (both admin and front-end events)
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	ActionUser/Functions/AJAX
 * @version     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evoau_ajax{
	// construct
		public function __construct(){
			$ajax_events = array(
				'the_ajax_au'=>'the_ajax_au',
				'evoau_event_submission'=>'event_form_submission',
			);
			foreach ( $ajax_events as $ajax_event => $class ) {				
				add_action( 'wp_ajax_'.  $ajax_event, array( $this, $class ) );
				add_action( 'wp_ajax_nopriv_'.  $ajax_event, array( $this, $class ) );
			}
		}

	// Event form submission
		function event_form_submission(){
			global $eventon_au;

			if( (isset($_POST['evoau_noncename']) && isset( $_POST ) && wp_verify_nonce( $_POST['evoau_noncename'], AJDE_EVCAL_BASENAME )
				) ||
				( !empty($eventon_au->frontend->evoau_opt['evoau_form_nonce']) || $eventon_au->frontend->evoau_opt['evoau_form_nonce']=='yes'  )
			){

				echo json_encode($eventon_au->frontend->save_form_submissions());
				exit;

			}else{
				echo json_encode(array(
					'status'=>'bad','msg'=>'bad_nonce'
				)); exit;
			}
		}

	// load new role caps in admin
		function the_ajax_au(){
			global $eventon_au;
			
			$role = $_POST['role'];		
				
			$content = $eventon_au->admin->get_cap_list_admin($role);
			
			$return_content = array(
				'content'=> $content,
				'status'=>'ok'
			);
			
			echo json_encode($return_content);		
			exit;
		}

}new evoau_ajax();

?>