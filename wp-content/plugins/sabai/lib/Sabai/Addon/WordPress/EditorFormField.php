<?php
class Sabai_Addon_WordPress_EditorFormField extends Sabai_Addon_Form_Field_AbstractField
{    
    private static $_editors = array();
    
    public function formFieldGetFormElement($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        if (empty($data['#rows'])) $data['#rows'] = get_option('default_post_edit_rows', 5);
        if ($this->_canRunWpEditor($name)) { 
            $data['#wp_editor_content'] = '';
            if (isset($data['#default_value'])) {
                $data['#wp_editor_content'] = $data['#default_value'];
            }
            $data['#default_value'] = $data['#value'] = null;
            $ele = $form->createHTMLQuickformElement('static', $name, $data['#label'], '');
            self::$_editors[$form->getFieldId($name) . '-editor'] = $ele;
        } else {
            $ele = $form->createHTMLQuickformElement('textarea', $name, $data['#label'], array('rows' => $data['#rows']));
        }
        
        return $ele;
    }
    
    public function formFieldOnSubmitForm($name, &$value, array &$data, Sabai_Addon_Form_Form $form)
    {
        // Do not mess with markdown formatted text
        $data['#no_trim'] = true;
        
        // Validate required/min_length/max_length settings if any
        if (false !== $validated = $this->_addon->getApplication()->Form_ValidateText($form, $value, $data, null, true, true)) {
            $value = $validated;
        }
    }

    public function formFieldOnCleanupForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {

    }

    public function formFieldOnRenderForm($name, array &$data, Sabai_Addon_Form_Form $form)
    {
        // Do not run on admin side for these elements, since it will be run by WP
        if ($this->_canRunWpEditor($name)) {
            $id = $form->getFieldId($name) . '-editor';
            $args = array(
                'wpautop' => true,
                'media_buttons' => current_user_can('upload_files'),
                'textarea_name' => $name,
                'textarea_rows' => $data['#rows'],
                'quicktags' => empty($data['#no_quicktags']),
                'tinymce' => empty($data['#no_tinymce']),
            );
            add_filter('mce_buttons', array(__CLASS__, 'mceButtonsFilter'), 99, 2);
            add_filter('quicktags_settings', array(__CLASS__, 'quickTagsSettingsFilter'), 99, 2);
            ob_start();
            wp_editor($data['#wp_editor_content'], $id, $args);
            self::$_editors[$id]->setValue(ob_get_clean());
        }
        $form->renderElement($data);
    }
    
    protected function _canRunWpEditor($name)
    {
        return !is_admin() || !in_array($name, array('content_body[0]', 'taxonomy_body[0]'));
    }
	
    public static function mceButtonsFilter($buttons, $id)
    {
        return isset(self::$_editors[$id]) ? array_diff($buttons, array('wp_more')) : array();
    }
	
    public static function quickTagsSettingsFilter($settings, $id)
    {
        if (isset(self::$_editors[$id])) {
            $settings['buttons'] = str_replace(',more', '', $settings['buttons']);
        }
        return $settings;
    }
}