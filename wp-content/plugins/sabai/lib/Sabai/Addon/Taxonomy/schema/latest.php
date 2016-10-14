<?php
$tables = array(
    'taxonomy_term' => array(
        'fields' => array(
            'term_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'term_title' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'term_entity_bundle_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'term_entity_bundle_type' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'term_id' => array(
                'autoincrement' => true,
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'term_created' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'term_updated' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'term_parent' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'term_user_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
        ),
        'indexes' => array(
            'term_id' => array(
                'primary' => true,
                'fields' => array(
                    'term_id' => array('sorting' => 'ascending'),
                ),
            ),
            'term_name' => array(
                'fields' => array(
                    'term_name' => array(
                    ),
                ),
            ),
            'term_title' => array(
                'fields' => array(
                    'term_title' => array(
                    ),
                ),
            ),
            'term_entity_bundle_name' => array(
                'fields' => array(
                    'term_entity_bundle_name' => array(
                    ),
                ),
            ),
            'term_entity_bundle_type' => array(
                'fields' => array(
                    'term_entity_bundle_type' => array(
                    ),
                ),
            ),
            'term_parent' => array('fields' => array('term_parent' => array())),
            'term_user_id' => array('fields' => array('term_user_id' => array())),
        ),
   	'initialization' => array(
            'insert' => array(
            ),
        ),
    ),
);
$tables['taxonomy_term_tree'] = array(
    'fields' => array(
        'tree_ancestor' => array(
            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
            ),
        'tree_descendant' => array(
            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
            'unsigned' => true,
            'notnull' => true,
        ),
        'tree_path_length' => array(
            'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
            'unsigned' => true,
            'notnull' => true,
            'length' => 5,
        ),
    ),
    'indexes' => array(
        'PRIMARY' => array(
            'fields' => array(
                'tree_ancestor' => array(),
                'tree_descendant' => array(),
                'tree_path_length' => array(),
            ),
            'primary' => true,
        ),
    ),
);
return array(
    'charset' => '',
    'description' => '',
    'tables' => $tables,
);