<?php
class SabaiFramework_Model_EntityCollection_Decorator_DescendantEntitiesCount extends SabaiFramework_Model_EntityCollection_Decorator
{
    protected $_entityName;
    protected $_descendantEntitiesCount;

    public function __construct($entityName, SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
        $this->_entityName = $entityName;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_descendantEntitiesCount)) {
            $this->_descendantEntitiesCount = array();
            if ($this->_collection->count() > 0) {
                $parent_ids = $this->_collection->getAllIds();
                $this->_descendantEntitiesCount = $this->_model->getRepository($this->_entityName)->countDescendantsByIds($parent_ids);
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        $id = $current->id;
        $current->setDescendantsCount(isset($this->_descendantEntitiesCount[$id]) ? $this->_descendantEntitiesCount[$id] : 0);

        return $current;
    }
}