<?php
class SabaiFramework_Model_EntityCollection_Decorator_ForeignEntities extends SabaiFramework_Model_EntityCollection_Decorator
{
    protected $_foreignSelfKey;
    protected $_foreignEntityName;
    protected $_foreignEntities;
    protected $_selfForeignVar;
    protected $_foreitnEntityObjectVarName;

    public function __construct($foreignSelfKey, $foreignEntityName, SabaiFramework_Model_EntityCollection $collection, $foreignEntityObjectVarName = null, $selfForeignVar = null)
    {
        parent::__construct($collection);
        $this->_foreignSelfKey = $foreignSelfKey;
        $this->_foreignEntityName = $foreignEntityName;
        $this->_foreitnEntityObjectVarName = isset($foreignEntityObjectVarName) ? $foreignEntityObjectVarName : $foreignEntityName;
        $this->_selfForeignVar = $selfForeignVar;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_foreignEntities)) {
            $this->_foreignEntities = array();
            if ($this->_collection->count() > 0) {
                $ids = !isset($this->_selfForeignVar) ? $this->_collection->getAllIds() : $this->_collection->getArray($this->_selfForeignVar);
                $criteria = new SabaiFramework_Criteria_In($this->_foreignSelfKey, $ids);
                $foreign_var = substr($this->_foreignSelfKey, strpos($this->_foreignSelfKey, '_') + 1);
                foreach ($this->_model->getRepository($this->_foreignEntityName)->fetchByCriteria($criteria) as $entity) {
                    $this->_foreignEntities[$entity->$foreign_var][] = $entity;
                }
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        $id = $current->id;
        $entities = !empty($this->_foreignEntities[$id]) ? $this->_foreignEntities[$id] : array();
        $current->assignObject($this->_foreitnEntityObjectVarName, $this->_model->createCollection($this->_foreignEntityName, $entities));

        return $current;
    }
}