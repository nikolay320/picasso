<?php
$tables = array(
    'comment_post' => array(
        'fields' => array(
            'post_body' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'post_body_html' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'post_entity_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'post_entity_bundle_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'post_published_at' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'post_status' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 1,
                'default' => 0,
            ),
            'post_vote_sum' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'post_vote_count' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'post_flag_sum' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'post_flag_count' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'post_edit_count' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'post_edit_last_at' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'post_edit_last_by' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'post_edit_last_reason' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'post_hidden_at' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'post_hidden_by' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'post_vote_disabled' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_BOOLEAN,
                'notnull' => true,
                'default' => false,
            ),
            'post_flag_disabled' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_BOOLEAN,
                'notnull' => true,
                'default' => false,
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
            'post_entity_bundle_id' => array(
                'fields' => array(
                    'post_entity_bundle_id' => array(
                    ),
                ),
            ),
            'post_published_at' => array(
                'fields' => array(
                    'post_published_at' => array(
                    ),
                ),
            ),
            'post_status' => array(
                'fields' => array(
                    'post_status' => array(
                    ),
                ),
            ),
            'post_vote_sum' => array(
                'fields' => array(
                    'post_vote_sum' => array(
                    ),
                ),
            ),
            'post_flag_sum' => array(
                'fields' => array(
                    'post_flag_sum' => array(
                    ),
                ),
            ),
            'post_edit_last_at' => array(
                'fields' => array(
                    'post_edit_last_at' => array(
                    ),
                ),
            ),
            'post_hidden_at' => array(
                'fields' => array(
                    'post_hidden_at' => array(
                    ),
                ),
            ),
            'post_entity_id_status' => array(
                'fields' => array(
                    'post_entity_id' => array(
                        'sorting' => 'ascending',
                    ),
                    'post_status' => array(
                        'sorting' => 'ascending',
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
    'comment_vote' => array(
        'fields' => array(
            'vote_value' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => false,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'vote_tag' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
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
            'vote_post_id' => array(
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
            'vote_tag' => array(
                'fields' => array(
                    'vote_tag' => array(
                    ),
                ),
            ),
            'vote_post_id_user' => array(
                'unique' => true,
                'fields' => array(
                    'vote_post_id' => array(
                        'sorting' => 'ascending',
                    ),
                    'vote_user_id' => array(
                        'sorting' => 'ascending',
                    ),
                ),
            ),
            'vote_post_id' => array('fields' => array('vote_post_id' => array())),
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