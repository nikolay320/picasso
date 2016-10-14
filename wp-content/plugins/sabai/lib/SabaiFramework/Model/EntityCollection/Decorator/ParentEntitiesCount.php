<?php
class SabaiFramework_Model_EntityCollection_Decorator_ParentEntitiesCount extends SabaiFramework_Model_EntityCollection_Decorator
{
    protected $_entityName;
    protected $_parentEntitiesCount;

    public function __construct($entityName, SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
        $this->_entityName = $entityName;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_parentEntitiesCount)) {
            $this->_parentEntitiesCount = array();
            if ($this->_collection->count() > 0) {
                $this->_parentEntitiesCount = $this->_model->getRepository($this->_entityName)->countParentsByIds($this->_collection->getAllIds());
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        $id = $current->id;
        $current->setParentsCount(isset($this->_parentEntitiesCount[$id]) ? $this->_parentEntitiesCount[$id] : 0);

        return $current;
    }
}