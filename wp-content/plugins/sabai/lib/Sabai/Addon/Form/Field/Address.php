<?php
class Sabai_Addon_Form_Field_Address extends Sabai_Addon_Form_Field_Group
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $data = array(
            '#tree' => true,
            '#children' => array(
                0 => array(
                    'address_wrapper' => array(
                        '#type' => 'markup',
                        '#value' => '<div class="sabai-row">',
                    ) + $form->defaultElementSettings(),
                ),
            ),
        ) + $data + $form->defaultElementSettings();
        $data['#children'][0] += $this->_getAddressFormFields($data, $form);
        $data['#children'][0] += array(
            'address_wrapper_end' => array(
                '#type' => 'markup',
                '#value' => '</div>',
            ) + $form->defaultElementSettings(),
        );

        return $form->createFieldset($name, $data);
    }
    
    protected function _getAddressFormFields(array $data, Sabai_Addon_Form_Form $form)
    {
        $ret = array(
            'street' => array(
                '#description' => array_key_exists('#title_street', $data) ? $data['#title_street'] : __('Address Line 1', 'sabai'),
                '#type' => 'textfield',
                '#attributes' => isset($data['#class_street']) ? array('class' => $data['#class_street']) : array(),
                '#default_value' => @$data['#default_value']['street'],
                '#weight' => 1,
                '#class' => 'sabai-col-sm-12',
            ) + $form->defaultElementSettings(),
        );
        if (empty($data['#disable_street2'])) {
            $ret['street2'] = array(
                '#description' => array_key_exists('#title_street2', $data) ? $data['#title_street2'] : __('Address Line 2', 'sabai'),
                '#type' => 'textfield',
                '#attributes' => isset($data['#class_street2']) ? array('class' => $data['#class_street2']) : array(),
                '#required' => !empty($data['#require_street2']),
                '#default_value' => @$data['#default_value']['street2'],
                '#weight' => 2,
                '#class' => 'sabai-col-sm-12',
            ) + $form->defaultElementSettings();
        }
        $cols = 0;
        $last = null;
        if (@$data['#city_type'] !== 'disabled') {
            ++$cols;
            $last = 'city';
            $attr = isset($data['#class_city']) ? array('class' => $data['#class_city']) : array();
            $ret['city'] = array(
                '#description' => array_key_exists('#title_city', $data) ? $data['#title_city'] : __('City', 'sabai'),
                '#attributes' => isset($data['#city']) ? array('data-default-value' => $data['#city']) + $attr : $attr,
                '#class' => 'sabai-col-sm-6',
                '#weight' => 3,
                '#prefix' => $cols % 2 ? '<div class="sabai-row">' : null,
            );
            if (!empty($data['#city_type'])) {
                $options = $data['#city_type'] !== 'select'
                    ? $this->_addon->getApplication()->getHelperBroker()->callHelper($data['#city_type'])
                    : @$data['#cities']['options'];
                $ret['city'] += array(
                    '#type' => 'select',
                    '#options' => array('' => '') + (array)$options,
                    '#default_value' => isset($data['#default_value']['city']) ? $data['#default_value']['city'] : @$data['#cities']['default'][0],
                    '#empty_value' => '',
                );
            } else {
                $ret['city'] += array(
                    '#type' => 'textfield',
                    '#default_value' => isset($data['#default_value']['city']) ? $data['#default_value']['city'] : @$data['#city'],
                );
            }
            $ret['city'] += $form->defaultElementSettings();
        } elseif (strlen((string)@$data['#city'])) {
            // Add hidden field for geolocation search
            $ret['city'] = array(
                '#type' => 'markup',
                '#value' => sprintf('<input class="%1$s" type="hidden" value="%2$s" data-default-value="%2$s" />', isset($data['#class_city']) ? $data['#class_city'] : '', Sabai::h($data['#city'])),
                '#weight' => 5,
            ) + $form->defaultElementSettings();
        }
        if (@$data['#province_type'] !== 'disabled') {
            ++$cols;
            $last = 'state';
            $attr = isset($data['#class_province']) ? array('class' => $data['#class_province']) : array();
            $ret['state'] = array(
                '#description' => array_key_exists('#title_province', $data) ? $data['#title_province'] : __('State / Province / Region', 'sabai'),
                '#attributes' => isset($data['#province']) ? array('data-default-value' => $data['#province']) + $attr : $attr,
                '#class' => 'sabai-col-sm-6',
                '#weight' => 4,
                '#prefix' => $cols % 2 ? '<div class="sabai-row">' : null,
                '#suffix' => $cols % 2 ? null : '</div>',
            );
            if (!empty($data['#province_type'])) {
                $options = $data['#province_type'] !== 'select'
                    ? $this->_addon->getApplication()->getHelperBroker()->callHelper($data['#province_type'])
                    : @$data['#provinces']['options'];
                $ret['state'] += array(
                    '#type' => 'select',
                    '#options' => array('' => '') + (array)$options,
                    '#default_value' => isset($data['#default_value']['state']) ? $data['#default_value']['state'] : @$data['#provinces']['default'][0],
                    '#empty_value' => '',
                );
            } else {
                $ret['state'] += array(
                    '#type' => 'textfield',
                    '#default_value' => isset($data['#default_value']['state']) ? $data['#default_value']['state'] : @$data['#province'],
                );
            }
            $ret['state'] += $form->defaultElementSettings();
        } elseif (strlen((string)@$data['#province'])) {
            // Add hidden field for geolocation search
            $ret['state'] = array(
                '#type' => 'markup',
                '#value' => sprintf('<input class="%1$s-hidden" type="hidden" value="%2$s" data-default-value="%2$s" />', isset($data['#class_province']) ? $data['#class_province'] : '', Sabai::h($data['#province'])),
                '#weight' => 5,
            ) + $form->defaultElementSettings();
        }
        if (@$data['#zip_type'] !== 'disabled') {
            ++$cols;
            $last = 'zip';
            $attr = isset($data['#class_zip']) ? array('class' => $data['#class_zip']) : array();
            $ret['zip'] = array(
                '#description' => array_key_exists('#title_zip', $data) ? $data['#title_zip'] : __('Postal / Zip Code', 'sabai'),
                '#attributes' => isset($data['#zip']) ? array('data-default-value' => $data['#zip']) + $attr : $attr,
                '#class' => 'sabai-col-sm-6',
                '#weight' => 5,
                '#prefix' => $cols % 2 ? '<div class="sabai-row">' : null,
                '#suffix' => $cols % 2 ? null : '</div>',
            );
            if (!empty($data['#zip_type'])) {
                $options = $data['#zip_type'] !== 'select'
                    ? $this->_addon->getApplication()->getHelperBroker()->callHelper($data['#zip_type'])
                    : @$data['#zips']['options'];
                $ret['zip'] += array(
                    '#type' => 'select',
                    '#options' => array('' => '') + (array)$options,
                    '#default_value' => isset($data['#default_value']['zip']) ? $data['#default_value']['zip'] : @$data['#zips']['default'][0],
                    '#empty_value' => '',
                );
            } else {
                $ret['zip'] += array(
                    '#type' => 'textfield',
                    '#default_value' => isset($data['#default_value']['zip']) ? $data['#default_value']['zip'] : @$data['#zip'],
                );
            }
            $ret['zip'] += $form->defaultElementSettings();
        } elseif (strlen((string)@$data['#zip'])) {
            // Add hidden field for geolocation search
            $ret['zip'] = array(
                '#type' => 'markup',
                '#value' => sprintf('<input class="%1$s-hidden" type="hidden" value="%2$s" data-default-value="%2$s" />', isset($data['#class_zip']) ? $data['#class_zip'] : '', Sabai::h($data['#zip'])),
                '#weight' => 5,
            ) + $form->defaultElementSettings();
        }
        if (@$data['#country_type'] !== 'disabled') {
            ++$cols;
            $last = 'country';
            $attr = isset($data['#class_country']) ? array('class' => $data['#class_country']) : array();
            $ret['country'] = array(
                '#description' => array_key_exists('#title_country', $data) ? $data['#title_country'] : __('Country', 'sabai'),
                '#attributes' => isset($data['#country']) ? array('data-default-value' => $data['#country']) + $attr : $attr,
                '#class' => 'sabai-col-sm-6',
                '#weight' => 6,
                '#prefix' => $cols % 2 ? '<div class="sabai-row">' : null,
                '#suffix' => $cols % 2 ? null : '</div>',
            );
            if (!empty($data['#country_type'])) {
                $options = array();
                if ($data['#country_type'] === 'select') {
                    $options = isset($data['#countries']['options']) ? $data['#countries']['options'] : $this->_addon->getApplication()->Countries();
                } else {
                    $options = $this->_addon->getApplication()->getHelperBroker()->callHelper($data['#country_type']);
                }
                $ret['country'] += array(
                    '#type' => 'select',
                    '#options' => array('' => '') + (array)$options,
                    '#default_value' => isset($data['#default_value']['country']) ? $data['#default_value']['country'] : @$data['#countries']['default'][0],
                    '#empty_value' => '',
                );
            } else {
                $ret['country'] += array(
                    '#type' => 'textfield',
                    '#default_value' => isset($data['#default_value']['country']) ? $data['#default_value']['country'] : @$data['#country'],
                );
            }
            $ret['country'] += $form->defaultElementSettings();
        } elseif (strlen((string)@$data['#country'])) {
            // Add hidden field for geolocation search
            $ret['country'] = array(
                '#type' => 'markup',
                '#value' => sprintf('<input class="%1$s-hidden" type="hidden" value="%2$s" data-default-value="%2$s" />', isset($data['#class_country']) ? $data['#class_country'] : '', Sabai::h($data['#country'])),
                '#weight' => 6,
            ) + $form->defaultElementSettings();
        }
        if ($last && $cols % 2) {
            // Close the last row
            $ret[$last]['#suffix'] = '</div>';
        }
        
        return $ret;
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        parent::formFieldOnSubmitForm($name, $value, $data, $form);
        
        if ($form->hasError()) return;
        
        $value = array_filter($value);
        
        foreach (array('city', 'state', 'zip', 'country') as $key) {
            if (isset($data['#' . $key . '_type']) && $data['#' . $key . '_type'] === 'disabled' && strlen((string)@$data['#' . $key])) {
                $value[$key] = $data['#' . $key];
            }
        }
    }
}
