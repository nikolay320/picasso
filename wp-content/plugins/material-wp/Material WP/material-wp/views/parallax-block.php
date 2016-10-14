<?php 

// Get Users
$user = wp_get_current_user(); 

// Select which BG to use
$customBG = $this->options->getOption('custom-bg');

// Check if it set
if (empty($customBG)) {
  
  // Set to default one selected
  $customBG = $this->getAsset('bgs/'.$this->options->getOption('default-bg').'.jpg');
  
} // end if;

// Else 
else {
  $customBG = (is_numeric($customBG)) ? $this->getAttachmentURL($customBG) : $customBG;
}

/*
 * Hook for developers to add new background images in the block
 * new in 0.0.17
 */
$customBG = apply_filters('mwp_parallax_bg', $customBG);

?>

<div id="parallax-main-block" class="parallax-container">
  <div class="parallax">
    
    <?php if ($this->options->getOption('parallax-options') !== 'solid-color') : ?>
      <img class="parallax-img" src="<?php echo $customBG; ?>">
    <?php endif; ?>
    
    <div id="parallax-content">
      <div class="container"></div>
    </div>
    
  </div>
  
  <a href="<?php echo get_edit_user_link($user->ID); ?>" >
    <div id="mwp-user-card" class="tooltiped" data-tooltip="<?php _e('Edit your profile', 'material-wp'); ?>" data-position="bottom">
      <div class="user-card-avatar">
        <?php 
        /**
         * Get avatar and remove avatar from classes
         */
        $avatar = get_avatar($user->ID, 60);
        echo str_replace("class='avatar", "class='", $avatar);
        ?>
      </div>
      <div class="user-card-info">
        <div class="user-card-name"><?php echo $user->display_name; ?></div>
        <div class="user-card-email"><?php echo $user->user_email; ?></div>
      </div>
    </div>
  </a>
</div>

<script type="text/javascript">
    jQuery('#adminmenu').attr('title', '<?php echo $this->options->getOption('menu-label'); ?>');
</script>