<?php
class Sabai_Addon_Directory_Controller_Admin_ClaimsSettings extends Sabai_Addon_System_Controller_Admin_Settings
{
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $config = $this->getAddon()->getConfig('claims');
        $claim_form_header = $this->getPlatform()->getOption($this->getAddon()->getName() . '_claim_form_header');
        if (!strlen($claim_form_header)) {
            $claim_form_header = __('If the listing is for your organisation, please complete the details below. Once we have confirmed your identity, we will give you full control over your listing and its contents.', 'sabai-directory');
        }
        $field_options = $this->_application->Directory_FieldOptions($this->getAddon()->getListingBundleName(), true);
        $form = array(
            'duration_expiration' => array(
                '#title' => __('Duration Settings', 'sabai-directory'),
                '#collapsed' => false,
                'duration' => array(
                    '#type' => 'number',
                    '#title' => __('Duration', 'sabai-directory'),
                    '#description' => __('Enter the number of days claims are valid before they expire. Enter 0 for no expiration.', 'sabai-directory'),
                    '#integer' => true,
                    '#default_value' => intval($config['duration']),
                    '#field_suffix' => __('days', 'sabai-directory'),
                    '#required' => true,
                    '#size' => 4,
                    '#display_unrequired' => true,
                ),
                'grace_period' => array(
                    '#type' => 'number',
                    '#title' => __('Grace period duration', 'sabai-directory'),
                    '#description' => __('Enter the number of days after which expired claims are deleted from the database.', 'sabai-directory'),
                    '#integer' => true,
                    '#default_value' => intval($config['grace_period']),
                    '#field_suffix' => __('days', 'sabai-directory'),
                    '#required' => true,
                    '#size' => 4,
                ),
                'trash_expired' => array(
                    '#type' => 'yesno',
                    '#title' => __('Move listings to trash when their claims expired', 'sabai-directory'),
                    '#default_value' => !empty($config['trash_expired']),
                ),
            ),
            'claim_form' => array(
                '#title' => __('Claim Listing Form Settings', 'sabai-directory'),
                '#collapsed' => false,
                'claim_form_header' => array(
                    '#type' => 'textarea',
                    '#title' => __('Claim listing form header', 'sabai-directory'),
                    '#description' => __('Enter the message displayed at the top of the form for claiming existing listings.', 'sabai-directory'),
                    '#default_value' => $claim_form_header,
                    '#rows' => 3,
                    '#tree' => false,
                ),
                'no_comment' => array(
                    '#type' => 'yesno',
                    '#title' => __('Do not require comment', 'sabai-directory'),
                    '#default_value' => !empty($config['no_comment']),
                ),
            ),
            'process' => array(
                '#title' => __('Approval Settings', 'sabai-directory'),
                '#collapsed' => false,
                '#tree' => true,
                'auto_approve_new' => array(
                    '#type' => 'yesno',
                    '#title' => __('Automatically approve claims for new listings', 'sabai-directory'),
                    '#default_value' => !empty($config['process']['auto_approve_new']),
                ),
                'auto_approve' => array(
                    '#type' => 'yesno',
                    '#title' => __('Automatically approve claims for existing listings', 'sabai-directory'),
                    '#default_value' => !empty($config['process']['auto_approve']),
                ),
                'delete_auto_approved' => array(
                    '#type' => 'yesno',
                    '#title' => __('Delete claims that have been approved automatically', 'sabai-directory'),
                    '#default_value' => !empty($config['process']['delete_auto_approved']),
                    '#states' => array(
                        'visible' => array(
                            'input[name^="process[auto_approve"]' => array('value' => 1),
                        ),
                    ),
                )
            ),
            'unclaimed' => array(
                '#title' => __('Unclaimed Listing Settings', 'sabai-directory'),
                '#collapsed' => false,
                '#tree' => true,
                'noindex' => array(
                    '#type' => 'yesno',
                    '#title' => __('Hide from the index page', 'sabai-directory'),
                    '#default_value' => !empty($config['unclaimed']['noindex']),
                ),
                'fields' => array(
                    '#title' => __('Fields', 'sabai-directory'),
                    '#description' => __('Select the fields allowed for unclaimed listings in the frontend.', 'sabai-directory'),
                    '#type' => 'checkboxes',
                    '#options' => $field_options[0],
                    '#options_disabled' => $field_options[1],
                    '#default_value' => isset($config['unclaimed']['fields']) ? array_merge($config['unclaimed']['fields'], $field_options[1]) : array_keys($field_options[0]),
                ),
            ),
        );
        $limitable_fields = array('directory_category', 'directory_photos');
        if ($this->isAddonLoaded('GoogleMaps')) {
            $limitable_fields[] = 'directory_location';
        }
        foreach ($limitable_fields as $field_name) {
            $field = $this->Entity_Field($this->getAddon()->getListingBundleName(), $field_name);
            if (!$field || $field->getFieldDisabled()) continue;
            
            if (!$max = $field->getFieldMaxNumItems()) {
                $max = 10;
            }
            $form['unclaimed'][$field_name] = array(
                '#title' => $field->getFieldLabel(),
                '#class' => 'sabai-form-group',
                '#collapsible' => false,
                'limit' => array(
                    '#type' => 'checkbox',
                    '#title' => sprintf(__('Limit the number of %s allowed in the frontend', 'sabai-directory'), $field->getFieldLabel()),
                    '#default_value' => !empty($config['unclaimed'][$field_name]['limit']),
                ),
                'num' => array(
                    '#type' => 'select',
                    '#options' => array_combine(range(0, $max), range(0, $max)),
                    '#default_value' => isset($config['unclaimed'][$field_name]['num']) ? $config['unclaimed'][$field_name]['num'] : 0,
                    '#size' => 5,
                    '#required' => array(array($this, 'isFieldRequired'), array(array('unclaimed', $field_name))),
                    '#states' => array(
                        'visible' => array(
                            sprintf('input[name="unclaimed[%s][limit][]"]', $field_name) => array('type' => 'checked', 'value' => true)
                        ),
                    ),
                ),
            );
        }
        $form['unclaimed']['leads'] = array(
            '#title' => __('Contact Us Form', 'sabai-directory'),
            '#class' => 'sabai-form-group',
            '#collapsible' => false,
            'enable' => array(
                '#type' => 'checkbox',
                '#default_value' => !empty($config['unclaimed']['leads']['enable']),
                '#title' => __('Allow users to send messages via contact us form', 'sabai-directory'),
            ),
            'to_author' => array(
                '#type' => 'checkbox',
                '#default_value' => !empty($config['unclaimed']['leads']['to_author']),
                '#title' => __('Send messages to author email address', 'sabai-directory'),
                '#states' => array(
                    'visible' => array(
                        'input[name="unclaimed[leads][enable][]"]' => array('type' => 'checked', 'value' => 1)
                    ),
                ),
            ),
            'to_contact' => array(
                '#type' => 'checkbox',
                '#default_value' => !empty($config['unclaimed']['leads']['to_contact']),
                '#title' => __('Send messages to contact info email address (if any)', 'sabai-directory'),
                '#states' => array(
                    'visible' => array(
                        'input[name="unclaimed[leads][enable][]"]' => array('type' => 'checked', 'value' => 1)
                    ),
                ),
            ),
        );
        $form['unclaimed']['reviews'] = array(
            '#title' => __('Reviews', 'sabai-directory'),
            '#collapsible' => false,
            'enable' => array(
                '#type' => 'checkbox',
                '#default_value' => !isset($config['unclaimed']['reviews']['enable']) || !empty($config['unclaimed']['reviews']['enable']),
                '#title' => __('Allow users to post reviews', 'sabai-directory'),
            ),
        );
        $listing_tabs = $this->getAddon()->getConfig('display', 'listing_tabs');
        $form['unclaimed']['tabs'] = array(
            '#title' => __('Single listing page tabs', 'sabai-directory'),
            '#type' => 'checkboxes',
            '#options' => isset($listing_tabs['options']) ? array_diff_key($listing_tabs['options'], $this->getAddon()->getListingDefaultTabs()) : array(),
            '#default_value' => @$config['unclaimed']['tabs'],
        );
        
        return $form;
    }
    
    public function isFieldRequired($form, $parents, $dependee = 'limit')
    {
        $values = $form->getValue($parents);
        return !empty($values[$dependee]);
    }
    
    protected function _getSuccessUrl(Sabai_Context $context)
    {
        return $this->Url($context->getRoute());
    }
    
    protected function _saveConfig(Sabai_Context $context, array $values)
    {
        // Save claim form header to platform instead of the add-ons table
        $this->getPlatform()->setOption($this->getAddon()->getName() . '_claim_form_header', $values['claim_form_header']);
        unset($values['claim_form_header']);
        $this->getAddon()->saveConfig(array('claims' => array('allow_existing' => $this->getAddon()->getConfig('claims', 'allow_existing')) + $values));
        
        // Clear cache to reload entity fields in case allowed fields changed
        $this->Entity_FieldCacheImpl()->entityFieldCacheClean();
    }
}