<?php
class Sabai_Addon_Content_FieldType implements Sabai_Addon_Field_IType, Sabai_Addon_Field_ISortable
{
    private $_addon, $_name;

    public function __construct(Sabai_Addon_Content $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldTypeGetInfo($key = null)
    {
        switch ($this->_name) {
            case 'content_post_title':
                $info = array(
                    'label' => __('Title', 'sabai'),
                    'default_widget' => $this->_name,
                    'default_renderer' => $this->_name,
                    'entity_types' => array('content'),
                    'creatable' => false,
                );
                break;
            case 'content_post_status':
                $info = array(
                    'label' => 'Status',
                    'entity_types' => array('content'),
                    'creatable' => false,
                );
                break;
            case 'content_post_published':
                $info = array(
                    'label' => 'Publish Date',
                    'entity_types' => array('content'),
                    'creatable' => false,
                );
                break;
            case 'content_post_id':
                $info = array(
                    'label' => 'Content ID',
                    'entity_types' => array('content'),
                    'creatable' => false
                );
                break;
            case 'content_post_views':
                $info = array(
                    'label' => 'View Count',
                    'entity_types' => array('content'),
                    'creatable' => false,
                );
                break;
            case 'content_post_user_id':
                $info = array(
                    'label' => 'Author',
                    'entity_types' => array('content'),
                    'creatable' => false,
                );
                break;
            case 'content_post_entity_bundle_name':
                $info = array(
                    'label' => 'Content Type',
                    'entity_types' => array('content'),
                    'creatable' => false,
                );
                break;
            case 'content_post_entity_bundle_type':
                $info = array(
                    'label' => 'Content Type',
                    'entity_types' => array('content'),
                    'creatable' => false,
                );
                break;
            case 'content_post_slug':
                $info = array(
                    'label' => 'Content Slug',
                    'entity_types' => array('content'),
                    'creatable' => false,
                );
                break;
            case 'content_children':
                $info = array(
                    'label' => 'Child Content',
                    'entity_types' => array('content'),
                    'creatable' => false,
                );
                break;
            case 'content_trashed':
                $info = array(
                    'label' => 'Trash Info',
                    'entity_types' => array('content'),
                    'creatable' => false,
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
            case 'content_trashed':
                return array(
                    'columns' => array(
                        'trashed_at' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => true,
                            'was' => 'trashed_at',
                            'default' => 0,
                        ),
                        'trashed_by' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => true,
                            'was' => 'trashed_by',
                            'default' => 0,
                        ),
                        'parent_entity_id' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => true,
                            'was' => 'parent_entity_id',
                            'default' => 0,
                        ),
                        'type' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'length' => 1,
                            'was' => 'type',
                            'default' => 0,
                        ),
                        'reason' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 255,
                            'was' => 'reason',
                            'default' => '',
                        ),
                        'prev_status' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 20,
                            'was' => 'prev_status',
                            'default' => '',
                        ),
                    ),
                    'indexes' => array(
                        'trashed_at' => array(
                            'fields' => array('trashed_at' => array('sorting' => 'ascending')),
                            'was' => 'trashed_at',
                        ),
                        'parent_entity_id' => array(
                            'fields' => array('parent_entity_id' => array('sorting' => 'ascending')),
                            'was' => 'parent_entity_id',
                        ),
                    ),
                );
        }
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values, array $currentValues = null)
    {
        switch ($this->_name) {          
            case 'content_trashed':
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

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values, Sabai_Addon_Entity_IEntity $entity){}
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {
        switch ($this->_name) {
            case 'content_trashed':
                return $valueToSave !== $currentLoadedValue;
        }
    }
    
    public function fieldSortableDoSort(Sabai_Addon_Field_IQuery $query, $fieldName, array $args = null)
    {
        switch ($this->_name) {
            case 'content_post_title':
                $query->sortByProperty('post_title', 'ASC');
                break;
            case 'content_post_published':
                $query->sortByProperty('post_published', isset($args) && $args[0] === 'asc' ? 'ASC' : 'DESC');
                break;
            case 'content_post_views':
                $query->sortByProperty('post_views', isset($args) && $args[0] === 'asc' ? 'ASC' : 'DESC');
                break;
        }
    }
}