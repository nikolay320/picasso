<?php
class SabaiFramework_Model_Paginator_EntityCriteria extends SabaiFramework_Model_Paginator
{
    protected $_entityName;
    protected $_entityId;
    protected $_criteria;

    public function __construct(SabaiFramework_Model_EntityRepository $repository, $entityName, $entityId, SabaiFramework_Criteria $criteria, $perpage, $sort, $order)
    {
        parent::__construct($repository, $perpage, $sort, $order);
        $this->_entityName = $entityName;
        $this->_entityId = $entityId;
        $this->_criteria = $criteria;
    }

    protected function _getElementCount()
    {
        $method = 'countBy' . $this->_entityName . 'AndCriteria';
        return $this->_repository->$method($this->_entityId, $this->_criteria);
    }

    protected function _getElements($limit, $offset)
    {
        $method = 'fetchBy' . $this->_entityName . 'AndCriteria';
        return $this->_repository->$method($this->_entityId, $this->_criteria, $limit, $offset, $this->_sort, $this->_order);
    }
}