<?php
class Sabai_Addon_Voting_RatingFormField extends Sabai_Addon_Form_Field_AbstractField
{
    private static $_renderCallbackRegistered = false;
    
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $data += array(
            '#rateit_min' => 0,
            '#rateit_max' => 5,
            '#step' => 0.5,
        );
        if (empty($data['#criteria'])) {
            $ele_id = $form->getFieldId($name);
            $data['#markup'] = $this->_renderRatingMarkup($ele_id, $data) . $this->_renderRatingHidden($ele_id, $name, $data);
        } else {
            // For compat with version < 1.3
            if (isset($data['#default_value']['']) && count($data['#default_value']) === 1) {
                foreach (array_keys($data['#criteria']) as $criteria) {
                    $data['#default_value'][$criteria] = $data['#default_value'][''];
                }
            }
            $markup = array('<table style="border:0; margin:0; padding:0;">');
            $hidden = array();
            foreach ($data['#criteria'] as $criterion => $label) {
                $ele_name = $name . '[' . $criterion . ']';
                $ele_id = $form->getFieldId($ele_name);
                $markup[] = '<tr>
    <th style="border:0; padding:5px 5px 5px 0; text-align:left; font-size:1em; line-height:1em; text-transform:none; letter-spacing:normal; font-weight:normal; vertical-align:middle;">'. $label . '</th>
    <td style="border:0; padding:5px 5px 5px 0; text-align:left; font-size:1em; line-height:1em; text-transform:none; letter-spacing:normal; vertical-align:middle;">'. $this->_renderRatingMarkup($ele_id, $data) .'</td>
</tr>';
                $hidden[] = $this->_renderRatingHidden($ele_id, $ele_name, $data, $criterion);
            }
            $markup[] = '</table>';
            $data['#markup'] = implode(PHP_EOL, array_merge($markup, $hidden));
        }
        
        // Register pre render callback if this is the first map element
        if (!self::$_renderCallbackRegistered) {
            $form->settings['#pre_render'][] = array($this, 'preRenderCallback');
            self::$_renderCallbackRegistered = true;
        }

        unset($data['#default_value'], $data['#value']);
        
        return $form->createElement('item', $name, $data);
    }
    
    protected function _renderRatingMarkup($id, $data)
    {
        return sprintf(
            '<div class="sabai-voting-rateit" data-rateit-backingfld="#%s" data-rateit-resetable="%s" data-rateit-ispreset="true" data-rateit-min="%d" data-rateit-max="%d" data-rateit-step="%d"></div>',
            $id,
            !isset($data['#rateit_resetable']) || $data['#rateit_resetable'] ? 'true' : 'false',
            $data['#rateit_min'],
            $data['#rateit_max'],
            $data['#step']
        );
    }
    
    protected function _renderRatingHidden($id, $name, $data, $criterion = '')
    {
        return sprintf(
            '<input type="hidden" id="%s" name="%s" value="%s" step="%s" />',
            $id,
            Sabai::h($name),
            Sabai::h(isset($data['#default_value'][$criterion]) ? $data['#default_value'][$criterion] : ''),
            Sabai::h($data['#step'])
        );
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (empty($data['#criteria'])) {
            $this->_validateValue($name, $value, $data, $form);
            return;
        }
        
        foreach (array_keys($data['#criteria']) as $criterion) {
            if (!$this->_validateValue($name, @$value[$criterion], $data, $form)) {
                return;
            }
        }
    }
    
    protected function _validateValue($name, $value, $data, Sabai_Addon_Form_Form $form)
    {
        if (strlen($value) === 0
            && $form->isFieldRequired($data)
        ) {
            $form->setError(isset($data['#required_error_message']) ? $data['#required_error_message'] : __('Please fill out this field.', 'sabai'), $name);
            return false;
        }
        if ($value < $data['#rateit_min'] || $value > $data['#rateit_max']) {
            $form->setError(sprintf(__('The input value must be between %d and %d.', 'sabai'), $data['#rateit_min'], $data['#rateit_max']), $name);
            return false;
        }
        if (($value * 100) % ($data['#step'] * 100)) {
            $form->setError(__('Invalid value.', 'sabai'), $name);
            return false;
        }
        return true;
    }

    public function formFieldOnCleanupForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnRenderForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
    }
    
    public function preRenderCallback($form)
    {
        $this->_addon->getApplication()->LoadJs('jquery.rateit.min.js', 'jquery-rateit', array('jquery'));
        $this->_addon->getApplication()->LoadCss('jquery.rateit.min.css', 'jquery-rateit');
        if ($this->_addon->getApplication()->getPlatform()->isLanguageRTL()) {
            $this->_addon->getApplication()->LoadCss('jquery.rateit-rtl.min.css', 'jquery-rateit-rtl', 'jquery-rateit');
        }
        $form->addJs('jQuery(document).ready(function ($) {
    $(".sabai-voting-rateit").rateit();
});');
    }
}