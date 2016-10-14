<?php
class Sabai_Addon_Field_Renderer_Text extends Sabai_Addon_Field_Renderer_AbstractRenderer
{    
    protected function _fieldRendererGetInfo()
    {
        return array(
            'field_types' => array('text', 'markdown_text'),
            'default_settings' => array('trim' => array('enable' => false, 'length' => 200, 'marker' => '...', 'link' => false), 'separator' => ''),
        );
    }
    
    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array())
    {
        return array(
            'trim' => array(
                '#class' => 'sabai-form-group',
                'enable' => array(
                    '#type' => 'checkbox',
                    '#title' => __('Trim text', 'sabai'),
                    '#default_value' => !empty($settings['trim']['enable']),
                ),
                'length' => array(
                    '#field_prefix' => __('Maximum number of characters:', 'sabai'),
                    '#type' => 'number',
                    '#integer' => true,
                    '#min_value' => 1,
                    '#default_value' => $settings['trim']['length'],
                    '#size' => 5,
                    '#states' => array(
                        'visible' => array(
                            sprintf('input[name="%s[trim][enable][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('type' => 'checked', 'value' => true),
                        ),
                    ),
                ), 
                'marker' => array(
                    '#field_prefix' => __('Suffix text:', 'sabai'),
                    '#type' => 'textfield',
                    '#default_value' => $settings['trim']['marker'],
                    '#size' => 10,
                    '#states' => array(
                        'visible' => array(
                            sprintf('input[name="%s[trim][enable][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('type' => 'checked', 'value' => true),
                        ),
                    ),
                ),
                'link' => array(
                    '#type' => 'checkbox',
                    '#title' => __('Link to post', 'sabai'),
                    '#default_value' => !empty($settings['trim']['link']),
                    '#states' => array(
                        'visible' => array(
                            sprintf('input[name="%s[trim][enable][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('type' => 'checked', 'value' => true),
                        ),
                    ),
                ),
            ),
        );
    }

    public function fieldRendererRenderField(Sabai_Addon_Field_IField $field, array $settings, array $values, Sabai_Addon_Entity_IEntity $entity)
    {
        $ret = array();
        foreach ($values as $value) {
            if (!strlen($value['value'])) continue;
            
            if (!isset($value['html'])) {
                $ret[] = '<p>' . Sabai::h($value['value']) . '</p>';
            } else {
                if (empty($settings['trim']['enable'])) {
                    $ret[] = $value['html'];
                } else {
                    if (!empty($settings['trim']['link'])) {
                        $link = $this->_addon->getApplication()->Entity_Permalink($entity, array('title' => $settings['trim']['marker'], 'class' => 'sabai-trim-marker'));
                        $ret[] = $this->_addon->getApplication()->Summarize($value['html'], $settings['trim']['length'] - mb_strlen($settings['trim']['marker']), '') . $link;
                    } else {
                        $ret[] = $this->_addon->getApplication()->Summarize($value['html'], $settings['trim']['length'], $settings['trim']['marker']);
                    }
                }
            }
        }
        return implode($settings['separator'], $ret);
    }
}