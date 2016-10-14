<?php
/**
 * RSVP frontend supporting functions
 * @version  0.2
 */
class evorsvp_functions{
	private $UMV = 'eventon_rsvp_user';

	// loggedin  user
		// version 2 of user rsvp data functions
			function print_debug($eventid){
				$data = get_post_meta($eventid, 'evors_data', true);
				print_r($data);
			}
			function save_user_rsvp_status($userid, $eventid, $ri=0, $rsvp_status, $eventpmv = ''){
				$rsvp_data = (!empty($eventpmv) && isset($eventpmv['evors_data']))? 
					unserialize($eventpmv['evors_data'][0]): get_post_meta($eventid, 'evors_data', true);

				// not the first itme doing this
				if(!empty($rsvp_data)){
					$rsvp_data[$userid][$ri] = $rsvp_status;
					update_post_meta($eventid, 'evors_data', $rsvp_data);
				}else{
					$rsvp_data[$userid][$ri] = $rsvp_status;
					add_post_meta($eventid, 'evors_data', $rsvp_data);
				}
			}
			function get_user_rsvp_status($userid, $eventid, $ri=0, $eventpmv = ''){
				$rsvp_data = (!empty($eventpmv) && isset($eventpmv['evors_data']))? 
					unserialize($eventpmv['evors_data'][0]): get_post_meta($eventid, 'evors_data', true);

				if(empty($rsvp_data)){
					return false;
				}else{
					return (isset($rsvp_data[$userid][$ri]))? $rsvp_data[$userid][$ri]: false;
				}
			}
			function trash_user_rsvp($userid, $eventid, $ri=0, $eventpmv = ''){
				$rsvp_data = (!empty($eventpmv) && isset($eventpmv['evors_data']))? 
					unserialize($eventpmv['evors_data'][0]): get_post_meta($eventid, 'evors_data', true);

				if(empty($rsvp_data)) return;

				if(empty($rsvp_data[$userid][$ri])) return;

				unset($rsvp_data[$userid][$ri]);
				update_post_meta($eventid,'evors_data', $rsvp_data);
			}
			function get_userloggedin_user_rsvp_status($eid, $ri=0, $eventpmv = ''){
				if(is_user_logged_in()){					
					return $this->get_user_rsvp_status($this->current_user_id(),$eid, $ri, $eventpmv );
				}else{return false;}
			}
			function current_user_id(){
				return get_current_user_id();
			}	

		// wait list functions
			function add_user_to_waitlist($userid, $eventid, $ri){
				$eventwaitlist = get_post_meta($eventid, 'evors_waitlist', true);
				// not the first itme doing this
				if(!empty($eventwaitlist) && isset($eventwaitlist[$ri]) ){
					if(!in_array($userid, $eventwaitlist[$ri])){
						$eventwaitlist[$ri][] = $userid;
						update_post_meta($eventid, 'evors_waitlist', $eventwaitlist);
					}else{
						return false;
					}					
				}else{
					$eventwaitlist[$ri][] = $userid;
					add_post_meta($eventid, 'evors_waitlist', $eventwaitlist);
				}
			}
			// move user out of wait list and into event rsvp list
			function move_user_to_event($userid, $eventid, $ri, $rsvp_status){
				$eventwaitlist = get_post_meta($eventid, 'evors_waitlist', true);
				if(!empty($eventwaitlist) && isset($eventwaitlist[$ri]) && in_array($userid, $eventwaitlist[$ri])){
					$key = array_search($userid, $eventwaitlist[$ri]);
					unset($eventwaitlist[$ri][$key]);

					// move user to event rsvp list
					$this->save_user_rsvp_status($userid, $eventid, $ri, $rsvp_status);
				}else{
					return false;
				}
			}

		// update to new version
			function update_to_new_system(){
				$user_data = get_option($this->UMV);
				foreach($user_data as $userid=>$data){
					foreach($data as $eventid=>$datar){
						foreach($datar as $ri=>$rsvp){
							$this->save_user_rsvp_status($userid, $eventid, $ri, $rsvp);
						}
					}
				}
				update_option('evors_update', 'true');
			}

		/*
			function add_user_meta($uid, $eid, $ri, $rsvp){
				$user_data = get_option($this->UMV);
				if(!empty($user_data) && !is_array($rsvp)){
					$user_data[$uid][$eid][$ri] = $rsvp;
					update_option($this->UMV, $user_data);
				}else{
					$user_data[$uid][$eid][$ri] = $rsvp;
					add_option($this->UMV, $user_data);
				}			
			}
			// GET user rsvp status by user id
				function get_user_rsvp_status($uid, $eid, $ri='0'){
					$user_data = get_option($this->UMV);
					//print_r($user_data);
					if(!empty($user_data)){
						return !empty($user_data[$uid][$eid][$ri])? $user_data[$uid][$eid][$ri]: false;
					}else{
						return false;
					}
				}
						
				function trash_user_rsvp($uid, $eid, $ri='0'){
					if($uid){
						$user_data = get_option($this->UMV);
						if(empty($user_data)) return;

						if(empty($user_data[$uid][$eid][$ri])) return;

						unset($user_data[$uid][$eid][$ri]);
						update_option($this->UMV, $user_data);
					}
				}
		*/
	
		// CHECK if user rsvped already
			function has_user_rsvped($post){
				$rsvped = new WP_Query( array(
					'posts_per_page'=>-1,
					'post_type' => 'evo-rsvp',
					'meta_query' => array(
						array('key' => 'email','value' => $post['email']),
						array('key' => 'e_id','value' => $post['e_id']),
						array('key' => 'repeat_interval','value' => $post['repeat_interval']),
					),
				));
				return ($rsvped->have_posts())? true: false;
			}
			function has_loggedin_user_rsvped(){
				$rsvped = new WP_Query( array(
					'posts_per_page'=>-1,
					'post_type' => 'evo-rsvp',
					'meta_query' => array(
						array('key' => 'e_id','value' => $post['e_id']),
						array('key' => 'repeat_interval','value' => $post['repeat_interval']),
						array('key' => 'userid','value' => $post['uid']),
					),
				));

				$rsvp = false;
				if($rsvped->have_posts() && $rsvped->found_posts==1){
					while($rsvped->have_posts()): $rsvped->the_post();
						$rsvp = get_post_meta($rsvped->post->ID, 'rsvp',true);
					endwhile;
				}
				wp_reset_postdata();

				return $rsvp;
			}

	// related to RSVP manager
		function get_user_events($userid){
			global $eventon_rs;

			$rsvps = new WP_Query(array(
				'posts_per_page'=>-1,
				'post_type' => 'evo-rsvp',
				'meta_query' => array(
					array('key' => 'userid','value' => $userid)
				),
				'meta_key'=>'last_name',
				'orderby'=>'post_date'
			));
			$userRSVP = array();
			ob_start();
			if($rsvps->have_posts()):					

				$datetime = new evo_datetime();
				$format = get_option('date_format');
				$currentTime = current_time('timestamp');

				while( $rsvps->have_posts() ): $rsvps->the_post();
					$_id = get_the_ID();
					$pmv = get_post_meta($_id);
					$checkin_status = (!empty($pmv['status']))? $pmv['status'][0]:'check-in'; // checkin status
					$e_id = (!empty($pmv['e_id']))? $pmv['e_id'][0]:false;

					if(!$e_id) continue;

					$epmv = get_post_custom($e_id);

					$rsvp = (!empty($pmv['rsvp'])?  $eventon_rs->frontend->get_rsvp_status($pmv['rsvp'][0]):'');
					$RI = (!empty($pmv['repeat_interval'])?$pmv['repeat_interval'][0]:'');

					$time = $datetime->get_correct_event_repeat_time($epmv, $RI, $format);
					$link = get_permalink($e_id);
					$link = $link.( strpos($link, '?')?'&ri='.$RI:'?ri='.$RI);

					$remaining_rsvp = $this->remaining_rsvp($epmv, $RI, $e_id);

					$p_classes = array();
					$p_classes[] = $time['start']>=$currentTime?'':'pastevent';

					echo "<p class='".(count($p_classes)>0? implode(' ', $p_classes):'')."'>RSVP ID: <b>#".$_id."</b> <span class='rsvpstatus'>{$rsvp}</span>
						<em class='checkin_status'>".$checkin_status."</em><br/>
						<em class='count'>".(!empty($pmv['count'])? $pmv['count'][0]:'-')."</em>
						<em class='event_data' >
							<span style='font-size:18px;'>EVENT: <a href='".$link."'>".get_the_title($e_id)."</a></span>
							<span class='event_time'>TIME: ".date($format.' h:i:a',$time['start'])." - ".date($format.' h:i:a',$time['end'])."</span>
							</em>
						";
					echo ($time['start']>=$currentTime)? 
						"<span class='action' data-cap='".(is_int($remaining_rsvp)? $remaining_rsvp:'na')."' data-etitle='".get_the_title($e_id)."' data-precap='".$this->is_per_rsvp_max_set($epmv)."' data-uid='{$userid}' data-rsvpid='{$_id}' data-eid='{$e_id}' data-ri='{$RI}' ><a class='update_rsvp' data-val='chu'>UPDATE</a></span>":'';
					echo "</p>";
					
				endwhile;
			endif;
			wp_reset_postdata();
			return ob_get_clean();
		}

	// RSVP post related
		// RETURN: remaining RSVP adjsuted for Repeat intervals
			function remaining_rsvp($event_pmv, $ri = 0, $event_id=''){
				// get already RSVP-ed count
				$yes = (!empty($event_pmv['_rsvp_yes']))? $event_pmv['_rsvp_yes'][0]:0;
				$maybe = (!empty($event_pmv['_rsvp_maybe']))? $event_pmv['_rsvp_maybe'][0]:0;

				// if capacity limit set for rsvp 
				if(!empty($event_pmv['evors_capacity']) && $event_pmv['evors_capacity'][0]=='yes'){
					// if capacity calculated per each repeat instance
					if($this->is_ri_count_active($event_pmv)){		
						$ri_capacity = unserialize($event_pmv['ri_capacity_rs'][0]);			
						$ri_count = !empty($event_pmv['ri_count_rs'])? unserialize($event_pmv['ri_count_rs'][0]):null;	

						if(empty($ri_capacity[$ri])) return 0;

						// if count not saved
						if(empty($ri_count)){
							$this->update_ri_count($event_id, $ri, 'y', $yes);
							$this->update_ri_count($event_id, $ri, 'm', $maybe);
						}	
						$count = (!empty($ri_count))? (!empty($ri_count[$ri]['y'])? $ri_count[$ri]['y']:0)+
							(!empty($ri_count[$ri]['m'])? $ri_count[$ri]['m']:0)
							:($yes+$maybe);

						return $ri_capacity[$ri] - $count;
					}elseif(
						// not 
						!empty($event_pmv['evors_capacity_count'])
					){
						$capacity = (int)$event_pmv['evors_capacity_count'][0];
						$remaining =  $capacity - ( $yes + $maybe);
						return ($remaining>0)? $remaining: false;
					}elseif($event_pmv['evors_capacity'][0]=='no'){
						return true;
					}
				}else{
				// set capacity limit is NOT set
					return true;
				}
			}
		// return total capacity for events adjusted for repeat intervals
			function get_total_adjusted_capacity($eid, $ri=0, $epmv=''){
				$epmv = (!empty($epmv))? $epmv: get_post_meta($eid);

				$setCap = (!empty($epmv['evors_capacity']) && $epmv['evors_capacity'][0]=='yes' )? true:false;
				$setCapVal = (!empty($epmv['evors_capacity_count']) )? $epmv['evors_capacity_count'][0]:false;
				$managRIcap = (!empty($epmv['_manage_repeat_cap_rs']) && $epmv['_manage_repeat_cap_rs'][0]=='yes')? true:false;
				$riCap = (!empty($epmv) && !empty($epmv['ri_capacity_rs']))? 
					unserialize($epmv['ri_capacity_rs'][0]):false;

				// if managing capacity per each ri
				if($managRIcap && $riCap){
					return !empty($riCap[$ri])? $riCap[$ri]:0;
				// if total capacity limit for event
				}elseif($setCap && $setCapVal){
					return ($setCapVal)? $setCapVal: 0;
				}else{
					return 0;
				}
				
			}
		// CHECK FUNCTIONs remaining RSVP
			function show_spots_remaining($event_pmv){
				return (!empty($event_pmv['evors_capacity_count'])
					&& !empty($event_pmv['evors_capacity_show'])
					&& $event_pmv['evors_capacity_show'][0] == 'yes'
					&& !empty($event_pmv['evors_capacity']) && $event_pmv['evors_capacity'][0]=='yes'
				)? true:false;
			}
			function show_whoscoming($event_pmv){
				return (!empty($event_pmv['evors_show_whos_coming'])
					&& $event_pmv['evors_show_whos_coming'][0] == 'yes')? true:false;
			}
			// check if repeat interval rsvp is activate
			function is_ri_count_active($event_pmv){
				 return (
					!empty($event_pmv['evors_capacity']) && $event_pmv['evors_capacity'][0]=='yes'
					&& !empty($event_pmv['_manage_repeat_cap_rs']) && $event_pmv['_manage_repeat_cap_rs'][0]=='yes'
					&& !empty($event_pmv['evcal_repeat']) && $event_pmv['evcal_repeat'][0] == 'yes' 
					&& !empty($event_pmv['ri_capacity_rs']) 
				)? true:false;
			}

		// GET RSVP attendee list as ARRAY
			function GET_rsvp_list($eventID, $ri=''){
				global $eventon_rs;

				$event_pmv = get_post_custom($eventID);
				$ri_count_active = $this->is_ri_count_active($event_pmv);
				$guestsAR = array('y'=>array(),'m'=>array(),'n'=>array());

				$metaKey = (!empty($eventon_rs->evors_opt['evors_orderby']) && $eventon_rs->evors_opt['evors_orderby']=='fn')? 'first_name':'last_name';

				$guests = new WP_Query(array(
					'posts_per_page'=>-1,
					'post_type' => 'evo-rsvp',
					'meta_query' => array(
						array('key' => 'e_id','value' => $eventID)
					),
					'meta_key'=>$metaKey,
					'orderby'=>array('meta_value'=>'DESC','title'=>'ASC')
				));
				if($guests->have_posts()):
					while( $guests->have_posts() ): $guests->the_post();
						$_id = get_the_ID();
						$pmv = get_post_meta($_id);
						$_status = (!empty($pmv['status']))? $pmv['status'][0]:'check-in';
						$rsvp = (!empty($pmv['rsvp']))? $pmv['rsvp'][0]:false;
						$e_id = (!empty($pmv['e_id']))? $pmv['e_id'][0]:false;

						if(!$rsvp) continue;
						if(!$e_id || $e_id!=$eventID) continue;

						if(
							(
								$ri_count_active && 
								((!empty($pmv['repeat_interval']) && $pmv['repeat_interval'][0]==$ri)
									|| ( empty($pmv['repeat_interval']) && $ri==0)
								)
							)
							|| !$ri_count_active 
							|| $ri=='all'
						){
							$lastName = isset($pmv['last_name'])? $pmv['last_name'][0]:'';
							$firstName = isset($pmv['first_name'])? $pmv['first_name'][0]:'';
							$guestsAR[$rsvp][$_id] = array(
								'fname'=> $firstName,
								'lname'=> $lastName,
								'name'=> $lastName.(!empty($lastName)?', ':'').$firstName,
								'email'=> $pmv['email'][0],
								'phone'=> (!empty($pmv['phone'])?$pmv['phone'][0]:''),
								'status'=>$_status,
								'count'=>$pmv['count'][0],						
							);
						}

					endwhile;
				endif;
				wp_reset_postdata();
				return array('y'=>$guestsAR['y'], 'm'=>$guestsAR['m'], 'n'=>$guestsAR['n']);
			}

		// GET repeat interval RSVP count
			function get_ri_count($rsvp, $ri=0, $event_pmv=''){
				$ri_count = (!empty($event_pmv) && !empty($event_pmv['ri_count_rs']))? 
					unserialize($event_pmv['ri_count_rs'][0]):false;
				if(!$ri_count) return 0;
				return !empty($ri_count[$ri][$rsvp])? $ri_count[$ri][$rsvp]:0;
			}
		// GET rsvp (remaining) count RI or not
			function get_rsvp_count($event_pmv, $rsvp, $ri=0){
				if($this->is_ri_count_active($event_pmv)){
					return $this->get_ri_count($rsvp, $ri, $event_pmv);
				}else{
					global $eventon_rs;
					return !empty($event_pmv['_rsvp_'.$eventon_rs->rsvp_array[$rsvp]])? 
						$event_pmv['_rsvp_'.$eventon_rs->rsvp_array[$rsvp]][0]:0;
				}				
			}
			function get_ri_remaining_count($rsvp, $ri=0, $ricount, $eventpmv){
				$openCount = (int)$this->get_ri_count($rsvp, $ri, $eventpmv);
				return $ricount - $openCount;
			}
			// GET rsvp count for given rsvp type
			function get_event_rsvp_count($event_id, $rsvp_type, $event_pmv=''){
				$event_pmv = (!empty($event_pmv))? $event_pmv: get_post_meta($event_id);
				return (!empty($event_pmv['_rsvp_'.$rsvp_type]))? $event_pmv['_rsvp_'.$rsvp_type][0]:'0';
			}


		// UPDATE repeat interval RSVP count
		// val = y,n
			function update_ri_count($event_id, $ri, $val, $count){
				$ri_count = get_post_meta($event_id, 'ri_count_rs', true);
				$ri_count = !empty($ri_count)? $ri_count: false;
				$ri_count[$ri][$val] = $count;
				update_post_meta($event_id, 'ri_count_rs', $ri_count);
			}
			public function _form_update_rsvp($post){
				global $eventon_rs;

				// update each fields
				foreach($post as $field=>$value){
					update_post_meta($post['rsvpid'], $field, $value);
				}
				// update usermeta
				if(isset($post['userid']) && isset($post['e_id'])){
					$this->save_user_rsvp_status($post['userid'], $post['e_id'], $post['repeat_interval'], $post['rsvp']);
				}

				// send confirmation email
				$post['rsvp_id'] = $post['rsvpid'];
				$post['emailtype'] = 'update';
				$eventon_rs->frontend->send_email($post);
				$eventon_rs->frontend->send_email($post, 'notification');

				// sync rsvp count after update
				$this->sync_rsvp_count($post['e_id']);
				return true;
			}

		// find a RSVP
			public function find_rsvp($rsvpid, $fname, $eid){
				$rsvp = get_post($rsvpid);
				if($rsvp){
					$rsvp_meta = get_post_custom($rsvpid);

					// check if first name and event id
					return ($fname == $rsvp_meta['first_name'][0] && $eid == $rsvp_meta['e_id'][0])? array('rsvp'=>$rsvp_meta['rsvp'][0], 'count'=>$rsvp_meta['count'][0]): false;
				}else{ return false;}
			}
		
		// SYNC rsvp status for an event
			function sync_rsvp_count($event_id){
				global $wpdb, $eventon_rs;

				// check if repeat interval RSVP active
				$event_pmv = get_post_custom($event_id);
				$is_ri_count_active = $this->is_ri_count_active($event_pmv);

				$ri_count = array();
				$rsvp_count = array('y'=>0,'n'=>0,'m'=>0);

				$evoRSVP = new WP_Query( array(
					'posts_per_page'=>-1,
					'post_type' => 'evo-rsvp',
					'meta_query' => array(
						array('key' => 'e_id','value' => $event_id,)
					)
				));
				if($evoRSVP->found_posts>0){
					while($evoRSVP->have_posts()): $evoRSVP->the_post();
						$rsvpPMV = get_post_custom($evoRSVP->post->ID);

						$rsvp = !empty($rsvpPMV['rsvp'])? $rsvpPMV['rsvp'][0]:false;
						$count = !empty($rsvpPMV['count'])? (int)$rsvpPMV['count'][0]:0;
						$ri = !empty($rsvpPMV['repeat_interval'])? $rsvpPMV['repeat_interval'][0]:0;

						$rsvp_count[$rsvp] = !empty($rsvp_count[$rsvp])? $rsvp_count[$rsvp]+$count: $count;

						if($is_ri_count_active){
							$ri_count[$ri][$rsvp] = !empty($ri_count[$ri][$rsvp])? $ri_count[$ri][$rsvp]+$count: $count;
						}

					endwhile;

					if(!empty($rsvp_count['y'])) update_post_meta($event_id,'_rsvp_yes', $rsvp_count['y'] );
					update_post_meta($event_id,'_rsvp_no', $rsvp_count['n'] );
					update_post_meta($event_id,'_rsvp_maybe', $rsvp_count['m'] );

					if(!empty($ri_count))
						update_post_meta($event_id,'ri_count_rs', $ri_count );

				}else{// no rsvps found
					update_post_meta($event_id,'_rsvp_yes', $rsvp_count['y'] );
					update_post_meta($event_id,'_rsvp_no', $rsvp_count['n'] );
					update_post_meta($event_id,'_rsvp_maybe', $rsvp_count['m'] );
				}
				wp_reset_postdata();
				/*
					// run through each rsvp status value
					foreach( $eventon_rs->frontend->rsvp_array as $rsvp=>$rsvpf){
						$ids = array();
						
						$_status = new WP_Query( array(
							'posts_per_page'=>-1,
							'post_type' => 'evo-rsvp',
							'meta_query' => array(
								'relation' => 'AND',
								array('key' => 'rsvp','value' => $rsvp,),
								array('key' => 'e_id','value' => $event_id,)
							)
						));

						if($_status->found_posts>0):
							while($_status->have_posts()): $_status->the_post();
								$rsvpPMV = get_post_custom($_status->post->ID);

								$ids[]= get_the_ID();
							endwhile;
							$idList = implode(",", $ids);		
							$count = $wpdb->get_var($wpdb->prepare("
								SELECT sum(meta_value)
								FROM $wpdb->postmeta
								WHERE meta_key = %s
								AND post_id in (".$idList.")", 'count'
								));
							$count = (!empty($count))?$count :0;
						else:
							$count =  0;
						endif;					
						update_post_meta($event_id,'_rsvp_'.$rsvpf, $count );
						wp_reset_postdata();
					}
				*/				
			}

	// EMAIL related
		function get_proper_times($eventpmv, $ri=0){
			global $eventon;

			$datetime = new evo_datetime();
			$correct_unix = $datetime->get_correct_event_repeat_time($eventpmv, $ri);
			$strings = $datetime->get_formatted_smart_time($correct_unix['start'], $correct_unix['end'],$eventpmv);
			
			return $strings;
		}
		public function _event_date($pmv, $start_unix, $end_unix){
			global $eventon;
			$evcal_lang_allday = eventon_get_custom_language( '','evcal_lang_allday', 'All Day');
			$date_array = $eventon->evo_generator->generate_time_('','', $pmv, $evcal_lang_allday,'','',$start_unix,$end_unix);	
			return $date_array;
		}
		public function edit_post_link($id){
			return get_admin_url().'post.php?post='.$id.'&action=edit';	
		}

	// Supporting
	
	// get IP address of user
		function get_client_ip() {
		    $ipaddress = '';
		    if ($_SERVER['HTTP_CLIENT_IP'])
		        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		    else if($_SERVER['HTTP_X_FORWARDED_FOR'])
		        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		    else if($_SERVER['HTTP_X_FORWARDED'])
		        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		    else if($_SERVER['HTTP_FORWARDED_FOR'])
		        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		    else if($_SERVER['HTTP_FORWARDED'])
		        $ipaddress = $_SERVER['HTTP_FORWARDED'];
		    else if($_SERVER['REMOTE_ADDR'])
		        $ipaddress = $_SERVER['REMOTE_ADDR'];
		    else
		        $ipaddress = false;
		    return $ipaddress;
		}
		function get_current_userid(){
			if(is_user_logged_in()){
				global $current_user;
				get_currentuserinfo();
				return $current_user->ID;
			}else{
				return false;
			}
		}
		// check if per rsvp count max set and return the max value
		function is_per_rsvp_max_set($event_pmv){
			return (!empty($event_pmv['evors_max_active']) && $event_pmv['evors_max_active'][0]=='yes' && !empty($event_pmv['evors_max_count'])) ? $event_pmv['evors_max_count'][0]: 'na';
		}
}