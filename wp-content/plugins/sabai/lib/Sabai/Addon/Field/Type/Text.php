<?php
class Sabai_Addon_Field_Type_Text extends Sabai_Addon_Field_Type_String
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('Paragraph Text', 'sabai'),
            'default_widget' => 'textarea',
            'default_renderer' => 'text',
            'default_settings' => array(
                'char_validation' => 'none',
            ),
        );
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {
        $form = parent::fieldTypeGetSettingsForm($settings, $parents);
        unset($form['mask'], $form['char_validation'], $form['regex']);
        return $form;
    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'value' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                    'notnull' => true,
                    'was' => 'value',
                ),
            ),
        );
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values, Sabai_Addon_Entity_IEntity $entity)
    {
        if (($widget = $field->getFieldWidget())
            && ($widget_impl = $this->_addon->getApplication()->Field_WidgetImpl($widget, true))
            && method_exists($widget_impl, 'fieldWidgetHtmlizeText')
        ) {
            $widget_info = $widget_impl->fieldWidgetGetInfo();
            $widget_settings = $field->getFieldWidgetSettings() + (array)@$widget_info['default_settings'];
            foreach ($values as $key => $value) {
                $values[$key] = array(
                    'value' => $value['value'],
                    'html' => $widget_impl->fieldWidgetHtmlizeText($field, $widget_settings, $value['value'], $entity),
                );
            }
        } else {
            foreach ($values as $key => $value) {
                $values[$key] = array(
                    'value' => $value['value'],
                    'html' => $this->_addon->getApplication()->Htmlize($value['value']),
                );
            }
        }
    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {
        $current = array();
        foreach ($currentLoadedValue as $value) {
            $current[] = $value['value'];
        }
        return $current !== $valueToSave;
    }
}