<?php
class Sabai_Addon_Time_OpeningHoursFieldRenderer extends Sabai_Addon_Field_Renderer_AbstractRenderer
{
    protected function _fieldRendererGetInfo()
    {
        return array(
            'label' => __('Opening Hours', 'sabai'),
            'field_types' => array('time_time'),
            'default_settings' => array(
                'show_closed' => true,
                'closed' => _x('Closed', 'opening hours', 'sabai'),
                'separator' => ', '
            ),
        );
    }
    
    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array())
    {
        return array(
            'show_closed' => array(
                '#type' => 'checkbox',
                '#title' => __('Show days without any entry as closed', 'sabai'),
                '#default_value' => $settings['show_closed'],
            ),
            'closed' => array(
                '#type' => 'textfield',
                '#title' => __('Label for closed days', 'sabai'),
                '#default_value' => $settings['closed'],
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[show_closed][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                            'type' => 'checked',
                            'value' => true,
                        ),
                    ),
                ),
            ),
        );
    }

    public function fieldRendererRenderField(Sabai_Addon_Field_IField $field, array $settings, array $values, Sabai_Addon_Entity_IEntity $entity)
    {
        $field_settings = $field->getFieldSettings();
        if (empty($field_settings['enable_day'])) return '';
        
        $days = $this->_addon->getApplication()->Time_Days();
        
        $_values = array();
        foreach ($values as $value) {
            if (empty($value['day'])) continue;
            
            $_values[$value['day']][$value['start']] = $value['end'];
        }
        
        $ret = array('<table class="sabai-time-opening-hours">');
        foreach ($this->_addon->getApplication()->Time_Days() as $day => $day_label) {
            if (!isset($_values[$day])) {
                if (!$settings['show_closed']) continue;
                
                $time_label = $settings['closed'];
            } elseif (1 === $count = count($_values[$day])) {
                $time_label = sprintf(
                    '%s - %s',
                    $this->_addon->getApplication()->Time(current(array_keys($_values[$day]))),
                    $this->_addon->getApplication()->Time(current($_values[$day]))
                );
            } else {
                ksort($_values[$day]); // sort by starting time
                $starts = array_keys($_values[$day]);
                $ends = array_values($_values[$day]);
                $i = 0;
                for ($j = 1; $j < $count; ++$j) {
                    if ($starts[$j] > $ends[$i] + 60) {
                        $i = $j;
                    } else {
                        if ($ends[$i] < $ends[$j]) {
                            $ends[$i] = $ends[$j];
                        }
                        unset($starts[$j], $ends[$j]);
                    }
                }
                $_ret = array();
                foreach (array_keys($starts) as $i) {
                    $_ret[] = sprintf(
                        '%s - %s',
                        $this->_addon->getApplication()->Time($starts[$i]),
                        $this->_addon->getApplication()->Time($ends[$i])
                    );
                }
                $time_label = implode($settings['separator'], $_ret);
            }
            $ret[] = '<tr><td>' . Sabai::h($day_label) . '</td><td>' . $time_label . '</td></tr>';
        }
        $ret[] = '</table>';
        return implode(PHP_EOL, $ret);
    }
}