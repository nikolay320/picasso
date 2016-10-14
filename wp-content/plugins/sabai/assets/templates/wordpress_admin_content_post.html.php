<?php 
$errors = array();
if ($form->hasError()) {
    $errors[] = sprintf(__('There was an error processing the form.', 'sabai'));
}
list($form_arr, $form_js) = $this->Form_RenderArray($form);

$bundle = $form->settings['#bundle'];
$has_title = isset($form_arr['elements']['content_post_title']['elements']['content_post_title[0]'][0]);
if ($has_title) {
    $title = $form_arr['elements']['content_post_title']['elements']['content_post_title[0]'][0]['value'];
    if ($title_error = @$form_arr['elements']['content_post_title']['elements']['content_post_title[0]'][0]['error']) {
        $errors[] = sprintf(__('%s Title: %s', 'sabai'), Sabai::h($this->Entity_BundleLabel($bundle)), Sabai::h($title_error));
    }
    unset($form_arr['elements']['content_post_title']);
}

$has_body = isset($form_arr['elements']['content_body']['elements']['content_body[0]'][0]);
if ($has_body) {
    $body = $form_arr['elements']['content_body']['elements']['content_body[0]'][0]['value'];
    if ($body_error = @$form_arr['elements']['content_body']['elements']['content_body[0]'][0]['error']) {
        $errors[] = sprintf(__('%s Content: %s', 'sabai'), Sabai::h($this->Entity_BundleLabel($bundle)), Sabai::h($body_error));
    }
    $body_rows = @$form_arr['elements']['content_body']['elements']['content_body[0]'][0]['rows'];
    unset($form_arr['elements']['content_body']);
}

if (!isset($entity) || !$this->Entity_Author($entity)->isAnonymous()) {
    unset($form_arr['elements']['content_guest_author']);
}
$submit_actions = $hidden_values = array();
if (isset($form_arr['elements']['content_post_status'])) {
    $submit_actions[] = array(
        'label' => __('Status:', 'sabai'),
        'markup' => $form_arr['elements']['content_post_status'][0]['html']
    );
    if (isset($form_arr['elements']['content_post_status'][0]['error'])) {
        $errors[] = sprintf(__('Status: %s', 'sabai'), Sabai::h($form_arr['elements']['content_post_status'][0]['error']));
    }
    unset($form_arr['elements']['content_post_status']);
}
if (isset($form_arr['elements']['content_post_published']['elements']['content_post_published[datetime]'][0]['html'])) {
    $submit_actions[] = array(
        'label_markup' => '<i class="fa fa-calendar"></i> ' . __('Published on:', 'sabai'),
        'markup' => $form_arr['elements']['content_post_published']['elements']['content_post_published[datetime]'][0]['html']
            . $form_arr['elements']['content_post_published']['elements']['content_post_published[date]'][0]['html']
    );
    if (isset($form_arr['elements']['content_post_published']['error'])) {
        $errors[] = sprintf(__('Published on: %s', 'sabai'), Sabai::h($form_arr['elements']['content_post_published']['error']));
    }
    unset($form_arr['elements']['content_post_published']);
}
foreach ($form_arr['elements'] as $element) {
    // Some special fields are not contained in a fieldset
    if (isset($element[0])) {
        $element = $element[0];
    }
    if ($element['type'] === 'hidden'
        || strpos($element['name'], '_') === 0
        || ($element['type'] === 'static' && false === strpos($element['html'], '<input') && false === strpos($element['html'], '<select') && false === strpos($element['html'], '<textarea'))
    ) {
        if ($element['type'] === 'hidden') {
            $hidden_values[$element['name']] = $element['value'];
        }
        continue;
    }

    // move taxonomy fields to the sidebar
    if (!empty($bundle->info['taxonomy_terms']) && in_array($element['name'], $bundle->info['taxonomy_terms'])) {	
        $element['position'] = 'side';
    } elseif ($element['name'] === 'content_post_user_id' || $element['name'] === 'content_featured') {
        $element['position'] = 'side';
    } else {
        $element['position'] = 'normal';
    }
    if (!strlen((string)@$element['label'][0])) {
        $label = __('Untitled', 'sabai');
    } else {
        $label = Sabai::h($element['label'][0]);
        unset($element['label'][0]);
    }
    if (!empty($element['required'])) {
        $label .= '<span class="sabai-form-field-required">*</span>';
    }
    if (isset($element['id'])) {
        $id = $element['id'];
        unset($element['id']);
    } else {
        $id = 'sabai_' . $bundle->name . '_postbox-' . $element['name'];
    }
    add_meta_box($id, $label, array($this, 'WordPress_Postbox'), 'sabai_content_addpost', $element['position'], 'default', array($element));
}
add_meta_box(
    'submitdiv', 
    __('Publish', 'sabai'),
    array($this, 'WordPress_Submitbox'),
    'sabai_content_addpost',
    'side',
    'high',
    isset($entity)
        ? array($hidden_values, $this->LinkToModal(__('Move to Trash', 'sabai'), $bundle->getAdminPath() . '/' . $entity->getId() . '/trash', array('width' => 470, 'loadingImage' => false), array('title' => __('Move to Trash', 'sabai'), 'class' => 'submitdelete deletion')), __('Update', 'sabai'), $entity, $submit_actions)
        : array($hidden_values, $this->LinkTo(__('Cancel', 'sabai'), $bundle->getAdminPath(), array(), array('class' => 'submitdelete deletion')), __('Publish', 'sabai'), null, $submit_actions)
);
wp_enqueue_script('postbox');
add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
?>
<?php if (!empty($errors)):?>
<div id="message" class="error">
<?php   foreach ($errors as $error):?>
  <p><strong><?php Sabai::_h($error);?></strong></p>
<?php   endforeach;?>
</div>
<?php endif;?>
<form class="sabai-form" <?php echo $form_arr['attributes'];?>>
<div id="poststuff">
  <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);?>
  <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
  <div id="post-body" class="metabox-holder columns-2">
<?php if ($has_title || $has_body):?>
	<div id="post-body-content">
<?php   if ($has_title):?>
		<div id="titlediv">
			<div id="titlewrap">
				<input type="text" name="content_post_title[0]" size="30" tabindex="1" value="<?php Sabai::_h($title);?>" id="title" autocomplete="off" placeholder="<?php Sabai::_h(__('Enter title here', 'sabai'));?>" />
			</div>
			<div class="inside">
<?php     if (isset($entity) && $entity->getId() && isset($bundle->info['permalink_path'])):?>
				<div id="edit-slug-box">
                    <?php include dirname(__FILE__) . '/wordpress_permalink.html.php';?>
				</div>
<?php     endif;?>
			</div>
			<input type="hidden" id="samplepermalinknonce" name="samplepermalinknonce" value="<?php echo $this->Token('wordpress_permalink');?>">
		</div>
        
<?php   endif;?>
<?php   if ($has_body):?>
		<div id="postdivrich" class="postarea edit-form-section">
		<?php wp_editor($body, 'content', array('textarea_name' => 'content_body[0]', 'textarea_rows' => $body_rows));?>
		</div>
<?php   endif;?>
	</div>
<?php endif;?>
	<div id="postbox-container-1" class="postbox-container">
		<?php do_meta_boxes('sabai_content_addpost','side', null); ?>
	</div>    

	<div id="postbox-container-2" class="postbox-container">
		<?php do_meta_boxes('sabai_content_addpost','normal', null);  ?>
		<?php do_meta_boxes('sabai_content_addpost','advanced', null); ?>
	</div>
  </div>
</div>
</form>
<script>
<?php echo $this->WordPress_PostboxJs('sabai_content_addpost');?>
<?php echo implode(PHP_EOL, $form_js);?>
</script>