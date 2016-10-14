<?php get_header();?>

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

<?php if ( have_posts() ) : ?>

    <div class="sabai-row ideas-list">
        <div class="sabai-questions-questions sabai-col-md-12">
            <?php
                function getIdeasCount($status,$ids){
                    $args = array(
                        'post_type' => 'ideas',
                        'post__in'  => $ids,
                        'meta_query' => array(
                            array(
                                'key'     => 'idea_status',
                                'value'   => $status,
                                'compare' => '=',
                            ),
                        ),
                    );
                    $cmp_ideas_query = new WP_Query( $args );
                    $cmp_ideas_count = $cmp_ideas_query->found_posts;
                    wp_reset_query();
                    return $cmp_ideas_count;
                }
            ?>
            <?php $i=0 ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <?php $i++ ?>
                <?php $author_id = get_the_author_meta('ID'); ?>
                <?php $post_meta = get_post_meta(get_the_id()) ?>
                <?php 
                    if($post_meta['campaign_ideas']){
                        $cmp_ideas_ids = $post_meta['campaign_ideas'];

                        $cmp_ideas_count['in discussion']   = getIdeasCount('in discussion',$cmp_ideas_ids);
                        $cmp_ideas_count['selected']        = getIdeasCount('selected',$cmp_ideas_ids);
                        $cmp_ideas_count['rejected']        = getIdeasCount('rejected',$cmp_ideas_ids);
                        $cmp_ideas_count['in project']      = getIdeasCount('in project',$cmp_ideas_ids);    
                    } else {
                        $cmp_ideas_count['in discussion']   = 0;
                        $cmp_ideas_count['selected']        = 0;
                        $cmp_ideas_count['rejected']        = 0;
                        $cmp_ideas_count['in project']      = 0;
                    }
                ?>

                <div style="padding:10px" class="sabai-entity sabai-entity-type-content sabai-entity-bundle-name-questions sabai-entity-bundle-type-questions sabai-entity-mode-summary sabai-questions-novotes sabai-clearfix">
                    <div class="sabai-row cmp_list">
                        <?php if($i==1): ?>
                            <div class="sabai-col-xs-2 sabai-questions-side" style="min-width: 120px;">
                                <div class="text-center"><?php _e("Ideas count",IDEAS_TEXT_DOMAIN); ?></div>
                                <div class="ideas_count_circle info"><?php echo $cmp_ideas_count['in discussion'] ?></div>
                                <div class="status_tag info"><?php _e("In discussion",IDEAS_TEXT_DOMAIN); ?></div>
                                <div class="ideas_count_circle primary"><?php echo $cmp_ideas_count['selected'] ?></div>
                                <div class="status_tag primary"><?php _e("Selected",IDEAS_TEXT_DOMAIN); ?></div>
                                <div class="ideas_count_circle danger"><?php echo $cmp_ideas_count['rejected'] ?></div>
                                <div class="status_tag danger"><?php _e("Rejected",IDEAS_TEXT_DOMAIN); ?></div>
                                <div class="ideas_count_circle success"><?php echo $cmp_ideas_count['in project'] ?></div>
                                <div class="status_tag success"><?php _e("In project",IDEAS_TEXT_DOMAIN); ?></div>
                            </div>
                        <?php else: ?>
                            <div class="sabai-col-xs-1 sabai-questions-side non_first">
                                <div class="ideas_count_circle info"><?php echo $cmp_ideas_count['in discussion'] ?></div>
                                <div style="clear:both;"></div>
                                <div class="ideas_count_circle primary"><?php echo $cmp_ideas_count['selected'] ?></div>
                                <div style="clear:both;"></div>
                                <div class="ideas_count_circle danger"><?php echo $cmp_ideas_count['rejected'] ?></div>
                                <div style="clear:both;"></div>
                                <div class="ideas_count_circle success"><?php echo $cmp_ideas_count['in project'] ?></div>
                                <div style="clear:both;"></div>
                            </div>
                        <?php endif ?>
                        <div class="sabai-col-xs-8 sabai-questions-main" style="padding-top: 20px">
                            <div class="sabai-questions-title">
                                <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class=" sabai-entity-permalink sabai-entity-id-424 sabai-entity-type-content sabai-entity-bundle-name-questions sabai-entity-bundle-type-questions">
                                    <?php the_title(); ?>
                                </a>            
                            </div>
                          
                            <div class="sabai-questions-body">
                                  <?php the_excerpt(); ?>  
                            </div>
                            <div class="sabai-questions-custom-fields">
                            </div>

                            <div class="sabai-questions-activity sabai-questions-activity-inline">
                                <?php if(isset($post_meta['campaign_end_date'][0]) && !empty($post_meta['campaign_end_date'][0])): ?>
                                    <div style="font-weight: bold; font-size: 16px;<?php if(strtotime($post_meta['campaign_end_date'][0]) < time() ) echo 'color:#FF9F2F;'; ?>">
                                        <?php _e("Campaign end date",IDEAS_TEXT_DOMAIN); ?> <?php echo date('d/m/Y H:i', strtotime($post_meta['campaign_end_date'][0])); ?>
                                    </div> 
                                <?php endif ?>
                            </div>
                        </div>
                        <div class="sabai-col-xs-2">
                            <?php $meta = get_post_meta(get_the_id()) ?>
                            <?php if(!empty($meta['campaign_image_id'][0])): ?>
                                <?php $attached_image = wp_get_attachment_image_src($meta['campaign_image_id'][0]) ?>
                                <a href="<?php echo $meta['campaign_image_url'][0] ?>" rel="prettyPhoto">
                                    <img src="<?php echo $attached_image[0] ?>"?>
                                </a>
                            <?php endif ?>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>

        </div>
    </div>


    <?php
    // page navigation.
    kleo_pagination();

else :
    // If no content, include the "No posts found" template.
    get_template_part( 'content', 'none' );

endif;
?>

<?php get_template_part('page-parts/general-after-wrap'); ?>

<?php get_footer(); ?>