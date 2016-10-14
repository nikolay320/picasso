<?php
class Sabai_Addon_Form_Field_Submit extends Sabai_Addon_Form_Field_AbstractField
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (isset($data['#src'])) {
            $type = 'image';
            $data['#value'] = $data['#src'];
        } else {
            $type = 'submit';
            if (!isset($data['#value'])) {
                if (!isset($data['#default_value'])) {
                    $data['#value'] = __('Submit', 'sabai');
                } else {
                    $data['#value'] = $data['#default_value'];
                }
            }
            $button_class = 'sabai-btn';
            if (isset($data['#btn_size'])
                && in_array($data['#btn_size'], array('sm', 'xs', 'lg'))
            ) {
                $button_class .= ' sabai-btn-' . $data['#btn_size'];
            }
            if (isset($data['#btn_type'])
                && in_array($data['#btn_type'], array('primary', 'success', 'danger', 'warning', 'info', 'link'))
            ) {
                $button_class .= ' sabai-btn-' . $data['#btn_type'];
            } else {
                $button_class .= ' sabai-btn-default';
            }
            if (!empty($data['#btn_block'])) {
                $button_class .= ' sabai-btn-block';
            }
            if (isset($data['#attributes']['class'])) {
                $data['#attributes']['class'] .= ' ' . $button_class;
            } else {
                $data['#attributes']['class'] = $button_class;
            }
        }
        $data['#default_value'] = null;

        return $form->createHTMLQuickformElement($type, $name, $data['#value'], $data['#attributes']);
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!isset($value)) return; // the button was not clicked

        // Save as clicked button
        $form->setClickedButton($data);

        // Move validate/submit handlers for this button to the global scope
        if (!empty($data['#validate'])) {
            foreach ($data['#validate'] as $callback) {
                $form->settings['#validate'][] = $callback;
            }
        }
        if (!empty($data['#submit'])) {
            foreach ($data['#submit'] as $key => $callback) {
                $form->settings['#submit'][$key][] = $callback;
            }
        }

        if (!empty($data['#skip_validate'])) {
            $form->settings['#skip_validate'] = true;
        }
    }

    public function formFieldOnCleanupForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnRenderForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        $form->renderElement($data);
    }
}