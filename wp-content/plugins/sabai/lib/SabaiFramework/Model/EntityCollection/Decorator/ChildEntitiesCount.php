<?php
class SabaiFramework_Model_EntityCollection_Decorator_ChildEntitiesCount extends SabaiFramework_Model_EntityCollection_Decorator
{
    protected $_entityName;
    protected $_childEntitiesCount;

    public function __construct($entityName, SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
        $this->_entityName = $entityName;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_childEntitiesCount)) {
            $this->_childEntitiesCount = array();
            if ($this->_collection->count() > 0) {
                $parent_ids = $this->_collection->getAllIds();
                $this->_childEntitiesCount = $this->_model->getRepository($this->_entityName)->countByParent($parent_ids);
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        $id = $current->id;
        $current->setChildrenCount(isset($this->_childEntitiesCount[$id]) ? $this->_childEntitiesCount[$id] : 0);

        return $current;
    }
}