<?php
class Sabai_Helper_NumberFormat extends Sabai_Helper
{
    public function help(Sabai $application, $number)
    {
        if ($number < 1000) {
            return number_format($number);
        }
        return number_format(round($number / 1000 * 10) / 10) . 'k';
    }
}