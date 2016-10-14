<?php
abstract class Sabai_Addon_Field_Renderer_Carousel extends Sabai_Addon_Field_Renderer_AbstractRenderer
{
    protected $_fieldTypes = array(), $_defaultSettings;
    protected static $_count = 0;
    
    protected function _fieldRendererGetInfo()
    {
        return array(
            'label' => __('Carousel', 'sabai'),
            'field_types' => $this->_fieldTypes,
            'default_settings' => $this->_defaultSettings + array(
                'mode' => 'horizontal',
                'auto' => false,
                'controls' => true,
                'pager' => true,
                'pause' => 4000,
                'captions' => true,
            ),
            'separatable' => false,
        );
    }

    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array())
    {
        return $this->_addon->getApplication()->CarouselOptions($settings, true, $parents);
    }

    public function fieldRendererRenderField(Sabai_Addon_Field_IField $field, array $settings, array $values, Sabai_Addon_Entity_IEntity $entity)
    {
        $id = 'sabai-field-carousel-' . self::$_count++;        
        return $this->_addon->getApplication()->Carousel($this->_getSlides($settings, $values, $entity, $id), $settings);
    }
    
    abstract protected function _getSlides(array $settings, array $values, Sabai_Addon_Entity_IEntity $entity, $id);
}