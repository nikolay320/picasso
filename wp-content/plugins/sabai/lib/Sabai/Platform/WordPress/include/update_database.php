<?php
function sabai_platform_wordpress_update_database(Sabai_Platform_WordPress $platform, $schema, $previousSchema = null)
{
    global $wpdb;
    if (isset($schema)) {
        if (is_string($schema)) {
            $schema = include $schema;
        }
        $sql = _sabai_platform_wordpress_update_database_schema($platform, $schema);
        if ($sql['delta']) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql['delta']);
        }
        foreach ($sql['inserts'] as $table_name => $inserts) {
            foreach ($inserts as $insert) {
                $wpdb->insert($table_name, $insert);
            }
        }
    } elseif (isset($previousSchema)) {
        if (is_string($previousSchema)) {
            $previousSchema = include $previousSchema;
        }
        $sql = _sabai_platform_wordpress_update_database_schema($platform, $previousSchema);
        if (!empty($sql['tables'])) {
            $wpdb->query('DROP TABLE IF EXISTS ' . implode(',', $sql['tables']) . ';');
        }
    }
}
    
function _sabai_platform_wordpress_update_database_schema(Sabai_Platform_WordPress $platform, $schema)
{
    $ret = array('delta' => null, 'tables' => array(), 'inserts' => array());
    if (empty($schema['tables'])) {
        return $ret;
    }
    $sql = array();
    foreach ($schema['tables'] as $table => $table_info) {
        $table_name = $platform->getDBTablePrefix() . $table;
        if (strlen($table_name) > 64) {
            throw new Sabai_RuntimeException('Table name is too long: ' . $table_name);
        }
        $columns = array();
        foreach ($table_info['fields'] as $column => $column_info) {
            switch ($column_info['type']) {
                case Sabai_Addon_Field::COLUMN_TYPE_BOOLEAN:
                    $columns[] = sprintf(
                        '%s tinyint(1) DEFAULT \'%d\'%s',
                        $column,
                        !empty($column_info['default']) ? 1 : 0,
                        false === @$column_info['notnull'] ? '' : ' NOT NULL'
                    );
                    break;
                case Sabai_Addon_Field::COLUMN_TYPE_DECIMAL:
                    $scale = !isset($column_info['scale']) ? 2 : $column_info['scale'];
                    $columns[] = sprintf(
                        '%s decimal(%d,%d)%s DEFAULT \'%s\'%s',
                        $column,
                        empty($column_info['length']) ? 10 : $column_info['length'],
                        $scale,
                        !empty($column_info['unsigned']) ? ' unsigned' : '',
                        isset($column_info['default']) ? $column_info['default'] : '0.' . str_repeat('0', $scale),
                        false === @$column_info['notnull'] ? '' : ' NOT NULL'
                    );
                    break;
                case Sabai_Addon_Field::COLUMN_TYPE_INTEGER:
                    $length = empty($column_info['length']) ? 10 : $column_info['length'];
                    $type = $length > 10 ? 'bigint' : 'int';
                    $columns[] = sprintf(
                        '%s %s(%d)%s%s%s%s',
                        $column,
                        $type,
                        $length,
                        !empty($column_info['unsigned']) ? ' unsigned' : '',
                        empty($column_info['autoincrement']) && isset($column_info['default']) ? " DEFAULT '" . intval($column_info['default']) . "'" : '',
                        false === @$column_info['notnull'] ? '' : ' NOT NULL',
                        empty($column_info['autoincrement']) ? '' : ' AUTO_INCREMENT'
                    );
                    break;
                case Sabai_Addon_Field::COLUMN_TYPE_TEXT:
                    $columns[] = sprintf(
                        '%s text%s',
                        $column,
                        false === @$column_info['notnull'] ? '' : ' NOT NULL'
                    );
                    break;
                case Sabai_Addon_Field::COLUMN_TYPE_VARCHAR:
                    $columns[] = sprintf(
                        '%s varchar(%d) DEFAULT \'%s\'%s',
                        $column,
                        empty($column_info['length']) ? 255 : $column_info['length'],
                        (string)@$column_info['default'],
                        false === @$column_info['notnull'] ? '' : ' NOT NULL'
                    );
                    break;
            }
        }
        foreach ($table_info['indexes'] as $index => $index_info) {
            $index_fields = array();
            foreach ($index_info['fields'] as $field => $field_info) {
                $index_fields[] = isset($field_info['length']) ? $field . '(' . $field_info['length'] . ')' : $field;
            }
            if (!empty($index_info['primary'])) {
                $columns[] = sprintf('PRIMARY KEY  (%s)', implode(',', $index_fields));
            } elseif (!empty($index_info['unique'])) {
                $columns[] = sprintf('UNIQUE KEY %s (%s)', $index, implode(',', $index_fields));
            } else {
                $columns[] = sprintf('KEY %s (%s)', $index, implode(',', $index_fields));
            }
        }
        if (!empty($table_info['initialization'])) {
            foreach ($table_info['initialization'] as $init_type => $init_data) {
                switch ($init_type) {
                    case 'insert';
                        $ret['inserts'][$table_name] = $init_data;
                        break;
                }
            }
        }
        $sql[$table_name] = sprintf('CREATE TABLE %s (
  %s
) DEFAULT CHARSET=%s;',
        $table_name,
        implode(",\n", $columns),
        defined('DB_CHARSET') && DB_CHARSET === 'utf8mb4' && defined('SABAI_WORDPRESS_DB_CHARSET') && SABAI_WORDPRESS_DB_CHARSET === 'utf8mb4' ? 'utf8mb4' : 'utf8');
    }
    if (!empty($sql)) {
        $ret['delta'] = implode("\n", $sql);
        $ret['tables'] = array_keys($sql);
    }
    return $ret;
}