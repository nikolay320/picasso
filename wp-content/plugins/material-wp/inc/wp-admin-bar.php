@function pick-visible-color($bg, $c1: #222, $c2: #fff) {
  @if (lightness($bg) > 60) {
    @return #222; // Lighter backgorund, return dark color
  } @else {
    @return #fff; // Darker background, return light color
  }
}

// Color of the toolbar
#wpadminbar #wp-toolbar > ul > li > a.ab-item,
#wpadminbar #wp-toolbar > ul > li > a.ab-item .ab-label,
#wpadminbar #wp-toolbar > ul > li > a.ab-item .ab-icon::before,
#wpadminbar #wp-toolbar > ul > li > a.ab-item::before,
#wpadminbar #wp-toolbar > ul > li > span, 
#wpadminbar #wp-toolbar > ul > li > div,
#wpadminbar #adminbarsearch:before {
  color: pick-visible-color(<?php echo $this->options->getOption('primary-color'); ?>) !important;
}

/**
 *
 * AdminBar
 *
 */
div#wpadminbar {
  height: <?php echo $this->options->getOption('adminbar-height'); ?>px !important;
  line-height: <?php echo $this->options->getOption('adminbar-height'); ?>px !important;
}

div#wpadminbar .ab-item,
div#wpadminbar .quicklinks .ab-empty-item,
div#wpadminbar .quicklinks .ab-empty-item a i,
div#wpadminbar a.ab-item,
div#wpadminbar > #wp-toolbar span.ab-label,
div#wpadminbar > #wp-toolbar span.noticon {
  height: <?php echo $this->options->getOption('adminbar-height'); ?>px !important;
  line-height: <?php echo $this->options->getOption('adminbar-height'); ?>px !important;
}

#wpadminbar .ab-icon,
#wpadminbar .ab-icon:before,
#wpadminbar .ab-item:before,
#wpadminbar .ab-item:after {
  height: <?php echo $this->options->getOption('adminbar-height'); ?>px !important;
  line-height: <?php echo $this->options->getOption('adminbar-height'); ?>px !important;
}

img.material-wp-logo {
  max-height: <?php echo $this->options->getOption('adminbar-height'); ?>px-10px;
}

li.force-mdi i {
  line-height: <?php echo $this->options->getOption('adminbar-height'); ?>px !important;
}

// Adaptations 
div#wpadminbar .ab-icon:before,
div#wpadminbar .ab-item:before,
div#wpadminbar #adminbarsearch:before, 
div#wpadminbar input#adminbar-search:focus {
  line-height: <?php echo $this->options->getOption('adminbar-height'); ?>px !important;
}

div#wpadminbar input#adminbar-search:focus {
  height: <?php echo $this->options->getOption('adminbar-height'); ?>px !important;
}

/**
 * Height of menu items
 */
#wpadminbar .ab-sub-wrapper .ab-item {
  height: <?php echo $this->options->getOption('adminbar-subitem-height'); ?>px !important;
  line-height: <?php echo $this->options->getOption('adminbar-subitem-height'); ?>px !important;
}
