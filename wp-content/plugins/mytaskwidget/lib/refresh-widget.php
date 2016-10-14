<?php
require ( dirname(__FILE__) . '/../../../../wp-load.php' );
global $table_prefix, $wpdb;

$user = wp_get_current_user();
$user_id = isset( $user->ID ) ? (int) $user->ID : 0 ;
$newflag = 0; $foundflag=0;
$ndate = date("Y-m-d", strtotime("-5 days", time()));

//$querystr = "SELECT id FROM `".$table_prefix."cpm_tasks` WHERE date(`start`)>'$ndate' AND user_id='$user_id'";
$querystr = "SELECT tk2.id
	FROM ".$table_prefix."cpm_project_items pi
	INNER JOIN ".$table_prefix."cpm_tasks tk2 ON tk2.item_id=pi.id
	WHERE date(`start`)>'$ndate'
	AND (pi.item_type='task' OR pi.item_type='cpm_task')
	AND pi.complete_status='0'
	AND tk2.user_id='".$user_id."'
	group by tk2.id";
$projposts = $wpdb->get_results($querystr, OBJECT);
if(count($projposts)>0) { $newflag=1; }

$querystr = "SELECT tk2.id
	FROM ".$table_prefix."cpm_project_items pi
	INNER JOIN ".$table_prefix."cpm_tasks tk2 ON tk2.item_id=pi.id
	WHERE (CURDATE() between date(tk2.`start`) and date(tk2.`due`))
	AND (pi.item_type='task' OR pi.item_type='cpm_task')
	AND pi.complete_status='0'
	AND tk2.user_id='".$user_id."'
	group by tk2.id";
$projposts = $wpdb->get_results($querystr, OBJECT);
$curTcnt = count($projposts);
$querystr = "SELECT tk2.id
	FROM ".$table_prefix."cpm_project_items pi
	INNER JOIN ".$table_prefix."cpm_tasks tk2 ON tk2.item_id=pi.id
	WHERE CURDATE()>date(tk2.`due`)
	AND (pi.item_type='task' OR pi.item_type='cpm_task')
	AND pi.complete_status='0'
	AND tk2.user_id='".$user_id."'
	group by tk2.id";
$projposts = $wpdb->get_results($querystr, OBJECT);
$ovrTcnt = count($projposts);
$querystr = "SELECT tk2.id
	FROM ".$table_prefix."cpm_project_items pi
	INNER JOIN ".$table_prefix."cpm_tasks tk2 ON tk2.item_id=pi.id
	WHERE (pi.item_type='task' OR pi.item_type='cpm_task')
	AND pi.complete_status='1'
	AND tk2.user_id='".$user_id."'
	AND date(pi.complete_date)>='$ndate'
	group by tk2.id";
$projposts = $wpdb->get_results($querystr, OBJECT);
$arcTcnt = count($projposts);
?>
<span class="hdr-lnk-box"><?php echo __('My Tasks', 'wp-mtw-tasks'); ?></span>
<div class="sl-table-box-2">
  <table align="center"><tr><td align="center">
    <span class="tbl_cont"><?php if($newflag==1) { echo "<i class='fa fa-exclamation-triangle redfont'></i>"; } ?>
    <a href="javascript:;" data-task="current"><?php echo __('Current', 'wp-mtw-tasks'); ?> (<?php echo $curTcnt;?>)</a></span>
    <span class="tbl_cont_sep">|</span>
    <span class="tbl_cont"><a href="javascript:;" data-task="overdue"><?php echo __('Overdue', 'wp-mtw-tasks'); ?> (<?php echo $ovrTcnt;?>)</a></span>
    <span class="tbl_cont_sep">|</span>
    <span class="tbl_cont"><a href="javascript:;" data-task="achieved"><?php echo __('Achieved', 'wp-mtw-tasks'); ?> (<?php echo $arcTcnt;?>)</a></span>
  </td></tr></table>
</div>
<span class="hdr-lnk-box"><a href="<?php echo site_url('/');?>/mes%20taches/" target="_blank"><?php echo __('Details', 'wp-mtw-tasks'); ?></a></span>