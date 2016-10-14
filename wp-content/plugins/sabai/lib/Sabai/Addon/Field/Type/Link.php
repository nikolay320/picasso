<?php
class Sabai_Addon_Field_Type_Link extends Sabai_Addon_Field_Type_AbstractType
{
    protected function _fieldTypeGetInfo()
    {
        return array(
            'label' => __('Link', 'sabai'),
            'default_widget' => 'link',
            'default_renderer' => 'link',
            'default_settings' => array(),
        );
    }

    public function fieldTypeGetSchema(array $settings)
    {
        return array(
            'columns' => array(
                'url' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'notnull' => true,
                    'was' => 'url',
                    'length' => 400,
                ),
                'title' => array(
                    'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                    'length' => 255,
                    'notnull' => true,
                    'was' => 'title',
                ),
            ),
            'indexes' => array(
                'url' => array(
                    'fields' => array('url' => array('sorting' => 'ascending', 'length' => 50)),
                    'was' => 'url',
                ),
                'title' => array(
                    'fields' => array('title' => array('sorting' => 'ascending', 'length' => 50)),
                    'was' => 'title',
                ),
            ),
        );
    }
    
    public function fieldTypeOnSave(Sabai_Addon_Field_IField $field, array $values)
    {
        $ret = array();
        foreach ($values as $weight => $value) {
            if (!is_array($value) || !is_string($value['url']) || strlen($value['url']) === 0 || $value['url'] === 'http://') continue;

            $ret[] = $value + array('title' => '');
        }

        return $ret;
    }
    
    public function fieldTypeOnExport(Sabai_Addon_Field_IField $field, array &$values)
    {
        foreach (array_keys($values) as $key) {
            $values[$key] = $values[$key]['title'] !== '' ? $values[$key]['url'] . '|' . $values[$key]['title'] : $values[$key]['url'];
        }
    }
}