<?php
$tables = array(
    'directory_claim' => array(
        'fields' => array(
            'claim_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 50,
                'default' => '',
            ),
            'claim_email' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 50,
                'default' => '',
            ),
            'claim_comment' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'claim_status' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 20,
                'default' => '',
            ),
            'claim_type' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 10,
                'default' => '',
            ),
            'claim_admin_note' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'claim_entity_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'claim_entity_bundle_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'claim_id' => array(
                'autoincrement' => true,
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'claim_created' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'claim_updated' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'claim_user_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
        ),
        'indexes' => array(
            'claim_id' => array(
                'primary' => true,
                'fields' => array(
                    'claim_id' => array('sorting' => 'ascending'),
                ),
            ),
            'claim_user_id' => array('fields' => array('claim_user_id' => array())),
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