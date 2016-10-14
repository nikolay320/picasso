<div id="<?php echo $id;?>" class="<?php echo $class;?> sabai-clearfix">
<?php if ($body = $this->Entity_RenderField($entity, 'taxonomy_body')):?>
    <div class="sabai-questions-body">
        <?php echo $body;?>
    </div>
<?php endif;?>
    <div class="sabai-questions-custom-fields">
        <?php $this->displayTemplate('questions_custom_fields', array('entity' => $entity));?>
    </div>
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