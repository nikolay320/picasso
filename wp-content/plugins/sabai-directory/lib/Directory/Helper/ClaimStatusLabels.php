<?php
class Sabai_Addon_Directory_Helper_ClaimStatusLabels extends Sabai_Helper
{    
    /**
     * @param Sabai $application
     */
    public function help(Sabai $application)
    {
        return array(
            'approved' => __('Approved', 'sabai-directory'),
            'rejected' => __('Rejected', 'sabai-directory'),
            'pending' => __('Pending', 'sabai-directory'),
        );
    }
}