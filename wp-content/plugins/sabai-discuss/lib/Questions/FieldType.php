<?php
class Sabai_Addon_Questions_FieldType implements Sabai_Addon_Field_IType
{
    private $_addon, $_name, $_info;

    public function __construct(Sabai_Addon_Questions $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldTypeGetInfo($key = null)
    {
        if (!isset($this->_info)) {
            switch ($this->_name) {
                case 'questions_resolved';
                    $this->_info = array(
                        'label' => 'Answered Question',
                        'default_settings' => array(),
                        'creatable' => false,
                    );
                    break;
                case 'questions_closed';
                    $this->_info = array(
                        'label' => __('Closed Question', 'sabai-discuss'),
                        'default_settings' => array(),
                        'creatable' => false,
                    );
                    break;
                case 'questions_answer_accepted';
                    $this->_info = array(
                        'label' => 'Accepted Answer',
                        'default_settings' => array(),
                        'creatable' => false,
                    );
                    break;
                default:
                    return;
            }
        }

        return isset($key) ? @$this->_info[$key] : $this->_info;
    }

    public function fieldTypeGetSettingsForm(array $settings, array $parents = array())
    {

    }

    public function fieldTypeGetSchema(array $settings)
    {
        switch ($this->_name) {
            case 'questions_resolved':
                return array(
                    'columns' => array(
                        'value' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_BOOLEAN,
                            'notnull' => true,
                            'unsigned' => true,
                            'was' => 'value',
                            'default' => false,
                        ),
                        'resolved_at' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => true,
                            'was' => 'resolved_at',
                            'default' => 0,
                        ),
                    ),
                    'indexes' => array(
                        'value' => array(
                            'fields' => array('value' => array('sorting' => 'ascending')),
                            'was' => 'value',
                        ),
                    ),
                );
            case 'questions_closed':
                return array(
                    'columns' => array(
                        'value' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_BOOLEAN,
                            'notnull' => true,
                            'unsigned' => true,
                            'was' => 'value',
                            'default' => false,
                        ),
                        'closed_at' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => true,
                            'was' => 'closed_at',
                            'default' => 0,
                        ),
                        'closed_by' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => true,
                            'was' => 'closed_by',
                            'default' => 0,
                        ),
                    ),
                    'indexes' => array(
                        'value' => array(
                            'fields' => array('value' => array('sorting' => 'ascending')),
                            'was' => 'value',
                        ),
                    ),
                );
            case 'questions_answer_accepted':
                return array(
                    'columns' => array(
                        'score' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => true,
                            'was' => 'score',
                            'default' => 0,
                        ),
                        'accepted_at' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => true,
                            'was' => 'accepted_at',
                            'default' => 0,
                        ),
                    ),
                    'indexes' => array(
                        'score' => array(
                            'fields' => array('score' => array('sorting' => 'ascending')),
                            'was' => 'value',
                        ),
                    ),
                );
        }
        
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        switch ($this->_name) {
            case 'questions_resolved':
                $ret = array();
                foreach ($values as $weight => $value) {
                    $value = is_array($value) ? (bool)$value['value'] : (bool)$value;
                    if ($value === false) {
                        $ret[] = false; // Do not save. Just delete entry from the storage.
                    } else {
                        $ret[] = array(
                            'value' => true,
                            'resolved_at' => time(),
                        );
                    }
                }
                return $ret;
                
            case 'questions_closed':
                $ret = array();
                foreach ($values as $weight => $value) {
                    $value = is_array($value) ? (bool)$value['value'] : (bool)$value;
                    if ($value === false) {
                        $ret[] = false; // Do not save. Just delete entry from the storage.
                    } else {
                        $ret[] = array(
                            'value' => true,
                            'closed_at' => time(),
                            'closed_by' => $this->_addon->getApplication()->getUser()->id,
                        );
                    }
                }
                return $ret;

            case 'questions_answer_accepted':
                $ret = array();
                foreach ($values as $weight => $value) {
                    $score = is_array($value) && isset($value['score']) ? (int)$value['score'] : 0;
                    if ($score <= 0) {
                        $ret[] = false; // Do not save. Just delete entry from the storage.
                    } else {
                        $ret[] = array('score' => $score) + $value;
                    }
                }
                return $ret;
        }
    }
    
    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values, Sabai_Addon_Entity_IEntity $entity)
    {
        switch ($this->_name) {
            case 'questions_resolved':
            case 'questions_closed':
                foreach ($values as $key => $value) {
                    $values[$key] = $value['value'];
                }
                break;
        }
    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {   
        switch ($this->_name) {
            case 'questions_resolved':
            case 'questions_closed':
                $new = array();
                foreach ($valueToSave as $value) {
                    $new[] = $value['value'];
                }
                return $currentLoadedValue !== $new;
            case 'questions_answer_accepted':
                return $valueToSave !== $currentLoadedValue;
        }
    }
}