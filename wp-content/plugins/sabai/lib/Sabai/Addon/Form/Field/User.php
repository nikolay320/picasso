<?php
class Sabai_Addon_Form_Field_User extends Sabai_Addon_Form_Field_Autocomplete
{
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (!empty($data['search_by_name'])) {
            $url = $this->_addon->getApplication()->Url('/sabai/user/_autocomplete', array('search_by_name' => (int)!empty($data['search_by_name']), Sabai_Request::PARAM_CONTENT_TYPE => 'json'), '', '&');
            $format = 'return "<img alt=\'" + item.name + "\' style=\'vertical-align:middle; height:20px;\' src=\'" + item.gravatar + "\' /> " + item.name';
        } else {
            $url = $this->_addon->getApplication()->Url('/sabai/user/_autocomplete', array(Sabai_Request::PARAM_CONTENT_TYPE => 'json'), '', '&');
            $format = 'return "<img alt=\'" + item.name + "\' style=\'vertical-align:middle; height:20px;\' src=\'" + item.gravatar + "\' /> " + item.username';
        }
        $defaults = array(
            '#ajax_url' => $url,
            '#default_items_callback' => array($this, 'getDefaultUsers'),
            '#noscript' => array('#type' => 'textfield'),
            '#tagging' => false,
            '#format_selection' => $format,
            '#format_result' => $format,
        );
        $data = $defaults + $data;
        if (!isset($data['#attributes']['placeholder'])) {
            $data['#attributes']['placeholder'] = __('Select User', 'sabai');
        }
        
        return parent::formFieldGetFormElement($name, $data, $form);        
    }

    public function getDefaultUsers($defaultValue, &$defaultItems, &$noscriptOptions)
    {
        $identities = $this->_addon->getApplication()->UserIdentities((array)$defaultValue);
        foreach ($identities as $identity) {
            $id = $identity->id;
            $text = $identity->name;
            $defaultItems[] = array(
                'id' => $id,
                'text' => Sabai::h($text),
                'username' => $identity->username,
                'gravatar' => $this->_addon->getApplication()
                    ->GravatarUrl($identity->email, Sabai::THUMBNAIL_SIZE_SMALL, $identity->gravatar_default, $identity->gravatar_rating),
            );
            $noscriptOptions[$id] = $text;
        }
    }
}