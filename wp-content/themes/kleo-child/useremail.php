<?php

$users= $_POST['users'];
$date= $_POST['date1'];
$url= $_POST['url'];
$post_id=$_POST['post_id'];


if ( ! add_post_meta( $post_id, 'users1', $users, true ) ) { 
   update_post_meta( $post_id, 'users1', $users);
}

if ( ! add_post_meta( $post_id, 'date1', $date1, true ) ) { 
   update_post_meta( $post_id, 'date1', $date1);
}



update_option( 'users1',$users );
update_option( 'date1',$date );
$users1 = explode(",", $users);
$email_from="waqasnabi28@gmail.com";
$url="waqasnabi28@gmail.com";
foreach($users1 as $u)
{

$user = get_user_by( 'login', $u);
  
  // create email headers
 
$headers = 'From: '.$email_from."\r\n".
 
'Reply-To: '.$email_from."\r\n" ;
 
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

$email_message="Hello ".$user->email."  <br/> Below is the link for invitation <br/> ".$url."?user=".$u."<br/>";

 
$issent=@mail("waqasnabi28@gmail.com", $email_subject, $email_message, $headers);  

}

echo "Email has been Sent to the users for invitation";

?>