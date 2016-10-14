<?php
class Sabai_Addon_Directory_Controller_ListingContact extends Sabai_Addon_Content_Controller_AddChildPost
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $form = parent::_doGetFormSettings($context, $formStorage);
        $this->_submitButtons['submit'] = array(
            '#value' => __('Submit', 'sabai-directory'),
            '#btn_type' => 'primary',
        );
        return $form;
    }
}