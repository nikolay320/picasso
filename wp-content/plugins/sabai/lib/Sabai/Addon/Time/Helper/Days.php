<?php
class Sabai_Addon_Time_Helper_Days extends Sabai_Helper
{
    protected $_days;
    
    public function help(Sabai $application, $withEmptyOption = false, $emptyLabel = '')
    {
        if (!isset($this->_days)) {
            $this->_days = array(
                1 => __('Monday', 'sabai'),
                2 => __('Tuesday', 'sabai'),
                3 => __('Wednesday', 'sabai'),
                4 => __('Thursday', 'sabai'),
                5 => __('Friday', 'sabai'),
                6 => __('Saturday', 'sabai'),
                7 => __('Sunday', 'sabai'),
            );
            $start_of_week = (int)$application->getPlatform()->getStartOfWeek();
            if (isset($this->_days[$start_of_week]) && $start_of_week !== 1) {
                $_days = array($start_of_week => $this->_days[$start_of_week]);
                unset($this->_days[$start_of_week]);
                for ($i = $start_of_week + 1; $i <= 7; $i++) {
                    $_days[$i] = $this->_days[$i];
                    unset($this->_days[$i]);
                }
                foreach ($this->_days as $i => $_day) {
                    $_days[$i] = $_day;
                }
                $this->_days = $_days;
            }
        }
        
        return $withEmptyOption ? array(0 => $emptyLabel) + $this->_days : $this->_days;
    }
}