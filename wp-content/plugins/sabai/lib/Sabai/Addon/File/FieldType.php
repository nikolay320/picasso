<?php
class Sabai_Addon_File_FieldType implements Sabai_Addon_Field_IType
{
    private $_addon, $_name;

    public function __construct(Sabai_Addon_File $addon, $name)
    {
        $this->_addon = $addon;
        $this->_name = $name;
    }

    public function fieldTypeGetInfo($key = null)
    {
        switch ($this->_name) {
            case 'file_file';
                $info = array(
                    'label' => __('File', 'sabai'),
                    'default_widget' => 'file_upload',
                    'default_renderer' => $this->_name,
                    'entity_types' => array('content', 'taxonomy'),
                );
                break;
            case 'file_image':
                $info = array(
                    'label' => __('Image', 'sabai'),
                    'default_widget' => 'file_upload',
                    'default_renderer' => $this->_name,
                    'entity_types' => array('content', 'taxonomy'),
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
        return array(
            'columns' => array(
                'file_id' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                    'notnull' => true,
                    'unsigned' => true,
                    'was' => 'value',
                    'default' => 0,
                ),
            ),
            'indexes' => array(
                'file_id' => array(
                    'fields' => array('file_id' => array('sorting' => 'ascending')),
                    'was' => 'value',
                ),
            ),
        );
    }

    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        $ret = array();
        foreach ($values as $value) {
            if (empty($value['id'])) continue;

            $ret[] = array('file_id' => $value['id']);
        }

        return $ret;
    }

    public function fieldTypeOnLoad(Sabai_Addon_Field_IField $field, array &$values, Sabai_Addon_Entity_IEntity $entity)
    {
        $files = array();
        foreach ($values as $key => $value) {
            $files[$value['file_id']] = $value['file_id'];
        }
        // Fetch file objects
        foreach ($this->_addon->getModel('File')->fetchByIds($files) as $file) {
            $files[$file->id] = $file->toArray();
        }
        $values = array_values($files);
    }
    
    public function fieldTypeIsModified($field, $valueToSave, $currentLoadedValue)
    {
        $current = $new = array();
        foreach ($currentLoadedValue as $value) {
            $current[] = $value['id'];
        }
        foreach ($valueToSave as $value) {
            $new[] = $value['file_id'];
        }
        return $current !== $new;
    }
}