<?php
class SabaiFramework_Model_EntityCollection_Decorator_AssocEntities extends SabaiFramework_Model_EntityCollection_Decorator
{
    protected $_linkEntityName;
    protected $_linkSelfKey;
    protected $_assocEntityTable;
    protected $_assocEntityName;
    protected $_assocEntities;

    public function __construct($linkEntityName, $linkSelfKey, $assocEntityTable, $assocEntityName, SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
        $this->_linkEntityName = $linkEntityName;
        $this->_linkSelfKey = $linkSelfKey;
        $this->_assocEntityTable = $assocEntityTable;
        $this->_assocEntityName = $assocEntityName;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_assocEntities)) {
            $this->_assocEntities = array();
            if ($this->_collection->count() > 0) {
                $criteria = new SabaiFramework_Criteria_In($this->_linkSelfKey, $this->_collection->getAllIds());
                $fields = array($this->_linkSelfKey, $this->_assocEntityTable . '.*');
                if ($rs = $this->_model->getGateway($this->_linkEntityName)->selectByCriteria($criteria, $fields)) {
                    while ($row = $rs->fetchAssoc()) {
                        $entity = $this->_model->create($this->_assocEntityName);
                        $entity->initVars($row);
                        $this->_assocEntities[$row[$this->_linkSelfKey]][] = $entity;
                    }
                }
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        $id = $current->id;
        $entities = !empty($this->_assocEntities[$id]) ? $this->_assocEntities[$id] : array();
        $current->assignObject($this->_assocEntityName, $this->_model->createCollection($this->_assocEntityName, $entities));

        return $current;
    }
}