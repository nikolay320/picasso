<?php if (!$IS_EMBED) $this->Action('directory_before_single_category', array($bundle->addon, $entity));?>
<?php $display_settings = $this->Config($this->Entity_Addon($entity), 'display');?>
<div id="<?php echo $id;?>" class="<?php echo $class;?> sabai-clearfix">
<?php if ($body = $this->Entity_RenderField($entity, 'taxonomy_body')):?>
    <div class="sabai-directory-body">
        <?php echo $body;?>
    </div>
<?php endif;?>
    <div class="sabai-directory-custom-fields">
        <?php $this->displayTemplate(array('directory_category_custom_fields', 'directory_custom_fields'), array('entity' => $entity));?>
    </div>
<?php if ($count = count((array)@$entity->data['child_terms'])): $categories = $this->SliceArray($entity->data['child_terms'], $display_settings['category_columns']);?>
    <div class="sabai-directory-categories">
        <div class="sabai-row">
<?php   foreach ($categories as $row => $columns):?>
            <div class="sabai-col-sm-<?php echo intval(12 / $display_settings['category_columns']);?>">
                <ul class="sabai-directory-category-children">
<?php     foreach ($columns as $category):?>
<?php       if ($listing_count = (int)@$category->data['content_count']['directory_listing']):?>
                <li><?php printf(__('%s (%d)', 'sabai-directory'), $this->Entity_Permalink($category, array('thumbnail' => 'directory_thumbnail')), $listing_count);?></li>
<?php       else:?>
                <li><?php echo $this->Entity_Permalink($category, array('thumbnail' => 'directory_thumbnail'));?></li>
<?php       endif;?>
<?php     endforeach;?>
                </ul>
            </div>
<?php   endforeach;?>
        </div>
    </div>
<?php endif;?>
<?php if (!empty($buttons)):?>
    <div class="sabai-navigation sabai-navigation-bottom sabai-entity-buttons">
        <?php echo $this->ButtonToolbar($buttons);?>
    </div>
<?php endif;?>
<?php if (!empty($links)):?>
    <div class="sabai-entity-links">
        <?php echo $this->ButtonLinks($links);?>
    </div>
<?php endif;?>
</div>
<?php if (!$IS_EMBED) $this->Action('directory_after_single_category', array($bundle->addon, $entity));?>