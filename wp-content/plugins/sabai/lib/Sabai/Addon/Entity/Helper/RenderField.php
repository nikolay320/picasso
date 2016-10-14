<?php
class Sabai_Addon_Entity_Helper_RenderField extends Sabai_Helper
{
    /**
     * Renders an entity field
     * @param Sabai $application
     * @param Sabai_Addon_Entity_IEntity $entity
     * @param string|Sabai_Addon_Entity_Model_Field $field
     * @param string|array $view
     * @param int $index
     * @param string $renderer
     */
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $field, $view = 'default', $index = null, $renderer = null)
    {
        if (!$field instanceof Sabai_Addon_Entity_Model_Field) {
            if (!$field
                || (!$field = $application->Entity_Field($entity, $field))
            ) {
                return '';
            }
        }
        if (!$values = $entity->getFieldValue($field->getFieldName())) {
            return '';
        }
        if (!is_array($view)) {
            if ($view === 'default') {
                if (false === $_view = $field->getFieldView($view)) {
                    return '';
                }
                if (null === $_view) $_view = 'default'; // always display if not explicaitly disabled
            } elseif ($view === 'summary' || $view === 'grid') {
                if (false === $_view = $field->getFieldView($view)) {
                    return '';
                }
                if (null === $_view) {
                    if ($field->isCustomField()) {
                        return '';
                    }
                    $_view = 'default';
                }
            } else {
                if (!$_view = $field->getFieldView($view)) {
                    return '';
                }
            }
            if (!$renderer = $field->getFieldRenderer($_view)) {
                $renderer = $field->getFieldType(); // for backward compat with 1.2
            }
            $settings = $field->getFieldRendererSettings($_view, $renderer);
        } else {
            if (!isset($renderer)) {
                $renderer = $field->getFieldType();
            }
            $settings = $view;
        }
        if (!$renderer_impl = $application->Field_RendererImpl($renderer, true)) {
            return '';
        }
        // Add renderer default settings
        $settings += (array)$renderer_impl->fieldRendererGetInfo('default_settings');
        
        // Get values
        if (isset($index)) {
            if (is_array($index)) {
                $values = array_intersect_key($values, array_flip($index));
            } else {
                $values = array($index => $values[$index]);
            }
        }

        return $application->Filter(
            'entity_render_field',
            $renderer_impl->fieldRendererRenderField($field, $settings, $values, $entity), array($entity, $field, $values, $renderer, $settings)
        );
    }
}