<?php
class Sabai_Addon_Directory_Controller_Admin_SpamSettings extends Sabai_Addon_System_Controller_Admin_Settings
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $config = $this->getAddon()->getConfig('spam');
        return array(
            'threshold' => array(
                '#title' => __('Spam Score Threshold', 'sabai-directory'),
                '#collapsed' => false,
                '#class' => 'sabai-form-group',
                '#tree' => true,
                'header' => array(
                    '#type' => 'markup',
                    '#value' => '<p>' . __('When a post is flagged, the post is assigned a "spam score". Posts with spam scores exceeding the threshold value are marked as spam and moved to trash automatically by the system. Also, posts with higher number of votes will have higher threshold. For example, if the value set here is 11, and a post has 10 votes, then the spam score threshold for the post will be 14 (11 + 0.3 x 10).', 'sabai-directory') . '</p>',
                ),
                'listing' => array(
                    '#type' => 'number',
                    '#field_prefix' => __('Listings:', 'sabai-directory'),
                    '#field_suffix' => '+ 0.3 x ' . __('number of rating stars', 'sabai-directory'),
                    '#default_value' => $config['threshold']['listing'],
                    '#size' => 4,
                    '#integer' => true,
                    '#required' => true,
                ),
                'review' => array(
                    '#type' => 'number',
                    '#field_prefix' => __('Reviews:', 'sabai-directory'),
                    '#field_suffix' => '+ 0.3 x ' . __('number of "helpful" votes', 'sabai-directory'),
                    '#default_value' => $config['threshold']['review'],
                    '#size' => 4,
                    '#integer' => true,
                    '#required' => true,
                ),
                'photo' => array(
                    '#type' => 'number',
                    '#field_prefix' => __('Photos', 'sabai-directory'),
                    '#field_suffix' => '+ 0.3 x ' . __('number of votes', 'sabai-directory'),
                    '#default_value' => $config['threshold']['photo'],
                    '#size' => 4,
                    '#integer' => true,
                    '#required' => true,
                ),
            ),
            '_auto_delete' => array(
                '#title' => __('Auto-delete Settings', 'sabai-directory'),
                '#collapsed' => false,
                'auto_delete' => array(
                    '#type' => 'checkbox',
                    '#title' => __('Auto-delete spam', 'sabai-directory'),
                    '#default_value' => $config['auto_delete'],
                    '#description' => __('When checked, posts that have been marked as spam will be deleted by the system after the period of time specified in the "Delete Spam After" option.', 'sabai-directory'),
                ),
                'delete_after' => array(
                    '#type' => 'number',
                    '#default_value' => $config['delete_after'],
                    '#field_prefix' => __('Delete spam after:', 'sabai-directory'),
                    '#description' => __('Enter the number of days the system will wait before auto-deleting posts marked as spam.', 'sabai-directory'),
                    '#field_suffix' => __('days', 'sabai-directory'),
                    '#size' => 4,
                    '#integer' => true,
                    '#states' => array(
                        'visible' => array(
                            'input[name="auto_delete[]"]' => array('type' => 'checked', 'value' => true),
                        ),
                    ),
                ),
            ),
        );
    }
    
    protected function _getSuccessUrl(Sabai_Context $context)
    {
        return $this->Url($context->getRoute());
    }

    protected function _saveConfig(Sabai_Context $context, array $values)
    {
        $this->getAddon()->saveConfig(array('spam' => $values));
    }
}