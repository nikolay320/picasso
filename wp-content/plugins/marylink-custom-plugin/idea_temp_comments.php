<?php 

global $current_user;
      get_currentuserinfo();

//$current_id = get_current_user_id();
$current_id = bp_displayed_user_id();
$comments_query = new WP_Comment_Query;

$args = array( 'post_type' => 'idea', 'user_id' => $current_id );

//var_dump($args);

if (!empty($_GET['sort_comment'])) {

			switch ($_GET['sort_comment']) {
			case 'newest':
				//$wp_query->set('orderby', 'date');
				$args['orderby'] = 'date';
				break;
			case 'oldest':
				//$wp_query->set('oldest', 'date');
				//$wp_query->set('order', 'ASC');
				$args['orderby'] = 'date';
				$args['order'] = 'ASC';
				break;
		}
}

//var_dump($args);


$comments = $comments_query->query( $args );

//var_dump($comments);

 ?>


<?php if ( $comments ) { ?>
    <div class="sabai-row" style="padding: 0px;">
        <div class="idea_search">
            <select class="idea_sort" onchange="window.location.href='?sort_comment='+jQuery('.idea_sort option:selected').val()">
                <option value="newest" <?php echo $_GET['sort_comment']=='newest'?'selected':''?> >
                <?php _e('Newest First', 'marylink-custom-plugin'); ?>
                </option>
                <option value="oldest" <?php echo $_GET['sort_comment']=='oldest'?'selected':''?> ><?php _e('Oldest First', 'marylink-custom-plugin'); ?></option>
            
            </select>
        </div>
    </div>

<?php } ?>

    <div class="sabai-row ideas-list">
        <div class="sabai-questions-questions sabai-col-md-12">

        <?php 

			if ( $comments ) {

				foreach ( $comments as $comment ) {

					?>

<div class="sabai-questions-answers">
 
   <div style="padding:10px" class="sabai-entity sabai-entity-type-content sabai-entity-bundle-name-questions-answers sabai-entity-bundle-type-questions-answers sabai-entity-mode-summary sabai-questions-accepted sabai-clearfix" id="sabai-entity-content-107">
    <div class="sabai-row">
        <div class="sabai-col-xs-2 sabai-questions-side">
            <div class="sabai-questions-vote-count">
                <span style="color:grey !important;" class="sabai-number">
				<?php echo wp_count_comments($comment->comment_post_ID)->total_comments; ?> 
				<i  style="color:grey;" class="fa fa-comments"></i>
				</span> 
				<?php __('comments', 'marylink-custom-plugin'); ?>            
			</div>
			
            <div class="sabai-questions-view-count">
                <?php $views_count = get_post_meta($comment->comment_post_ID,'_views_count')[0] ?>
                <span class="sabai-number"><?php echo $views_count?$views_count:0 ?></span> <?php _e('Views', 'marylink-custom-plugin'); ?>  
            </div>
			
        </div>
        <div class="sabai-col-xs-10 sabai-questions-main">
            <div class="sabai-questions-title">
                <i title="This answer has been accepted." class="sabai-entity-icon-questions-resolved fa fa-check-circle"></i> 
				<?php _e('In reply to: ', 'marylink-custom-plugin'); ?> 
				<a class=" sabai-entity-permalink sabai-entity-id-107 sabai-entity-type-content sabai-entity-bundle-name-questions-answers sabai-entity-bundle-type-questions-answers" title="Comment réduire les photos &quot;couvertures&quot; de profil ?" href="<?php echo get_permalink($comment->comment_post_ID); ?>">
				<?php echo get_the_title($comment->comment_post_ID);?>
				</a>            
			</div>
            <div class="sabai-questions-body">
                <p><?php echo $comment->comment_content; ?></p>
            </div>
            <div class="sabai-questions-activity sabai-questions-activity-inline">
                <ul class="sabai-entity-activity"><li><a data-popover-url="http://localhost/all/mary/sabai/user/profile/startops5" rel="nofollow" style="background:left center url(<?php echo bp_core_fetch_avatar ( array( 'item_id' => $current_id, 'type' => 'thumb', 'html' => 'false' ) ); ?>) no-repeat transparent; height:24px; padding-left:29px; display:inline-block; background-size:24px 24px" class="sabai-user sabai-user-with-thumbnail" href="<?php echo site_url();?>/members/<?php echo $current_user->user_login; ?>/"><?php echo $current_user->user_firstname ." ".$current_user->user_lastname; ?></a> 
				<?php _e('time ', 'marylink-custom-plugin'); ?>  
				<span title="<?php echo $comment->comment_date; ?>"><?php echo $comment->comment_date; ?></span>
				</li>
				</ul>            
				</div>
        </div>
    </div>
</div>  

 </div>


					<?php

					//echo '<p>' . $comment->comment_content .' on <a href=\''.get_permalink($comment->comment_post_ID). '\'>'.get_the_title($comment->comment_post_ID).'</a></p>';
					//echo " date ".$comment->comment_date;
				    //echo 'User first name: ' . $current_user->user_firstname ." ".$current_user->user_lastname. "\n";
				    //echo '/members/' . bp_core_get_username( get_current_user_id() );
					//echo bp_core_fetch_avatar ( array( 'item_id' => $current_id, 'type' => 'thumb', 'html' => 'false' ) );
				}
			} else {
                    __('No comments found', 'marylink-custom-plugin');           
			}


         ?>

        </div>
    </div>


