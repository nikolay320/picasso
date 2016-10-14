<?php
class SabaiFramework_Model_Paginator_Entity extends SabaiFramework_Model_Paginator
{
    protected $_entityName;
    protected $_entityId;

    public function __construct($repository, $entityName, $entityId, $perpage, $sort, $order)
    {
        parent::__construct($repository, $perpage, $sort, $order);
        $this->_entityName = $entityName;
        $this->_entityId = $entityId;
    }

    protected function _getElementCount()
    {
        $method = 'countBy' . $this->_entityName;
        return $this->_repository->$method($this->_entityId);
    }

    protected function _getElements($limit, $offset)
    {
        $method = 'fetchBy' . $this->_entityName;
        return $this->_repository->$method($this->_entityId, $limit, $offset, $this->_sort, $this->_order);
    }
}