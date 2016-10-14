<?php
/**
 * Plugin Name: Frontend Wp Project Manager pro
 Plugin URI: http://www.wolopress.com/
 * Description: Custom plugin for frontend task and discussion submit
 * Version: 1.0
 * Author: Mansur Ahamed
 * Author URI: http://www.wolopress.com/
 */
define(FRONTEND_PROJECT_MANAGER, plugin_dir_url(__FILE__));
add_shortcode("cpm_add_task","cpm_task_new_form_call");
add_shortcode("cpm_add_discussion","cpm_add_discussion_form");
add_action( 'wp_ajax_load_cpm_task_by_project',  'load_cpm_task_by_projectbyID' );
add_action( 'wp_ajax_nopriv_load_cpm_task_by_project',  'load_cpm_task_by_projectbyID' );
add_action( 'wp_ajax_nopriv_cpm_task_add', array( 'CPM_Ajax', 'add_new_task' ) );
add_action( 'wp_ajax_load_cpm_add_discussion',  'cpm_add_discussion_form' );
add_action( 'wp_ajax_nopriv_load_cpm_add_discussion',  'cpm_add_discussion_form' );

function cpm_add_discussion_form(){
	if(isset($_GET['project_id']) && isset($_GET['project_action'])){
		
	?>
    <script language="javascript">
	jQuery(document).ready(function(){
			jQuery( "#cpm-discussion-dialog" ).dialog();

	});
	</script>
		<?php } // If url postid variable available?>
        	                <style>
#cpm_discussion_sht select, #cpm_discussion_sht div input#message_title{
	width:250px ;
	height:28px ;
	margin-bottom:8px;
}
#cpm_discussion_sht h2{
	font-size:18px;
}

/*Change Style*/
#cpm_discussion_sht a.message-cancel, 
#cpm_discussion_sht div.milestone, 
#cpm_discussion_sht div.cpm-make-privacy, 
#cpm_discussion_sht div.notify-users,
#cpm_task_sht a.message-cancel, 
#cpm_task_sht div.milestone, 
#cpm_task_sht div.cpm-make-privacy, 
#cpm_task_sht div.notify-users {
	display: none;
}

#cpmdiscussion_form{margin-top:8px;}
#cpmdiscussion_form div input#message_title{
	border:solid 1px #a9a9a9 !important;
}
</style>	
            <div id="cpm_discussion_sht">
          <select id="all_project_select_discussion" >
          <?php
		$newProject = new CPM_Project();
		$posts = $newProject->get_projects();
		$firstPostID;$count = 0;
		foreach($posts as $post){
			if($post->post_title){
				if(!$count):
					$firstPostID = $post->ID;
				endif;
				$count++;
			if(isset($_GET['project_id']) && (intval($_GET['project_id']) == $post->ID)):
				$selected = "selected";
				$firstPostID = intval($_GET['project_id']); //Get project ID from url
				else:
				$selected = "";
			endif;	
			echo "<option value='".$post->ID."' $selected>";
			esc_attr_e( $post->post_title);
			echo "</option>";
			}
			
		}
 ?>   
          </select> <?php esc_attr_e( 'Sélectionnez projet'); ?>
            <div id="cpmdiscussion_form">
        <?php echo cpm_discussion_form($firstPostID); ?>
	<div id="ajaxmsg">
    </div>
	</div>
	</div>
    <script language="javascript">
    	jQuery( document ).ready(function() {
			jQuery("#cpm_discussion_sht #create_message").click(function(){
				//jQuery("#milestone").remove();
				if(!jQuery("#all_project_select_discussion").val()) 
					return false;

				//jQuery(this).parents("#cpm-discussion-dialog").dialog('close');
				jQuery.notify({
					// options
					message: "Discussion Ajouté avec succès"
				},{
					// settings
					type: 'danger'
				});  
			});
			
			jQuery( "#all_project_select_discussion").change(function() { 
				jQuery('#cpm_discussion_sht input[name="project_id"]').val(jQuery( "#all_project_select_discussion").val());
			});
    	});
	</script>
    <?php
}//EOF


function cpm_task_new_form_call(){
		 ?>
        <style>
		[data-notify="progressbar"] {
	margin-bottom: 0px;
	position: absolute;
	bottom: 0px;
	left: 0px;
	width: 100%;
	height: 5px;
}
#cpm_task_sht select{
	width:250px;
	height:28px;
}

#cpm_task_sht div input[type="text"]{
	border:solid 1px #a9a9a9;
	margin-bottom:8px;
}
#cpm_task_sht div input.date_picker_to{
		width:90%;
}
.datepickershow{ float:right; margin-top:-20px;}
a.todo-cancel{
	display:none ;
}
#cpm_task_sht div.chosen-container ul li input
{
	min-height:30px !important;
	font-size:14px !important;
}
</style>
		<!-- Mansur Custom Select-->
        <div id="cpm_task_sht">
          <div style="margin-bottom:8px;">
          <select id="all_project_select" name="allProjects">
          <?php
		$newProject = new CPM_Project();
		$newTask = new CPM_Task();
		$posts = $newProject->get_projects();
		$firsttaskID;$firstProjectID;$projectArray;
		$count = 0;
		foreach($posts as $post):
			$taskList = $newTask->get_task_lists($post->ID);
			if($post->post_title && $taskList){
			$projectArray[] = $taskList;  //Getting only projects that has task list available
			if(!$count):
				$firstProjectID = $post->ID;
				foreach ($taskList as $taskID):
					if(!$count):
						$firsttaskID = $taskID->ID;
					endif;
					$count++;
				endforeach;
			endif;
			echo "<option value='".$post->ID."'>";
			esc_attr_e($post->post_title);
			echo "</option>";
			}	
		endforeach;
			 ?>   
          </select> <?php esc_attr_e( 'Select Project'); ?>
         	
        </div>
       
        <div style="margin-bottom:8px;" id="subtaskList"></div>
         <?php cpm_task_new_form($firsttaskID ,$firstProjectID); ?>
         </div>
           <script type='text/javascript' src='<?php echo FRONTEND_PROJECT_MANAGER.'bootstrap-notify.js';?>'></script>
           <script type='text/javascript' src='<?php echo FRONTEND_PROJECT_MANAGER.'datepicker.js';?>'></script>
        	<link rel="stylesheet" type="text/css" href="<?php echo FRONTEND_PROJECT_MANAGER.'datepicker.css';?>">
			<link rel="stylesheet" type="text/css" href="<?php echo FRONTEND_PROJECT_MANAGER.'animate.css';?>">
        
        <?php 
		global $countthisScript;
?>  <script language="javascript">
/********************************Load of task with ajax************************************/
		jQuery( document ).ready(function() {
			getTaskByProjectID();
			jQuery('.date_picker_to').addClass('datepicker2');

			jQuery('body').on('click', '#cpm_task_sht input[name="submit_todo"]', function() {
				if(!jQuery('#cpm_task_sht .task_title').val()){
					return false;
				}
				jQuery(this).parents("#cpm-task-dialog").dialog('close');
				jQuery.notify({
					// options
					message: "Tâche Ajouté avec succès"
				},{
					// settings
					type: 'danger'
				});  
				var data = jQuery('#cpm_task_sht .cpm-task-form').serialize();
				var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
	 			jQuery.post(ajaxurl, data, function (res) {
	            	res = JSON.parse(res);
		 		});
				return false;
			});

			jQuery( "#cpm_task_sht #all_project_select").change(function() { 
				getTaskByProjectID(jQuery( "select#subtaskList option:selected").val());
				jQuery('#cpm_task_sht input[name="project_id"]').val(jQuery( "#cpm_task_sht  #all_project_select option:selected").val());
				jQuery('#cpm_task_sht input[name="list_id"]').val(jQuery( "select#subtaskList option:selected").val());
			});

			jQuery( "select#subtaskList").change(function() { 
				jQuery('#cpm_task_sht input[name="project_id"]').val(jQuery( "#cpm_task_sht  #all_project_select option:selected").val());
				jQuery('#cpm_task_sht input[name="list_id"]').val(jQuery( "select#subtaskList option:selected").val());
			});

			function getTaskByProjectID(currentTaskList){
				var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
				var projectID = jQuery( "#all_project_select option:selected").val();
				// This does the ajax request
				jQuery.ajax({
		    		url: ajaxurl,
		    		data: {
		        		'action' : 'load_cpm_task_by_project',
		        		'projectID' : projectID,
						'taskID' : currentTaskList
		    		},
		    		success:function(data) { //Showing notification that data saved
						jQuery('#subtaskList').html(data);
		  		  	},
			    	error: function(errorThrown){
				        console.log(errorThrown);
			    	}
				});
			}
		});
		
		</script>
        <div id="dynamicTaskList">
        </div>
    </div>
<!-- Mansur Custom Ajax call-->
<?php  //Endif	
} //EOF

function load_cpm_task_by_projectbyID(){
	$newTask = new CPM_Task();
	$taskList = $newTask->get_task_lists($_REQUEST['projectID']);
	?>
    
    	<select id="subtaskList">
        <?php
		$firstTask;$count = 0;
		foreach($taskList as $task):
		if(!$count):
			$firstTask = $task->ID;
		endif;
		$count++;
		echo '<option value="'.$task->ID.'">'.$task->post_title.'</option>';
		endforeach;
		?>
        </select>
        <script language="javascript">
		jQuery('input[name="list_id"]').val("<?php echo $firstTask; ?>");
		</script>
         <?php esc_attr_e( 'Sélectionnez Liste des tâches' ); 
		 
		 
		 
		 //Updating users
		 $users = CPM_Project::getInstance()->get_users( intval($_REQUEST['projectID']) ); //Dynamic user change on project ID
    if ( $users ) {
		echo '<script language="javascript">';
		echo 'jQuery("#task_assign").empty();'; 
		foreach ( $users as $user ) {

            if ( is_array( $selected ) ) {
                $selectd_status = in_array( $user['id'], $selected ) ? 'selected="selected"' : '';
            } else {
                $selectd_status = selected( $selected, $user['id'], false );
            }?>
		jQuery("#task_assign").append(jQuery("<option <?php echo $selectd_status;?>></option>").val(<?php echo $user['id']; ?>).html("<?php echo $user['name'];?>"));
			
             <?php
        }
		echo 'jQuery("#task_assign").trigger("chosen:updated")';
		echo '</script>';
	}
	
	?>
         
    <?php
	die();
	
}