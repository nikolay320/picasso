<?php 

$current_id = bp_displayed_user_id();

$query = new WP_Query( array( 'post_type' => 'ideas', 'author' => $current_id ) );


if ( $query->have_posts() ) : ?>

    <div class="col-md-3">
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
                                <a data-toggle="modal" class="popup_comment_link" data-target="#commentModal" style="cursor:pointer">
                                    <?php $user_comments = get_comments(array('author__in'=>array(get_current_user_id()),'post_id'=>get_the_ID())); ?>
                                    <span class="sabai-number" <?php echo count($user_comments)?'style="color:red"':'' ?> >
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
                                <?php $views_count = get_post_meta(get_the_ID(),'views_count')[0] ?>
                                <span class="sabai-number"><?php echo $views_count?$views_count:0 ?></span> <?php __('Views', 'marylink-custom-plugin'); ?>  
                            </div>
                        </div>
                        <div class="sabai-col-xs-10 sabai-questions-main">
                            <div class="sabai-questions-title">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class=" sabai-entity-permalink sabai-entity-id-424 sabai-entity-type-content sabai-entity-bundle-name-questions sabai-entity-bundle-type-questions">
                                    <span class="idea-avatar"><?php echo get_avatar( $author_id, '32' );  ?></span>
                                    <?php include_once('wp-admin/includes/plugin.php'); ?>
                                    <?php if(is_plugin_active('mycred/mycred.php')): ?>
                                        <span class="idea_user_points"><?php echo mycred_get_users_cred( $author_id ) ?> <?php echo mycred()->plural() ?></span>
                                    <?php endif ?>
                                    <?php the_title(); ?>
                                </a>            
                            </div>
                          
                            <div class="sabai-questions-body">
                                  <?php the_excerpt(); ?>  
                            </div>
                            <div class="sabai-questions-custom-fields">
                            </div>

                            <div class="sabai-questions-activity sabai-questions-activity-inline">
                                <ul class="sabai-entity-activity">
                                    <li>
                                        <a href="<?php echo bp_core_get_user_domain( $author_id ); ?>" class="sabai-user sabai-user-with-thumbnail" rel="nofollow" data-popover-url="http://marylink.appteka.cc/sabai/user/profile/jcantenot">
                                            <?php echo bp_core_get_user_displayname($author_id) ?>
                                        </a> 
                                        posted 
                                        <span title="<?php get_the_date(); ?>">
                                        <?php echo human_time_diff( strtotime( get_the_date('Y-m-d H:i:s')), time() ); ?>
                                        ago
                                        </span>
                                    </li>
                                    <li>
                                        <i class="fa fa-clock-o"></i>
                                        last active 
                                        <?php echo human_time_diff( strtotime( get_user_meta( $author_id, 'last_activity', true )), time() ); ?>
                                        ago
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
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>

        </div>
    </div>


    <?php

endif;
?>