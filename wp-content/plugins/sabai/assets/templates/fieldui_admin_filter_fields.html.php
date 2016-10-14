<noscript>
    <div class="sabai-alert sabai-alert-danger"><?php echo __('This page requires JavaScript enabled in your browser.', 'sabai');?></div>
</noscript>
<form method="post" action="<?php echo $form_submit_path;?>" id="sabai-fieldui">
<div class="sabai-clearfix sabai-fieldui">
    <div id="sabai-fieldui-active-wrap">
        <div id="sabai-fieldui-active">
<?php for ($row = 1; $row <= $row_count; ++$row):?>
            <div class="sabai-row">
<?php   for ($column = 1; $column <= $column_count; ++$column):?>
                <div class="sabai-fieldui-fields sabai-col-sm-<?php echo intval(12 / $column_count);?>">
<?php     foreach ((array)@$filters[$row][$column] as $filter_id => $filter):?>
<?php       if (!$field = @$fields[$filter['field']]) continue;?>
<?php       if (false === $field_preview = $this->FieldUI_PreviewFilter($field, $filter['filter'])) continue;?>
                    <div id="sabai-fieldui-field<?php echo $filter_id;?>" class="sabai-fieldui-field sabai-fieldui-field-type-<?php echo str_replace('_', '-', $filter['filter']->type);?>">
                        <div class="sabai-fieldui-field-info">
                            <div class="sabai-fieldui-field-control">
                                <a href="<?php echo $form_edit_field_url;?>" class="sabai-fieldui-field-edit" data-modal-title="<?php echo Sabai::h($filter['filter']->getLabel()) . ' - ' . $filter['filter']->name;?>" title="<?php echo __('Edit field', 'sabai');?>"><i class="fa fa-lg fa-cog"></i></a><?php if ($filter['filter']->isCustomFilter()):?>&nbsp;<a href="#" class="sabai-fieldui-field-delete" title="<?php echo __('Delete field', 'sabai');?>"><i class="fa fa-lg fa-trash-o"></i></a><?php endif;?>
                            </div>
                            <div class="sabai-fieldui-field-title"><?php echo Sabai::h($filter['filter']->getLabel());?> - <?php echo $filter['filter']->name;?></div>
                        </div>                  
                        <div class="sabai-fieldui-field-preview"><?php echo $field_preview;?></div>
                        <input class="sabai-fieldui-field-id" type="hidden" name="filters[]" value="<?php echo $filter_id;?>" />
                    </div>
<?php     endforeach;?>
                </div>
                <input type="hidden" name="filters[]" value="__COLUMN__" />
<?php   endfor;?>
            </div>
            <input type="hidden" name="filters[]" value="__ROW__" />
<?php endfor;?>
            <?php echo $this->TokenHtml('fieldui_admin_submit_filter_fields');?>
            <div style="clear:both;"></div>
        </div>
    </div>
    <div id="sabai-fieldui-available-wrap">
        <div class="sabai-fieldui-available sabai-fieldui-layout sabai-hidden-xs">
            <div class="sabai-fieldui-title">
                <div class="sabai-fieldui-control">
                    <a href="#" class="sabai-fieldui-toggle"><i class="fa fa-caret-up"></i></a>
                </div>
                <div class="sabai-fieldui-label"><?php echo __('Form Layout', 'sabai');?></div>
            </div>    
            <div class="sabai-fieldui-content">
                <select name="row_count">
<?php for ($_row_count = 1; $_row_count <= 10; ++$_row_count):?>
                <option value="<?php echo $_row_count;?>"<?php if ($_row_count == $row_count):?> selected="selected"<?php endif;?>><?php printf(_n('%d row', '%d rows', $_row_count, 'sabai'), $_row_count);?></option>
<?php endfor;?>
                </select>
                <select name="column_count">
<?php foreach (array(1, 2, 3, 4, 6) as $_column_count):?>
                <option value="<?php echo $_column_count;?>"<?php if ($_column_count == $column_count):?> selected="selected"<?php endif;?>><?php printf(_n('%d column', '%d columns', $_column_count, 'sabai'), $_column_count);?></option>
<?php endforeach;?>
                </select>
            </div>
        </div>
<?php if (!empty($filterable_fields)):?>
        <div class="sabai-fieldui-available sabai-fieldui-fields">
            <div class="sabai-fieldui-title">
                <div class="sabai-fieldui-control">
                    <a href="#" class="sabai-fieldui-toggle"><i class="fa fa-caret-up"></i></a>
                </div>
                <div class="sabai-fieldui-label"><?php echo __('Filterable Fields', 'sabai');?></div>
            </div>    
            <div class="sabai-fieldui-content sabai-clearfix">
<?php   foreach ($filterable_fields as $field):?>
                <a href="<?php echo $form_create_field_url;?>" data-field-id="<?php Sabai::_h($field['field']->getFieldId());?>" class="sabai-btn sabai-btn-default" data-modal-title="<?php echo Sabai::h($field['field']->getFieldLabel())?>"><?php echo Sabai::h($field['field']->getFieldLabel())?><br /><span>(<?php Sabai::_h($field['field_type']);?>)</span></a>
<?php   endforeach;?>
            </div>
        </div>
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
    <input class="sabai-fieldui-field-id" type="hidden" name="filters[]" value="" />
</div>