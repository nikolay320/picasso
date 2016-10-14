<?php get_template_part( 'content', get_the_content() ); ?>

<!-- Begin Attachments -->
<?php $idea_image_id = get_post_meta(get_the_ID(),'_idea_image_id',true) ?>
<?php $idea_file_id = get_post_meta(get_the_ID(),'_idea_file_id',true) ?>
<?php if($idea_image_id || $idea_file_id): ?>
    <div class="hr-title hr-long"><abbr><?php _e("Attachments",IDEAS_TEXT_DOMAIN); ?></abbr></div>
    <div class="text-center" style="margin:20px 0">
        <?php if($idea_image_id): ?>
            <div>
                <a href="<?php echo wp_get_attachment_url( $idea_image_id ) ?>" rel="prettyPhoto">
                    <?php echo wp_get_attachment_image( $idea_image_id, 'medium' ) ?>
                </a>
            </div>
        <?php endif ?>
        <?php if($idea_file_id): ?>
            <?php $file_type = explode('/',get_post_mime_type( $idea_file_id )) ?>
            <?php $file_url = wp_get_attachment_url( $idea_file_id ) ?>
            <div class="attach_file_link">
                <a href="<?php echo $file_url ?>">
                <?php ini_set('display_errors', 1) ?>
                    <?php if(is_file(getcwd().'/wp-content/plugins/ideas/assets/img/file_types/'.$file_type[1].'.png')):?>
                        <img src="<?php echo get_site_url().'/wp-content/plugins/ideas/assets/img/file_types/'.$file_type[1].'.png'?>" title="<?php echo $file_url ?>">
                    <?php else: ?>
                        <img src="<?php echo get_site_url().'/wp-content/plugins/ideas/assets/img/file_types/label.png'?>" title="<?php echo $file_url ?>">
                    <?php endif ?>
                </a>
            </div>
        <?php endif ?>
    </div>
<?php endif ?>

<!-- End Attachments -->

<?php $idea_video_id = get_post_meta(get_the_ID(),'_idea_video_id',true) ?>
<?php if($idea_video_id): ?>
    <?php $video_type = explode('/',get_post_mime_type( $idea_video_id )) ?>
    <?php $video_url = wp_get_attachment_url( $idea_video_id ) ?>
    <div class="hr-title hr-long"><abbr><?php _e("Video",IDEAS_TEXT_DOMAIN); ?></abbr></div>
    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-10">
            <?php echo do_shortcode('[video width="600" height="300" '.$video_type[1].'="'.$video_url.'"][/video]'); ?>
        </div>
    </div>
<?php endif ?>


<!-- frontend -->
<!-- idea images -->
<?php if ($idea_images = get_post_meta(get_the_ID(), '_idea_images', true)): ?>
<div>
    <div class="hr-title hr-long"><abbr><?php echo '<strong>' . __('Idea Images', 'ideas_plugin') . '</strong>:'; ?></abbr></div>
    <br />

    <div class="row idea-images">
        <?php foreach ($idea_images as $idea_image_id => $idea_image_url): ?>
            <div class="col-xs-6 col-sm-6 col-md-3 image">
                <div class="thumbnail">
                    <a href="<?php echo $idea_image_url; ?>" rel="prettyPhoto">
                        <img src="<?php echo wp_get_attachment_thumb_url($idea_image_id); ?>" />
                    </a>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>
<?php endif ?>

<!-- idea files -->
<?php if ($idea_files = get_post_meta(get_the_ID(), '_idea_files', true)): ?>
<div>
    <div class="hr-title hr-long"><abbr><?php echo '<strong>' . __('Idea Files', 'ideas_plugin') . '</strong>:'; ?></abbr></div>
    <br />

    <div class="row idea-files">
        <?php foreach ($idea_files as $idea_file_id => $idea_file_url): ?>
            <div class="col-xs-6 col-sm-6 col-md-3 image">
                <div class="thumbnail">
                    <?php
                    // base path, /var/www/html/picasso-dev/wp-content/plugins/ideas/
                    $base = IDEAS_PLUGIN_PATH . 'assets/img/file_types/';

                    // url, http://localhost/picasso-dev/wp-content/plugins/ideas/
                    $url = plugins_url('ideas/assets/img/file_types/');

                    // file type
                    $file_type = wp_check_filetype($idea_file_url);

                    if (!file_exists($base . $file_type['ext'] . '.png')) {
                        $icon_url = $url . 'label.png';
                    } else {
                        $icon_url = $url . $file_type['ext'] . '.png';
                    }
                    ?>
                    <a href="<?php echo $idea_file_url; ?>">
                        <img src="<?php echo $icon_url; ?>" title="<?php echo $idea_file_url; ?>" />
                    </a>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>
<?php endif ?>

<!-- idea videos -->
<?php if ($idea_videos = get_post_meta(get_the_ID(), '_idea_videos', true)): ?>
    <div>
        <div class="hr-title hr-long"><abbr><?php echo '<strong>' . __('Idea Videos', 'ideas_plugin') . '</strong>:'; ?></abbr></div>
        <br />

        <div class="row idea-videos">
            <?php foreach ($idea_videos as $idea_video_id => $idea_video_url): ?>
                <div class="col-xs-12 col-sm-12 col-md-6 video">
                    <?php
                    $video_type = explode('/', get_post_mime_type($idea_video_id));
                    echo do_shortcode('[video width="600" height="300" ' . $video_type[1] . '="' . $idea_video_url . '"]');
                    ?>
                </div>
            <?php endforeach ?>
        </div>
    </div>
<?php endif ?>
<!-- end frontend -->


<?php
$idea_youtube = get_post_meta($post->ID, '_idea_youtube', true);
if ($idea_youtube) {
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $idea_youtube, $match);
    $video_id = $match[1];
    ?>
    <div class="hr-title hr-long"><abbr>YouTube</abbr></div>
    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-10">
            
            <iframe width="600" height="300" src="https://www.youtube.com/embed/<?php echo $video_id; ?>" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
    <?php
} ?>