<?php
abstract class Sabai_Addon_System_Controller_Admin_EmailSettings extends Sabai_Addon_Form_Controller
{    
    protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage)
    {
        $this->_cancelUrl = null;
        $this->_submitButtons[] = array('#value' => __('Save Changes', 'sabai'), '#btn_type' => 'primary');
        $this->_successFlash = __('Settings saved.', 'sabai');
        $current_settings = $this->_getCurrentEmailSettings($context);
        $form = array(
            '#tree' => true,
            'emails' => array(),
        );
        foreach ($this->Filter('system_email_settings', $this->_getEmailSettings($context), array($this->getAddon()->getName())) as $name => $email_settings) {
            if (!isset($email_settings['type'])) continue;
            
            $settings = isset($current_settings[$name]) ? $current_settings[$name] + $email_settings : $email_settings;
            $form['emails'][$name] = array(
                '#title' => $settings['title'],
                '#collapsed' => false,
                'enable' => array(
                    '#type' => 'checkbox',
                    '#title' => __('Enable this email notification', 'sabai'),
                    '#description' => $settings['description'],
                    '#default_value' => !empty($settings['enable']),
                    '#weight' => 1,
                ),
                'email' => array(
                    '#description' => sprintf(
                        __('Enter the message that should be sent with this notification. Available template tags: %s', 'sabai'),
                        implode(', ', array_merge($settings['tags'], $this->_getDefaultTags()))
                    ),
                    'subject' => array(
                        '#title' => __('Subject', 'sabai'),
                        '#type' => 'textfield',
                        '#default_value' => $settings['email']['subject'],
                    ),
                    'body' => array(
                        '#title' => __('Body', 'sabai'),
                        '#type' => 'textarea',
                        '#default_value' => $settings['email']['body'],
                        '#rows' => 6,
                    ),
                    '#states' => array(
                        'visible' => array(
                            'input[name="'. 'emails' .'['. $name . '][enable][]"]' => array('type' => 'checked', 'value' => true),
                        ),
                    ),
                    '#weight' => 10,
                ),
                // Save type which is useful for later use, when sending notifications
                'type' => array(
                    '#type' => 'hidden',
                    '#value' => $settings['type'],
                ),
            );
            if ($settings['type'] === 'roles') {
                $form['emails'][$name]['roles'] = array(
                    '#type' => 'checkboxes',
                    '#options' => $this->System_Roles('title', true),
                    '#title' => __('User Roles', 'sabai'),
                    '#description' => __('Select the user roles to which this email notification is sent.', 'sabai'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="'. 'emails' .'['. $name .'][enable][]"]' => array('type' => 'checked', 'value' => true),
                        ),
                    ),
                    '#default_value' => isset($settings['roles']) ? $settings['roles'] : null,
                    '#weight' => 5,
                );
            } elseif ($settings['type'] === 'user') {
                if (!empty($settings['has_guest_author'])) {
                    $form['emails'][$name]['send_to_guest'] = array(
                        '#type' => 'checkbox',
                        '#title' => __('Send this notification to guest authors', 'sabai'),
                        '#default_value' => !empty($settings['send_to_guest']),
                        '#weight' => 5,
                    );
                }
                $form['emails'][$name]['cc_roles'] = array(
                    '#type' => 'checkbox',
                    '#title' => __('Send a copy to users of selected roles', 'sabai'),
                    '#default_value' => !empty($settings['cc_roles']),
                    '#weight' => 6,
                );
                $form['emails'][$name]['roles'] = array(
                    '#type' => 'checkboxes',
                    '#options' => $this->System_Roles('title', true),
                    '#title' => __('User Roles', 'sabai'),
                    '#description' => __('Select the user roles to which a copy of this email notification is sent.', 'sabai'),
                    '#states' => array(
                        'visible' => array(
                            'input[name="'. 'emails' .'['. $name .'][cc_roles][]"]' => array('type' => 'checked', 'value' => true),
                        ),
                    ),
                    '#default_value' => isset($settings['roles']) ? $settings['roles'] : null,
                    '#weight' => 7,
                );
            }
        }
        
        return $form;
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
        $this->getPlatform()->setOption($this->getAddon()->getName() . '_emails', $form->values['emails']);
    }
    
    protected function _getCurrentEmailSettings(Sabai_Context $context)
    {
        return (array)$this->System_EmailSettings($this->getAddon()->getName());
    }
    
    protected function _getDefaultTags()
    {
        return array('{site_name}', '{site_email}', '{site_url}', '{recipient_name}');
    }
    
    /*
     * @return array
     */
    abstract protected function _getEmailSettings(Sabai_Context $context);
}