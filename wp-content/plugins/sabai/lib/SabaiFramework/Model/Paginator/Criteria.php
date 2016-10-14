<?php
class SabaiFramework_Model_Paginator_Criteria extends SabaiFramework_Model_Paginator
{
    protected $_criteria;

    public function __construct(SabaiFramework_Model_EntityRepository $repository, SabaiFramework_Criteria $criteria, $perpage, $sort, $order)
    {
        parent::__construct($repository, $perpage, $sort, $order);
        $this->_criteria = $criteria;
    }

    protected function _getElementCount()
    {
        return $this->_repository->countByCriteria($this->_criteria);
    }

    protected function _getElements($limit, $offset)
    {
        return $this->_repository->fetchByCriteria($this->_criteria, $limit, $offset, $this->_sort, $this->_order);
    }
}