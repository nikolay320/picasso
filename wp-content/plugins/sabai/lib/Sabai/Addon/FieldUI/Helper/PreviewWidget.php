<?php
class Sabai_Addon_FieldUI_Helper_PreviewWidget extends Sabai_Helper
{
    /**
     * @return string
     * @param Sabai $application
     * @param Sabai_Addon_Field_IField $field The field for which to make a label
     */
    public function help(Sabai $application, Sabai_Addon_Field_IField $field)
    {
        try {
            $iwidget = $application->Field_WidgetImpl($field->getFieldWidget());
        } catch (Sabai_IException $e) {
            return $e->getMessage();
        }
        // Init widget settings
        $widget_settings = $field->getFieldWidgetSettings() + (array)$iwidget->fieldWidgetGetInfo('default_settings');
        $is_hidden = $iwidget->fieldWidgetGetInfo('is_hidden');
        $is_fieldset = $iwidget->fieldWidgetGetInfo('is_fieldset') || $iwidget->fieldWidgetGetInfo('repeatable');
        $title = $field->getFieldData('hide_label') ? '' : $field->getFieldLabel();
        if ($is_hidden) {
            $title = strlen($title) ? sprintf(__('%s (hidden)', 'sabai'), $title) : __('(hidden)', 'sabai');
        }
        $description = $field->getFieldDescription();
        $preview = method_exists($iwidget, 'fieldWidgetGetPreview') ? $iwidget->fieldWidgetGetPreview($field, $widget_settings) : '';
        if (false === $preview) {
            return false; // return false to never display the widget
        }
        if ($preview !== ''
            && !$iwidget->fieldWidgetGetInfo('accept_multiple')
            && $iwidget->fieldWidgetGetInfo('repeatable')
        ) {
            $max_num = $field->getFieldMaxNumItems();
            switch ($max_num) {
                case 1:
                    break;
                default:
                    $preview = sprintf('%s<div style="margin-top:10px;"><span class="sabai-btn sabai-btn-default sabai-btn-xs" ><i class="fa fa-plus"></i> %s</span></div>', $preview, __('Add More', 'sabai'));
                    break;
            }
        }
        
        return sprintf(
            '<div class="sabai-fieldui-widget-preview%1$s%8$s">
  <div class="sabai-fieldui-widget-label"%2$s>%3$s%4$s</div>
  <div class="sabai-fieldui-widget-description"%9$s>%7$s</div>
  <div class="sabai-fieldui-widget-form">%5$s</div>
  <div class="sabai-fieldui-widget-description"%6$s>%7$s</div>
</div>',
            $is_hidden ? ' sabai-fieldui-widget-preview-hidden' : '',
            !strlen($title) || $iwidget->fieldWidgetGetInfo('disable_preview_title')  ? ' style="display:none;"' : '',
            Sabai::h($title),
            $field->getFieldRequired() ? '<span class="sabai-fieldui-widget-required">*</span>' : '',
            $preview,
            $is_fieldset || !strlen($description) || $iwidget->fieldWidgetGetInfo('disable_preview_description') ? ' style="display:none;"' : '',
            $description,
            $field->getFieldDisabled() ? ' sabai-fieldui-widget-preview-disabled' : '',
            $is_fieldset ? '' : ' style="display:none;"'
        );
    }
}