<?php 

//add_action('wp_footer', 'marylinkcustom_the_notify_trans', 15);


function marylinkcustom_the_notify_trans() {


	$post_type = $_GET;

    var_dump($post_type);

	$isNoti = (int) $post_type['noti'];


	if (empty($isNoti) || $isNoti !== 1)
		return 0;

	set_transient('setNotify', 1, 500 );
    return 1;
}


//add_action('wp_footer', 'marylinkcustom_add_notification', 20);


function marylinkcustom_add_notification() {

	$post_type = $_GET;

	$checkTrans = (int) get_transient('setNotify' );


	if (empty($checkTrans) || $checkTrans !== 1 || empty($post_type['post_id_class']) || empty($post_type['ques_user_link']))
		return 0;

	if (!empty($post_type['type']))
		$type = $post_type['type'];
	else
		$type = null;

	if (!empty($post_type['url']))
		$url = $post_type['url'];
	else
		$url = null;

	if (!empty($post_type['post_id_class']))
		$post_id = (int) preg_replace("/[^0-9]/", "", $post_type['post_id_class']);
	else
		$post_id = null;

	if (!empty($post_type['ques_user_link']))
		$ques_author_slash = preg_split("/members\//", $post_type['ques_user_link'])[1];
	else
		$ques_author_slash = null;

	if (!empty($ques_author_slash))
		$ques_author_name = preg_split("/\//", $ques_author_slash)[0];
	else
		$ques_author_name = null;

    if (!empty($ques_author_name))
        $ques_author_id = get_user_by( 'slug', $ques_author_name )->ID;
    else
        $ques_author_id = null;


    if (!empty($post_type['title']))
        $title = $post_type['title'];
    else
        $title = null;



	    $this_user_id = get_current_user_id();

	    $time = time();


	    $add_notification = bp_notifications_add_notification( array(
            'user_id'           => $ques_author_id,
            'item_id'           => $post_id,
            'secondary_item_id' => $time,
            'component_name'    => 'sabai_notification',
            'component_action'  => "sabai_notification_custom_action_{$time}",
            'date_notified'     => bp_core_current_time(),
        ) );

//var_dump($add_notification);

        $data_array_sabai_noti = array(
            'type' => $type, 
            'url' => $url, 
            'post_id' => $post_id, 
            'ques_author_name' => $ques_author_name, 
            'ques_author_id' => $ques_author_id, 
            'notifier_id'   => $this_user_id,
            'title' => $title
            );


    $add_notify_meta = add_user_meta( $ques_author_id, $time, $data_array_sabai_noti);
   // var_dump($add_notify_meta);

    delete_transient('setNotify' );
    echo json_encode(array(
        'add_notification' => $add_notification,
        'add_notify_meta'  => $add_notify_meta
    ));  

     wp_die();
  
}


function sabai_custom_filter_notifications_get_registered_components( $component_names = array() ) {
     
        if ( ! is_array( $component_names ) ) {
            $component_names = array();
        }

        array_push( $component_names, 'sabai_notification' );


        return $component_names;
    }
//add_filter( 'bp_notifications_get_registered_components', 'sabai_custom_filter_notifications_get_registered_components' );

function sabai_custom_idea_buddypress_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string', $time = null) {
     	
     	//var_dump($action);

		//var_dump($secondary_item_id);
        // New custom notifications
        if ( 'sabai_notification_custom_action_'.$secondary_item_id === $action ) {
        
            $comment = get_comment( $item_id );
            
            $get_the_title = get_the_title( $comment->comment_post_ID );

           /* $custom_title = $comment->comment_author . __(' tested on the idea ', 'marylink-custom-plugin') . get_the_title( $comment->comment_post_ID );
            $custom_link  = get_comment_link( $comment );
            $custom_text = $comment->comment_author . __(' tested on your idea ', 'marylink-custom-plugin') . get_the_title( $comment->comment_post_ID );*/
            $custom_title = ' the_title ';
            $custom_link  = 'http://#';
            $custom_text = ' custom text ';


            // WordPress Toolbar
            if ( 'string' === $format ) {
                $return = apply_filters( 'sabai_notification_custom_filter', '<a data-test="abc" href="' . esc_url( $custom_link ) . '" title="' . esc_attr( $custom_title ) . '">' . esc_html( $custom_text ) . '</a>', $custom_text, $custom_link );


                //$return = apply_filters( 'custom_filter', '<a href="' . esc_url( $custom_link ) . '" title="' . esc_attr( $custom_title ) . '">' . esc_html__( '%1$s commented on your idea %2$s', $comment->comment_author, $get_the_title ) . '</a>', $custom_text, $custom_link );
     
            // Deprecated BuddyBar
            } else {
                $return = apply_filters( 'sabai_notification_custom_filter', array(
                    'text' => $custom_text,
                    'link' => $custom_link
                ), $custom_link, (int) $total_items, $custom_text, $custom_title );
            }




            return $return;
            
        }
        
    }
   // add_filter( 'bp_notifications_get_notifications_for_user', 'sabai_custom_idea_buddypress_notifications', 10, 6 );




 ?>