<?php
$campaign_image_src = get_post_meta(get_the_ID(), '_campaign_image', true);
$campaign_image_id = get_post_meta(get_the_ID(), '_campaign_image_id', true);
$campaign_youtube = get_post_meta(get_the_ID(), '_campaign_youtube', true);

echo '<div class="single-campaign-content-inner">';

	if ($campaign_youtube) {
		preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $campaign_youtube, $match);
		$youtube_video_id = $match[1];

		echo '<div class="campaign-youtube-video">';
			echo '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . $youtube_video_id . '" frameborder="0" allowfullscreen></iframe>';
		echo '</div>';
	}

	if ($campaign_image_id) {
		echo '<div class="campaign-image">';
			$campaign_image_thumb_src = wp_get_attachment_image_src($campaign_image_id, 'medium_large');
			echo '<a href="' . $campaign_image_src . '" rel="prettyPhoto"><img src="' . $campaign_image_thumb_src[0] . '"></a>';
		echo '</div>';
	}

	echo '<div class="campaign-content">';
		the_content();
	echo '</div>';

echo '</div>';
