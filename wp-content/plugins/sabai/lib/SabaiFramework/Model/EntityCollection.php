<?php
abstract class SabaiFramework_Model_EntityCollection implements Iterator, Countable, ArrayAccess
{
    protected $_name, $_model;
    private $_array, $_key = 0;

    protected function __construct(SabaiFramework_Model $model, $name)
    {
        $this->_name = $name;
        $this->_model = $model;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getModel()
    {
        return $this->_model;
    }

    public function setModel(SabaiFramework_Model $model)
    {
        $this->_model = $model;
    }

    public function with($decoration)
    {
        $args = func_get_args();
        return $this->_model->decorate($this, $args);
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getAllIds()
    {
        return array_keys($this->getArray());
    }

    public function getArray($var = null, $key = null)
    {
        $ret = array();

        if (!isset($this->_array)) {
            $this->_array = array();
            $this->rewind();
            $key = isset($key) ? $key : 'id';
            while ($this->valid()) {
                $entity = $this->current();
                $this->_array[$entity->id] = $entity;
                $ret[$entity->$key] = isset($var) ? $entity->$var : $entity;
                $this->next();
            }
        } else {
            $key = isset($key) ? $key : 'id';
            foreach (array_keys($this->_array) as $id) {
                $entity = $this->_array[$id];
                $ret[$entity->$key] = isset($var) ? $entity->$var : $entity;
            }
        }

        return $ret;
    }

    /**
     * Updates values of all the entities within the collection
     *
     * @param array $values
     */
    public function update(array $values, $commit = false)
    {
        $this->rewind();
        while ($this->valid()) {
            foreach ($values as $key => $value) {
                $this->current()->set($key, $value);
            }
            $this->next();
        }
        if ($commit) $this->_model->commit();
    }

    /**
     * Mark all the entities within the collection from as removed
     */
    public function delete($commit = false)
    {
        $this->rewind();
        while ($this->valid()) {
            $this->current()->markRemoved();
            $this->next();
        }
        if ($commit) $this->_model->commit();
    }

    /**
     * @return mixed
     */
    public function getNext()
    {
        $ret = false;
        if ($this->valid()) {
            $ret = $this->current();
            $this->next();
        }
        return $ret;
    }

    /**
     * @return mixed
     */
    public function getFirst()
    {
        $this->rewind();
        return $this->valid() ? $this->current() : false;
    }

    public function rewind()
    {
        $this->_key = 0;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->offsetExists($this->_key);
    }

    public function next()
    {
        ++$this->_key;
    }

    /**
     * @return SabaiFramework_Model_Entity
     */
    public function current()
    {
        $ret = $this->offsetGet($this->_key);
        $this->_model->cacheEntity($ret);

        return $ret;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->_key;
    }
}