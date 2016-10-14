<?php
class Sabai_Addon_Directory_Model_Claim extends Sabai_Addon_Directory_Model_Base_Claim
{
    public function getLabel()
    {
        return '#' . str_pad($this->id, 5, 0, STR_PAD_LEFT);
    }
    
    public function getStatusLabel()
    {
        switch ($this->status) {
            case 'pending':
                return __('Pending', 'sabai-directory');
            case 'rejected':
                return __('Rejected', 'sabai-directory');
            case 'approved':
                return __('Approved', 'sabai-directory');
            default:
                return '';
        }
    }
    
    public function getStatusLabelClass()
    {
        switch ($this->status) {
            case 'pending':
                return'sabai-label-warning';                  
            case 'approved':
                return 'sabai-label-success';
            case 'rejected':
                return 'sabai-label-danger';
            default:
                return '';
        }
    }
}

class Sabai_Addon_Directory_Model_ClaimRepository extends Sabai_Addon_Directory_Model_Base_ClaimRepository
{
}