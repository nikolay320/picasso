<?php
$tables = array(
    'system_addon' => array(
        'fields' => array(
            'addon_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'addon_version' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 30,
                'default' => '',
            ),
            'addon_params' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'addon_priority' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 2,
                'default' => 0,
            ),
            'addon_events' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'addon_parent_addon' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'addon_created' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'addon_updated' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
        ),
        'indexes' => array(
            'addon_name' => array(
                'primary' => true,
                'fields' => array(
                    'addon_name' => array('sorting' => 'ascending'),
                ),
            ),
            'addon_priority' => array(
                'fields' => array(
                    'addon_priority' => array(
                    ),
                ),
            ),
        ),
   	'initialization' => array(
            'insert' => array(
                array(
                    'addon_name' => 'System',
                    'addon_created' => '1357603200',
                    'addon_updated' => '0',
                    'addon_version' => '1.3.28',
                    'addon_params' => '',
                    'addon_priority' => '99',
                    'addon_events' => '',
                    'addon_parent_addon' => '',
                ),
            ),
        ),
    ),
    'system_route' => array(
        'fields' => array(
            'route_path' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'route_method' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 10,
                'default' => '',
            ),
            'route_format' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'route_controller' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'route_controller_addon' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'route_forward' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'route_addon' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'route_type' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 2,
                'default' => 0,
            ),
            'route_class' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'route_access_callback' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_BOOLEAN,
                'notnull' => true,
                'default' => false,
            ),
            'route_title_callback' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_BOOLEAN,
                'notnull' => true,
                'default' => false,
            ),
            'route_callback_path' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'route_callback_addon' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'route_title' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'route_description' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'route_weight' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 3,
                'default' => 0,
            ),
            'route_depth' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 2,
                'default' => 0,
            ),
            'route_ajax' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 1,
                'default' => 0,
            ),
            'route_priority' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 1,
                'default' => 5,
            ),
            'route_data' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'route_id' => array(
                'autoincrement' => true,
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'route_created' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'route_updated' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
        ),
        'indexes' => array(
            'route_id' => array(
                'primary' => true,
                'fields' => array(
                    'route_id' => array('sorting' => 'ascending'),
                ),
            ),
            'route_path' => array(
                'fields' => array(
                    'route_path' => array(
                    ),
                ),
            ),
            'route_addon' => array(
                'fields' => array(
                    'route_addon' => array(
                    ),
                ),
            ),
            'route_depth' => array(
                'fields' => array(
                    'route_depth' => array(
                    ),
                ),
            ),
        ),
   	'initialization' => array(
            'insert' => array(
            ),
        ),
    ),
    'system_adminroute' => array(
        'fields' => array(
            'adminroute_path' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'adminroute_method' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 10,
                'default' => '',
            ),
            'adminroute_format' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'adminroute_controller' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'adminroute_controller_addon' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'adminroute_forward' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'adminroute_addon' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'adminroute_type' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 2,
                'default' => 0,
            ),
            'adminroute_class' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'adminroute_access_callback' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_BOOLEAN,
                'notnull' => true,
                'default' => false,
            ),
            'adminroute_title_callback' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_BOOLEAN,
                'notnull' => true,
                'default' => false,
            ),
            'adminroute_callback_path' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'adminroute_callback_addon' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'adminroute_title' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'adminroute_description' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'adminroute_weight' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 3,
                'default' => 0,
            ),
            'adminroute_depth' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 2,
                'default' => 0,
            ),
            'adminroute_ajax' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 1,
                'default' => 0,
            ),
            'adminroute_priority' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 1,
                'default' => 5,
            ),
            'adminroute_data' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'adminroute_id' => array(
                'autoincrement' => true,
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'adminroute_created' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'adminroute_updated' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
        ),
        'indexes' => array(
            'adminroute_id' => array(
                'primary' => true,
                'fields' => array(
                    'adminroute_id' => array('sorting' => 'ascending'),
                ),
            ),
            'adminroute_path' => array(
                'fields' => array(
                    'adminroute_path' => array(
                    ),
                ),
            ),
            'adminroute_addon' => array(
                'fields' => array(
                    'adminroute_addon' => array(
                    ),
                ),
            ),
            'adminroute_depth' => array(
                'fields' => array(
                    'adminroute_depth' => array(
                    ),
                ),
            ),
        ),
   	'initialization' => array(
            'insert' => array(
            ),
        ),
    ),
    'system_role' => array(
        'fields' => array(
            'role_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'role_title' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'role_permissions' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'role_created' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'role_updated' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
        ),
        'indexes' => array(
            'role_name' => array(
                'primary' => true,
                'fields' => array(
                    'role_name' => array('sorting' => 'ascending'),
                ),
            ),
        ),
   	'initialization' => array(
            'insert' => array(
            ),
        ),
    ),
    'system_permission' => array(
        'fields' => array(
            'permission_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'permission_title' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'permission_addon' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 40,
                'default' => '',
            ),
            'permission_weight' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 3,
                'default' => 0,
            ),
            'permission_description' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_TEXT,
                'notnull' => true,
            ),
            'permission_guest_allowed' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_BOOLEAN,
                'notnull' => true,
                'default' => false,
            ),
            'permission_permissioncategory_name' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_VARCHAR,
                'notnull' => true,
                'length' => 255,
                'default' => '',
            ),
            'permission_id' => array(
                'autoincrement' => true,
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'permission_created' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
            'permission_updated' => array(
                'type' => Sabai_Addon_Field::COLUMN_TYPE_INTEGER,
                'unsigned' => true,
                'notnull' => true,
                'length' => 10,
                'default' => 0,
            ),
        ),
        'indexes' => array(
            'permission_id' => array(
                'primary' => true,
                'fields' => array(
                    'permission_id' => array('sorting' => 'ascending'),
                ),
            ),
            'permission_name' => array(
                'unique' => true,
                'fields' => array(
                    'permission_name' => array(
                    ),
                ),
            ),
            'permission_title' => array(
                'fields' => array(
                    'permission_title' => array(
                    ),
                ),
            ),
            'permission_addon' => array(
                'fields' => array(
                    'permission_addon' => array(
                    ),
                ),
            ),
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