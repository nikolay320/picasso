<?php
class Sabai_Addon_Field_Renderer_Default extends Sabai_Addon_Field_Renderer_AbstractRenderer
{
    protected function _fieldRendererGetInfo()
    {
        $info = array(
            'field_types' => array($this->_name),
            'default_settings' => array(),
        );
        switch ($this->_name) {
            case 'boolean':
                $info['default_settings'] = array(
                    'on_label' => __('Yes', 'sabai'),
                    'off_label' => __('No', 'sabai'),
                );
                break;
            case 'user':
                $info['default_settings'] = array(
                    'separator' => ' ',
                    'format' => 'thumb_s_l',
                );
                break;
            case 'number':
                $info['default_settings'] = array(
                    'separator' => ' ',
                    'dec_point' => '.',
                    'thousands_sep' => ',',
                );
                break;
            case 'range':
                $info['default_settings'] = array(
                    'separator' => ' ',
                    'dec_point' => '.',
                    'thousands_sep' => ',',
                    'range_sep' => ' ' . _x('to', 'range separator', 'sabai') . ' ',
                );
                break;
            case 'link':
                $info['default_settings'] = array(
                    'separator' => ' ',
                    'target' => '_blank',
                    'rel' => array('nofollow', 'external'),
                    'show' => array('as' => 'text', 'btn' => 'sabai-btn-default'),
                );
                break;
            default:
                $info['default_settings'] = array(
                    'separator' => ', ',
                );
                break;
        }
        return $info;
    }

    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array())
    {
        switch ($this->_name) {
            case 'boolean':
                return array(
                    'on_label' => array(
                        '#type' => 'textfield',
                        '#size' => 10,
                        '#title' => __('ON label', 'sabai'),
                        '#default_value' => $settings['on_label'],
                    ),
                    'off_label' => array(
                        '#type' => 'textfield',
                        '#size' => 10,
                        '#title' => __('OFF label', 'sabai'),
                        '#default_value' => $settings['off_label'],
                    ),
                    
                );
            case 'user':
                return array(
                    'format' => array(
                        '#title' => __('Format', 'sabai'),
                        '#type' => 'select',
                        '#options' => array(
                            'link' => __('Link', 'sabai'),
                            'thumb_s' => __('Thumbnail (small)', 'sabai'),
                            'thumb_m' => __('Thumbnail (medium)', 'sabai'),
                            'thumb_l' => __('Thumbnail (large)', 'sabai'),
                            'thumb_s_l' => __('Thumbnail (small) with link', 'sabai'),
                            'thumb_m_l' => __('Thumbnail (medium) with link', 'sabai'),
                        ),
                        '#default_value' => $settings['format'],
                    ),
                );
            case 'number':
                return array(
                    'dec_point' => array(
                        '#type' => 'textfield',
                        '#size' => 3,
                        '#title' => __('Decimal point', 'sabai'),
                        '#default_value' => $settings['dec_point'],
                    ),
                    'thousands_sep' => array(
                        '#type' => 'textfield',
                        '#size' => 3,
                        '#title' => __('Thousands separator', 'sabai'),
                        '#default_value' => $settings['thousands_sep'],
                    ),
                );
            case 'range':
                return array(
                    'dec_point' => array(
                        '#type' => 'textfield',
                        '#size' => 3,
                        '#title' => __('Decimal point', 'sabai'),
                        '#default_value' => $settings['dec_point'],
                    ),
                    'thousands_sep' => array(
                        '#type' => 'textfield',
                        '#size' => 3,
                        '#title' => __('Thousands separator', 'sabai'),
                        '#default_value' => $settings['thousands_sep'],
                    ),
                    'range_sep' => array(
                        '#type' => 'textfield',
                        '#title' => __('Range separator', 'sabai'),
                        '#default_value' => $settings['range_sep'],
                        '#no_trim' => true,
                        '#size' => 10,
                    ),
                );
            case 'link':
                return array(
                    'target' => array(
                        '#title' => __('Open link in', 'sabai'),
                        '#type' => 'radios',
                        '#options' => array(
                            '_self' => __('Current window', 'sabai'),
                            '_blank' => __('New window', 'sabai'),
                        ),
                        '#class' => 'sabai-form-inline',
                        '#default_value' => $settings['target'],
                        '#weight' => 1,
                    ),
                    'rel' => array(
                        '#title' => __('Add to rel attribute'),
                        '#weight' => 2,
                        '#class' => 'sabai-form-inline',
                        '#type' => 'checkboxes',
                        '#options' => array(
                            'nofollow' => __('Add "nofollow"', 'sabai'),
                            'external' => __('Add "external"', 'sabai'),
                        ),
                        '#default_value' => $settings['rel'],
                    ),
                    'show' => array(
                        '#title' => __('Show link as', 'sabai'),
                        '#class' => 'sabai-form-group',
                        '#tree' => true,
                        '#collapsible' => false,
                        'as' => array(
                            '#type' => 'radios',
                            '#options' => array('text' => __('Text link', 'sabai'), 'btn' => __('Button', 'sabai')),
                            '#default_value' => $settings['show']['as'],
                            '#class' => 'sabai-form-inline',
                        ),
                        'btn' => array(
                            '#type' => 'radios',
                            '#options' => $this->_addon->getApplication()->ButtonOptions(__('Link', 'sabai'), 'mini'),
                            '#title_no_escape' => true,
                            '#default_value' => $settings['show']['btn'],
                            '#class' => 'sabai-form-inline',
                            '#states' => array(
                                'visible' => array(
                                    sprintf('input[name="%s[show][as]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 'btn'),
                                ),
                            ),
                        ),
                        '#weight' => 3,
                    ),
                );           
        }
    }

    public function fieldRendererRenderField(Sabai_Addon_Field_IField $field, array $settings, array $values, Sabai_Addon_Entity_IEntity $entity)
    {
        $type = $field->getFieldType();   
        return $this->$type($field, $settings, $values);
    }
    
    protected function string(Sabai_Addon_Field_IField $field, array $settings, array $values)
    {
        $ret = array();
        $field_settings = $field->getFieldSettings();
        if ($field_settings['char_validation'] === 'email') {
            $values = array_map('antispambot', $values);
        }
        foreach ($values as $value) {
            $ret[] = @$field_settings['prefix'] . Sabai::h($value) . @$field_settings['suffix'];
        }
        return implode($settings['separator'], $ret);
    }
    
    protected function email(Sabai_Addon_Field_IField $field, array $settings, array $values)
    {
        return implode($settings['separator'], array_map('antispambot', $values));
    }
    
    protected function phone(Sabai_Addon_Field_IField $field, array $settings, array $values)
    {
        return implode($settings['separator'], $values);
    }
    
    protected function number(Sabai_Addon_Field_IField $field, array $settings, array $values)
    {
        $ret = array();
        $field_settings = $field->getFieldSettings();
        $dec_point = isset($settings['dec_point']) ? $settings['dec_point'] : '.';
        $thousands_sep = isset($settings['thousands_sep']) ? $settings['thousands_sep'] : ',';
        foreach ($values as $value) {
            $ret[] = @$field_settings['prefix'] . number_format($value, $field_settings['decimals'], $dec_point, $thousands_sep) . @$field_settings['suffix'];
        }
        return implode($settings['separator'], $ret);
    }
    
    protected function range(Sabai_Addon_Field_IField $field, array $settings, array $values)
    {
        $ret = array();
        $field_settings = $field->getFieldSettings();
        $dec_point = isset($settings['dec_point']) ? $settings['dec_point'] : '.';
        $thousands_sep = isset($settings['thousands_sep']) ? $settings['thousands_sep'] : ',';
        foreach ($values as $value) {
            $ret[] = @$field_settings['prefix'] . number_format($value['min'], $field_settings['decimals'], $dec_point, $thousands_sep) . '<span class="sabai-field-range-separator">' . $settings['range_sep'] . '</span>'
                . number_format($value['max'], $field_settings['decimals'], $dec_point, $thousands_sep). @$field_settings['suffix'];
        }
        return implode($settings['separator'], $ret);
    }
        
    protected function choice(Sabai_Addon_Field_IField $field, array $settings, array $values)
    {
        $ret = array();
        $field_settings = $field->getFieldSettings();
        foreach ($values as $value) {
            if (isset($field_settings['options']['options'][$value])) {
                $ret[] = Sabai::h($field_settings['options']['options'][$value]);
            }
        }
        return implode($settings['separator'], $ret);
    }
            
    protected function boolean(Sabai_Addon_Field_IField $field, array $settings, array $values)
    {
        return empty($values[0]) ? $settings['off_label'] : $settings['on_label'];
    }    

    protected function user(Sabai_Addon_Field_IField $field, array $settings, array $values)
    {
        $ret = array();
        switch ($settings['format']) {
            case 'link':
                $helper = 'UserIdentityLink';
                break;
            case 'thumb_s':
                $helper = 'UserIdentityThumbnailSmall';
                break;
            case 'thumb_m':
                $helper = 'UserIdentityThumbnailMedium';
                break;
            case 'thumb_l':
                $helper = 'UserIdentityThumbnailLarge';
                break;
            case 'thumb_m_l':
                $helper = 'UserIdentityLinkWithThumbnailMedium';
                break;
            default:
                $helper = 'UserIdentityLinkWithThumbnailSmall';
                break;
        }
        foreach ($values as $value) {
            $ret[] = $this->_addon->getApplication()->$helper($value);
        }
        return implode($settings['separator'], $ret);
    }
           
    protected function link(Sabai_Addon_Field_IField $field, array $settings, array $values)
    {
        $ret = array();
        foreach ($values as $value) {
            $ret[] = $this->_link($field, $value, $settings);
        }
        return implode($settings['separator'], $ret);
    }
    
    protected function _link(Sabai_Addon_Field_IField $field, $value, $settings)
    {
        $field_settings = $field->getFieldSettings();
        $widget_settings = $field->getFieldWidgetSettings();
        if (isset($field_settings['target'])) { // for compat with 1.2
            $settings['target'] = $field_settings['target'];
            if ($field_settings['nofollow']) {
                $settings['rel'] = array('nofollow');
            }
            $widget_settings['title'] = $field_settings['title'];
        }
        $title_settings = $widget_settings['title'];
        return sprintf(
            '<a href="%s"%s rel="%s"%s>%s</a>',
            Sabai::h($value['url']),
            $settings['target'] === '_blank' ? ' target="_blank"' : '',
            implode(' ', $settings['rel']),
            $settings['show']['as'] === 'btn' ? ' class="sabai-btn ' . $settings['show']['btn'] . '"' : '',
            !empty($title_settings['no_custom']) && isset($title_settings['default']) && strlen($title_settings['default'])
                ? Sabai::h($title_settings['default'])
                : (strlen($value['title']) ? Sabai::h($value['title']) : Sabai::h($value['url']))
        );
    }
}