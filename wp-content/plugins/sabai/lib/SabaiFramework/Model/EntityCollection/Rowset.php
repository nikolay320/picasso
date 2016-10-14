<?php
abstract class SabaiFramework_Model_EntityCollection_Rowset extends SabaiFramework_Model_EntityCollection
{
    protected $_rs, $_emptyEntity, $_count;

    public function __construct($name, SabaiFramework_DB_Rowset $rs, SabaiFramework_Model_Entity $emptyEntity, SabaiFramework_Model $model)
    {
        parent::__construct($model, $name);
        $this->_rs = $rs;
        $this->_emptyEntity = $emptyEntity;
    }

    public function count()
    {
        if (!isset($this->_count)) {
            $this->_count = is_object($this->_rs) ? $this->_rs->rowCount() : 0;
        }

        return $this->_count;
    }

    public function offsetExists($index)
    {
        return $index < $this->count();
    }

    public function offsetGet($index)
    {
        $this->_rs->seek($index);
        $entity = clone $this->_emptyEntity;
        $this->_loadRow($entity, $this->_rs->fetchAssoc());

        return $entity;
    }

    public function offsetSet($index, $value)
    {

    }

    public function offsetUnset($index)
    {

    }

    abstract protected function _loadRow(SabaiFramework_Model_Entity $entity, array $row);
}