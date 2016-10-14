<?php
class Sabai_Addon_Directory_Controller_Admin_EmailsSettings extends Sabai_Addon_System_Controller_Admin_EmailSettings
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $form = parent::_doGetFormSettings($context, $formStorage);
        
        $form['emails']['listing_expires']['days'] = array(
            '#type' => 'number',
            '#description' => __('Enter the number of days before the expiration date on which this notification will be sent.', 'sabai-directory'),
            '#default_value' => ($days = $this->System_EmailSettings($this->getAddon()->getName(), 'listing_expires', 'days')) ? $days : 7,
            '#integer' => true,
            '#min_value' => 1,
            '#max_value' => 30,
            '#size' => 3,
            '#field_suffix' => __('day(s)', 'sabai-directory'),
            '#weight' => 3,
        );
        
        return $form;
    }
    
    protected function _getEmailSettings(Sabai_Context $context)
    {        
        return $this->Directory_NotificationSettings($this->getAddon()->getName());
    }
}
