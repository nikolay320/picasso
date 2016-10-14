<?php
$post_id = $campaign_id = get_the_ID();
$pi_idea_status = pi_idea_status();
$campaign_ideas = get_post_meta($post_id, '_campaign_ideas', false);
$campaign_deadline = get_post_meta($post_id, '_campaign_deadline', true);
$campaign_image_id = get_post_meta($post_id, '_campaign_image_id', true);
$campaign_image_src = get_post_meta($post_id, '_campaign_image', true);
$campaign_body_class = $campaign_image_id ? 'col-sm-6 col-md-7 col-lg-8' : 'col-sm-8 col-md-9 col-lg-10';

if (!$campaign_ideas) {
	$campaign_ideas = array(0);
}

// check if campaign has ended
$interval = $campaign_deadline ? ($campaign_deadline - current_time('timestamp')) : 0;

$total_ideas = 0;
$total_comments = 0;
$total_likes = 0;

$args = array(
	'post_type'      => 'idea',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'post__in'       => $campaign_ideas,
);

$ideas = get_posts($args);

foreach ($ideas as $idea) {
	$idea_id = $idea->ID;
	$total_comments = $total_comments + wp_count_comments($idea_id)->total_comments;
	$total_likes = $total_likes + get_post_meta($idea_id, '_liked', true);
	$total_ideas++;
}
?>
<div class="clear campaign-list">

	<?php if ($campaign_image_id): ?>
		<div class="col-xs-12 col-sm-2 col-md-2 campaign-image">
			<a href="<?php echo $campaign_image_src; ?>" rel="prettyPhoto">
				<?php $campaign_image_thumb_src = wp_get_attachment_image_src($campaign_image_id, 'medium'); ?>
				<img src="<?php echo $campaign_image_thumb_src[0]; ?>">
			</a>
		</div>
	<?php endif ?>

	<div class="col-xs-12 <?php echo $campaign_body_class; ?> campaign-body">
		<div class="campaign-title"><a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a></div>
		<div class="campaign-excerpt"><?php echo wp_trim_words(get_the_content(), 25, ' [...]'); ?></div>

		<div class="row">
			
			<div class="col-lg-6">

				<div class="campaign-stats">
					<div class="total-ideas">
						<i class="fa fa-lightbulb-o"></i> <?php echo $total_ideas; ?>
					</div>
					<div class="total-comments">
						<i class="fa fa-comments"></i> <?php echo $total_comments; ?>
					</div>
					<div class="total-likes">
						<i class="fa fa-thumbs-up"></i> <?php echo $total_likes; ?>
					</div>
				</div><!-- .campaign-stats -->

			</div>

			<div class="col-lg-6">

				<?php if ($interval > 0): ?>
					<ul class="countdown-clock countdown-clock-2" data-date="<?php echo date('m/d/Y H:i:s', $campaign_deadline); ?>" data-offset="<?php echo get_option('gmt_offset'); ?>">
						<li>
							<span class="days">00</span>
							<p class="days-text"><?php _e('Days', CAMPAIGNS_LOCALE); ?></p>
						</li>
						<li class="seperator">:</li>
						<li>
							<span class="hours">00</span>
							<p class="hours-text"><?php _e('Hours', CAMPAIGNS_LOCALE); ?></p>
						</li>
						<li class="seperator">:</li>
						<li>
							<span class="minutes">00</span>
							<p class="minutes-text"><?php _e('Minutes', CAMPAIGNS_LOCALE); ?></p>
						</li>
						<li class="seperator">:</li>
						<li>
							<span class="seconds">00</span>
							<p class="seconds-text"><?php _e('Seconds', CAMPAIGNS_LOCALE); ?></p>
						</li>
					</ul>
				<?php endif ?>

			</div>

		</div>

	</div><!-- .campaign-body -->

	<div class="col-xs-12 col-sm-4 col-md-3 col-lg-2 campaign-statistics">
		<?php foreach ($pi_idea_status as $status_key => $status_title): ?>
			<?php if ($status_key !== 'no-status'): ?>
				<div class="status-wrapper <?php echo $status_key; ?>">
					<?php if ($status_key === 'not-selected' || $status_key === 'no-go'): ?>
						<div class="line-wrapper">
							<span class="line"><span class="dot"></span></span>
						</div>
						<div class="count-ideas-wrapper">
							<div class="count-ideas status-<?php echo $status_key; ?>">
								<span class="count"><?php echo pc_count_ideas_for_given_status($status_key, $campaign_ideas); ?></span>
								<span class="status"><?php echo $status_title; ?></span>
							</div>
						</div>
					<?php else: ?>
						<div class="count-ideas status-<?php echo $status_key; ?>">
							<span class="count"><?php echo pc_count_ideas_for_given_status($status_key, $campaign_ideas); ?></span>
							<span class="status"><?php echo $status_title; ?></span>
						</div>
					<?php endif ?>
				</div>
			<?php endif ?>
		<?php endforeach ?>
	</div><!-- .campaign-statistics -->

</div>