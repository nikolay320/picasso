<?php
class Sabai_Addon_WordPress_TextFieldRenderer extends Sabai_Addon_Field_Renderer_Text
{    
    protected function _fieldRendererGetInfo()
    {
        $ret = parent::_fieldRendererGetInfo();
        $ret['default_settings']['shortcode'] = false;
        $ret['default_settings']['trim']['marker'] = apply_filters('excerpt_more', ' ' . '[&hellip;]');
        return $ret;
    }
    
    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array())
    {        
        $shortcode_roles = array_intersect_key(
            $this->_addon->getApplication()->getPlatform()->getUserRoles(),
            $this->_addon->getApplication()->AdministratorRoles() + array_flip((array)$this->_addon->getConfig('shortcode_roles'))
        );
        return parent::fieldRendererGetSettingsForm($fieldType, $settings, $view, $parents) + array(
            'shortcode' => array(
                '#type' => 'checkbox',
                '#title' => __('Process shortcode(s)', 'sabai'),
                '#default_value' => $settings['shortcode'],
                '#description' => __(sprintf(
                    __('User roles allowed to use shortcodes: %s', 'sabai'),
                    implode(', ', $shortcode_roles)
                )),
                '#states' => array(
                    'visible' => array(
                        sprintf('input[name="%s[trim][enable][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array('type' => 'checked', 'value' => false),
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

            $content = isset($value['html']) ? $value['html'] : $value['value'];
            if (empty($settings['trim']['enable'])) {
                if ($settings['shortcode']) {
                    $content = $this->_doShortcode($content, $entity);
                }
                $ret[] = $content;
            } else {
                $content = strip_shortcodes($content);                
                if (!empty($settings['trim']['link'])) {
                    $link = $this->_addon->getApplication()->Entity_Permalink($entity, array('title' => $settings['trim']['marker'], 'class' => 'sabai-trim-marker'));
                    $ret[] = $this->_addon->getApplication()->Summarize($content, $settings['trim']['length'] - mb_strlen($settings['trim']['marker']), '') . $link;
                } else {
                    $ret[] = $this->_addon->getApplication()->Summarize($content, $settings['trim']['length'], $settings['trim']['marker']);
                }             
            }
        }
        return implode($settings['separator'], $ret);
    }
    
    protected function _doShortcode($text, Sabai_Addon_Entity_IEntity $entity)
    {        
        $author = null;
        $application = $this->_addon->getApplication();
        if (isset($this->_bundle->info['author_helper'])) {
            $author = $application->{$this->_bundle->info['author_helper']}($entity);
        }
        if (!$entity->getAuthorId()) {
            return strip_shortcodes($text);
        }
        if (!$author) {
            $author = $application->Entity_Author($entity);
        }
        return $author->isAnonymous() ? strip_shortcodes($text) : $application->WordPress_DoShortcode($text, $author);
    }
}
