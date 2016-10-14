<?php
$tables = array(
    'file_token' => array(
        'fields' => array(
            'token_hash' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'fixed' => true,
                'notnull' => true,
                'length' => 32,
                'default' => '',
            ),
            'token_form_build_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'fixed' => true,
                'notnull' => true,
                'length' => 32,
                'default' => '',
            ),
            'token_form_field_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'token_expires' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'token_settings' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'token_file_count' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 3,
                'default' => 0,
            ),
            'token_id' => array(
                'autoincrement' => true,
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'token_created' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'token_updated' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'token_user_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
        ),
        'indexes' => array(
            'token_id' => array(
                'primary' => true,
                'fields' => array(
                    'token_id' => array('sorting' => 'ascending'),
                ),
            ),
            'token_hash' => array(
                'fields' => array(
                    'token_hash' => array(
                    ),
                ),
            ),
            'token_form_build_id' => array(
                'fields' => array(
                    'token_form_build_id' => array(
                    ),
                ),
            ),
            'token_expires' => array(
                'fields' => array(
                    'token_expires' => array(
                    ),
                ),
            ),
            'token_user_id' => array('fields' => array('token_user_id' => array())),
        ),
   	'initialization' => array(
            'insert' => array(
            ),
        ),
    ),
    'file_file' => array(
        'fields' => array(
            'file_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'file_title' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'file_type' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 50,
                'default' => '',
            ),
            'file_size' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'file_extension' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 20,
                'default' => '',
            ),
            'file_hash' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 32,
                'default' => '',
            ),
            'file_is_image' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 1,
                'default' => 0,
            ),
            'file_width' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 5,
                'default' => 0,
            ),
            'file_height' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 5,
                'default' => 0,
            ),
            'file_id' => array(
                'autoincrement' => true,
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'file_created' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'file_updated' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'file_token_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'file_user_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
        ),
        'indexes' => array(
            'file_id' => array(
                'primary' => true,
                'fields' => array(
                    'file_id' => array('sorting' => 'ascending'),
                ),
            ),
            'file_token_id' => array('fields' => array('file_token_id' => array())),
            'file_user_id' => array('fields' => array('file_user_id' => array())),
        ),
   	'initialization' => array(
            'insert' => array(
            ),
        ),
    ),
);
return array(
    'charset' => '',
    'description' => '',
    'tables' => $tables,
);