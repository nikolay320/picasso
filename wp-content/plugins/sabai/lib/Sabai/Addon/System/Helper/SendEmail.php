<?php
class Sabai_Addon_System_Helper_SendEmail extends Sabai_Helper
{    
    public function help(Sabai $application, $addonName, $name, array $tags = array(), $user = null, array $options = array())
    {
        if (!is_array($addonName)) {
            if (!$settings = $application->System_EmailSettings($addonName)) {
                $settings = $application->Filter('system_email_settings', array(), array($addonName));
            }
            if (empty($settings[$name]['enable'])) {
                return;
            }
            $settings = $settings[$name];
        } else {
            $settings = $addonName;
        }
        
        // Is it HTML?
        if (!isset($options['is_html'])) {
            $options['is_html'] = !empty($settings['email']['send_html']);
        }
        
        $recipients = array();
        if (@$settings['type'] === 'roles') {
            if (empty($settings['roles'])) {
                return; // no roles defined
            }
            foreach ($application->getPlatform()->getUsersByUserRole($settings['roles']) as $identity) {
                // if $user is set, it is the author of an updated content and make sure notification is not sent to the author
                if (isset($user) && $user->id === $identity->id) { 
                    continue;
                }
                if (!$identity->email) {
                    continue;
                }
                $recipients[$identity->email] = $identity->name;
            }
        } elseif (@$settings['type'] === 'admin') {
            foreach ($application->Administrators() as $identity) {
                if (!$identity->email) {
                    continue;
                }
                $recipients[$identity->email] = $identity->name;
            }
        } else {
            // $user is the recipient
            if (!isset($user)) {
                return;
            }
            if (is_object($user)) {
                if (!$user->email) {
                    return;
                }
                if (!$user->id && empty($settings['send_to_guest'])) {
                    return;
                }
                $recipients[$user->email] = $user->name;
            } elseif (is_array($user)) {
                foreach ($user as $identity) {
                    if (is_object($identity)) {
                        if (!$identity->email) {
                            continue;
                        }
                        if (!$identity->id && empty($settings['send_to_guest'])) {
                            continue;
                        }
                        $recipients[$identity->email] = $identity->name;
                    } elseif (is_array($identity)) {
                        $recipients[$identity['email']] = $identity['name'];
                    }
                }
            } else {
                return;
            }
            // CC to users of selected roles?
            if (!empty($settings['cc_roles']) && !empty($recipients) && !empty($settings['roles'])) {
                $cc_recipients = array();
                foreach ($application->getPlatform()->getUsersByUserRole($settings['roles']) as $identity) {
                    if ($identity->id === $user->id || !$identity->email) {
                        continue;
                    }
                    $cc_recipients[$identity->email] = $identity->name;
                }
            }
        }
        if (empty($recipients)
            || (!$recipients = $application->Filter('system_email_recipients', $recipients, array($addonName, $name)))) {
            return;
        }
        
        $tags += array(
            '{site_name}' => $application->getPlatform()->getSiteName(),
            '{site_email}' => $application->getPlatform()->getSiteEmail(),
            '{site_url}' => $application->getPlatform()->getSiteUrl(),
        );
        $subject = strtr($settings['email']['subject'], $tags);
        $body = strtr($settings['email']['body'], $tags);
        $search = array('{recipient_name}');
        foreach ($recipients as $recipient_email => $recipient_name) {
            $replace = array($recipient_name);
            $application->getPlatform()->mail($recipient_email, str_replace($search, $replace, $subject), str_replace($search, $replace, $body), $options);
        }
        if (!empty($cc_recipients)) {
            $body = array(
                __('---------- Forwarded message ----------', 'sabai'),
                sprintf(__('Sent From: %s', 'sabai'), $application->getPlatform()->getSiteEmail()),
                sprintf(1 === ($count = count($recipients)) ? __('Sent to: %s <%s>', 'sabai') : __('Sent to: %s <%s> and %d other recipient(s)', 'sabai'), $recipients[0]->name, $recipients[0]->email, $count),
                sprintf(__('Original Subject: %s', 'sabai'), $subject),
                '',
                $body
            );
            $subject = sprintf(__('FW: %s', 'sabai'), $subject);
            $body = implode("\n", $body);
            foreach ($cc_recipients as $cc_recipient_email => $cc_recipient_name) {
                $application->getPlatform()->mail($cc_recipient_email, str_replace($search, $replace, $subject), str_replace($search, $replace, $body), $options);
            }
        }
    }
}
