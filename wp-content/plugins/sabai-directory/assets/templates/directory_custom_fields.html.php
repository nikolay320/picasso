<?php if (!isset($view)) $view = 'default'; foreach ($this->Entity_CustomFields($entity) as $field):?>
<?php   if ($field_output = $this->Entity_RenderField($entity, $field, $view)):?>
    <div class="sabai-directory-field sabai-field-type-<?php echo str_replace('_', '-', $field->getFieldType());?> sabai-field-name-<?php echo str_replace('_', '-', $field->getFieldName());?> sabai-clearfix">
        <div class="sabai-field-label"><?php echo $field->getFieldTitle($view);?></div>
        <div class="sabai-field-value"><?php echo $field_output;?></div>
    </div>
<?php   endif;?>
<?php endforeach;?>
