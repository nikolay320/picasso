<?php
$tables = array(
    'voting_vote' => array(
        'fields' => array(
            'vote_entity_type' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'vote_bundle_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'vote_entity_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'vote_value' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_DECIMAL,
                'unsigned' => false,
                'notnull' => true,
                'length' => 5,
                'scale' => 2,
                'default' => 0,
            ),
            'vote_tag' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'vote_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'vote_comment' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'vote_reference_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'vote_ip' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 15,
                'default' => '',
            ),
            'vote_id' => array(
                'autoincrement' => true,
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'vote_created' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'vote_updated' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'vote_user_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
        ),
        'indexes' => array(
            'vote_id' => array(
                'primary' => true,
                'fields' => array(
                    'vote_id' => array('sorting' => 'ascending'),
                ),
            ),
            'vote_entity_type_id_tag_user' => array(
                'fields' => array(
                    'vote_entity_type' => array(
                        'sorting' => 'ascending',
                    ),
                    'vote_bundle_id' => array(
                        'sorting' => 'ascending',
                    ),
                    'vote_entity_id' => array(
                        'sorting' => 'ascending',
                    ),
                    'vote_tag' => array(
                        'sorting' => 'ascending',
                    ),
                    'vote_user_id' => array(
                        'sorting' => 'ascending',
                    ),
                    'vote_name' => array(
                        'sorting' => 'ascending',
                    ),
                    'vote_reference_id' => array(
                        'sorting' => 'ascending',
                    ),
                ),
            ),
            'vote_user_id' => array('fields' => array('vote_user_id' => array())),
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