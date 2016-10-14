<?php
class Sabai_Addon_Form_Field_Checkboxes extends Sabai_Addon_Form_Field_Radios
{
    protected function _createRadioButton(array $data, $value, $label)
    {
        $ret = parent::_createRadioButton($data, $value, $label);
        $ret['#type'] = 'checkbox';

        return $ret;
    }

    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        parent::formFieldOnSubmitForm($name, $value, $data, $form);

        if (!empty($data['#max_selection']) && count($value) > $data['#max_selection']) {
            $form->setError(sprintf(__('Maximum of %d selections allowed.', 'sabai'), $data['#max_selection']), $name);
        }
    }
}