<?php
class Sabai_Addon_Time_FormField extends Sabai_Addon_Form_Field_AbstractField
{
    static private $_elements = array(), $_jsLoaded;

    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!isset(self::$_elements[$form->settings['#id']])) {
            self::$_elements[$form->settings['#id']] = array();
        }
        if (!isset($data['#id'])) {
            $data['#id'] = $form->getFieldId($name);
        }
        if (!isset($data['#default_value'])) {
            if (!empty($data['#current_time_selected'])) {
                $current_time = $this->_addon->getApplication()->getPlatform()->getSystemToSiteTime(time());
                $data['#default_value'] = array(
                    'start' => date('h:i A', $current_time),
                    'end' => '',
                    'day' => date('w', $current_time),
                );
            }
        } else {
            if (is_numeric($data['#default_value'])) {
                $current_time = $this->_addon->getApplication()->getPlatform()->getSystemToSiteTime($data['#default_value']);
                $data['#default_value'] = array(
                    'start' => date('h:i A', $current_time),
                    'end' => '',
                    'day' => 0,
                );
            } else {
                if (is_numeric($data['#default_value']['start'])) {
                    $data['#default_value']['start'] %= 86400;
                    $data['#default_value']['start'] = date('h:i A', $this->_addon->getApplication()->getPlatform()->getSystemToSiteTime(mktime(0, 0, 0)) + $data['#default_value']['start']);
                }
                if (is_numeric($data['#default_value']['end'])) {
                    $data['#default_value']['end'] %= 86400;
                    $data['#default_value']['end'] = date('h:i A', $this->_addon->getApplication()->getPlatform()->getSystemToSiteTime(mktime(0, 0, 0)) + $data['#default_value']['end']);
                }
                $data['#default_value']['day'] = (int)@$data['#default_value']['day'];
            }
        } 
        
        $name = Sabai::h($name);
        $markup = array();
        
        if (isset($data['#field_prefix'])) {
            $markup[] = '<span class="sabai-form-field-prefix">' . $data['#field_prefix'] . '</span>';
        }
        
        // Add day select list
        if (empty($data['#disable_day'])) {
            $markup[] = sprintf('<select class="sabai-time-timepicker-day sabai-focus-off" name="%s[day]">', $name);
            foreach ($this->_addon->getApplication()->Time_Days(true) as $key => $day) {
                $markup[] = sprintf(
                    '<option value="%d"%s>%s</option>',
                    $key,
                    isset($data['#default_value']['day']) && $data['#default_value']['day'] === $key ? ' selected="selected"' : '',
                    Sabai::h($day)
                );
            }
            $markup[] = '</select>';
        }   
        // Add start time
        $markup[] = sprintf(
            '<input type="text" name="%s[start]" value="%s" class="sabai-time-timepicker-start sabai-focus-off" size="6" placeholder="HH:MM" />',
            $name,
            @$data['#default_value']['start']
        );
        // Add end time
        if (empty($data['#disable_end'])) {
            $markup[] = sprintf(
                '<span class="sabai-form-field-prefix">-</span><input type="text" name="%s[end]" value="%s" class="sabai-time-timepicker-end sabai-focus-off" size="6" placeholder="HH:MM" />',
                $name,
                @$data['#default_value']['end']
            );
        }
        
        if (isset($data['#field_suffix'])) {
            $markup[] = '<span class="sabai-form-field-suffix">' . $data['#field_suffix'] . '</span>';
        }
        
        // Register pre render callback if this is the first date element
        if (empty(self::$_elements[$form->settings['#id']])) {
            $form->settings['#pre_render'][] = array($this, 'preRenderCallback');
        }

        self::$_elements[$form->settings['#id']][$name] = $data['#id'];

        unset($data['#default_value'], $data['#value']);
        
        if (!isset($data['#class'])) {
            $data['#class'] = '';
        }
        $data['#class'] .= ' sabai-form-type-time-timepicker sabai-form-inline';

        return $form->createHTMLQuickformElement('static', $name, $data['#label'], implode(PHP_EOL, $markup));
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        $value = array_map('trim', $value);
        foreach (array('start', 'end') as $key) {
            if (!isset($value[$key]) || !strlen($value[$key])) continue;

            if (false !== $time = $this->_validateTime($value[$key])) {
                $value[$key] = $time;
            } else {
                $form->setError(__('Invalid time.', 'sabai'), $name);
                return;
            }
        }
        if (!isset($value['start']) || !strlen($value['start'])) {
            if ($form->isFieldRequired($data)
                || (empty($data['#disable_day']) && !empty($value['day'])) // day selected
                || (empty($data['#disable_end']) && (isset($value['end']) && strlen($value['end']))) // end time selected
            ) {
                $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : __('Please pick a time.', 'sabai'), $name);
                return;
            }
            $value = null;
        } else {
            if (empty($data['#disable_day']) && empty($value['day'])) {
                $form->setError(__('Please select a day of week.', 'sabai'), $name);
                return;
            }
            if (empty($data['#disable_end']) && (!isset($value['end']) || !strlen($value['end']))) {
                $form->setError(__('Please select an end time.', 'sabai'), $name);
            }
        }
    }
    
    protected function _validateTime($value)
    {
        $is_am = $is_pm = false;
        // remove am/pm string
        if (false !== stripos($value, 'am')) {
            $is_am = true;
            $value = str_ireplace('am', '', $value);
        } elseif (false !== stripos($value, 'pm')) {
            $is_pm = true;
            $value = str_ireplace('pm', '', $value);
        }
        $time = array_map('trim', explode(':', $value));
        if (count($time) !== 2 || !is_numeric($time[0]) || !is_numeric($time[1])) {
            return false;
        }
        $time[0] = intval($time[0]);
        $time[1] = intval($time[1]);
        // convert hour to 24-hour
        if ($is_am) {
            if ($time[0] >= 12) {
                $time[0] -= 12;
            }
        } elseif ($is_pm) {
            if ($time[0] < 12) {
                $time[0] += 12;
            }
        }
        if ($time[0] < 0 || $time[0] > 23 || $time[1] < 0 || $time[1] > 59) {
            return false;
        }
        return $this->_addon->getApplication()->getPlatform()->getSiteToSystemTime(mktime($time[0], $time[1], 0)) % 86400;
    }
    
    public function formFieldOnCleanupForm($name, array &$data, Sabai_Addon_Form_Form $form){}

    public function formFieldOnRenderForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
    }

    public function preRenderCallback($form)
    {
        if (empty(self::$_elements[$form->settings['#id']])) return;
        
        $js = array();
        
        if (!self::$_jsLoaded) {
            $application = $this->_addon->getApplication();
            $application->LoadJs('jquery.ui.timepicker.min.js', 'jquery-ui-timepicker', 'jquery-ui-core');
            $application->LoadJs('sabai-time-timepicker.min.js', 'sabai-time-timepicker', array('sabai', 'jquery-ui-timepicker'));
            // Init time picker
            $js[] = sprintf(
                '$.timepicker.setDefaults({
        hourText: "%1$s",
        minuteText: "%2$s",
        amPmText: ["%3$s", "%4$s"],
        showLeadingZero: false,
        showNowButton: true,
        nowButtonText: "%5$s",
        showDeselectButton: true,
        deselectButtonText: "%6$s",
        showPeriod: true,
        showLeadingZero: true
    });',
                __('Hour', 'timepicker', 'sabai'),
                __('Minute', 'timepicker', 'sabai'),
                __('AM', 'timepicker', 'sabai'),
                __('PM', 'timepicker', 'sabai'),
                _x('Now', 'timepicker', 'sabai'),
                __('Deselect', 'timepicker', 'sabai')
            );
            self::$_jsLoaded = true;
        }
        // Add js to instantiate date/time pickers
        foreach (self::$_elements[$form->settings['#id']] as $id) {
            $js[] = 'SABAI.Time.timepicker("#'. $id .'");';
        }
        // Add js
        $form->addJs(sprintf(
            'jQuery(document).ready(function ($) {
    %s
});',
            implode(PHP_EOL, $js)
        ));
    }
}