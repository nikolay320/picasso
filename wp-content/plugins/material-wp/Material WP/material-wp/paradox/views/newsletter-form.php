<!-- Begin MailChimp Signup Form -->
<div id="cchbc-form" style="width: 100%;">

    <p class="description"><?php _e("If you like our products and support, feel free to join our <strong>CodeCanyon Happy Costumer Club</strong>, where we will share news about upcoming products and versions of plugins, discuss new features, provide discounts and much more!", $this->td); ?></p>

    <label for="mce-EMAIL"><?php _e("You're just a step away from joining the club!", $this->td); ?></label>
    <a target="_blank" href="http://eepurl.com/bCULcb" class="button"><?php _e('Join the Club', $this->td); ?></a>

</div>

<style type="text/css">
  #cchbc-form {
    box-sizing: border-box;
    width: 100%;
    padding-right: 300px;
    background: right center no-repeat url('<?php echo $this->getAsset('cchbc-logo.png', 'img', true); ?>');
    background-size: auto 100%;
  }
  
  #cchbc-form p.description {
    margin-bottom: 20px;
  }
  
  #cchbc-form label {
    text-transform: uppercase;
    display: block;
    font-size: 90%;
    margin-bottom: 5px;
    font-weight: bold;
  }
  
  #cchbc-form input {
    padding: 5px 10px;
    margin-bottom: 5px;
  }
</style>

<!--End mc_embed_signup-->