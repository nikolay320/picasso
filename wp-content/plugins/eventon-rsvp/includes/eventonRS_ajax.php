<?php
/**
 * RSVP Events Ajax Handlers
 *
 * Handles AJAX requests via wp_ajax hook (both admin and front-end events)
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	EventON-RS/Functions/AJAX
 * @version     2.3.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evorsvp_ajax{
	public function __construct(){
		$ajax_events = array(
				'the_ajax_evors'=>'evoRS_save_rsvp',
				'the_ajax_evors_fnd'=>'evoRS_find_rsvp',
				'the_ajax_evors_a1'=>'evoRS_getattendees',
				'the_ajax_evors_a3'=>'evoRS_generate_csv',
				'the_ajax_evors_a2'=>'evoRS_sync_count',
				'the_ajax_evors_a4'=>'evoRS_checkin',
				'the_ajax_evors_a4X'=>'evoRS_checkinAB',
				'the_ajax_evors_a5'=>'evoRS_admin_resend_confirmation',
				'the_ajax_evors_a6'=>'evoRS_admin_custom_confirmation',
				'the_ajax_evors_a7'=>'rsvp_from_eventtop',
				'the_ajax_evors_a8'=>'find_rsvp_byuser',
				'the_ajax_evors_a9'=>'emailing_rsvp_admin',
				'the_ajax_evors_a10'=>'update_rsvp_manager',
			);
			foreach ( $ajax_events as $ajax_event => $class ) {				
				add_action( 'wp_ajax_'.  $ajax_event, array( $this, $class ) );
				add_action( 'wp_ajax_nopriv_'.  $ajax_event, array( $this, $class ) );
			}
	}
	// NEW RSVP from EVENTTOP
		function rsvp_from_eventtop(){
			$status = 0;
			$message ='';
			
			global $eventon_rs;
			$front = $eventon_rs->frontend;
			
			// sanitize each posted values
			foreach($_POST as $key=>$val){
				$post[$key]= sanitize_text_field(urldecode($val));
			}

			// pull email and name from user data
			if(!empty($post['uid'])){
				$user_info = get_userdata($post['uid']);
				if(!empty($user_info->user_email))
					$post['email']= $user_info->user_email;
				if(!empty($user_info->first_name))
					$post['first_name']= $user_info->first_name;
				if(!empty($user_info->last_name))
					$post['last_name']= $user_info->last_name;

				// other default values
				$post['count']='1';
			}

			// check if already rsvped
			$already_rsvped = $front->functions->has_user_rsvped($post);
			if(!$already_rsvped){
				$save= $front->_form_save_rsvp($post);
				$message = ($save==7)? 
					$front->get_form_message('err7', $post['lang']): $front->get_form_message('succ', $post['lang']);
			}else{// already rsvped
				$message = $front->get_form_message('err8', $post['lang']);
				$status = 0;
			}
					
			$return_content = array(
				'message'=> $message,
				'status'=>(($status==7)?7:0)
			);
			
			echo json_encode($return_content);		
			exit;
		}
	// SAVE a RSVP from the rsvp form
		function evoRS_save_rsvp(){
			
			$nonce = $_POST['postnonce'];
			$status = 0;
			$message = $save = '';

			if(! wp_verify_nonce( $nonce, 'evors_nonce' ) ){
				$status = 1;	$message ='Invalid Nonce';				
			}else{
				global $eventon_rs;
				$front = $eventon_rs->frontend;
				
				// sanitize each posted values
				foreach($_POST as $key=>$val){
					$post[$key]= sanitize_text_field(urldecode($val));
				}
					
				// if UPDATING
				if(!empty($post['rsvpid'])){
					$save= $front->functions->_form_update_rsvp($post);
					$status = 0;
				}else{
					// check if already rsvped
					$already_rsvped = $front->functions->has_user_rsvped($post);
					if(!$already_rsvped){
						$save= $front->_form_save_rsvp($post); // pass the rsvp id for change rsvp status after submit
						$status = ($save==7)? 7: 0;
					}else{ $status = 8;}
				}		
				$message = $save;
			}
					
			$return_content = array(
				'message'=> $message,
				'status'=>$status
			);
			
			echo json_encode($return_content);		
			exit;
		}

	// FIND RSVP in order to change
		function evoRS_find_rsvp(){
			global $eventon_rs;
			$front = $eventon_rs->frontend;

			$rsvp = get_post($_POST['rsvpid']);
			$post_type = get_post_type($_POST['rsvpid']);

			if($rsvp!='' && $post_type =='evo-rsvp'){
				$rsvp_meta = get_post_meta($_POST['rsvpid']);
			}else{
				$rsvp_meta = false;
			}		
			// send out results
			echo json_encode(array(
				'status'=>(($rsvp!='')? '0':'1'),			
				'content'=> $rsvp_meta,
			));		
			exit;
		}
		function find_rsvp_byuser(){
			$rsvp = new WP_Query(array(
				'post_type'=>'evo-rsvp',
				'meta_query' => array(
					array(
						'key'     => 'userid',
						'value'   => $_POST['uid'],
					),
					array(
						'key'     => 'e_id',
						'value'   => $_POST['eid'],
					),array(
						'key'     => 'repeat_interval',
						'value'   => $_POST['ri'],
					),
				),
			));
			$rsvpid = false;
			if($rsvp->have_posts()){
				while($rsvp->have_posts()): $rsvp->the_post();
					$rsvpid = $rsvp->post->ID;
				endwhile;
				wp_reset_postdata();

				if(!empty($rsvpid)){
					$rsvp_meta = get_post_meta($rsvpid);
					$status = 0;
				}else{
					$status = 1;
				}
			}else{
				$status = 1;
			}

			// send out results
			echo json_encode(array(
				'status'=>$status,
				'rsvpid'=> ($rsvpid? $rsvpid:''),		
				'content'=> (!empty($rsvp_meta)? $rsvp_meta: ''),
			));		
			exit;
		}

	// GET list of attendees for event
		function evoRS_getattendees(){
			global $eventon_rs;
			$status = 0;
			ob_start();

				$ri = (!empty($_POST['ri']) || (!empty($_POST['ri']) && $_POST['ri']==0 ))? $_POST['ri']:'all'; // repeat interval
				$__checking_status_text = $eventon_rs->frontend->get_trans_checkin_status();

				$RSVP_LIST = $eventon_rs->frontend->functions->GET_rsvp_list($_POST['e_id'], $ri);
				
				echo "<div class='evors_list'>";
				echo "<p class='header'>RSVP Status: YES</p>";
				if(!empty($RSVP_LIST['y']) && count($RSVP_LIST['y'])>0){
					foreach($RSVP_LIST['y'] as $_id=>$rsvp){
						$phone = !empty($rsvp['phone'])? $rsvp['phone']:false;
						$_status = (!empty($rsvp['status']))? $rsvp['status']:'check-in';
						$_status = $__checking_status_text[$_status];
						?>
						<p><?php echo '#'.$_id.' '. $rsvp['name'].' <i>('.$rsvp['email'].( $phone? ' PHONE:'.$phone:'').')</i>';?><span data-id='<?php echo $_id;?>' data-status='<?php echo $_status;?>' class='checkin <?php echo ($_status=='checked')? 'checked':null;?>'><?php echo $_status;?></span><span><?php echo $rsvp['count'];?></span></p>
						<?php
					}
				}else{
					echo "<p>No Attendees found.</p>";
				}

				echo "<p class='header'>RSVP Status: MAYBE</p>";
				if(!empty($RSVP_LIST['m']) && count($RSVP_LIST['m'])>0){
					foreach($RSVP_LIST['m'] as $_id=>$rsvp){
						$phone = !empty($rsvp['phone'])? $rsvp['phone']:false;
						$_status = (!empty($rsvp['status']))? $rsvp['status']:'check-in';
						$_status = $__checking_status_text[$_status];
						?>
						<p><?php echo '#'.$_id.' '. $rsvp['name'].' <i>('.$rsvp['email'].( $phone? ' PHONE:'.$phone:'').')</i>';?><span data-id='<?php echo $_id;?>' data-status='<?php echo $_status;?>' class='checkin <?php echo ($_status=='checked')? 'checked':null;?>'><?php echo $_status;?></span><span><?php echo $rsvp['count'];?></span></p>
						<?php
					}
				}else{
					echo "<p>No Attendees found.</p>";
				}
				

				echo "</div>";


			$output = ob_get_clean();

			$return_content = array(
				'content'=> $output,
				'status'=>$status
			);
			
			echo json_encode($return_content);		
			exit;
		}

	// Download CSV of attendance
		function evoRS_generate_csv(){

			global $eventon_rs;
			$e_id = $_REQUEST['e_id'];

			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename=RSVP_attendees_".date("d-m-y").".csv");
			header("Pragma: no-cache");
			header("Expires: 0");
			//$fp = fopen('file.csv', 'w');

			echo "Last Name,First Name,Email Address,Phone,Email Updates,RSVP,Count\n";

			$entries = new WP_Query(array(
				'posts_per_page'=>-1,
				'post_type' => 'evo-rsvp',
				'meta_query' => array(
					array('key' => 'e_id','value' => $e_id,'compare' => '=',	)
				)
			));
			if($entries->have_posts()):
				$array = $eventon_rs->rsvp_array;
				while($entries->have_posts()): $entries->the_post();
					$__id = get_the_ID();
					$pmv = get_post_meta($__id);

					echo (isset($pmv['last_name'])?$pmv['last_name'][0]:'').",".$pmv['first_name'][0].",".$pmv['email'][0].",".
						(!empty($pmv['phone'])? $pmv['phone'][0]:'').",".
						$pmv['updates'][0].",". $array[$pmv['rsvp'][0]].",".$pmv['count'][0]."\n";

				endwhile;
			endif;
			wp_reset_postdata();
		}

	// SYNC count
		function evoRS_sync_count(){

			$status = 0;
			$e_id = $_POST['e_id'];

			global $eventon_rs;
			$eventon_rs->frontend->functions->sync_rsvp_count($e_id);

				$pmv = '';
				$yes_count =  $eventon_rs->frontend->functions->get_event_rsvp_count($e_id, 'yes');
				$maybe_count =  $eventon_rs->frontend->functions->get_event_rsvp_count($e_id, 'maybe');
				$no_count =  $eventon_rs->frontend->functions->get_event_rsvp_count($e_id, 'no');

				ob_start();
			?>
				<p><b><?php echo $yes_count; ?></b><span>YES</span></p>
				<p><b><?php echo $maybe_count;?></b><span>Maybe</span></p>
				<p><b><?php echo $no_count;?></b><span>No</span></p>
				<div class='clear'></div>	
			<?php

			$return_content = array(
				'content'=> ob_get_clean(),
				'status'=>$status
			);
			
			echo json_encode($return_content);		
			exit;

		}

	// update RSVP Manager
		function update_rsvp_manager(){
			global $eventon_rs;
			$return_content = array(
				'content'=> $eventon_rs->frontend->functions->get_user_events($_POST['uid'])
			);
			
			echo json_encode($return_content);		
			exit;
		}

	// emaling attendees
		function emailing_rsvp_admin(){
			global $eventon_rs, $eventon;

			$eid = $_POST['eid'];
			$type = $_POST['type'];
			$RI = !empty($_POST['repeat_interval'])? $_POST['repeat_interval']:'all'; // repeat interval
			$emails = $EMAILED = $_message_addition = false;

			// email attendees list to someone
			if($type=='someone'){
				$emails = explode(',', str_replace(' ', '', htmlspecialchars_decode($_POST['emails'])));

				$guests = $eventon_rs->frontend->functions->GET_rsvp_list($eid, $RI);
				if(is_array($guests) && isset($guests['y']) && count($guests['y'])>0){
					ob_start();
					
					$datetime = new evo_datetime();
					$epmv = get_post_custom($eid);
					$eventdate = $datetime->get_correct_formatted_event_repeat_time($epmv, ($RI=='all'?'0':$RI));

					echo "<p>Guests Attending to ".get_the_title($eid)." on ".$eventdate['start']."</p>";
					echo "<table style='padding-top:15px; width:100%;text-align:left'><thead><tr><th>Last Name</th><th>First Name</th><th>Email</th><th>Count</th></tr></thead>
					<tbody>";
					foreach($guests['y'] as $guest){
						echo "<tr><td>".$guest['lname'] ."</td><td>".$guest['fname']."</td><td>".$guest['email']. "</td><td>".$guest['count']."</td></tr>";
					}
					echo "</tbody></table>";
					$_message_addition = ob_get_clean();
				}

			}elseif($type=='coming'){
				$guests = $eventon_rs->frontend->functions->GET_rsvp_list($eid, $RI);
				foreach(array('y','m') as $rsvp_status){
					if(is_array($guests) && isset($guests[$rsvp_status]) && count($guests[$rsvp_status])>0){
						foreach($guests[$rsvp_status] as $guest){
							$emails .= $guest['email'].',';
						}
					}
				}
			}elseif($type=='notcoming'){
				$guests = $eventon_rs->frontend->functions->GET_rsvp_list($eid, $RI);
				if(is_array($guests) && isset($guests['n']) && count($guests['n'])>0){
					foreach($guests['n'] as $guest){
						$emails .= $guest['email'] .',';
					}
				}
			}elseif($type=='all'){
				$guests = $eventon_rs->frontend->functions->GET_rsvp_list($eid, $RI);
				foreach(array('y','m','n') as $rsvp_status){
					if(is_array($guests) && isset($guests[$rsvp_status]) && count($guests[$rsvp_status])>0){
						foreach($guests[$rsvp_status] as $guest){
							$emails .= $guest['email'] .',';
						}
					}
				}
			}

			// emaling
			if($emails){				
				$messageBODY = "<div style='padding:15px'>".(!empty($_POST['message'])? strip_tags($_POST['message']).'<br/><br/>':'' ).($_message_addition?$_message_addition:'') . "</div>";
				$messageBODY = $eventon_rs->frontend->get_evo_email_body($messageBODY);
				$from_email = $eventon_rs->frontend->get_from_email();
			
				$args = array(
					'html'=>'yes',
					'to'=> ($type=='someone'? $emails: $from_email),
					'subject'=>$_POST['subject'],
					'from'=>$from_email,
					'message'=>$messageBODY,
				);

				$headers = 'From: '.$from_email. "\r\n";
				$headers .= 'Reply-To: '.$from_email. "\r\n";
				if($type!='someone') $headers .= 'Bcc: '.$emails. "\r\n";

				$args['header'] = $headers;

				//print_r($args);
				$EMAILED =  $eventon_rs->helper->send_email($args);
			}			

			$return_content = array(
				'status'=> ($EMAILED?'0':'did not go'),
				'other'=>$args
			);
			
			echo json_encode($return_content);		
			exit;
		}

	// CHECK in attendee
		function evoRS_checkin(){
			$rsvp_id = $_POST['rsvp_id'];
			$status = $_POST['status'];

			update_post_meta($rsvp_id, 'status',$status);

			$return_content = array(
				'status'=>'0'
			);
			
			echo json_encode($return_content);		
			exit;
		}

	// CHECK in attendee from rsvp edit page
		function evoRS_checkinAB(){
			global $eventon_rs;

			$rsvp_id = $_POST['rsvp_id'];
			$status = $_POST['status'];

			update_post_meta($rsvp_id, 'status',$status);

			$return_content = array(
				'new_status_lang'=>$eventon_rs->frontend->get_checkin_status($status),
			);
			
			echo json_encode($return_content);		
			exit;
		}

	// resend confirmation
		function evoRS_admin_resend_confirmation(){
			global $eventon_rs;

			$rsvp_id = $_POST['rsvp_id'];			
			$rsvp_pmv = get_post_custom($rsvp_id);

			$args['rsvp_id'] = $rsvp_id;
			$args['first_name'] = (!empty($rsvp_pmv['first_name']))?$rsvp_pmv['first_name'][0]:null;
			$args['last_name'] = (!empty($rsvp_pmv['last_name']))?$rsvp_pmv['last_name'][0]:null;
			$args['email'] = (!empty($rsvp_pmv['email']))?$rsvp_pmv['email'][0]:null;
			$args['e_id'] = (!empty($rsvp_pmv['e_id']))?$rsvp_pmv['e_id'][0]:null;
			$args['rsvp'] = (!empty($rsvp_pmv['rsvp']))?$rsvp_pmv['rsvp'][0]:null;
			$args['repeat_interval'] = (!empty($rsvp_pmv['repeat_interval']))?$rsvp_pmv['repeat_interval'][0]:0;

			$send_mail = $eventon_rs->frontend->send_email($args);

			$return_content = array(
				'status'=>'0'
			);
			
			echo json_encode($return_content);		
			exit;
		}

	// send custom emails
		function evoRS_admin_custom_confirmation(){
			global $eventon_rs;

			$rsvp_id = $_POST['rsvp_id'];			
			$rsvp_pmv = get_post_custom($rsvp_id);

			$args['rsvp_id'] = $rsvp_id;
			$args['first_name'] = (!empty($rsvp_pmv['first_name']))?$rsvp_pmv['first_name'][0]:null;
			$args['last_name'] = (!empty($rsvp_pmv['last_name']))?$rsvp_pmv['last_name'][0]:null;
			$args['email'] = $_POST['email'];
			$args['e_id'] = (!empty($rsvp_pmv['e_id']))?$rsvp_pmv['e_id'][0]:null;
			$args['rsvp'] = (!empty($rsvp_pmv['rsvp']))?$rsvp_pmv['rsvp'][0]:null;
			$args['repeat_interval'] = (!empty($rsvp_pmv['repeat_interval']))?$rsvp_pmv['repeat_interval'][0]:0;

			$send_mail = $eventon_rs->frontend->send_email($args);

			$return_content = array(
				'status'=>'0'
			);
			
			echo json_encode($return_content);		
			exit;
		}
}
new evorsvp_ajax();
?>