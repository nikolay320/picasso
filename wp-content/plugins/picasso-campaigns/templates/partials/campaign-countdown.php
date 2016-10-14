<div class="campaign-info-box">
	<div class="row">
		<div class="col-md-4">
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

			<?php if ($interval > 0): ?>
				<div class="campaign-add-idea">
					<?php $link = add_query_arg('campaign_id', $campaign_id, pi_idea_create_page()); ?>
					<a href="<?php echo $link; ?>" class="btn-primary add-idea-btn">
						<span class="fa fa-lightbulb-o"></span>
						<?php _e('Add Idea', 'picasso-ideas'); ?>
					</a>
				</div><!-- .campaign-add-idea -->
			<?php endif ?>
		</div><!-- .col-md-4 -->

		<div class="col-md-8">
			<div class="text-center campaign-deadline-info">
				<?php if ($interval > 0): ?>
					<ul class="countdown-clock" data-date="<?php echo date('m/d/Y H:i:s', $campaign_deadline); ?>" data-offset="<?php echo get_option('gmt_offset'); ?>">
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
				<?php else: ?>
					<h2><?php _e('Campaign has ended', CAMPAIGNS_LOCALE); ?></h2>
				<?php endif ?>
			</div><!-- .campaign-deadline-info -->
		</div><!-- .col-md-8 -->
	</div><!-- .row -->
</div><!-- .campaign-info-box -->