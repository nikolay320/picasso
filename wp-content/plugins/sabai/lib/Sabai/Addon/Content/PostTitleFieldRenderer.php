<?php
class Sabai_Addon_Content_PostTitleFieldRenderer extends Sabai_Addon_Field_Renderer_AbstractRenderer
{    
    protected function _fieldRendererGetInfo()
    {
        return array(
            'field_types' => array($this->_name),
            'default_settings' => array('link' => true, 'feature' => false),
        );
    }
    
    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array())
    {
        return array(
            'link' => array(
                '#type' => 'checkbox',
                '#title' => __('Link to post', 'sabai'),
                '#default_value' => $settings['link'],
            ),
            'feature' => array(
                '#type' => 'checkbox',
                '#title' => __('Add featured post icon', 'sabai'),
                '#default_value' => $settings['feature'],
            ),
        );
    }

    public function fieldRendererRenderField(Sabai_Addon_Field_IField $field, array $settings, array $values, Sabai_Addon_Entity_IEntity $entity)
    {        
        $title = $values[0];
        return $this->_addon->getApplication()->Entity_RenderTitle($entity, array('alt' => $title, 'no_link' => empty($settings['link']), 'no_feature' => empty($settings['feature'])));
    }
}