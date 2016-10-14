<?php 

$post_type = $_POST;

if (empty($post_type) || empty($post_type['noti']) || $post_type['noti'] != 1)
	die();

require_once('../../../wp-load.php');

////start
	$checkTrans = 1;

	if (empty($checkTrans) || $checkTrans !== 1 || empty($post_type['post_id_class']) || empty($post_type['ques_user_link']))
		die();

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

    echo json_encode(array(
        'add_notification' => $add_notification,
        'add_notify_meta'  => $add_notify_meta
    ));  


////end

 ?>