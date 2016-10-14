<?php
class Sabai_Addon_Directory_FieldType implements Sabai_Addon_Field_IType
{
    private $_addon, $_name, $_info;

    public function __construct(Sabai_Addon_Directory $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldTypeGetInfo($key = null)
    {
        if (!isset($this->_info)) {
            $this->_info = array(
                'default_settings' => array(),
                'creatable' => false,
            );
            switch ($this->_name) {
                case 'directory_contact':
                    $this->_info += array(
                        'label' => 'Contact Info',
                        'default_renderer' => $this->_name,
                        'default_widget' => $this->_name,
                    );
                    break;
                case 'directory_social':
                    $this->_info += array(
                        'label' => 'Social Accounts',
                        'default_widget' => $this->_name,
                    );
                    break;
                case 'directory_photos':
                    $this->_info += array(
                        'label' => 'Listing Photos',
                        'default_renderer' => $this->_name,
                    );
                    break;
                case 'directory_photo':
                    $this->_info += array(
                        'label' => 'Listing Photo',
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
            case 'directory_contact':
                return array(
                    'columns' => array(
                        'phone' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 50,
                            'was' => 'phone',
                            'default' => '',
                        ),
                        'mobile' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 50,
                            'was' => 'mobile',
                            'default' => '',
                        ),
                        'fax' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 50,
                            'was' => 'fax',
                            'default' => '',
                        ),
                        'email' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 100,
                            'was' => 'email',
                            'default' => '',
                        ),
                        'website' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 255,
                            'was' => 'website',
                            'default' => '',
                        ),
                    ),
                );
            case 'directory_social':
                return array(
                    'columns' => array(
                        'twitter' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 20,
                            'was' => 'twitter',
                            'default' => '',
                        ),
                        'facebook' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 255,
                            'was' => 'facebook',
                            'default' => '',
                        ),
                        'googleplus' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                            'notnull' => true,
                            'length' => 255,
                            'was' => 'googleplus',
                            'default' => '',
                        ),
                    ),
                );
            case 'directory_photo':
                return array(
                    'columns' => array(
                        'official' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => true,
                            'length' => 1,
                            'was' => 'official',
                            'default' => 0,
                        ),
                        'display_order' => array(
                            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                            'notnull' => true,
                            'unsigned' => true,
                            'length' => 2,
                            'was' => 'display_order',
                            'default' => 0,
                        ),
                    ),
                    'indexes' => array(
                        'official_display_order' => array(
                            'fields' => array(
                                'official' => array('sorting' => 'ascending'),
                                'display_order' => array('sorting' => 'ascending'),
                            ),
                            'was' => 'official_display_order',
                        ),
                    ),
                );
        }
        
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values, array $currentValues = null)
    {
        switch ($this->_name) {
            case 'directory_contact':
                $ret = array();
                foreach ($values as $weight => $value) {
                    if (!is_array($value)) {
                        continue;
                    }
                    if (strlen((string)@$value['phone']) || strlen((string)@$value['mobile']) || strlen((string)@$value['fax']) || strlen((string)@$value['email']) || strlen((string)@$value['website'])) {
                        $ret[] = $value;
                    }
                }
                return $ret;
            case 'directory_social':
                $ret = array();
                foreach ($values as $weight => $value) {
                    if (!is_array($value)) {
                        continue;
                    }
                    if (strlen((string)@$value['twitter']) || strlen((string)@$value['facebook']) || strlen((string)@$value['googleplus'])) {
                        $ret[] = $value;
                    }
                }
                return $ret;
            case 'directory_photo':
                $ret = array();
                foreach ($values as $weight => $value) {
                    if (!is_array($value) || empty($value['official'])) {
                        continue;
                    }
                    $ret[] = $value;
                }
                return $ret;
        }
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values, Sabai_Addon_Entity_IEntity $entity){}
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {   
        switch ($this->_name) {
            default:
                return $valueToSave !== $currentLoadedValue;
        }
    }
}