<?php
class Sabai_Addon_Taxonomy_FieldWidget implements Sabai_Addon_Field_IWidget
{
    private $_addon, $_name, $_info;

    public function __construct(Sabai_Addon_Taxonomy $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldWidgetGetInfo($key = null)
    {
        if (!isset($this->_info[$this->_name])) {
            switch ($this->_name) {
                case 'taxonomy_tagging':
                    $this->_info[$this->_name] = array(
                        'label' => __('Text input field', 'sabai'),
                        'field_types' => array('taxonomy_terms'),
                        'accept_multiple' => true,
                        'default_settings' => array(
                            'enhanced_ui' => true,
                            'tagging' => true,
                            'separator' => ','
                        ),
                    );
                    break;
                case 'taxonomy_term_parent':
                    $this->_info[$this->_name] = array(
                        'label' => __('Select list', 'sabai'),
                        'field_types' => array('taxonomy_term_parent'),
                        'accept_multiple' => false,
                        'default_settings' => array(
                        ),
                    );
                    break;
            }
        }

        return isset($key) ? @$this->_info[$this->_name][$key] : $this->_info[$this->_name];
    }

    public function fieldWidgetGetSettingsForm($fieldType, array $settings, array $parents = array())
    {
        switch ($this->_name) {
            case 'taxonomy_tagging':
                return array(
                    'enhanced_ui' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Enable enhanced user interface', 'sabai'),
                        '#description' => sprintf(__('Check this to apply the jQuery %s plugin to this field to enable ajax auto-suggestion and more user friendly input method.', 'sabai'), '<a href="http://ivaynberg.github.com/select2/">Select2</a>'),
                        '#default_value' => $settings['enhanced_ui'],
                    ),
                    'tagging' => array(
                        '#type' => 'checkbox',
                        '#title' => __('Allow adding new items', 'sabai'),
                        '#description' => __('Check this to allow the user to add new items that do not currently exist in the list. This requires the "Add XXX" permission granted to the user, where XXX is the type of item such as Categories, Tags, and etc.', 'sabai'),
                        '#default_value' => $settings['tagging'],
                    ),
                    'separator' => array(
                        '#type' => 'textfield',
                        '#title' => __('Separator', 'sabai'),
                        '#description' => __('Enter a text string used to separate multiple terms.', 'sabai'),
                        '#default_value' => $settings['separator'],
                        '#states' => array(
                            'visible' => array(
                                sprintf('input[name="%s[enhanced_ui][]"]', $this->_addon->getApplication()->Form_FieldName($parents)) => array(
                                    'type' => 'checked',
                                    'value' => false,
                                ),
                            ),
                        ),
                        '#required' => array(array($this, 'isSeparatorRequired'), array($parents)),
                        '#size' => 5,
                    ),
                );
        }
    }
    
    public function isSeparatorRequired($form, $parents)
    {
        $values = $form->getValue($parents);
        return $values['enhanced_ui'] === false;
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        switch ($this->_name) {
            case 'taxonomy_tagging':
                if (!$bundle = $this->_getFieldBundle($field)) {
                    return array();
                }
                if ($settings['enhanced_ui']) {
                    return array(
                        '#type' => 'autocomplete',
                        '#default_value' => $this->_getDefaultValue($value),
                        '#ajax_url' => $this->_addon->getApplication()->Url(($admin ? $bundle->getAdminPath() : $bundle->getPath()) . '/_autocomplete', array(Sabai_Request::PARAM_CONTENT_TYPE => 'json'), '', '&'),
                        '#default_items_callback' => array($this, 'getAutocompleteDefaultItems'),
                        '#multiple' => $field->getFieldMaxNumItems() != 1,
                        '#max_selection' => $field->getFieldMaxNumItems(),
                        '#noscript' => array('#type' => 'textfield'),
                        '#tagging' => $settings['tagging'] && $this->_addon->getApplication()->HasPermission($bundle->name . '_add'),
                        '#element_validate' => array(array(array($this, 'validateTerms'), array($bundle, $settings['tagging']))),
                        '#bundle' => $bundle,
                    );
                }
                return array(
                    '#type' => 'textfield',
                    '#element_validate' => array(array(array($this, 'validateTerms'), array($bundle, $settings['tagging']))),
                    '#separator' => $settings['separator'],
                    '#max_selection' => $field->getFieldMaxNumItems(),
                    '#default_value' => $this->_getDefaultValue($value),
                    '#bundle' => $bundle,
                );
            case 'taxonomy_term_parent':
                if (empty($field->Bundle->info['taxonomy_hierarchical'])) {
                    return array(
                        '#type' => 'hidden',
                        '#value' => 0,
                    );
                }
                $application = $this->_addon->getApplication();
                $application->LoadJs('select2.min.js', 'select2', 'jquery');
                $application->LoadCss('select2.min.css', 'select2');
                $application->LoadJs('sabai-taxonomy.min.js', 'sabai-taxonomy', array('sabai', 'select2'));
                return array(
                    '#type' => 'select',
                    '#default_value' => $value,
                    '#multiple' => false,
                    '#options' => $this->_addon->getApplication()->Taxonomy_Tree($field->Bundle, array('prefix' => '--'), array('' => '')),
                    '#title' => sprintf(__('Parent %s', 'sabai'), $this->_addon->getApplication()->Entity_BundleLabel($field->Bundle, true)),
                    '#attributes' => array('data-placeholder' => sprintf(__('Select %s', 'sabai'), $this->_addon->getApplication()->Entity_BundleLabel($field->Bundle, true))),
                    '#class' => 'sabai-taxonomy-select2',
                );
        }
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        switch ($this->_name) {
            case 'taxonomy_tagging':
                return '<input type="text" disabled="disabled" style="width:100%;" />';
            case 'taxonomy_term_parent':
                if (empty($field->Bundle->info['taxonomy_hierarchical'])) {
                    return false;
                }
                return sprintf(
                    '<select disabled="disabled"><option>%s</option></select>',
                    sprintf(__('Select %s', 'sabai'), Sabai::h($this->_addon->getApplication()->Entity_BundleLabel($field->Bundle, true)))
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

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $settings, array $parents = array())
    {

    }
        
    public function getAutocompleteDefaultItems($defaultValue, &$defaultItems, &$noscriptOptions)
    {
        $entity_type_impl = $this->_addon->getApplication()->Entity_TypeImpl('taxonomy');
        foreach ($entity_type_impl->entityTypeGetEntitiesByIds(array_keys($defaultValue)) as $entity) {
            $id = $entity->getSlug();
            $title = $entity->getTitle();
            $defaultItems[] = array('id' => $id, 'text' => Sabai::h($title));
            $noscriptOptions[$id] = $title;
        }
    }
    
    private function _getDefaultValue($value)
    {
        if (isset($value)) {
            $default_value = array();
            foreach ($value as $entity) {
                if (!is_object($entity)) continue;

                $default_value[$entity->getId()] = $entity->getSlug();
            }
        } else {
            $default_value = null;
        }
        return $default_value;
    }

    public function validateTerms($form, &$value, $element, $bundle, $tagging)
    {
        if (empty($value)) return;

        $term_names = array();
        foreach ((array)$value as $term_name) {
            $term_slug = $this->_addon->getApplication()->Slugify($term_name);
            $term_names[$term_slug] = $term_name;
        }
        $model = $this->_addon->getModel();
        $value = array();
        foreach ($model->Term->entityBundleName_is($bundle->name)->name_in(array_keys($term_names))->fetch() as $term) {
            $value[$term->id] = $term->name;
        }
        $new_term_names = array_diff_key($term_names, array_flip($value));
        
        if (empty($new_term_names)) return; // no new terms
        
        // Check permission to create new tags
        if (!$tagging || !$this->_addon->getApplication()->HasPermission($bundle->name . '_add')) {
            $form->setError(sprintf(
                __('The following %s do not exist: %s', 'sabai'),
                strtolower($this->_addon->getApplication()->Entity_BundleLabel($bundle, false)),
                implode(', ', $new_term_names)
            ), $element);
            return;
        }

        $user_id = $this->_addon->getApplication()->getUser()->id;
        foreach ($new_term_names as $new_term_name => $new_term_title) {
            $term = $model->create('Term');
            $term->markNew();
            $term->entity_bundle_name = $bundle->name;
            $term->entity_bundle_type = $bundle->type;
            $term->title = $new_term_title;
            $term->name = $new_term_name;
            $term->user_id = $user_id;
            $model->commit();
            $value[$term->id] = $term->name;
        }
    }
}