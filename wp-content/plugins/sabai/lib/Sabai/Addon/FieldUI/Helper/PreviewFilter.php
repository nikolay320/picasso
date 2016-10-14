<?php
class Sabai_Addon_FieldUI_Helper_PreviewFilter extends Sabai_Helper
{
    /**
     * @return string
     * @param Sabai $application
     * @param Sabai_Addon_Field_IField $field The field for which to make a label
     * @param Sabai_Addon_Entity_Model_Filter $filter
     */
    public function help(Sabai $application, Sabai_Addon_Field_IField $field, Sabai_Addon_Entity_Model_Filter $filter)
    {
        try {
            $ifilter = $application->Field_FilterImpl($filter->type);
        } catch (Sabai_IException $e) {
            return $e->getMessage();
        }
        // Init filter settings
        $settings = $filter->data['settings'] + (array)$ifilter->fieldFilterGetInfo('default_settings');
        $is_hidden = $ifilter->fieldFilterGetInfo('is_hidden');
        $is_fieldset = $ifilter->fieldFilterGetInfo('is_fieldset');
        
        if (!empty($filter->data['hide_label'])) {
            $title = '';
        } else {
            if ($is_hidden) {
                $title = sprintf(__('%s (hidden)', 'sabai'), $filter->getLabel());
            } else {
                $title = $filter->getLabel();
            }
        }
        $preview = method_exists($ifilter, 'fieldFilterGetPreview') ? $ifilter->fieldFilterGetPreview($field, $filter->name, $settings) : '';
        if (false === $preview) {
            return false; // return false to never display the filter
        }
        
        return sprintf(
            '<div class="sabai-fieldui-filter-preview%1$s%7$s">
  <div class="sabai-fieldui-filter-label"%2$s>%3$s</div>
  <div class="sabai-fieldui-filter-description"%8$s>%6$s</div>
  <div class="sabai-fieldui-filter-form">%4$s</div>
  <div class="sabai-fieldui-filter-description"%5$s>%6$s</div>
</div>',
            $is_hidden ? ' sabai-fieldui-filter-preview-hidden' : '',
            !strlen($title) || $ifilter->fieldFilterGetInfo('disable_preview_title')  ? ' style="display:none;"' : '',
            Sabai::h($title),
            $preview,
            $is_fieldset || !strlen($filter->data['description']) || $ifilter->fieldFilterGetInfo('disable_preview_description') ? ' style="display:none;"' : '',
            $filter->data['description'],
            !empty($filter->data['disabled']) ? ' sabai-fieldui-filter-preview-disabled' : '',
            $is_fieldset ? '' : ' style="display:none;"'
        );
    }
}