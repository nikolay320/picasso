<?php
get_header();
global $klc_ideas;
$modifier_can_post_user_review = $klc_ideas['modifier_can_post_user_review'];
?>

<style type="text/css">
    .kleo-navbar-fixed .navbar{z-index: 1002;}
</style>

<?php if(post_type_exists('ideas')): ?>

    <?php $post_meta = get_post_meta(get_the_id()); ?>

    <?php
        if(empty($post_meta['campaign_end_date'][0]) || strtotime($post_meta['campaign_end_date'][0]) < time()){
            $campaign_end = true;
        } else {
            $campaign_end = false;  
        }
    ?>

    <?php if(!$campaign_end): ?>
        <?php add_idea_modal(get_the_id()); ?>
    <?php endif ?>

   <script>
    jQuery(document).ready(function($){
        $('.popup_comment_link').on('click',function(){
            $('#add_comment_form .post_id').val($(this).find('.post_id').val())
            coments_count = $(this).find('.comments_count_number')
        })

        $('.submit-comment-button').on('click',function(){
            var form = new FormData($('#add_comment_form').get(0))
            form.append( 'action', 'add_comment' )
            $.ajax({            
              type: "POST",
              url: '<?php echo admin_url("admin-ajax.php") ?>',
              data: form,
              contentType: false,
              processData: false,
              dataType: "json",
              success: function ( response ) {
                $('#commentModal').modal('hide') 
                $('#add_comment_form').get(0).reset()
                $('.idea_info_block span').html(response.html)
                $('.idea_info_block').show()
                coments_count = coments_count.text(parseInt(coments_count.text())+1)
              }
            })
        })

        $('.campaign_idea_favorite').on('click',function(){
            elem = $(this)
            if(elem.find('.favorite-star').hasClass('fa-star-o')){
                var status = 1
            } else {
                var status = 0
            }
            var data = {
                action: 'idea_favorits',
                status: status,
                post_id: elem.find('.post_id').val()
            };
            jQuery.post( "<?php echo admin_url('admin-ajax.php') ?>" , data, function(response) {
                if(response.success){
                    var favorites_count = elem.find('.favorites_count').text()
                    console.log(favorites_count)
                    if(elem.find('.favorite-star').hasClass('fa-star-o')){
                        elem.find('.favorite-star').removeClass('fa-star-o')
                        elem.find('.favorite-star').addClass('fa-star')
                        elem.find('.favorites_count').text(parseInt(favorites_count)+1)
                    } else {
                        elem.find('.favorite-star').addClass('fa-star-o')
                        elem.find('.favorite-star').removeClass('fa-star')
                        elem.find('.favorites_count').text(favorites_count-1)
                    }
                }
            }, 'json');
        })
    })
    </script>

    <div class="modal fade" id="commentModal" tabindex="-1" role="dialog"  style="display:none; outline: none;">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php _e("Add Comment",IDEAS_TEXT_DOMAIN); ?></h4>
          </div>
          <div class="modal-body">
            <?php if(is_user_logged_in ()): ?>
                <form id="add_comment_form">
                    <div class="form-group">
                        <lavel><?php _e("Comment text",IDEAS_TEXT_DOMAIN); ?></lavel>
                        <textarea name="text" id="comment_text" class="form-control" style="max-width:100%"></textarea>
                        <input type='hidden' name="post_id" class="post_id">
                    </div>
                </form>
            <?php else: ?>
                <div class="form-group">
                    To post a comment please login
                </div>
            <?php endif ?>
          </div>
          <div class="modal-footer" style="margin-top:0">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e("Close",IDEAS_TEXT_DOMAIN); ?></button>
            <?php if(is_user_logged_in ()): ?>
                <button type="button" class="btn btn-primary submit-comment-button"><?php _e("Add",IDEAS_TEXT_DOMAIN); ?></button>
            <?php endif ?>
          </div>
        </div>
      </div>
    </div>
<?php endif ?>

<?php
//Specific class for post listing */
if ( kleo_postmeta_enabled() ) {
    $meta_status = ' with-meta';
    add_filter( 'kleo_main_template_classes', create_function( '$cls','$cls .= "'.$meta_status.'"; return $cls;' ) );
}

/* Related posts logic */
$related = sq_option( 'related_posts', 1 );
if ( ! is_singular('post') ) {
    $related = sq_option( 'related_custom_posts', 0 );
}
//post setting
if(get_cfield( 'related_posts') != '' ) {
    $related = get_cfield( 'related_posts' );
}
?>

<?php get_template_part( 'page-parts/general-title-section' ); ?>

<?php get_template_part( 'page-parts/general-before-wrap' );?>



<?php 
    
    $likes_total = 0;
    $comments_total = 0;
    $ideas_total = 0;

    $cmp_ideas = get_post_meta(get_the_id(),'campaign_ideas');
    $ideas = (!empty($cmp_ideas)) ? get_posts(array('include' =>$cmp_ideas,'post_type'=>'ideas','nopaging' => true)) : null;
  

if(isset($_REQUEST['sort']) && $_REQUEST['sort']=="voted2")
{



/*$ideas = new WP_Query(array(
		'post_status' =>'published',
		'post_type' =>'ideas',
		'post__in'      => $cmp_ideas,
		'orderby' => 'meta_value_num',
		'meta_key' => '_liked',,

	));	*/
	
	$args = array(
	'include' =>$cmp_ideas,
    'post_status'=>'publish',
    'post_type' => 'ideas',
    'meta_query' => array(
		array(
			'key'     => '_liked',
			'compare' => 'NOT EXISTS'
		),
	 )
	);
    
	
 $ideas1 = get_posts($args);

 $ideas = (!empty($cmp_ideas)) ? get_posts(array('posts_per_page'=>-1,'include' =>$cmp_ideas,'post_type'=>'ideas','meta_key'=>'_liked','orderby'=>'meta_value_num','nopaging' => true)) : null;

$ideas = array_merge( $ideas, $ideas1 );


 //$ideas = (!empty($cmp_ideas)) ? get_posts(array('include' =>$cmp_ideas,,'post_type'=>'ideas','nopaging' => true)) : null;
  


   
}
else if(isset($_REQUEST['sort']) && $_REQUEST['sort']=="answers1")
{
   
 $ideas = (!empty($cmp_ideas)) ? get_posts(array('include' =>$cmp_ideas,'post_type'=>'ideas','orderby'=>'comment_count','order'=>'dsc','nopaging' => true)) : null;
 

}

  
    if($ideas){
        foreach ($ideas as $idea) {
            $likes_total = $likes_total+get_post_meta($idea->ID,'_liked',true);
            $comments_total = $comments_total+wp_count_comments($idea->ID)->total_comments;
            $ideas_total++;
        }
    }

?>

<div class="row top_box">
    <div class="col-md-4">
        <div class="row text-center campaign_stats">
            <div class="col-md-4">
                <div><i class="fa fa-lightbulb-o"></i> <?php echo $ideas_total ?></div>
            </div>
            <div class="col-md-4">
                <div><i class="fa fa-comments"></i> <?php echo $comments_total ?></div>
            </div>
            <div class="col-md-4">
                <div><i class="fa fa-thumbs-up"></i> <?php echo $likes_total ?></div>
            </div>
        </div>
        <div class="cmp_add_idea_cont">
            <?php if(!$campaign_end): ?>
                <?php if(post_type_exists('ideas')): ?>
                    <div style="margin-top: 10px;"><?php add_idea_button() ?></div>
                <?php endif ?>
            <?php endif ?>
        </div>
    </div>
    <div class="col-md-8 text-center">
        <?php if(isset($post_meta['campaign_end_date'][0]) && !empty($post_meta['campaign_end_date'][0]) ): ?>
            <?php 
                $end_time = strtotime($post_meta['campaign_end_date'][0]);
                $now_time = time();
                $interval = $end_time - $now_time;
            ?>
            <?php if($interval>0): ?>
                <div class="flipclock"></div>
                <script type="text/javascript">
                    var clock = jQuery('.flipclock').FlipClock(<?php echo $interval ?>,{
                        clockFace: 'DailyCounter',
                        countdown: true
                    });
                </script>  
            <?php else: ?>
                <h2 style="color:#FF9F2F">Campagne terminée</h2>
            <?php endif ?>
        <?php endif ?> 
    </div>
</div>
<hr>
<div class="row single_campaign">
    <div class="col-md-5">
        <?php if(isset($post_meta['campaign_video_url'][0]) && !empty($post_meta['campaign_video_url'][0]) ): ?>
            <?php $youtube_identifier = preg_replace('/https:\/\/youtu.be/', '', $post_meta['campaign_video_url'][0]) ?>
            <iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo $youtube_identifier ?>" frameborder="0" allowfullscreen></iframe>
        <?php elseif(isset($post_meta['campaign_image_url'][0]) && !empty($post_meta['campaign_image_url'][0])): ?>
            <img src="<?php echo $post_meta['campaign_image_url'][0] ?>">
        <?php endif ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <div style="text-align:justify">
                <?php the_content(); ?>
            </div>
        <?php endwhile; ?>
    </div>
    <div class="col-md-7 campaign_ideas">
        <div style="margin-bottom: 20px">
        <?php _e("Sort by",IDEAS_TEXT_DOMAIN); ?>:    
            <a href="?sort=answers1"><?php _e("Most Answers",IDEAS_TEXT_DOMAIN); ?></a> | <a href="?sort=newest"><?php _e("Newest First",IDEAS_TEXT_DOMAIN); ?></a> | <a href="?sort=voted2"><?php _e("Most Votes",IDEAS_TEXT_DOMAIN); ?></a>
        </div>
        <?php if(isset($ideas) && !empty($ideas)): ?>
            <?php foreach ($ideas as $idea): ?>
                <?php if($idea && $idea->post_status=='publish'): ?>
                    <?php $idea_meta = get_post_meta($idea->ID); ?>
                    <div style="padding:10px" class="sabai-entity sabai-entity-type-content sabai-entity-bundle-name-questions sabai-entity-bundle-type-questions sabai-entity-mode-summary sabai-questions-novotes sabai-clearfix">
                        <div class="sabai-row">
                            <div class="sabai-col-xs-3 sabai-questions-side">
                                <?php if($campaign_end): ?>
                                   <!-- <div style="background: #FFFFFF;position: absolute;z-index: 1001;height: 100%;width: 100%;opacity: 0.5;"></div>-->
                                <?php endif ?>   
                               <?php /*?> <?php if(function_exists('kleo_item_likes')): ?>
                                    <div class="sabai-questions-vote-count idea_vote" user_id=<?php echo $idea->post_author; ?>>
                                        <?php $item_votes = get_post_meta($idea->ID,'_item_likes') ?>
                                        <?php kleo_item_likes($idea->ID) ?>            
                                    </div>
                                <?php endif ?><?php */?>
								<!--waqas changes start-->
							  <div class="sabai-questions-vote-count idea_vote whole"  data-ulike-id="<?php echo $idea->ID; ?>"  user_id=<?php the_author_meta( 'ID' ); ?> >
                              <?php if(function_exists('wp_ulike1'))  echo wp_ulike1('get',$idea->ID); ?>         
                             </div>
							 <!--waqas changes end-->
                                <div class="sabai-questions-answer-count">
                                    <a data-toggle="modal" class="popup_comment_link" data-target="#commentModal" style="cursor:pointer">
                                        <?php $comments = get_comments(array('post_id'=>$idea->ID)); ?>
                                        <span class="sabai-number" <?php echo count($comments)?'style="color:#24B129"':'' ?> >
                                            <div class="comments_count_number" style="display:inline"><?php echo count($comments) ?></div>
                                            <i class="fa fa-comments" title="Commenter"></i>
                                            <input type='hidden' class="post_id" value="<?php echo $idea->ID ?>">
                                        </span>
                                    </a>
                                </div>
                                <?php 
                                    $idea_status = get_post_meta($idea->ID, 'idea_status', true);
                                    switch ($idea_status) {
                                        case 'in discussion':
                                            $idea_status_localized_title = __('in discussion', IDEAS_TEXT_DOMAIN);
                                            $idea_status_class = 'btn-info';
                                            break;
                                        case 'selected':
                                            $idea_status_localized_title = __('selected', IDEAS_TEXT_DOMAIN);
                                            $idea_status_class = 'btn-primary';
                                            break;
                                        case 'rejected':
                                            $idea_status_localized_title = __('rejected', IDEAS_TEXT_DOMAIN);
                                            $idea_status_class = 'btn-danger';
                                            break;
                                        case 'in project':
                                            $idea_status_localized_title = __('in project', IDEAS_TEXT_DOMAIN);
                                            $idea_status_class = 'btn-success';
                                            break;
                                        case 'in review':
                                            $idea_status_localized_title = __('in review', IDEAS_TEXT_DOMAIN);
                                            $idea_status_class = 'in-review';
                                            break;
                                        case 'already reviewed':
                                            $idea_status_localized_title = __('already reviewed', IDEAS_TEXT_DOMAIN);
                                            $idea_status_class = 'already-reviewed';
                                            break;
                                        default:
                                            $idea_status_localized_title = '';
                                            $idea_status_class = '';
                                    }
                                ?>
                                <?php if($idea_status): ?>
                                    <div class="text-center <?php echo $idea_status_class ?> status_tag"><?php echo $idea_status_localized_title; ?></div> 
                                <?php endif ?>
                                <div class="text-center">
                                    <?php $views_count = get_post_meta($idea->ID,'views_count')[0] ?>
                                    <span class="sabai-number"><?php echo $views_count?$views_count:0 ?></span> <?php _e("views",IDEAS_TEXT_DOMAIN); ?>
                                    <?php if(is_user_logged_in()): ?>
                                        <span class="campaign_idea_favorite" data-toggle="idea-tooltip" data-placement="top" title="<?php _e('Put in favorites', IDEAS_TEXT_DOMAIN); ?>">
                                            <?php $favorites_meta = get_post_meta($idea->ID,'idea_favorites') ?>
                                            <i class="favorite-star fa-lg fa <?php echo in_array(get_current_user_id(),$favorites_meta)?'fa-star':'fa-star-o' ?>"></i>
                                            <span class="favorites_count">
                                                <?php echo count($favorites_meta) ?>  
                                            </span>  
                                            <input type="hidden" class="post_id" value="<?php echo $idea->ID ?>"> 
                                        </span>
                                    <?php endif ?>  
                                </div>
                            </div>
                            <div class="sabai-col-xs-9 sabai-questions-main">
                                <div class="sabai-questions-title">
                                    <a href="<?php echo site_url().'/Ideas/'.$idea->post_name ?>" style="white-space: normal;" class="sabai-entity-permalink sabai-entity-type-content sabai-entity-bundle-name-questions sabai-entity-bundle-type-questions">
                                        <span class="idea-avatar"><?php echo get_avatar( $idea->post_author, '32' );  ?></span>
                                        <?php echo $idea->post_title ?>
                                    </a>       
                                </div>
                                <div class="sabai-questions-body">
                                    <?php echo wp_trim_words( $idea->post_content, 15, ' [...]' ); ?>   
                                </div>

                                <?php
                                $idea_id = $idea->ID;
                                $enable_expert_reviews = get_post_meta($idea_id, '_idea_enable_expert_reviews', true);
                                $enable_user_reviews = get_post_meta($idea_id, '_idea_enable_user_reviews', true);
                                $enable_idea_updates = get_post_meta($idea_id, '_idea_enable_idea_updates', true);

                                // find experts
                                $idea_experts = klc_get_experts_for_given_idea($idea_id);

                                // current user id
                                $current_user_id = get_current_user_id();

                                // idea status
                                $idea_status = get_post_meta($idea_id, 'idea_status', true);
                                ?>

                                <div class="klc-table idea-info idea-info-in-campaing-post">
                                    <?php
                                    if ($enable_expert_reviews === 'on'):
                                    ?>
                                        <div class="klc-table-cell idea-experts">
                                            <?php
                                            if ($idea_experts) {
                                                echo '<span class="expert-title">' . __('Experts', IDEAS_TEXT_DOMAIN) . ': ' . '</span>';

                                                foreach ($idea_experts as $expert_id) {
                                                    $args = array(
                                                        'post_type'   => 'idea_review',
                                                        'post_status' => 'publish',
                                                        'numberposts' => -1,
                                                        'fields'      => 'ids',
                                                        'meta_query'  => array(
                                                            'relation' => 'AND',
                                                            array(
                                                                'key'     => '_idea_id',
                                                                'value'   => $idea_id,
                                                                'compare' => '=',
                                                            ),
                                                            array(
                                                                'key'     => '_expert_id',
                                                                'value'   => $expert_id,
                                                                'compare' => '=',
                                                            ),
                                                        ),
                                                    );

                                                    $review = get_posts($args);

                                                    echo '<div class="expert-avatar">' . get_avatar($expert_id, '32');

                                                    if ($review && get_post_meta($review[0], '_idea_review', true)) {
                                                        echo '<i class="favorite-star fa-lg fa fa-star"></i>';
                                                    }

                                                    echo '</div>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    <?php endif ?>

                                    <?php if (klc_ideas_modifier() && $enable_expert_reviews === 'on' && $idea_status != 'already reviewed'): ?>
                                        <div class="klc-table-cell idea-modify">
                                            <a class="klc-idea-link" href="<?php echo get_permalink($idea_id); ?>?assign-experts"><?php _e('Assign experts', IDEAS_TEXT_DOMAIN); ?></a>
                                        </div>
                                    <?php endif ?>

                                    <?php if (in_array($current_user_id, $idea_experts) && $enable_expert_reviews === 'on' && $idea_status == 'in review'): ?>
                                        <div class="klc-table-cell idea-expert-review">
                                            <a class="klc-idea-link idea-edit-link" href="<?php echo get_permalink($idea_id); ?>#expert-reviews"><?php _e('Post your expert review now!', IDEAS_TEXT_DOMAIN); ?></a>
                                        </div>
                                    <?php endif ?>

                                    <?php
                                    if (klc_ideas_modifier()) {
                                        if ($modifier_can_post_user_review == '1' && $enable_user_reviews === 'on' && $idea_status == 'in review') {
                                            ?>
                                            <div class="klc-table-cell idea-user-review">
                                                <a class="klc-idea-link idea-edit-link" href="<?php echo get_permalink($idea_id); ?>#user-reviews"><?php _e('Post your review now!', IDEAS_TEXT_DOMAIN); ?></a>
                                            </div>
                                            <?php
                                        }
                                    } elseif (!in_array($current_user_id, $idea_experts) && $enable_user_reviews === 'on' && $idea_status == 'in review') {
                                        ?>
                                        <div class="klc-table-cell idea-user-review">
                                            <a class="klc-idea-link idea-edit-link" href="<?php echo get_permalink($idea_id); ?>#user-reviews"><?php _e('Post your review now!', IDEAS_TEXT_DOMAIN); ?></a>
                                        </div>
                                        <?php
                                    }
                                    ?>

                                    <?php if ($enable_idea_updates === 'on' && klc_check_for_idea_update_owner($idea_id) && $idea_status == 'already reviewed'): ?>
                                        <div class="klc-table-cell idea-user-update">
                                            <a class="klc-idea-link" href="<?php echo get_permalink($idea_id); ?>?post-idea-update"><?php _e('Post idea update', IDEAS_TEXT_DOMAIN); ?></a>
                                        </div>
                                    <?php endif ?>
                                </div>

                                <div class="sabai-questions-activity sabai-questions-activity-inline" style="margin-top:10px">
                                    <ul class="sabai-entity-activity">
                                        <li>
                                            <a href="<?php echo bp_core_get_user_domain( $idea->post_author ); ?>" class="sabai-user sabai-user-with-thumbnail" rel="nofollow" data-popover-url="http://marylink.appteka.cc/sabai/user/profile/jcantenot">
                                                <?php echo bp_core_get_user_displayname($idea->post_author) ?>
                                            </a> 
                                            <?php _e("posted",IDEAS_TEXT_DOMAIN); ?> : 
                                            <span title="<?php $idea->post_date ?>">
                                            <?php echo human_time_diff( strtotime( $idea->post_date), time() ); ?>
                                            </span>
                                        </li>
                                        <?php if($updated_at=get_post_meta($idea->ID,'updated_at',true)): ?>
                                        <li>
                                            <?php _e("updated",IDEAS_TEXT_DOMAIN); ?> : 
                                            <?php echo human_time_diff( strtotime( $updated_at ), time() ); ?>
                                        </li>
                                        <?php endif ?> 
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
                    <hr>
                <?php endif ?>
            <?php endforeach;?>
        <?php endif; ?>
    </div>
</div>



<?php get_template_part('page-parts/general-after-wrap');?>

<?php get_footer(); ?>