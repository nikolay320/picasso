<?php if (!empty($fields)):?>
<div class="sabai-directory-tab sabai-directory-tab-<?php echo $tab_name;?>">
    <div class="sabai-directory-custom-fields">
<?php   foreach ($fields as $field_name => $field):?>
        <div class="sabai-directory-field sabai-field-type-<?php echo str_replace('_', '-', $field['type']);?> sabai-field-name-<?php echo str_replace('_', '-', $field_name);?> sabai-clearfix">
            <div class="sabai-field-label"><?php Sabai::_h($field['title']);?></div>
            <div class="sabai-field-value"><?php echo $field['output'];?></div>
        </div>
<?php   endforeach;?>
    </div>
</div>
<?php endif;?>