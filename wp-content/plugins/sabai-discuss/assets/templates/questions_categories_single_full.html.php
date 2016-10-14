<div id="<?php echo $id;?>" class="<?php echo $class;?> sabai-clearfix">
<?php if ($body = $this->Entity_RenderField($entity, 'taxonomy_body')):?>
    <div class="sabai-questions-body">
        <?php echo $body;?>
    </div>
<?php endif;?>
    <div class="sabai-questions-custom-fields">
        <?php $this->displayTemplate('questions_custom_fields', array('entity' => $entity));?>
    </div>
<?php if ($count = count((array)@$entity->data['child_terms'])): $categories = $this->SliceArray($entity->data['child_terms'], 2);?>
    <div class="sabai-questions-categories">
        <div class="sabai-row">
<?php   foreach ($categories as $row => $columns):?>
            <div class="sabai-col-md-6">
<?php     foreach ($columns as $category):?>
                <div class="sabai-questions-category">
<?php       if ($questions_count = (int)@$category->data['content_count']['questions']):?>
                    <?php printf(__('%s (%d)', 'sabai-discuss'), $this->Entity_Permalink($category), $questions_count);?>
<?php       else:?>
                    <?php echo $this->Entity_Permalink($category);?>
<?php       endif;?>
                    <?php echo $this->Entity_RenderField($category, 'taxonomy_body', 'summary');?>
                </div>
<?php     endforeach;?>
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