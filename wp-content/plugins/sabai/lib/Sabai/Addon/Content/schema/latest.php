<?php
$tables = array(
    'content_post' => array(
        'fields' => array(
            'post_title' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'post_slug' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'post_published' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'post_status' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 20,
                'default' => '',
            ),
            'post_views' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 7,
                'default' => 0,
            ),
            'post_entity_bundle_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'post_entity_bundle_type' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'post_id' => array(
                'autoincrement' => true,
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'post_created' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'post_updated' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'post_user_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
        ),
        'indexes' => array(
            'post_id' => array(
                'primary' => true,
                'fields' => array(
                    'post_id' => array('sorting' => 'ascending'),
                ),
            ),
            'post_title' => array(
                'fields' => array(
                    'post_title' => array(
                    ),
                ),
            ),
            'post_slug' => array(
                'fields' => array(
                    'post_slug' => array(
                    ),
                ),
            ),
            'post_published' => array(
                'fields' => array(
                    'post_published' => array(
                    ),
                ),
            ),
            'post_status' => array(
                'fields' => array(
                    'post_status' => array(
                    ),
                ),
            ),
            'post_views' => array(
                'fields' => array(
                    'post_views' => array(
                    ),
                ),
            ),
            'post_entity_bundle_name' => array(
                'fields' => array(
                    'post_entity_bundle_name' => array(
                    ),
                ),
            ),
            'post_entity_bundle_type' => array(
                'fields' => array(
                    'post_entity_bundle_type' => array(
                    ),
                ),
            ),
            'post_user_id' => array('fields' => array('post_user_id' => array())),
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