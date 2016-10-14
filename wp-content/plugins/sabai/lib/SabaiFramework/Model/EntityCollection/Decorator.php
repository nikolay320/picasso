<?php
abstract class SabaiFramework_Model_EntityCollection_Decorator extends SabaiFramework_Model_EntityCollection
{
    /**
     * @var SabaiFramework_Model_EntityCollection
     */
    protected $_collection;

    /**
     * Constructor
     *
     * @param SabaiFramework_Model_EntityCollection $collection
     * @param SabaiFramework_Model $model
     * @return SabaiFramework_Model_EntityCollection_Decorator
     */
    public function __construct(SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct($collection->getModel(), $collection->getName());
        $this->_collection = $collection;
    }

    public function offsetExists($index)
    {
        return $this->_collection->offsetExists($index);
    }

    public function offsetGet($index)
    {
        return $this->_collection->offsetGet($index);
    }

    public function offsetSet($index, $value)
    {
        $this->_collection->offsetSet($index, $value);
    }

    public function offsetUnset($index)
    {
        $this->_collection->offsetUnset($index);
    }

    public function count()
    {
        return $this->_collection->count();
    }

    public function rewind()
    {
        $this->_collection->rewind();
    }

    public function valid()
    {
        return $this->_collection->valid();
    }

    public function next()
    {
        $this->_collection->next();
    }

    public function current()
    {
        return $this->_collection->current();
    }

    public function key()
    {
        return $this->_collection->key();
    }

}