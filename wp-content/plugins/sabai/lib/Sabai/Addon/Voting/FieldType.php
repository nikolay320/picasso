<?php
class Sabai_Addon_Voting_FieldType implements Sabai_Addon_Field_IType, Sabai_Addon_Field_ISortable
{
    private $_addon, $_name;

    public function __construct(Sabai_Addon_Voting $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldTypeGetInfo($key = null)
    {
        switch ($this->_name) {
            case 'voting_default':
                $info = array(
                    'label' => __('Vote', 'sabai'),
                    'default_settings' => array(),
                );
                break;
            case 'voting_updown':
                $info = array(
                    'label' => __('Vote', 'sabai'),
                    'default_settings' => array(),
                );
                break;
            case 'voting_helpful':
                $info = array(
                    'label' => __('Helpful', 'sabai'),
                    'default_settings' => array(),
                );
                break;
            case 'voting_rating':
                $info = array(
                    'label' => __('Rating', 'sabai'),
                    'default_settings' => array(),
                    'max_num_items' => 0,
                    'default_renderer' => 'voting_rating',
                );
                break;
            case 'voting_favorite':
                $info = array(
                    'label' => __('Favorite', 'sabai'),
                    'default_settings' => array(),
                );
                break;
            case 'voting_flag':
                $info = array(
                    'label' => __('Flag', 'sabai'),
                    'default_settings' => array(),
                );
                break;
            default:
                return;
        }

        return isset($key) ? @$info[$key] : $info;
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {

    }

    public function fieldTypeGetSchema(array $settings)
    {
        switch ($this->_name) {
            case 'voting_updown':
            case 'voting_flag':
            case 'voting_helpful':
                $schema = array(
                    'columns' => array(
                        'count' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'was' => 'count',
                            'default' => 0,
                        ),
                        'sum' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => false,
                            'was' => 'sum',
                            'default' => 0,
                        ),
                        'average' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_DECIMAL,
                            'notnull' => true,
                            'length' => 5,
                            'scale' => 2,
                            'unsigned' => false,
                            'was' => 'average',
                            'default' => 0,
                        ),
                        'last_voted_at' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'was' => 'last_voted_at',
                            'default' => 0,
                        ),
                    ),
                    'indexes' => array(
                        'count' => array(
                            'fields' => array('count' => array('sorting' => 'ascending')),
                            'was' => 'count',
                        ),
                        'sum' => array(
                            'fields' => array('sum' => array('sorting' => 'ascending')),
                            'was' => 'sum',
                        ),
                        'average' => array(
                            'fields' => array('average' => array('sorting' => 'ascending')),
                            'was' => 'average',
                        ),
                        'last_voted_at' => array(
                            'fields' => array('last_voted_at' => array('sorting' => 'ascending')),
                            'was' => 'last_voted_at',
                        ),
                    ),
                );
                if ($this->_name === 'voting_updown') {
                    $schema['columns'] += array(
                        'count_init' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => false,
                            'was' => 'count_init',
                            'default' => 0,
                        ),
                        'sum_init' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => false,
                            'was' => 'sum_init',
                            'default' => 0,
                        ),
                    );
                }
                return $schema;
            case 'voting_rating':
            case 'voting_default':
                return array(
                    'columns' => array(
                        'count' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'was' => 'count',
                            'default' => 0,
                        ),
                        'sum' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_DECIMAL,
                            'notnull' => true,
                            'length' => 5,
                            'scale' => 2,
                            'unsigned' => false,
                            'was' => 'sum',
                            'default' => 0,
                        ),
                        'average' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_DECIMAL,
                            'notnull' => true,
                            'length' => 5,
                            'scale' => 2,
                            'unsigned' => false,
                            'was' => 'average',
                            'default' => 0,
                        ),
                        'last_voted_at' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'was' => 'last_voted_at',
                            'default' => 0,
                        ),
                        'name' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'length' => 40,
                            'notnull' => true,
                            'was' => 'name',
                            'default' => '',
                        ),
                    ),
                    'indexes' => array(
                        'count' => array(
                            'fields' => array('count' => array('sorting' => 'ascending')),
                            'was' => 'count',
                        ),
                        'sum' => array(
                            'fields' => array('sum' => array('sorting' => 'ascending')),
                            'was' => 'sum',
                        ),
                        'average' => array(
                            'fields' => array('average' => array('sorting' => 'ascending')),
                            'was' => 'average',
                        ),
                        'last_voted_at' => array(
                            'fields' => array('last_voted_at' => array('sorting' => 'ascending')),
                            'was' => 'last_voted_at',
                        ),
                        'name' => array(
                            'fields' => array('name' => array('sorting' => 'ascending')),
                            'was' => 'name',
                        ),
                    ),
                );
            case 'voting_favorite':
                return array(
                    'columns' => array(
                        'count' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'was' => 'count',
                            'default' => 0,
                        ),
                        'last_voted_at' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'was' => 'last_voted_at',
                            'default' => 0,
                        ),
                        'count_init' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => false,
                            'was' => 'count_init',
                            'default' => 0,
                        ),
                    ),
                    'indexes' => array(
                        'count' => array(
                            'fields' => array('count' => array('sorting' => 'ascending')),
                            'was' => 'count',
                        ),
                        'last_voted_at' => array(
                            'fields' => array('last_voted_at' => array('sorting' => 'ascending')),
                            'was' => 'last_voted_at',
                        ),
                    ),
                );
        }
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        switch ($this->_name) {
            case 'voting_updown':
            case 'voting_default':
                // these fields require entries with 0 count (0 sum) saved so that they can be properly
                // sorted with entries with value of sum below 0
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
                $ret = array();
                foreach ($values as $weight => $value) {
                    $count = is_array($value) && isset($value['count']) ? (int)$value['count'] : 0;
                    if ($count <= 0) {
                        $ret[] = false; // Do not save. Just delete entry from the storage.
                    } else {
                        $ret[] = array('count' => $count) + $value;
                    }
                }
                return $ret;
        }        
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values, Sabai_Addon_Entity_IEntity $entity)
    {
        switch ($this->_name) {
            case 'voting_rating':
            case 'voting_default':
                $new_values = array();
                foreach ($values as $value) {
                    // Index by vote names
                    $new_values[$value['name']] = array(
                        'count' => $value['count'],
                        'sum' => $value['sum'],
                        'average' => $value['average'],
                        'last_voted_at' => $value['last_voted_at'],
                    );
                }
                $values = $new_values;
                break;
        }
    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {
        switch ($this->_name) {
            case 'voting_rating':
            case 'voting_default':
                $current = array();
                if (!empty($currentLoadedValue)) {
                    foreach ($currentLoadedValue as $name => $value) {
                        $current[] = $value + array('name' => $name);
                    }
                }
                return $current !== $valueToSave;
            default:
                return $valueToSave !== $currentLoadedValue;
        }
    }
    
    public function fieldSortableDoSort(Sabai_Addon_Field_IQuery $query, $fieldName, array $args = null)
    {
        switch ($this->_name) {
            case 'voting_rating':
                $query->startCriteriaGroup('OR')
                    ->fieldIs('voting_rating', '', 'name')
                    ->fieldIsNull('voting_rating', 'average')
                    ->finishCriteriaGroup()
                    ->sortByField('voting_rating', isset($args) && $args[0] === 'asc' ? 'ASC' : 'DESC', 'average');
                break;
            case 'voting_helpful':
                $order = isset($args) && $args[0] === 'asc' ? 'ASC' : 'DESC';
                $query->sortByField('voting_helpful', $order, 'average')
                    ->sortByField('voting_helpful', $order, 'sum');
                break;
            case 'voting_updown':
                $query->sortByField('voting_updown', isset($args) && $args[0] === 'asc' ? 'ASC' : 'DESC', 'sum');
                break;
            case 'voting_flags':
                $order = isset($args) && $args[0] === 'asc' ? 'ASC' : 'DESC';
                switch (@$args['type']) {
                    case 'sum':
                        $query->sortByField('voting_flag', $order, 'sum')
                            ->sortByField('voting_flag', $order, 'last_voted_at');
                        break;
                    case 'count':
                        $query->sortByField('voting_flag', $order, 'count')
                            ->sortByField('voting_flag', $order, 'last_voted_at');
                        break;
                    default:
                        $query->sortByField('voting_flag', $order, 'last_voted_at');
                }
        }
    }
}