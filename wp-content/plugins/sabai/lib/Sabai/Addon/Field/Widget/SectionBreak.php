<?php
class Sabai_Addon_Field_Widget_SectionBreak extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Section Break', 'sabai'),
            'field_types' => array('sectionbreak'),
            'default_settings' => array(
                
            ),
            'requirable' => false,
            'disable_preview_title' => true,
            'disable_preview_description' => true,
        );
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        return array(
            '#type' => 'sectionbreak',
            '#title' => $field->getFieldLabel(),
            '#description' => $field->getFieldDescription(),
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        return sprintf(
            '<div class="sabai-form-field sabai-form-type-sectionbreak"><h2 class="sabai-form-field-label">%s</h2>%s</div>',
            Sabai::h($field->getFieldLabel()),
            $field->getFieldDescription() ? '<div class="sabai-form-field-description sabai-form-field-description-top">' . $field->getFieldDescription() . '</div>' : ''
        );
    }
}