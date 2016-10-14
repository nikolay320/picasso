<?php
class SabaiFramework_Model_EntityCollection_Array extends SabaiFramework_Model_EntityCollection
{
    private $_entities;

    public function __construct(SabaiFramework_Model $model, $name, array $entities = array())
    {
        parent::__construct($model, $name);
        $this->_entities = array_merge($entities, array()); // reindex array
    }

    public function offsetExists($index)
    {
        return array_key_exists($index, $this->_entities);
    }

    public function offsetGet($index)
    {
        return $this->_entities[$index];
    }

    public function offsetSet($index, $value)
    {
        $this->_entities[$index] = $value;
    }

    public function offsetUnset($index)
    {
        unset($this->_entities[$index]);
    }

    public function count()
    {
        return count($this->_entities);
    }
}