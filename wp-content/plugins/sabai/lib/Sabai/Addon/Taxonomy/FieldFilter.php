<?php
class Sabai_Addon_Taxonomy_FieldFilter extends Sabai_Addon_Field_Filter_AbstractFilter
{
    protected function _fieldFilterGetInfo()
    {
        switch ($this->_name) {
            case 'taxonomy_select':
                return array(
                    'label' => __('Select list', 'sabai'),
                    'field_types' => array('taxonomy_terms'),
                    'default_settings' => array(
                        'num' => 10,
                        'depth' => 0,
                        'content_bundle' => null,
                        'default_text' => null,
                    ),
                );
        }
    }

    public function fieldFilterGetSettingsForm(Sabai_Addon_Field_IField $field, array $settings, array $parents = array())
    {
        if (!$bundle = $this->_getFieldBundle($field)) {
            return;
        }
        $ret = array(
            'default_text' => array(
                '#type' => 'textfield',
                '#title'=> __('Default text', 'sabai'),
                '#default_value' => $this->_getDefaultText($bundle, $settings),
                '#weight' => 2,
                '#placeholder' => sprintf(__('Select %s', 'sabai'), $this->_addon->getApplication()->Entity_BundleLabel($bundle, true)),
            ),
        );
        if (empty($bundle->info['taxonomy_hierarchical'])) {
            return $ret + array(
                'num' => array(
                    '#type' => 'number',
                    '#title' => __('Number of term options', 'sabai'),
                    '#default_value' => $settings['num'],
                    '#size' => 3,
                    '#integer' => true,
                    '#weight' => 1,
                ),
            ); 
        } else {
            return $ret + array(
                'depth' => array(
                    '#type' => 'number',
                    '#title' => __('Depth of term hierarchy tree', 'sabai'),
                    '#default_value' => $settings['depth'],
                    '#size' => 3,
                    '#integer' => true,
                    '#weight' => 1,
                ),
            );
        }
    }
    
    protected function _getDefaultText($bundle, array $settings)
    {
        return strlen((string)$settings['default_text'])
            ? $settings['default_text']
            : sprintf(__('Select %s', 'sabai'), $this->_addon->getApplication()->Entity_BundleLabel($bundle, true));
    }

    public function fieldFilterGetForm(Sabai_Addon_Field_IField $field, $filterName, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $request = null, array $requests = null, $isSubmitOnChanage = true, array $parents = array())
    {
        switch ($this->_name) {
            case 'taxonomy_select':
                if (!$taxonomy_bundle = $this->_getFieldBundle($field)) {
                    return array();
                }
                $default_text = $this->_getDefaultText($taxonomy_bundle, $settings);
                $ret = array(
                    '#type' => 'select',
                    '#empty_value' => '',
                    '#multiple' => false,
                    '#bundle' => $taxonomy_bundle,
                );
                if (empty($taxonomy_bundle->info['taxonomy_hierarchical'])) {
                    $ret['#options'] = array('' => $default_text);
                    $ret['#options'] += $this->_addon->getModel('Term')->entityBundleName_is($taxonomy_bundle->name)->fetch($settings['num'], 0, 'updated', 'DESC')->getArray('title');
                    return $ret;
                }
                
                // Hierarchical taxonomy
                $ret['#options'] = $this->_getTermList($bundle, $taxonomy_bundle, $default_text);                
                $ret = array(
                    0 => array('#weight' => 0, '#class' => 'sabai-taxonomy-term-0') + $ret,
                    '#element_validate' => array(array($this, 'validateTaxonomySelect')),
                    '#class' => 'sabai-form-inline',
                    '#collapsible' => false,
                );
                if (!$max_depth = $this->_addon->getApplication()->getModel(null, 'Taxonomy')->getGateway('Term')->getMaxDepth($taxonomy_bundle->name)) {
                    return $ret;
                }
                if ($settings['depth'] && $settings['depth'] < $max_depth) {
                    $max_depth = $settings['depth'];
                }
                $default_values = array();
                if (!empty($request)
                    && (null !== $value = $this->_getSelectedValue($request))
                ) {
                    if (!isset($ret[0]['#options'][$value])) {
                        foreach ($this->_addon->getApplication()->getModel('Term', 'Taxonomy')->fetchParents($value) as $parent) {
                            $default_values[] = $parent->id;
                        }
                    }
                    $default_values[] = $value;
                    $ret[0]['#default_value'] = $default_values[0];
                }
                
                // Use load_options state?
                if ($isSubmitOnChanage) {
                    if (!empty($default_values)) {
                        $max_depth = min($max_depth, count($default_values));
                        for ($i = 1; $i <= $max_depth; $i++) {
                            $options = $this->_getTermList($bundle, $taxonomy_bundle, $default_text, $default_values[$i - 1]);
                            if (count($options) <= 1) break;
                            
                            $ret[$i] = array(
                                '#type' => 'select',
                                '#options' => $options,
                                '#weight' => $i,
                                '#default_value' => @$default_values[$i],
                            );
                        }
                    }
                    return $ret;
                }

                $url = $this->_addon->getApplication()->MainUrl('/sabai/taxonomy/child_terms', array('bundle' => $taxonomy_bundle->name, 'count' => 1, Sabai_Request::PARAM_CONTENT_TYPE => 'json'), '', '&');
                for ($i = 1; $i <= $max_depth; $i++) {
                    $ret[$i] = array(
                        '#type' => 'select',
                        '#class' => 'sabai-hidden sabai-taxonomy-term-' . $i,
                        '#attributes' => array('data-load-url' => $url),
                        '#states' => array(
                            'load_options' => array(
                                sprintf('.sabai-taxonomy-term-%d select', $i - 1) => array('type' => 'selected', 'value' => true, 'container' => '.sabai-form-fields'),
                            ),
                        ),
                        '#options' => array('' => $default_text),
                        '#states_selector' => '.sabai-taxonomy-term-' . $i,
                        '#skip_validate_option' => true,
                        '#weight' => $i,
                        '#default_value' => @$default_values[$i],
                    );
                }
                return $ret;
        }
    }
    
    protected function _getTermList($bundle, $taxonomyBundle, $defaulText = '', $parent = 0)
    {
        $ret = array('' => $defaulText);
        $terms = $this->_addon->getApplication()->Taxonomy_Terms($taxonomyBundle);
        if (!empty($terms[$parent])) {
            foreach ($terms[$parent] as $term) {
                $ret[$term['id']] = sprintf(
                    __('%s (%d)', 'sabai'),
                    $term['title'],
                    isset($term['count'][$bundle->type]) ? $term['count'][$bundle->type] : 0
                ); 
            }
        }
        return $ret;
    }
    
    public function validateTaxonomySelect(Sabai_Addon_Form_Form $form, &$value, $element)
    {
        $value = $this->_getSelectedValue($value);
    }
    
    protected function _getSelectedValue(array $values)
    {
        // make sure terms are of the same branch
        $parent = array_shift($values);
        if (null === $parent || !strlen($parent)) {
            return;
        }        
        $model = $this->_addon->getApplication()->getModel('Term', 'Taxonomy');
        while (null !== $value = array_shift($values)) {
            if (!strlen($value)
                || (!$term = $model->fetchById($value))
                || $term->parent != $parent
            ) {
                break;
            }
            $parent = $value;
        }
        return $parent;
    }
    
    public function fieldFilterIsFilterable(Sabai_Addon_Field_IField $field, $filterName, array $settings, &$value, array $requests = null)
    {
        switch ($this->_name) {
            case 'taxonomy_select':
                return !empty($value);
        }
    }
    
    public function fieldFilterDoFilter(Sabai_Addon_Field_IQuery $query, Sabai_Addon_Field_IField $field, $filterName, array $settings, $value)
    {
        switch ($this->_name) {
            case 'taxonomy_select':                
                $term_ids = (array)$value;
                foreach ($this->_addon->getApplication()->Taxonomy_Descendants($value, false) as $_term) {
                    $term_ids[] = $_term->id;
                }
                $query->fieldIsIn($field->getFieldName(), $term_ids);
        }
    }
    
    public function fieldFilterGetPreview(Sabai_Addon_Field_IField $field, $filterName, array $settings)
    {
        switch ($this->_name) {
            case 'taxonomy_select':
                if (!$bundle = $this->_getFieldBundle($field)) {
                    return '';
                }
                return sprintf(
                    '<select disabled="disabled"><option>%s</option></select>',
                    sprintf(__('Select %s', 'sabai'), Sabai::h($this->_addon->getApplication()->Entity_BundleLabel($bundle, true)))
                );
        }
    }
    
    private function _getFieldBundle($field)
    {
        return $this->_addon->getApplication()->getModel('Bundle', 'Entity')
            ->entitytypeName_is('taxonomy')
            ->id_is($field->getFieldData('bundle_id'))
            ->fetchOne();
    }
}