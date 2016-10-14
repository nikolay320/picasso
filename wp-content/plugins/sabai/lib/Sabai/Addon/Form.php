<?php
class Sabai_Addon_Form extends Sabai_Addon
    implements Sabai_Addon_Form_IFields
{
    const VERSION = '1.3.28', PACKAGE = 'sabai';
    const FORM_BUILD_ID_NAME = '_sabai_form_build_id', FORM_SUBMIT_BUTTON_NAME = '_sabai_form_submit',
        FORM_CALLBACK_WEIGHT_DEFAULT = 99999, FORM_ID_PLACEHOLDER = '_sabai_form_id';

    public function formGetFieldTypes()
    {
        return array('textarea', 'radio', 'radios', 'checkbox', 'checkboxes', 'select',
            'hidden', 'item', 'markup', 'password', 'textfield', 'fieldset', 'submit',
            'grid', 'tableselect', 'token', 'options', 'sectionbreak', 'text', 'search',
            'url', 'email', 'number', 'range', 'address', 'slider', 'icon', 'yesno', 'file',
            'autocomplete', 'user');
    }

    public function formGetField($type)
    {
        switch ($type) {
            case 'textarea':
                return new Sabai_Addon_Form_Field_Textarea($this);
            case 'radio':
                return new Sabai_Addon_Form_Field_Radio($this);
            case 'radios':
                return new Sabai_Addon_Form_Field_Radios($this);
            case 'checkbox':
                return new Sabai_Addon_Form_Field_Checkbox($this);
            case 'checkboxes':
                return new Sabai_Addon_Form_Field_Checkboxes($this);
            case 'select':
                return new Sabai_Addon_Form_Field_Select($this);
            case 'hidden':
                return new Sabai_Addon_Form_Field_Hidden($this);
            case 'item':
                return new Sabai_Addon_Form_Field_Item($this);
            case 'markup':
                return new Sabai_Addon_Form_Field_Markup($this);
            case 'password':
                return new Sabai_Addon_Form_Field_Password($this);
            case 'textfield':
            case 'text':
            case 'search':
            case 'url':
            case 'email':
            case 'number':
                return new Sabai_Addon_Form_Field_Text($this);
            case 'range':
                return new Sabai_Addon_Form_Field_Range($this);
            case 'fieldset':
                return new Sabai_Addon_Form_Field_Fieldset($this);
            case 'submit':
                return new Sabai_Addon_Form_Field_Submit($this);
            case 'grid':
                return new Sabai_Addon_Form_Field_Grid($this);
            case 'tableselect':
                return new Sabai_Addon_Form_Field_TableSelect($this);
            case 'token':
                return new Sabai_Addon_Form_Field_Token($this);
            case 'options':
                return new Sabai_Addon_Form_Field_Options($this);
            case 'sectionbreak':
                return new Sabai_Addon_Form_Field_SectionBreak($this);
            case 'address':
                return new Sabai_Addon_Form_Field_Address($this);
            case 'slider':
                return new Sabai_Addon_Form_Field_Slider($this);
            case 'icon':
                return new Sabai_Addon_Form_Field_Icon($this);
            case 'yesno':
                return new Sabai_Addon_Form_Field_YesNo($this);
            case 'file':
                return new Sabai_Addon_Form_Field_File($this);
            case 'autocomplete':
                return new Sabai_Addon_Form_Field_Autocomplete($this);
            case 'user':
                return new Sabai_Addon_Form_Field_User($this);
        }
    }

    public function setFormStorage($formBuildId, $storage)
    {
        $this->_application->getPlatform()->setSessionVar('form_' . $formBuildId, $storage);
    }

    public function getFormStorage($formBuildId)
    {
        return $this->_application->getPlatform()->getSessionVar('form_' . $formBuildId);
    }

    public function clearFormStorage($formBuildId)
    {
        return $this->_application->getPlatform()->deleteSessionVar('form_' . $formBuildId);
    }

    public function onFormIFieldsInstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        $this->_application->getPlatform()->deleteCache('form_fields');
    }

    public function onFormIFieldsUninstalled(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        $this->_application->getPlatform()->deleteCache('form_fields');
    }

    public function onFormIFieldsUpgraded(Sabai_Addon $addon, ArrayObject $log)
    {
        if ($addon->hasParent()) return;
        
        $this->_application->getPlatform()->deleteCache('form_fields');
    }
    
    public function getSubmittedFiles($name)
    {
        if (empty($_FILES)) return array();

        if (isset($_FILES[$name])) return $_FILES[$name];

        if (false === $pos = strpos($name, '[')) return array();

        $base = substr($name, 0, $pos);
        $key = str_replace(array(']', '['), array('', '"]["'), substr($name, $pos + 1, -1));
        $code = array(sprintf('if (!isset($_FILES["%1$s"]["name"]["%2$s"]) || $_FILES["%1$s"]["error"]["%2$s"] === UPLOAD_ERR_NO_FILE) return array();', $base, $key));
        $code[] = '$file = array();';
        foreach (array('name', 'type', 'size', 'tmp_name', 'error') as $property) {
            $code[] = sprintf('$file["%1$s"] = $_FILES["%2$s"]["%1$s"]["%3$s"];', $property, $base, $key);
        }
        $code[] = 'return $file;';

        return eval(implode(PHP_EOL, $code));
    }
    
    public function initTextFieldSettings(Sabai_Addon_Form_Form $form, array &$data)
    {
        $data['#attributes']['maxlength'] = !empty($data['#max_length']) ? $data['#max_length'] : 255;
        if (!empty($data['#width'])) {
            $style_width = 'width:' . $data['#width'];
        } else {
            if (!empty($data['#size'])) {
                $data['#attributes']['size'] = $data['#size'];
                $style_width = 'width:' . ceil($data['#size'] * 0.9) . 'em;';
            } else {
                if (empty($data['#no_autosize'])) {
                    if (isset($data['#field_prefix']) && isset($data['#field_suffix'])) {
                        $style_width = 'width:85%;';
                    } elseif (isset($data['#field_prefix']) || isset($data['#field_suffix'])) {
                        $style_width = 'width:90%;';
                    } else {
                        $style_width = 'width:100%;';
                    }
                }
            }
        }
        if (isset($style_width)) {
            if (!isset($data['#attributes']['style'])) {
                $data['#attributes']['style'] = $style_width;
            } else {
                $data['#attributes']['style'] .= $style_width;
            }
        }
        // Auto populate field?
        if (!isset($data['#default_value'])) {
            if (isset($data['#auto_populate'])) {
                switch ($data['#auto_populate']) {
                    case 'email':
                        $data['#default_value'] = $this->_application->getUser()->email;
                        break;
                    case 'url':
                        $data['#default_value'] = $this->_application->getUser()->url;
                        break;
                    case 'username':
                        $data['#default_value'] = $this->_application->getUser()->username;
                        break;
                    case 'name':
                        $data['#default_value'] = $this->_application->getUser()->name;
                        break;
                }
            }
        }
        
        if (!isset($data['#attributes']['placeholder'])) {
            if ($data['#type'] === 'url' || @$data['#char_validation'] === 'url') {
                $data['#attributes']['placeholder'] = 'http://';
            }
        }
    }
}