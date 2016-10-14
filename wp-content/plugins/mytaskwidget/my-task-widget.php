<?php
/*
  Plugin Name: My Task Widget
  Plugin URI: http://starlinewebsolutions.com/
  Description: My Task Widget Plugin is used for view Widget in anywhere on site with Ajax Response to view Specific Task summary with able to redirect any Project, Task List or perticular Task, as well able to mark as complete/achieve from direct widget. Shortcode: [MTW_TASKS]
  Version: 1.0
  Author: SL
  Author URI: http://starlinewebsolutions.com/
  Text Domain: wp-mtw-tasks
  Domain Path: /i18n/languages/
*/

class SL_MyTaskWidget{

  	// Constructor
    function __construct() {
		add_action('init', array($this, 'load_my_transl'));
    }
	
	public function load_my_transl()
    {
        load_plugin_textdomain('wp-mtw-tasks', FALSE, dirname(plugin_basename(__FILE__)).'/i18n/languages/');
    }

	// View of Initial Box
	function sl_show_task_types() {
	  	global $table_prefix, $wpdb;

		echo "<script> var js_site_url = '".site_url('/')."'; </script>";
	    wp_enqueue_style( 'sl-mtw-style', plugins_url('/css/mtw_css.css', __FILE__));
	    wp_enqueue_script( 'sl-mtw-script', plugins_url('/js/load-tasks.js', __FILE__ ));

	  	$user = wp_get_current_user();
		$user_id = isset( $user->ID ) ? (int) $user->ID : 0 ;
	  	$newflag = 0; $foundflag=0;
	    $ndate = date("Y-m-d", strtotime("-5 days", time()));
		
		$querystr = "SELECT tk2.id
			FROM ".$table_prefix."cpm_project_items pi
			INNER JOIN ".$table_prefix."cpm_tasks tk2 ON tk2.item_id=pi.id
			WHERE (pi.item_type='task' OR pi.item_type='cpm_task')
			AND tk2.user_id='".$user_id."'
			AND (pi.complete_status='0' OR (pi.complete_status='1' AND date(pi.complete_date)>='$ndate'))";
		$projposts = $wpdb->get_results($querystr, OBJECT);
		if(count($projposts)>0) { $foundflag=1; }

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
      <?php if($foundflag==1) { ?>
      <div class="sl_link_box">
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
      </div>
      <div id="SL_PRJ_LIST"></div>
      <?php }
	}
}

$mtw_Obj = new SL_MyTaskWidget();
add_filter('widget_text', 'do_shortcode'); 
add_shortcode( 'MTW_TASKS', array('SL_MyTaskWidget', 'sl_show_task_types') );

?>