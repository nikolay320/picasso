<?php

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

// We need to do the same thing for our log
$logo = $this->options->getOption('custom-logo');

// We need to check if logo is just an id
$logo = is_numeric($logo) ? $this->getAttachmentURL($logo) : $logo;

?>
<style type="text/css">
  
  /* Body BG */
  body {
    background-image: url(<?php echo $customBG; ?>);
    background-position: center center;
    background-size: cover;
  }
  
  /* Changing the logo */
  .login h1 a {
    background-image: url(<?php echo $logo; ?>);
  }
  
  <?php if (!$this->options->getOption('back-to-blog')) : ?>
  /** If the user chose to hide the back to blog, we hide it */
  #backtoblog {
    display: none;
  }
  <?php endif; ?>
  
</style>

<?php
/**
 * As request for some of our buyers, we now will give the option to 
 * change the link in the login screen
 */
?>
<script type="text/javascript">
  window.onload = function() {

    <?php 
    $url = $this->options->getOption('logo-link');
    $link = empty($url) ? site_url() : $url;
    ?>

    var url = '<?php echo $link; ?>';

    // Replace URL and title
    document.querySelector('.login h1 a').href = url;
    document.querySelector('.login h1 a').title = '';

  };
</script>