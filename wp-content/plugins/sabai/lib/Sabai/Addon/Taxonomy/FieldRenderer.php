<?php
class Sabai_Addon_Taxonomy_FieldRenderer extends Sabai_Addon_Field_Renderer_AbstractRenderer
{    
    protected function _fieldRendererGetInfo()
    {
        switch ($this->_name) {
            case 'taxonomy_terms':
                return array(
                    'field_types' => array($this->_name),
                    'default_settings' => array(
                        'icon' => 'folder-open',
                        'limit' => 0,
                        'separator' => '&nbsp;&nbsp;',
                    )
                );
        }
    }
    
    public function fieldRendererGetSettingsForm($fieldType, array $settings, $view, array $parents = array())
    {
        switch ($this->_name) {
            case 'taxonomy_terms':
                return array(
                    'icon' => array(
                        '#title' => __('Icon', 'sabai'),
                        '#type' => 'icon',
                        '#size' => 15,
                        '#default_value' => $settings['icon'],
                    ),
                    'limit' => array(
                        '#type' => 'number',
                        '#integer' => true,
                        '#min_value' => 0,
                        '#title' => __('Maximum number of items to display (0 or unlimited)', 'sabai'),
                        '#default_value' => $settings['limit'], 
                        '#size' => 3,
                        '#states' => array(
                            'invisible' => array(
                                'select[name="max_num_items"]' => array('value' => 1),
                            ),
                        ),
                    ),
                    'separator' => array(
                        '#type' => 'textfield',
                        '#title' => __('Separator', 'sabai'),
                        '#default_value' => $settings['separator'],  
                        '#size' => 8,
                    ),
                );
        }
    }

    public function fieldRendererRenderField(Sabai_Addon_Field_IField $field, array $settings, array $values, Sabai_Addon_Entity_IEntity $entity)
    {
        switch ($this->_name) {
            case 'taxonomy_terms':
                $ret = array();
                $application = $this->_addon->getApplication();
                $options = array('no_escape' => true);
                if (!empty($settings['limit']) && $settings['limit'] < count($values)) {
                    $values = array_slice($values, 0, $settings['limit']);
                }
                $format = $settings['icon'] ? '<i class="fa fa-' . $settings['icon'] . '"></i> %s' : '%s';
                foreach (array_keys($values) as $i) {
                    $options['title'] = sprintf($format, Sabai::h($values[$i]->getTitle()));
                    $ret[] = $application->Entity_Permalink($values[$i], $options);
                }
                return implode($settings['separator'], $ret);
        }
    }
}