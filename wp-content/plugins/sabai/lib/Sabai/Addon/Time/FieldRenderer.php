<?php
class Sabai_Addon_Time_FieldRenderer extends Sabai_Addon_Field_Renderer_AbstractRenderer
{
    protected function _fieldRendererGetInfo()
    {
        return array(
            'field_types' => array('time_time'),
            'default_settings' => array(
                'daytime_sep' => ' ',
                'time_sep' => ' - ',
                'separator' => ', '
            ),
        );
    }

    public function fieldRendererRenderField(Sabai_Addon_Field_IField $field, array $settings, array $values, Sabai_Addon_Entity_IEntity $entity)
    {
        $field_settings = $field->getFieldSettings();
        if (!empty($field_settings['enable_day'])) {
            $days = $this->_addon->getApplication()->Time_Days();
        }
        $ret = array();
        foreach ($values as $value) {
            $str = '';
            if (isset($days) && isset($days[$value['day']])) {
                $str .= $days[$value['day']] . $settings['daytime_sep'];
            }
            $str .= $this->_addon->getApplication()->Time($value['start']);
            if (!empty($field_settings['enable_end'])) {
                $str .= $settings['time_sep'] . $this->_addon->getApplication()->Time($value['end']);
            }
            $ret[] = $str;
        }
        return implode($settings['separator'], $ret);
    }
}