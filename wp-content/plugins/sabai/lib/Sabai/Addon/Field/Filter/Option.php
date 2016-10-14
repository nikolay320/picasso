<?php
class Sabai_Addon_Field_Filter_Option extends Sabai_Addon_Field_Filter_AbstractFilter
{
    protected $_multiple = true, $_emptyValue = '';
    
    protected function _fieldFilterGetInfo()
    {
        return array(
            'label' => __('Choice', 'sabai'),
            'field_types' => array('choice'),
            'default_settings' => array(
                'type' => 'checkboxes',
                'inline' => false,
                'show_more' => array('num' => 5, 'text' => null),
                'andor' => 'OR',
                'default_text' => _x('Any', 'option', 'sabai'),
            ),
            'is_fieldset' => true,
        );
    }

    public function fieldFilterGetSettingsForm(Sabai_Addon_Field_IField $field, array $settings, array $parents = array())
    {
        $form = array(
            'type' => array(
                '#title' => __('Form element type', 'sabai'),
                '#type' => 'radios',
                '#options' => array('checkboxes' => __('Checkboxes', 'sabai'), 'radios' => __('Radio buttons', 'sabai'), 'select' => __('Select list', 'sabai')),
                '#default_value' => $settings['type'],
                '#default_value_auto' => true,
                '#weight' => 5,
                '#class' => 'sabai-form-inline',
            ),
            'inline' => array(
                '#type' => 'checkbox',
                '#title' => __('Display inline', 'sabai'),
                '#description' => __('Check this to align all options on the same line.', 'sabai'),
                '#default_value' => $settings['inline'],
                '#weight' => 10,
                '#states' => array(
                    'invisible' => array(
                        sprintf('input[name="%s[type]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 'select'),
                    ),
                ),
            ),
            'show_more' => array(
                '#states' => array(
                    'invisible' => array(
                        sprintf('input[name="%s[type]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 'select'),
                    ),
                ),
                'num' => array(
                    '#type' => 'number',
                    '#integer' => true,
                    '#min_value' => 0,
                    '#title' => __('Number of options to display', 'sabai'),
                    '#description' => __('If there are more options than the number specified here, those options will be displayed in a popup window.', 'sabai'),
                    '#default_value' => $settings['show_more']['num'],
                    '#size' => 4,
                ),
                'text' => array(
                    '#type' => 'textfield',
                    '#title' => __('Label for link to open popup window', 'sabai'),
                    '#default_value' => isset($settings['show_more']['text']) ? $settings['show_more']['text'] : sprintf(__('More %s', 'sabai'), (string)$field),
                    '#states' => array(
                        'visible' => array(
                            sprintf('input[name="%s[show_more][num]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                                'type' => 'filled',
                                'value' => true,
                            ),
                        ),
                        'invisible' => array(
                            sprintf('input[name="%s[show_more][num]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 0),
                        ),
                    ),
                ),
                '#weight' => 15,
            ),
            'andor' => array(
                '#title' => __('Match any or all', 'sabai'),
                '#type' => 'radios',
                '#options' => array('OR' => __('Match any', 'sabai'), 'AND' => __('Match all', 'sabai')),
                '#default_value' => $settings['andor'],
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[type]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 'checkboxes'),
                    ), 
                ),
                '#class' => 'sabai-form-inline',
                '#weight' => 20,
            ),
            'default_text' => array(
                '#type' => 'textfield',
                '#title'=> __('Default text', 'sabai'),
                '#default_value' => $settings['default_text'],
                '#placeholder' => _x('Any', 'option', 'sabai'),
                '#weight' => 25,
                '#states' => array(
                    'invisible' => array(
                        sprintf('input[name="%s[type]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('value' => 'checkboxes'),
                    ),
                ),
            )
        );
        
        if (!$this->_multiple) {
            unset($form['type']['#options']['checkboxes']);
        }
        return $form;
    }
    
    public function fieldFilterGetForm(Sabai_Addon_Field_IField $field, $filterName, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $request = null, array $requests = null, $isSubmitOnChanage = true, array $parents = array())
    {
        $options = $this->_getOptions($field, $settings);
        $options_valid = array_keys($options);
        switch ($settings['type']) {
            case 'radios':
                if ($this->_showMoreLink($options, $settings)) {
                    $options = $this->_getViewableOptions($options, $settings, $request);
                    list($more_link, $js, $more_form) = $this->_getMoreOptions($field, $filterName, $settings, $request);
                }
                $options[$this->_emptyValue] = $settings['default_text'];
                $options_valid[] = $default_value = $this->_emptyValue; 
                break;
            case 'select':
                $options = array($this->_emptyValue => $settings['default_text']) + $options;
                $options_valid[] = $default_value = $this->_emptyValue; 
                break;
            case 'checkboxes':
            default:
                if ($this->_showMoreLink($options, $settings)) {
                    $options = $this->_getViewableOptions($options, $settings, $request);
                    list($more_link, $js, $more_form) = $this->_getMoreOptions($field, $filterName, $settings, $request);
                }
                $default_value = null;
                $settings['type'] = 'checkboxes';
        }
        return array(
            '#type' => $settings['type'],
            '#options' => $options,
            '#options_valid' => $options_valid,
            '#class' => $settings['inline'] ? 'sabai-form-inline' : null,
            '#field_suffix' => isset($more_link) ? $more_link : null,
            '#prefix' => isset($more_form) ? '<div style="display:none;" class="sabai-field-filter-option-more-options-' . str_replace('_', '-', $filterName) . '">'. $more_form .'</div>' : null,
            '#js' => @$js,
            '#default_value' => $default_value,
        );
    }
    
    protected function _showMoreLink(array $options, array $settings)
    {
        return $settings['show_more']['num'] > 0 && count($options) > $settings['show_more']['num'];
    }
    
    protected function _getViewableOptions(array $options, array $settings, $request)
    {
        $ret = array();
        $option_count = 0;
        if (isset($request)
            && ($request = array_intersect(array_keys($options), (array)$request))
        ) {
            foreach ($request as $_request) {
                $ret[$_request] = $options[$_request];
                unset($options[$_request]);
                ++$option_count;
                if ($option_count == $settings['show_more']['num']) break;
            }
        }
        if ($option_count < $settings['show_more']['num']) {
            $ret += array_slice($options, 0, $settings['show_more']['num'] - $option_count, true);
        }
        return $ret;
    }
    
    public function fieldFilterIsFilterable(Sabai_Addon_Field_IField $field, $filterName, array $settings, &$value, array $requests = null)
    {
        return $settings['type'] === 'checkboxes' ? !empty($value) : $value != $this->_emptyValue;
    }
    
    public function fieldFilterDoFilter(Sabai_Addon_Field_IQuery $query, Sabai_Addon_Field_IField $field, $filterName, array $settings, $value)
    {
        $value = (array)$value;
        if (count($value) === 1) {
            $query->addIsCriteria($field, 'value', array_shift($value));
        } elseif (!$this->_multiple || !$this->_isMultipleChoiceField($field) || $settings['andor'] === 'OR') {
            $query->addInCriteria($field, 'value', $value);
        } else {
            $query->startCriteriaGroup('AND')->addIsCriteria($field, 'value', array_shift($value));
            $i = 1;
            foreach ($value as $_value) {
                $query->addIsCriteria($field, 'value', $_value, $field->getFieldName() . ++$i);
            }
			$query->finishCriteriaGroup();
        }
    }
    
    public function fieldFilterGetPreview(Sabai_Addon_Field_IField $field, $filterName, array $settings)
    {
        if ($settings['type'] === 'select') {
            return '<select disabled="disabled"><option>' . Sabai::h($settings['default_text']) . '</option></select>';
        }
        
        $type = $settings['type'] === 'checkboxes' ? 'checkbox' : 'radio';
        $options = $this->_getOptions($field, $settings);
        $ret = array();
        if ($settings['show_more']['num'] > 0
            && count($options) > $settings['show_more']['num']
        ) {
            foreach (array_slice($options, 0, $settings['show_more']['num'], true) as $label) {
                $ret[] = sprintf('<input type="%s" disabled="disabled" />%s', $type, Sabai::h($label));
            }
            $ret[] = sprintf('<a disabled="disabled" class="sabai-field-filter-option-more">%s</a>', Sabai::h($settings['show_more']['text']));
        } else {
            foreach ($options as $label) {
                $ret[] = sprintf('<input type="%s" disabled="disabled" />%s', $type, Sabai::h($label));
            }
        }
        return implode($settings['inline'] ? '&nbsp;&nbsp;' : '<br />', $ret);
    }
    
    protected function _getMoreOptions(Sabai_Addon_Field_IField $field, $filterName, array $settings, $value)
    {
        $form = $this->_addon->getApplication()->Form_Build($this->_getMoreOptionsForm($filterName, $settings['type'], $value, $this->_getOptions($field, $settings)));
        $js = sprintf(
            "jQuery(document).ready(function($){
    var link = $('#%2\$s');
    link.click(function(e){
        var modal = SABAI.modal('<form class=\"sabai-form\" id=\"%1\$s\">' + link.closest('form').find('.sabai-field-filter-option-more-options-%5\$s').html() + '</form>', '%3\$s', 600);
        modal.find('input[name=\"%4\$s\"]').prop('disabled', false);
        $('#%1\$s').submit(function(e){
            var form = link.closest('form'), inputs = form.find('input[name=\"%4\$s\"]').prop('checked', false);
            $(this).find(':checked').each(function() {
                var input = inputs.filter('[value=\"' + this.value + '\"]');
                if (input.length) {
                    input.prop('checked', true).prop('disabled', false);
                }
            });
            e.preventDefault();
            modal.hide();
            form.submit();
        });
        e.preventDefault();
    });
});",
            $form->settings['#id'],
            $link_id = 'sabai-field-filter-option-' . md5(uniqid(mt_rand(), true)),
            $settings['show_more']['text'],
            $settings['type'] === 'checkboxes' ? $filterName . '[]' : $filterName,
            str_replace('_', '-', $filterName)
        );
        return array(
            $this->_addon->getApplication()->LinkTo($settings['show_more']['text'], '#', array(), array('id' => $link_id, 'class' => 'sabai-field-filter-option-more')),
            $js,
            $this->_addon->getApplication()->Form_Render($form, '', true)
        );
    }
    
    protected function _getMoreOptionsForm($filterName, $type, $value, $options)
    {
        return array(
            '#build_id' => false,
            '#token' => false,
            $filterName => array(
                '#type' => $type,
                '#options' => $options,
                '#options_disabled' => array_keys($options),
                '#default_value' => $value,
            ),
            Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME => $this->_addon->getApplication()->Form_SubmitButtons(),
        );
    }
    
    protected function _getOptions(Sabai_Addon_Field_IField $field, array $settings)
    {
        $field_settings = $field->getFieldSettings();
        return (array)@$field_settings['options']['options'];
    }
    
    protected function _isMultipleChoiceField(Sabai_Addon_Field_IField $field)
    {
        return $field->getFieldWidget() === 'checkboxes'
            || ($field->getFieldWidget() === 'select' && $field->getFieldMaxNumItems() !== 1);
    }
}