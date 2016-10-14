<?php
class Sabai_Addon_Voting_Helper_RenderRating extends Sabai_Helper
{
    public function help(Sabai $application, $entityOrRating, $name = '', $plain = false)
    {
        if (is_object($entityOrRating)) {
            $values = $entityOrRating->getFieldValue('voting_rating');
            if (!isset($values[$name])
                || empty($values[$name]['count'])
            ) {
                return '';
            }
            $value = $values[$name]['average'];
        } else {
            $value = $entityOrRating;
        }
        $rounded = round($value, 1) * 10;
        $remainder = $rounded % 5;
        $rounded -= $remainder;
        if ($remainder > 2) {
            $rounded += 5;
        }
        return $plain ? $rounded : sprintf(
            '<span class="sabai-rating sabai-rating-%d" title="%s"></span>',
            $rounded,
            sprintf(__('%.2f out of 5 stars', 'sabai'), $value)
        );
    }
}