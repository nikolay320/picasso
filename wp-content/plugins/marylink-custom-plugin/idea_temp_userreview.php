<?php 

$current_id = bp_displayed_user_id();
//$type = ($type === 'expert') ? '_expert_id' : '_user_id';
$type = '_user_id';
$user_id = $current_id;


		$args = array(
			'post_type'      => 'idea_review',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		$meta_query = array(
			'relation' => 'AND',
			array(
			    'key'     => $type,
			    'value'   => $user_id,
			    'compare' => '=',
			),
			array(
			    'key'     => $type,
			    'compare' => 'EXIST',
			),
		);

		$args['meta_query'] = $meta_query;

		$review = get_posts($args);
		//write_log($review);
		//write_log($args);
		$idea_array = array();
			array_push($idea_array, 1);
		foreach ( $review as $id ) {
			$idea_id = get_post_meta($id, '_idea_id', true);
			array_push($idea_array, $idea_id);
		}
		//write_log($idea_array);
		
		
$query = new WP_Query( array( 'post_type' => 'idea', 'post__in' => $idea_array ) );
		//write_log($query);
		
if ( $query->have_posts() ) : ?>

    <div class="sabai-row" style="padding: 0px;">
     <div class="idea_search">
            <select class="idea_sort" onchange="window.location.href='?sort='+jQuery('.idea_sort option:selected').val()">
                <option value="newest" <?php echo $_GET['sort']=='newest'?'selected':''?> ><?php _e('Newest First', 'marylink-custom-plugin'); ?></option>
                <option value="oldest" <?php echo $_GET['sort']=='oldest'?'selected':''?> ><?php _e('Oldest First', 'marylink-custom-plugin'); ?></option>
                <option value="recent" <?php echo $_GET['sort']=='recent'?'selected':''?> ><?php _e('Recently Active', 'marylink-custom-plugin'); ?></option>
                <option value="voted" <?php echo $_GET['sort']=='voted'?'selected':''?> ><?php _e('Most Votes', 'marylink-custom-plugin'); ?></option>
                <option value="answers" <?php echo $_GET['sort']=='answers'?'selected':''?> ><?php _e('Most Answers', 'marylink-custom-plugin'); ?></option>
            </select>
        </div>
    </div>

    <div class="sabai-row ideas-list">
        <div class="sabai-questions-questions sabai-col-md-12">

            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
			
<?php 
$idea_id = get_the_ID();

global $picasso_ideas;
$current_user_id = get_current_user_id();
$author_id = get_the_author_meta('ID');
$post_id = $idea_id = get_the_ID();
$edit_link = get_the_permalink($picasso_ideas['idea_edit_page']) . '?id=' . $post_id;

// Campaign
$campaign_id = get_post_meta($post_id, '_idea_campaign', true);

if ($campaign_id) {
	$campaign = get_post($campaign_id);
	$campaign_criteria = get_post_meta($campaign_id, '_campaign_criteria', true);

	// check if votes is enabled for no-status
	$enable_votes_for_no_status = get_post_meta($campaign_id, '_campaign_enable_votes_for_no_status', true);
} else {
	$campaign = array();
	$campaign_criteria = array();
	$enable_votes_for_no_status = '';
}

// review criteria
if ($campaign_criteria) {
	$review_criteria = $campaign_criteria;
} elseif (key_exists('review_criteria', $picasso_ideas) && $picasso_ideas['review_criteria']) {
	$review_criteria = $picasso_ideas['review_criteria'];
} else {
	$review_criteria = array();
}

// expert reviews and average ratings
$expert_reviews_and_average_ratings = pi_get_reviews_and_average_rating($idea_id, 'expert', $review_criteria);
$expert_review_found = $expert_reviews_and_average_ratings['review_found'];

// user reviews and average ratings
$user_reviews_and_average_ratings = pi_get_reviews_and_average_rating($idea_id, 'user', $review_criteria);
$user_review_found = $user_reviews_and_average_ratings['review_found'];

// idea updates
$idea_updates = pi_get_idea_updates($post_id);

// idea status
$idea_status = get_post_meta($post_id, '_idea_status', true);


$enable_expert_reviews = get_post_meta($idea_id, '_idea_enable_expert_reviews', true);
$enable_user_reviews = get_post_meta($idea_id, '_idea_enable_user_reviews', true);
$enable_idea_updates = get_post_meta($idea_id, '_idea_enable_idea_updates', true);

?>

                <?php $author_id = get_the_author_meta('ID'); ?>

                <div style="padding:10px" class="sabai-entity sabai-entity-type-content sabai-entity-bundle-name-questions sabai-entity-bundle-type-questions sabai-entity-mode-summary sabai-questions-novotes sabai-clearfix">
                    <div class="sabai-row">
                        <div class="sabai-col-xs-2 sabai-questions-side">
                            <?php if(function_exists('kleo_item_likes')): ?>
                            <div class="sabai-questions-vote-count idea_vote" user_id=<?php the_author_meta( 'ID' ); ?>>
                                <?php $item_votes = get_post_meta(get_the_ID(),'_item_likes') ?>  
                                    <?php kleo_item_likes(get_the_ID()) ?>        
                                </div>
                            <?php endif ?>
                            <div class="sabai-questions-answer-count">
                                <a data-toggle="modal" href="<?php echo the_permalink()?>#comments" class="popup_comment_link" data-target="#commentModal" style="cursor:pointer">
                                    <?php $user_comments = get_comments(array('author__in'=>array(get_current_user_id()),'post_id'=>get_the_ID())); ?>
                                    <span style="color:grey;" class="sabai-number" <?php echo count($user_comments)?'style="color:red"':'' ?> >
                                        <div class="comments_count_number" style="display:inline"><?php echo get_comments_number() ?></div>
                                        <i class="fa fa-comments"></i>
                                        <input type='hidden' class="post_id" value="<?php echo get_the_ID() ?>">
                                    </span>
                                </a>
                            </div>
                            <?php 
                                $idea_status = get_post_meta($post->ID, 'idea_status', true);
                                switch ($idea_status) {
                                    case 'in discussion':
                                        $idea_status_class = 'btn-info';
                                        break;
                                    case 'selected':
                                        $idea_status_class = 'btn-primary';
                                        break;
                                    case 'rejected':
                                        $idea_status_class = 'btn-danger';
                                        break;
                                    case 'in project':
                                        $idea_status_class = 'btn-success';
                                        break;
                                    default:
                                        $idea_status_class = '';
                                } 
                            ?>
                            <?php if($idea_status): ?>
                                <div class="text-center <?php echo $idea_status_class ?> status_tag"><?php echo $idea_status ?></div> 
                            <?php endif ?>
                            <div class="sabai-questions-view-count">
                                <?php $views_count = get_post_meta(get_the_ID(),'_views_count')[0] ?>
                                <span class="sabai-number"><?php echo $views_count?$views_count:0 ?></span> <?php _e('Views', 'marylink-custom-plugin'); ?>  
                            </div>
                        </div>
                        <div class="sabai-col-xs-10 sabai-questions-main">
                            <div class="sabai-questions-title">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class=" sabai-entity-permalink sabai-entity-id-424 sabai-entity-type-content sabai-entity-bundle-name-questions sabai-entity-bundle-type-questions">
                                    <span class="idea-avatar"><?php echo get_avatar( $author_id, '32' );  ?></span>
                                    <?php include_once('wp-admin/includes/plugin.php'); ?>
                                    <?php if(is_plugin_active('mycred/mycred.php')): ?>
                                        <span class="idea_user_points"><?php //echo mycred_get_users_cred( $author_id ) ?> <?php //echo mycred()->plural() ?></span>
                                    <?php endif ?>
                                    <?php the_title(); ?>
                                </a>            
                            </div>
                          
                            <div class="sabai-questions-body">
                                  <?php 



                                  //the_excerpt();
                                	  $excerpt = get_the_excerpt();
                                 	$str_excerpt = str_split($excerpt);
                                 	$count_str = count($str_excerpt);

                                 	if ((int) $str_excerpt[$count_str-1] === 0 && $str_excerpt[$count_str-2] == ' ')
                                 		$str_excerpt[$count_str-1] = ' ';

                                 	//var_dump($str_excerpt);

                                 	$resurrection = implode("", $str_excerpt);

                                 	echo $resurrection;

                                   ?>  
                            </div>
                            <div class="sabai-questions-custom-fields">
                            </div>
		
		
                            <div class="sabai-questions-activity sabai-questions-activity-inline">
                                <ul class="sabai-entity-activity">
                                    <li>
                                        <a href="<?php echo bp_core_get_user_domain( $author_id ); ?>" class="sabai-user sabai-user-with-thumbnail" rel="nofollow" data-popover-url="http://marylink.appteka.cc/sabai/user/profile/jcantenot">
                                            <?php echo bp_core_get_user_displayname($author_id) ?>
                                        </a> 
                                        <?php _e('posted', 'marylink-custom-plugin'); ?> 
                                        <span title="<?php get_the_date(); ?>">
                                        <?php echo human_time_diff( strtotime( get_the_date('Y-m-d H:i:s')), time() ); ?>
                                        <?php _e('ago', 'marylink-custom-plugin'); ?> 
                                        </span>
                                    </li>
                                    <li>
                                        <i class="fa fa-clock-o"></i>
                                        <?php _e('last active', 'marylink-custom-plugin'); ?> 
                                        <?php echo human_time_diff( strtotime( get_user_meta( $author_id, 'last_activity', true )), time() ); ?>
                                        <?php _e('ago', 'marylink-custom-plugin'); ?> 
                                    </li>
                                </ul>
                                <div class="pull-right">
                                    <?php $tags = wp_get_post_tags( get_the_ID()) ?>
                                    <?php if(count($tags)):?>
                                        <?php foreach($tags as $tag):?>
                                            <div class="idea_tag"><?php echo $tag->name ?></div>
                                        <?php endforeach ?>
                                    <?php endif ?>
                                </div>                                          
                            </div>
							
							
		<?php 
		$idea_status = get_post_meta($post_id, '_idea_status', true);
		if ($enable_user_reviews === 'on' && ($idea_status == 'review' || $idea_status == 'in-project')): ?>
            <div role="tabpanel" class="tab-pane" id="user-reviews">
                <?php
                $params = array(
                    'review_found'             => $user_reviews_and_average_ratings['review_found'],
                    'average_in_each_criteria' => $user_reviews_and_average_ratings['average_in_each_criteria'],
                    'average'                  => $user_reviews_and_average_ratings['average'],
                    'reviews'                  => $user_reviews_and_average_ratings['reviews'],
                    'idea_id'                  => $idea_id,
                    'campaign_id'              => $campaign_id,
                    'idea_status'              => $idea_status,
                    'idea_experts'             => $idea_experts,
                    'review_type'              => 'user',
                    'current_user_id'          => $current_user_id,
                    'review_criteria'          => $review_criteria,
                );

                pi_render_template(IDEAS_TEMPLATE_PATH . 'partials/tabs/idea-reviews.php', $params);
                ?>
            </div>
        <?php endif ?>

		
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>

        </div>
    </div>


    <?php

endif;
?>