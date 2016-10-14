<?php
get_header();
global $klc_ideas;
$modifier_can_post_user_review = $klc_ideas['modifier_can_post_user_review'];
?>

<?php // add_idea_modal(); ?>

<?php
// frontend idea create/edit modal

// wp_enqueue_style('cmb2-frontend-form');
// wp_enqueue_style('reveal-modal');
// wp_enqueue_script('reveal-modal');
// wp_enqueue_script('custom-upload-media');

wp_enqueue_style('cmb2-frontend-form');
wp_enqueue_style('remodal-style');
wp_enqueue_style('remodal-default-style');
wp_enqueue_script('remodal-script');
wp_enqueue_script('custom-upload-media');
?>

<!-- <div class="reveal-modal" id="frontend-idea-submit"></div> -->

<div class="remodal" id="frontend-idea-modal">
    <button data-remodal-action="close" class="remodal-close"></button>
    <div id="frontend-idea-submit"></div>
</div>
<?php // end frontend idea create/edit modal ?>

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
            coments_count.parent().css('color', '#24B129');
          }
        })
    })

    var idea_id, idea_text, idea_title;

    $('.idea_popup').on('click',function(){
        $('#idea_modal').modal('show');
        idea_id = $(this).attr('data-ideaid');
        idea_title = $('.idea_title_'+idea_id).html();
        $('.idea_hidden_text_'+idea_id+' .wpulike.wpulike-default').remove();
		$('.idea_hidden_text_'+idea_id+' .tiles').remove();
        $('.edit_title').val(idea_title);
        tinyMCE.get("editor_idea").setContent($('.idea_hidden_text_'+idea_id).html());
        $( "select[name=idea_campaign]" ).val($(this).data('campaign'));
    })

    $('.edit-idea-button').on('click',function(){
       $.ajax({
            type: "POST",
            url: '<?php echo admin_url("admin-ajax.php") ?>',
            data: {
                action: 'edit_ideas',
                idea_id: idea_id,
                idea_text: tinyMCE.get("editor_idea").getContent(),
                idea_campaign : $('#idea_campaign').find(":selected").val(),
                idea_title : $('.edit_title').val()
            },
            success: function() {
                $('#idea_modal').modal("hide");
                $('.idea_title_'+idea_id).html($('.edit_title').val())
                $('.idea_text_'+idea_id).html(tinyMCE.get("editor_idea").getContent().substr(0,350) + ' [...]');
            }
        })
    });

    $('#idea_modal').on('show.bs.modal', function() {
        $('html').css({overflow: 'hidden'});
    });

    $('#idea_modal').on('hidden.bs.modal', function () {
        $('select[name=idea_campaign]').prop('selectedIndex',0);
        $('html').css({overflow: 'auto'});
    })

    $('.idea_favorite').on('click',function(){
        elem = $(this)
        if(elem.find('.favorite-star').hasClass('fa-star-o')){
            var status = 1
        } else {
            var status = 0
        }
        var data = {
            action: 'idea_favorits',
            status: status,
            post_id: elem.find('#post_id').val()
        };
        jQuery.post( "<?php echo admin_url('admin-ajax.php') ?>" , data, function(response) {
            if(response.success){
                var favorites_count = elem.find('.favorites_count').text()
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
                <?php _e('To post a comment please login', IDEAS_TEXT_DOMAIN); ?>
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
                <input name="title" type="text" class="form-control edit_title">
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
$blog_type = sq_option('blog_type','masonry');
$blog_type = apply_filters( 'kleo_blog_type', $blog_type );

$template_classes = $blog_type . '-listing';
if ( sq_option( 'blog_meta_status', 1 ) == 1 ) {
    $template_classes .= ' with-meta';
} else {
    $template_classes .= ' no-meta';
}
if ( $blog_type == 'standard' && sq_option('blog_standard_meta', 'left' ) == 'inline' ) {
    $template_classes .= ' inline-meta';
}
add_filter('kleo_main_template_classes', create_function('$cls','$cls .=" posts-listing ' . $template_classes . '"; return $cls;'));
?>

<?php get_template_part('page-parts/general-title-section'); ?>

<?php get_template_part('page-parts/general-before-wrap'); ?>

<?php if ( category_description() ) : ?>
    <div class="archive-description"><?php echo category_description(); ?></div>
<?php endif; ?>

<div class="row sabai-questions-search idea_search_box">
    <div class="col-md-4">
        <div class="idea_search">
            <select class="idea_sort" style="font-size: 14px;" onchange="window.location.href='?sort='+jQuery('.idea_sort option:selected').val()">
                <option value="newest" <?php echo (isset($_GET['sort']) && $_GET['sort']=='newest') ?'selected':''?> ><?php _e("Newest First",IDEAS_TEXT_DOMAIN); ?></option>
                <option value="oldest" <?php echo (isset($_GET['sort']) && $_GET['sort']=='oldest') ?'selected':''?> ><?php _e("Oldest First",IDEAS_TEXT_DOMAIN); ?></option>
                <option value="recent" <?php echo (isset($_GET['sort']) && $_GET['sort']=='recent') ?'selected':''?> ><?php _e("Recently Active",IDEAS_TEXT_DOMAIN); ?></option>
                <option value="voted" <?php echo (isset($_GET['sort']) && $_GET['sort']=='voted') ?'selected':''?> ><?php _e("Most Votes",IDEAS_TEXT_DOMAIN); ?></option>
                <option value="answers" <?php echo (isset($_GET['sort']) && $_GET['sort']=='answers') ?'selected':''?> ><?php _e("Most Answers",IDEAS_TEXT_DOMAIN); ?></option>
                <option value="my-ideas" <?php echo (isset($_GET['sort']) && $_GET['sort']=='my-ideas') ?'selected':''?> ><?php _e("My Ideas",IDEAS_TEXT_DOMAIN); ?></option>
            </select>
        </div>
    </div>
    <form>
        <div class="col-md-4">
            <input type="text" name="s" class="idea_search_input">
            <input type="hidden" name="type" value="ideas">
        </div>
        <div class="col-md-1">
            <button type="submit" style="min-width: 100%;" class="btn btn-info idea_search" style="width:100%">
                <i class="fa fa-search"></i>
            </button>
        </div>
    </form>
    <div class="col-md-3">
        <?php // add_idea_button() ?>
<!--         <a href="#" class="btn btn-info add-idea-button create-idea-modal-link" data-ajax-url="<?php echo admin_url('admin-ajax.php'); ?>">
            <span class="fa fa-lightbulb-o"></span>
            <?php _e('Add Idea', IDEAS_TEXT_DOMAIN); ?>
            <span class="fa fa-spinner fa-spin loader"></span>
        </a> -->

        <a href="<?php echo get_permalink($klc_ideas['idea_create_page']); ?>" class="btn btn-info add-idea-button">
            <span class="fa fa-lightbulb-o"></span>
            <?php _e('Add Idea', IDEAS_TEXT_DOMAIN); ?>
        </a>
    </div>
</div>

<?php
wp_reset_query();

if (isset($_GET['sort']) && !empty($_GET['sort'])) {
    $sort = $_GET['sort'];

    if ($sort === 'oldest') {
        $query = new WP_Query(
            array(
                'post_type'      => 'ideas',
                'posts_per_page' => 10,
                'orderby'        => 'date',
                'order'          => 'ASC',
                'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1,
            )
        );
    } elseif ($sort === 'recent') {
        $query = new WP_Query(
            array(
                'post_type'      => 'ideas',
                'posts_per_page' => 10,
                'orderby'        => 'modified',
                'order'          => 'DESC',
                'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1,
            )
        );
    } elseif ($sort === 'voted') {
        $query = new WP_Query(
            array(
                'post_type'      => 'ideas',
                'posts_per_page' => 10,
                'orderby'        => 'meta_value_num',
                'meta_key'       => '_liked',
                'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1,
            )
        );
    } elseif ($sort === 'answers') {
        $query = new WP_Query(
            array(
                'post_type'      => 'ideas',
                'posts_per_page' => 10,
                'orderby'        => 'comment_count',
                'order'          => 'DESC',
                'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1,
            )
        );
    } elseif ($sort === 'my-ideas') {
        $query = new WP_Query(
            array(
                'post_type'      => 'ideas',
                'posts_per_page' => 10,
                'orderby'        => 'comment_count',
                'order'          => 'DESC',
                'author'         => get_current_user_id(),
                'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1,
            )
        );
    } else {
        $query = new WP_Query(
            array(
                'post_type'      => 'ideas',
                'posts_per_page' => 10,
                'orderby'        => 'date',
                'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1,
            )
        );
    }
} else {
    $query = new WP_Query(
        array(
            'post_type'      => 'ideas',
            'posts_per_page' => 10,
            'orderby'        => 'date',
            'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1,
        )
    );
}
?>

<?php if ( $query->have_posts() ) : ?>
    <div class="sabai-row ideas-list">
        <div class="sabai-questions-questions sabai-col-md-12">

            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
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
                $query1 = new WP_Query( $args );
                $campaign_id = $query1->get_posts();

                if ($campaign_id) {
                    $campaign_post_meta = get_post_meta($campaign_id[0]->ID);
                    
                    if(!empty($campaign_id[0]->ID) && !empty($campaign_post_meta['campaign_end_date'][0]) && strtotime($campaign_post_meta['campaign_end_date'][0]) < time()){
                    	$campaign_end = true;
                    } else {
                    	$campaign_end = false;
                    }
                } else {
                    $campaign_end = false;
                }
                ?>

                <?php $author_id = get_the_author_meta('ID'); ?>

                <div style="padding:10px" class="sabai-entity sabai-entity-type-content sabai-entity-bundle-name-questions sabai-entity-bundle-type-questions sabai-entity-mode-summary sabai-questions-novotes sabai-clearfix">
                    <div class="sabai-row">
                        <div class="sabai-col-xs-2 sabai-questions-side">
                            <?php if($campaign_end): ?>
                            <!-- <div style="background: #FFFFFF;position: absolute;z-index: 1001;height: 100%;width: 100%;opacity: 0.5;"></div>->
                            <?php endif ?> 
                            <?php /*?><?php if(function_exists('kleo_item_likes')): ?>
                            <div class="sabai-questions-vote-count idea_vote" user_id=<?php the_author_meta( 'ID' ); ?>>
                                <?php $item_votes = get_post_meta(get_the_ID(),'_item_likes') ?>  
                                    <?php kleo_item_likes(get_the_ID()) ?>        
                                </div>
                            <?php endif ?><?php */?>
							<!--waqas changes start-->
							  <div class="sabai-questions-vote-count idea_vote whole" data-ulike-id="<?php echo get_the_ID(); ?>"  user_id=<?php the_author_meta( 'ID' ); ?> >
                              <?php if(function_exists('wp_ulike1'))  echo wp_ulike1('get',get_the_ID()); ?>         
                             </div>
							 <!--waqas changes end-->
                            <div class="sabai-questions-answer-count">
                                <a data-toggle="modal" class="popup_comment_link" data-target="#commentModal" style="cursor:pointer">
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
                                <?php $views_count = get_post_meta(get_the_ID(), 'views_count', true); ?>
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
                        <div class="sabai-col-xs-10 sabai-questions-main">
                            <div class="sabai-questions-title">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class="sabai-entity-permalink sabai-entity-id-424 sabai-entity-type-content sabai-entity-bundle-name-questions sabai-entity-bundle-type-questions">
                                    <span class="idea-avatar"><?php echo get_avatar( $author_id, '32' );  ?></span>
                                    <span class="idea_title_<?php the_ID() ?>" style="white-space: normal;"><?php the_title(); ?></span></a>
                                </a> 
                                <?php $idea_image_id = get_post_meta(get_the_ID(),'idea_image_id',true) ?>
                                <?php $idea_file_id = get_post_meta(get_the_ID(),'idea_file_id',true) ?>
                                <?php if($idea_image_id || $idea_file_id): ?>
                                    <i class="fa fa-file-o" style="margin-left: 10px"></i>
                                <?php endif ?>           
                            </div>
                          
                            <div class="sabai-questions-body idea_text_<?php the_ID(); ?>">
                                <span class="idea_hidden_text_<?php the_ID(); ?>" style="display: none;"><?php the_content(); ?></span>
                                <?php echo wp_trim_words( get_the_content(), 25, ' [...]' ); ?>
                            </div>
                            <div class="sabai-questions-custom-fields">
                            </div>

                            <?php
                            $enable_expert_reviews = get_post_meta(get_the_ID(), '_idea_enable_expert_reviews', true);
                            $enable_user_reviews = get_post_meta(get_the_ID(), '_idea_enable_user_reviews', true);
                            $enable_idea_updates = get_post_meta(get_the_ID(), '_idea_enable_idea_updates', true);

                            // find experts
                            $idea_experts = klc_get_experts_for_given_idea(get_the_ID());

                            // current user id
                            $current_user_id = get_current_user_id();

                            // idea status
                            $idea_status = get_post_meta(get_the_ID(), 'idea_status', true);
                            ?>

                            <div class="klc-table idea-info">
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
                                                            'value'   => get_the_ID(),
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
                                        <a class="klc-idea-link" href="<?php echo get_permalink(); ?>?assign-experts"><?php _e('Assign experts', IDEAS_TEXT_DOMAIN); ?></a>
                                    </div>
                                <?php endif ?>

                                <?php if (in_array($current_user_id, $idea_experts) && $enable_expert_reviews === 'on' && $idea_status == 'in review'): ?>
                                    <div class="klc-table-cell idea-expert-review">
                                        <a class="klc-idea-link idea-edit-link" href="<?php echo get_permalink(); ?>#expert-reviews"><?php _e('Post your expert review now!', IDEAS_TEXT_DOMAIN); ?></a>
                                    </div>
                                <?php endif ?>

                                <?php
                                if (klc_ideas_modifier()) {
                                    if ($modifier_can_post_user_review == '1' && $enable_user_reviews === 'on' && $idea_status == 'in review') {
                                        ?>
                                        <div class="klc-table-cell idea-user-review">
                                            <a class="klc-idea-link idea-edit-link" href="<?php echo get_permalink(); ?>#user-reviews"><?php _e('Post your review now!', IDEAS_TEXT_DOMAIN); ?></a>
                                        </div>
                                        <?php
                                    }
                                } elseif (!in_array($current_user_id, $idea_experts) && $enable_user_reviews === 'on' && $idea_status == 'in review') {
                                    ?>
                                    <div class="klc-table-cell idea-user-review">
                                        <a class="klc-idea-link idea-edit-link" href="<?php echo get_permalink(); ?>#user-reviews"><?php _e('Post your review now!', IDEAS_TEXT_DOMAIN); ?></a>
                                    </div>
                                    <?php
                                }
                                ?>

                                <?php if ($enable_idea_updates === 'on' && klc_check_for_idea_update_owner(get_the_ID()) && $idea_status == 'already reviewed'): ?>
                                    <div class="klc-table-cell idea-user-update">
                                        <a class="klc-idea-link" href="<?php echo get_permalink(); ?>?post-idea-update"><?php _e('Post idea update', IDEAS_TEXT_DOMAIN); ?></a>
                                    </div>
                                <?php endif ?>
                            </div>

                            <div class="sabai-questions-activity sabai-questions-activity-inline">
                                <ul class="sabai-entity-activity">
                                    <li>
                                        <a href="<?php echo bp_core_get_user_domain( $author_id ); ?>" class="sabai-user sabai-user-with-thumbnail" rel="nofollow" data-popover-url="http://marylink.appteka.cc/sabai/user/profile/jcantenot">
                                            <?php echo bp_core_get_user_displayname($author_id) ?>
                                        </a> 
                                        <?php _e("posted",IDEAS_TEXT_DOMAIN); ?> : 
                                        <span title="<?php get_the_date(); ?>">
                                        <?php // echo human_time_diff( strtotime( get_the_date('Y-m-d H:i:s')), time() ); ?>
                                        <?php // echo get_the_date('Y-m-d h:i:s'); ?>

                                        <?php
                                        printf( _x( '%s ago', '%s = human-readable time difference', IDEAS_TEXT_DOMAIN ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) );
                                        ?>

                                        </span>
                                    </li>
                                    <?php if($updated_at=get_post_meta(get_the_id(),'updated_at',true)): ?>
                                    <li>
                                       <i class="fa fa-clock-o"></i>
                                        <?php _e("updated",IDEAS_TEXT_DOMAIN); ?> : 
                                        <?php echo human_time_diff( strtotime( $updated_at ), time() ); ?>
                                    </li>
                                    <?php endif ?>
                                    <?php // if(is_user_logged_in () && is_author_idea() && !$campaign_end): ?>
                                    <?php if(is_user_logged_in () && is_author_idea()): ?>
                                    <li>
<!--                                         <a class="idea_popup" data-campaign="<?php echo (!empty($campaign_id[0]->ID)) ? $campaign_id[0]->ID : 0; ?>" data-ideaid="<?php the_ID();?>" style="cursor:pointer">
                                            <i class="fa fa-edit"></i> <?php _e("Edit",IDEAS_TEXT_DOMAIN); ?>
                                        </a> -->

                                        <!-- <a href="#" class="edit-idea-modal-link" data-idea-id="<?php echo get_the_ID(); ?>" data-ajax-url="<?php echo admin_url('admin-ajax.php'); ?>"><i class="fa fa-edit"></i> <?php _e('Edit', IDEAS_TEXT_DOMAIN); ?></a><span class="fa fa-spinner fa-spin idea-edit-modal-open-loader"></span> -->
                                        <a href="<?php echo get_permalink($klc_ideas['idea_edit_page']) . '?id=' . get_the_id(); ?>"><i class="fa fa-edit"></i> <?php _e('Edit', IDEAS_TEXT_DOMAIN); ?></a>
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
                    </div>
                </div>
            <?php endwhile; ?>

        </div>
    </div>

    <?php
    // page navigation
    if (isset($_GET['sort']) && $_GET['sort']) {
        kleo_pagination($query->max_num_pages);
    } else {
        kleo_pagination();
    }

wp_reset_query();

else :
    // If no content, include the "No posts found" template.
    get_template_part( 'content', 'none' );

endif;
?>

<?php get_template_part('page-parts/general-after-wrap'); ?>

<?php get_footer(); ?>