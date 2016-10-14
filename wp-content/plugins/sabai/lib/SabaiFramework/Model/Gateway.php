<?php
abstract class SabaiFramework_Model_Gateway implements SabaiFramework_Criteria_Visitor
{
    /**
     * @var SabaiFramework_DB
     */
    protected $_db;

    public function setDB(SabaiFramework_DB $db)
    {
        $this->_db = $db;
    }

    public function getTableName()
    {
        return $this->_db->getResourcePrefix() . $this->getName();
    }

    /**
     * @return array All fields used within this gateway
     */
    public function getAllFields()
    {
        return $this->getFields() + $this->getSortFields();
    }

    public function selectById($id, array $fields = array())
    {
        return $this->_db->query($this->_getSelectByIdQuery($id, $fields));
    }

    public function selectByIds(array $ids, array $fields = array())
    {
        return $this->_db->query($this->_getSelectByIdsQuery($ids, $fields));
    }

    public function selectByCriteria(SabaiFramework_Criteria $criteria, array $fields = array(), $limit = 0, $offset = 0, array $sort = null, array $order = null, $group = null)
    {
        $criterions = array();
        $criteria->acceptVisitor($this, $criterions);
        $query = $this->_getSelectByCriteriaQuery(implode(' ', $criterions), $fields);

        return $this->selectBySQL($query, $limit, $offset, $sort, $order, $group);
    }

    public function insert(array $values)
    {
        $this->_beforeInsert($values);
        if (1 !== $this->_db->exec($this->_getInsertQuery($values))) {
            throw new SabaiFramework_Exception(sprintf('Failed inserting a new row to the table %s. Last DB error: %s', $this->getTableName(), $this->_db->lastError()));
        }
        if (false === $id = $this->_db->lastInsertId($this->getTableName(), $this->_getIdFieldName())) {
            throw new SabaiFramework_Exception(sprintf('Failed fetching the last insert ID from %s. Last DB error: %s', $this->getTableName(), $this->_db->lastError()));
        }
        if (0 === $id) { // PK is not an auto_increment field
            $id = $values[$this->_getIdFieldName()];
        } else {
            $values[$this->_getIdFieldName()] = $id;
        }
        $this->_afterInsert($id, $values);

        return $id;
    }

    protected function _beforeInsert(array &$new) {}

    protected function _afterInsert($id, array $new){}

    public function updateById($id, array $values)
    {
        if (!$old = $this->selectById($id)->fetchAssoc()) {
            throw new SabaiFramework_Exception(sprintf('Failed fetching a row with an ID of %s from %s on before update. Last DB error: %s', $id, $this->getTableName(), $this->_db->lastError()));
        }
        $this->_beforeUpdate($id, $values, $old);
        $this->_db->exec($this->_getUpdateQuery($id, $values));
        $this->_afterUpdate($id, $values, $old);
    }

    protected function _beforeUpdate($id, array &$new, array $old) {}

    protected function _afterUpdate($id, array $new, array $old){}

    public function deleteById($id)
    {
        if (!$old = $this->selectById($id)->fetchAssoc()) {
            throw new SabaiFramework_Exception(sprintf('Failed fetching a row with an ID of %s from %s on before delete. Last DB error: %s', $id, $this->getTableName(), $this->_db->lastError()));
        }
        $this->_beforeDelete($id, $old);
        $this->_db->exec($this->_getDeleteQuery($id));
        $this->_afterDelete($id, $old);
    }

    protected function _beforeDelete($id, array $old) {}

    protected function _afterDelete($id, array $old) {}

    /**
     * Enter description here...
     *
     * @param SabaiFramework_Criteria $criteria
     * @param array $values
     * @return int Number of affected rows
     * @throws SabaiFramework_DB_Exception
     */
    public function updateByCriteria(SabaiFramework_Criteria $criteria, array $values)
    {
        $sets = array();
        $fields = $this->getFields();
        foreach (array_keys($values) as $k) {
            if (isset($fields[$k])) {
                $operator = '=';
                $this->_sanitizeForQuery($values[$k], $fields[$k], $operator);
                $sets[$k] = $k . $operator . $values[$k];
            }
        }
        $criterions = array();
        $criteria->acceptVisitor($this, $criterions);

        return $this->_db->exec($this->_getUpdateByCriteriaQuery(implode(' ', $criterions), $sets));
    }

    public function deleteByCriteria(SabaiFramework_Criteria $criteria)
    {
        $criterions = array();
        $criteria->acceptVisitor($this, $criterions);

        return $this->_db->exec($this->_getDeleteByCriteriaQuery(implode(' ', $criterions)));
    }

    public function countByCriteria(SabaiFramework_Criteria $criteria)
    {
        $criterions = array();
        $criteria->acceptVisitor($this, $criterions);

        return $this->_db->query($this->_getCountByCriteriaQuery(implode(' ', $criterions)))->fetchSingle();
    }

    public function selectBySQL($sql, $limit = 0, $offset = 0, array $sort = null, array $order = null, $group = null)
    {
        if (isset($group)) {
            $fields = $this->getFields();
            $groups = array();
            foreach ((array)$group as $_group) {
                if (isset($fields[$_group])) $groups[] = $_group;
            }
            if (!empty($groups)) $sql .= ' GROUP BY ' . implode(', ', $groups);
        }
        if (isset($sort)) {
            $sort_fields = $this->getSortFields();
            foreach (array_keys($sort) as $i) {
                if (isset($sort_fields[$sort[$i]])) {
                    $order_by[] = $sort[$i] . ' ' . (isset($order[$i]) && $order[$i] == 'DESC' ? 'DESC': 'ASC');
                }
            }
            if (isset($order_by)) $sql .= ' ORDER BY ' . implode(', ', $order_by);
        }

        return $this->_db->query($sql, $limit, $offset);
    }

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
        $field = $criteria->getField();
        $fields = $this->getAllFields();
        if (!isset($fields[$field])) return;

        $value = $criteria->getValue();
        $this->_sanitizeForQuery($value, $fields[$field], $operator);
        $criterions[] = $field;
        $criterions[] = $operator;
        $criterions[] = $value;
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
        $field = $criteria->getField();
        $fields = $this->getAllFields();
        if (!isset($fields[$field])) return;

        $criterions[] = $field;
        $criterions[] = 'IS NULL';
    }

    public function visitCriteriaIsNotNull(SabaiFramework_Criteria_IsNotNull $criteria, &$criterions)
    {
        $field = $criteria->getField();
        $fields = $this->getAllFields();
        if (!isset($fields[$field])) return;

        $criterions[] = $field;
        $criterions[] = 'IS NOT NULL';
    }

    private function _visitCriteriaArray(SabaiFramework_Criteria_Array $criteria, &$criterions, $format)
    {
        $field = $criteria->getField();
        $fields = $this->getAllFields();
        if (!isset($fields[$field])) return;

        $values = $criteria->getArray();
        if (!empty($values)) {
            $data_type = $fields[$field];
            $operator = null;
            foreach ($values as $v) {
                $this->_sanitizeForQuery($v, $data_type, $operator);
                $value[] = $v;
            }
            $criterions[] = sprintf($format, $field, implode(',', $value));
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
        $field = $criteria->getField();
        $fields = $this->getAllFields();
        if (!isset($fields[$field])) return;

        $value = sprintf($format, $criteria->getString());
        $operator = 'LIKE';
        $this->_sanitizeForQuery($value, $fields[$field], $operator);
        $criterions[] = $field;
        $criterions[] = 'LIKE';
        $criterions[] = $value;
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
        $field = $criteria->getField();
        $field2 = $criteria->getField2();
        $fields = $this->getAllFields();
        if (!isset($fields[$field]) || !isset($fields[$field2])) return;

        $criterions[] = $field;
        $criterions[] = $operator;
        $criterions[] = $field2;
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

    /**
     * @param mixed $value
     * @param int $dataType
     * @param string $operator
     */
    private function _sanitizeForQuery(&$value, $dataType = null, &$operator)
    {
        switch ($dataType) {
            case SabaiFramework_Model::KEY_TYPE_INT_NULL:
                if (is_numeric($value)) {
                    $value = intval($value);
                } else {
                    $value = 'NULL';
                    $operator = ($operator === '!=') ? 'IS NOT' : 'IS';
                }
                return;
            case SabaiFramework_Model::KEY_TYPE_INT:
                $value = intval($value);
                return;
            case SabaiFramework_Model::KEY_TYPE_TEXT:
            case SabaiFramework_Model::KEY_TYPE_VARCHAR:
                $value = $this->_db->escapeString($value);
                return;
            case SabaiFramework_Model::KEY_TYPE_FLOAT:
                $value = str_replace(',', '.', floatval($value));
                return;
            case SabaiFramework_Model::KEY_TYPE_BOOL:
                $value = $this->_db->escapeBool($value);
                return;
            case SabaiFramework_Model::KEY_TYPE_BLOB:
                $value = $this->_db->escapeBlob($value);
                return;
            default:
                $value = $this->_db->escapeString($value);
        }
    }

    /**
     * Gets the fields that can be used for sorting.
     * This method will only be overwritten by assoc entities.
     *
     * @return array
     */
    public function getSortFields()
    {
        return $this->getFields();
    }

    /**
     * Gets the last error message returned by the database driver
     *
     * @return string
     */
    public function getError()
    {
        return $this->_db->lastError();
    }

    abstract public function getName();
    abstract public function getFields();
    abstract protected function _getIdFieldName();
    abstract protected function _getSelectByIdQuery($id, $fields);
    abstract protected function _getSelectByIdsQuery($ids, $fields);
    abstract protected function _getSelectByCriteriaQuery($criteriaStr, $fields);
    abstract protected function _getInsertQuery(&$values);
    abstract protected function _getUpdateQuery($id, $values);
    abstract protected function _getDeleteQuery($id);
    abstract protected function _getUpdateByCriteriaQuery($criteriaStr, $sets);
    abstract protected function _getDeleteByCriteriaQuery($criteriaStr);
    abstract protected function _getCountByCriteriaQuery($criteriaStr);
}