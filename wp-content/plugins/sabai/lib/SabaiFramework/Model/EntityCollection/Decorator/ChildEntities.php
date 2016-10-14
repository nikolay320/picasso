<?php
class SabaiFramework_Model_EntityCollection_Decorator_ChildEntities extends SabaiFramework_Model_EntityCollection_Decorator
{
    protected $_parentKey;
    protected $_entityName;
    protected $_childEntities;

    public function __construct($entityName, $parentKey, SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
        $this->_parentKey = $parentKey;
        $this->_entityName = $entityName;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_childEntities)) {
            $this->_childEntities = array();
            if ($this->_collection->count() > 0) {
                $criteria = new SabaiFramework_Criteria_In($this->_parentKey, $this->_collection->getAllIds());
                $children = $this->_model->getRepository($this->_entityName)->fetchByCriteria($criteria);
                foreach ($children as $child) {
                    $this->_childEntities[$child->parent][] = $child;
                }
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        $id = $current->id;
        $entities = !empty($this->_childEntities[$id]) ? $this->_childEntities[$id] : array();
        $current->assignObject('Children', $this->getModel()->createCollection($this->_entityName, $entities));

        return $current;
    }
}