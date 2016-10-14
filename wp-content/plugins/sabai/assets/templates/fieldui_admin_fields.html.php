<noscript>
    <div class="sabai-alert sabai-alert-danger"><?php echo __('This page requires JavaScript enabled in your browser.', 'sabai');?></div>
</noscript>
<form method="post" action="<?php echo $form_submit_path;?>" id="sabai-fieldui">
<div class="sabai-clearfix sabai-fieldui">
    <div id="sabai-fieldui-active-wrap">
        <div id="sabai-fieldui-active">
            <div class="sabai-fieldui-fields">
<?php foreach ($fields as $field):?>
<?php   if (!isset($field_types[$field->getFieldType()]) || !$field->getFieldWidget()) continue;?>
<?php   if (isset($existing_fields[$field->getFieldType()][$field->getFieldName()])): $hidden_existing_fields[$field->getFieldType()][$field->getFieldName()] = $existing_fields[$field->getFieldType()][$field->getFieldName()]; unset($existing_fields[$field->getFieldType()][$field->getFieldName()]); endif;?>
<?php   if (false === $field_preview = $this->FieldUI_PreviewWidget($field)) continue;?>
                <div id="sabai-fieldui-field<?php echo $field->getFieldId();?>" class="sabai-fieldui-field sabai-fieldui-field-type-<?php echo str_replace('_', '-', $field->getFieldType());?>" data-field-type="<?php echo $field->getFieldType();?>" data-field-type-normalized="<?php echo str_replace('_', '-', $field->getFieldType());?>" data-field-name="<?php echo $field->getFieldName();?>">
                    <div class="sabai-fieldui-field-info">
						<div class="sabai-fieldui-field-control">
                            <a href="<?php echo $form_edit_field_url;?>" class="sabai-fieldui-field-edit" data-modal-title="<?php echo Sabai::h($field->getFieldLabel()) . ' - ' . $field->getFieldName();?>" title="<?php echo __('Edit field', 'sabai');?>"><i class="fa fa-lg fa-cog"></i></a><?php if ($field->isCustomField()):?>&nbsp;<a href="#" class="sabai-fieldui-field-delete" title="<?php echo __('Delete field', 'sabai');?>"><i class="fa fa-lg fa-trash-o"></i></a><?php endif;?>
                        </div>
                        <div class="sabai-fieldui-field-title"><?php echo Sabai::h($field->getFieldLabel()) . ' - ' . $field->getFieldName();?></div>
                    </div>                  
                    <div class="sabai-fieldui-field-preview"><?php echo $field_preview;?></div>
                    <input class="sabai-fieldui-field-id" type="hidden" name="fields[]" value="<?php echo $field->getFieldId();?>" />
                </div>
<?php endforeach;?>
            </div>
            <?php echo $this->TokenHtml('fieldui_admin_submit_fields');?>
            <div style="clear:both;"></div>
        </div>
    </div>
    <div id="sabai-fieldui-available-wrap">
        <div class="sabai-fieldui-available">
            <div class="sabai-fieldui-title">
                <div class="sabai-fieldui-control">
                    <a href="#" class="sabai-fieldui-toggle"><i class="fa fa-caret-up"></i></a>
                </div>
                <div class="sabai-fieldui-label"><?php echo __('Available Fields', 'sabai');?></div>
            </div>    
            <div class="sabai-fieldui-fields sabai-clearfix">
<?php foreach ($field_types as $field_type): if (!$field_type['creatable']) continue;?>
                <a href="<?php echo $form_create_field_url;?>" data-field-type="<?php echo $field_type['type'];?>" class="sabai-btn sabai-btn-default"><?php Sabai::_h($field_type['label']);?></a>
<?php endforeach;?>
            </div>
        </div>
<?php if (!empty($existing_fields)):?>
<?php   foreach ($existing_fields as $existing_field_type => $_existing_fields):?>
<?php     if ((!$field_type = @$field_types[$existing_field_type])) continue;?>
        <div class="sabai-fieldui-available" id="sabai-fieldui-existing-fields-<?php echo str_replace('_', '-', $existing_field_type);?>"<?php if (empty($_existing_fields)):?> style="display:none;"<?php endif;?>>
            <div class="sabai-fieldui-title">
                <div class="sabai-fieldui-control">
                    <a href="#" class="sabai-fieldui-toggle"><i class="fa fa-caret-down"></i></a>
                </div>
                <div class="sabai-fieldui-label"><?php printf(__('Existing Fields (%s)', 'sabai'), Sabai::h($field_type['label']));?></div>
            </div>    
            <div class="sabai-fieldui-fields sabai-clearfix" style="display:none;">
<?php foreach ($_existing_fields as $existing_field_name => $existing_field):?>
                <a href="<?php echo $form_create_field_url;?>" data-field-type="<?php echo $existing_field->getFieldType();?>" data-field-name="<?php echo $existing_field_name;?>" class="sabai-btn sabai-btn-default"><?php Sabai::_h($existing_field->getFieldLabel());?></a>
<?php endforeach;?>
<?php if (!empty($hidden_existing_fields[$existing_field_type])):?>
<?php   foreach ($hidden_existing_fields[$existing_field_type] as $existing_field_name => $existing_field):?>
                <a href="<?php echo $form_create_field_url;?>" data-field-type="<?php echo $existing_field->getFieldType();?>" data-field-name="<?php echo $existing_field_name;?>" class="sabai-btn sabai-btn-default" style="display:none !important;"><?php Sabai::_h($existing_field->getFieldLabel());?></a>
<?php   endforeach;?>
<?php endif;?>
            </div>
        </div>
<?php   endforeach;?>
<?php endif;?>
    </div>
</div>
</form>
<div class="sabai-fieldui-field" id="sabai-fieldui-field" style="display:none;">
    <div class="sabai-fieldui-field-info">
        <div class="sabai-fieldui-field-control">
            <a href="<?php echo $form_edit_field_url;?>" class="sabai-fieldui-field-edit" data-modal-title="" title="<?php echo __('Edit field', 'sabai');?>"><i class="fa fa-lg fa-cog"></i></a>&nbsp;<a href="#" class="sabai-fieldui-field-delete" title="<?php echo __('Delete field', 'sabai');?>"><i class="fa fa-lg fa-trash-o"></i></a>
        </div>
        <div class="sabai-fieldui-field-title"></div>
    </div>
    <div class="sabai-fieldui-field-preview"></div>
    <input class="sabai-fieldui-field-id" type="hidden" name="fields[]" value="" />
</div>