<?php
class Sabai_Addon_Field_Renderer_Video extends Sabai_Addon_Field_Renderer_AbstractRenderer
{
    protected function _fieldRendererGetInfo()
    {
        return array(
            'field_types' => array($this->_name),
            'default_settings' => array(
                'columns' => 1,
            ),
            'separatable' => false,
        );
    }

    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array())
    {
        return array(
            'columns' => array(
                '#title' => __('Number of columns', 'sabai'),
                '#type' => 'radios',
                '#class' => 'sabai-form-inline',
                '#options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4),
                '#default_value' => $settings['columns'],
            ),
        );
    }

    public function fieldRendererRenderField(Sabai_Addon_Field_IField $field, array $settings, array $values, Sabai_Addon_Entity_IEntity $entity)
    {
        $ret = array('<div class="sabai-row">');
        $width = 12 / $settings['columns'];
        foreach ($values as $value) {
            $ret[] = '<div class="sabai-col-md-' . $width . '"><div class="sabai-field-video">';
            switch ($value['provider']) {
                case 'vimeo':
                    $ret[] = $this->_renderVimeoVideo($field, $settings, $value);
                    break;
                default:
                    $ret[] = $this->_renderYouTubeVideo($field, $settings, $value);
            }
            $ret[] = '</div></div>';
        }
        $ret[] = '</div>';
        return implode(PHP_EOL, $ret);
    }
    
    protected function _renderVimeoVideo(Sabai_Addon_Field_IField $field, array $settings, array $value)
    {
        return sprintf('
            <iframe src="//player.vimeo.com/video/%s" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>',
            $value['id']
        );
    }
    
    protected function _renderYoutubeVideo(Sabai_Addon_Field_IField $field, array $settings, array $value)
    {
        return sprintf('
            <iframe id="player" type="text/html" src="//www.youtube.com/embed/%s" frameborder="0"></iframe>',
            $value['id']
        );
    }
}