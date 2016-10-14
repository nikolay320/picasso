<?php

add_filter('un_window_class', 'un_detect_browsers');

function usernoise_url($path = '/'){
  return plugins_url() . '/' . USERNOISE_DIR . $path;
}

function usernoise_path($path){
  return dirname(USERNOISE_MAIN) . $path;
}

function un_ajax_url($action = null){
  return admin_url('admin-ajax.php') . "?action=un_$action";
}

function un_element_class($filter, $default = null){
  $classes = array();
  if ($default){
    if (is_array($default))
      $classes = array_merge($classes, $default);
    else
      $classes []= $default;
  }
  $classes = apply_filters($filter, $classes);
  if (empty($classes))
    return;
  echo 'class="' . esc_attr(join(' ', $classes)) . '" ';
}

function un_element_style($filter, $default = null){
  $style = array();
  if ($default){
    if (is_array($default))
      $style = array_merge($style, $default);
    else
      $style []= $default;
  }
  $style = apply_filters($filter, $style);
  if (empty($style))
    return;
  echo 'style="' . esc_attr(join('; ', $style)) . '" ';
}


function un_window_class(){
  un_element_class('un_window_class');
}

function un_feedback_has_author($id){
  $email = get_post_meta($id, '_email', true);
  $user = get_post_meta($id, '_author', true);
  $name = get_post_meta($id, '_name', true);
  return $user || $email || $name;
}

function un_feedback_has_name($id){
  return get_post_meta($id, '_name', true);
}

function un_feedback_author_email($id){
  $email = get_post_meta($id, '_email', true);
  $user = get_post_meta($id, '_author', true);
  if (!$email && $user){
    $user = get_user_by('id', $user);
    $email = $user->user_email;
  }
  return $email;
}

function un_feedback_author_link($id){
  global $un_h;
  $email = get_post_meta($id, '_email', true);
  $user = get_post_meta($id, '_author', true);
  $name = get_post_meta($id, '_name', true);
  if (!$user) {
    echo esc_html(get_post_meta($id, '_name', true)) . "<br>";
  }
  if ($email){
    $un_h->tag('a', array('href' => 'mailto:' . esc_html($email)), esc_html($email) );
  }
  if ($name) {
    echo " " . esc_html($name) . " ";
  }
  if ($user){
    if ($email){
      echo (' ' . __('or', 'usernoise') . ' ');
    }
    $user_object = get_user_by('id', $user);
    if ($user_object){
      $un_h->tag('a', array('href' =>
        admin_url('user-edit.php?user_id=' . $user .
          '_wp_http_referer=' . admin_url('post.php?post=' . $id . '&action=edit'))),
          esc_html(get_userdata($user)->display_name));
    }
  }
}

function un_feedback_author_name($id){
  $email = get_post_meta($id, '_email', true);
  $user = get_post_meta($id, '_author', true);
  $name = get_post_meta($id, '_name', true);
  if ($name){
    return $name;
  }
  if ($user){
    return get_userdata($user)->display_name;
  }
  return preg_replace('/@.*/', '', $email);
}

function un_get_feedback_type_span($id, $show_text = true){
  global $un_h;
  if($type = wp_get_post_terms($id, FEEDBACK_TYPE)){
    $img = $un_h->_tag('i', array('class' => un_get_term_meta($type[0]->term_id, 'icon')));
    return $img . ($show_text ?  "&nbsp;" . __(esc_html($type[0]->name), 'usernoise') : '');
  }
  return null;
}

function un_button_style(){
  un_element_style('un-button_style');
}

function un_option_or_text($option_name, $default_text){
  $text = un_get_option($option_name);
  if (empty($text))
    echo $default_text;
  echo $text;
}

function un_feedback_email_placeholder(){
  if (!is_user_logged_in())
    return __(un_get_option(UN_FEEDBACK_EMAIL_PLACEHOLDER), 'usernoise');
  $current_user = wp_get_current_user();
  return $current_user->user_email;
}
function un_feedback_user_name_placeholder(){
  if (!is_user_logged_in())
    return __(un_get_option(UN_FEEDBACK_NAME_PLACEHOLDER), 'usernoise');
  $current_user = wp_get_current_user();
  return $current_user->display_name;
}
function un_feedback_button_text(){
  un_option_or_text(UN_FEEDBACK_BUTTON_TEXT, __('Feedback', 'usernoise'));
}

function un_submit_feedback_button_text(){
  un_option_or_text(UN_SUBMIT_FEEDBACK_BUTTON_TEXT, __('Submit feedback', 'usernoise'));
}

function un_feedback_form_text(){
  un_option_or_text(UN_FEEDBACK_FORM_TEXT,
    __('Please tell us what do you think, any kind of feedback is highly appreciated.', 'usernoise'));
}

function capture_html($file){
  ob_start();
  require($file);
  return ob_get_clean();
}

function un_detect_browsers($classes){
  $old = false;
  if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0') !== false){
    $classes []= 'ie7';
    $old = true;
  }
  if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8') !== false){
    $classes []= 'ie8';
    $old = true;
  }
  if (!$old){
    $classes []= 'css3';
  }
  return $classes;
}


add_filter('un_window_class', 'un_filter_set_window_font_class');
function un_filter_set_window_font_class($classes){
  $classes []= sanitize_title(un_get_option(UN_USE_FONT));
  return $classes;
}

function un_button_class(){
  if ($option = un_get_option(UN_FEEDBACK_BUTTON_POSITION))
    $classes []= "un-" . $option;
  if (empty($classes))
    $classes []= 'un-left';
  if (un_get_option(UN_FEEDBACK_BUTTON_SHOW_BORDER))
    $classes []= 'un-has-border';
  return apply_filters('un_button_class', $classes);
}

function un_get_icons(){
  $data = explode("\n", file_get_contents(usernoise_path('/inc/icons.txt')));
  foreach($data as $icon){
    $icon = trim($icon);
    $objects []= array('icon' => $icon, 'label' => $icon, 'data-icon' => $icon);
  }
  return $objects;
}

function _un_get_the_feedback_status(){
  global  $un_model, $id;
  return  $un_model->get_feedback_status_name($id);
}

function _un_get_the_feedback_status_slug(){
  global  $un_model, $id;
  return  $un_model->get_feedback_status($id);
}


function _un_get_the_feedback_likes(){
  global $id;
  if ($likes = get_post_meta($id, '_likes', true))
    return $likes;
  return 0;
}


function _un_human_time_diff($from, $to = ''){
  if (is_string($from)){
    $from = strtotime($from);
  }
  if ( empty( $to ) )
    $to = time();
  if (class_exists('DateTime') && method_exists('DateTime', 'diff')){
    $from_date = new DateTime("@{$from}");
    $to_date = new DateTime("@{$to}");
    $diff = $to_date->diff($from_date);
    if ($diff->d < 7){
      return human_time_diff($from, $to);
    } elseif ($diff->m < 1){
      return sprintf(_n('%s week', '%s weeks', (int)round($diff->d / 7), 'usernoise'), (int)round($diff->d / 7));
    } elseif ($diff->m < 12) {
      return sprintf(_n('%s month', '%s months', $diff->m, 'usernoise'), $diff->m);
    } else {
      return sprintf(_n('%s year', '%s years', $diff->y, 'usernoise'), $diff->y);
    }
  }
  return human_time_diff($from, $to);
}

function un_get_feedback_types_for_form(){
  $types = get_terms(FEEDBACK_TYPE, array('un_orderby_meta' => 'position', 'hide_empty' => false));
  $result = array();
  foreach($types as $type){
    $result[$type->slug] = $type->name;
  }
  return $result;
}

function un_get_default_feedback_type(){
  $types = un_get_feedback_types_for_form();
  $keys = array_keys($types);
  return count($keys) ? $keys[0] : null;
}

function un_get_form_fields($options){
  $fields = array();
  $current_user = wp_get_current_user();
  if (un_get_option(UN_FEEDBACK_FORM_SHOW_EMAIL)){
    $fields['email'] = array(
      'type' => 'email',
      'label' => __('Email address', 'usernoise'),
      'placeholder' => !$options['external'] && is_user_logged_in() ? $current_user->user_email : __('you@example.com', 'usernoise'),
      'validators' => !$options['external'] && is_user_logged_in() ? array('email') : array('required', 'email')
      );
  }
  if (un_get_option(UN_FEEDBACK_FORM_SHOW_NAME)){
    $fields['name']  = array(
      'type' => 'text',
      'label' => __('Your name', 'usernoise'),
      'placeholder' => !$options['external'] && is_user_logged_in() ? $current_user->display_name : __('John Smith', 'usernoise'),
      'validators' => !$options['external'] && is_user_logged_in() ? array() : array('required')
    );
  }
  if (un_get_option(UN_FEEDBACK_FORM_SHOW_SUMMARY)){
    $fields['summary'] = array(
      'type' => 'text',
      'label' => __('Summary', 'usernoise'),
      'placeholder' => __('Short summary', 'usernoise'),
      'validators' => array('required')
    );
  }
  if (un_get_option(UN_FEEDBACK_FORM_SHOW_TYPE)){
    $fields['type'] = array(
        'type' => 'dropdown',
        'label' => __('Feedback type', 'usernoise'),
        'default' => null,
        'default_text' =>__('Please select', 'usernoise'),
        'options' => un_get_feedback_types_for_form(),
        'validators' => array('required')
    );
  }
  return apply_filters('un_form_fields', $fields);
}

function un_config($options) {
  global $un_model;
  return array(
    'config' => array(
      'loggedIn' => get_current_user_id() != false,
      'onlyLoggedIn' => get_option(UN_ONLY_REGISTERED) === "1",
      'button' => array(
        'enabled' => get_option(UN_SHOW_FEEDBACK_BUTTON, true),
        'disableOnMobiles' => un_get_option(UN_DISABLE_ON_MOBILES),
        'text' => (un_get_option(UN_FEEDBACK_BUTTON_ICON) ? ("<i class='un-button-icon-" . un_get_option(UN_FEEDBACK_BUTTON_ICON) . "'></i>")  : '') . un_get_option(UN_FEEDBACK_BUTTON_TEXT, __('Feedback', 'usernoise')),
        'style' => array(
          "background-color" =>un_get_option(UN_FEEDBACK_BUTTON_COLOR),
          "color" => un_get_option(UN_FEEDBACK_BUTTON_TEXT_COLOR)
        ),
        'class' => implode(' ', un_button_class()),
        ),
      'likes' => $options['external'] ? null : $un_model->extract_likes(),
      'urls' => array(
        "config" => array(
          'get' => un_ajax_url('config_get')
        ),
        "feedback" => array(
          'post' => un_ajax_url('feedback_post'),
          'get' => un_ajax_url('feedback_get'),
          'like' => un_ajax_url('feedback_like'),
          'get_id' => un_ajax_url('feedback_get_id'),
          'all' => un_get_option(UN_SHOW_ALL_FEEDBACKS_LINK)
          ),
        "comment" => array(
          'post' => un_ajax_url('comment_post')
          ),
        "usernoise"         => usernoise_url(),
        "html2canvasproxy" => usernoise_url() . "proxy.php"
      ),
      'form' => array('fields' => un_get_form_fields($options)),
      'comments' => array(
        'enabled' => un_get_option(UN_COMMENTS_ENABLE, true) && (!get_option('comment_registration', true) || get_current_user_id() != false)
      ),
      'screenshot' => array(
        'enable' => get_option(UN_FEEDBACK_FORM_SCREENSHOT_ENABLE, true),
        'format' => get_option(UN_FEEDBACK_FORM_SCREENSHOT_FORMAT, 'png'),
        'quality' => get_option(UN_FEEDBACK_FORM_SCREENSHOT_QUALITY, 0.7)
      )
    ),
    'i18n' => array(
        'Leave a feedback' => __('Feedback', 'usernoise'),
        'Enter your feedback here' => __('Enter your feedback here', 'usernoise'),
        'Next' => __('Next', 'usernoise'),
        'Taking screenshot' => __('Taking screenshot', 'usernoise'),
        'Take a screenshot' => __('Take a screenshot', 'usernoise'),
        'screenshot.png' => __('screenshot.png', 'usernoise'),
        'Cancel' => __('Cancel', 'usernoise'),
        'Add some details' => __('Details', 'usernoise'),
        'Back' => __('Back', 'usernoise'),
        'Submit' => __('Submit', 'usernoise'),
        'Submitting' => __('Submitting', 'usernoise'),
        'Error sending feedback' => __('Error sending feedback', 'usernoise'),
        'Close' => __('Close', 'usernoise'),
        'OKText' => __('Your feedback was submitted successfully', 'usernoise'),
        'Done' => __('Done', 'usernoise'),
        'Please enter a valid email address' => __('Please enter a valid email address', 'usernoise'),
        'This field is required' => __('This field is required', 'usernoise'),
        'My feedback' => __('My feedback', 'usernoise'),
        'No feedback matching the criteria' => __('No feedback matching the criteria', 'usernoise'),
        'Comments:' => __('Comments:', 'usernoise'),
        'Leave a comment'=> __('Leave a comment', 'usernoise'),
        'Like' => __('Like', 'usernoise'),
        'Your email' => __('Your email', 'usernoise'),
        'Your name' => __('Your name', 'usernoise'),
        'Comment text' => __('Comment text', 'usernoise'),
        'All feedback' => __('All feedback', 'usernoise'),
        'Your comment is being approved now' => __('Your comment is being approved now', 'usernoise'),
        'See what others a saying' => __('See what others a saying', 'usernoise'),
    ));
}

function un_script($options = array()){
  global $un_model;
  $options = wp_parse_args($options, array('external' => false));
  ?>
  <script type="text/javascript">
    var usernoise = <?php echo json_encode(un_config($options)) ?>;
    </script>
    <?php
}
