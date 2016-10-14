<?php
abstract class Sabai_Addon_Entity_ParentFieldWidget extends Sabai_Addon_Field_Widget_AbstractWidget
{
    protected $_entityType, $_fieldTypes;

    public function __construct(Sabai_Addon $addon, $name, $entityType, $fieldTypes)
    {
        parent::__construct($addon, $name);
        $this->_entityType = $entityType;
        $this->_fieldTypes = (array)$fieldTypes;
    }

    protected function _fieldWidgetGetInfo()
    {
        return array(
            'label' => __('Autocomplete text field', 'sabai'),
            'field_types' => $this->_fieldTypes,
            'accept_multiple' => true,
            'admin_only' => true,
        );
    }

    public function fieldWidgetGetForm(Sabai_Addon_Field_IField $field, array $settings, Sabai_Addon_Entity_Model_Bundle $bundle, $value = null, Sabai_Addon_Entity_IEntity $entity = null, array $parents = array(), $admin = false)
    {
        if (!$bundle = $this->_getParentBundle($field)) {
            return array();
        }
        return array(
            '#type' => 'autocomplete',
            '#default_value' => $this->_getDefaultValue($value),
            '#ajax_url' => $this->_addon->getApplication()->Url(($admin ? $bundle->getAdminPath() : $bundle->getPath()) . '/_autocomplete', array(Sabai_Request::PARAM_CONTENT_TYPE => 'json'), '', '&'),
            '#default_items_callback' => array($this, 'getAutocompleteDefaultItems'),
            '#multiple' => $field->getFieldMaxNumItems() != 1,
            '#max_selection' => $field->getFieldMaxNumItems(),
            '#noscript' => array('#type' => 'select'),
        );
    }
    
    public function fieldWidgetGetPreview(Sabai_Addon_Field_IField $field, array $settings)
    {
        return '<input type="text" disabled="disabled" style="width:100%;" />';
    }

    public function fieldWidgetGetEditDefaultValueForm($fieldType, array $settings, array $parents = array())
    {

    }
    
    public function getAutocompleteDefaultItems($defaultValue, &$defaultItems, &$noscriptOptions)
    {
        foreach ($this->_addon->getApplication()->Entity_TypeImpl($this->_entityType)->entityTypeGetEntitiesByIds($defaultValue) as $entity) {
            $id = $entity->getId();
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
                $default_value[] = is_object($entity) ? $entity->getId() : $entity;
            }
        } else {
            $default_value = null;
        }
        return $default_value;
    }
    
    private function _getParentBundle($field)
    {        
        if (empty($field->Bundle->info['parent'])
            || (!$parent_bundle = $field->Bundle->info['parent'])
        ) {
            return false;
        }
        return $this->_addon->getApplication()->Entity_Bundle($parent_bundle);
    }
}