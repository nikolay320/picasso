<?php
class Sabai_Addon_Date_DatePickerFormField extends Sabai_Addon_Form_Field_AbstractField
{
    static private $_elements = array(), $_enableTimepicker = false;

    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!isset(self::$_elements[$form->settings['#id']])) {
            self::$_elements[$form->settings['#id']] = array();
        }
        if (!isset($data['#id'])) {
            $data['#id'] = $form->getFieldId($name);
        }
        
        if (!array_key_exists('#empty_value', $data)) {
            $data['#empty_value'] = null;
        }
        
        $data['#disable_time'] = isset($data['#disable_time']) ? $data['#disable_time'] : false;
        if (!isset($data['#default_value'])) {
            if (!empty($data['#current_date_selected'])) {
                $data['#default_value'] = $this->_addon->getApplication()->getPlatform()->getSystemToSiteTime(time());
            } else {
                $data['#default_value'] = $data['#empty_value'];
            }
        } else {
            if (is_int($data['#default_value'])) {
                $data['#default_value'] = $this->_addon->getApplication()->getPlatform()->getSystemToSiteTime($data['#default_value']);
            }
        }
        
        if (is_array($data['#default_value'])) {
            $default_date = $data['#default_value']['date'];
        } else {
            if (is_int($data['#default_value'])) {
                $default_date = $data['#default_value'] !== $data['#empty_value'] ? date('Y/m/d', $data['#default_value']) : '';
            } else {
                // only date
                $default_date = $data['#default_value'];
            }
        }

        // Define number of months to display on date picker
        if (0 >= $data['#number_months'] = intval(@$data['#number_months'])) {
            $data['#number_months'] = 1;
        }

        // Define min/max date
        if (isset($data['#min_date']) && !is_int($data['#min_date'])) {
            unset($data['#min_date']);
        }
        if (isset($data['#max_date'])) {
            if (!is_int($data['#max_date'])
                || (isset($data['#min_date']) && $data['#max_date'] < $data['#min_date'])
            ) {
                unset($data['#max_date']);
            }
        }

        $name = Sabai::h($name);
        
        // Build markup
        if (!$data['#disable_time']) {
            if (is_array($data['#default_value'])) {
                $default_time = $data['#default_value']['time'];
            } else {
                if (is_int($data['#default_value'])) {
                    $default_time = $data['#default_value'] !== $data['#empty_value'] ? date('h:i A', $data['#default_value']) : '';
                } else {
                    // only date
                    $default_time = '';
                }
            }
            $markup = sprintf(
                '<input type="text" size="8" class="sabai-date-datepicker-date sabai-focus-off" data-date-min="%2$d" data-date-max="%3$d" data-date-num-months="%4$d" />
<input type="text" name="%1$s[time]" value="%5$s" size="6" class="sabai-date-datepicker-time sabai-focus-off" placeholder="HH:MM" />
<input type="hidden" name="%1$s[date]" value="%6$s" class="sabai-date-datepicker-alt" />',
                $name,
                @$data['#min_date'],
                @$data['#max_date'],
                $data['#number_months'],
                $default_time,
                $default_date
            );
            // Enable timepicker script
            if (!self::$_enableTimepicker) {
                self::$_enableTimepicker = true;
            }
        } else {
            $markup = sprintf(
                '<input type="text" size="8" class="sabai-date-datepicker-date sabai-focus-off" data-date-min="%2$d" data-date-max="%3$d" data-date-num-months="%4$d" />
<input type="hidden" name="%1$s" value="%5$s" class="sabai-date-datepicker-alt" />',
                $name,
                @$data['#min_date'],
                @$data['#max_date'],
                $data['#number_months'],
                $default_date
            );
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
        $data['#class'] .= ' sabai-form-type-date-datepicker';
        
        return $form->createHTMLQuickformElement('static', $name, $data['#label'], $markup);
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!is_array($value)) {
            $value = $this->_addon->getApplication()
                ->Date_ValidateDatePickerFormField($name, $value, $value, $data, $form);
            return;
        }
        
        if (!$data['#disable_time']
            && ($value['time'] = trim((string)@$value['time']))
            && strlen($value['time'])
        ) {
            $time = $value['time'];
        } else {
            $time = is_string($data['#disable_time']) ? $data['#disable_time'] : null;
        }
        
        $value = $this->_addon->getApplication()
            ->Date_ValidateDatePickerFormField($name, @$value['alt'], @$value['date'], $data, $form, $time);
    }

    public function formFieldOnCleanupForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnRenderForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
    }

    public function preRenderCallback($form)
    {
        if (empty(self::$_elements[$form->settings['#id']])) return;

        $this->_addon->getApplication()->Date_Scripts();
        
        $js = array();
        // Enable time picker?
        if (self::$_enableTimepicker) {
            // Add js to set timepicker default options
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
        }
        // Add js to instantiate date/time pickers
        foreach (self::$_elements[$form->settings['#id']] as $id) {
            $js[] = 'SABAI.Date.datetimepicker("#'. $id .'");';
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