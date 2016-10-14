@function pick-visible-color($bg, $c1: #222, $c2: #fff, $percentage: 60) {
  @if (lightness($bg) > $percentage) {
    @return $c1; // Lighter backgorund, return dark color
  } @else {
    @return $c2; // Darker background, return light color
  }
}

/* Links */

$mwp-link-color: pick-visible-color(<?php echo $this->options->getOption('primary-color'); ?>, <?php echo $this->options->getOption('accent-color'); ?>, <?php echo $this->options->getOption('primary-color'); ?>, 80);

a {
	color: $mwp-link-color;

	&:hover,
	&:active,
	&:focus {
		color: darken($mwp-link-color, 10%);
	}
}

/* custom Badges */
.badge.new,
#adminmenu .awaiting-mod,
#adminmenu .update-plugins {
  background-color: <?php echo $this->options->getOption('accent-color'); ?> !important;
  color: pick-visible-color(<?php echo $this->options->getOption('accent-color'); ?>);
  
  &.badge.new.no-new {
    min-width: 0 !important;
    padding-left: 5px;
    padding-left: 5px;
  }
  
  &.badge.new.no-new::after {
    content: "" !important;
  }
  
}

/* Core UI */
.wp-core-ui {
  
  .button-primary {
    //@include button( $button-color );
    background-color: <?php echo $this->options->getOption('accent-color'); ?> !important;
    color: pick-visible-color(<?php echo $this->options->getOption('accent-color'); ?>);
    text-shadow: 0 -1px 1px darken(<?php echo $this->options->getOption('accent-color'); ?>, 10%),1px 0 1px darken(<?php echo $this->options->getOption('accent-color'); ?>, 10%),0 1px 1px darken(<?php echo $this->options->getOption('accent-color'); ?>, 10%),-1px 0 1px darken(<?php echo $this->options->getOption('accent-color'); ?>, 10%) !important;
    &:hover,
	&:active,
	&:focus {
      background-color: darken(<?php echo $this->options->getOption('accent-color'); ?>, 10%) !important;
      color: pick-visible-color(<?php echo $this->options->getOption('accent-color'); ?>);
    }
  }

  .wp-ui-primary {
      //color: $text-color;
      background-color: <?php echo $this->options->getOption('accent-color'); ?> !important;
  }
  .wp-ui-text-primary {
      //color: $base-color;
  }

  .wp-ui-highlight {
      //color: $menu-highlight-text;
      background-color: <?php echo $this->options->getOption('accent-color'); ?> !important;
  }
  .wp-ui-text-highlight {
      //color: $menu-highlight-background;
  }

  .wp-ui-notification {
      //color: $menu-bubble-text;
      background-color: <?php echo $this->options->getOption('accent-color'); ?> !important;
  }
  .wp-ui-text-notification {
      //color: $menu-bubble-background;
  }

  .wp-ui-text-icon {
      //color: $menu-icon;
  }
  
}

#wpadminbar.solid, #parallax-main-block .parallax {
    background-color: <?php echo $this->options->getOption('primary-color'); ?> !important;
}

#wpbody-content h2:first-child:not(.nav-tab-wrapper)>a, 
#wpbody-content h1:first-child:not(.nav-tab-wrapper)>a, 
#wpbody-content >.subsubsub:not(.nav-tab-wrapper)>a,
.page-title-action,

// My mail and other plugins ajustment
#wpbody-content h2 .add-new-h2 {
  
  background-color: <?php echo $this->options->getOption('accent-color'); ?> !important;
  
  &:hover,
  &:active,
  &:focus {
    background-color: darken(<?php echo $this->options->getOption('accent-color'); ?>, 10%) !important;
  }
}

// Toggle
.wp-responsive-open #wpadminbar #wp-admin-bar-menu-toggle a {
  background-color: transparent !important;
}

// Parallax block BG
div#parallax-main-block > div.parallax {
  background-color: <?php echo $this->options->getOption('parallax-bg-color'); ?> !important;

  <?php if ($this->options->getOption('parallax-options') == 'parallax') : ?>
  img {opacity: 1;}
  <?php endif; ?>
}

// Ajusts in width and hights
/* Desktops and laptops computers */
@media only screen
and (min-width : 1224px) {
/* Add you style here */
  
  <?php
  
  // We need to do some kickass maths now to discovery which width to attribute to the 
  // Wrapper element now that the user has changed the values
  // The value max is 80%
  // the normal value is 66%
  // We need to progressivily increase the width based on the pixel chossed by the user
  
  $max = 320;
  $min = 180;
  $diff = $max - $min;
  
  // We set our percentages values
  $percentages = array(80, 78.6, 77.2, 75.8, 74.4, 73, 71.6, 70.2, 68.8, 67.4, 66);
  $numGroups = count($percentages);

  // Steps
  $step = $diff / $numGroups;
  
  // Select the porcentage based on the level of "reduction"
  $menuWidth = $this->options->getOption('menu-width');
  
  $menuPercentage = ($menuWidth - $min) / $diff;
  //var_dump($menuPercentage);
  $menuGroup = floor($menuPercentage * 10);
  
  // retrieve the percentage
  $percentage = $percentages[$menuGroup];
  
  ?>
  
  // Now we make the wrap a container
  body:not(.wp-customizer) #wpbody-content,
  #screen-meta-links {
    left: <?php echo $this->options->getOption('menu-width'); ?>px;
    width: <?php echo $percentage; ?>% !important;
  }
  
  // Modal de themes
  .theme-wrap {
    left: <?php echo $this->options->getOption('menu-width'); ?>px !important;
  }

  div.single-theme .theme-wrap {
    left: 0px !important;
  }
  
  // Width of the menu
  #adminmenu {
    width: <?php echo $this->options->getOption('menu-width'); ?>px !important;
  }

  // Height of Parallax Block
  #parallax-main-block {
    height: <?php echo $this->options->getOption('parallax-height'); ?>px !important;
  }

  // Ajust the margin top of the menu
  #adminmenuwrap {
    margin-top: <?php echo $this->options->getOption('parallax-height'); ?>px !important;
  }
  
}

/**
 * Menu Icon Random Colors
 */

<?php 
/**
 * If the user chose to use random colors, use them
 */
if ($this->options->getOption('menu-random-color')) : ?>

// Sorted Icon color
$list: #9c27b0, #795548, #689f38, #db4437, #ff6839, #3f51b5, #303F9F, #00bcd4, #8BC34A, #F44336, #FFA000, #607D8B, #616161, #616161, #7C4DFF, #9c27b0, #795548, #689f38, #db4437, #ff6839, #3f51b5, #303F9F, #00bcd4, #8BC34A, #F44336, #FFA000, #607D8B, #616161, #616161, #7C4DFF, #9c27b0, #795548, #689f38, #db4437, #ff6839, #3f51b5, #303F9F, #00bcd4, #8BC34A, #F44336, #FFA000, #607D8B, #616161, #616161, #7C4DFF;

$index: 1;

@each $color in $list {
  #adminmenu li.menu-top:not(.wp-menu-separator):nth-child(#{$index}) {
    div.wp-menu-image, div.wp-menu-image:before {
      color: nth($list, $index) !important;
    }
  }
  
  $index: $index + 1;
  
}

<?php 
/**
 * Otherwise, use the color he listed
 */
else: ?>

#adminmenu li.menu-top:not(.wp-menu-separator) {
  div.wp-menu-image, div.wp-menu-image:before {
    color: <?php echo $this->options->getOption('menu-custom-color'); ?> !important;
  }
}

<?php endif; ?>

<?php
/**
 * Detect custom sibar plugin to enqueue custom styles
 */
if (is_plugin_active('custom-sidebars/customsidebars.php')) : ?>

.widgets-php div#wpcontent, .widgets-php div#wpfooter {
  margin-right: 0;
}

.widgets-php div.widget-liquid-right {
  top: auto;
  right: auto;
  position: relative;
  background: transparent;
  height: auto !important;
  overflow: hidden;
}

.widgets-php div.widget-liquid-right .scrollbar {
  display: none !important;
}

.widgets-php div.widget-liquid-right .viewport {
  height: auto !important;
}

.widgets-php div.widget-liquid-right .overview {
  border: none;
  position: relative;
}

.widgets-php div.widget-liquid-left {
  width: 40%;
}

<?php endif; ?>

<?php
/**
 * Detect real-media-library plugin to enqueue custom styles
 */
if (is_plugin_active('real-media-library/real-media-library.php')) : ?>

body.wp-admin.upload-php div#wpbody-content {
  float: none;
  box-sizing: border-box;
  padding-left: 260px;
}

div.rml-container {
  position: absolute;
  top: 14px;
  margin-left: 5px;
  left: <?php echo $this->options->getOption('menu-width'); ?>px;
}

<?php endif; ?>

/**
 * menu Admin on the right
 */
@media only screen
and (min-width : 1224px) {
  
  /**
   * Menu on the right CSS
   */
  body.material-wp-menu-right {

    // Menu Change positiom
    #adminmenuwrap {
      right: <?php echo $this->options->getOption('menu-width'); ?>px;
      left: auto !important;
    }
    
    #adminmenu {
      right: 0;
      left: 0;
    }

    // Now we make the wrap a container
    &:not(.wp-customizer) #wpbody-content {
      left: auto;
      float: right;
      right: <?php echo $this->options->getOption('menu-width'); ?>px;
      width: <?php echo $percentage; ?>% !important;
    }

    // Modal de themes
    .theme-wrap {
      right: <?php echo $this->options->getOption('menu-width'); ?>px !important;
      left: 30px !important;
    }
    
    //
    #parallax-main-block #mwp-user-card {
      right: -72px;
      width: <?php echo $this->options->getOption('menu-width'); ?>px;
    }

  }
  /**
   * End of menu on the right
   */
  
}

/**
 * FormCraft
 */
html div.formcraft-css div.options-head {
  left: 0;
  top: <?php echo $this->options->getOption('adminbar-height'); ?>px;
  z-index: 99999;
}

div.formcraft-css div.fields-list-right {
  top: <?php echo $this->options->getOption('adminbar-height') + 40; ?>px;
}

/**
 * Fix Tooltip
 */
.rtl {
  div.material-tooltip {
    left:0; /** fix **/
    top:0;
    will-change: top, left; /** fix **/
  }
}