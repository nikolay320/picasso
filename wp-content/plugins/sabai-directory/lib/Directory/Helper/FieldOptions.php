<?php
class Sabai_Addon_Directory_Helper_FieldOptions extends Sabai_Helper
{
    public function help(Sabai $application, $bundleName, $addGuestFields = false)
    {
        $fields = $field_options = $field_options_disabled = array();
        $field_types = $application->Field_Types();
        foreach ($application->Entity_Field($bundleName) as $field_name => $field) {
            if ($field->getFieldDisabled()
                || $field_name === 'directory_claim'
                || $field_name === 'paidlistings_plan'
                || strpos($field_name, 'field_meta') === 0
            ) {
                continue;
            }
            $field_label = $field_types[$field->getFieldType()]['label'];
            if (!isset($fields[$field->getFieldWeight()])) {
                $fields[$field->getFieldWeight()] = array();
            }
            if ($field->isCustomField()) {
                $fields[$field->getFieldWeight()][$field_name] = sprintf('%s (%s)', $field, $field_label);
                continue;
            }
            if ($field_name === 'directory_contact') {
                $fields[$field->getFieldWeight()] += array(
                    'directory_contact_phone' => __('Phone Number', 'sabai-directory'),
                    'directory_contact_mobile' => __('Mobile Number', 'sabai-directory'),
                    'directory_contact_fax' => __('Fax Number', 'sabai-directory'),
                    'directory_contact_email' => __('E-mail', 'sabai-directory'),
                    'directory_contact_website' => __('Website', 'sabai-directory'),
                );
            } elseif ($field_name === 'directory_social') {
                $fields[$field->getFieldWeight()] += array(
                    'directory_social_twitter' => __('Twitter', 'sabai-directory'),
                    'directory_social_facebook' => __('Facebook URL', 'sabai-directory'),
                    'directory_social_googleplus' => __('Google+ URL', 'sabai-directory'),
                );
            } elseif ($field_name === 'content_guest_author') {
                if ($addGuestFields) {
                    $fields[$field->getFieldWeight()][$field_name] = (string)$field;
                    $field_options_disabled[] = $field_name;
                }
            } elseif ($field_name === 'directory_header_user') {
                if ($addGuestFields) {
                    $fields[$field->getFieldWeight()][$field_name] = sprintf('%s (%s)', $field, $field_label);
                }
            } else {
                if ($field->getFieldWidget()) {
                    if ($field->getFieldType() === 'sectionbreak') {
                        $fields[$field->getFieldWeight()][$field_name] = sprintf('%s (%s)', $field, $field_label);
                    } else {
                        $fields[$field->getFieldWeight()][$field_name] = (string)$field;
                        if ($field_name !== 'content_body') {
                            $field_options_disabled[] = $field_name;
                        }
                    }
                }
            }
        }
        ksort($fields);
        foreach ($fields as $_fields) {
            $field_options += $_fields;
        }
        
        return array($field_options, $field_options_disabled);
    }
}