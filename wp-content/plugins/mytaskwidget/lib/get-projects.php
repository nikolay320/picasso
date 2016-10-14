<?php
/** Make sure that the WordPress bootstrap has run before continuing. */
require ( dirname(__FILE__) . '/../../../../wp-load.php' );
global  $wpdb;
$ptype = trim($_POST['ptype']);
$user = wp_get_current_user();
$user_id = isset( $user->ID ) ? (int) $user->ID : 0 ;
$TaskArr = array();
$newPrjList = array();
$newTskList = array();
$ctime = time();
$chkClass = "cpm-uncomplete";
$chkAction = "cpm_task_complete";
$is_checked = "";
if($ptype=="current") {
	$querystr = "SELECT p.ID, p.post_author, p.post_title, p.post_type, tl.ID as l_id, tl.post_author as l_author, tl.post_title as l_title, tl.post_type as l_type, tk.ID as t_id, tk.post_author as t_author, tk.post_title as t_title, tk.post_type as t_type, tk2.`start`, tk2.`due`, GROUP_CONCAT(tk2.user_id) as usr_list
	FROM ".$table_prefix."posts p 
	INNER JOIN ".$table_prefix."posts tl ON p.ID=tl.post_parent 
	INNER JOIN ".$table_prefix."posts tk ON tl.ID=tk.post_parent
	INNER JOIN ".$table_prefix."cpm_project_items pi on (pi.project_id=p.ID AND (pi.item_type='task' OR pi.item_type='cpm_task') AND pi.object_id=tk.ID)
	INNER JOIN ".$table_prefix."cpm_tasks tk2 ON tk2.item_id=pi.id
	WHERE (p.post_type='cpm_project' AND p.post_status='publish') 
	AND (tl.post_type='cpm_task_list' AND tl.post_status='publish') 
	AND (tk.post_type='cpm_task' AND tk.post_status='publish')
	AND (CURDATE() between date(tk2.`start`) and date(tk2.`due`))
	AND pi.complete_status='0'
	AND tk2.user_id='".$user_id."'
	group by t_id ORDER BY p.`ID` ASC";
} else if($ptype=="overdue") {
	$querystr = "SELECT p.ID, p.post_author, p.post_title, p.post_type, tl.ID as l_id, tl.post_author as l_author, tl.post_title as l_title, tl.post_type as l_type, tk.ID as t_id, tk.post_author as t_author, tk.post_title as t_title, tk.post_type as t_type, tk2.`start`, tk2.`due`, GROUP_CONCAT(tk2.user_id) as usr_list
	FROM ".$table_prefix."posts p 
	INNER JOIN ".$table_prefix."posts tl ON p.ID=tl.post_parent 
	INNER JOIN ".$table_prefix."posts tk ON tl.ID=tk.post_parent
	INNER JOIN ".$table_prefix."cpm_project_items pi on (pi.project_id=p.ID AND (pi.item_type='task' OR pi.item_type='cpm_task') AND pi.object_id=tk.ID)
	INNER JOIN ".$table_prefix."cpm_tasks tk2 ON tk2.item_id=pi.id
	WHERE (p.post_type='cpm_project' AND p.post_status='publish') 
	AND (tl.post_type='cpm_task_list' AND tl.post_status='publish') 
	AND (tk.post_type='cpm_task' AND tk.post_status='publish')
	AND CURDATE()>date(tk2.`due`)
	AND pi.complete_status='0'
	AND tk2.user_id='".$user_id."'
	group by t_id ORDER BY p.`ID` ASC";
} else if($ptype=="achieved") {
	$chkClass = "cpm-completed"; $chkAction = "cpm_task_open"; $is_checked="checked";
	$dtfrom = date("Y-m-d", strtotime("-5 days", time()));
	$querystr = "SELECT p.ID, p.post_author, p.post_title, p.post_type, tl.ID as l_id, tl.post_author as l_author, tl.post_title as l_title, tl.post_type as l_type, tk.ID as t_id, tk.post_author as t_author, tk.post_title as t_title, tk.post_type as t_type, tk2.`start`, pi.complete_date as `due`, GROUP_CONCAT(tk2.user_id) as usr_list
	FROM ".$table_prefix."posts p 
	INNER JOIN ".$table_prefix."posts tl ON p.ID=tl.post_parent 
	INNER JOIN ".$table_prefix."posts tk ON tl.ID=tk.post_parent
	INNER JOIN ".$table_prefix."cpm_project_items pi on (pi.project_id=p.ID AND (pi.item_type='task' OR pi.item_type='cpm_task') AND pi.object_id=tk.ID)
	INNER JOIN ".$table_prefix."cpm_tasks tk2 ON tk2.item_id=pi.id
	WHERE (p.post_type='cpm_project' AND p.post_status='publish') 
	AND (tl.post_type='cpm_task_list' AND tl.post_status='publish') 
	AND (tk.post_type='cpm_task' AND tk.post_status='publish')
	AND pi.complete_status='1' AND date(pi.complete_date)>='$dtfrom'
	AND tk2.user_id='".$user_id."'
	group by t_id ORDER BY p.`ID` ASC";
}

echo '<span class="close-wg-box"><i class="fa fa-times-circle redfont"></i></span>';

$projposts = $wpdb->get_results($querystr, OBJECT);
if(count($projposts)>0) {
  foreach($projposts as $proj) {
  	$TaskArr['Project'][$proj->ID] = $proj->post_title;
	$TaskArr['TaskList'][$proj->ID][$proj->l_id] = $proj->l_title;
	$TaskArr['Tasks'][$proj->ID][$proj->l_id][$proj->t_id] = array(
		'title'=>$proj->t_title, 'author'=>$proj->t_author, 'time'=>$proj->due, 'user_list'=>explode(",",$proj->usr_list));
	$ttime = strtotime($proj->start);
	if( ($ctime-$ttime) <= 432000 ) { $newPrjList[$proj->ID]=1; $newTskList[$proj->t_id]=1; }
	$TaskArr['ProjCnt'][$proj->ID]++; 
  }
} else {
  echo '<ul class="sl-cpm-todolists"><li class="sl-sticky_list"><header class="sl-no-records">' . __( 'No Tasks Found', 'wp-mtw-tasks') . '</header></li></ul>';
  exit;
}
if(count($TaskArr['Project'])>0)
{
  echo '<ul class="sl-cpm-todolists">';
  foreach($TaskArr['Project'] as $pid=>$title)
  {
	echo '<li class="sl-sticky_list" data-id="'.$tl_id.'" id="cpm-list-'.$tl_id.'">
	  <header class="sl-cpm-list-header">
		<a href="'.site_url('/').'projets/?project_id='.$pid.'&amp;tab=project&amp;action=index">'.$title.' ('.$TaskArr['ProjCnt'][$pid].')</a>
	  </header>';
	if(count($TaskArr['TaskList'][$pid])>0)
	{
	  foreach($TaskArr['TaskList'][$pid] as $tl_id=>$tl_title)
	  {
	  echo '<div class="sl-sub-title"><a href="'.site_url('/').'projets/?project_id='.$pid.'&amp;tab=task&amp;action=single&amp;list_id='.$tl_id.'">'.$tl_title.'</a></div>
		<ul class="sl-cpm-todos">';
		if(count($TaskArr['Tasks'][$pid][$tl_id])>0)
		{
		  foreach($TaskArr['Tasks'][$pid][$tl_id] as $tk_id=>$tk_data)
		  {
			echo '<li class="sl-cpm-todo-openlist cpm-single-task">';
			if( array_key_exists($tk_id, $newTskList) && $ptype=="current") { 
				echo "<i class='fa fa-exclamation-triangle redfont'></i>&nbsp;";
			}
			echo '<input type="checkbox" name="" value="'.$tk_id.'" data-is_admin="no" data-project="'.$pid.'" data-list="'.$tl_id.'" data-single="" data-action="'.$chkAction.'" class="'.$chkClass.'" '.$is_checked.'>';
            echo '<a href="'.site_url('/').'projets/?project_id='.$pid.'&amp;tab=task&amp;action=todo&amp;list_id='.$tl_id.'&amp;task_id='.$tk_id.'" class="task-title"> '.$tk_data['title'].' </a>
              <span class="cpm-assigned-user">';
			cpm_assigned_user($tk_data['user_list']);
			echo '</span>
              <span class="sl-cpm-'.$ptype.'-date">'.date("M d",strtotime($tk_data['time'])).'</span>
        	</li>';
		  }
		}
		echo '</ul>';
	  }
	}
	echo '</li>';
  }
  echo '</ul>';
} else {
	echo __( 'No Tasks Found', 'wp-mtw-tasks');
}
?>