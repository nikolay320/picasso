<?php

/**
 * Mount Feature List
 */
$features = array(
	// Hide Menus
	array(
    'title' => __('Hide any WordPress Dashboard menu or submenu.', $this->textDomain),
    'text'  => __('Sometimes we don\'t want the final user or users from other roles to have access to some of WordPress functionality. WP Admin Menu Manager aims to make it easier for you to hide those items from them.', $this->textDomain),
    'image' => $this->getAsset('feature-one.png'),
    'icon'  => 'dashicons dashicons-lock',
	),

  // Rename Menus
  array(
    'title' => __('Rename any menu and submenu as well!', $this->textDomain),
    'text'  => __('Other times all we want is the ability to change the labels of the menus and submenus. Now it\'s possible to do so with just one simple step, using WP Admin Menu Manager.', $this->textDomain),
    'image' => $this->getAsset('feature-two.png'),
    'icon'  => 'dashicons dashicons-editor-spellcheck',
  ),

  // Reorder Menus
  array(
    'title' => __('Reorder the menus too!', $this->textDomain),
    'text'  => __('Change the menu items order, as well as submenus, is also possible. And it is as simple as dragging and dropping them on the desired order!', $this->textDomain),
    'image' => $this->getAsset('feature-three.png'),
    'icon'  => 'dashicons dashicons-sort',
  ),


);

/**
 * Action Button
 */
$actionButton = array(
  'title' => __('Now that you know everything you can do using this plugin, click bellow to start! And please don\'t forgot to rate the plugin, if you like it.', $this->textDomain),
  'url'   => admin_url('/edit.php?post_type=amm'),
  'text'  => __('I want to start!', $this->textDomain),
);

?>

<div id="about-732">

<?php foreach ($features as $feature) : ?>
  <div class="feature-block-732">
    
  <?php if ($feature['image']) : ?>
    <div class="feature-block-image-732">
      <?php echo ($feature['icon']) ? "<i class='{$feature['icon']}'></i> " : ''; ?>
      <img src="<?php echo $feature['image']; ?>" alt="<?php echo $feature['title']; ?>">
    </div>
  <?php endif; ?>

  <div class="feature-block-text-732">
    <h2><?php echo $feature['title']; ?></h2>
    <p><?php echo $feature['text']; ?></p>
  </div>

  </div>
<?php endforeach; ?>

  <div class="about-cta-732">
    <p><?php echo $actionButton['title']; ?></p>
    <p><a class="button button-primary button-hero" href="<?php echo $actionButton['url']; ?>"><?php echo $actionButton['text']; ?></a></p>
  </div>

</div>


