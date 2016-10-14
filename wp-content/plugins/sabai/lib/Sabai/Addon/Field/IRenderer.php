<?php
interface Sabai_Addon_Field_IRenderer
{
    public function fieldRendererGetInfo($key = null);
    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array());
    public function fieldRendererRenderField(Sabai_Addon_Field_IField $field, array $settings, array $values, Sabai_Addon_Entity_IEntity $entity);
}