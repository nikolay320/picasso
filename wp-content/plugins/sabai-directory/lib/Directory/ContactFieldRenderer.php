<?php
class Sabai_Addon_Directory_ContactFieldRenderer extends Sabai_Addon_Field_Renderer_AbstractRenderer
{
    protected function _fieldRendererGetInfo()
    {
        return array(
            'field_types' => array($this->_name),
            'default_settings' => array(
                'hide' => array(),
                'phone' => array(
                    'icon' => 'phone',
                ),
                'mobile' => array(
                    'icon' => 'mobile',
                ),
                'fax' => array(
                    'icon' => 'fax',
                ),
                'email' => array(
                    'type' => 'email',
                    'label' => __('Send E-mail', 'sabai-directory'),
                    'icon' => 'envelope',
                ),
                'website' => array(
                    'type' => 'url',
                    'length' => 50,
                    'label' => __('Website', 'sabai-directory'),
                    'rel' => array('nofollow', 'external'),
                    'icon' => 'globe',
                )
            ),
        );
    }
    
    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array())
    {
        return array(
            'hide' => array(
                '#title' => __('Hidden fields', 'sabai-directory'),
                '#type' => 'checkboxes',
                '#options' => array(
                    'phone' => __('Phone Number', 'sabai-directory'),
                    'mobile' => __('Mobile Number', 'sabai-directory'),
                    'fax' => __('Fax Number', 'sabai-directory'),
                    'email' => __('E-mail', 'sabai-directory'),
                    'website' => __('Website', 'sabai-directory'),
                ),
                '#default_value' => $settings['hide'],
                '#class' => 'sabai-form-inline',
            ),
            'phone' => array(
                '#title' => __('Phone Number', 'sabai-directory'),
                '#class' => 'sabai-form-group',
                '#tree' => true,
                '#collapsible' => false,
                '#states' => array(
                    'invisible' => array(
                        sprintf('input[name="%s[hide][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 'phone'),
                    ),
                ),
                'icon' => array(
                    '#type' => 'icon',
                    '#field_prefix' => __('Icon:', 'sabai-directory'),
                    '#default_value' => $settings['phone']['icon'],
                ),
            ),
            'mobile' => array(
                '#title' => __('Mobile Number', 'sabai-directory'),
                '#class' => 'sabai-form-group',
                '#tree' => true,
                '#collapsible' => false,
                '#states' => array(
                    'invisible' => array(
                        sprintf('input[name="%s[hide][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 'mobile'),
                    ),
                ),
                'icon' => array(
                    '#type' => 'icon',
                    '#field_prefix' => __('Icon:', 'sabai-directory'),
                    '#default_value' => $settings['mobile']['icon'],
                ),
            ),
            'fax' => array(
                '#title' => __('Fax Number', 'sabai-directory'),
                '#class' => 'sabai-form-group',
                '#tree' => true,
                '#collapsible' => false,
                '#states' => array(
                    'invisible' => array(
                        sprintf('input[name="%s[hide][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 'fax'),
                    ),
                ),
                'icon' => array(
                    '#type' => 'icon',
                    '#field_prefix' => __('Icon:', 'sabai-directory'),
                    '#default_value' => $settings['fax']['icon'],
                ),
            ),
            'email' => array(
                '#title' => __('E-mail', 'sabai-directory'),
                '#class' => 'sabai-form-group',
                '#tree' => true,
                '#collapsible' => false,
                '#states' => array(
                    'invisible' => array(
                        sprintf('input[name="%s[hide][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 'email'),
                    ),
                ),
                'type' => array(
                    '#type' => 'radios',
                    '#options' => array('email' => __('Display e-mail address', 'sabai-directory'), 'label' => __('Display custom label', 'sabai-directory')),
                    '#default_value' => $settings['email']['type'],
                    '#class' => 'sabai-form-inline',
                ),
                'label' => array(
                    '#type' => 'textfield',
                    '#default_value' => $settings['email']['label'],
                    '#states' => array(
                        'visible' => array(
                            sprintf('input[name="%s[email][type]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 'label'),
                        ),
                    ),
                ),
                'icon' => array(
                    '#type' => 'icon',
                    '#field_prefix' => __('Icon:', 'sabai-directory'),
                    '#default_value' => $settings['email']['icon'],
                ),
            ),
            'website' => array(
                '#title' => __('Website', 'sabai-directory'),
                '#class' => 'sabai-form-group',
                '#tree' => true,
                '#collapsible' => false,
                '#states' => array(
                    'invisible' => array(
                        sprintf('input[name="%s[hide][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 'website'),
                    ),
                ),
                'type' => array(
                    '#type' => 'radios',
                    '#options' => array('url' => __('Display URL', 'sabai-directory'), 'label' => __('Display custom label', 'sabai-directory')),
                    '#default_value' => $settings['website']['type'],
                    '#class' => 'sabai-form-inline',
                ),
                'length' => array(
                    '#type' => 'number',
                    '#field_prefix' => __('Maximum URL length (0 for unlimited):', 'sabai-directory'),
                    '#default_value' => $settings['website']['length'],
                    '#integer' => true,
                    '#size' => 3,
                    '#states' => array(
                        'visible' => array(
                            sprintf('input[name="%s[website][type]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 'url'),
                        ),
                    ),
                ),
                'label' => array(
                    '#type' => 'textfield',
                    '#default_value' => $settings['website']['label'],
                    '#states' => array(
                        'visible' => array(
                            sprintf('input[name="%s[website][type]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 'label'),
                        ),
                    ),
                ),
                'rel' => array(
                    '#class' => 'sabai-form-inline',
                    '#type' => 'checkboxes',
                    '#options' => array(
                        'nofollow' => __('Add rel="nofollow"', 'sabai-directory'),
                        'external' => __('Add rel="external"', 'sabai-directory'),
                    ),
                    '#default_value' => $settings['website']['rel'],
                ),
                'icon' => array(
                    '#type' => 'icon',
                    '#field_prefix' => __('Icon:', 'sabai-directory'),
                    '#default_value' => $settings['website']['icon'],
                ),
            ),
        );
    }

    public function fieldRendererRenderField(Sabai_Addon_Field_IField $field, array $settings, array $values, Sabai_Addon_Entity_IEntity $entity)
    {
        $ret = array();
        $value = $values[0];
        if (!empty($value['phone']) && !in_array('phone', $settings['hide'])) {
            $phone = Sabai::h($value['phone']);
            $ret[] = sprintf(
                '<div class="sabai-directory-contact-tel">%s<span class="sabai-hidden-xs" itemprop="telephone">%s</span><span class="sabai-visible-xs-inline"><a href="tel:%s">%s</a></span></div>',
                $settings['phone']['icon'] ? '<i class="fa fa-' . $settings['phone']['icon'] . ' fa-fw"></i> ' : '',
                $phone,
                preg_replace('/[^0-9]/','', $value['phone']),
                $phone
            );
        }
        if (!empty($value['mobile']) && !in_array('mobile', $settings['hide'])) {
            $mobile = Sabai::h($value['mobile']);
            $ret[] = sprintf(
                '<div class="sabai-directory-contact-mobile">%s<span class="sabai-hidden-xs" itemprop="telephone">%s</span><span class="sabai-visible-xs-inline"><a href="tel:%s">%s</a></span></div>',
                $settings['mobile']['icon'] ? '<i class="fa fa-' . $settings['mobile']['icon'] . ' fa-fw"></i> ' : '',
                $mobile,
                preg_replace('/[^0-9]/','', $value['mobile']),
                $mobile
            );
        }
        if (!empty($value['fax']) && !in_array('fax', $settings['hide'])) {
            $ret[] = sprintf(
                '<div class="sabai-directory-contact-fax">%s<span itemprop="faxNumber">%s</span></div>',
                $settings['fax']['icon'] ? '<i class="fa fa-' . $settings['fax']['icon'] . ' fa-fw"></i> ' : '',
                Sabai::h($value['fax'])
            );
        }
        if (!empty($value['email']) && !in_array('email', $settings['hide'])) {
            $email = antispambot($value['email']);
            $ret[] = sprintf(
                '<div class="sabai-directory-contact-email">%s<a href="mailto:%s" target="_blank">%s</a></div>',
                $settings['email']['icon'] ? '<i class="fa fa-' . $settings['email']['icon'] . ' fa-fw"></i> ' : '',
                $email,
                $settings['email']['type'] === 'email' ? $email : Sabai::h($settings['email']['label'])
            );
        }
        if (!empty($value['website']) && !in_array('website', $settings['hide'])) {
            $rel = array();
            foreach (array('nofollow', 'external') as $_rel) {
                if (in_array($_rel, $settings['website']['rel'])) {
                    $rel[] = $_rel;
                }
            }
            $ret[] = sprintf(
                '<div class="sabai-directory-contact-website">%s<a href="%s" target="_blank" rel="%s">%s</a></div>',
                $settings['website']['icon'] ? '<i class="fa fa-' . $settings['website']['icon'] . ' fa-fw"></i> ' : '',
                Sabai::h($value['website']),
                implode(' ', $rel),
                Sabai::h($this->_renderWebsite($value['website'], $settings))
            );  
        }
        return implode(PHP_EOL, $ret);
    }
    
    protected function _renderWebsite($website, $settings)
    {
        if ($settings['website']['type'] === 'url') {
            return empty($settings['website']['length']) ? $website : mb_strimwidth($website, 0, 50, '...');
        }
        return $settings['website']['label'];
    }
}
