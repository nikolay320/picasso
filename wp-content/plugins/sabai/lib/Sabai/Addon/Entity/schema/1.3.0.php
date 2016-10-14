<?php
$tables = array(
    'entity_bundle' => array(
        'fields' => array(
            'bundle_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'bundle_path' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 100,
                'default' => '',
            ),
            'bundle_type' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'bundle_addon' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'bundle_label' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'bundle_label_singular' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'bundle_info' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'bundle_system' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_BOOLEAN,
                'notnull' => true,
                'default' => false,
            ),
            'bundle_entitytype_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'bundle_id' => array(
                'autoincrement' => true,
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'bundle_created' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'bundle_updated' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
        ),
        'indexes' => array(
            'bundle_id' => array(
                'primary' => true,
                'fields' => array(
                    'bundle_id' => array('sorting' => 'ascending'),
                ),
            ),
            'bundle_name' => array(
                'unique' => true,
                'fields' => array(
                    'bundle_name' => array(
                    ),
                ),
            ),
            'bundle_addon' => array(
                'fields' => array(
                    'bundle_addon' => array(
                    ),
                ),
            ),
            'bundle_label' => array(
                'fields' => array(
                    'bundle_label' => array(
                    ),
                ),
            ),
            'bundle_label_singular' => array(
                'fields' => array(
                    'bundle_label_singular' => array(
                    ),
                ),
            ),
            'bundle_system' => array(
                'fields' => array(
                    'bundle_system' => array(
                    ),
                ),
            ),
            'bundle_entitytype_name' => array(
                'fields' => array(
                    'bundle_entitytype_name' => array(
                        'sorting' => 'ascending',
                    ),
                ),
            ),
        ),
   	'initialization' => array(
            'insert' => array(
            ),
        ),
    ),
    'entity_fieldconfig' => array(
        'fields' => array(
            'fieldconfig_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'fieldconfig_type' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'fieldconfig_storage' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'fieldconfig_system' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 2,
                'default' => 0,
            ),
            'fieldconfig_settings' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'fieldconfig_property' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 50,
                'default' => '',
            ),
            'fieldconfig_schema' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'fieldconfig_entitytype_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'fieldconfig_id' => array(
                'autoincrement' => true,
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'fieldconfig_created' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'fieldconfig_updated' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'fieldconfig_bundle_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
        ),
        'indexes' => array(
            'fieldconfig_id' => array(
                'primary' => true,
                'fields' => array(
                    'fieldconfig_id' => array('sorting' => 'ascending'),
                ),
            ),
            'fieldconfig_name' => array(
                'unique' => true,
                'fields' => array(
                    'fieldconfig_name' => array(
                    ),
                ),
            ),
            'fieldconfig_system' => array(
                'fields' => array(
                    'fieldconfig_system' => array(
                    ),
                ),
            ),
            'fieldconfig_entitytype_name' => array(
                'fields' => array(
                    'fieldconfig_entitytype_name' => array(
                        'sorting' => 'ascending',
                    ),
                ),
            ),
            'fieldconfig_bundle_id' => array('fields' => array('fieldconfig_bundle_id' => array())),
        ),
   	'initialization' => array(
            'insert' => array(
            ),
        ),
    ),
    'entity_field' => array(
        'fields' => array(
            'field_data' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'field_id' => array(
                'autoincrement' => true,
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'field_created' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'field_updated' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'field_bundle_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'field_fieldconfig_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
        ),
        'indexes' => array(
            'field_id' => array(
                'primary' => true,
                'fields' => array(
                    'field_id' => array('sorting' => 'ascending'),
                ),
            ),
            'field_bundle_id' => array('fields' => array('field_bundle_id' => array())),
            'field_fieldconfig_id' => array('fields' => array('field_fieldconfig_id' => array())),
        ),
   	'initialization' => array(
            'insert' => array(
            ),
        ),
    ),
    'entity_fieldcache' => array(
        'fields' => array(
            'fieldcache_entity_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'fieldcache_fields' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'fieldcache_entitytype_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'fieldcache_id' => array(
                'autoincrement' => true,
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'fieldcache_created' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'fieldcache_updated' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'fieldcache_bundle_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
        ),
        'indexes' => array(
            'fieldcache_id' => array(
                'primary' => true,
                'fields' => array(
                    'fieldcache_id' => array('sorting' => 'ascending'),
                ),
            ),
            'fieldcache_entity_id_entitytype_name' => array(
                'unique' => true,
                'fields' => array(
                    'fieldcache_entity_id' => array(
                        'sorting' => 'ascending',
                    ),
                    'fieldcache_entitytype_name' => array(
                        'sorting' => 'ascending',
                    ),
                ),
            ),
            'fieldcache_bundle_id' => array('fields' => array('fieldcache_bundle_id' => array())),
        ),
   	'initialization' => array(
            'insert' => array(
            ),
        ),
    ),
    'entity_filter' => array(
        'fields' => array(
            'filter_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'filter_type' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'filter_data' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'filter_id' => array(
                'autoincrement' => true,
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'filter_created' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'filter_updated' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'filter_bundle_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'filter_field_id' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
        ),
        'indexes' => array(
            'filter_id' => array(
                'primary' => true,
                'fields' => array(
                    'filter_id' => array('sorting' => 'ascending'),
                ),
            ),
            'filter_bundle_id' => array('fields' => array('filter_bundle_id' => array())),
            'filter_field_id' => array('fields' => array('filter_field_id' => array())),
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