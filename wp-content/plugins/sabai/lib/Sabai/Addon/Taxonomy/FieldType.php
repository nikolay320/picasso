<?php
class Sabai_Addon_Taxonomy_FieldType implements Sabai_Addon_Field_IType
{
    private $_addon, $_name;

    public function __construct(Sabai_Addon_Taxonomy $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldTypeGetInfo($key = null)
    {
        $info = array('creatable' => false);
        switch ($this->_name) {
            case 'taxonomy_term_title':
                $info += array(
                    'label' => __('Title', 'sabai'),
                    'act_as_widget' => 'string',
                    'entity_types' => array('taxonomy'),
                );
                break;
            case 'taxonomy_term_id':
                $info += array(
                    'label' => 'Taxonomy term ID',
                    'entity_types' => array('taxonomy'),
                );
                break;
            case 'taxonomy_term_name':
                $info += array(
                    'label' => 'Taxonomy term slug',
                    'entity_types' => array('taxonomy'),
                );
                break;
            case 'taxonomy_term_created':
                $info += array(
                    'label' => 'Taxonomy term created date',
                    'entity_types' => array('taxonomy'),
                );
                break;
            case 'taxonomy_term_user_id':
                $info += array(
                    'label' => 'Taxonomy term author',
                    'entity_types' => array('taxonomy'),
                );
                break;
            case 'taxonomy_term_entity_bundle_name':
                $info += array(
                    'label' => 'Taxonomy type',
                    'entity_types' => array('taxonomy'),
                );
                break;
            case 'taxonomy_term_entity_bundle_type':
                $info += array(
                    'label' => 'Taxonomy type',
                    'entity_types' => array('taxonomy'),
                );
                break;
            case 'taxonomy_terms':
                $info += array(
                    'label' => __('Taxonomy Terms', 'sabai'),
                    'entity_types' => array('content'),
                    'default_renderer' => 'taxonomy_terms',
                );
                break;
            case 'taxonomy_content':
                $info += array(
                    'label' => 'Taxonomy content',
                    'entity_types' => array('taxonomy'),
                );
                break;
            case 'taxonomy_content_count':
                $info += array(
                    'label' => 'Taxonomy content count',
                    'entity_types' => array('taxonomy'),
                );
                break;
            case 'taxonomy_term_parent':
                $info += array(
                    'label' => __('Parent Term', 'sabai'),
                    'entity_types' => array('taxonomy'),
                    'default_widget' => 'taxonomy_term_parent',
                );
                break;
        }

        return isset($key) ? @$info[$key] : $info;
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {

    }
    
    public function fieldTypeGetSchema(array $settings)
    {
        switch ($this->_name) {
            case 'taxonomy_terms':
                return array(
                    'columns' => array(
                        'value' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => true,
                            'was' => 'value',
                            'default' => 0,
                        ),
                    ),
                );
            case 'taxonomy_content_count':
                return array(
                    'columns' => array(
                        'value' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => true,
                            'was' => 'value',
                            'default' => 0,
                        ),
                        'content_bundle_name' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 40,
                            'was' => 'content_bundle_name',
                            'default' => '',
                        ),
                    ),
                    'indexes' => array(
                        'value' => array(
                            'fields' => array('value' => array('sorting' => 'ascending')),
                            'was' => 'value',
                        ),
                    ),
                );
        }
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        switch ($this->_name) {
            case 'taxonomy_terms':
                $ret = $term_ids = array();
                foreach ($values as $weight => $value) {
                    if (is_array($value)) {  // tagging
                        foreach (array_keys($value) as $term_id) {
                            if (empty($term_id)) {
                                continue;
                            }
                            $term_ids[$term_id] = $term_id;
                            
                        }
                    } elseif (!empty($value)) {
                        $term_ids[$value] = $value;
                    }
                }
                foreach ($term_ids as $term_id) {
                    $ret[]['value'] = $term_id;
                }
                return $ret;
            case 'taxonomy_content_count':
                $ret = array();
                foreach ($values as $value) {
                    if (!is_array($value)) {
                        $ret[] = false; // delete
                    } else {
                        $ret[] = $value;
                    }
                }
                return $ret;
            default:
                return array();
        }
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values, Sabai_Addon_Entity_IEntity $entity)
    {
        switch ($this->_name) {
            case 'taxonomy_terms':
                $entities = array();
                foreach ($values as $key => $value) {
                    $entities[$value['value']] = $key;
                }
                $values = array();
                foreach ($this->_addon->getModel('Term')->fetchByIds(array_keys($entities)) as $term) {
                    $key = $entities[$term->id];
                    $values[$key] = $term->toEntity();
                }
                ksort($values); // re-order as it was saved
                break;
            case 'taxonomy_content_count':
                foreach ($values as $value) {
                    // Index by child bundle name for easier access to counts
                    $values[0][$value['content_bundle_name']] = (int)$value['value'];
                    unset($values[0]['value'], $values[0]['content_bundle_name']);
                }
                break;
            default:
                break;
        }
    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {
        switch ($this->_name) {
            case 'taxonomy_terms':
                $current = $new = array();
                if (!empty($currentLoadedValue)) {
                    foreach ($currentLoadedValue as $value) {
                        $current[] = $value->getId();
                    }
                }
                foreach ($valueToSave as $value) {
                    $new[] = (int)$value['value'];
                }
                return $current !== $new;
            case 'taxonomy_content_count':
                $current = $new = array();
                if (!empty($currentLoadedValue[0])) {
                    foreach ($currentLoadedValue[0] as $content_bundle_name => $value) {
                        $current[] = array('value' => $value, 'content_bundle_name' => $content_bundle_name);
                    }
                }
                foreach ($valueToSave as $value) {
                    $new[] = array('value' => (int)$value['value'], 'content_bundle_name' => $value['content_bundle_name']);
                }
                return $current !== $new;
        }
    }
}