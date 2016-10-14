<?php
class Sabai_Addon_Markdown extends Sabai_Addon
    implements Sabai_Addon_Field_IWidgets,
               Sabai_Addon_Form_IFields,
               Sabai_Addon_System_IAdminSettings
{
    const VERSION = '1.3.28', PACKAGE = 'sabai';

    public function fieldGetWidgetNames()
    {
        return array('markdown_textarea');
    }

    public function fieldGetWidget($name)
    {
        return new Sabai_Addon_Markdown_FieldWidget($this, $name);
    }

    public function formGetFieldTypes()
    {
        return array('markdown_textarea');
    }

    public function formGetField($type)
    {
        return new Sabai_Addon_Markdown_FormField($this, $type);
    }
    
    public function getDefaultConfig()
    {
        return array(
            'help' => false,
            'help_url' => 'http://en.wikipedia.org/wiki/Markdown',
            'help_window' => array('width' => 720, 'height' => 480),
        );
    }
    
    public function systemGetAdminSettingsForm()
    {
        return array(
            'help' => array(
                '#type' => 'checkbox',
                '#title' => __('Enable markdown help', 'sabai'),
                '#default_value' => !empty($this->_config['help']),
            ),
            'help_url' => array(
                '#type' => 'url',
                '#default_value' => $this->_config['help_url'],
                '#title' => __('Help URL', 'sabai'),
                '#description' => __('Enter the URL of a page that will open up in a popup window when the help button on the Markdown editor is clicked.', 'sabai'),
                '#states' => array(
                    'visible' => array(
                        'input[name="help[]"]' => array(
                            'type' => 'checked',
                            'value' => true,
                        ),
                    ),
                ),
            ),
            'help_window' => array(
                '#class' => 'sabai-form-inline',
                '#title' => __('Help window dimension', 'sabai'),
                '#description' => __('Enter the dimension of the popup help window in pixels.', 'sabai'),
                '#collapsible' => false,
                'width' => array(
                    '#type' => 'textfield',
                    '#default_value' => $this->_config['help_window']['width'],
                    '#size' => 4,
                    '#integer' => true,
                    '#field_suffix' => 'x',
                ),
                'height' => array(
                    '#type' => 'textfield',
                    '#default_value' => $this->_config['help_window']['height'],
                    '#size' => 4,
                    '#integer' => true,
                ),
                '#states' => array(
                    'visible' => array(
                        'input[name="help[]"]' => array(
                            'type' => 'checked',
                            'value' => true,
                        ),
                    ),
                ),
            ),
        );
    }
}