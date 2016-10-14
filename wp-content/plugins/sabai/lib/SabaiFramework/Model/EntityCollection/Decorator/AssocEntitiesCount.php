<?php
class SabaiFramework_Model_EntityCollection_Decorator_AssocEntitiesCount extends SabaiFramework_Model_EntityCollection_Decorator
{
    protected $_linkEntityName;
    protected $_linkSelfKey;
    protected $_assocEntityName;
    protected $_assocEntitiesCount;

    public function __construct($linkEntityName, $linkSelfKey, $assocEntityName, SabaiFramework_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
        $this->_linkEntityName = $linkEntityName;
        $this->_linkSelfKey = $linkSelfKey;
        $this->_assocEntityName = $assocEntityName;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_assocEntitiesCount)) {
            $this->_assocEntitiesCount = array();
            if ($this->_collection->count() > 0) {
                $criteria = new SabaiFramework_Criteria_In($this->_linkSelfKey, $this->_collection->getAllIds());
                $fields = array($this->_linkSelfKey, 'COUNT(*)');
                if ($rs = $this->_model->getGateway($this->_linkEntityName)->selectByCriteria($criteria, $fields, 0, 0, null, null, $this->_linkSelfKey)) {
                    while ($row = $rs->fetchRow()) {
                        $this->_assocEntitiesCount[$row[0]] = $row[1];
                    }
                }
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        $count = isset($this->_assocEntitiesCount[$current->id]) ? $this->_assocEntitiesCount[$current->id] : 0;
        $current->assignObject($this->_assocEntityName . 'Count', $count);

        return $current;
    }
}