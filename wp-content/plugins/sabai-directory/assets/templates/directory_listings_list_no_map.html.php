<?php if (!empty($entities)):?>
<?php   foreach ($entities as $entity):?>
<?php     $this->displayTemplate($entity['entity']->getBundleType() . '_single_' . $entity['display_mode'], array('address_weight' => @$entity['entity']->data['weight'], 'is_mile' => $settings['is_mile']) + $entity);?>
<?php   endforeach;?>
<?php else:?>
<p><?php echo __('No entries were found.', 'sabai-directory');?></p>
<?php endif;?>

