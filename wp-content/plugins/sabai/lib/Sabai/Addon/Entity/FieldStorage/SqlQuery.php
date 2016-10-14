<?php
class Sabai_Addon_Entity_FieldStorage_SqlQuery implements SabaiFramework_Criteria_Visitor
{
    private $_tableName, $_tableIdColumn, $_tableColumns, $_tableJoins, $_fieldColumnTypes, $_db, $_fieldQuery, $_parsed = false,
        $_criteria, $_joins, $_countJoins, $_sorts, $_group, $_groupSort, $_tables = array(), $_sortTables = array(), $_extraFields, $_distinct;

    public function __construct(array $entityTypeInfo, array $fieldColumnTypes, SabaiFramework_DB $db, Sabai_Addon_Entity_FieldQuery $fieldQuery)
    {
        $this->_tableName = $entityTypeInfo['table_name'];
        $this->_tableIdColumn = ($table_id_column = $fieldQuery->getTableIdColumn()) ? $table_id_column : 'entity.' . $entityTypeInfo['table_id_key'];
        $this->_tableColumns = $entityTypeInfo['properties'];
        $this->_fieldColumnTypes = $fieldColumnTypes;
        $this->_db = $db;
        $this->_fieldQuery = $fieldQuery;
        $table_joins = empty($entityTypeInfo['table_joins']) ? $fieldQuery->getTableJoins() : $entityTypeInfo['table_joins'] + $fieldQuery->getTableJoins();
        if (!empty($table_joins)) {
            $_table_joins = array();
            foreach ($table_joins as $table_name => $table) {
                $_table_joins[$table['alias']] = sprintf('LEFT JOIN %1$s %2$s ON %2$s.%3$s', $table_name, $table['alias'], $table['on']);
            }
            $this->_tableJoins = implode(' ', $_table_joins);
        } else {
            $this->_tableJoins = '';
        }
    }

    public function getEntityCount($limit = 0, $offset = 0)
    {
        $this->_parseFieldQuery();
        
        if ($this->_group) {
            $sql = sprintf(
                'SELECT %6$s, COUNT(%1$s) AS cnt FROM %2$s entity %3$s %4$s WHERE %5$s GROUP BY %6$s %7$s',
                $this->_distinct ? 'DISTINCT(' . $this->_tableIdColumn .')' : $this->_tableIdColumn,
                $this->_tableName,
                $this->_tableJoins,
                $this->_countJoins,
                $this->_criteria,
                $this->_group,
                $this->_groupSort
            );
            $rs = $this->_db->query($sql, $limit, $offset);
            $ret = array();
            while ($row = $rs->fetchRow()) {
                $ret[$row[0]] = $row[1];
            }

            return $ret;
        }
        
        $sql = sprintf(
            'SELECT COUNT(%s) FROM %s entity %s %s WHERE %s',
            $this->_distinct ? 'DISTINCT(' . $this->_tableIdColumn .')' : $this->_tableIdColumn,
            $this->_tableName,
            $this->_tableJoins,
            $this->_countJoins,
            $this->_criteria
        );

        return $this->_db->query($sql)->fetchSingle();
    }

    public function getEntityIds($limit, $offset)
    {
        $this->_parseFieldQuery();
        
        $sql = sprintf(
            'SELECT %s %s AS id %s FROM %s entity %s %s WHERE %s %s',
            $this->_distinct ? 'DISTINCT' : '',
            $this->_tableIdColumn,
            isset($this->_extraFields) ? ', ' . $this->_extraFields : '',
            $this->_tableName,
            $this->_tableJoins,
            $this->_joins,
            $this->_criteria,
            $this->_sorts
        );
        $rs = $this->_db->query($sql, $limit, $offset);
        $ret = array();
        if (isset($this->_extraFields)) {
            while ($row = $rs->fetchAssoc()) {
                $id = $row['id'];
                unset($row['id']);
                if (!isset($ret[$id])) {
                    $ret[$id] = $row;
                } else {
                    $ret[$id] = array_merge_recursive($ret[$id], $row);
                }
            }
        } else {
            while ($row = $rs->fetchAssoc()) {
                $ret[$row['id']] = $row['id'];
            }
        }

        return $ret;
    }

    private function _parseFieldQuery()
    {
        if ($this->_parsed) return;

        // Criteria
        $_criteria = array();
        $criteria = $this->_fieldQuery->getCriteria();
        $criteria->acceptVisitor($this, $_criteria);
        $this->_criteria = implode(' ', $_criteria);
        
        // Extra fields
        if ($extra_fields = $this->_fieldQuery->getExtraFields()) {
            foreach ($extra_fields as $as => $sql) {
                $extra_fields[$as] = $sql . ' AS ' . $as;
            }
            $this->_extraFields = implode(', ', $extra_fields);
        }

        // Sorts
        if ($sorts = $this->_fieldQuery->getSorts()) {
            $_sorts = array();
            foreach ($sorts as $sort) {
                if (!empty($sort['is_property'])) {
                    $_sorts[] = $this->_getPropertyColumn($sort['column']) . ' ' . $sort['order'];
                } elseif (!empty($sort['is_extra_field'])) {
                    $_sorts[] = $sort['field_name'] . ' ' . $sort['order'];
                } elseif (!empty($sort['is_random'])) {
                    $_sorts[] = 'RAND()';
                } else {
                    $table = $sort['field_name'];
                    if (!isset($this->_tables[$table])) {
                        $this->_tables[$table] = $this->_sortTables[$table] = $table;
                    }
                    $_sorts[] = $table . '.' . $sort['column'] . ' ' . $sort['order'];
                }
            }
            $this->_sorts = 'ORDER BY ' . implode(', ', $_sorts);
        } else {
            $this->_sorts = '';
        }
           
        // Group
        if ($group = $this->_fieldQuery->getGroup()) {
            if ($group['is_property']) {
                $this->_group = $this->_getPropertyColumn($group['column']);
            } else {
                $table = isset($group['table_alias']) ? $group['table_alias'] : $group['field_name'];
                if (!isset($this->_tables[$table])) {
                    $this->_tables[$table] = $group['field_name'];
                }
                $this->_group = $table . '.' . $group['column'];
            }
            $this->_groupSort = isset($group['order']) ? 'ORDER BY cnt ' . $group['order'] : '';
        }

        // Table joins
        if (!empty($this->_tables)) {
            $table_prefix = $this->_db->getResourcePrefix();
            foreach ($this->_tables as $table_alias => $table) {
                if (!is_array($table)) {
                    $_joins[$table_alias] = 'LEFT JOIN ' . $table_prefix . 'entity_field_' . $table
                        . ' ' . $table_alias . ' ON ' . $table_alias . '.entity_id = ' . $this->_tableIdColumn;
                } else {
                    $_joins[$table_alias] = sprintf(
                        'LEFT JOIN %1$s %2$s ON %2$s.%3$s',
                        $table['name'],
                        $table_alias,
                        isset($table['on']) ? $table['on'] : 'entity_id = ' . $this->_tableIdColumn
                    );
                }
            }
            if (!empty($this->_sortTables)) {
                $this->_joins = implode(' ', $_joins);
                // For the count query, remove table joins that are used for sorting purpose only
                $this->_countJoins = implode(' ', array_diff_key($_joins, $this->_sortTables));
            } else {
                $this->_joins = $this->_countJoins = implode(' ', $_joins);
            }
        } else {
            $this->_joins = $this->_countJoins = '';
        }
        
        $this->_distinct = $this->_fieldQuery->isDistinct();

        $this->_parsed = true;
    }

    /* Start implementation of SabaiFramework_Criteria_Visitor */

    public function visitCriteriaEmpty(SabaiFramework_Criteria_Empty $criteria, &$criterions)
    {
        $criterions[] = '1=1';
    }

    public function visitCriteriaComposite(SabaiFramework_Criteria_Composite $criteria, &$criterions)
    {
        if ($criteria->isEmpty()) {
            $criterions[] = '1=1';
            return;
        }
        $elements = $criteria->getElements();
        $count = count($elements);
        $conditions = $criteria->getConditions();
        $criterions[] = '(';
        $elements[0]->acceptVisitor($this, $criterions);
        for ($i = 1; $i < $count; $i++) {
            $criterions[] = $conditions[$i];
            $elements[$i]->acceptVisitor($this, $criterions);
        }
        $criterions[] = ')';
    }

    public function visitCriteriaCompositeNot(SabaiFramework_Criteria_CompositeNot $criteria, &$criterions)
    {
        $criterions[] = 'NOT';
        $criterions[] = $this->visitCriteriaComposite($criteria, $criterions);
    }

    private function _visitCriteriaValue(SabaiFramework_Criteria_Value $criteria, &$criterions, $operator)
    {
        $target = $criteria->getField();
        if ($target['is_property']) {
            $criterions[] = $this->_getPropertyColumn($target['column']);
            $data_type = $this->_tableColumns[$target['column']]['column_type'];
        } elseif ($target['field_name']) {
            $table = isset($target['table_alias']) ? $target['table_alias'] : $target['field_name'];
            $this->_tables[$table] = $target['field_name'];
            $criterions[] = $table . '.' . $target['column'];
            $data_type = $this->_fieldColumnTypes[$target['field_name']][$target['column']];
        } else {
            // custom target or extra field
            if (!empty($target['table'])) {
                foreach ($target['table'] as $table_name => $table) {
                    $this->_tables[$table['alias']] = array('name' => $table_name, 'on' => $table['on']);
                }
            }
            $criterions[] = $target['column'];
            $data_type = $target['column_type'];
        }
        $criterions[] = $operator;
        $criterions[] = Sabai_Addon_Entity_FieldStorage_Sql::escapeFieldValue($this->_db, $criteria->getValue(), $data_type);
    }

    public function visitCriteriaIs(SabaiFramework_Criteria_Is $criteria, &$criterions)
    {
        $this->_visitCriteriaValue($criteria, $criterions, '=');
    }

    public function visitCriteriaIsNot(SabaiFramework_Criteria_IsNot $criteria, &$criterions)
    {
        $this->_visitCriteriaValue($criteria, $criterions, '!=');
    }

    public function visitCriteriaIsSmallerThan(SabaiFramework_Criteria_IsSmallerThan $criteria, &$criterions)
    {
        $this->_visitCriteriaValue($criteria, $criterions, '<');
    }

    public function visitCriteriaIsGreaterThan(SabaiFramework_Criteria_IsGreaterThan $criteria, &$criterions)
    {
        $this->_visitCriteriaValue($criteria, $criterions, '>');
    }

    public function visitCriteriaIsOrSmallerThan(SabaiFramework_Criteria_IsOrSmallerThan $criteria, &$criterions)
    {
        $this->_visitCriteriaValue($criteria, $criterions, '<=');
    }

    public function visitCriteriaIsOrGreaterThan(SabaiFramework_Criteria_IsOrGreaterThan $criteria, &$criterions)
    {
        $this->_visitCriteriaValue($criteria, $criterions, '>=');
    }

    public function visitCriteriaIsNull(SabaiFramework_Criteria_IsNull $criteria, &$criterions)
    {
        $target = $criteria->getField();
        if ($target['is_property']) {
            $criterions[] = $this->_getPropertyColumn($target['column']);
        } elseif ($target['field_name']) {
            $table = isset($target['table_alias']) ? $target['table_alias'] : $target['field_name'];
            $this->_tables[$table] = $target['field_name'];
            $criterions[] = $table . '.' . $target['column'];
        } else {
            // custom target or extra field
            if (!empty($target['table'])) {
                foreach ($target['table'] as $table_name => $table) {
                    $this->_tables[$table['alias']] = array('name' => $table_name, 'on' => $table['on']);
                }
            }
            $criterions[] = $target['column'];
        }
        $criterions[] = 'IS NULL';
    }

    public function visitCriteriaIsNotNull(SabaiFramework_Criteria_IsNotNull $criteria, &$criterions)
    {
        $target = $criteria->getField();
        if ($target['is_property']) {
            $criterions[] = $this->_getPropertyColumn($target['column']);
        } elseif ($target['field_name']) {
            $table = isset($target['table_alias']) ? $target['table_alias'] : $target['field_name'];
            $this->_tables[$table] = $target['field_name'];
            $criterions[] = $table . '.' . $target['column'];
        } else {
            // custom target or extra field
            if (!empty($target['table'])) {
                foreach ($target['table'] as $table_name => $table) {
                    $this->_tables[$table['alias']] = array('name' => $table_name, 'on' => $table['on']);
                }
            }
            $criterions[] = $target['column'];
        }
        $criterions[] = 'IS NOT NULL';
    }

    private function _visitCriteriaArray(SabaiFramework_Criteria_Array $criteria, &$criterions, $format)
    {
        $values = $criteria->getArray();
        if (empty($values)) {
            return;
        }
        $target = $criteria->getField();
        if ($target['is_property']) {
            $data_type = $this->_tableColumns[$target['column']]['column_type'];
        } elseif ($target['field_name']) {
            $data_type = $this->_fieldColumnTypes[$target['field_name']][$target['column']];
        } else {
            // custom target or extra field
            $data_type = $target['column_type'];
        }
        foreach (array_keys($values) as $k) {
            $values[$k] = Sabai_Addon_Entity_FieldStorage_Sql::escapeFieldValue($this->_db, $values[$k], $data_type);
        }
        if ($target['is_property']) {
            $criterions[] = sprintf($format, $this->_getPropertyColumn($target['column']), implode(',', $values));
        } elseif ($target['field_name']) {
            $table = isset($target['table_alias']) ? $target['table_alias'] : $target['field_name'];
            $this->_tables[$table] = $target['field_name'];
            $criterions[] = sprintf($format, $table . '.' . $target['column'], implode(',', $values));
        } else {
            // custom target or extra field
            if (!empty($target['table'])) {
                foreach ($target['table'] as $table_name => $table) {
                    $this->_tables[$table['alias']] = array('name' => $table_name, 'on' => $table['on']);
                }
            }
            $criterions[] = sprintf($format, $target['column'], implode(',', $values));
        }
    }

    public function visitCriteriaIn(SabaiFramework_Criteria_In $criteria, &$criterions)
    {
        $this->_visitCriteriaArray($criteria, $criterions, '%s IN (%s)');
    }

    public function visitCriteriaNotIn(SabaiFramework_Criteria_NotIn $criteria, &$criterions)
    {
        $this->_visitCriteriaArray($criteria, $criterions, '%s NOT IN (%s)');
    }

    private function _visitCriteriaString(SabaiFramework_Criteria_String $criteria, &$criterions, $format)
    {
        $target = $criteria->getField();
        if ($target['is_property']) {
            $criterions[] = $this->_getPropertyColumn($target['column']);
            $data_type = $this->_tableColumns[$target['column']]['column_type'];
        } elseif ($target['field_name']) {
            $table = isset($target['table_alias']) ? $target['table_alias'] : $target['field_name'];
            $this->_tables[$table] = $target['field_name'];
            $criterions[] = $table . '.' . $target['column'];
            $data_type = $this->_fieldColumnTypes[$target['field_name']][$target['column']];
        } else {
            // custom target or extra field
            if (!empty($target['table'])) {
                foreach ($target['table'] as $table_name => $table) {
                    $this->_tables[$table['alias']] = array('name' => $table_name, 'on' => $table['on']);
                }
            }
            $criterions[] = $target['column'];
            $data_type = $target['column_type'];
        }
        $criterions[] = 'LIKE';
        $criterions[] = Sabai_Addon_Entity_FieldStorage_Sql::escapeFieldValue($this->_db, sprintf($format, $criteria->getString()), $data_type);
    }

    public function visitCriteriaStartsWith(SabaiFramework_Criteria_StartsWith $criteria, &$criterions)
    {
        $this->_visitCriteriaString($criteria, $criterions, '%s%%');
    }

    public function visitCriteriaEndsWith(SabaiFramework_Criteria_EndsWith $criteria, &$criterions)
    {
        $this->_visitCriteriaString($criteria, $criterions, '%%%s');
    }

    public function visitCriteriaContains(SabaiFramework_Criteria_Contains $criteria, &$criterions)
    {
        $this->_visitCriteriaString($criteria, $criterions, '%%%s%%');
    }

    private function _visitCriteriaField(SabaiFramework_Criteria_Field $criteria, &$criterions, $operator)
    {
        $criterions[] = '1=1';
    }

    public function visitCriteriaIsField(SabaiFramework_Criteria_IsField $criteria, &$criterions)
    {
        $this->_visitCriteriaField($criteria, $criterions, '=');
    }

    public function visitCriteriaIsNotField(SabaiFramework_Criteria_IsNotField $criteria, &$criterions)
    {
        $this->_visitCriteriaField($criteria, $criterions, '!=');
    }

    public function visitCriteriaIsSmallerThanField(SabaiFramework_Criteria_IsSmallerThanField $criteria, &$criterions)
    {
        $this->_visitCriteriaField($criteria, $criterions, '<');
    }

    public function visitCriteriaIsGreaterThanField(SabaiFramework_Criteria_IsGreaterThanField $criteria, &$criterions)
    {
        $this->_visitCriteriaField($criteria, $criterions, '>');
    }

    public function visitCriteriaIsOrSmallerThanField(SabaiFramework_Criteria_IsOrSmallerThanField $criteria, &$criterions)
    {
        $this->_visitCriteriaField($criteria, $criterions, '<=');
    }

    public function visitCriteriaIsOrGreaterThanField(SabaiFramework_Criteria_IsOrGreaterThanField $criteria, &$criterions)
    {
        $this->_visitCriteriaField($criteria, $criterions, '>=');
    }

    /* End implementation of SabaiFramework_Criteria_Visitor */
    
    private function _getPropertyColumn($column)
    {
        return isset($this->_tableColumns[$column]['column_real']) ? $this->_tableColumns[$column]['column_real'] : 'entity.' . $column;
    }
}