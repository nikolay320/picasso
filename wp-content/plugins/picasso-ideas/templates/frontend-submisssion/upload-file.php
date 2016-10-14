<div class="picasso-upload-wrapper">
	<div class="upload-form">
		<div class="form-group">
			<div class="pi-input-group">
				<div class="input-group">
					<label class="input-group-btn">
						<span class="btn btn-default">
							<?php echo $button_title; ?> <input type="file" accept="<?php echo $mime_type; ?>">
						</span>
					</label>
					<input type="text" class="form-control file-name-holder" readonly>
				</div>
			</div>

			<div class="pi-btn-group">
				<?php
				$post_max_size = intval(ini_get('post_max_size')) * 1024 * 1024;
				$file_size_message = sprintf(__('Exceeded filesize limit. Maximum size should be %dMB.', 'picasso-ideas'), $post_max_size / (1024 * 1024));
				?>
				<input type="hidden" name="post_max_size" value="<?php echo $post_max_size; ?>">
				<input type="hidden" name="file_size_message" value="<?php echo $file_size_message; ?>">
				<input type="hidden" name="file_type" value="<?php echo $supposed_file_type; ?>">
				<input type="hidden" name="meta_field_name" value="<?php echo $meta_field_name; ?>">
				<input type="submit" class="btn btn-primary pi-upload-file" value="<?php _e('Upload', 'picasso-ideas'); ?>">
			</div>
		</div>
	</div>

	<div class="progress hidden">
		<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 60%;">0%</div>
	</div>

	<div class="alert alert-danger hidden upload-errors"></div>

	<div class="processing-files hidden">
		<span class="fa fa-spinner fa-spin fa-3x fa-fw"></span>
	</div>

	<div class="files-list">
		<?php
		if ($post_id) {
			$meta_field_value = get_post_meta($post_id, $meta_field_name, true);
			$html = '';
			
			if ($meta_field_value) {
				
				if ($supposed_file_type === 'image') {
					foreach ($meta_field_value as $attach_id => $image_link) {
						$image_thumb = wp_get_attachment_image_src($attach_id);

						$html .= '<div class="single-image">';
							$html .= '<div class="image-wrapper">';
								$html .= '<a href="' . $image_link . '" rel="prettyPhoto">';
									$html .= '<img src="' . $image_thumb[0] . '" />';
								$html .= '</a>';
								$html .= '<input type="hidden" name="' . $meta_field_name . '[' . $attach_id . ']" value="' . esc_url($image_link) . '">';
								$html .= '<a href="javascript:void(0)" class="remove"></a>';
							$html .= '</div>';
						$html .= '</div>';
					}
				}

				else {
					foreach ($meta_field_value as $attach_id => $attach_link) {
						$html .= '<div class="single-file">';
							$html .= '<div class="file-wrapper">';
								$html .= '<span class="attachment-title">' . get_the_title($attach_id) . '</span>';
								$html .= '<input type="hidden" name="' . $meta_field_name . '[' . $attach_id . ']" value="' . esc_url($attach_link) . '">';
								$html .= '<a href="javascript:void(0)" class="remove"></a>';
							$html .= '</div>';
						$html .= '</div>';
					}
				}

			}

			echo $html;
		}
		?>
	</div>
</div>