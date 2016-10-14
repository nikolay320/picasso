<?php
class Sabai_Addon_Date_FieldRenderer extends Sabai_Addon_Field_Renderer_AbstractRenderer
{
    protected function _fieldRendererGetInfo()
    {
        return array(
            'field_types' => array('date_timestamp'),
            'default_settings' => array('separator' => ', '),
        );
    }

    public function fieldRendererRenderField(Sabai_Addon_Field_IField $field, array $settings, array $values, Sabai_Addon_Entity_IEntity $entity)
    {
        $field_settings = $field->getFieldSettings();
        $ret = array();
        foreach ($values as $value) {
            $ret[] = !empty($field_settings['enable_time'])
                ? $this->_addon->getApplication()->DateTime($value)
                : $this->_addon->getApplication()->Date($value);
        }
        return implode($settings['separator'], $ret);
    }
}