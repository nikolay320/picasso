<?php
class Sabai_Addon_WordPress_Helper_Postbox extends Sabai_Helper
{    
    public function help(Sabai $application, $arg, $box)
    {
        echo '<div class="sabai-form-fields">';
        $form = $box['args'][0];
        if (isset($form['elements'])) {
            foreach ($form['elements'] as $element) {
                $this->_renderFormElement($element);
            } 
        } else {
            // Field that has accept_multple set to true but renders a single element
            if (isset($form['label'][0])) {
                unset($form['label'][0]); // postbox already has a title, so do not display within content
            }
            echo $this->_doRenderFormElement($form);
        }
        echo '</div>';
    }

    private function _renderFormElement($element)
    {
        if (!empty($element['elements'])) {
            // Fieldset
            $class = isset($element['class']) ? $element['class'] : '';
            if (isset($element['error'])) {
                $class .= ' sabai-form-field-error';
            }
            $required = !empty($element['required']);
            echo '<fieldset id="'. $element['id'] .'" class="sabai-form-field '. Sabai::h($class) .'">';
            if ($element['label'][0]) {
                if ($required) {
                    echo '<legend><span>' . Sabai::h($element['label'][0]) . '</span><span class="sabai-form-field-required">*</span></legend>';
                } else {
                    echo '<legend><span>' . Sabai::h($element['label'][0]) . '</span></legend>';
                }
            }
            echo '<div class="sabai-form-fields">';
            foreach ($element['elements'] as $_element) {
                $this->_renderFormElement($_element);
            }
            echo '</div>';
            if (isset($element['error'])) {
                echo '<span class="sabai-form-field-error">' . Sabai::h($element['error']) . '</span>';
            }
            if (isset($element['label'][1]) && strlen($element['label'][1])) {
                echo '<div class="sabai-form-field-description">'. $element['label'][1] .'</div>';
            }
            echo '</fieldset>';
        } else {
            // Field
            foreach ($element as $_element) {
                echo $this->_doRenderFormElement($_element);
            }
        }
    }

    private function _doRenderFormElement($element)
    {
        if ($element['type'] === 'hidden') {
            echo $element['html'];
            return;
        }
        if ($element['type'] === 'static' && !isset($element['label'][0])) { // markup form type does not have orig_label
            echo $element['html'];
            if (isset($element['error'])) {
                echo '<span class="sabai-form-field-error">' . Sabai::h($element['error']) . '</span>';
            }
            return;
        }
        $class = isset($element['class']) ? $element['class'] : '';
        if (isset($element['error'])) {
            $class .= ' sabai-form-field-error';
        }
        $required = !empty($element['required']);
        if (isset($element['label'][0]) && strlen($element['label'][0])) {
            if ($required) {
                $label = '<div class="sabai-form-field-label"><span>'. $element['label'][0] .'</span><span class="sabai-form-field-required">*</span></div>';
            } else {
                $label = '<div class="sabai-form-field-label"><span>'. $element['label'][0] .'</span></div>';
            }
        } else {
            $label = '';
        }
    
        printf(
            '%s
<div id="%s" class="sabai-form-field %s">
  %s
  %s
  %s
  %s
  %s
  %s
</div>
%s',
            isset($element['prefix']) ? $element['prefix'] : '',
            isset($element['id']) ? $element['id'] : '',
            $class,
            $label,
            isset($element['field_prefix']) ? '<span class="sabai-form-field-prefix">' . $element['field_prefix'] . '</span>' : '',
            $element['html'],
            isset($element['field_suffix']) ? '<span class="sabai-form-field-suffix">'. $element['field_suffix'] . '</span>' : '',
            isset($element['error']) ? '<span class="sabai-form-field-error">' . Sabai::h($element['error']) . '</span>' : '',
            !empty($element['label'][1]) ? '<div class="sabai-form-field-description">' . $element['label'][1] . '</div>' : '',
            isset($element['suffix']) ? $element['suffix'] : ''
        );
    }
}