<?php
class Sabai_Addon_Comment_Controller_Admin_Settings extends Sabai_Addon_System_Controller_Admin_Settings
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $config = $this->getAddon()->getConfig();
        return array(
            'spam' => array(
                '#tree' => true,
                'threshold' => array(
                    '#type' => 'textfield',
                    '#title' => __('Spam score threshold', 'sabai'),
                    '#description' => __('When a comment is flagged, the comment is assigned a "spam score". Comments with spam scores exceeding the threshold value are marked as spam and become hidden from the public.', 'sabai'),
                    '#default_value' => $config['spam']['threshold'],
                    '#size' => 4,
                    '#integer' => true,
                    '#required' => true,
                ),
                'auto_delete' => array(
                    '#type' => 'checkbox',
                    '#title' => __('Auto-delete spam', 'sabai'),
                    '#default_value' => $config['spam']['auto_delete'],
                    '#description' => __('When checked, comments that are marked as spam will be deleted by the system.', 'sabai'),
                ),
                'delete_after' => array(
                    '#type' => 'textfield',
                    '#default_value' => $config['spam']['delete_after'],
                    '#field_prefix' => __('Delete spam after:', 'sabai'),
                    '#description' => __('Enter the number of days the system will wait before auto-deleting comments marked as spam.', 'sabai'),
                    '#field_suffix' => __('days', 'sabai'),
                    '#size' => 4,
                    '#integer' => true,
                    '#states' => array(
                        'visible' => array(
                            'input[name="spam[auto_delete][]"]' => array('type' => 'checked', 'value' => true),
                        ),
                    ),
                ),
            ),
            'show_login_link' => array(
                '#type' => 'checkbox',
                '#title' => __('Show login link to guest users', 'sabai'),
                '#default_value' => $config['show_login_link'],
            ),
        );
    }
}