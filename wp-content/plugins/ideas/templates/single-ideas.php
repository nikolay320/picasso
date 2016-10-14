<?php get_header(); ?>

<?php
// expert rating chart modal
global $klc_ideas;
$review_criteria = $klc_ideas['review_criteria'] ? $klc_ideas['review_criteria'] : array();
$modifier_can_post_user_review = $klc_ideas['modifier_can_post_user_review'];

// idea id
$idea_id = get_the_ID();

// expert reviews and average ratings
$expert_reviews_and_average_ratings = klc_get_reviews_and_average_rating($idea_id, 'expert');
$expert_review_found = $expert_reviews_and_average_ratings['review_found'];

// user reviews and average ratings
$user_reviews_and_average_ratings = klc_get_reviews_and_average_rating($idea_id, 'user');

if ($expert_review_found) {
    $radar_chart_labels = implode('","', $review_criteria);
    $radar_chart_label = __('Average', IDEAS_TEXT_DOMAIN);
    $radar_chart_data = implode(',', $expert_reviews_and_average_ratings['average_in_each_criteria']);

    $data_expert_ratings = klc_count_reviews('expert');
    $bar_chart_data = implode(',', $data_expert_ratings);
    $bar_chart_label = __('Ratings', IDEAS_TEXT_DOMAIN);

    klc_render_template('expert-rating-chart');
}

// idea updates
$idea_updates = klc_get_idea_updates(get_the_ID());

$current_user_id = get_current_user_id();

// load scripts and styles
wp_enqueue_script('jquery.stickytabs');
wp_enqueue_script('bootstrap_notify-js');
wp_enqueue_style('jquery_raty-styles');
wp_enqueue_script('jquery_raty-js');
?>

<script type="text/javascript">
    jQuery(document).ready(function($){
        $('.idea-tabs').stickyTabs();

        // open comments tab
        if ($('idea-tabs').length) {
            $('.sabai-questions-answer-count').click(function(event) {
                // event.preventDefault();
                $('.idea-tabs a[href="#idea-comments"]').tab('show');
            });

            // open idea-details tab
            $('.go-to-idea-detials').click(function(event) {
                // event.preventDefault();
                $('.idea-tabs a[href="#idea-details"]').tab('show');
            });
        }

        $('.idea_favorite').on('click',function(){
            if($('.favorite-star').hasClass('fa-star-o')){
                var status = 1
            } else {
                var status = 0
            }
            var data = {
                action: 'idea_favorits',
                status: status,
                post_id: $('#post_id').val()
            };
            jQuery.post( "<?php echo admin_url('admin-ajax.php') ?>" , data, function(response) {
                if(response.success){
                    var favorites_count = $('.favorites_count').text()
                    if($('.favorite-star').hasClass('fa-star-o')){
                        $('.favorite-star').removeClass('fa-star-o')
                        $('.favorite-star').addClass('fa-star')
                        $('.favorites_count').text(parseInt(favorites_count)+1)
                    } else {
                        $('.favorite-star').addClass('fa-star-o')
                        $('.favorite-star').removeClass('fa-star')
                        $('.favorites_count').text(favorites_count-1)
                    }
                }
            }, 'json');
        });

        var comment_id, comment_text;
        
        $('.comment_popup').on('click',function(){
            $('#comment_modal').modal('show');
            comment_id = $(this).data('commentid');
            comment_text = $(".comment_text_"+comment_id).html();
            tinyMCE.get("editor_comment").setContent(comment_text);
        });

        $('.edit-comment-button').on('click',function(){
           $.ajax({
                type: "POST",
                url: '<?php echo admin_url("admin-ajax.php") ?>',
                data: {
                    action: 'edit_comments',
                    comment_id: comment_id,
                    comment_text: tinyMCE.get("editor_comment").getContent()
                },
                success: function() {
                    $('#comment_modal').modal("hide");
                    $(".comment_text_"+comment_id).html(tinyMCE.get("editor_comment").getContent());
                }
            })
        });


        $('#idea_modal').on('show.bs.modal', function() {
            $('html').css({overflow: 'hidden'});
        });

        $('#idea_modal').on('hidden.bs.modal', function () {
            $('html').css({overflow: 'auto'});
        });

        <?php if($expert_review_found): ?>
        // radar chart
        var radarCtx = document.getElementById("expert-rating-radar-chart"),
            radarChart = null;

        var radarData = {
            labels: ["<?php echo $radar_chart_labels; ?>"],
            datasets: [
                {
                    label: "<?php echo $radar_chart_label; ?>",
                    backgroundColor: "rgba(179,181,198,0.2)",
                    borderColor: "rgba(179,181,198,1)",
                    pointBackgroundColor: "rgba(179,181,198,1)",
                    pointBorderColor: "#fff",
                    pointHoverBackgroundColor: "#fff",
                    pointHoverBorderColor: "rgba(179,181,198,1)",
                    data: [<?php echo $radar_chart_data; ?>]
                }
            ]
        };

        var radarOptions = {
            responsive : true,
            legend: {
                display: false,
            },
            scale: {
                ticks: {
                    max: 5,
                    min: 0,
                    stepSize: 1,
                    fontSize: 12
                },
                pointLabels: {
                    fontFamily: "Roboto Condensed",
                    fontSize: 14,
                },
            },
        };

        // bar chart
        var barCtx = document.getElementById("expert-rating-horizontal-chart"),
            barChart = null;

        var barData = {
            labels: ["\uf005\uf005\uf005\uf005\uf005", "\uf005\uf005\uf005\uf005\uf123", "\uf005\uf005\uf005\uf005\uf006", "\uf005\uf005\uf005\uf123\uf006", "\uf005\uf005\uf005\uf006\uf006", "\uf005\uf005\uf123\uf006\uf006", "\uf005\uf005\uf006\uf006\uf006", "\uf005\uf123\uf006\uf006\uf006", "\uf005\uf006\uf006\uf006\uf006", "\uf123\uf006\uf006\uf006\uf006", "\uf006\uf006\uf006\uf006\uf006"],
            datasets: [{
                label: "<?php echo $bar_chart_label; ?>",
                backgroundColor: "rgba(179,181,198,0.2)",
                borderColor: "rgba(179,181,198,1)",
                pointBackgroundColor: "rgba(179,181,198,1)",
                pointBorderColor: "#fff",
                pointHoverBackgroundColor: "#fff",
                pointHoverBorderColor: "rgba(179,181,198,1)",
                data: [<?php echo $bar_chart_data; ?>],
            }]
        };

        var barOptions = {
            legend: {
                display: false,
            },
            tooltips: {
                enabled: false,
            },
            scales: {
                yAxes: [{
                    gridLines: {
                        display: false,
                    },
                    ticks: {
                        fontFamily: 'FontAwesome',
                        fontColor: '#FE642E',
                        fontSize: 18,
                    },
                }],
                xAxes: [{
                    gridLines: {
                        display: false,
                        drawBorder: false,
                    },
                    ticks: {
                        display: false,
                    },
                }],
            },
            hover: {
                animationDuration: 0
            },
            animation: {
                onComplete: function() {
                    var chartInstance = this.chart;
                    var ctx = chartInstance.ctx;
                    ctx.textBaseline = 'top';
                    ctx.fillStyle = '#333';
                    ctx.font = '14px Roboto Condensed';
                    Chart.helpers.each(this.data.datasets.forEach(function(dataset, i) {
                        var meta = chartInstance.controller.getDatasetMeta(i);
                        Chart.helpers.each(meta.data.forEach(function(bar, index) {
                            if (dataset.data[index] == 0) {
                                ctx.fillText(dataset.data[index], bar._model.x + 10, bar._model.y - 8);
                            } else {
                                ctx.fillText(dataset.data[index], bar._model.x - 25, bar._model.y - 8);
                            }
                        }), this)
                    }), this);
                }
            }
        };

        $('#expert-reviews-chart').on('show.bs.modal', function() {
            $('html').css({overflow: 'hidden'});

            radarChart = new Chart(radarCtx, {
                type: 'radar',
                data: radarData,
                options: radarOptions
            });

            barChart = new Chart(barCtx, {
                type: 'horizontalBar',
                data: barData,
                options: barOptions,
            });
        });

        $('#expert-reviews-chart').on('hidden.bs.modal', function () {
            if (radarChart != null) {
                radarChart.destroy();
            }

            if (barChart != null) {
                barChart.destroy();
            }

            $('html').css({overflow: 'auto'});
        });
        <?php endif; ?>

        var idea_id;

        $('.idea_popup').on('click',function(){
            $('#idea_modal').modal('show');
            idea_id = $(this).data('ideaid');
            $(".article-content .wpulike.wpulike-default").remove();
			$(".article-content .tiles").remove();
            tinyMCE.get("editor_idea").setContent($(".article-content").html());
            $('#idea_title').val($('.page-title').html());
            $( "select[name=idea_campaign]" ).val($(this).data('campaign'));
        });

        $('.edit-idea-button').on('click',function(){
           $.ajax({
                type: "POST",
                url: '<?php echo admin_url("admin-ajax.php") ?>',
                data: {
                    action: 'edit_ideas',
                    idea_id: idea_id,
                    idea_text: tinyMCE.get("editor_idea").getContent(),
                    idea_campaign : $('#idea_campaign').find(":selected").val(),
                    idea_title: $('#idea_title').val()
                },
                success: function() {
                    $('#idea_modal').modal("hide");
                    $('.article-content').html(tinyMCE.get("editor_idea").getContent());
                    $('.page-title').html($('#idea_title').val());
                    location.reload();
                }
            })
        });

    })
</script>

<?php if(is_user_logged_in ()): ?>
<div class="modal fade" id="comment_modal" tabindex="-1" role="dialog"  style="display:none; outline: none;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php _e("Edit Comment",IDEAS_TEXT_DOMAIN); ?></h4>
      </div>
      <div class="modal-body">
        <?php wp_editor( "", "editor_comment", array('quicktags' => false) ); ?>
      </div>
      <div class="modal-footer" style="margin-top:0">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e("Close",IDEAS_TEXT_DOMAIN); ?></button>
        <button type="button" class="btn btn-primary edit-comment-button"><?php _e("Save",IDEAS_TEXT_DOMAIN); ?></button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if(is_user_logged_in () ): ?>
    <div class="modal fade" id="idea_modal" tabindex="-1" role="dialog"  style="display:none; outline: none;">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php _e("Edit Idea",IDEAS_TEXT_DOMAIN); ?></h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
                <label><?php _e("Idea Title",IDEAS_TEXT_DOMAIN); ?>*</label>
                <input name="title" type="text" id="idea_title" class="form-control">
            </div>
            <?php if(post_type_exists('campaigns')): ?>
                <?php $campaigns = get_posts(array('post_type'=>'campaigns','nopaging' => true)) ?>
                <?php if(!empty($campaigns)): ?>
                  <div class="form-group">
                    <label><?php _e("Campaigns",IDEAS_TEXT_DOMAIN); ?></label>
                    <select name="idea_campaign" id="idea_campaign" class="form-control">
                      <option value="0"><?php _e("Select campaign",IDEAS_TEXT_DOMAIN); ?></option>
                      <?php foreach ($campaigns as $key => $val): ?>
                        <?php $post_meta = get_post_meta($val->ID); ?>
                        <?php if(isset($post_meta['campaign_end_date'][0]) && !empty($post_meta['campaign_end_date'][0]) ): ?>
                          <?php if(strtotime($post_meta['campaign_end_date'][0]) > time()): ?>
                            <option value="<?php echo $val->ID ?>"><?php echo $val->post_title ?></option>
                          <?php endif ?>
                        <?php endif ?>
                      <?php endforeach ?>
                    </select>
                  </div>
                <?php endif ?>
            <?php endif ?>
            <?php wp_editor( "", "editor_idea", array('quicktags' => false) ); ?>
          </div>
          <div class="modal-footer" style="margin-top:0">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e("Close",IDEAS_TEXT_DOMAIN); ?></button>
            <button type="button" class="btn btn-primary edit-idea-button"><?php _e("Save",IDEAS_TEXT_DOMAIN); ?></button>
          </div>
        </div>
      </div>
    </div>
<?php endif; ?>

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

<div class="ideas-list single_idea">
<?php /* Start the Loop */ ?>
<?php while ( have_posts() ) : the_post(); ?>
    <?php 
    $args = array(
        'post_type'  => 'campaigns',
        'meta_query' => array(
            array(
                'key'     => 'campaign_ideas',
                'value'   => get_the_ID(),
                'compare' => '=',
            ),
        ),
    );
    $query = new WP_Query( $args );
    $campaign = $query->get_posts();

    if ($campaign) {
        $campaign_id = $campaign[0]->ID;
        $campaign_post_meta = get_post_meta($campaign[0]->ID);

        if(!empty($campaign[0]->ID) && !empty($campaign_post_meta['campaign_end_date'][0]) && strtotime($campaign_post_meta['campaign_end_date'][0]) < time()){
            $campaign_end = true;
        } else {
            $campaign_end = false;  
        }
    } else {
        $campaign_id = '';
        $campaign_end = false;
    }

    ?>
    <?php if(isset($campaign[0])&&!empty($campaign[0])): ?>
        <div class="idea_campaign_info top_box">
            <?php $cmp_image_id = get_post_meta($campaign[0]->ID,'campaign_image_id',true) ?>
            <?php if($cmp_image_id): ?>
                <div class="pull-left idea_campaign_img">
                    <a href="<?php echo wp_get_attachment_url( $cmp_image_id ) ?>" rel="prettyPhoto">
                        <?php echo wp_get_attachment_image( $cmp_image_id ) ?>
                    </a>
                </div>
            <?php endif ?>
            <div>
                <a href="<?php  echo site_url() ?>/campaigns/<?php echo $campaign[0]->post_name ?>">
                    <div style="font-size:16px;text-transform: uppercase;"><?php echo $campaign[0]->post_title ?></div    >
                </a>
                <?php 
                    $due_date = get_post_meta($campaign[0]->ID,'campaign_end_date',true);
                    $due_date = new DateTime($due_date);
                    $now = new DateTime();
                    $interval = $due_date->diff($now); 
                ?>
                <div><?php _e("Time left",IDEAS_TEXT_DOMAIN); ?>: <?php echo $interval->format('%m mois, %d jour') ?></div>
                <div><?php _e("Ideas count",IDEAS_TEXT_DOMAIN); ?>: <?php echo count(get_post_meta($campaign[0]->ID,'campaign_ideas',false)) ?></div>
                <div style="text-align:justify; margin-top:20px">
                    <?php echo wp_trim_words( $campaign[0]->post_content,22 ) ?> 
                </div>
            </div>
        </div>
    <?php endif ?>

    <div class="row">
        <div class="col-md-2">
            <div class="sabai-col-xs-2 sabai-questions-side" user_id style="width: 100%;">
                <?php if($campaign_end): ?>
                   <!-- <div style="background: #FFFFFF;position: absolute;z-index: 1001;height: 100%;width: 100%;opacity: 0.5;"></div>->
                <?php endif; ?> 
               <?php /*?> <?php if(function_exists('kleo_item_likes')): ?>
                    <div class="sabai-questions-vote-count idea_vote" user_id=<?php the_author_meta( 'ID' ); ?> >
                        <?php kleo_item_likes(get_the_ID()) ?>            
                    </div>
                <?php endif ?><?php */?>
				<!--waqas changes start-->
							  <div class="sabai-questions-vote-count idea_vote whole" user_id=<?php the_author_meta( 'ID' ); ?> >
                              <?php if(function_exists('wp_ulike'))  echo wp_ulike('get'); ?>         
                             </div>
			    <!--waqas changes end-->
                <div class="sabai-questions-answer-count">
                    <?php
                    $comments_link = (isset($_GET['assign-experts']) || isset($_GET['post-expert-review']) || isset($_GET['post-user-review']) || isset($_GET['post-idea-update'])) ? get_the_permalink() . '#idea-comments' : '#idea-comments';
                    ?>
                    <a href="<?php echo $comments_link; ?>" style="cursor:pointer">
                        <?php $user_comments = get_comments(array('author__in'=>array(get_current_user_id()),'post_id'=>get_the_ID())); ?>
                        <span class="sabai-number" <?php echo count($user_comments)?'style="color:#24B129"':'' ?> >
                            <div class="comments_count_number" style="display:inline"><?php echo get_comments_number() ?></div>
                            <i class="fa fa-comments" title="Commenter"></i>
                            <input type='hidden' class="post_id" value="<?php echo get_the_ID() ?>">
                        </span>
                    </a>
                </div>
                <?php 
                    $idea_status = get_post_meta($post->ID, 'idea_status', true);
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
                <div class="sabai-questions-view-count">
                    <?php $views_count = get_post_meta(get_the_ID(),'views_count')[0] ?>
                    <span class="sabai-number"><?php echo $views_count?$views_count:0 ?></span> <?php _e("views",IDEAS_TEXT_DOMAIN); ?>  
                    <?php if(is_user_logged_in()): ?>
                        <span class="idea_favorite" data-toggle="idea-tooltip" data-placement="top" title="<?php _e('Put in favorites', IDEAS_TEXT_DOMAIN); ?>">
                            <?php $favorites_meta = get_post_meta(get_the_id(),'idea_favorites') ?>
                            <span class="text-center"><i class="favorite-star fa-lg fa <?php echo in_array(get_current_user_id(),$favorites_meta)?'fa-star':'fa-star-o' ?>"></i>
                            </span>
                            <span class="text-center favorites_count"><?php echo count($favorites_meta) ?></span>
                            <input type="hidden" id="post_id" value="<?php the_ID() ?>"> 
                        </span>
                    <?php endif ?>
                </div>
            </div>
        </div>
        <div class="col-md-10">
            <div>
                <?php $author_id = get_the_author_meta('ID'); ?>
                <span class="idea-avatar"><?php echo get_avatar( $author_id, '32' );  ?></span>

                <?php if ($expert_review_found): ?>
                    <?php
                    $expert_average_ratings = $expert_reviews_and_average_ratings['average'];
                    $string = sprintf(_n('%d expert review', '%d expert reviews', count($expert_reviews_and_average_ratings['reviews']), IDEAS_TEXT_DOMAIN), count($expert_reviews_and_average_ratings['reviews']));
                    ?>
                    <div class="expert-ratings-modal-wrapper" data-toggle="modal" data-target="#expert-reviews-chart">
                        <!-- <span class="review-idea-average-rating" data-score="<?php echo $expert_average_ratings; ?>"></span> -->
                        <span class="expert-rating-average"><?php echo $expert_average_ratings; ?></span>
                        <span class="fa fa-bar-chart"></span>
                        <?php echo '(' . $string . ')'; ?>
                    </div>
                <?php endif ?>
            </div>

            <?php
            if (isset($_GET['assign-experts']) || isset($_GET['post-expert-review']) || isset($_GET['post-user-review']) || isset($_GET['post-idea-update'])) {
                $read_more = '<a href="' . get_the_permalink() . '#idea-details" class="go-to-idea-detials"> [' . __('See More', IDEAS_TEMPLATE_PATH) . ']</a>';
            } else {
                $read_more = '<a href="#idea-details" class="go-to-idea-detials"> [' . __('See More', IDEAS_TEMPLATE_PATH) . ']</a>';
            }

            echo '<div class="idea-excerpt">' . wp_trim_words(get_the_content(), 25, ' ' . $read_more) . '</div>';
            ?>

            <div style="margin-bottom: 20px">
                <div class="sabai-questions-activity sabai-questions-activity-inline">
                    <ul class="sabai-entity-activity">
                        <li>
                            <a href="<?php echo bp_core_get_user_domain( $author_id ); ?>" class="sabai-user sabai-user-with-thumbnail" rel="nofollow" data-popover-url="http://marylink.appteka.cc/sabai/user/profile/jcantenot">
                                <?php echo bp_core_get_user_displayname($author_id) ?>
                            </a> 
                            <?php _e("posted",IDEAS_TEXT_DOMAIN); ?> : 
                            <span title="<?php get_the_date(); ?>">
                            <?php echo human_time_diff( strtotime( get_the_date('Y-m-d H:i:s')), time() ); ?>
                            </span>
                        </li>
                        <?php if($updated_at=get_post_meta(get_the_id(),'updated_at',true)): ?>
                        <li>
                            <i class="fa fa-clock-o"></i>
                            <?php _e("updated",IDEAS_TEXT_DOMAIN); ?> :  
                            <?php echo human_time_diff( strtotime( $updated_at ), time() ); ?>
                        </li>
                        <?php endif ?>
                        <?php if(is_user_logged_in () && is_author_idea() && !$campaign_end): ?>
                        <li>
                            <a class="idea_popup" data-campaign="<?php echo (!empty($campaign[0]->ID)) ? $campaign[0]->ID : 0; ?>" data-ideaid="<?php the_ID();?>" style="cursor:pointer">
                                <i class="fa fa-edit"></i> <?php _e("Edit",IDEAS_TEXT_DOMAIN); ?>
                            </a>
                        </li>
                        <?php endif; ?>
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
            <?php get_template_part( 'page-parts/posts-social-share' ); ?>
        </div>
    </div>

    <?php 
    if( $related == 1 ) {
        get_template_part( 'page-parts/posts-related' );
    }
    ?>

    <?php
    if ( sq_option( 'post_navigation', 1 ) == 1 ) :
        // Previous/next post navigation.
        kleo_post_nav();
    endif;
    ?>

    <?php if($campaign_end): ?>
        <script>
        jQuery(document).ready(function($){
            //$("#respond").remove();
            $( ".edit-link" ).each(function() {
                $( this ).remove();
            });
        });</script>
    <?php endif; ?>

    <?php
    // find idea experts
    $idea_experts = klc_get_experts_for_given_idea($idea_id);

    $idea_status = get_post_meta($idea_id, 'idea_status', true);
    $enable_expert_reviews = get_post_meta($idea_id, '_idea_enable_expert_reviews', true);
    $enable_idea_updates = get_post_meta($idea_id, '_idea_enable_idea_updates', true);

    // assign expert template
    if (isset($_GET['assign-experts']) && klc_ideas_modifier() && $enable_expert_reviews === 'on' && $idea_status != 'already reviewed') {
        $params = array(
            'idea_experts' => $idea_experts,
            'campaign_id'  => $campaign_id,
        );

        klc_render_template('assign-experts', $params);
    }

    // post user review template
    elseif (isset($_GET['post-idea-update']) && klc_check_for_idea_update_owner($idea_id) && $enable_idea_updates === 'on' && $idea_status == 'already reviewed') {
        klc_render_template('post-idea-update');
    }

    // idea tabs template
    elseif (empty($_GET)) {
        $params = array(
            'expert_reviews_and_average_ratings' => $expert_reviews_and_average_ratings,
            'user_reviews_and_average_ratings'   => $user_reviews_and_average_ratings,
            'idea_updates'                       => $idea_updates,
            'idea_id'                            => $idea_id,
            'idea_status'                        => $idea_status,
            'idea_experts'                       => $idea_experts,
            'modifier_can_post_user_review'      => $modifier_can_post_user_review,
        );

        klc_render_template('idea-tabs', $params);
    }
    ?>
    
<?php endwhile; ?>

</div>
<?php get_template_part('page-parts/general-after-wrap');?>

<?php get_footer(); ?>