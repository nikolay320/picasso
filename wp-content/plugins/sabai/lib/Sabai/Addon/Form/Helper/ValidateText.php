<?php
class Sabai_Addon_Form_Helper_ValidateText extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Form_Form $form, $value, array $element, $errorElementName = null, $checkRequired = true, $isHtml = false)
    {
        if (!isset($errorElementName)) $errorElementName = $element['#name'];

        if (!empty($element['#char_validation'])
            && in_array($element['#char_validation'], array('integer', 'numeric', 'alnum', 'alpha', 'lower', 'upper', 'url', 'email'))
        ) {
            $element['#' . $element['#char_validation']] = true;
        }

        if (empty($element['#no_trim'])) {
            $value = $application->Trim($value);
        }
        
        // Remove value sent from placeholder
        if ($element['#type'] === 'url' && $value === 'http://') {
            $value = '';
        }
        
        if (strlen($value) === 0) {
            if ($checkRequired) {
                if ($form->isFieldRequired($element)) {
                    $form->setError(isset($element['#required_error_message']) ? $element['#required_error_message'] : __('Please fill out this field.', 'sabai'), $errorElementName);
                    return false;
                }
            }
            return $value;
        }

        if (!empty($element['#integer'])) {
            if (!preg_match('/^-?\d+$/', $value)) {
                $form->setError(__('The input value must be an integer.', 'sabai'), $errorElementName);
                return false;
            }
        } elseif (!empty($element['#numeric'])) {
            if (!is_numeric($value)) {
                $form->setError(__('The input value must be numeric.', 'sabai'), $errorElementName);
                return false;
            }
        } elseif (!empty($element['#alpha'])) {
            if (!ctype_alpha($value)) {
                $form->setError(__('The input value must consist of alphabets only.', 'sabai'), $errorElementName);
                return false;
            }
        } elseif (!empty($element['#alnum'])) {
            if (!ctype_alnum($value)) {
                $form->setError(__('The input value must consist of alphanumeric characters only.', 'sabai'), $errorElementName);
                return false;
            }
        } elseif (!empty($element['#lower'])) {
            if (!ctype_lower($value)) {
                $form->setError(__('The input value must consist of lowercasae characters only.', 'sabai'), $errorElementName);
                return false;
            }
        } elseif (!empty($element['#upper'])) {
            if (!ctype_upper($value)) {
                $form->setError(__('The input value must consist of uppercase characters only.', 'sabai'), $errorElementName);
                return false;
            }
        } elseif ($element['#type'] === 'url' || !empty($element['#url'])) {
            $value_to_check = $value;
            if (0 === strpos($value, '//')) {
                if (!empty($element['#allow_url_no_protocol'])) {
                    $value_to_check = 'http:' . $value;
                }
            } elseif (false === strpos($value, '://')) {
                $value_to_check = $value = 'http://' . $value;
            }
            // Add a temporary fix for php 5.2.13/5.3.2 returning false for URLs containing hyphens
            $php_version = substr(PHP_VERSION, 0, strpos(PHP_VERSION, '-')); // remove extra version info
            if (version_compare($php_version, '5.3.2', '==') || version_compare($php_version, '5.2.13', '==')) {
                $value_to_check = str_replace('-', '', $value_to_check);
            }
            if (!preg_match('#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#', $value_to_check)) { // supports IDN
            //if (!filter_var($value_to_check, FILTER_VALIDATE_URL)) {
                $form->setError(__('The input value is not a valid URL.', 'sabai'), $errorElementName);
                return false;
            }
        } elseif ($element['#type'] === 'email' || !empty($element['#email'])) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $form->setError(__('The input value is not a valid E-mail address.', 'sabai'), $errorElementName);
                return false;
            }
        }

        // Check min/max length
        $min_length = empty($element['#min_length']) ? null : (int)$element['#min_length'];
        $max_length = empty($element['#max_length']) ? null : (int)$element['#max_length'];
        $value_length = mb_strlen($isHtml ? strip_tags(html_entity_decode($value)) : $value, SABAI_CHARSET);
        if ($max_length && $min_length) {
            if ($max_length === $min_length) {
                if ($value_length !== $max_length) {
                    $form->setError(sprintf(__('The input value must be %d characters.', 'sabai'), $max_length), $errorElementName);
                    return false;
                }
            } else {
                if ($value_length < $min_length || $value_length > $max_length) {
                    $form->setError(sprintf(__('The input value must be between %d and %d characters.', 'sabai'), $min_length, $max_length), $errorElementName);
                    return false;
                }
            }
        } elseif ($max_length) {
            if ($value_length > $max_length) {
                $form->setError(sprintf(__('The input value must be shorter than %d characters.', 'sabai'), $max_length), $errorElementName);
                return false;
            }
        } elseif ($min_length) {
            if ($value_length < $min_length) {
                $form->setError(sprintf(__('The input value must be longer than %d characters.', 'sabai'), $min_length), $errorElementName);
                return false;
            }
        }
        
        if (!empty($element['#integer']) || !empty($element['#numeric'])) {
            if (isset($element['#min_value'])) {
                if ($value < $element['#min_value']) {
                    $form->setError(sprintf(__('The value must be equal or greater than %s.', 'sabai'), $element['#min_value']), $errorElementName);
                    return false;
                }
            }
            if (isset($element['#max_value'])) {
                if ($value > $element['#max_value']) {
                    $form->setError(sprintf(__('The value must not be greater than %s.', 'sabai'), $element['#max_value']), $errorElementName);
                    return false;
                }
            }
        }
        
        // Validate against regex?
        if (isset($element['#regex']) && strlen($element['#regex'])) {
            if (!preg_match($element['#regex'], $value, $matches)) {
                $form->setError(isset($element['#regex_error_message']) ? $element['#regex_error_message'] : sprintf(__('The input value did not match the regular expression: %s', 'sabai'), $element['#regex']), $errorElementName);
                return false;
            }
        }

        return $value;
    }
}