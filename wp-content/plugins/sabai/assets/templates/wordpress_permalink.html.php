<?php 
$slug = $entity->getSlug();
$slug_trimmed = mb_strwidth($slug) > 30 ? mb_strimwidth($slug, 0, 22, '&hellip;') . substr($slug, strlen(mb_strimwidth($slug, 0, mb_strwidth($slug) - 14))) : $slug;
?>
<strong><?php echo __('Permalink:', 'sabai');?></strong>
<span id="sample-permalink"><?php echo strlen($slug) ? dirname($this->Entity_Url($entity)) : rtrim($this->Entity_Url($entity), '/');?>/<span id="editable-post-name" title="<?php echo __('Click to edit this part of the permalink', 'sabai');?>"><?php echo $slug_trimmed;?></span>/</span>
&lrm;<span id="edit-slug-buttons"><a href="#post_name" class="edit-slug button button-small hide-if-no-js" onclick="editPermalink(<?php echo $entity->getId();?>); return false;"><?php echo __('Edit', 'sabai');?></a></span>
<span id="editable-post-name-full"><?php echo $slug;?></span>