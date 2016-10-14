<?php
/**
 *
 * LOGIN CUSTOMIZER
 * Adds all the custom functionality related with the custom login customizer panel
 *
 */

// Background Panel
$bg = $this->options->createThemeCustomizerSection(array(
  'name'  => __('Background', 'material-wp'),
  'panel' => __('Material WP Login', 'material-wp'),
));

// Block Panel
$block = $this->options->createThemeCustomizerSection(array(
  'name'  => __('Login Block', 'material-wp'),
  'panel' => __('Material WP Login', 'material-wp'),
));

// Background Panel
$inputs = $this->options->createThemeCustomizerSection(array(
  'name'  => __('Inputs', 'material-wp'),
  'panel' => __('Material WP Login', 'material-wp'),
));

/**
 * Lets start with the block settings options
 */
$block->createOption(array(
  'id'          => 'login-block-padding',
  'name'        => __('Block Padding', 'material-wp'),
  'desc'        => __('Select the padding to be applied to the login block.', 'material-wp'),
  'type'        => 'number',
  'unit'        => 'px',
  'default'     => 20,
  'livepreview' => 'alert("lol");',
));

$block->createOption(array(
  'id'          => 'login-block-background-color',
  'name'        => __('Block Background Color', 'material-wp'),
  'desc'        => __('Select the background color to be applied to the login block.', 'material-wp'),
  'type'        => 'color',
  'default'     => '#fff',
  'livepreview' => '$("#loginform").css("backgroundColor", value); console.log(value);',
));