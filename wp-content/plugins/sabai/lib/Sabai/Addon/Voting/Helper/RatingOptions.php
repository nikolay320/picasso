<?php
class Sabai_Addon_Voting_Helper_RatingOptions extends Sabai_Helper
{
    public function help(Sabai $application, $type = 'select')
    {
        if ($type !== 'select') {
            $options = array(5 => '<span class="sabai-rating sabai-rating-50"></span>');
            for ($i = 4; $i > 0; --$i) {
                $options[$i] = sprintf(__('%s & Up', 'sabai'), '<span class="sabai-rating sabai-rating-'. $i * 10 .'"></span>');
            }
            $options[0] = _x('Any', 'option', 'sabai');
        } else {
            $options = array(0 => _x('Any', 'option', 'sabai'), 5 => sprintf(__('%d stars', 'sabai'), 5));
            for ($i = 4; $i > 0; --$i) {
                $options[$i] = sprintf(__('%d stars & up', 'sabai'), $i);
            }
        }
        return $options;
    }
}